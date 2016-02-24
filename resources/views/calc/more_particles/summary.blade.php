<?php

use \Calctool\Models\Chapter;
use \Calctool\Models\Activity;
use \Calctool\Models\Detail;
use \Calctool\Models\Part;
use \Calctool\Calculus\MoreOverview;
?>
<div>

	<div>
		<label>Aanneming</label>
		<div class="toggle-content">

			<table class="table table-striped">
				<thead>
					<tr>
						<th class="col-md-3">Hoofdstuk</th>
						<th class="col-md-4">Werkzaamheden</th>
						<th class="col-md-1"><span class="pull-right">Arbeidsuren</th>
						<th class="col-md-1"><span class="pull-right">Arbeid</th>
						<th class="col-md-1"><span class="pull-right">Materiaal</th>
						<th class="col-md-1"><span class="pull-right">Materieel</th>
						<th class="col-md-1"><span class="pull-right">Totaal</th>
					</tr>
				</thead>

				<tbody>
					@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
					<?php $i = 0; ?>
					@foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','contracting')->first()->id)->where('detail_id','=',Detail::where('detail_name','=','more')->first()->id)->orderBy('created_at')->get() as $activity)
					<?php $i++; ?>
					<tr>
						<td class="col-md-3">{{ $i==1 ? $chapter->chapter_name : '' }}</td>
						<td class="col-md-4">{{ $activity->activity_name }}</td>
						<td class="col-md-1"><span class="pull-right">{{ number_format(MoreOverview::laborTotal($activity), 2, ",",".") }}</td>
						<td class="col-md-1"><span class="pull-right total-ex-tax">{{ '&euro; '.number_format(MoreOverview::laborActivity($activity), 2, ",",".") }}</span></td>
						<td class="col-md-1"><span class="pull-right total-ex-tax">{{ '&euro; '.number_format(MoreOverview::materialActivityProfit($activity, $project->profit_more_contr_mat), 2, ",",".") }}</span></td>
						<td class="col-md-1"><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::equipmentActivityProfit($activity, $project->profit_more_contr_equip), 2, ",",".") }}</span></td>
						<td class="col-md-1"><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::activityTotalProfit($activity, $project->profit_more_contr_mat, $project->profit_more_contr_equip), 2, ",",".") }} </td>
					</tr>
					@endforeach
					@endforeach
					<tr>
						<th class="col-md-3"><strong>Totaal Aanneming</strong></th>
						<th class="col-md-4">&nbsp;</th>
						<td class="col-md-1"><strong><span class="pull-right">{{ number_format(MoreOverview::contrLaborTotalAmount($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::contrLaborTotal($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::contrMaterialTotal($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::contrEquipmentTotal($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::contrTotal($project), 2, ",",".") }}</span></strong></td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>

	<div>
		<label>Onderaanneming</label>
		<div class="toggle-content">

			<table class="table table-striped">
				<thead>
					<tr>
						<th class="col-md-3">Hoofdstuk</th>
						<th class="col-md-4">Werkzaamheden</th>
						<th class="col-md-1"><span class="pull-right">Arbeidsuren</th>
						<th class="col-md-1"><span class="pull-right">Arbeid</th>
						<th class="col-md-1"><span class="pull-right">Materiaal</th>
						<th class="col-md-1"><span class="pull-right">Materieel</th>
						<th class="col-md-1"><span class="pull-right">Totaal</th>
					</tr>
				</thead>

				<tbody>
					@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
					<?php $i = 0; ?>
					@foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','subcontracting')->first()->id)->where('detail_id','=',Detail::where('detail_name','=','more')->first()->id)->orderBy('created_at')->get() as $activity)
					<?php $i++; ?>
					<tr>
						<td class="col-md-3">{{ $i==1 ? $chapter->chapter_name : ''}}</td>
						<td class="col-md-4">{{ $activity->activity_name }}</td>
						<td class="col-md-1"><span class="pull-right">{{ number_format(MoreOverview::laborTotal($activity), 2, ",",".") }}</td>
						<td class="col-md-1"><span class="pull-right total-ex-tax">{{ '&euro; '.number_format(MoreOverview::laborActivity($activity), 2, ",",".") }}</span></td>
						<td class="col-md-1"><span class="pull-right total-ex-tax">{{ '&euro; '.number_format(MoreOverview::materialActivityProfit($activity, $project->profit_more_subcontr_mat), 2, ",",".") }}</span></td>
						<td class="col-md-1"><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::equipmentActivityProfit($activity, $project->profit_more_subcontr_equip), 2, ",",".") }}</span></td>
						<td class="col-md-1"><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::activityTotalProfit($activity, $project->profit_more_subcontr_mat, $project->profit_more_subcontr_equip), 2, ",",".") }} </td>
					</tr>
					@endforeach
					@endforeach
					<tr>
						<th class="col-md-3"><strong>Totaal Onderaanneming</strong></th>
						<th class="col-md-4">&nbsp;</th>
						<td class="col-md-1"><strong><span class="pull-right">{{ number_format(MoreOverview::subcontrLaborTotalAmount($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::subcontrLaborTotal($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::subcontrMaterialTotal($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::subcontrEquipmentTotal($project), 2, ",",".") }}</span></strong></td>
						<td class="col-md-1"><strong><span class="pull-right">{{ '&euro; '.number_format(MoreOverview::subcontrTotal($project), 2, ",",".") }}</span></strong></td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>

	<div>
		<label>Totalen project</label>
		<div class="toggle-content">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="col-md-3">&nbsp;</th>
						<th class="col-md-4">&nbsp;</th>
						<th class="col-md-1"><span class="pull-right">Arbeidsuren</span></th>
						<th class="col-md-1"><span class="pull-right">Arbeid</span></th>
						<th class="col-md-1"><span class="pull-right">Materiaal</span></th>
						<th class="col-md-1"><span class="pull-right">Materieel</span></th>
						<th class="col-md-1"><span class="pull-right">Totaal</span></th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<th class="col-md-3">&nbsp;</th>
						<th class="col-md-4">&nbsp;</th>
						<td class="col-md-1"><span class="pull-right"><strong>{{ number_format(MoreOverview::laborSuperTotalAmount($project), 2, ",",".") }}</span></td>
						<td class="col-md-1"><span class="pull-right"><strong>{{ '&euro; '.number_format(MoreOverview::laborSuperTotal($project), 2, ",",".") }}</strong></span></td>
						<td class="col-md-1"><span class="pull-right"><strong>{{ '&euro; '.number_format(MoreOverview::materialSuperTotal($project), 2, ",",".") }}</strong></span></td>
						<td class="col-md-1"><span class="pull-right"><strong>{{ '&euro; '.number_format(MoreOverview::equipmentSuperTotal($project), 2, ",",".") }}</strong></span></td>
						<td class="col-md-1"><span class="pull-right"><strong>{{ '&euro; '.number_format(MoreOverview::superTotal($project), 2, ",",".") }}</strong></span></td>
					</tr>
				</tbody>
			</table>
			<h5><strong>Weergegeven bedragen zijn exclusief BTW</strong></h5>
		</div>
	</div>

</div>