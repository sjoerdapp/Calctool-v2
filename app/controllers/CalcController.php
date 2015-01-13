<?php

class CalcController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function getCalculation()
	{
		return View::make('calc.calculation');
	}


	public function getEstimate()
	{
		return View::make('calc.estimate');
	}

	public function getLess()
	{
		return View::make('calc.less');
	}

	public function getMore()
	{
		return View::make('calc.more');
	}

	public function doNewChapter()
	{
		$rules = array(
			'chapter' => array('required','max:50'),
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {

			return Redirect::back()->withErrors($validator)->withInput(Input::all());
		} else {

			$chapter = new Chapter;
			$chapter->chapter_name = Input::get('chapter');
			$chapter->priority = 0;
			$chapter->project_id = Route::Input('project_id');

			$chapter->save();

			return Redirect::back()->with('success', 1);
		}

	}

	public function doNewActivity()
	{
		$rules = array(
			'activity' => array('required','max:50'),
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {

			return Redirect::back()->withErrors($validator)->withInput(Input::all());
		} else {

			$part = Part::where('part_name','=','contracting')->first();
			$part_type = PartType::where('type_name','=','calculation')->first();

			$activity = new Activity;
			$activity->activity_name = Input::get('activity');
			$activity->priority = 0;
			$activity->chapter_id = Route::Input('chapter_id');
			$activity->part_id = $part->id;
			$activity->part_type_id = $part_type->id;

			$activity->save();

			return Redirect::back()->with('success', 1);

		}
	}

	public function doUpdatePart()
	{
		$rules = array(
			'value' => array('required','integer','min:0'),
			'activity' => array('required','integer','min:0')
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			$messages = $validator->messages();

			return json_encode(['success' => 0, 'message' => $messages]);
		} else {

			$activity = Activity::find(Input::get('activity'));
			$activity->part_id = Input::get('value');
			$activity->save();

			return json_encode(['success' => 1]);
		}
	}

	public function doUpdatePartType()
	{
		$rules = array(
			'value' => array('required','integer','min:0'),
			'activity' => array('required','integer','min:0')
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			$messages = $validator->messages();

			return json_encode(['success' => 0, 'message' => $messages]);
		} else {

			$activity = Activity::find(Input::get('activity'));
			$activity->part_type_id = Input::get('value');
			$activity->save();

			return json_encode(['success' => 1]);
		}
	}

		public function doNewMaterial()
	{
		$rules = array(
			'name' => array('required','alpha_dash','max:50'),
			'unit' => array('required','max:10'),
			'rate' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
			'amount' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
			'activity' => array('required','integer','min:0'),
			'tax' => array('required','integer')
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			$messages = $validator->messages();

			return json_encode(['success' => 0, 'message' => $messages]);
		} else {
			$material = CalculationMaterial::create(array(
				"material_name" => Input::get('name'),
				"unit" => Input::get('unit'),
				"rate" => str_replace(',', '.', str_replace('.', '' , Input::get('rate'))),
				"amount" => str_replace(',', '.', str_replace('.', '' , Input::get('amount'))),
				"tax_id" => Input::get('tax'),
				"activity_id" => Input::get('activity'),
			));

			return json_encode(['success' => 1, 'id' => $material->id]);
		}
	}

	public function doDeleteMaterial()
	{
		$rules = array(
			'id' => array('required','integer','min:0'),
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			$messages = $validator->messages();

			return json_encode(['success' => 0, 'message' => $messages]);
		} else {
			CalculationMaterial::destroy(Input::get('id'));

			return json_encode(['success' => 1]);
		}
	}

	public function doUpdateMaterial()
	{
		$rules = array(
			'id' => array('integer','min:0'),
			'name' => array('alpha_dash','max:50'),
			'unit' => array('max:10'),
			'rate' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
			'amount' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
			'tax' => array('integer')
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			$messages = $validator->messages();

			return json_encode(['success' => 0, 'message' => $messages]);
		} else {

			$material = CalculationMaterial::find(Input::get('id'));
			$material->material_name = Input::get('name');
			$material->unit = Input::get('unit');
			$material->rate = str_replace(',', '.', str_replace('.', '' , Input::get('rate')));
			$material->amount = str_replace(',', '.', str_replace('.', '' , Input::get('amount')));
			$material->tax_id = Input::get('tax');

			$material->save();

			return json_encode(['success' => 1]);

		}
	}
}
