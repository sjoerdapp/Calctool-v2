<?php

use \Calctool\Models\ProjectType;
use \Calctool\Calculus\MoreEndresult;

$type = ProjectType::find($project->type_id);
?>
<h4>Aanneming</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th class="col-md-4">&nbsp;</th>
			<th class="col-md-1">Uren</th>
			<th class="col-md-2">Bedrag (excl. BTW)</th>
			<th class="col-md-1">&nbsp;</th>
			<th class="col-md-1">BTW</th>
			<th class="col-md-2">BTW bedrag</th>
			<th class="col-md-1">&nbsp;</th>
		</tr>
	</thead>

	<tbody>
		@if (!$project->tax_reverse)
		<tr>
			<td class="col-md-4">Arbeidskosten</td>
			<td class="col-md-1">{{ number_format(MoreEndresult::conCalcLaborActivityTax1($project), 2, ",",".") }}</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcLaborActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">21%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcLaborActivityTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-4">&nbsp;</td>
			<td class="col-md-1">{{ number_format(MoreEndresult::conCalcLaborActivityTax2($project), 2, ",",".") }}</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcLaborActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">6%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcLaborActivityTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@else
		<tr>
			<td class="col-md-4">Arbeidskosten</td>
			<td class="col-md-1">{{ number_format(MoreEndresult::conCalcLaborActivityTax3($project), 2, ",",".") }}</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcLaborActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">0%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@endif

		@if (!$project->tax_reverse)
		<tr>
			<td class="col-md-4">Materiaalkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcMaterialActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">21%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcMaterialActivityTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-4">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcMaterialActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">6%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcMaterialActivityTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@else
		<tr>
			<td class="col-md-4">Materiaalkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcMaterialActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">0%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@endif

		@if (!$project->tax_reverse)
		<tr>
			<td class="col-md-4">Materieelkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcEquipmentActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">21%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcEquipmentActivityTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-4">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcEquipmentActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">6%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcEquipmentActivityTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@else
		<tr>
			<td class="col-md-4">Materieelkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::conCalcEquipmentActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">0%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@endif

		<tr>
			<td class="col-md-4"><strong>Totaal Aanneming </strong></td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2"><strong>{{ '&euro; '.number_format(MoreEndresult::totalContracting($project), 2, ",",".") }}</strong></td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2"><strong>{{ '&euro; '.number_format(MoreEndresult::totalContractingTax($project), 2, ",",".") }}</strong></td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
	</tbody>
</table>

<h4>Onderaanneming</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th class="col-md-4">&nbsp;</th>
			<th class="col-md-1">Uren</th>
			<th class="col-md-2">Bedrag (excl. BTW)</th>
			<th class="col-md-1">&nbsp;</th>
			<th class="col-md-1">BTW</th>
			<th class="col-md-2">BTW bedrag</th>
			<th class="col-md-1">&nbsp;</th>
		</tr>
	</thead>

	<tbody>
		@if (!$project->tax_reverse)
		<tr>
			<td class="col-md-4">Arbeidskosten</td>
			<td class="col-md-1">{{ number_format(MoreEndresult::subconCalcLaborActivityTax1($project), 2, ",",".") }}</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcLaborActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">21%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcLaborActivityTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-4">&nbsp;</td>
			<td class="col-md-1">{{ number_format(MoreEndresult::subconCalcLaborActivityTax2($project), 2, ",",".") }}</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcLaborActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">6%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcLaborActivityTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@else
		<tr>
			<td class="col-md-4">Arbeidskosten</td>
			<td class="col-md-1">{{ number_format(MoreEndresult::subconCalcLaborActivityTax3($project), 2, ",",".") }}</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcLaborActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">0%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@endif

		@if (!$project->tax_reverse)
		<tr>
			<td class="col-md-4">Materiaalkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcMaterialActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">21%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcMaterialActivityTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-4">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcMaterialActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">6%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcMaterialActivityTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@else
		<tr>
			<td class="col-md-4">Materiaalkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcMaterialActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">0%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@endif

		@if (!$project->tax_reverse)
		<tr>
			<td class="col-md-4">Materieelkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcEquipmentActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">21%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcEquipmentActivityTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-4">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcEquipmentActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">6%</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcEquipmentActivityTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@else
		<tr>
			<td class="col-md-4">Materieelkosten</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::subconCalcEquipmentActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">0%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
		@endif

		<tr>
			<td class="col-md-4"><strong>Totaal Onderaanneming </strong></td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2"><strong>{{ '&euro; '.number_format(MoreEndresult::totalSubcontracting($project), 2, ",",".") }}</strong></td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2"><strong>{{ '&euro; '.number_format(MoreEndresult::totalSubcontractingTax($project), 2, ",",".") }}</strong></td>
			<td class="col-md-1">&nbsp;</td>
		</tr>
	</tbody>
</table>

<h4>Totalen {{ $type->type_name == 'regie' ? 'Project' : 'Meerwerk' }}</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th class="col-md-5">&nbsp;</th>
			<th class="col-md-2">Bedrag (excl. BTW)</th>
			<th class="col-md-1">&nbsp;</th>
			<th class="col-md-1">&nbsp;</th>
			<th class="col-md-1">BTW bedrag</th>
			<th class="col-md-2"><span class="pull-right">Bedrag (incl. BTW)</span></th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td class="col-md-5"><strong>Calculatief te factureren meerwerk (excl. BTW)<strong></td>
			<td class="col-md-2">{{ '&euro; '.number_format(MoreEndresult::totalProject($project), 2, ",",".") }}</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2">&nbsp;</td>
		</tr>
		@if (!$project->tax_reverse)
		<tr>
			<td class="col-md-5">BTW bedrag aanneming 21%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">{{ '&euro; '.number_format(MoreEndresult::totalContractingTax1($project), 2, ",",".") }}</td>
			<td class="col-md-2">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-5">BTW bedrag aanneming 6%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">{{ '&euro; '.number_format(MoreEndresult::totalContractingTax2($project), 2, ",",".") }}</td>
			<td class="col-md-2">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-5">BTW bedrag onderaanneming 21%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">{{ '&euro; '.number_format(MoreEndresult::totalSubcontractingTax1($project), 2, ",",".") }}</td>
			<td class="col-md-2">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-5">BTW bedrag onderaanneming 6%</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">{{ '&euro; '.number_format(MoreEndresult::totalSubcontractingTax2($project), 2, ",",".") }}</td>
			<td class="col-md-2">&nbsp;</td>
		</tr>
		@endif
		<tr>
			<td class="col-md-5">Te factureren BTW bedrag</td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">{{ '&euro; '.number_format(MoreEndresult::totalProjectTax($project), 2, ",",".") }}</td>
			<td class="col-md-2">&nbsp;</td>
		</tr>
		<tr>
			<td class="col-md-5"><strong>Calculatief te factureren (Incl. BTW)</strong></td>
			<td class="col-md-2">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-1">&nbsp;</td>
			<td class="col-md-2"><strong class="pull-right">{{ '&euro; '.number_format(MoreEndresult::superTotalProject($project), 2, ",",".") }}</strong></td>

		</tr>

	</tbody>

</table>