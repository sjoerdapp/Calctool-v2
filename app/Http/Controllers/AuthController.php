<?php

namespace Calctool\Http\Controllers;

use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;
use Longman\TelegramBot\Telegram as TTelegram;
use Longman\TelegramBot\Request as TRequest;
use Illuminate\Validation\ValidationException;

use \Calctool\Models\User;
use \Calctool\Models\UserType;
use \Calctool\Models\Audit;
use \Calctool\Models\Telegram;
use \Calctool\Models\MessageBox;

use \Auth;
use \Redis;
use \Hash;
use \Mailgun;

class AuthController extends Controller {

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Route
	 */
	public function doLogin(Request $request)
	{
		$errors = new MessageBag;

		$username = strtolower(trim($request->input('username')));
		$userdata = array(
			'username' 	=> $username,
			'password' 	=> $request->input('secret'),
			'active' 	=> 1,
			'banned' 	=> NULL
		);

		$userdata2 = array(
			'email' 	=> $username,
			'password' 	=> $request->input('secret'),
			'active' 	=> 1,
			'banned' 	=> NULL
		);

		$remember = $request->input('rememberme') ? true : false;

		if (Redis::exists('auth:'.$username.':block')) {
			$errors = new MessageBag(['auth' => ['Account geblokkeerd voor 15 minuten']]);
			return back()->withErrors($errors)->withInput($request->except('secret'));
		}

		if(Auth::attempt($userdata, $remember) || Auth::attempt($userdata2, $remember)){

			/* Email must be confirmed */
			if (Auth::user()->confirmed_mail == NULL) {
				Auth::logout();
				$errors = new MessageBag(['mail' => ['Email nog niet bevestigd']]);
				return back()->withErrors($errors)->withInput($request->except('secret'));
			}

			Redis::del('auth:'.$username.':fail', 'auth:'.$username.':block');

			Audit::CreateEvent('auth.login.succces', 'Login with: ' . \Calctool::remoteAgent());

			if (Auth::user()->isSystem()) {
				return redirect('/admin');
			}

			/* Redirect to dashboard*/
			return redirect('/');
		} else {

			// Login failed
			$errors = new MessageBag(['password' => ['Gebruikersnaam of wachtwoord verkeerd']]);

			// Count the failed logins
			$failcount = Redis::get('auth:'.$username.':fail');
			if ($failcount >= 4) {
				Redis::set('auth:'.$username.':block', true);
				Redis::expire('auth:'.$username.':block', 900);
			} else {
				Redis::incr('auth:'.$username.':fail');
			}

			$failuser = \Calctool\Models\User::where('username', $username)->first();
			if ($failuser) {
				Audit::CreateEvent('auth.login.failed', 'Failed tries: ' . $failcount, $failuser->id);
			}

			return back()->withErrors($errors)->withInput($request->except('secret'))->withCookie(cookie()->forget('swpsess'));
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Route
	 */
	public function doRegister(Request $request)
	{
		$request->merge(array('username' => strtolower(trim($request->input('username')))));
		$request->merge(array('email' => strtolower(trim($request->input('email')))));
		
		$this->validate($request, [
			'username' => array('required','max:30','unique:user_account'),
			'email' => array('required','max:80','email','unique:user_account'),
			'secret' => array('required','confirmed','min:5'),
			'secret_confirmation' => array('required','min:5'),
		]);

		$user = new User;
		$user->username = $request->get('username');
		$user->secret = Hash::make($request->get('secret'));
		$user->firstname = $user->username;
		$user->api = md5(mt_rand());
		$user->token = sha1($user->secret);
		$user->referral_key = md5(mt_rand());
		$user->ip = \Calctool::remoteAddr();
		$user->email = $request->get('email');
		$user->expiration_date = date('Y-m-d', strtotime("+1 month", time()));
		$user->user_type = UserType::where('user_type','=','user')->first()->id;
		$user->user_group = 100;

		$data = array('email' => $user->email, 'api' => $user->api, 'token' => $user->token, 'username' => $user->username);
		Mailgun::send('mail.confirm', $data, function($message) use ($data) {
			$message->to($data['email'], strtolower(trim($data['username'])));
			$message->subject('CalculatieTool.com - Account activatie');
			$message->bcc('info@calculatietool.com', 'CalculatieTool.com');
			$message->from('info@calculatietool.com', 'CalculatieTool.com');
			$message->replyTo('info@calculatietool.com', 'CalculatieTool.com');
		});

		$user->save();

		Audit::CreateEvent('account.new.success', 'Created new account from template', $user->id);

		return back()->with('success', 'Account aangemaakt, er is een bevestingsmail verstuurd');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Route
	 */
	public function doNewPassword(Request $request, $api, $token)
	{
		$this->validate($request, [
			'secret' => array('required','confirmed','min:5'),
			'secret_confirmation' => array('required','min:5'),
		]);

		$user = User::where('token','=',$token)->where('api','=',$api)->first();
		if (!$user) {
			$errors = new MessageBag(['activate' => ['Activatielink is niet geldig']]);
			return redirect('login')->withErrors($errors);
		}
		$user->secret = Hash::make($request->get('secret'));
		$user->active = true;
		$user->token = sha1($user->secret);
		$user->save();

		Audit::CreateEvent('auth.update.password.success', 'Updated with: ' . \Calctool::remoteAgent(), $user->id);

		Auth::login($user);
		return redirect('/');
	}

	private function informAdmin(User $newuser)
	{
		if (env('TELEGRAM_ENABLED') && env('TELEGRAM_ENABLED') == "true") {
			$telegram = new TTelegram(env('TELEGRAM_API'), env('TELEGRAM_NAME'));
			TRequest::initialize($telegram);

			foreach (User::where('user_type','=',UserType::where('user_type','=','admin')->first()->id)->get() as $admin) {
				$tgram = Telegram::where('user_id','=',$admin->id)->first();
				if ($tgram && $tgram->alert) {

					$text  = "Nieuwe gebruiker aangemeld\n";
					$text .= "Gebruikersnaam: " . $newuser->username . "\n";
					$text .= "Email: " . $newuser->email . "\n";

					$data = array();
					$data['chat_id'] = $tgram->uid;
					$data['text'] = $text;

					$result = TRequest::sendMessage($data);
				}
			}
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Route
	 */
	public function doActivate(Request $request, $api, $token)
	{
		$user = User::where('token','=',$token)->where('api','=',$api)->first();
		if (!$user) {
			$errors = new MessageBag(['activate' => ['Activatielink is niet geldig']]);
			return redirect('login')->withErrors($errors);
		}
		if ($user->confirmed_mail) {
			$errors = new MessageBag(['activate' => ['Account is al geactiveerd']]);
			return redirect('login')->withErrors($errors);
		}
		$user->confirmed_mail = date('Y-m-d H:i:s');
		$user->save();

		// \DemoProjectTemplate::setup($user->id);

		$this->informAdmin($user);

		$message = new MessageBox;
		$message->subject = 'Welkom ' . $user->username;
		$message->message = 'Beste ' . $user->username . ',<br /><br />Welkom bij de CalculatieTool.com,<br /><br />

		Je account is aangemaakt met alles dat de CalculatieTool.com te bieden heeft. Geen verborgen of afgeschermde delen en alles is beschikbaar. Hieronder valt ook onze GRATIS service jouw offertes en facturen door ons te laten versturen met de post. Jouw persoonlijke account is dus klaar voor gebruik, succes met het Calculeren, Offreren, Registreren, Facturen en Administreren met deze al-in-one-tool!
			<br>
			<br>
		Graag willen we nog even een paar dingen uitleggen voordat je aan de slag gaat. Het programma is iets anders van opzet dan de doorsnee calculatie apps. Het grootste verschil zit hem in de module calculeren. Deze is opgezet volgens het <i>biervilt principe</i>. Dit houdt in dat je geen normeringen kunt toe passen. 
			<br>
			<br>
		Je geeft gewoon letterlijk op wat je nodig hebt voor de arbeid, het materiaal en het materieel. Wij richten ons echt op de ZZP markt en daar kwam deze wens van calculeren uit naar voren. Het programma is heel duidelijk van opzet en je hebt het echt zo onder knie, desalniettemin kunnen er altijd vagen zijn. Stel deze gerust, dan proberen wij deze zo snel mogelijk te beantwoorden.
			<br>
			<br>
		Probeer het programma echt even te doorgronden. Er wordt je echt een hoop werk uit handen genomen als je het onder de knie hebt en je zal er net als andere gebruikers een hoop plezier aan beleven.
			<br>
			<br>
		Er zijn diverse filmpjes die de modules duidelijk maken. 
			<br>
			<br>
		De prijs die wij na de 30 dagen proefperiode hanteren is blijvend laag en je kan altijd  alle functionaliteiten gebruiken die het programma biedt.
			<br>
			<br>
			<br />Wanneer de Quickstart pop-up of de pagina <a href="/mycompany">mijn bedrijf</a> wordt ingevuld kan je direct aan de slag met je eerste project.<br /><br />Groet, Maikel van de CalculatieTool.com';

		$message->from_user = User::where('username', 'system')->first()['id'];
		$message->user_id =	$user->id;

		$message->save();

		$data = array('email' => $user->email, 'username' => $user->username);
		Mailgun::send('mail.letushelp', $data, function($message) use ($data) {
			$message->to($data['email'], strtolower(trim($data['username'])));
			$message->subject('CalculatieTool.com - Bedankt');
			// $message->bcc('info@calculatietool.com', 'CalculatieTool.com');
			$message->from('info@calculatietool.com', 'CalculatieTool.com');
			$message->replyTo('info@calculatietool.com', 'CalculatieTool.com');
		});

		Audit::CreateEvent('auth.activate.success', 'Activated with: ' . \Calctool::remoteAgent(), $user->id);

		Auth::login($user);

		return redirect('/?nstep=intro')->withCookie(cookie('_xintr'.$user->id, '1', 60*24*7));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Route
	 */
	public function doBlockPassword(Request $request)
	{
		$this->validate($request, [
			'email' => array('required','max:80','email')
		]);

		$user = User::where('email','=',$request->get('email'))->first();
		if (!$user)
			return redirect('login')->with('success', 1);
		$user->secret = Hash::make(mt_rand());
		$user->active = false;
		$user->api = md5(mt_rand());

		$data = array('email' => $user->email, 'api' => $user->api, 'token' => $user->token, 'username' => $user->username);
		Mailgun::send('mail.password', $data, function($message) use ($data) {
			$message->to($data['email'], strtolower(trim($data['username'])));
			$message->subject('CalculatieTool.com - Wachtwoord herstellen');
			$message->from('info@calculatietool.com', 'CalculatieTool.com');
			$message->replyTo('info@calculatietool.com', 'CalculatieTool.com');
		});

		$user->save();

		Audit::CreateEvent('auth.reset.password.mail.success', 'Reset with: ' . \Calctool::remoteAgent(), $user->id);

		return redirect('login')->with('success', 'Wachtwoord geblokkeerd');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Route
	 */
	public function doHideNextStep()
	{
		return Response::make(json_encode(['success' => 1]))->withCookie(cookie()->forget('nstep'));
	}


	public function doLogout()
	{
		Audit::CreateEvent('auth.logout.success', 'User destroyed current session');
		Auth::logout();
		return redirect('/login');
	}
}
