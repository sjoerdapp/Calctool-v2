<?php

namespace Calctool\Http\Controllers;

use Illuminate\Http\Request;

use \Calctool\Models\BankAccount;
use \Calctool\Models\Cashbook;

use \Auth;
//use \Cookie;

class CashbookController extends Controller {

	/**
	 * Display a listing of the resource.
	 * GET /relation
	 *
	 * @return Response
	 */

	public function doNewAccount(Request $request)
	{
		$this->validate($request, [
			'account' => array('required',),
			'account_name' => array('required'),
		]);

		$account = new BankAccount;
		$account->user_id = Auth::id();
		$account->account = $request->get('account');
		$account->account_name = $request->get('account_name');

		$account->save();
		
		$amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));
		if ($amount) {
			$book = new Cashbook;
			$book->account_id = $account->id;
			$book->amount = $amount;
			$book->payment_date = date('Y-m-d');
			$book->description = 'Initieel rekeningbedrag';

			$book->save();
		}

		return json_encode(['success' => 1]);
	}

	public function doNewCashRow(Request $request)
	{
		$this->validate($request, [
			'account' => array('required',),
			'amount' => array('required'),
			'date' => array('required'),
		]);

		$account = BankAccount::find($request->get('account'));
		if (!$account || !$account->isOwner()) {
			return json_encode(['success' => 0]);
		}

		$book = new Cashbook;
		$book->account_id = $account->id;
		$book->amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));
		$book->payment_date = date('Y-m-d',strtotime($request->get('date')));
		$book->description = $request->get('desc');

		$book->save();

		return json_encode(['success' => 1]);
	}

}
