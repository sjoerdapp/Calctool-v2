<?php

namespace Calctool\Http\Controllers;

use Illuminate\Http\Request;

use \Calctool\Models\Relation;
use \Calctool\Models\RelationKind;
use \Calctool\Models\Contact;
use \Calctool\Models\Iban;

use \Auth;
use \Cookie;

class QuickstartController extends Controller {

	/**
	 * Display a listing of the resource.
	 * GET /relation
	 *
	 * @return Response
	 */

	public function getNewContact()
	{
		return view('user.new_contact');
	}

	public function getMyCompany()
	{
		return view('user.edit_mycompany');
	}

	public function doNewMyCompanyQuickstart(Request $request)
	{
		$this->validate($request, [
			/* Company */
			'company_type' => array('required_if:relationkind,zakelijk','numeric'),
			'company_name' => array('required_if:relationkind,zakelijk','max:50'),
			'kvk' => array('numeric','min:8'),
			'btw' => array('alpha_num','min:14'),
			'street' => array('required','alpha','max:60'),
			'address_number' => array('required','alpha_num','max:5'),
			'zipcode' => array('required','size:6'),
			'city' => array('required','alpha_num','max:35'),
			'province' => array('required','numeric'),
			'country' => array('required','numeric'),
			/* Contacty */
			'contact_name' => array('required','max:50'),
			'contact_firstname' => array('required','max:30'),
			'email' => array('required','email','max:80'),
			'contactfunction' => array('required','numeric'),
		]);

		/* General */
		$relation = new Relation;
		$relation->user_id = Auth::id();
		$relation->note = $request->get('note');
		$relation->debtor_code = mt_rand(1000000, 9999999);

		/* Company */
		$relation->kind_id = RelationKind::where('kind_name','=','zakelijk')->first()->id;
		$relation->company_name = $request->get('company_name');
		$relation->type_id = $request->get('company_type');
		$relation->kvk = $request->get('kvk');
		$relation->btw = $request->get('btw');
		$relation->email = $request->get('email');

		/* Adress */
		$relation->address_street = $request->get('street');
		$relation->address_number = $request->get('address_number');
		$relation->address_postal = $request->get('zipcode');
		$relation->address_city = $request->get('city');
		$relation->province_id = $request->get('province');
		$relation->country_id = $request->get('country');

		$relation->save();

		$contact = new Contact;
		$contact->firstname = $request->get('contact_firstname');
		$contact->lastname = $request->get('contact_name');
		$contact->mobile = $request->get('mobile');
		$contact->email = $request->get('email');
		$contact->relation_id = $relation->id;
		$contact->function_id = $request->get('contactfunction');

		$contact->save();

		$user = Auth::user();
		$user->self_id = $relation->id;
		$user->save();

		return redirect('/')->with('success', 1)->withCookie(cookie()->forget('nstep'))->withCookie(cookie()->forever('_stxs'.$user->id, 1));
	}

}
