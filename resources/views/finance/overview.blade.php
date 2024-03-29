<?php

use \BynqIO\Dynq\Models\Relation;
use \BynqIO\Dynq\Models\Contact;
use \BynqIO\Dynq\Models\Invoice;
use \BynqIO\Dynq\Models\Resource;
use \BynqIO\Dynq\Models\Project;
use \BynqIO\Dynq\Models\Offer;

$relation = Relation::find(Auth::user()->self_id);
$user = Auth::user();
?>

@extends('layout.master')

@section('title', 'Mijn bedrijf')

@push('style')
<link media="all" type="text/css" rel="stylesheet" href="/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css">
@endpush

@push('scripts')
<script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
@endpush

@section('content')
<script type="text/javascript">
$(document).ready(function() {
    $('#tab-offer').click(function(e){
        sessionStorage.toggleOfferInvoice{{Auth::id()}} = 'offer';
    });
    $('#tab-invoice').click(function(e){
        sessionStorage.toggleOfferInvoice{{Auth::id()}} = 'invoice';
    });

    if (sessionStorage.toggleOfferInvoice{{Auth::id()}}){
        $toggleOpenTab = sessionStorage.toggleOfferInvoice{{Auth::id()}};
        $('#tab-'+$toggleOpenTab).addClass('active');
        $('#'+$toggleOpenTab).addClass('active');
    } else {
        sessionStorage.toggleOfferInvoice{{Auth::id()}} = 'offer';
        $('#tab-offer').addClass('active');
        $('#offer').addClass('active');
    }
});
</script>

<div id="wrapper">

    <section class="container">

        <div class="col-md-12">

            <div>
                <ol class="breadcrumb">
                  <li><a href="/">Dashboard</a></li>
                  <li class="active">Financieel</li>
                </ol>
            <div>

            @if (Session::has('success'))
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i>
                <strong>{{ Session::get('success') }}</strong>
            </div>
            @endif

            @if (count($errors) > 0)
            <div class="alert alert-danger">
                <i class="fa fa-frown-o"></i>
                <strong>Fouten in de invoer</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                    <li><h5 class="nomargin">{{ $error }}</h5></li>
                    @endforeach
                </ul>
            </div>
            @endif

            <h2 style="margin: 10px 0 20px 0;"><strong>Overzichten</strong></h2>

                <div class="tabs nomargin-top">

                    <ul class="nav nav-tabs">
                        <li id="tab-offer">
                            <a href="#offer" data-toggle="tab">Openstaande offertes</a>
                        </li>
                        <li id="tab-invoice">
                            <a href="#invoice" data-toggle="tab">Openstaande facturen</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div id="offer" class="tab-pane">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="col-md-4">Project</th>
                                        <th class="col-md-2">Offertenummer</th>
                                        <th class="col-md-2">Datum</th>
                                        <th class="col-md-3">Offertebedrag</th>
                                        <th class="col-md-3">Acties</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $offer_total = 0; ?>
                                    @foreach(Project::where('user_id', Auth::user()->id)->whereNull('project_close')->orderBy('created_at', 'desc')->get() as $project)
                                    <?php
                                    $offer = Offer::where('project_id', $project->id)->whereNull('offer_finish')->orderBy('created_at','desc')->first();
                                    if (!$offer)
                                        continue;
                                    ?>
                                    <?php
                                    $offer_total += $offer->offer_total;
                                    ?>
                                    <tr>
                                        <td class="col-md-4"><a href="/project/{{ $project->id }}-{{ $project->slug() }}/details">{{ $project->project_name }}</a></td>
                                        <td class="col-md-2"><a href="/offer/project-{{ $project->id }}/offer-{{ $offer->id }}">{{ $offer->offer_code }}</a></td>
                                        <td class="col-md-2"><?php echo date('d-m-Y', strtotime($offer->offer_make)); ?></td>
                                        <td class="col-md-3">@money($offer->offer_total)</td>
                                        <td class="col-md-3"><a href="/resource/{{ ($offer->resource_id) }}/download/quotation.pdf" class="btn btn-primary btn-xs"><i class="fa fa-cloud-download fa-fw"></i> Downloaden</a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfooter>
                                    <tr>
                                        <th class="col-md-4">Totaal</th>
                                        <th class="col-md-2"></th>
                                        <th class="col-md-2"></th>
                                        <th class="col-md-3">@money($offer_total)</th>
                                        <th class="col-md-3"></th>
                                    </tr>
                                </tfooter>
                            </table>
                        </div>
                        <div id="invoice" class="tab-pane">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="col-md-3">Project</th>
                                        <th class="col-md-2">Factuurnummer</th>
                                        <th class="col-md-2">Bedrag (Excl. BTW)</th>
                                        <th class="col-md-2">Aangemaakt op</th>
                                        <th class="col-md-1">Conditie</th>
                                        <th class="col-md-2">Acties</th>
                                        <th class="col-md-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $invoice_total = 0; ?>
                                    @foreach(Project::where('user_id', Auth::user()->id)->whereNull('project_close')->orderBy('created_at', 'desc')->get() as $project)
                                    <?php
                                    $offer_last = Offer::where('project_id',$project->id)->orderBy('created_at', 'desc')->first();
                                    if (!$offer_last)
                                        continue;
                                    ?>
                                    @foreach(Invoice::where('offer_id',$offer_last->id)->where('invoice_close', true)->whereNull('payment_date')->orderBy('priority','desc')->get() as $invoice)
                                    <?php
                                    $invoice_total += $invoice->amount;
                                    ?>
                                    <tr>
                                        <td class="col-md-3"><a href="/project/{{ $project->id }}-{{ $project->slug() }}/details">{{ $project->project_name }}</a></td>
                                        <td class="col-md-2"><a href="/invoice/project-{{ $project->id }}/pdf-invoice-{{ $invoice->id }}">{{ Auth::user()->pref_use_ct_numbering ? $invoice->invoice_code : ($invoice->book_code ? $invoice->book_code : $invoice->invoice_code) }}</a></td>
                                        <td class="col-md-2">@money($invoice->amount, false)</td>
                                        <td class="col-md-2">{{ $invoice->invoice_make ? date("d-m-Y", strtotime($invoice->invoice_make)) : '-' }}</td>
                                        <td class="col-md-1">{{ $invoice->payment_condition }} dagen</td>
                                        <td class="col-md-1"><a href="/resource/{{ ($invoice->resource_id) }}/download/invoice.pdf" class="btn btn-primary btn-xs"><i class="fa fa-cloud-download fa-fw"></i> Downloaden</a></td>
                                        <td class="col-md-1"></td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                                <tfooter>
                                    <tr>
                                        <th class="col-md-3">Totaal</th>
                                        <th class="col-md-2"></th>
                                        <th class="col-md-2">@money($invoice_total)</th>
                                        <th class="col-md-2"></th>
                                        <th class="col-md-1"></th>
                                        <th class="col-md-1"></th>
                                        <th class="col-md-1"></th>
                                    </tr>
                                </tfooter>
                            </table>
                        </div>

                    </div>
                </div>

        </div>

    </section>

</div>
@stop
