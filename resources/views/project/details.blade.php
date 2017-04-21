<?php

use BynqIO\CalculatieTool\Calculus\CalculationEndresult;
use BynqIO\CalculatieTool\Models\Relation;
use BynqIO\CalculatieTool\Models\PurchaseKind;
use BynqIO\CalculatieTool\Models\Contact;
use BynqIO\CalculatieTool\Models\Project;
use BynqIO\CalculatieTool\Models\ProjectType;
use BynqIO\CalculatieTool\Models\Offer;
use BynqIO\CalculatieTool\Models\Invoice;
use BynqIO\CalculatieTool\Models\Wholesale;
use BynqIO\CalculatieTool\Models\ProjectShare;
use BynqIO\CalculatieTool\Models\RelationKind;
use BynqIO\CalculatieTool\Models\Province;
use BynqIO\CalculatieTool\Models\Country;
use BynqIO\CalculatieTool\Models\Chapter;
use BynqIO\CalculatieTool\Models\Activity;
use BynqIO\CalculatieTool\Models\Timesheet;
use BynqIO\CalculatieTool\Models\Resource;
use BynqIO\CalculatieTool\Models\TimesheetKind;
use BynqIO\CalculatieTool\Models\Purchase;
use BynqIO\CalculatieTool\Models\EstimateLabor;
use BynqIO\CalculatieTool\Models\EstimateMaterial;
use BynqIO\CalculatieTool\Models\EstimateEquipment;
use BynqIO\CalculatieTool\Models\MoreLabor;
use BynqIO\CalculatieTool\Models\MoreMaterial;
use BynqIO\CalculatieTool\Models\MoreEquipment;
use BynqIO\CalculatieTool\Models\CalculationLabor;
use BynqIO\CalculatieTool\Models\CalculationMaterial;
use BynqIO\CalculatieTool\Models\CalculationEquipment;

$common_access_error = false;
$project = Project::find(Route::Input('project_id'));
if (!$project || !$project->isOwner() || $project->is_dilapidated)
    $common_access_error = true;
else {
    $offer_last = Offer::where('project_id',$project->id)->orderBy('created_at', 'desc')->first();
    $share = ProjectShare::where('project_id', $project->id)->first();
    if ($offer_last)
        $cntinv = Invoice::where('offer_id',$offer_last->id)->where('invoice_close',true)->count('id');
    else
        $cntinv = 0;

}

if($common_access_error){ ?>
    @section('content')
    <div id="wrapper">
        <section class="container">
            <div class="alert alert-danger">
                <i class="fa fa-frown-o"></i>
                <strong>Fout</strong>
                Dit project bestaat niet
            </div>
        </section>
    </div>
    @stop
<?php }else{
    $type = ProjectType::find($project->type_id);

    $offer_last ? $invoice_end = Invoice::where('offer_id','=', $offer_last->id)->where('isclose','=',true)->first() : $invoice_end = null;

    $estim_total = 0;
    $more_total = 0;
    $less_total = 0;
    $disable_estim = false;
    $disable_more = false;
    $disable_less = false;

    foreach(Chapter::where('project_id','=', $project->id)->get() as $chap) {
        foreach(Activity::where('chapter_id','=', $chap->id)->get() as $activity) {
            $estim_total += EstimateLabor::where('activity_id','=', $activity->id)->count('id');
            $estim_total += EstimateMaterial::where('activity_id','=', $activity->id)->count('id');
            $estim_total += EstimateEquipment::where('activity_id','=', $activity->id)->count('id');

            $more_total += MoreLabor::where('activity_id','=', $activity->id)->count('id');
            $more_total += MoreMaterial::where('activity_id','=', $activity->id)->count('id');
            $more_total += MoreEquipment::where('activity_id','=', $activity->id)->count('id');	

            $less_total += CalculationLabor::where('activity_id','=', $activity->id)->where('isless',true)->count('id');
            $less_total += CalculationMaterial::where('activity_id','=', $activity->id)->where('isless',true)->count('id');
            $less_total += CalculationEquipment::where('activity_id','=', $activity->id)->where('isless',true)->count('id');	
        }
    }

    if ($offer_last) {
        $disable_estim = true;
    }
    if ($estim_total>0) {
        $disable_estim = true;
    }

    if ($invoice_end && $invoice_end->invoice_close) {
        $disable_more = true;
    }
    if ($more_total>0) {
        $disable_more = true;
    }

    if ($invoice_end && $invoice_end->invoice_close) {
        $disable_less = true;
    }
    if ($less_total>0) {
        $disable_less = true;
    }
?>

@extends('layout.master')

@section('title', 'Projectdetails')

@push('style')
<link media="all" type="text/css" rel="stylesheet" href="/components/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css">
<link media="all" type="text/css" rel="stylesheet" href="/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css">
@endpush

@push('scripts')
<script src="/components/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
<script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="/plugins/summernote/summernote.min.js"></script>
@endpush

@section('content')
<script type="text/javascript">
$(document).ready(function() {
    $('#tab-project').click(function(e){
        sessionStorage.toggleTabProj{{Auth::id()}} = 'project';
    });
    $('#tab-calc').click(function(e){
        sessionStorage.toggleTabProj{{Auth::id()}} = 'calc';
    });
    $('#tab-doc').click(function(e){
        sessionStorage.toggleTabProj{{Auth::id()}} = 'doc';
    });
    $('#tab-advanced').click(function(e){
        sessionStorage.toggleTabProj{{Auth::id()}} = 'advanced';
    });
    $('#tab-communication').click(function(e){
        sessionStorage.toggleTabProj{{Auth::id()}} = 'communication';
    });
    if (sessionStorage.toggleTabProj{{Auth::id()}}){
        $toggleOpenTab = sessionStorage.toggleTabProj{{Auth::id()}};
        $('#tab-'+$toggleOpenTab).addClass('active');
        $('#'+$toggleOpenTab).addClass('active');
    } else {
        sessionStorage.toggleTabProj{{Auth::id()}} = 'project';
        $('#tab-project').addClass('active');
        $('#project').addClass('active');
    }
    $('#addnew').click(function(e) {
        $curThis = $(this);
        e.preventDefault();
        $date = $curThis.closest("tr").find("input[name='date']").val();
        $hour = $curThis.closest("tr").find("input[name='hour']").val();
        $type = $curThis.closest("tr").find("select[name='typename']").val();
        $activity = $curThis.closest("tr").find("select[name='activity']").val();
        $note = $curThis.closest("tr").find("input[name='note']").val();
        $.post("/timesheet/new", {
            date: $date,
            hour: $hour,
            type: $type,
            activity: $activity,
            note: $note,
            project: {{ $project->id }},
        }, function(data){
            var $curTable = $curThis.closest("table");
            var json = data;
            if (json.success) {
                $curTable.find("tr:eq(1)").clone().removeAttr("data-id")
                .find("td:eq(0)").text($date).end()
                .find("td:eq(1)").text(json.hour).end()
                .find("td:eq(2)").text(json.type).end()
                .find("td:eq(3)").text(json.activity).end()
                .find("td:eq(4)").text($note).end()
                .find("td:eq(7)").html('<button class="btn btn-danger btn-xs fa fa-times deleterowp"></button>').end()
                .prependTo($curTable);
                $curThis.closest("tr").find("input").val("");
                $curThis.closest("tr").find("select").val("");
            }
        });
    });
    $('#addnewpurchase').click(function(e) {
        $curThis = $(this);
        e.preventDefault();
        $date = $curThis.closest("tr").find("input[name='date']").val();
        $hour = $curThis.closest("tr").find("input[name='hour']").val();
        $type = $curThis.closest("tr").find("select[name='typename']").val();
        $relation = $curThis.closest("tr").find("select[name='relation']").val();
        $note = $curThis.closest("tr").find("input[name='note']").val();
        $.post("/purchase/new", {
            date: $date,
            amount: $hour,
            type: $type,
            relation: $relation,
            note: $note,
            project: {{ $project->id }}
        }, function(data){
            var $curTable = $curThis.closest("table");
            var json = data;
            $curTable.find("tr:eq(1)").clone().removeAttr("data-id")
            .find("td:eq(0)").text($date).end()
            .find("td:eq(1)").text(json.relation).end()
            .find("td:eq(2)").html(json.amount).end()
            .find("td:eq(3)").text(json.type).end()
            .find("td:eq(4)").text($note).end()
            .find("td:eq(7)").html('<button class="btn btn-danger btn-xs fa fa-times deleterowp"></button>').end()
            .prependTo($curTable);
            $curThis.closest("tr").find("input").val("");
            $curThis.closest("tr").find("select").val("");
        });
    });
    $("body").on("click", ".deleterow", function(e){
        e.preventDefault();
        var $curThis = $(this);
        if($curThis.closest("tr").attr("data-id"))
            $.post("/timesheet/delete", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
                $curThis.closest("tr").hide("slow");
            }).fail(function(e) { console.log(e); });
    });
    $("body").on("click", ".deleterowp", function(e){
        e.preventDefault();
        var $curThis = $(this);
        if($curThis.closest("tr").attr("data-id"))
            $.post("/purchase/delete", {project: {{ $project->id }}, id: $curThis.closest("tr").attr("data-id")}, function(){
                $curThis.closest("tr").hide("slow");
            }).fail(function(e) { console.log(e); });
    });
    $('.dopay').click(function(e){
        if(confirm('Factuur betalen?')){
            $curThis = $(this);
            $curproj = $(this).attr('data-project');
            $curinv = $(this).attr('data-invoice');
            $.post("/invoice/pay", {project: {{ $project->id }}, id: $curinv, projectid: $curproj}, function(data){
                $rs = data;
                $curThis.replaceWith('Betaald op ' +$rs.payment);
            }).fail(function(e) { console.log(e); });
        }
    });
    $('.doinvclose').click(function(e){
        $curThis = $(this);
        $curproj = $(this).attr('data-project');
        $curinv = $(this).attr('data-invoice');
        $.post("/invoice/invclose", {project: {{ $project->id }}, id: $curinv, projectid: $curproj}, function(data){
            $rs = data;
            $curThis.replaceWith($rs.billing);
        }).fail(function(e) { console.log(e); });
    });
    $('#typename').change(function(e){
        $.get('/timesheet/activity/{{ $project->id }}/' + $(this).val(), function(data){
            $('#activity').prop('disabled', false).find('option').remove();
            $('#activity').prop('disabled', false).find('optgroup').remove();
            var groups = new Array();
            $.each(data, function(idx, item) {
                var index = -1;
                for(var i = 0, len = groups.length; i < len; i++) {
                    if (groups[i].group === item.chapter) {
                        groups[i].data.push({value: item.id, text: item.activity_name});
                        index = i;
                        break;
                    }
                }
                if (index == -1) {
                    groups.push({group: item.chapter, data: [{value: item.id, text: item.activity_name}]});
                }
            });
            $.each(groups, function(idx, item){
                $('#activity').append($('<optgroup>', {
                    label: item.group
                }));
                $.each(item.data, function(idx2, item2){
                    $('#activity').append($('<option>', {
                        value: item2.value,
                        text : item2.text
                    }));
                });
            });
        });
    });
    $('#projclose').datepicker().on('changeDate', function(e){
        $('#projclose').datepicker('hide');
        if(confirm('Project sluiten?')){
            $.post("/project/updateprojectclose", {
                date: e.date.toISOString(),
                project: {{ $project->id }}
            }, function(data){
                location.reload();
            });
        }
    });
    $('#wordexec').datepicker().on('changeDate', function(e){
        $('#wordexec').datepicker('hide');
        $.post("/project/updateworkexecution", {
            date: e.date.toISOString(),
            project: {{ $project->id }}
        }, function(data){
            location.reload();
        });
    });
    $('#wordcompl').datepicker().on('changeDate', function(e){
        $('#wordcompl').datepicker('hide');
        $.post("/project/updateworkcompletion", {
            date: e.date.toISOString(),
            project: {{ $project->id }}
        }, function(data){
            location.reload();
        });
    });

    $('#summernote').summernote({
        height: $(this).attr("data-height") || 200,
        toolbar: [
        ["style", ["bold", "italic", "underline", "strikethrough", "clear"]],
        ["para", ["ul", "ol", "paragraph"]],
        ["table", ["table"]],
        ["media", ["link", "picture", "video"]],
        ]
    });

    $('.summernote').summernote({
        height: $(this).attr("data-height") || 200,
        toolbar: [
        ["style", ["bold", "italic", "underline", "strikethrough", "clear"]],
        ["para", ["ul", "ol", "paragraph"]],
        ["table", ["table"]],
        ["media", ["link", "picture", "video"]],
        ]
    });

    $("[name='tax_reverse']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='use_equipment']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='use_subcontract']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='use_estimate']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='use_more']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='use_less']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='mail_reminder']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='hide_null']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});

    $("[name='hour_rate']").change(function() {
        if ($("[name='more_hour_rate']").val() == undefined || $("[name='more_hour_rate']").val() == '0,00')
            $("[name='more_hour_rate']").val($(this).val());
    });

    $('#btn-load-file').change(function() {
        $('#upload-file').submit();
    });
});
</script>

<div class="modal fade" id="myYouTube" tabindex="-1" role="dialog" aria-labelledby="mYouTubeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <iframe width="1280" height="720" src="https://www.youtube.com/embed/XSZFya8kovo" frameborder="0" allowfullscreen></iframe>

        </div>
    </div>
</div>

<div id="wrapper">

    <section class="container">

        @include('calc.wizard', array('page' => 'project'))

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

        @if ($offer_last)
        @if (CalculationEndresult::totalProject($project) != $offer_last->offer_total)
        <div class="alert alert-warning">
            <i class="fa fa-fa fa-info-circle"></i>
            De invoergegevens zijn gewijzigd ten op zichte van de laatste offerte
        </div>
        @endif
        @endif

        <h2><strong>Project</strong> {{$project->project_name}} &nbsp;&nbsp;<a class="fa fa-youtube-play yt-vid" href="javascript:void(0);" data-toggle="modal" data-target="#myYouTube"></a></h2>

        @if(!Relation::where('user_id','=', Auth::user()->id)->count())
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Let Op!</strong> Maak eerst een opdrachtgever aan onder <a href="/relation/new">nieuwe relatie</a>.
        </div>
        @endif

        <div class="tabs nomargin-top">

            <ul class="nav nav-tabs">
                <li id="tab-project">
                    <a href="#project" data-toggle="tab"><i class="fa fa-info"></i> Projectgegevens</a>
                </li>
                @if ($type->type_name != 'snelle offerte en factuur')
                <li id="tab-advanced">
                    <a href="#advanced" data-toggle="tab" data-toggle="tab"><i class="fa fa-sliders"></i> Extra opties</a>
                </li>
                <li id="tab-calc">
                    <a href="#calc" data-toggle="tab"><i class="fa fa-percent"></i> Uurtarief en Winstpercentages</a>
                </li>
                <li id="tab-doc">
                    <a href="#doc" data-toggle="tab"><i class="fa fa-cloud"></i> Documenten</a>
                </li>
                @endif
                @if ($share && $share->client_note )
                <li id="tab-communication">
                    <a href="#communication" data-toggle="tab">Communicatie opdrachtgever </a>
                </li>
                @endif
            </ul>

            <div class="tab-content">

                <div id="project" class="tab-pane">
                    <div class="pull-right">
                        <a href="javascript:void(0);" data-toggle="modal" data-target="#notepad" class="btn btn-primary"><i class="fa fa-file-text-o"></i>&nbsp;Kladblok</a>
                        <div class="btn-group" role="group">	
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Acties&nbsp;&nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a href="/project/{{ $project->id }}-{{ str_slug($project->project_name) }}/printoverview" target="new"><i class="fa fa-file-pdf-o"></i>&nbsp;Projectoverzicht</a></i>
                                <li><a href="/project/{{ $project->id }}-{{ str_slug($project->project_name) }}/packlist" target="new"><i class="fa fa-file-pdf-o"></i>&nbsp;Raaplijst</a></i>
                                <li><a href="/project/{{ $project->id }}-{{ str_slug($project->project_name) }}/copy"><i class="fa fa-copy"></i>&nbsp;Project kopieren</a></i>
                                @if (!$project->project_close)
                                <li><a href="#" id="projclose"><i class="fa fa-close"></i>&nbsp;Project sluiten</a></li>
                                @else
                                <li><a href="/project/{{ $project->id }}-{{ str_slug($project->project_name) }}/cancel" onclick="return confirm('Project laten vervallen?')"><i class="fa fa-times"></i>&nbsp;Project vervallen</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                        <form method="post" {!! $offer_last && $offer_last->offer_finish ? 'action="/project/update/note"' : 'action="/project/update"' !!}>
                            {!! csrf_field() !!}

                            <div class="modal fade" id="notepad" tabindex="-1" role="dialog" aria-labelledby="notepad" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="myModalLabel2">Project kladblok</h4>
                                        </div>

                                        <div class="modal-body">
                                            <div class="form-group ">
                                                <div class="col-md-12">
                                                    <textarea name="note" id="summernote" data-height="200" class="form-control">{{ Input::old('note') ? Input::old('note') : $project->note }}</textarea>

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <h4>Projectgegevens</h4>	
                            <h5><strong>Gegevens</strong></h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Projectnaam*</label>
                                        <input name="name" maxlength="50" id="name" type="text" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} value="{{ Input::old('name') ? Input::old('name') : $project->project_name }}" class="form-control" />
                                        <input type="hidden" name="id" id="id" value="{{ $project->id }}"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="contractor">Opdrachtgever*</label>
                                        @if (!Relation::find($project->client_id)->isActive())
                                        <select name="contractor" id="contractor" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} class="form-control pointer">
                                            @foreach (Relation::where('user_id','=', Auth::id())->get() as $relation)
                                            <option {{ $project->client_id==$relation->id ? 'selected' : '' }} value="{{ $relation->id }}">{{ RelationKind::find($relation->kind_id)->kind_name == 'zakelijk' ? ucwords($relation->company_name) : (Contact::where('relation_id','=',$relation->id)->first()['firstname'].' '.Contact::where('relation_id','=',$relation->id)->first()['lastname']) }}</option>
                                            @endforeach
                                        </select>
                                        @else
                                        <select name="contractor" id="contractor" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} class="form-control pointer">
                                            @foreach (Relation::where('user_id','=', Auth::id())->where('active',true)->get() as $relation)
                                            <option {{ $project->client_id==$relation->id ? 'selected' : '' }} value="{{ $relation->id }}">{{ RelationKind::find($relation->kind_id)->kind_name == 'zakelijk' ? ucwords($relation->company_name) : (Contact::where('relation_id','=',$relation->id)->first()['firstname'].' '.Contact::where('relation_id','=',$relation->id)->first()['lastname']) }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <h5><strong>Adresgegevens</strong></h5>
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="street">Straat*</label>
                                        <input name="street" id="street" maxlength="60" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} type="text" value="{{ Input::old('street') ? Input::old('street') : $project->address_street}}" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="address_number">Huis nr.*</label>
                                        <input name="address_number" maxlength="5" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="address_number" type="text" value="{{ Input::old('address_number') ? Input::old('address_number') : $project->address_number }}" class="form-control"/>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="zipcode">Postcode*</label>
                                        <input name="zipcode" maxlength="6" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="zipcode" type="text" maxlength="6" value="{{ Input::old('zipcode') ? Input::old('zipcode') : $project->address_postal }}" class="form-control"/>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="city">Plaats*</label>
                                        <input name="city" maxlength="35" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="city" type="text" value="{{ Input::old('city') ? Input::old('city'): $project->address_city }}" class="form-control"/>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="province">Provincie*</label>
                                        <select name="province" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="province" class="form-control pointer">
                                            @foreach (Province::all() as $province)
                                            <option {{ $project->province_id==$province->id ? 'selected' : '' }} value="{{ $province->id }}">{{ ucwords($province->province_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Land*</label>
                                        <select name="country" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="country" class="form-control pointer">
                                            @foreach (Country::all() as $country)
                                            <option {{ $project->country_id==$country->id ? 'selected' : '' }} value="{{ $country->id }}">{{ ucwords($country->country_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <h4>Projectstatussen</h4>

                            <div class="col-md-6">

                                <div class="row">
                                    <div class="col-md-4"><strong>Offerte stadium</strong></div>
                                    <div class="col-md-4"><strong></strong></div>
                                    <div class="col-md-4"><i>Laatste wijziging</i></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Calculatie gestart</div>
                                    <div class="col-md-4"><?php echo date('d-m-Y', strtotime(DB::table('project')->select('created_at')->where('id','=',$project->id)->get()[0]->created_at)); ?></div>
                                    <div class="col-md-4"><i><?php echo date('d-m-Y', strtotime(DB::table('project')->select('updated_at')->where('id','=',$project->id)->get()[0]->updated_at)); ?></i></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Offerte opgesteld</div>
                                    <div class="col-md-4"><?php if ($offer_last) { echo date('d-m-Y', strtotime(DB::table('offer')->select('created_at')->where('id','=',$offer_last->id)->get()[0]->created_at)); } ?></div>
                                    <div class="col-md-4"><i><?php if ($offer_last) { echo ''.date('d-m-Y', strtotime(DB::table('offer')->select('updated_at')->where('id','=',$offer_last->id)->get()[0]->updated_at)); } ?></i></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Opdracht <a data-toggle="tooltip" data-placement="bottom" data-original-title="Vul hier de datum in wanneer je opdracht hebt gekregen op je offerte. De calculatie slaat dan definitief dicht." href="javascript:void(0);"><i class="fa fa-info-circle"></i></a></div>
                                    <div class="col-md-4">{{ $offer_last && $offer_last->offer_finish ? date('d-m-Y', strtotime($offer_last->offer_finish)) : '' }}</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4"><strong>Opdracht stadium</strong></div>
                                    <div class="col-md-4"><strong></strong></div>
                                    <div class="col-md-4"><i>Laatste wijziging</i></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Start uitvoering <a data-toggle="tooltip" data-placement="bottom" data-original-title="Vul hier de datum in dat je met uitvoering bent begonnen" href="#"><i class="fa fa-info-circle"></i></a></div>
                                    <div class="col-md-4"><?php if ($project->project_close) { echo $project->work_execution ? date('d-m-Y', strtotime($project->work_execution)) : ''; }else{ if ($project->work_execution){ echo date('d-m-Y', strtotime($project->work_execution)); }else{ ?><a href="#" id="wordexec">Bewerk</a><?php } } ?></div>
                                    <div class="col-md-4"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">Opleverdatum <a data-toggle="tooltip" data-placement="bottom" data-original-title="Vul hier de datum in dat je het moet/wilt/verwacht opleveren" href="#"><i class="fa fa-info-circle"></i></a></div>
                                    <div class="col-md-4"><?php if ($project->project_close) { echo $project->work_completion ? date('d-m-Y', strtotime($project->work_completion)) : ''; }else{ if ($project->work_completion){ echo date('d-m-Y', strtotime($project->work_completion)); }else{ ?><a href="#" id="wordcompl">Bewerk</a><?php } } ?></div>
                                    <div class="col-md-4"></div>
                                </div>
                                @if ($project->use_estim)
                                <div class="row">
                                    <div class="col-md-4">Stelposten gesteld</div>
                                    <div class="col-md-4"><i>{{ $project->start_estimate ? date('d-m-Y', strtotime($project->start_estimate)) : '' }}</i></div>
                                    <div class="col-md-4"><i>{{ $project->update_estimate ? ''.date('d-m-Y', strtotime($project->update_estimate)) : '' }}</i></div>
                                </div>
                                @endif
                                @if ($project->use_more)
                                <div class="row">
                                    <div class="col-md-4">Meerwerk toegevoegd</div>
                                    <div class="col-md-4">{{ $project->start_more ? date('d-m-Y', strtotime($project->start_more)) : '' }}</div>
                                    <div class="col-md-4"><i>{{ $project->update_more ? ''.date('d-m-Y', strtotime($project->update_more)) : '' }}</i></div>
                                </div>
                                @endif
                                @if ($project->use_less)
                                <div class="row">
                                    <div class="col-md-4">Minderwerk verwerkt</div>
                                    <div class="col-md-4">{{ $project->start_less ? date('d-m-Y', strtotime($project->start_less)) : '' }}</div>
                                    <div class="col-md-4"><i>{{ $project->update_less ? ''.date('d-m-Y', strtotime($project->update_less)) : '' }}</i></div>
                                </div>
                                @endif
                                <br>

                                @if ($project->project_close)
                                <div class="row">
                                    <div class="col-md-4"><strong>Project gesloten</strong></div>
                                    <div class="col-md-4">{{ date('d-m-Y', strtotime($project->project_close)) }}</a></div>
                                </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-12" style="margin-top: 15px;">
                                    @if (!$project->project_close)
                                    <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
                                    @endif
                                </div>
                            </div>

                        </form>
                    </div>

                    @if ($type->type_name != 'snelle offerte en factuur')
                    <div id="calc" class="tab-pane">
                        <form method="post" action="/project/updatecalc">
                            {!! csrf_field() !!}
                            <input type="hidden" name="id" id="id" value="{{ $project->id }}"/>
                            <div class="row">
                                <div class="col-md-3"><h5><strong>Eigen uurtarief <a data-toggle="tooltip" data-placement="bottom" data-original-title="Geef hier uw uurtarief op wat door heel de calculatie gebruikt wordt voor dit project. Of stel deze in bij Voorkeuren om bij elk project te kunnen gebruiken." href="javascript:void(0);"><i class="fa fa-info-circle"></i></a></strong></h5></div>
                                <div class="col-md-1"></div>
                                @if ($type->type_name != 'regie')
                                <div class="col-md-2"><h5><strong>Calculatie</strong></h5></div>
                                <div class="col-md-2"><h5><strong>Meerwerk</strong></h5></div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-3"><label for="hour_rate">Uurtarief excl. BTW</label></div>
                                <div class="col-md-1"><div class="pull-right">&euro;</div></div>
                                @if ($type->type_name != 'regie')
                                <div class="col-md-2">
                                    <input name="hour_rate" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} type="text" value="{{ Input::old('hour_rate') ? Input::old('hour_rate') : number_format($project->hour_rate, 2,",",".") }}" class="form-control form-control-sm-number"/>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <input name="more_hour_rate" {{ $project->project_close ? 'disabled' : ($cntinv ? 'disabled' : '') }} id="more_hour_rate" type="text" value="{{ Input::old('more_hour_rate') ? Input::old('more_hour_rate') : number_format($project->hour_rate_more, 2,",",".") }}" class="form-control form-control-sm-number"/>
                                </div>
                            </div>

                            <h5><strong>Aanneming <a data-toggle="tooltip" data-placement="bottom" data-original-title="Geef hier uw winstpercentage op wat u over uw materiaal en overig wilt gaan rekenen. Of stel deze in bij Voorkeuren om bij elk project te kunnen gebruiken." href="javascript:void(0);"><i class="fa fa-info-circle"></i></a></strong></h5></strong></h5>
                            <div class="row">
                                <div class="col-md-3"><label for="profit_material_1">Winstpercentage materiaal</label></div>
                                <div class="col-md-1"><div class="pull-right">%</div></div>
                                @if ($type->type_name != 'regie')
                                <div class="col-md-2">
                                    <input name="profit_material_1" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="profit_material_1" type="number" min="0" max="200" value="{{ Input::old('profit_material_1') ? Input::old('profit_material_1') : $project->profit_calc_contr_mat }}" class="form-control form-control-sm-number"/>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <input name="more_profit_material_1" {{ $project->project_close ? 'disabled' : ($cntinv ? 'disabled' : '') }} id="more_profit_material_1" type="number" min="0" max="200" value="{{ Input::old('more_profit_material_1') ? Input::old('more_profit_material_1') : $project->profit_more_contr_mat }}" class="form-control form-control-sm-number"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3"><label for="profit_equipment_1">Winstpercentage overig</label></div>
                                <div class="col-md-1"><div class="pull-right">%</div></div>
                                @if ($type->type_name != 'regie')
                                <div class="col-md-2">
                                    <input name="profit_equipment_1" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="profit_equipment_1" type="number" min="0" max="200" value="{{ Input::old('profit_equipment_1') ? Input::old('profit_equipment_1') : $project->profit_calc_contr_equip }}" class="form-control form-control-sm-number"/>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <input name="more_profit_equipment_1" {{ $project->project_close ? 'disabled' : ($cntinv ? 'disabled' : '') }} id="more_profit_equipment_1" type="number" min="0" max="200" value="{{ Input::old('more_profit_equipment_1') ? Input::old('more_profit_equipment_1') : $project->profit_more_contr_equip }}" class="form-control form-control-sm-number"/>
                                </div>
                            </div>

                            <h5><strong>Onderaanneming <a data-toggle="tooltip" data-placement="bottom" data-original-title="Onderaanneming: Geef hier uw winstpercentage op wat u over het materiaal en overig van uw onderaanneming wilt gaan rekenen. Of stel deze in bij Voorkeuren om bij elk project te kunnen gebruiken." href="javascript:void(0);"><i class="fa fa-info-circle"></i></a></strong></h5></strong></h5>
                            <div class="row">
                                <div class="col-md-3"><label for="profit_material_2">Winstpercentage materiaal</label></div>
                                <div class="col-md-1"><div class="pull-right">%</div></div>
                                @if ($type->type_name != 'regie')
                                <div class="col-md-2">
                                    <input name="profit_material_2" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="profit_material_2" type="number" min="0" max="200" value="{{ Input::old('profit_material_2') ? Input::old('profit_material_2') : $project->profit_calc_subcontr_mat }}" class="form-control form-control-sm-number"/>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <input name="more_profit_material_2" {{ $project->project_close ? 'disabled' : ($cntinv ? 'disabled' : '') }} id="more_profit_material_2" type="number" min="0" max="200" value="{{ Input::old('more_profit_material_2') ? Input::old('more_profit_material_2') : $project->profit_more_subcontr_mat }}" class="form-control form-control-sm-number"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3"><label for="profit_equipment_2">Winstpercentage overig</label></div>
                                <div class="col-md-1"><div class="pull-right">%</div></div>
                                @if ($type->type_name != 'regie')
                                <div class="col-md-2">
                                    <input name="profit_equipment_2" {{ $project->project_close ? 'disabled' : ($offer_last && $offer_last->offer_finish ? 'disabled' : '') }} id="profit_equipment_2" type="number" min="0" max="200" value="{{ Input::old('profit_equipment_2') ? Input::old('profit_equipment_2') : $project->profit_calc_subcontr_equip }}" class="form-control form-control-sm-number"/>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <input name="more_profit_equipment_2" {{ $project->project_close ? 'disabled' : ($cntinv ? 'disabled' : '') }} id="more_profit_equipment_2" type="number" min="0" max="200" value="{{ Input::old('more_profit_equipment_2') ? Input::old('more_profit_equipment_2') : $project->profit_more_subcontr_equip }}" class="form-control form-control-sm-number"/>
                                </div>
                            </div>
                            <br/>
                            <div class="row">
                                <div class="col-md-12">
                                    @if (!$project->project_close)
                                    <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                    <div id="advanced" class="tab-pane">

                        <form method="POST" action="/project/updateoptions">
                            {!! csrf_field() !!}
                            <input type="hidden" name="id" id="id" value="{{ $project->id }}"/>

                            @if ($type->type_name != 'regie')
                            <div class="row">
                                <div class="col-md-6">	
                                    <div class="col-md-3">
                                        <label for="type"><b>BTW verlegd</b></label>
                                        <div class="form-group">
                                            <input name="tax_reverse" disabled type="checkbox" {{ $project->tax_reverse ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-9" style="padding-top:30px;">
                                        <p>Een project zonder btw bedrag invoeren.</p>
                                        <ul>
                                            <li>Kan na aanmaken project niet ongedaan gemaakt worden</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">	
                                    <div class="col-md-3">
                                        <label for="type"><b>Stelposten</b></label>
                                        <div class="form-group">
                                            <input name="use_estimate" {{ $project->project_close ? 'disabled' : ($disable_estim ? 'disabled' : '') }} type="checkbox" {{ $project->use_estimate ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-9"  style="padding-top:30px;">		
                                        <p>Voeg stelposten toe aan je calculatie.</p>
                                        <ul>
                                            <li>Definitief te maken voor factuur na opdracht</li>
                                            <li>Uit te zetten indien ongebruikt</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-3">
                                        <label for="type"><b>Onderaanneming</b></label>
                                        <div class="form-group">
                                            <input name="use_subcontract" type="checkbox" {{ $project->project_close ? 'disabled' : ($project->use_subcontract ? 'disabled' : '') }} {{ $project->use_subcontract ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-9"  style="padding-top:30px;">
                                        <p>Voeg onderaanneming toe aan je {{ $type->type_name == 'regie' ? 'regiewerk' : 'calculatie' }}.</p>
                                        <ul>
                                            <li>Kan na toevoegen niet ongedaan gemaakt worden</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-3">
                                        <label for="type"><b>Overige</b></label>
                                        <div class="form-group">
                                            <input name="use_equipment" type="checkbox" {{ $project->project_close ? 'disabled' : ($project->use_equipment ? 'disabled' : '') }} {{ $project->use_equipment ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-9" style="padding-top:30px;">
                                        <p>Voeg naast arbeid en materiaal een extra niveau toe aan je {{ $type->type_name == 'regie' ? 'regiewerk' : 'calculatie' }}.</p>
                                        <ul>
                                            <li>Bijvoorbeeld voor <i>materieel</i></li>
                                            <li>Kan na toevoegen niet ongedaan gemaakt worden</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @if ($type->type_name != 'regie')
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-3">
                                        <label for="type"><b>Meerwerk</b></label>
                                        <div class="form-group">
                                            <input name="use_more" type="checkbox" {{ $project->project_close ? 'disabled' : ($disable_more ? 'disabled' : '') }} {{ $project->use_more ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-9" style="padding-top:30px;">
                                        <p>Voeg meerwerk toe aan je project.</p>
                                        <ul>
                                            <li>Pas invulbaar na opdracht</li>
                                            <li>Uit te zetten indien ongebruikt</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-3">
                                        <label for="type"><b>Minderwerk</b></label>
                                        <div class="form-group">
                                            <input name="use_less" type="checkbox" {{ $project->project_close ? 'disabled' : ($disable_less ? 'disabled' : '') }} {{ $project->use_less ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-9" style="padding-top:30px;">
                                        <p>Voeg minderwerk toe aan je prpject.</p>
                                        <ul>
                                            <li>Pas invulbaar na opdracht</li>
                                            <li>Uit te zetten indien ongebruikt</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <br/>
                            <div class="row">
                                <div class="col-md-12">
                                    @if (!$project->project_close)
                                    <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="communication" class="tab-pane">
                        <div class="form-group">
                            <div class="col-md-9">
                                <form method="POST" action="/project/update/communication" accept-charset="UTF-8">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="project" value="{{ $project->id }}"/>

                                    <h5><strong>Vraag opmerkingen van je opdrachtgever </strong><a data-toggle="tooltip" data-placement="bottom" data-original-title="Alleen mogelijk wanneer een offerte verzonden is per e-mail op de offerte pagina." href="javascript:void(0);"><i class="fa fa-info-circle"></i></a></h5>
                                    <div class="row">
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <div class="white-row well">
                                                    {!!  $share ? $share->client_note : ''!!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5><strong>Jouw reactie</strong></h5>
                                    <div class="row">
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <textarea name="user_note" id="user_note" rows="10" class="summernote form-control">{{ $share ? $share->user_note : ''}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="btn btn-primary"><i class="fa fa-check"></i> Verzenden</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                    <h5><strong>Gegevens van uw relatie</strong></h5>
                                </div>
                                <div class="row">
                                    <label>Opdrachtgever </label>
                                    <?php $relation = Relation::find($project->client_id); ?>
                                    @if (!$relation->isActive())
                                    <span> {{ RelationKind::find($relation->kind_id)->kind_name == 'zakelijk' ? ucwords($relation->company_name) : (Contact::where('relation_id','=',$relation->id)->first()['firstname'].' '.Contact::where('relation_id','=',$relation->id)->first()['lastname']) }}</span>
                                    @else
                                    <span> {{ RelationKind::find($relation->kind_id)->kind_name == 'zakelijk' ? ucwords($relation->company_name) : (Contact::where('relation_id','=',$relation->id)->first()['firstname'].' '.Contact::where('relation_id','=',$relation->id)->first()['lastname']) }}</span>
                                    @endif
                                </div>
                                <div class="row">
                                    <label for="name">Straat</label>
                                    <span>{{ $relation->address_street }} {{ $relation->address_number }}</span>
                                </div>
                                <div class="row">
                                    <label for="name">Postcode</label>
                                    <span>{{ $relation->address_postal }}</span>
                                </div>
                                <div class="row">
                                    <label for="name">Plaats</label>
                                    <span>{{ $relation->address_city }}</span>
                                </div>

                                <?php
                                $contact=Contact::where('relation_id',$relation->id)->first();
                                ?>
                                <div class="row">
                                    <label for="name">Contactpersoon</label>
                                    <span>{{ $contact->getFormalName() }}</span>
                                </div>
                                <div class="row">
                                    <label for="name">Telefoon</label>
                                    <span>{{ $contact->mobile }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="doc" class="tab-pane">

                        @if (!$project->project_close)
                        <div class="pull-right">
                            <form id="upload-file" action="/project/document/upload" method="post" enctype="multipart/form-data">
                                {!! csrf_field() !!}
                                <label class="btn btn-primary btn-file">
                                    <i class="fa fa-cloud-upload"></i>&nbsp;Upload document <input type="file" name="projectfile" id="btn-load-file" style="display: none;">
                                </label>
                                <input type="hidden" value="{{ $project->id }}" name="project" />
                            </form>
                        </div>
                        @endif

                        <h4>Projectdocumenten</h4>

                        <div class="white-row">

                            <div id="cartContent">
                                <div class="item head">
                                    <span class="cart_img" style="width:45px;"></span>
                                    <span class="product_name fsize13 bold">Filename</span>
                                    <span class="remove_item fsize13 bold" style="width: 120px;"></span>
                                    <span class="total_price fsize13 bold">Grootte</span>
                                    <span class="qty fsize13 bold">Geupload</span>
                                    <div class="clearfix"></div>
                                </div>
                                <?php $i=0; ?>
                                @foreach(Resource::where('project_id', $project->id)->get() as $file)
                                <?php $i++; ?>
                                <div class="item">
                                    <div class="cart_img" style="width:45px;"><a href="/res-{{ $file->id }}/download"><i class="fa {{ $file->fa_icon() }} fsize20"></i></a></div>
                                    <a href="/res-{{ $file->id }}/download" class="product_name">{{ $file->resource_name }}</a>
                                    @if (!$project->project_close)
                                    <a href="/res-{{ $file->id }}/delete" class="btn btn-danger btn-xs" style="float: right;margin: 10px;">Verwijderen</a>
                                    @else
                                    <a href="#" class="btn btn-danger btn-xs disabled" style="float: right;margin: 10px;">Verwijderen</a>
                                    @endif
                                    <div class="total_price"><span>{{ round($file->file_size/1024) }}</span> Kb</div>
                                    <div class="qty">{{ $file->created_at->format("d-m-Y") }}</div>
                                    <div class="clearfix"></div>
                                </div>
                                @endforeach
                                @if (!$i)
                                <div class="item">
                                    <div style="width: 100%;text-align: center;" class="product_name">Er zijn nog geen documenten bij dit project</div>
                                </div>
                                @endif

                                <div class="clearfix"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </section>

</div>
@stop
<?php } ?>