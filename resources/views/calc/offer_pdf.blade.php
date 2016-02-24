<?php

use \Calctool\Models\Project;
use \Calctool\Models\Relation;
use \Calctool\Models\Contact;
use \Calctool\Models\Offer;
use \Calctool\Models\Resource;
use \Calctool\Models\ProjectType;
use \Calctool\Calculus\CalculationEndresult;
use \Calctool\Http\Controllers\OfferController;
use \Calctool\Models\DeliverTime;
use \Calctool\Models\Valid;
use \Calctool\Models\Chapter;
use \Calctool\Models\Tax;
use \Calctool\Models\BlancRow;
use \Calctool\Models\Activity;
use \Calctool\Models\Part;
use \Calctool\Models\PartType;
use \Calctool\Calculus\CalculationOverview;
use \Calctool\Calculus\BlancRowsEndresult;

$c=false;

$project = Project::find($offer->project_id);
if (!$project || !$project->isOwner()) {
  exit();
}
$relation = Relation::find($project->client_id);
$relation_self = Relation::find(Auth::user()->self_id);
if ($relation_self)
   $contact_self = Contact::where('relation_id','=',$relation_self->id);

$include_tax = $offer->include_tax; //BTW bedragen weergeven
$only_totals = $offer->only_totals; //Alleen het totale offertebedrag weergeven
$seperate_subcon = !$offer->seperate_subcon; //Onderaanneming apart weergeven
$display_worktotals = $offer->display_worktotals; //Kosten werkzaamheden weergeven
$display_specification = $offer->display_specification; //Hoofdstukken en werkzaamheden weergeven
$display_description = $offer->display_description;  //Omschrijving werkzaamheden weergeven

$type = ProjectType::find($project->type_id);

function invoice_condition($offer) {
	if ($offer && $offer->invoice_quantity > 1) {
		if ($offer && $offer->downpayment) {
			echo "Indien opdracht gegund wordt, ontvangt u " . $offer->invoice_quantity . " termijnen waarvan de eerste termijn een aanbetaling betreft á &euro; " . number_format($offer->downpayment_amount, 2, ",",".");
		} else {
			echo "Indien opdracht gegund wordt, ontvangt u " . $offer->invoice_quantity . " termijnen waarvan de laatste een eindfactuur.";
		}
	} else {
		echo "Indien opdracht gegund wordt, ontvangt u één eindfactuur.";
	}
} 


?>

<!DOCTYPE html>
<html lang="en">
  <head>
	 <meta charset="utf-8">
	<title>Offerte</title>
	<link rel="stylesheet" href="{{ asset('css/pdf.css') }}" media="all" />
   </head>
  <body>

	<header class="clearfix">
	  	<div id="heading" class="clearfix">
			<table border="0" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td style="width: 345px">
							<div id="logo">
								<?php if ($relation_self && $relation_self->logo_id) echo "<img src=\"".asset(Resource::find($relation_self->logo_id)->file_location)."\"/>"; ?>
							</div>
						</td>
						<td style="width: 100px">
							<div><h3 class="name">{{ $relation_self->company_name }}</h3></div>
							<div><strong>Adres:</strong></div>
							<div><strong>&nbsp;</strong></div>
							<div><strong>Telefoon:</strong></div>
							<div><strong>E-mail:</strong></div>
							<div><strong>KVK:</strong></div>
						</td>
						<td style="width: 150px">
							<div>&nbsp;</div>
							<div>{{ $relation_self->address_street . ' ' . $relation_self->address_number }}</div>
							<div>{{ $relation_self->address_postal . ', ' . $relation_self->address_city }}</div>
							<div>{{ $relation_self->phone }}</div>
							<div>{{ $relation_self->email }}</div>
							<div>{{ $relation_self->kvk }}</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</header>
	
	<main>
	   	<div id="heading" class="clearfix">
		<table border="0" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td style="width: 345px">{{ $relation->company_name }}</td>
					<td style="width: 100px"><strong>Nummer:</strong></td>
					<td style="width: 150px">{{ OfferController::getOfferCode($project->id) }}</td>
				</tr>
				<tr>
					<td style="width: 345px">T.a.v. {{ Contact::find($offer->to_contact_id)->getFormalName() }}</td>
					<td style="width: 100px"><strong>Projectnaam:</strong></td>
					<td style="width: 150px">{{ $project->project_name }}</td>
				</tr>
				<tr>
					<td style="width: 345px">{{ $relation->address_street . ' ' . $relation->address_number }}</td>
					<td style="width: 100px"><strong>Datum:</strong></td>
					<td style="width: 150px">{{ date("j M Y", strtotime($offer->offer_make)) }}</td>
				</tr>
				<tr>
					<td style="width: 345px">{{ $relation->address_postal . ', ' . $relation->address_city }}</td>
					<td style="width: 100px"><strong>Versie:</strong></td>
					<td style="width: 150px">{{ Offer::where('project_id', $project->id)->count() }}</td>
				</tr>
			</tbody>
		</table>
		</div>
		<div id="spacing"></div>

		<div class="openingtext">Geachte {{ Contact::find($offer->to_contact_id)->getFormalName() }},</div>
		<div class="openingtext">{{ ($offer ? $offer->description : '') }}</div>






@if (!$only_totals)
	  <h1 class="name">Specificatie offerte</h1>
	  <hr>
	  @if($type->type_name == 'blanco offerte & factuur')
	  <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 147px" align="left" class="qty">Omschrijving</th>
			<th style="width: 60px" align="left" class="qty">€ / Eenh (excl. BTW)</th>
			<th style="width: 119px" align="left" class="qty">Aantal</th>
			<th style="width: 70px" align="left" class="qty">Totaal</th>
			<th style="width: 80px" align="left" class="qty">BTW</th>
			<th style="width: 119px" align="left" class="qty">BTW bedrag</th>
		  </tr>
		</thead>
		<tbody>
		  @foreach (BlancRow::where('project_id','=', $project->id)->get() as $row)
		  <tr style="page-break-after: always;">
			<td class="qty">{{ $row->description }}</td>
			<td class="qty">{{ '&euro; '.number_format($row->rate, 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format($row->amount, 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format($row->rate * $row->amount, 2, ",",".") }}</td>
			<td class="qty">{{ Tax::find($row->tax_id)->tax_rate }}%</td>
			<td class="qty">{{ '&euro; '.number_format(($row->rate * $row->amount/100) * Tax::find($row->tax_id)->tax_rate, 2, ",",".") }}</td>
		  </tr>
		  @endforeach
		</tbody>
	  </table>
	  @else
	  <hr>
	  @if ($seperate_subcon)
	  <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 147px" align="left" class="qty">&nbsp;</th>
			<th style="width: 60px" align="left" class="qty">Uren</th>
			<th style="width: 119px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">BTW %</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">&nbsp;</th>
		  </tr>
		</thead>
		<tbody>
		@if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Arbeidskosten</td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::conCalcLaborActivityTax1($project)+CalculationEndresult::subconCalcLaborActivityTax1($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax1Amount($project)+CalculationEndresult::subconCalcLaborActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax1AmountTax($project)+CalculationEndresult::subconCalcLaborActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ ' '.number_format(CalculationEndresult::conCalcLaborActivityTax2($project)+CalculationEndresult::subconCalcLaborActivityTax2($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax2Amount($project)+CalculationEndresult::subconCalcLaborActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax2AmountTax($project)+CalculationEndresult::subconCalcLaborActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Arbeidskosten</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax3($project)+CalculationEndresult::subconCalcLaborActivityTax3($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax3Amount($project)+CalculationEndresult::conCalcLaborActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Materiaalkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax1Amount($project)+CalculationEndresult::conCalcMaterialActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax1AmountTax($project)+CalculationEndresult::subconCalcMaterialActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax2Amount($project)+CalculationEndresult::subconCalcMaterialActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax2AmountTax($project)+CalculationEndresult::subconCalcMaterialActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Materiaalkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax3Amount($project)+CalculationEndresult::subconCalcMaterialActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Materieelkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax1Amount($project)+CalculationEndresult::subconCalcEquipmentActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax1AmountTax($project)+CalculationEndresult::subconCalcEquipmentActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax2Amount($project)+CalculationEndresult::subconCalcEquipmentActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax2AmountTax($project)+CalculationEndresult::subconCalcEquipmentActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">strong>Materieelkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax3Amount($project)+CalculationEndresult::subconCalcEquipmentActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		</tbody>
	  </table>

	  @else

	 <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		   <tr style="page-break-after: always;">
			<th style="width: 147px" align="left" class="qty">AANNEMING</th>
			<th style="width: 60px" align="left" class="qty">Uren</th>
			<th style="width: 119px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">BTW %</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">&nbsp;</th>
		  </tr>
		</thead>

		<tbody>
		@if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Arbeidskosten</td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::conCalcLaborActivityTax1($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::conCalcLaborActivityTax2($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Arbeidskosten</td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::conCalcLaborActivityTax3($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Materiaalkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Materiaalkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Materieelkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Materieelkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		  <tr>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Totaal Aanneming </strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty"><strong>{{ '&euro; '.number_format(CalculationEndresult::totalContracting($project), 2, ",",".") }}</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">@if($include_tax) <strong>{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax($project), 2, ",",".") }}</strong> @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
	 </table>
	 <br>
	 <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 147px" align="left" class="qty">ONDERAANNEMING</th>
			<th style="width: 60px" align="left" class="qty">Uren</th>
			<th style="width: 120px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">BTW %</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">&nbsp;</th>
		  </tr>
		</thead>
		<tbody>
		@if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Arbeidskosten</td>
			<td class="qty">{{ ' '.number_format(CalculationEndresult::subconCalcLaborActivityTax1($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ ' '.number_format(CalculationEndresult::subconCalcLaborActivityTax2($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Arbeidskosten</td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::subconCalcLaborActivityTax3($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Materiaalkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Materiaalkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">Materieelkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty">Materieelkosten</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		  <tr>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Totaal Onderaanneming </strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty"><strong>{{ '&euro; '.number_format(CalculationEndresult::totalSubcontracting($project), 2, ",",".") }}</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">@if($include_tax) <strong>{{ '&euro; '.number_format(CalculationEndresult::totalSubcontractingTax($project), 2, ",",".") }}</strong> @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr>
			<td class="qty">&nbsp;</td>
		  </tr>
		</tbody>
	  </table>

@endif
@endif

	  <h1 class="name">Totalen offerte</h1>
	  <hr>
	  <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 207px" align="left" class="qty">&nbsp;</th>
			<th style="width: 119px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">&nbsp;</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">@if($include_tax) Bedrag (incl. BTW) @endif</th>
		  </tr>
		</thead>
		<tbody>
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Calculatief te offreren (excl. BTW)</strong></td>
			<td class="qty"><class="pull-right">{{ '&euro; '.number_format(CalculationEndresult::totalProject($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @if($include_tax)
		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">BTW bedrag 21%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax1($project)+CalculationEndresult::totalSubcontractingTax1($project)+BlancRowsEndresult::rowTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">BTW bedrag 6%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax2($project)+CalculationEndresult::totalSubcontractingTax2($project)+BlancRowsEndresult::rowTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Calculatief te offreren (Incl. BTW)</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty"><strong class="pull-right">{{ '&euro; '.number_format(CalculationEndresult::superTotalProject($project)+BlancRowsEndresult::rowTax1AmountTax($project)+BlancRowsEndresult::rowTax2AmountTax($project), 2, ",",".") }}</strong></td>
		  </tr>
		  @endif
		</tbody>
	  </table>
	  <br>
	  <h1 class="name">Bepalingen</h1>
	  <hr>
	  <div class="terms">
		<li>
			{{ invoice_condition($offer) }}
		</li>
		<li>
		 @if (DeliverTime::find($offer->deliver_id)->delivertime_name == "per direct" || DeliverTime::find($offer->deliver_id)->delivertime_name == "in overleg")
		  Wij kunnen de werkzaamheden
		  {{ DeliverTime::find($offer->deliver_id)->delivertime_name }}
		  starten na uw opdrachtbevestiging.
		@else
		  Wij kunnen de werkzaamheden starten binnen
		  {{ DeliverTime::find($offer->deliver_id)->delivertime_name }}
		  na uw opdrachtbevestiging.
		@endif</li>
		<li>Deze offerte is geldig tot {{ Valid::find($offer->valid_id)->valid_name }} na dagtekening.</li>
		@if($offer->extracondition)
		<li>{{ $offer->extracondition }}</li>
		@endif
	  </div>
	 
	  <div class="closingtext">{{ ($offer ? $offer->closure : '') }}</div>

	  <div class="from">Met vriendelijke groet,</div>
	  <div class="from">{{ Contact::find($offer->from_contact_id)->firstname ." ". Contact::find($offer->from_contact_id)->lastname }}</div>
	</main>

	@else

@if (!$only_totals)
	 <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <h4 class="name">Aanneming</h4>
		  <tr style="page-break-after: always;">
			<th style="width: 147px" align="left" class="qty">&nbsp;</th>
			<th style="width: 60px" align="left" class="qty">Uren</th>
			<th style="width: 119px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">BTW %</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">&nbsp;</th>
		  </tr>
		</thead>

		<tbody>
		@if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Arbeidskosten</strong></td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::conCalcLaborActivityTax1($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::conCalcLaborActivityTax2($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Arbeidskosten</strong></td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::conCalcLaborActivityTax3($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcLaborActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materiaalkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materiaalkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcMaterialActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materieelkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materieelkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::conCalcEquipmentActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		  <tr>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Totaal Aanneming </strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty"><strong>{{ '&euro; '.number_format(CalculationEndresult::totalContracting($project), 2, ",",".") }}</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">@if($include_tax) <strong>{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax($project), 2, ",",".") }}</strong> @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
	 </table>
	 <br>
	 <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <h4 class="name">Onderaanneming</h4>
		  <tr style="page-break-after: always;">
			<th style="width: 147px" align="left" class="qty">&nbsp;</th>
			<th style="width: 60px" align="left" class="qty">Uren</th>
			<th style="width: 120px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">BTW %</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">&nbsp;</th>
		  </tr>
		</thead>
		<tbody>
		@if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Arbeidskosten</strong></td>
			<td class="qty">{{ ' '.number_format(CalculationEndresult::subconCalcLaborActivityTax1($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ ' '.number_format(CalculationEndresult::subconCalcLaborActivityTax2($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Arbeidskosten</strong></td>
			<td class="qty">{{ ''.number_format(CalculationEndresult::subconCalcLaborActivityTax3($project), 2, ",",".") }}</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcLaborActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materiaalkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materiaalkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcMaterialActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif

		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materieelkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax1Amount($project), 2, ",",".") }}</td>
			<td class="qty">21%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax1AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax2Amount($project), 2, ",",".") }}</td>
			<td class="qty">6%</td>
			<td class="qty">@if($include_tax) {{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax2AmountTax($project), 2, ",",".") }} @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @else
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Materieelkosten</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::subconCalcEquipmentActivityTax3Amount($project), 2, ",",".") }}</td>
			<td class="qty">0%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		  <tr>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Totaal Onderaanneming </strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty"><strong>{{ '&euro; '.number_format(CalculationEndresult::totalSubcontracting($project), 2, ",",".") }}</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">@if($include_tax) <strong>{{ '&euro; '.number_format(CalculationEndresult::totalSubcontractingTax($project), 2, ",",".") }}</strong> @endif</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr>
			<td class="qty">&nbsp;</td>
		  </tr>
		</tbody>
	  </table>

	  <h1 class="name">Totalen offerte</h1>
	  <hr>
	  <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 207px" align="left" class="qty">&nbsp;</th>
			<th style="width: 119px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">&nbsp;</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">@if($include_tax) Bedrag (incl. BTW) @endif</th>
		  </tr>
		</thead>
		<tbody>
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Calculatief te offreren (excl. BTW)</strong></td>
			<td class="qty"><class="pull-right">{{ '&euro; '.number_format(CalculationEndresult::totalProject($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @if($include_tax)
		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">BTW bedrag 21%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax1($project)+CalculationEndresult::totalSubcontractingTax1($project)+BlancRowsEndresult::rowTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">BTW bedrag 6%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax2($project)+CalculationEndresult::totalSubcontractingTax2($project)+BlancRowsEndresult::rowTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Calculatief te offreren (Incl. BTW)</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty"><strong class="pull-right">{{ '&euro; '.number_format(CalculationEndresult::superTotalProject($project), 2, ",",".") }}</strong></td>
		  </tr>
		  @endif
		</tbody>
	  </table>
@else

	  <h1 class="name">Totalen offerte</h1>
	  <hr>
	  <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 207px" align="left" class="qty">&nbsp;</th>
			<th style="width: 119px" align="left" class="qty">Bedrag @if($include_tax) (excl. BTW) @endif</th>
			<th style="width: 70px" align="left" class="qty">&nbsp;</th>
			<th style="width: 80px" align="left" class="qty">@if($include_tax) BTW bedrag @endif</th>
			<th style="width: 119px" align="left" class="qty">@if($include_tax) Bedrag (incl. BTW) @endif</th>
		  </tr>
		</thead>
		<tbody>
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Calculatief te offreren (excl. BTW)</strong></td>
			<td class="qty"><class="pull-right">{{ '&euro; '.number_format(CalculationEndresult::totalProject($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @if($include_tax)
		  @if (!$project->tax_reverse)
		  <tr style="page-break-after: always;">
			<td class="qty">BTW bedrag 21%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax1($project)+CalculationEndresult::totalSubcontractingTax1($project)+BlancRowsEndresult::rowTax1AmountTax($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  <tr style="page-break-after: always;">
			<td class="qty">BTW bedrag 6%</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">{{ '&euro; '.number_format(CalculationEndresult::totalContractingTax2($project)+CalculationEndresult::totalSubcontractingTax2($project)+BlancRowsEndresult::rowTax2AmountTax($project), 2, ",",".") }}</td>
			<td class="qty">&nbsp;</td>
		  </tr>
		  @endif
		  <tr style="page-break-after: always;">
			<td class="qty"><strong>Calculatief te offreren (Incl. BTW)</strong></td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty"><strong class="pull-right">{{ '&euro; '.number_format(CalculationEndresult::superTotalProject($project)+BlancRowsEndresult::rowTax1AmountTax($project)+BlancRowsEndresult::rowTax2AmountTax($project), 2, ",",".") }}</strong></td>								
		  </tr>
		  @endif
		</tbody>
	  </table>

	   <div class="closingtext">{{ ($offer ? $offer->closure : '') }}</div>

	  <h1 class="name">Bepalingen</h1>
	  <hr>
	  <div class="terms">
		<li>
			{{ invoice_condition($offer) }}
		</li>
		<li>
		@if (DeliverTime::find($offer->deliver_id)->delivertime_name == "per direct" || DeliverTime::find($offer->deliver_id)->delivertime_name == "in overleg")
		  Wij kunnen de werkzaamheden
		  {{ DeliverTime::find($offer->deliver_id)->delivertime_name }}
		  starten na uw opdrachtbevestiging.
		@else
		  Wij kunnen de werkzaamheden starten binnen
		  {{ DeliverTime::find($offer->deliver_id)->delivertime_name }}
		  na uw opdrachtbevestiging.
		@endif</li>
		<li>Deze offerte is geldig tot {{ Valid::find($offer->valid_id)->valid_name }} na dagtekening.</li>
		@if($offer->extracondition)
		<li>{{ $offer->extracondition }}</li>
		@endif
	  </div>
	  <div class="from">Met vriendelijke groet,</div>
	  <div class="from">{{ Contact::find($offer->from_contact_id)->firstname ." ". Contact::find($offer->from_contact_id)->lastname }}</div>
	</main>
 
@endif

@if (!$only_totals)
	  <?#--PAGE HEADER SECOND START--?>
	  <div style="page-break-after:always;"></div>
	  <header class="clearfix">
		<div id="logo">
		<?php if ($relation_self && $relation_self->logo_id) echo "<img src=\"".asset(Resource::find($relation_self->logo_id)->file_location)."\"/>"; ?>
		</div>
		  <div id="invoice">
		  <h3 class="name">{{ OfferController::getOfferCode($project->id) }}</h3>
		  <div class="date">{{ $project->project_name }}</div>
		  <div class="date">{{ date("j M Y", strtotime($offer->offer_make)) }}</div>
		</div>
	  </header>
	  <?#--PAGE HEADER SECOND END--?>

	  <div class="closingtext">{{ ($offer ? $offer->closure : '') }}</div>

	  <h1 class="name">Bepalingen</h1>
	  <hr>
	  <div class="terms">
	  <li>
			{{ invoice_condition($offer) }}
		</li>
		<li>
		@if (DeliverTime::find($offer->deliver_id)->delivertime_name == "per direct" || DeliverTime::find($offer->deliver_id)->delivertime_name == "in overleg")
		  Wij kunnen de werkzaamheden
		  {{ DeliverTime::find($offer->deliver_id)->delivertime_name }}
		  starten na uw opdrachtbevestiging.
		@else
		  Wij kunnen de werkzaamheden starten binnen
		  {{ DeliverTime::find($offer->deliver_id)->delivertime_name }}
		  na uw opdrachtbevestiging.
		@endif</li>
		<li>Deze offerte is geldig tot {{ Valid::find($offer->valid_id)->valid_name }} na dagtekening.</li>
		@if($offer->extracondition)
		<li>{{ $offer->extracondition }}</li>
		@endif
	  </div>
	  <div class="from">Met vriendelijke groet,</div>
	  <div class="from">{{ Contact::find($offer->from_contact_id)->firstname ." ". Contact::find($offer->from_contact_id)->lastname }}</div>
	</main>

 @endif
 @endif

	@if ($display_worktotals)
	@if ($seperate_subcon)

	  <?#--PAGE HEADER SECOND START--?>
	  <div style="page-break-after:always;"></div>
	  <header class="clearfix">
		<div id="logo">
		<?php if ($relation_self && $relation_self->logo_id) echo "<img src=\"".asset(Resource::find($relation_self->logo_id)->file_location)."\"/>"; ?>
		</div>
		  <div id="invoice">
		  <h3 class="name">{{ OfferController::getOfferCode($project->id) }}</h3>
		  <div class="date">{{ $project->project_name }}</div>
		  <div class="date">{{ date("j M Y", strtotime($offer->offer_make)) }}</div>
		</div>
	  </header>
	  <?#--PAGE HEADER SECOND END--?>

	 <h1 class="name">Totaalkosten per werkzaamheid</h1>
	 <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 130px" class="qty">Hoofdstuk</th>
			<th style="width: 170px" class="qty">Werkzaamheid</th>
			<th style="width: 40px" class="qty">@if ($display_specification) Uren @endif</th>
			<th style="width: 51px" class="qty">@if ($display_specification) Arbeid @endif</th>
			<th style="width: 51px" class="qty">@if ($display_specification) Materiaal @endif</th>
			<th style="width: 51px" class="qty">@if ($display_specification) Materieel @endif</th>
			<th style="width: 51px" class="qty">Totaal</th>
			<th style="width: 51px" class="qty">Stelpost</th>
		  </tr>
		</thead>
		<tbody>
		  @foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
		  @foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','contracting')->first()->id)->orderBy('created_at')->get() as $activity)
		  <tr>
			<td class="qty">{{ $chapter->chapter_name }}</td>
			<td class="qty">{{ $activity->activity_name }}</td>
			<td class="qty">@if ($display_specification) <span>{{ number_format(CalculationOverview::laborTotal($activity), 2, ",",".") }} @endif</td>
			<td class="qty">@if ($display_specification) <span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::laborActivity($project->hour_rate, $activity), 2, ",",".") }}</span>@endif</td>
			<td class="qty">@if ($display_specification) <span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::materialActivityProfit($activity, $project->profit_calc_contr_mat), 2, ",",".") }}</span>@endif</td>
			<td class="qty">@if ($display_specification) <span>{{ '&euro; '.number_format(CalculationOverview::equipmentActivityProfit($activity, $project->profit_calc_contr_equip), 2, ",",".") }}</span>@endif</td>
			<td class="qty"><span>{{ '&euro; '.number_format(CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_contr_mat, $project->profit_calc_contr_equip), 2, ",",".") }} </td>
			<td class="qty text-center">
			<?php
			  if (PartType::find($activity->part_type_id)->type_name=='estimate') {
				echo "<strong>Ja</strong>";
			  }
			?>
			</td>
		  </tr>
		  @endforeach
		  @endforeach
		  @foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
		  @foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','subcontracting')->first()->id)->orderBy('created_at')->get() as $activity)
		  <tr><?#-- item --?>
			<td class="qty">{{ $chapter->chapter_name }}</td>
			<td class="qty">{{ $activity->activity_name }}</td>
			<td class="qty">@if ($display_specification) <span>{{ number_format(CalculationOverview::laborTotal($activity), 2, ",",".") }} @endif</td>
			<td class="qty">@if ($display_specification) <span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::laborActivity($project->hour_rate, $activity), 2, ",",".") }}</span>@endif</td>
			<td class="qty">@if ($display_specification) <span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::materialActivityProfit($activity, $project->profit_calc_subcontr_mat), 2, ",",".") }}</span>@endif</td>
			<td class="qty">@if ($display_specification) <span>{{ '&euro; '.number_format(CalculationOverview::equipmentActivityProfit($activity, $project->profit_calc_subcontr_equip), 2, ",",".") }}</span>@endif</td>
			<td class="qty"><span>{{ '&euro; '.number_format(CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_subcontr_mat, $project->profit_calc_subcontr_equip), 2, ",",".") }} </td>
			<td class="qty text-center">
			<?php
			  if (PartType::find($activity->part_type_id)->type_name=='estimate') {
				echo "<strong>Ja</strong>";
			  }
			?>
			</td>
		  </tr>
		  @endforeach
		  @endforeach
		</tbody>

	 </table>

	 <h1 class="name">Totalen project</h1>
	 <table border="0" cellspacing="0" cellpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 300px" class="qty">&nbsp;</th>
			<th style="width: 40px" class="qty">@if ($display_specification) Uren @endif</th>
			<th style="width: 51px" class="qty">@if ($display_specification) Arbeid @endif</th>
			<th style="width: 51px" class="qty">@if ($display_specification) Materiaal @endif</th>
			<th style="width: 51px" class="qty">@if ($display_specification) Materieel @endif</th>
			<th style="width: 51px" class="qty">@if ($display_specification)Totaal @endif</th>
			<th style="width: 51px" class="qty">&nbsp;</th>
		  </tr>
		</thead>
		<tbody>
		  <td class="qty"><strong>&nbsp;</td>
		  <td class="qty"><strong>@if ($display_specification) <span>{{ number_format(CalculationOverview::laborSuperTotalAmount($project), 2, ",",".") }}</span>@endif</strong></td>
		  <td class="qty"><strong>@if ($display_specification) <span>{{ '&euro; '.number_format(CalculationOverview::laborSuperTotal($project), 2, ",",".") }}</span>@endif</strong></td>
		  <td class="qty"><strong>@if ($display_specification) <span>{{ '&euro; '.number_format(CalculationOverview::materialSuperTotal($project), 2, ",",".") }}</span>@endif</strong></td>
		  <td class="qty"><strong>@if ($display_specification) <span>{{ '&euro; '.number_format(CalculationOverview::equipmentSuperTotal($project), 2, ",",".") }}</span>@endif</strong></td>
		  <td class="qty"><strong><span>{{ '&euro; '.number_format(CalculationOverview::superTotal($project), 2, ",",".") }}</span></strong></td>
		  <td class="qty">&nbsp;</td>
		</tbody>
	  </table>
	  <strong>Weergegeven bedragen zijn exclusief BTW</strong>
	  @else

	  <?#--PAGE HEADER SECOND START--?>
	  <div style="page-break-after:always;"></div>
	  <header class="clearfix">
		<div id="logo">
		<?php if ($relation_self && $relation_self->logo_id) echo "<img src=\"".asset(Resource::find($relation_self->logo_id)->file_location)."\"/>"; ?>
		</div>
		  <div id="invoice">
		  <h3 class="name">{{ OfferController::getOfferCode($project->id) }}</h3>
		  <div class="date">{{ $project->project_name }}</div>
		  <div class="date">{{ date("j M Y", strtotime($offer->offer_make)) }}</div>
		</div>
	  </header>
	  <?#--PAGE HEADER SECOND END--?>

	<h1 class="name">Totalen project</h1>
	<h4 class="name">Aanneming</h4>
	<table border="0" cellspacing="0" cellpadding="0">
	  <thead>
		<tr style="page-break-after: always;">
		  <th style="width: 130px" class="qty">Hoofdstuk</th>
		  <th style="width: 170px" class="qty">Werkzaamheid</th>
		  <th style="width: 40px" class="qty">@if ($display_specification) Uren @endif</th>
		  <th style="width: 51px" class="qty">@if ($display_specification) Arbeid @endif</th>
		  <th style="width: 51px" class="qty">@if ($display_specification) Materiaal @endif</th>
		  <th style="width: 51px" class="qty">@if ($display_specification) Materieel @endif</th>
		  <th style="width: 51px" class="qty">Totaal</th>
		  <th style="width: 51px" class="qty">Stelpost</th>
		 </tr>
	  </thead>
	  <tbody>
	  @foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
	  @foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','contracting')->first()->id)->orderBy('created_at')->get() as $activity)
		<tr style="page-break-after: always;">
		  <td class="qty">{{ $chapter->chapter_name }}</td>
		  <td class="qty">{{ $activity->activity_name }}</td>
		  <td class="qty">@if ($display_specification)<span>{{ number_format(CalculationOverview::laborTotal($activity), 2, ",",".") }}@endif</td>
		  <td class="qty">@if ($display_specification)<span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::laborActivity($project->hour_rate, $activity), 2, ",",".") }}</span>@endif</td>
		  <td class="qty">@if ($display_specification)<span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::materialActivityProfit($activity, $project->profit_calc_contr_mat), 2, ",",".") }}</span>@endif</td>
		  <td class="qty">@if ($display_specification)<span>{{ '&euro; '.number_format(CalculationOverview::equipmentActivityProfit($activity, $project->profit_calc_contr_equip), 2, ",",".") }}</span>@endif</td>
		  <td class="qty"><span>{{ '&euro; '.number_format(CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_contr_mat, $project->profit_calc_contr_equip), 2, ",",".") }}</td>
		  <td class="qty text-center">
		  <?php
			if (PartType::find($activity->part_type_id)->type_name=='estimate') {
			 echo "<strong>Ja</strong>";
		   }
		  ?>
		  </td>
		</tr>
		@endforeach
		@endforeach
		<tr style="page-break-after: always;">
		  <td class="qty"><strong>Totaal</strong></td>
		  <td class="qty">&nbsp;</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ number_format(CalculationOverview::contrLaborTotalAmount($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ '&euro; '.number_format(CalculationOverview::contrLaborTotal($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ '&euro; '.number_format(CalculationOverview::contrMaterialTotal($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ '&euro; '.number_format(CalculationOverview::contrEquipmentTotal($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty"><strong><span>{{ '&euro; '.number_format(CalculationOverview::contrTotal($project), 2, ",",".") }}</span></strong></td>
		  <td class="qty">&nbsp;</td>
		</tr>
	  </tbody>
	</table>

	<h4 class="name">Onderaanneming</h4>
	<table border="0" cellspacing="0" cellpadding="0">
	  <thead>
		<tr style="page-break-after: always;">
		  <th style="width: 130px" class="qty">Hoofdstuk</th>
		  <th style="width: 170px" class="qty">Werkzaamheid</th>
		  <th style="width: 40px" class="qty">@if ($display_specification) Uren @endif</th>
		  <th style="width: 51px" class="qty">@if ($display_specification) Arbeid @endif</th>
		  <th style="width: 51px" class="qty">@if ($display_specification) Materiaal @endif</th>
		  <th style="width: 51px" class="qty">@if ($display_specification) Materieel @endif</th>
		  <th style="width: 51px" class="qty">Totaal</th>
		  <th style="width: 51px" class="qty">Stelpost</th>
		 </tr>
	  </thead>
	  <tbody>
		@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
		@foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','subcontracting')->first()->id)->orderBy('created_at')->get() as $activity)
		<tr style="page-break-after: always;">
		  <td class="qty">{{ $chapter->chapter_name }}</td>
		  <td class="qty">{{ $activity->activity_name }}</td>
		  <td class="qty">@if ($display_specification)<span>{{ number_format(CalculationOverview::laborTotal($activity), 2, ",",".") }}@endif</td>
		  <td class="qty">@if ($display_specification)<span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::laborActivity($project->hour_rate, $activity), 2, ",",".") }}</span>@endif</td>
		  <td class="qty">@if ($display_specification)<span class="total-ex-tax">{{ '&euro; '.number_format(CalculationOverview::materialActivityProfit($activity, $project->profit_calc_subcontr_mat), 2, ",",".") }}</span>@endif</td>
		  <td class="qty">@if ($display_specification)<span>{{ '&euro; '.number_format(CalculationOverview::equipmentActivityProfit($activity, $project->profit_calc_subcontr_equip), 2, ",",".") }}</span>@endif</td>
		  <td class="qty"><span>{{ '&euro; '.number_format(CalculationOverview::activityTotalProfit($project->hour_rate, $activity, $project->profit_calc_subcontr_mat, $project->profit_calc_subcontr_equip), 2, ",",".") }} </td>
		  <td class="qty text-center">
		  <?php
			if (PartType::find($activity->part_type_id)->type_name=='estimate') {
			  echo "<strong>Ja</strong>";
			}
		  ?>
		  </td>
		</tr>
		@endforeach
		@endforeach
		<tr style="page-break-after: always;">
		  <td class="qty"><strong>Totaal</strong></td>
		  <td class="qty">&nbsp;</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ number_format(CalculationOverview::subcontrLaborTotalAmount($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ '&euro; '.number_format(CalculationOverview::subcontrLaborTotal($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ '&euro; '.number_format(CalculationOverview::subcontrMaterialTotal($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty">@if ($display_specification)<strong><span>{{ '&euro; '.number_format(CalculationOverview::subcontrEquipmentTotal($project), 2, ",",".") }}</span></strong>@endif</td>
		  <td class="qty"><strong><span>{{ '&euro; '.number_format(CalculationOverview::subcontrTotal($project), 2, ",",".") }}</span></strong></td>
		  <td class="qty">&nbsp;</td>
		</tr>
	  </tbody>
	</table>

	 <h1 class="name">Totalen project</h1>
	 <table border="0" cellspacing="0" cellnpadding="0">
		<thead>
		  <tr style="page-break-after: always;">
			<th style="width: 130px" class="qty"class="qty">&nbsp;</th>
			<th style="width: 170px" class="qty"class="qty">&nbsp;</th>
			<th style="width: 40px" class="qty"class="qty">@if ($display_specification) Uren @endif</th>
			<th style="width: 51px" class="qty"class="qty">@if ($display_specification) Arbeid @endif</th>
			<th style="width: 51px" class="qty"class="qty">@if ($display_specification) Materiaal @endif</th>
			<th style="width: 51px" class="qty"class="qty">@if ($display_specification) Materieel @endif</th>
			<th style="width: 51px" class="qty"class="qty">Totaal</th>
			<th style="width: 51px" class="qty"class="qty">&nbsp;</th>
		  </tr>
		</thead>
		<tbody>
		  <tr style="page-break-after: always;">
			<td class="qty">&nbsp;</td>
			<td class="qty">&nbsp;</td>
			<td class="qty">@if ($display_specification)<span>{{ number_format(CalculationOverview::laborSuperTotalAmount($project), 2, ",",".") }}</span>@endif</td>
			<td class="qty">@if ($display_specification)<span>{{ '&euro; '.number_format(CalculationOverview::laborSuperTotal($project), 2, ",",".") }}</span>@endif</td>
			<td class="qty">@if ($display_specification)<span>{{ '&euro; '.number_format(CalculationOverview::materialSuperTotal($project), 2, ",",".") }}</span>@endif</td>
			<td class="qty">@if ($display_specification)<span>{{ '&euro; '.number_format(CalculationOverview::equipmentSuperTotal($project), 2, ",",".") }}</span>@endif</td>
			<td class="qty"><span>{{ '&euro; '.number_format(CalculationOverview::superTotal($project), 2, ",",".") }}</span></td>
			<td class="qty">&nbsp;</td>
		  </tr>
	  </table>
	 <strong>Weergegeven bedragen zijn exclusief BTW</strong>
	  @endif
	  @endif

	@if ($display_description)
	@if ($seperate_subcon)

	  <?#--PAGE HEADER SECOND START--?>
	  <div style="page-break-after:always;"></div>
	  <header class="clearfix">
		<div id="logo">
		<?php if ($relation_self && $relation_self->logo_id) echo "<img src=\"".asset(Resource::find($relation_self->logo_id)->file_location)."\"/>"; ?>
		</div>
		  <div id="invoice">
		  <h3 class="name">{{ OfferController::getOfferCode($project->id) }}</h3>
		  <div class="date">{{ $project->project_name }}</div>
		  <div class="date">{{ date("j M Y", strtotime($offer->offer_make)) }}</div>
		</div>
	  </header>
	  <?#--PAGE HEADER SECOND END--?>

	<h1 class="name">Omschrijving werkzaamheden</h1>
	<table border="0" cellspacing="0" cellpadding="0">
	  <thead>
		<tr>
		  <th style="width: 130px" class="qty">Hoofdstuk</th>
		  <th style="width: 170px" class="qty">Werkzaamheid</th>
		  <th class="qty">Omschrijving</th>
		</tr>
	  </thead>
	  <tbody>
		@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
		<?php $i = true; ?>
		@foreach (Activity::where('chapter_id','=', $chapter->id)->orderBy('created_at')->get() as $activity)
		<tr>
		  <td class="qty" valign="top"><br><?php echo ($i ? $chapter->chapter_name : ''); $i = false; ?></td>
		  <td class="qty" valign="top"><br>{{ $activity->activity_name }}</td>
		  <td class="qty" valign="top"><br><span>{!! $activity->note !!}</td>
		</tr>
		@endforeach
		@endforeach
	  </tbody>
	</table>
	@else

	  <?#--PAGE HEADER SECOND START--?>
	  <div style="page-break-after:always;"></div>
	  <header class="clearfix">
		<div id="logo">
		<?php if ($relation_self && $relation_self->logo_id) echo "<img src=\"".asset(Resource::find($relation_self->logo_id)->file_location)."\"/>"; ?>
		</div>
		  <div id="invoice">
		  <h3 class="name">{{ OfferController::getOfferCode($project->id) }}</h3>
		  <div class="date">{{ $project->project_name }}</div>
		  <div class="date">{{ date("j M Y", strtotime($offer->offer_make)) }}</div>
		</div>
	  </header>
	  <?#--PAGE HEADER SECOND END--?>

	<h1 class="name">Omschrijving werkzaamheden</h1>
	<h4 class="name">Aanneming</h4>
	<table border="0" cellspacing="0" cellpadding="0">
	  <thead>
		<tr>
		  <th style="width: 130px"class="qty">Hoofdstuk</th>
		  <th style="width: 170px"class="qty">Werkzaamheid</th>
		  <th class="qty">Omschrijving</th>
		</tr>
	  </thead>
	  <tbody>
		@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
		<?php $i = true; ?>
		@foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','contracting')->first()->id)->orderBy('created_at')->get() as $activity)
		<tr>
		  <td class="qty" valign="top"><br><?php echo ($i ? $chapter->chapter_name : ''); $i = false; ?></td>
		  <td class="qty" valign="top"><br>{{ $activity->activity_name }}</td>
		  <td class="qty" valign="top"><br><span>{{ $activity->note }}</td>
		</tr>
		@endforeach
		@endforeach
	  </tbody>
	</table>
	 <h4 class="name">Onderaanneming</h4>
	<table border="0" cellspacing="0" cellpadding="0">
	  <thead>
		<tr>
		  <th style="width: 130px" class="qty">Hoofdstuk</th>
		  <th style="width: 170px" class="qty">Werkzaamheid</th>
		  <th class="qty">Omschrijving</th>
		</tr>
	  </thead>
	  <tbody>
		@foreach (Chapter::where('project_id','=', $project->id)->orderBy('created_at')->get() as $chapter)
		<?php $i = true; ?>
		@foreach (Activity::where('chapter_id','=', $chapter->id)->where('part_id','=',Part::where('part_name','=','subcontracting')->first()->id)->orderBy('created_at')->get() as $activity)
		<tr>
		  <td class="qty"><?php echo ($i ? $chapter->chapter_name : ''); $i = false; ?></td>
		  <td class="qty">{{ $activity->activity_name }}</td>
		  <td class="qty"><br><span>{{ $activity->note }}</td>
		</tr>
		@endforeach
		@endforeach
	  </tbody>
	</table>
	@endif
	@endif
  </body>
</html>
