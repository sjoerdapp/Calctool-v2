<?php
use BynqIO\Dynq\Models\UserGroup;
use BynqIO\Dynq\Models\UserType;

$user = Auth::user();
$user_type_name = UserType::find($user->user_type)->user_type;
?>

@extends('layout.master')

@section('title', 'Account')

@push('style')
<link media="all" type="text/css" rel="stylesheet" href="/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css">
@endpush

@push('scripts')
<script src="/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="/js/iban.js"></script>
@endpush

@section('content')

<script type="text/javascript">
$(document).ready(function() {
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

    $('#tab-company').click(function(e){
        sessionStorage.toggleTabMyAcc{{Auth::id()}} = 'company';
    });
    $('#tab-payment').click(function(e){
        sessionStorage.toggleTabMyAcc{{Auth::id()}} = 'payment';
    });
    $('#tab-contact').click(function(e){
        sessionStorage.toggleTabMyAcc{{Auth::id()}} = 'contact';
    });
    $('#tab-apps').click(function(e){
        sessionStorage.toggleTabMyAcc{{Auth::id()}} = 'apps';
    });
    $('#tab-other').click(function(e){
        sessionStorage.toggleTabMyAcc{{Auth::id()}} = 'other';
    });

    @if (!Auth::user()->hasPayed())
        sessionStorage.toggleTabMyAcc{{Auth::id()}} = 'payment';
        $('#tab-payment').addClass('active');
        $('#payment').addClass('active');
    @else
    if (sessionStorage.toggleTabMyAcc{{Auth::id()}}){
        $toggleOpenTab = sessionStorage.toggleTabMyAcc{{Auth::id()}};
        $('#tab-'+$toggleOpenTab).addClass('active');
        $('#'+$toggleOpenTab).addClass('active');
    } else {
        sessionStorage.toggleTabMyAcc{{Auth::id()}} = 'company';
        $('#tab-company').addClass('active');
        $('#company').addClass('active');
    }
    @endif

    $('#warn-link').click(function(e) {
        var $curr = sessionStorage.toggleTabMyAcc{{Auth::id()}}
        $('#tab-' + $curr).removeClass('active');
        $('#' + $curr).removeClass('active');
        $('#tab-payment').addClass('active');
        $('#payment').addClass('active');
    });

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

    $("[name='payment_mandate']")
        .bootstrapSwitch({onText: 'Ja',offText: 'Nee'})
        .on('switchChange.bootstrapSwitch', function(event, state) {
          if (state) {
              $('#mandate').show();
              $('#payperclick').hide();
              $('#payment_url').attr('href', '/payment?auto=1');
          } else {
            $('#mandate').hide();
              $('#payperclick').show();
              $('#payment_url').attr('href', '/payment');
        }
    });

    $('#acc-deactive').click(function(e){
        e.preventDefault();
        location.href = '/account/deactivate?reason=' + $('#reason').val();
    });

    $('#promocode').blur(function(e){
        e.preventDefault();
        $field = $(this);
        if ($field.val()) {
            $.post("/payment/promocode", {
                code: $field.val()
            }, function(data) {
                if (data.success) {
                    $field.addClass('success-input');
                    $('#currprice').text(data.famount);
                } else {
                    $field.addClass('error-input');
                    $('#errmess').show();
                }
            });
        }
    });

});
</script>

{{--TODO: Move into inline model--}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog">
        <div class="modal-content">

            <div class="modal-body">
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <i class="fa fa-frown-o"></i>
                    <strong>Fout</strong>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
                @endif

                <div class="bs-callout text-center styleBackground nomargin">
                    <h2 id="payperclick" style="display:none;">Verleng met &eacute;&eacute;n maand voor &euro; <strong id="currprice">{{ number_format(UserGroup::find($user->user_group)->subscription_amount, 2,",",".") }}</strong></h2>
                    <h2 id="mandate">Elke maand automatisch voor &euro; <strong id="currprice">{{ number_format(UserGroup::find($user->user_group)->subscription_amount, 2,",",".") }}</strong></h2>
                </div>

                <br />
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="payment_mandate" style="display:block;"><strong>Automatisch verlengen</strong></label>
                            <input name="payment_mandate" type="checkbox" checked>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="promocode">Promotiecode</label>
                            <input name="promocode" id="promocode" type="text" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span id="errmess" style="color:rgb(248, 97, 97);display:none;"><br />Deze promotiecode bestaat niet of is niet meer geldig.</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="col-md-12">
                    <a id="payment_url" href="/payment?auto=1" class="btn btn-primary"><i class="fa fa-check"></i> Betalen</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="paymentModalUpdate" tabindex="-1" role="dialog" aria-labelledby="paymentModalUpdate" aria-hidden="true">
    <div class="modal-dialog modal-dialog">
        <div class="modal-content">

            <div class="modal-body">
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <i class="fa fa-frown-o"></i>
                    <strong>Fout</strong>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
                @endif

                <div class="bs-callout text-center styleBackground nomargin-top">
                    <h2>{{ $user->monthsBehind() }} {{ $user->monthsBehind() > 1 ? 'maanden' : 'maand' }} bijwerken* voor &euro; <strong id="currprice">{{ number_format($user->monthsBehind() * UserGroup::find($user->user_group)->subscription_amount, 2,",",".") }}</strong></h2>
                </div>
                <span>*Automatische betalingen kunnen alleen worden ingesteld met een actief account</span>
            </div>

            <div class="modal-footer">
                <div class="col-md-12">
                    <a href="/payment?incr={{ $user->monthsBehind() }}" class="btn btn-primary"><i class="fa fa-check"></i> Betalen</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deactivateModal" tabindex="-1" role="dialog" aria-labelledby="deactivateModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog">
        <div class="modal-content">

            <div class="modal-body">
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <i class="fa fa-frown-o"></i>
                    <strong>Fout</strong>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
                @endif

                <div class="bs-callout text-center styleBackground nomargin">
                    <h2><strong><i class="fa fa-frown-o fsize40" aria-hidden="true"></i></strong> Definitief opzeggen?</h2>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label>Reden voor opzegging:</label>
                        <textarea name="reason" id="reason" rows="5" class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="col-md-6 text-left" style="padding: 0;">
                    <button class="btn btn-primary" data-dismiss="modal">Annuleren</button>
                </div>
                <div class="col-md-6" style="padding: 0;">
                    <a href="javascript:void(0);" class="btn btn-danger" id="acc-deactive">Definitief deactiveren</a>
                </div>
            </div>
        </div>
    </div>
</div>
{{--/TODO--}}

<div id="wrapper">

    <section class="container">

        <div class="col-md-12">

            <div>
                <ol class="breadcrumb">
                  <li><a href="/">Dashboard</a></li>
                  <li class="active">Account</li>
                </ol>
            <div>
            <br>

            @if (!Auth::user()->hasPayed())
            <div class="alert alert-warning">
                <i class="fa fa-warning"></i>
                Account is verlopen, activeer onder <a href="javascript:void(0);" id="warn-link" style="color:#8a6d3b;"><b>Betalingen</b></a>.
            </div>
            <div class="alert alert-info">
                <i class="fa fa-info"></i>
                Langer testen? Dat kan, neem contact op met de support afdeling.
            </div>

            @endif

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

            <?php
                $clients = DB::table('oauth_sessions')
                            ->join('oauth_clients', 'oauth_sessions.client_id', '=', 'oauth_clients.id')
                            ->leftJoin('oauth_access_tokens', 'oauth_sessions.id', '=', 'oauth_access_tokens.session_id')
                            ->select('oauth_sessions.*', 'oauth_clients.name', 'oauth_clients.active', 'oauth_access_tokens.created_at as last_used')
                            ->where('owner_id',Auth::id())->get();
            ?>

            <h2><strong>Account</strong> Instellingen</h2>

                <div class="tabs nomargin-top">

                    <ul class="nav nav-tabs">
                        <li id="tab-company">
                            <a href="#company" data-toggle="tab"><i class="fa fa-info"></i> Accountgegevens</a>
                        </li>
                        @if ($user_type_name != 'demo')
                        <li id="tab-payment">
                            <a href="#payment" data-toggle="tab"><i class="fa fa-credit-card"></i> Betalingen</a>
                        </li>
                        <li id="tab-contact">
                            <a href="#contact" data-toggle="tab"><i class="fa fa-key"></i> Wachtwoord</a>
                        </li>
                        @endif
                        @if (count($clients))
                        <li id="tab-apps">
                            <a href="#apps" data-toggle="tab"><i class="fa fa-exchange"></i> Applicaties</a>
                        </li>
                        @endif
                        @if (Cookie::has('beta'))
                        <li id="tab-other">
                            <a href="#other" data-toggle="tab"><i class="fa fa-plus"></i> Overig</a>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <div id="company" class="tab-pane">

                            <form method="POST" action="/account/updateuser" accept-charset="UTF-8">
                            {!! csrf_field() !!}

                            <div>
                            <h4 class="company">Contactgegevens</h4>
                            </div>
                            <div class="row company">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="firstname">Voornaam</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="firstname" id="firstname" type="text" value="{{ old('firstname') ? old('firstname') : $user->firstname }}" class="form-control" />
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="lastname">Achternaam</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="lastname" id="lastname" type="text" value="{{ old('lastname') ? old('lastname') : $user->lastname }}" class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gender" style="display:block;">Geslacht</label>
                                        <select {{ session()->has('swap_session') ? 'disabled' : '' }} name="gender" id="gender" class="form-control pointer">
                                            <option value="-1">Selecteer</option>
                                            <option {{ $user->gender=='M' ? 'selected' : '' }} value="M">Man</option>
                                            <option {{ $user->gender=='V' ? 'selected' : '' }} value="V">Vrouw</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row company">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="phone">Telefoonnummer</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="phone" id="phone" type="text" maxlength="12" value="{{ old('phone') ? old('phone') : $user->phone }}" class="form-control"/>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="mobile">Mobiel</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="mobile" id="mobile" type="text" maxlength="12" value="{{ old('mobile') ? old('mobile') : $user->mobile }}" class="form-control"/>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="email">Email*</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="email" id="email" type="email" value="{{ old('email') ? old('email') : $user->email }}" class="form-control"/>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="website">Website</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="website" id="website" type="url" value="{{ old('website') ? old('website') : $user->website }}" class="form-control"/>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button {{ session()->has('swap_session') ? 'disabled' : '' }} class="btn btn-primary {{ session()->has('swap_session') ? 'disabled' : '' }}"><i class="fa fa-check"></i> Opslaan</button>
                                </div>
                            </div>
                        </form>

                        </div>
                        @if ($user_type_name != 'demo')
                        <div id="payment" class="tab-pane">

                            @if(!session()->has('swap_session'))
                            <div class="pull-right">
                                <a href="#" data-toggle="modal" data-target="#deactivateModal" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> Account deactiveren</a>
                                @if (UserGroup::find(Auth::user()->user_group)->subscription_amount == 0)
                                <a href="/payment/increasefree" class="btn btn-primary">Gratis verlengen</a>
                                @elseif ($user->monthsBehind() < 2)
                                @if (Auth::user()->payment_subscription_id)
                                <a href="/payment/subscription/cancel" class="btn btn-primary">Incasso stoppen</a>
                                @else
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#paymentModal"><i class="fa fa-refresh" aria-hidden="true"></i> Account verlengen</a>
                                @endif
                                @else
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#paymentModalUpdate"><i class="fa fa-refresh" aria-hidden="true"></i> Account bijwerken</a>
                                @endif
                            </div>
                            @endif

                            <h4>Accountlicentie</h4>
                            <div class="row">
                                <div class="col-md-3"><strong>Account actief tot:</strong></div>
                                <div class="col-md-2">{{ $user->dueDateHuman() }}</div>
                                <div class="col-md-7">&nbsp;</div>
                            </div>
                            <br />
                            <h4>Betalingsgeschiedenis</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="col-md-2">Datum</th>
                                        <th class="col-md-1">Bedrag</th>
                                        <th class="col-md-3">Type</th>
                                        <th class="col-md-4">Omschrijving</th>
                                        <th class="col-md-1">Betalingswijze</th>
                                        <th class="col-md-1"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php $i=0; ?>
                                    @foreach (BynqIO\Dynq\Models\Payment::where('user_id',Auth::user()->id)->where('status','paid')->orderBy('created_at', 'desc')->get() as $order)
                                    <?php $i++; ?>
                                    <tr>
                                        <td class="col-md-2"><strong>{{ date('d-m-Y H:i:s', strtotime(DB::table('payment')->select('created_at')->where('id','=',$order->id)->get()[0]->created_at)) }}</strong></td>
                                        <td class="col-md-1">{{ '&euro; '.number_format($order->amount, 2,",",".") }}</td>
                                        <td class="col-md-3">{{ $order->getTypeName() }}</td>
                                        <td class="col-md-4">{{ $order->description }}</td>
                                        <td class="col-md-1">{{ $order->method ? ucfirst($order->method) : '-' }}</td>
                                        <td class="col-md-1">
                                        @if ($order->resource_id)
                                        <a href="/resource/{{ $order->resource_id }}/download/invoice.pdf" class="btn btn-xs btn-primary" style="padding:0px 10px;"><i class="fa fa-download" aria-hidden="true"></i>Factuur</a>
                                        @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if (!$i)
                                    <tr>
                                        <td colspan="6" style="text-align: center;">Er zijn nog geen betalingen</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div id="contact" class="tab-pane">

                            <form method="POST" action="account/security/update" accept-charset="UTF-8">
                            {!! csrf_field() !!}

                            <h4 class="company">Wachtwoord wijzigen</h4>
                            <div class="row company">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="curr_secret">Huidig wachtwoord</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="curr_secret" id="curr_secret" type="password" class="form-control" autocomplete="off"/>
                                    </div>
                                </div>

                            </div>
                            <div class="row company">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="secret">Wachtwoord</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="secret" id="secret" type="password" class="form-control" autocomplete="off"/>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="secret_confirmation">Herhaal wachtwoord</label>
                                        <input {{ session()->has('swap_session') ? 'disabled' : '' }} name="secret_confirmation" id="secret_confirmation" type="password" class="form-control" autocomplete="off"/>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="api">Referral link</label>
                                        <input name="api" id="api" type="text" readonly="readonly" value="{{ url('register') }}?client_referer={{ $user->referral_key }}" class="form-control"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button {{ session()->has('swap_session') ? 'disabled' : '' }} class="btn btn-primary {{ session()->has('swap_session') ? 'disabled' : '' }}" name="save-password"><i class="fa fa-check"></i> Opslaan</button>
                                </div>
                            </div>
                        </form>

                        </div>
                        @endif

                        @if (count($clients))
                        <div id="apps" class="tab-pane">

                            <h4>Externe applicaties</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="col-md-2">Applicatie</th>
                                        <th class="col-md-2">Datum akkoord</th>
                                        <th class="col-md-4">Laatst vernieuwd</th>
                                        <th class="col-md-2">Actief</th>
                                        <th class="col-md-2"></th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php
                                    ?>
                                    @foreach ($clients as $client)
                                    <tr>
                                        <td class="col-md-2"><strong>{{ $client->name }}</strong></td>
                                        <td class="col-md-2">{{ date('d-m-Y H:i:s', strtotime($client->created_at)) }}</td>
                                        <td class="col-md-4">{{ $client->last_used ?date('d-m-Y H:i:s', strtotime($client->last_used)) : '-' }}</td>
                                        <td class="col-md-2">{{ $client->active ? 'Ja' : 'Nee' }}</td>
                                        <td class="col-md-2" style="text-align:right">
                                        @if(!session()->has('swap_session'))
                                            <a href="/account/oauth/session/{{ $client->id }}/revoke" class="btn btn-danger btn-xs">Intrekken</a>
                                        @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        @if (Cookie::has('beta'))
                        <div id="other" class="tab-pane">

                            <div class="row">
                                <div class="col-md-3">

                                    <div style="width: 20rem;">
                                      <div class="card-block">
                                        <h4 class="card-title">Demo Project</h4>
                                        <p class="card-text">Laad een voorbeeldproject. Dit kan handig zijn om functies te testen of om gelijk aan de slag te kunnen.</p>
                                        <a href="/account/loaddemo" class="btn btn-primary"><i class="fa fa-check"></i> Laad demoproject</a>
                                      </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                        @endif
                    </div>
                </div>

        </div>

    </section>

</div>

@stop
