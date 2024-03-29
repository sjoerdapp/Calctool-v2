<?php

use BynqIO\Dynq\Models\Relation;
use BynqIO\Dynq\Models\Contact;
use BynqIO\Dynq\Models\DeliverTime;
use BynqIO\Dynq\Models\Valid;

$relation = Relation::find($project->client_id);
$relation_self = Relation::find(Auth::user()->self_id);
?>

@push('style')
<link media="all" type="text/css" rel="stylesheet" href="/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css">
@endpush

@push('scripts')
<script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="/plugins/jquery.number.min.js"></script>
@endpush

@push('jsinline')
<script type="text/javascript">
$(document).ready(function() {
    $("[type='checkbox'").bootstrapSwitch({onText: 'Ja', offText: 'Nee'});
    $("[name=terms]").change(function() {
        if ($(this).val() < 2) {
            $("[name=amount]").prop('disabled', true);
        } else {
            $("[name=amount]").prop('disabled', false);
        }
    });

    $("[name=amount]").number({!! \BynqIO\Dynq\Services\FormatService::monetaryJS('true') !!});

    /* Remove contents from modal on close */
    $(document).on('hidden.bs.modal', function (e) {
        $(e.target).removeData('bs.modal');
    });
});
</script>
@endpush

@section('component_buttons')
<div class="pull-right">
    <form action="/quotation/new" method="post">
        {!! csrf_field() !!}
        <input type="hidden" name="project" value="{{ $project->id }}" />
        @foreach(Input::all() as $input => $value)
        <input type="hidden" name="{{ $input }}" value="{{ $value }}" />
        @endforeach
        <button class="btn btn-primary" {{ Input::has('ts') ?: 'disabled' }}><i class="fa fa-check-square-o"></i>Offereren</button>
    </form>
</div>
@endsection

<div class="modal fade" id="asyncModal" tabindex="-1" role="dialog" aria-labelledby="asyncModal" aria-hidden="true">
    <div class="modal-dialog {{-- modal-lg --}} {{-- modal-sm --}}">
        <div class="modal-content"></div>
    </div>
</div>

<form action="" method="get" class="white-row">
    <input type="hidden" name="ts" value="{{ time() }}">
    <input type="hidden" name="conditions" value="{{ Input::has('conditions') ? Input::get('conditions') : '' }}"/>
    <input type="hidden" name="pretext" value="{{ Input::has('pretext') ? Input::get('pretext') : '' }}"/>
    <input type="hidden" name="posttext" value="{{ Input::has('posttext') ? Input::get('posttext') : '' }}"/>

    <h3 class="page-header nomargin-top">Instellingen</h3>

    {{-- Proposal options --}}
    <div class="row">
        <div class="form-group">

            <div class="col-md-5">
                <label>Termijnen</label>
                <input type="number" class="form-control" name="terms" min="1" max="99" value="{{ Input::has('terms') ? Input::get('terms') : '1' }}">
            </div>

            <div class="col-md-7">
                <label>Aanbetalingsbedrag</label>
                <input type="text" class="form-control" name="amount" {{ Input::has('terms') && Input::get('terms') > 1 ?: 'disabled' }} value="{{ Input::has('amount') ? Input::get('amount') : '0' }}">
            </div>

            <div class="col-md-12 col-sm-6">
                <label>Contactpersoon</label>
                <select class="form-control" name="contact_to">
                    <option value="">Selecteer</option>
                    @foreach (Contact::where('relation_id',$relation->id)->get() as $contact)
                    <option {{ Input::has('contact_to') ? (Input::get('contact_to') == $contact->id ? 'selected' : '') : '' }} value="{{ $contact->id }}">{{ Contact::find($contact->id)->getFormalName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 col-sm-6">
                <label>Namens</label>
                <select class="form-control" name="contact_from">
                    <option value="">Selecteer</option>
                    @foreach (Contact::where('relation_id',$relation_self->id)->get() as $contact)
                    <option {{ Input::has('contact_from') ? (Input::get('contact_from') == $contact->id ? 'selected' : '') : '' }} value="{{ $contact->id }}">{{ $contact->firstname . ' ' . $contact->lastname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-sm-6">
                <label>Oplevering</label>
                <select class="form-control" name="deliver">
                    <option value="">Selecteer</option>
                    @foreach (DeliverTime::all() as $deliver)
                    <option {{ Input::has('deliver') ? (Input::get('deliver') == $deliver->id ? 'selected' : '') : '' }} value="{{ $deliver->id }}">{{ $deliver->delivertime_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-sm-6">
                <label>Offerte geldig</label>
                <select class="form-control" name="valid">
                    <option value="">Selecteer</option>
                    @foreach (Valid::all() as $valid)
                    <option {{ Input::has('valid') ? (Input::get('valid') == $valid->id ? 'selected' : '') : '' }} value="{{ $valid->id }}">{{ $valid->valid_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-offset-0 col-sm-12">
                <div class="checkbox">
                    <input name="separate_subcon" type="checkbox" {{ Input::has('separate_subcon') ? 'checked' : '' }}><span style="margin-left:10px;">Onderaanneming specificeren</span>
                </div>
            </div>
        </div>
    </div>
    {{-- /Proposal options --}}

    {{-- Proposal texts --}}
    <h3 class="page-header nomargin-top">Teksten</h3>
    <div class="row">
        <div class="col-sm-offset-0 col-sm-12" style="margin-bottom: 10px;">
            <a href="/inline/inline_edit?selector=pretext&title=Aanheftekst&package=component.modal" style="width:100px" data-toggle="modal" data-target="#asyncModal" class="btn btn-sm btn-default">Aanheftekst</a><span style="margin-left:10px;">Tekst na de aanhef</span>
        </div>
        <div class="col-sm-offset-0 col-sm-12" style="margin-bottom: 10px;">
            <a href="/inline/inline_edit?selector=conditions&title=Bepalingen&package=component.modal" style="width:100px" data-toggle="modal" data-target="#asyncModal" class="btn btn-sm btn-default">Bepalingen</a><span style="margin-left:10px;">Voeg extra bepalingen toe</span>
        </div>
        <div class="col-sm-offset-0 col-sm-12" style="margin-bottom: 10px;">
            <a href="/inline/inline_edit?selector=posttext&title=Sluittekst&package=component.modal" style="width:100px" data-toggle="modal" data-target="#asyncModal" class="btn btn-sm btn-default">Sluittekst</a><span style="margin-left:10px;">Geef sluittekst op</span>
        </div>
    </div>
    {{-- /Proposal texts --}}

    <h3 class="page-header nomargin-top">Bijlagen</h3>

    {{-- Proposal pages --}}
    <div class="row">
        <div class="form-group">

            <div class="col-sm-offset-0 col-sm-12">
                <div class="checkbox">
                    <input name="display_specification" type="checkbox" {{ Input::has('display_specification') ? 'checked' : '' }}><span style="margin-left:10px;">Specificeren op onderdelen</span>
                </div>
            </div>
            <div class="col-sm-offset-0 col-sm-12">
                <div class="checkbox">
                    <input name="display_worktotals" type="checkbox" {{ Input::has('display_worktotals') ? 'checked' : '' }}><span style="margin-left:10px;">Totaalkosten per werkzaamheid</span>
                </div>
            </div>
            <div class="col-sm-offset-0 col-sm-12">
                <div class="checkbox">
                    <input name="display_description" type="checkbox" {{ Input::has('display_description') ? 'checked' : '' }}><span style="margin-left:10px;">Omschrijving per werkzaamheid</span>
                </div>
            </div>

            <div class="col-md-12">
                <label>&nbsp;</label>
                <button class="btn btn-primary fullwidth"><i class="fa fa-refresh" aria-hidden="true"></i>Bijwerken</button>
            </div>
        </div>
    </div>
    {{-- /Proposal pages --}}

</form>
