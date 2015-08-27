<?php

/*
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Written by Marco Boretto <marco.bore@gmail.com>
*/

namespace Longman\TelegramBot\Commands;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Command;
use Longman\TelegramBot\Entities\Update;

class AuthCommand extends Command
{
	protected $name = 'auth';
	protected $description = 'Verbindt met CalculatieTool profiel';
	protected $usage = '/auth <API-key>';
	protected $version = '1.0.0';
	protected $enabled = true;
	protected $public = true;

	private $user;

	private function getAuthStatus($telid)
	{
		$userid = \Redis::get('auth:telegram:'.$telid);
		if ($userid) {
			$this->user = \User::find($userid);
			return true;
		} else {
			return false;
		}
	}

	public function execute()
	{
		$update = $this->getUpdate();
		$message = $this->getMessage();

		$chat_id = $message->getChat()->getId();
		$message_id = $message->getMessageId();
		$text = $message->getText(true);

		if ($this->getAuthStatus($message->getFrom()->getId())) {
			$text = 'Telegram is gekoppeld aan gebruiker \'' . $this->user->username . '\'. Gebruik /deauth om Telegram te ontkoppelen';
		} else {

			if (empty($text)) {
				$text = 'Geeft de API key op, deze is te vinden in Mijn Account';
			} else {
				$user = \User::where('api','=',$text)->first();
				if ($user) {
					\Redis::set('auth:'.$user->username.':telegram', true);
					\Redis::set('auth:telegram:'.$message->getFrom()->getId(), $user->id);
					$text = 'Beste, ' . $user->firstname . "\n";
					$text .= 'Telegram is verbonden met uw account';
				} else {
					$text = 'Ongeldige API key';
				}
			}

		}

		$data = array();
		$data['chat_id'] = $chat_id;
		$data['text'] = $text;

		$result = Request::sendMessage($data);
		return $result;
	}
}