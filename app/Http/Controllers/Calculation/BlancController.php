<?php

/**
 * Copyright (C) 2017 Bynq.io B.V.
 * All Rights Reserved
 *
 * This file is part of the Dynq project.
 *
 * Content can not be copied and/or distributed without the express
 * permission of the author.
 *
 * @package  Dynq
 * @author   Yorick de Wid <y.dewid@calculatietool.com>
 */

namespace BynqIO\Dynq\Http\Controllers\Calculation;

use BynqIO\Dynq\Models\Project;
use BynqIO\Dynq\Models\Chapter;
use BynqIO\Dynq\Models\BlancRow;
use BynqIO\Dynq\Models\Part;
use BynqIO\Dynq\Models\PartType;
use BynqIO\Dynq\Models\ProjectType;
use BynqIO\Dynq\Models\Tax;
use BynqIO\Dynq\Models\Activity;
use BynqIO\Dynq\Calculus\InvoiceTerm;
use BynqIO\Dynq\Calculus\ResultEndresult;
use BynqIO\Dynq\Calculus\CalculationRegister;
use BynqIO\Dynq\Models\CalculationMaterial;
use BynqIO\Dynq\Models\CalculationEquipment;
use BynqIO\Dynq\Models\CalculationLabor;
use BynqIO\Dynq\Models\EstimateLabor;
use BynqIO\Dynq\Models\EstimateMaterial;
use BynqIO\Dynq\Models\EstimateEquipment;
use BynqIO\Dynq\Models\Invoice;
use BynqIO\Dynq\Models\Offer;
use BynqIO\Dynq\Http\Controllers\Controller;
use Illuminate\Http\Request;

use \Auth;
use \PDF;

class BlancController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Default Home Controlluse BynqIO\Dynq\Models\Invoice;er
    |--------------------------------------------------------------------------
    |
    | You may wish to use controllers instead of, or in addition to, Closure
    | based routes. That's great! Here is an example controller method to
    | get you started. To route to this controller, just add the route:
    |
    |	Route::get('/', 'HomeController@showWelcome');
    |
    */

    public function getBlanc(Request $request, $projectid)
    {
        /*$project = Project::find($projectid);
        if ($project) {
            if ($project->project_close)
                return response()->view('calc.calculation_closed');
            $offer_last = Offer::where('project_id','=',$project->id)->orderBy('created_at', 'desc')->first();
            if ($offer_last && $offer_last->offer_finish)
                return response()->view('calc.calculation_closed');
        }
        return response()->view('calc.calculation');*/
        return view('calc.blanc_row');
    }

    public function doNewRow(Request $request)
    {
        $this->validate($request, [
            'name' => array('required','max:100'),
            'rate' => array('required','max:10'),
            'amount' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
        ]);

        $project = Project::find($request->input('project'));
        if (!$project->isOwner()) {
            return response()->json(['success' => 0]);
        }

        if ($project->tax_reverse) {
            $tax_id = 1;
        } else {
            $tax_id = $request->get('tax');
        }

        $row = BlancRow::create(array(
            "description" => $request->get('name'),
            "tax_id" => $tax_id,
            "rate" => str_replace(',', '.', str_replace('.', '' , $request->get('rate'))),
            "amount" => str_replace(',', '.', str_replace('.', '' , $request->get('amount'))),
            "project_id" => $request->input('project'),
        ));

        return response()->json(['success' => 1, 'id' => $row->id]);
    }

    public function doDeleteCalculationLabor(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer','min:0'),
        ]);

        $rec = CalculationLabor::find($request->input('id'));
        if (!$rec)
            return response()->json(['success' => 0]);
        $activity = Activity::find($rec->activity_id);
        if (!$activity)
            return response()->json(['success' => 0]);
        $chapter = Chapter::find($activity->chapter_id);
        if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
            return response()->json(['success' => 0]);
        }

        $rec->delete();

        return response()->json(['success' => 1]);
    }

    public function doDeleteCalculationMaterial(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer','min:0'),
        ]);

        $rec = CalculationMaterial::find($request->input('id'));
        if (!$rec)
            return response()->json(['success' => 0]);
        $activity = Activity::find($rec->activity_id);
        if (!$activity)
            return response()->json(['success' => 0]);
        $chapter = Chapter::find($activity->chapter_id);
        if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
            return response()->json(['success' => 0]);
        }

        $rec->delete();

        return response()->json(['success' => 1]);
    }

    public function doDeleteCalculationEquipment(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer','min:0'),
        ]);

        $rec = CalculationEquipment::find($request->input('id'));
        if (!$rec)
            return response()->json(['success' => 0]);
        $activity = Activity::find($rec->activity_id);
        if (!$activity)
            return response()->json(['success' => 0]);
        $chapter = Chapter::find($activity->chapter_id);
        if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
            return response()->json(['success' => 0]);
        }

        $rec->delete();

        return response()->json(['success' => 1]);
    }

    public function doUpdateRow(Request $request)
    {
        $this->validate($request, [
            'id' => array('integer','min:0'),
            'name' => array('max:100'),
            'rate' => array('max:10'),
            'amount' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/')
        ]);

        $project = Project::find($request->input('project'));
        if (!$project->isOwner()) {
            return response()->json(['success' => 0]);
        }

        $row = BlancRow::find($request->input('id'));
        if (!$row)
            return response()->json(['success' => 0]);
        if (!$row || !Project::find($row->project_id)->isOwner()) {
            return response()->json(['success' => 0]);
        }

        if ($project->tax_reverse) {
            $tax_id = 1;
        } else {
            $tax_id = $request->get('tax');
        }

        $row->description = $request->get('name');
        $row->tax_id = $tax_id;
        $row->rate = str_replace(',', '.', str_replace('.', '' , $request->get('rate')));
        $row->amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));

        $row->save();

        return response()->json(['success' => 1]);
    }

    public function doUpdateCalculationEquipment(Request $request)
    {
        $this->validate($request, [
            'id' => array('integer','min:0'),
            'name' => array('max:100'),
            'unit' => array('max:10'),
            'rate' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/')
        ]);

        $equipment = CalculationEquipment::find($request->input('id'));
        if (!$equipment)
            return response()->json(['success' => 0]);
        $activity = Activity::find($equipment->activity_id);
        if (!$activity)
            return response()->json(['success' => 0]);
        $chapter = Chapter::find($activity->chapter_id);
        if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
            return response()->json(['success' => 0]);
        }

        $equipment->equipment_name = $request->get('name');
        $equipment->unit = $request->get('unit');
        $equipment->rate = str_replace(',', '.', str_replace('.', '' , $request->get('rate')));
        $equipment->amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));

        $equipment->save();

        return response()->json(['success' => 1]);
    }

    public function doUpdateCalculationLabor(Request $request)
    {
        $this->validate($request, [
            'id' => array('integer','min:0'),
            'rate' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/')
        ]);

            $labor = CalculationLabor::find($request->input('id'));
            if (!$labor)
                return response()->json(['success' => 0]);
            $activity = Activity::find($labor->activity_id);
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $rate = $request->get('rate');
            if (empty($rate)) {
                $_labor = CalculationLabor::find($request->input('id'));
                $_activity = Activity::find($_labor->activity_id);
                $_chapter = Chapter::find($_activity->chapter_id);
                $_project = Project::find($_chapter->project_id);
                $rate = $_project->hour_rate;
            } else {
                $rate = str_replace(',', '.', str_replace('.', '' , $rate));
            }

            $labor->rate = $rate;
            $labor->amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));

            $labor->save();

            return response()->json(['success' => 1]);
    }

    public function doNewEstimateMaterial(Request $request)
    {
        $this->validate($request, [
            'name' => array('required','max:100'),
            'unit' => array('required','max:10'),
            'rate' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'activity' => array('required','integer','min:0')
        ]);

        $activity = Activity::find($request->input('activity'));
        if (!$activity)
            return response()->json(['success' => 0]);
        $chapter = Chapter::find($activity->chapter_id);
        if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
            return response()->json(['success' => 0]);
        }

        $material = EstimateMaterial::create(array(
            "material_name" => $request->get('name'),
            "unit" => $request->get('unit'),
            "rate" => str_replace(',', '.', str_replace('.', '' , $request->get('rate'))),
            "amount" => str_replace(',', '.', str_replace('.', '' , $request->get('amount'))),
            "activity_id" => $activity->id,
            "original" => true,
            "isset" => false
        ));

        return response()->json(['success' => 1, 'id' => $material->id]);
    }

    public function doNewEstimateEquipment(Request $request)
    {
        $this->validate($request, [
            'name' => array('required','max:100'),
            'unit' => array('required','max:10'),
            'rate' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'activity' => array('required','integer','min:0')
        ]);

        $activity = Activity::find($request->input('activity'));
        if (!$activity)
            return response()->json(['success' => 0]);
        $chapter = Chapter::find($activity->chapter_id);
        if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
            return response()->json(['success' => 0]);
        }

        $equipment = EstimateEquipment::create(array(
            "equipment_name" => $request->get('name'),
            "unit" => $request->get('unit'),
            "rate" => str_replace(',', '.', str_replace('.', '' , $request->get('rate'))),
            "amount" => str_replace(',', '.', str_replace('.', '' , $request->get('amount'))),
            "activity_id" => $activity->id,
            "original" => true,
            "isset" => false
        ));

        return response()->json(['success' => 1, 'id' => $equipment->id]);
    }

    public function doNewEstimateLabor(Request $request)
    {
        $this->validate($request, [
            'rate' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('required','regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'activity' => array('required','integer','min:0')
        ]);

            $activity = Activity::find($request->input('activity'));
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $rate = $request->get('rate');
            if (empty($rate)) {
                $_activity = Activity::find($request->input('activity'));
                $_chapter = Chapter::find($_activity->chapter_id);
                $_project = Project::find($_chapter->project_id);
                $rate = $_project->hour_rate;
            } else {
                $rate = str_replace(',', '.', str_replace('.', '' , $rate));
            }
            $labor = EstimateLabor::create(array(
                "rate" => $rate,
                "amount" => str_replace(',', '.', str_replace('.', '' , $request->get('amount'))),
                "activity_id" => $activity->id,
                "original" => true,
                "isset" => false
            ));

            return response()->json(['success' => 1, 'id' => $labor->id]);
    }

    public function doDeleteEstimateLabor(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer','min:0'),
        ]);

            $rec = EstimateLabor::find($request->input('id'));
            if (!$rec)
                return response()->json(['success' => 0]);
            $activity = Activity::find($rec->activity_id);
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $rec->delete();

            return response()->json(['success' => 1]);
    }

    public function doDeleteEstimateMaterial(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer','min:0'),
        ]);

            $rec = EstimateMaterial::find($request->input('id'));
            if (!$rec)
                return response()->json(['success' => 0]);
            $activity = Activity::find($rec->activity_id);
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $rec->delete();

            return response()->json(['success' => 1]);
    }

    public function doDeleteEstimateEquipment(Request $request)
    {
        $this->validate($request, [
            'id' => array('required','integer','min:0'),
        ]);

            $rec = EstimateEquipment::find($request->input('id'));
            if (!$rec)
                return response()->json(['success' => 0]);
            $activity = Activity::find($rec->activity_id);
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $rec->delete();

            return response()->json(['success' => 1]);
    }

    public function doUpdateEstimateMaterial(Request $request)
    {
        $this->validate($request, [
            'id' => array('integer','min:0'),
            'name' => array('max:100'),
            'unit' => array('max:10'),
            'rate' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/')
        ]);

            $material = EstimateMaterial::find($request->input('id'));
            if (!$material)
                return response()->json(['success' => 0]);
            $activity = Activity::find($material->activity_id);
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $material->material_name = $request->get('name');
            $material->unit = $request->get('unit');
            $material->rate = str_replace(',', '.', str_replace('.', '' , $request->get('rate')));
            $material->amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));

            $material->save();

            return response()->json(['success' => 1]);
    }

    public function doUpdateEstimateEquipment(Request $request)
    {
        $this->validate($request, [
            'id' => array('integer','min:0'),
            'name' => array('max:100'),
            'unit' => array('max:10'),
            'rate' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/')
        ]);

            $equipment = EstimateEquipment::find($request->input('id'));
            if (!$equipment)
                return response()->json(['success' => 0]);
            $activity = Activity::find($equipment->activity_id);
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $equipment->equipment_name = $request->get('name');
            $equipment->unit = $request->get('unit');
            $equipment->rate = str_replace(',', '.', str_replace('.', '' , $request->get('rate')));
            $equipment->amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));

            $equipment->save();

            return response()->json(['success' => 1]);
    }

    public function doUpdateEstimateLabor(Request $request)
    {
        $this->validate($request, [
            'id' => array('integer','min:0'),
            'rate' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/'),
            'amount' => array('regex:/^([0-9]+.?)?[0-9]+[.,]?[0-9]*$/')
        ]);

            $labor = EstimateLabor::find($request->input('id'));
            if (!$labor)
                return response()->json(['success' => 0]);
            $activity = Activity::find($labor->activity_id);
            if (!$activity)
                return response()->json(['success' => 0]);
            $chapter = Chapter::find($activity->chapter_id);
            if (!$chapter || !Project::find($chapter->project_id)->isOwner()) {
                return response()->json(['success' => 0]);
            }

            $rate = $request->get('rate');
            if (empty($rate)) {
                $_labor = EstimateLabor::find($request->input('id'));
                $_activity = Activity::find($_labor->activity_id);
                $_chapter = Chapter::find($_activity->chapter_id);
                $_project = Project::find($_chapter->project_id);
                $rate = $_project->hour_rate;
            } else {
                $rate = str_replace(',', '.', str_replace('.', '' , $rate));
            }

            $labor->rate = $rate;
            $labor->amount = str_replace(',', '.', str_replace('.', '' , $request->get('amount')));

            $labor->save();

            return response()->json(['success' => 1]);
    }
}
