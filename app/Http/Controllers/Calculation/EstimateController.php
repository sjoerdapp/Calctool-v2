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

use Exception;
use Carbon\Carbon;
use BynqIO\Dynq\Models\Project;
use BynqIO\Dynq\Models\Chapter;
use BynqIO\Dynq\Models\Part;
use BynqIO\Dynq\Models\PartType;
use BynqIO\Dynq\Models\Activity;
use BynqIO\Dynq\Models\Timesheet;
use BynqIO\Dynq\Models\EstimateLabor;
use BynqIO\Dynq\Models\EstimateMaterial;
use BynqIO\Dynq\Models\EstimateEquipment;
use BynqIO\Dynq\Http\Controllers\Controller;
use Illuminate\Http\Request;

//TODO: update $proj->update_estimate = date('Y-m-d');
class EstimateController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Default Home Controlluse BynqIO\Dynq\Models\Invoice
    |--------------------------------------------------------------------------
    |
    | You may wish to use controllers instead of, or in addition to, Closure
    | based routes. That's great! Here is an example controller method to
    | get you started. To route to this controller, just add the route:
    |
    |	Route::get('/', 'HomeController@showWelcome');
    |
    */

    private function profit($layer, $activity, $project) {
        if ($activity->isSubcontracting()) {
            switch ($layer) {
                case 'labor':
                    return 0;
                case 'material':
                    return $project->profit_calc_subcontr_mat;
                case 'other':
                    return $project->profit_calc_subcontr_equip;
            }
        } else {
            switch ($layer) {
                case 'labor':
                    return 0;
                case 'material':
                    return $project->profit_calc_contr_mat;
                case 'other':
                    return $project->profit_calc_contr_equip;
            }
        }
    }

    protected function newLaborRow($activity, Array $parameters)
    {
        $object = null;
        $project = $activity->chapter->project;

        $rate = $project->hour_rate;
        if ($activity->isSubcontracting() && isset($parameters['rate'])) { //TODO: remove?
            $rate = $parameters['rate'];
        }

        $object = EstimateLabor::create([
            "set_rate"        => $rate,
            "set_amount"      => $parameters['amount'],
            "activity_id"     => $activity->id,
            "original"        => false,
            "isset"           => true
        ]);

        return [
            'id'            => $object->id,
            'amount'        => $object->set_amount * $object->set_rate,
        ];
    }

    protected function newMaterialRow($activity, Array $parameters)
    {
        $object = null;
        $project = $activity->chapter->project;

        $object = EstimateMaterial::create([
            "set_material_name"  => $parameters['name'],
            "set_unit"           => $parameters['unit'],
            "set_rate"           => $parameters['rate'],
            "set_amount"         => $parameters['amount'],
            "activity_id"        => $activity->id,
            "original"           => false,
            "isset"              => true
        ]);

        return [
            'id'            => $object->id,
            'amount'        => $object->set_amount * $object->set_rate,
            'amount_incl'   => $object->set_amount * $object->set_rate * (($this->profit('material', $activity, $project) / 100) + 1),
        ];
    }

    protected function newOtherRow($activity, Array $parameters)
    {
        $object = null;
        $project = $activity->chapter->project;

        $object = EstimateEquipment::create([
            "set_equipment_name"  => $parameters['name'],
            "set_unit"            => $parameters['unit'],
            "set_rate"            => $parameters['rate'],
            "set_amount"          => $parameters['amount'],
            "activity_id"         => $activity->id,
            "original"            => false,
            "isset"               => true
        ]);

        return [
            'id'            => $object->id,
            'amount'        => $object->set_amount * $object->set_rate,
            'amount_incl'   => $object->set_amount * $object->set_rate * (($this->profit('other', $activity, $project) / 100) + 1),
        ];
    }

    protected function newTimesheetRow($activity, Array $parameters)
    {
        $object = null;
        $project = $activity->chapter->project;

        $timesheet = Timesheet::create([
            'register_date'      => Carbon::parse($parameters['date']),
            'register_hour'      => $parameters['amount'],
            'note'               => $parameters['name'],
            'activity_id'        => $activity->id,
            'timesheet_kind_id'  => 2
        ]);

        $object = EstimateLabor::create([
            "set_rate"     => $project->hour_rate,
            "set_amount"   => $parameters['amount'],
            "hour_id"      => $timesheet->id,
            "activity_id"  => $activity->id,
            "original"     => false,
            "isset"        => true
        ]);

        return [
            'id'            => $object->id,
            'amount'        => $object->set_amount * $object->set_rate,
        ];
    }

    public function new(Request $request)
    {
        $this->validate($request, [
            'name'      => ['required_unless:layer,labor', 'max:100'],
            'unit'      => ['required_unless:layer,labor,timesheet', 'max:10'],
            'rate'      => ['required_unless:layer,labor,timesheet', 'numeric'],
            'date'      => ['required_if:layer,timesheet'],
            'amount'    => ['required', 'numeric'],
            'activity'  => ['required', 'integer', 'min:0'],
            'layer'     => ['required'],
        ]);

        $object = null;

        $activity = Activity::findOrFail($request->get('activity'));

        switch ($request->get('layer')) {
            case 'labor':
                $object = $this->newLaborRow($activity, $request->all());
                break;
            case 'material':
                $object = $this->newMaterialRow($activity, $request->all());
                break;
            case 'other':
                $object = $this->newOtherRow($activity, $request->all());
                break;
            case 'timesheet':
                $object = $this->newTimesheetRow($activity, $request->all());
                break;
        }

        return response()->json(array_merge(['success' => true], $object));
    }

    protected function updateLaborRow($activity, Array $parameters)
    {
        $project = $activity->chapter->project;

        $rate = $project->hour_rate;
        if ($activity->isSubcontracting() && isset($parameters['rate'])) { //?
            $rate = $parameters['rate'];
        }

        $row = EstimateLabor::findOrFail($parameters['id']);
        $row->set_rate       = $rate;
        $row->set_amount     = $parameters['amount'];
        $row->isset          = true;
        $row->save();

        return [
            'id'            => $row->id,
            'amount'        => $row->set_amount * $row->set_rate,
        ];
    }

    protected function updateMaterialRow($activity, Array $parameters)
    {
        $project = $activity->chapter->project;

        $row = EstimateMaterial::findOrFail($parameters['id']);
        $row->set_material_name  = $parameters['name'];
        $row->set_unit           = $parameters['unit'];
        $row->set_rate           = $parameters['rate'];
        $row->set_amount         = $parameters['amount'];
        $row->isset              = true;
        $row->save();

        return [
            'id'            => $row->id,
            'amount'        => $row->set_amount * $row->set_rate,
            'amount_incl'   => $row->set_amount * $row->set_rate * (($this->profit('material', $activity, $project) / 100) + 1),
        ];
    }

    protected function updateOtherRow($activity, Array $parameters)
    {
        $project = $activity->chapter->project;

        $row = EstimateEquipment::findOrFail($parameters['id']);
        $row->set_equipment_name  = $parameters['name'];
        $row->set_unit            = $parameters['unit'];
        $row->set_rate            = $parameters['rate'];
        $row->set_amount          = $parameters['amount'];
        $row->isset               = true;
        $row->save();

        return [
            'id'            => $row->id,
            'amount'        => $row->set_amount * $row->set_rate,
            'amount_incl'   => $row->set_amount * $row->set_rate * (($this->profit('other', $activity, $project) / 100) + 1),
        ];
    }

    protected function updateTimesheetRow($activity, Array $parameters)
    {
        $project = $activity->chapter->project;

        $rate = $project->hour_rate;
        if ($activity->isSubcontracting() && isset($parameters['rate'])) { //?
            $rate = $parameters['rate'];
        }

        $row = EstimateLabor::findOrFail($parameters['id']);
        $row->set_rate       = $rate;
        $row->set_amount     = $parameters['amount'];
        $row->save();

        $timesheet = Timesheet::findOrFail($row->hour_id);
        $timesheet->register_date  = Carbon::parse($parameters['date']);
        $timesheet->register_hour  = $parameters['amount'];
        $timesheet->note           = $parameters['name'];
        $timesheet->save();

        return [
            'id'            => $row->id,
            'amount'        => $row->set_amount * $row->set_rate,
        ];
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'id'        => ['required', 'integer', 'min:0'],
            'name'      => ['required_unless:layer,labor', 'max:100'],
            'unit'      => ['required_unless:layer,labor,timesheet', 'max:10'],
            'rate'      => ['required_unless:layer,labor,timesheet', 'numeric'],
            'date'      => ['required_if:layer,timesheet'],
            'amount'    => ['required', 'numeric'],
            'activity'  => ['required', 'integer', 'min:0'],
            'layer'     => ['required'],
        ]);

        $object = null;

        $activity = Activity::findOrFail($request->get('activity'));

        switch ($request->get('layer')) {
            case 'labor':
                $object = $this->updateLaborRow($activity, $request->all());
                break;
            case 'material':
                $object = $this->updateMaterialRow($activity, $request->all());
                break;
            case 'other':
                $object = $this->updateOtherRow($activity, $request->all());
                break;
            case 'timesheet':
                $object = $this->updateTimesheetRow($activity, $request->all());
                break;
        }

        return response()->json(array_merge(['success' => true], $object));
    }

    protected function deleteMaterialRow(Array $parameters)
    {
        $row = EstimateMaterial::findOrFail($parameters['id']);
        if ($row->isOriginal()) {
            throw new Exception('not allowed');
        }

        $row->delete();
    }

    protected function deleteOtherRow(Array $parameters)
    {
        $row = EstimateEquipment::findOrFail($parameters['id']);
        if ($row->isOriginal()) {
            throw new Exception('not allowed');
        }

        $row->delete();
    }

    protected function deleteTimesheetRow(Array $parameters)
    {
        //TODO: check for isOriginal() ?
        $row = EstimateLabor::findOrFail($parameters['id']);
        $timesheet = Timesheet::findOrFail($row->hour_id);
        $timesheet->delete();
        $row->delete();
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id'        => ['required', 'integer', 'min:0'],
            'activity'  => ['required', 'integer', 'min:0'],
            'layer'     => ['required'],
        ]);

        try {
            switch ($request->get('layer')) {
                case 'material':
                    $this->deleteMaterialRow($request->all());
                    break;
                case 'other':
                    $this->deleteOtherRow($request->all());
                    break;
                case 'timesheet':
                    $this->deleteTimesheetRow($request->all());
                    break;
            }
        } catch (Exception $e) {
            return response()->json(['success' => 0]);
        }

        return response()->json(['success' => 1]);
    }

    protected function resetLaborRow(Array $parameters)
    {
        $row = EstimateLabor::findOrFail($parameters['id']);
        if (!$row->isOriginal()) {
            throw new Exception('not allowed');
        }

        $row->set_rate    = null;
        $row->set_amount  = null;
        $row->isset       = false;
        $row->save();

        return $row;
    }

    protected function resetMaterialRow(Array $parameters)
    {
        $row = EstimateMaterial::findOrFail($parameters['id']);
        if (!$row->isOriginal()) {
            throw new Exception('not allowed');
        }

        $row->set_material_name  = null;
        $row->set_unit           = null;
        $row->set_rate           = null;
        $row->set_amount         = null;
        $row->isset              = false;
        $row->save();

        return $row;
    }

    protected function resetOtherRow(Array $parameters)
    {
        $row = EstimateEquipment::findOrFail($parameters['id']);
        if (!$row->isOriginal()) {
            throw new Exception('not allowed');
        }

        $row->set_equipment_name  = null;
        $row->set_unit            = null;
        $row->set_rate            = null;
        $row->set_amount          = null;
        $row->isset               = false;
        $row->save();

        return $row;
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'id'        => ['required', 'integer', 'min:0'],
            'activity'  => ['required', 'integer', 'min:0'],
            'layer'     => ['required'],
        ]);

        $original = [];

        try {
            switch ($request->get('layer')) {
                case 'labor':
                    $object = $this->resetLaborRow($request->all());
                    $original['rate'] = $object->rate;
                    $original['amount'] = $object->amount;
                    break;
                case 'material':
                    $object = $this->resetMaterialRow($request->all());
                    $original['name'] = $object->material_name;
                    $original['unit'] = $object->unit;
                    $original['rate'] = $object->rate;
                    $original['amount'] = $object->amount;
                    break;
                case 'other':
                    $object = $this->resetOtherRow($request->all());
                    $original['name'] = $object->equipment_name;
                    $original['unit'] = $object->unit;
                    $original['rate'] = $object->rate;
                    $original['amount'] = $object->amount;
                    break;
            }
        } catch (Exception $e) {
            return response()->json(['success' => 0]);
        }

        return response()->json(array_merge(['success' => 1], $original));
    }

    //TODO; placed here fow now
    public function asyncSummary($projectid)
    {
        $project = Project::find($projectid);

        return view('component.estimate.summary', ['project' => $project, 'section' => 'summary', 'filter' => function($section, $builder) {
            return $builder->whereNull('detail_id')
                           ->where('part_type_id', PartType::where('type_name','estimate')->firstOrFail()->id)
                           ->orderBy('priority');
        }]);
    }

    //TODO; placed here fow now
    public function asyncEndresult($projectid)
    {
        $project = Project::find($projectid);
        return view('component.estimate.endresult', ['project' => $project, 'section' => 'summary']);
    }
}
