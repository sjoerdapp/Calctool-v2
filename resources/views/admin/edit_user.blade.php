@extends('layout.master')

@section('content')

@section('title', 'Gebruiker bewerken')

@push('style')
<link media="all" type="text/css" rel="stylesheet" href="/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css">
<link media="all" type="text/css" rel="stylesheet" href="/components/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css">
@endpush

@push('scripts')
<script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="/plugins/summernote/summernote.min.js"></script>
<script src="/components/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
@endpush

<?php
$allevents = false;
if (Input::get('allevents') == 1) {
    $allevents = true;
}
$user = \BynqIO\Dynq\Models\User::find(Route::input('user_id'));
if (!$user){ ?>
@section('content')
<div id="wrapper">
    <section class="container">
        <div class="alert alert-danger">
            <i class="fa fa-frown-o"></i>
            <strong>Fout</strong>
            Deze gebruiker bestaat niet
        </div>
    </section>
</div>
@stop
<?php }else{ ?>
<script type="text/javascript">
$(document).ready(function() {
    $('#tab-overview').click(function(e){
        sessionStorage.toggleAdminEditUser{{Auth::id()}} = 'overview';
    });
    $('#tab-userdata').click(function(e){
        sessionStorage.toggleAdminEditUser{{Auth::id()}} = 'userdata';
    });
    $('#tab-admin').click(function(e){
        sessionStorage.toggleAdminEditUser{{Auth::id()}} = 'admin';
    });
    $('#tab-audit').click(function(e){
        sessionStorage.toggleAdminEditUser{{Auth::id()}} = 'audit';
    });
    if (sessionStorage.toggleAdminEditUser{{Auth::id()}}){
        $toggleOpenTab = sessionStorage.toggleAdminEditUser{{Auth::id()}};
        $('#tab-'+$toggleOpenTab).addClass('active');
        $('#'+$toggleOpenTab).addClass('active');
    } else {
        sessionStorage.toggleAdminEditUser{{Auth::id()}} = 'overview';
        $('#tab-overview').addClass('active');
        $('#overview').addClass('active');
    }
    function prefixURL(field) {
        var cur_val = $(field).val();
        if (!cur_val)
            return;
        var ini = cur_val.substring(0,4);
        if (ini == 'http')
            return;
        else {
            if (cur_val.indexOf("www") >=0) {
                $(field).val('http://' + cur_val);
            } else {
                $(field).val('http://www.' + cur_val);
            }
        }
    }
    $('#website').blur(function(e) {
        prefixURL($(this));
    });
    $('#iban').blur(function() {
        if (! IBAN.isValid($(this).val()) ) {
            $(this).parent().addClass('has-error');
        } else {
            $(this).parent().removeClass('has-error');
        }
    });

    $("[name='toggle-api']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});
    $("[name='toggle-active']").bootstrapSwitch({onText: 'Ja',offText: 'Nee'});

     $('.summernote').summernote({
            height: $(this).attr("data-height") || 200,
            toolbar: [
                ["style", ["bold", "italic", "underline", "strikethrough", "clear"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["table", ["table"]],
                ["media", ["link", "picture", "video"]],
                ["misc", ["codeview"]]
            ]
        })

    $('.datepick').datepicker();
});
</script>
<div id="wrapper">

    <section class="container">

        <div class="col-md-12">

            <div>
                <ol class="breadcrumb">
                    <li><a href="/">Dashboard</a></li>
                    <li><a href="/admin">Admin Dashboard</a></li>
                    <li><a href="/admin/user">Gebruikers</a></li>
                    <li class="active">{{ $user->username }}</li>
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
                        <strong>Fout</strong>
                        @foreach ($errors->all() as $error)
                        {{ $error }}
                        @endforeach
                    </div>
                    @endif

                    <div class="pull-right">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opties<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                @if (!$user->isAdmin())
                                <li><a href="/admin/user-{{ $user->id }}/switch">Gebruiker overnemen</a></li>
                                @endif
                                @if ($user->active)
                                <li><a href="/admin/user-{{ $user->id }}/validation">Validatie project laden</a></li>
                                <li><a href="/admin/user-{{ $user->id }}/stabu">STABU project laden</a></li>
                                <li><a href="/admin/message?user={{ $user->id }}">Bericht sturen</a></li>
                                <li><a href="/admin/user-{{ $user->id }}/passreset">Stuur wachtwoord reset link</a></li>
                                @if (!$user->isAdmin())
                                <li><a href="/admin/user-{{ $user->id }}/passdefault">Standaard wachtwoord</a></li>
                                @endif
                                <li><a href="/admin/payment?user_id={{ $user->id }}">Transacties</a></li>
                                @if ($user->payment_subscription_id)
                                <li><a href="/admin/user-{{ $user->id }}/subscription/cancel">Incasso stoppen</a></li>
                                @endif
                                @endif
                                @if (Auth::user()->isSystem() && Auth::id() != $user->id)
                                <li><a href="/admin/user-{{ $user->id }}/login">Als gebruiker inloggen</a></li>
                                <li><a href="/admin/user-{{ $user->id }}/purge">Definitef verwijderen</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <h2><strong>Gebruiker</strong> {{ $user->username }}</h2>

                    <div class="tabs nomargin-top">

                        <ul class="nav nav-tabs">
                            <li id="tab-overview">
                                <a href="#overview" data-toggle="tab">Overzicht</a>
                            </li>
                            <li id="tab-userdata">
                                <a href="#userdata" data-toggle="tab">Gebruikersgegevens</a>
                            </li>
                            <li id="tab-admin">
                                <a href="#admin" data-toggle="tab">Adminlog</a>
                            </li>
                            <li id="tab-audit">
                                <a href="#audit" data-toggle="tab">Eventlog</a>
                            </li>
                        </ul>


                        <div class="tab-content">
                            <div id="overview" class="tab-pane">
                                <section class="paddings">
                                    <div class="row text-center countTo">
                                        <div class="col-md-3">
                                            <strong data-to="1244">{{ $user->projects()->whereNull('project_close')->count() . '/' . $user->projects()->count() }}</strong>
                                            <label>Projecten</label>
                                        </div>
                                        <div class="col-md-3">
                                            <strong data-to="67">{{ $user->relations()->count() }}</strong>
                                            <label>Relaties</label>
                                        </div>
                                        <div class="col-md-3">
                                            <strong data-to="67">{{ $user->relations()->count() }}</strong>
                                            <label>Contacten</label>
                                        </div>
                                        <div class="col-md-3">
                                            <strong data-to="32">{{ $user->login_count }}</strong>
                                            <label>Logins</label>
                                        </div>
                                    </div>
                                </section>

                                <div style="overflow:overlay;">
                                    <div class="col-md-6">

                                        <div class="row">
                                            <div class="col-md-12"><h3>Details</h3></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">Activiteit</div>
                                            <div class="col-md-6">{{ $user->currentStatus() }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">Waardering</div>
                                            <div class="col-md-6">
                                                @for ($i = 0; $i < ($user->login_count/10); $i++)
                                                <i class="fa fa-star" aria-hidden="true"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12"><h3>Projectstadium</h3></div>
                                        </div>

                                        @if (0)
                                        <div class="row">
                                            <div class="col-md-4">Start uitvoering <a data-toggle="tooltip" data-placement="bottom" data-original-title="Vul hier de datum in dat je met uitvoering bent begonnen" href="#"><i class="fa fa-info-circle"></i></a></div>
                                            <div class="col-md-4"><?php if ($project->project_close) { echo $project->work_execution ? date('d-m-Y', strtotime($project->work_execution)) : ''; }else{ if ($project->work_execution){ echo date('d-m-Y', strtotime($project->work_execution)); }else{ ?><a href="#" id="wordexec">Bewerk</a><?php } } ?></div>
                                            <div class="col-md-4"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">Opleverdatum <a data-toggle="tooltip" data-placement="bottom" data-original-title="Vul hier de datum in dat je het moet/wilt/verwacht opleveren" href="#"><i class="fa fa-info-circle"></i></a></div>
                                            <div class="col-md-4"><?php if ($project->project_close) { echo $project->work_completion ? date('d-m-Y', strtotime($project->work_completion)) : ''; }else{ if ($project->work_completion){ echo date('d-m-Y', strtotime($project->work_completion)); }else{ ?><a href="#" id="wordcompl">Bewerk</a><?php } } ?></div>
                                            <div class="col-md-4"><i class="fa fa-check" aria-hidden="true"></i></div>
                                        </div>
                                        @endif

                                        @if (0)
                                        <div class="row">
                                            <div class="col-md-6"><i class="fa fa-chevron-right" aria-hidden="true"></i> Stelposten Stellen</div>
                                            <div class="col-md-6"><i class="fa fa-check" aria-hidden="true"></i> Afgerond op {{ $carbon::now()->toFormattedDateString() }}<i>{{ $project->start_estimate ? date('d-m-Y', strtotime($project->start_estimate)) : '' }}</i></div>
                                        </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-md-6"><i class="fa fa-chevron-right" aria-hidden="true"></i> Offerte</div>
                                            <div class="col-md-6"><?php //if ($offer_last) { echo $offer_last->created_at->toFormattedDateString(); } else { echo '-'; } ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6"><i class="fa fa-chevron-right" aria-hidden="true"></i> Opdracht</div>
                                            <div class="col-md-6">{{-- $offer_last && $offer_last->offer_finish ? date('d-m-Y', strtotime($offer_last->offer_finish)) : '-' --}}</div>
                                        </div>

                                        @if (0)
                                        <div class="row">
                                            <div class="col-md-6"><i class="fa fa-chevron-right" aria-hidden="true"></i> Meerwerk</div>
                                            <div class="col-md-6">{{ $project->start_more ? date('d-m-Y', strtotime($project->start_more)) : '-' }}</div>
                                        </div>
                                        @endif
                                        @if (0)
                                        <div class="row">
                                            <div class="col-md-6"><i class="fa fa-chevron-right" aria-hidden="true"></i> Minderwerk</div>
                                            <div class="col-md-6">{{ $project->start_less ? date('d-m-Y', strtotime($project->start_less)) : '-' }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div id="userdata" class="tab-pane">

                                <form method="POST" action="" accept-charset="UTF-8">
                                    {!! csrf_field() !!}

                                    <h4 class="company">Gebruikersgegevens</h4>
                                    <div class="row company">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="company_name">Gebruikersnaam*</label>
                                                <input name="username" {{ $user->isAdmin() ? 'readonly' : '' }} id="username" type="text" value="{{ old('username') ? old('username') : $user->username}}" class="form-control" />
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="user_type">Gebruikerstype</label>
                                                <select name="type" id="type" class="form-control pointer">
                                                    <?php
                                                    foreach (\BynqIO\Dynq\Models\UserType::all() as $type) {
                                                        if ($type->user_type == 'system')
                                                            continue;
                                                    ?>
                                                    <option {{ $user->user_type==$type->id ? 'selected' : '' }} value="{{ $type->id }}">{{ ucwords($type->user_type) }}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="customer_id">Customer ID</label>
                                                <input name="customer_id" disabled class="form-control" value="{{ $user->payment_customer_id }}">
                                            </div>
                                        </div>

                                    </div>

                                    <h4>Contactgegevens</h4>
                                    <div class="row">

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="firstname">Voornaam</label>
                                                <input name="firstname" id="firstname" type="text" value="{{ old('firstname') ? old('firstname') : $user->firstname }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="lastname">Achternaam</label>
                                                <input name="lastname" id="lastname" type="text" value="{{ old('lastname') ? old('lastname') : $user->lastname }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="mobile">Mobiel</label>
                                                <input name="mobile" id="mobile" type="number" value="{{ old('mobile') ? old('mobile') : $user->mobile}}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="telephone">Telefoonnummer</label>
                                                <input name="telephone" id="telephone" type="number" value="{{ old('telephone') ? old('telephone') : $user->phone }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="email">Email*</label>
                                                <input name="email" {{ $user->isAdmin() ? 'readonly' : '' }} id="email" type="email" value="{{ old('email') ? old('email') : $user->email }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="website">Website</label>
                                                <input name="website" id="website" type="url" value="{{ old('website') ? old('website') : $user->website }}" class="form-control"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="gender" style="display:block;">Geslacht</label>
                                                <select name="gender" id="gender" class="form-control pointer">
                                                    <option value="-1">Selecteer</option>
                                                    <option {{ $user->gender=='M' ? 'selected' : '' }} value="M">Man</option>
                                                    <option {{ $user->gender=='V' ? 'selected' : '' }} value="V">Vrouw</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                    @if ($user->isOnline())
                                    <h4>Huidige sessie</h4>
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="website">Huidige locatie</label>
                                                <input name="location" id="location" disabled value="{{ $user->current_url }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="address_city">Logins</label>
                                                <input type="text" value="{{ $user->login_count }}" disabled class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address_city">Actief</label>
                                                <input type="text" value="Online" disabled class="form-control"/>
                                            </div>
                                        </div>

                                    </div>
                                    @else
                                    <h4>Laatste sessie</h4>
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="website">Laatste locatie</label>
                                                <input name="location" id="location" disabled value="{{ $user->current_url }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="address_city">Logins</label>
                                                <input type="text" value="{{ $user->login_count }}" disabled class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address_city">Actief</label>
                                                <input type="text" value="{{ $user->currentStatus() }}" disabled class="form-control"/>
                                            </div>
                                        </div>

                                    </div>
                                    @endif

                                    <h4>Overig</h4>
                                    <div class="row">

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="iban">Account verloopdatum</label>
                                                <input name="expdate" id="expdate" type="date" value="{{ old('expdate') ? old('expdate') : date('Y-m-d', strtotime($user->expiration_date)) }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="iban">Activeringsdatum</label>
                                                <input name="confirmdate" id="confirmdate" type="date" value="{{ old('confirmdate') ? old('confirmdate') : date('Y-m-d', strtotime($user->confirmed_mail)) }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <!-- <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="iban">Blokkeringsdatum</label>
                                                <input name="bandate" id="bandate" type="date" value="{{ ($user->banned ? date('Y-m-d', strtotime($user->banned)) : '') }}" class="form-control"/>
                                            </div>
                                        </div> -->
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="gender" style="display:block;">Gebruikersgroep</label>
                                                <select name="group" id="group" class="form-control pointer">
                                                    @foreach (\BynqIO\Dynq\Models\UserGroup::all() as $group)
                                                    <option {{ $user->user_group==$group->id ? 'selected' : '' }} value="{{ $group->id }}">{{ ucwords($group->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="tag">Gebruikerstag</label>
                                                <select name="tag" id="tag" class="form-control pointer">
                                                    <option value="-1">Geen</option>
                                                    <?php
                                                    foreach (\BynqIO\Dynq\Models\UserTag::all() as $tag) {
                                                    ?>
                                                    <option {{ $user->user_tag_id==$tag->id ? 'selected' : '' }} value="{{ $tag->id }}">{{ $tag->name }}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="toggle-api" style="display:block;">API toegang</label>
                                                <input {{ $user->isAdmin() ? 'disabled' : '' }} name="toggle-api" type="checkbox" {{ $user->api_access ? 'checked' : '' }}>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="toggle-active" style="display:block;">Actief</label>
                                                <input name="toggle-active" type="checkbox" {{ $user->active ? 'checked' : '' }}>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address_city">Referral at signup</label>
                                                <input type="text" value="{{ $user->referral_url }}" disabled class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address_city">IP adres</label>
                                                <input type="text" value="{{ $user->ip }}" disabled class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mandate">Mandate ID</label>
                                                <input name="mandate" type="text" disabled value="{{ $user->payment_subscription_id }}" class="form-control"/>
                                            </div>
                                        </div>

                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="api">Referral link</label>
                                                <input name="api" id="api" type="text" readonly="readonly" value="{{ url('/') }}/register?client_referer={{ $user->referral_key }}" class="form-control"/>
                                            </div>
                                        </div>

                                    </div>

                                    <h4 class="hidden-xs">Opmerkingen <a data-toggle="tooltip" data-placement="bottom" data-original-title="Niet zichtbaar voor de gebruiker." href="javascript:void(0);"><i class="fa fa-info-circle"></i></a></h4>
                                    <div class="row hidden-xs">
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <textarea name="note" id="note" rows="10" class="summernote form-control">{{ old('note') ? old('note') : $user->note }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <h4 class="hidden-xs">Kladblok van de gebruiker</h4>
                                    <div class="row hidden-xs">
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <textarea name="notepad" id="note" rows="10" class="summernote form-control">{{ old('notepad') ? old('notepad') : $user->notepad }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
                                        </div>
                                    </div>

                                </form>
                            </div>

                            <div id="admin" class="tab-pane">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="col-md-2 hidden-sm hidden-xs">Datum</th>
                                            <th class="col-md-7">Actie</th>
                                            <th class="col-md-2">Label</th>
                                            <th class="col-md-1"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach (\BynqIO\Dynq\Models\AdminLog::where('user_id', $user->id)->orderBy('created_at','asc')->get() as $rec)
                                        <tr>
                                            <td class="col-md-2">{{ date('d-m-Y', strtotime(DB::table('admin_log')->select('created_at')->where('id',$rec->id)->get()[0]->created_at)) }}</td>
                                            <td class="col-md-7">{{ $rec->note }}</td>
                                            <td class="col-md-2">{{ ucwords($rec->label->label_name) }}</td>
                                            <td class="col-md-1"></td>
                                        </tr>
                                        @endforeach
                                        <form method="POST" action="/admin/user-{{ $user->id }}/adminlog/new" accept-charset="UTF-8">
                                            {!! csrf_field() !!}

                                            <tr>
                                                <td class="col-md-2"><input type="text" name="date" id="date" class="form-control-sm-text datepick"/></td>
                                                <td class="col-md-7"><input type="text" name="note" id="note" class="form-control-sm-text" placeholder="Gebruiker geholpen met project invullen..." maxlength="100" /></td>
                                                <td class="col-md-2">
                                                    <select name="label" id="label" class="getact form-control-sm-text">
                                                        @foreach (\BynqIO\Dynq\Models\AdminLogLabel::all() as $label)
                                                        <option value="{{ $label->id }}">{{ ucwords($label->label_name) }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="col-md-1"><button class="btn btn-primary btn-xs"> Toevoegen</button></td>
                                            </tr>

                                        </form>
                                    </tbody>
                                </table>
                            </div>

                            <div id="audit" class="tab-pane">

                                <div class="pull-right">
                                    @if ($allevents)
                                    <a class="btn btn-primary" href="/admin/user-{{ $user->id }}/edit" >Laatste events</a>
                                    @else
                                    <a class="btn btn-primary" href="/admin/user-{{ $user->id }}/edit?allevents=1" >Alle events</a>
                                    @endif
                                </div>

                                <h4>Meest recente event bovenaan</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="col-md-2 hidden-sm hidden-xs">Datum</th>
                                            <th class="col-md-2 hidden-sm hidden-xs">IP</th>
                                            <th class="col-md-8">Event</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if ($allevents) {
                                            $selection = \BynqIO\Dynq\Models\Audit::where('user_id', $user->id)->orderBy('created_at','desc')->get();
                                        } else {
                                            $selection = \BynqIO\Dynq\Models\Audit::where('user_id', $user->id)->orderBy('created_at','desc')->limit(25)->get();
                                        }
                                        ?>
                                        @foreach ($selection as $rec)
                                        <tr>
                                            <td class="col-md-2 hidden-sm hidden-xs">{{ date('d-m-Y H:i:s', strtotime(DB::table('audit')->select('created_at')->where('id',$rec->id)->get()[0]->created_at)) }}</td>
                                            <td class="col-md-2 hidden-sm hidden-xs">{{ $rec->ip }}</td>
                                            <td class="col-md-8">{!! nl2br($rec->event) !!}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </section>

            </div>
@stop
<?php } ?>
