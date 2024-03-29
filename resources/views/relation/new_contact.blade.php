<?php
$common_access_error = false;
$relation = \BynqIO\Dynq\Models\Relation::find(Route::Input('relation_id'));
if (!$relation || !$relation->isOwner()) {
    $common_access_error = true;
} else {
    $contact = \BynqIO\Dynq\Models\Contact::where('relation_id','=',$relation->id)->first();
}
?>

@extends('layout.master')

<?php if($common_access_error){ ?>
@section('content')
<div id="wrapper">
    <section class="container">
        <div class="alert alert-danger">
            <i class="fa fa-frown-o"></i>
            <strong>Fout</strong>
            Deze relatie bestaat niet
        </div>
    </section>
</div>
@stop
<?php }else{ ?>

@section('content')
<div id="wrapper">

    <section class="container">

        <div class="col-md-12">

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

            <div>
            <ol class="breadcrumb">
              <li><a href="/">Dashboard</a></li>
              <li><a href="/relation">Relaties</a></li>
              <li><a href="/relation/{{ $relation->id }}-{{ $relation->slug() }}/details">{{ $relation->company_name ? $relation->company_name : $contact->firstname . ' ' . $contact->lastname }}</a></li>
             <li class="active" /relation-{{ $relation->id }}/contact/new">nieuw contact</li>
            </ol>
            </div>

            <h2><strong>Nieuw</strong> contact</h2>

            <div class="white-row">
                <form method="POST" action="/relation/contact/new" accept-charset="UTF-8">
                {!! csrf_field() !!}
                <h4>Contactgegevens</h4>
                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="contact_salutation">Titel</label>
                            <input name="contact_salutation" maxlength="16" id="contact_salutation" type="text" value="{{ old('contact_salutation') }}" class="form-control"/>
                            <input type="hidden" name="id" id="id" value="{{ $relation->id }}"/>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="contact_name">Achternaam <a style="text-decoration:none;cursor:default;">*</a></label>
                            <input name="contact_name" maxlength="50" id="contact_name" type="text" value="{{ old('contact_name') }}" class="form-control" required/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="contact_firstname">Voornaam</label>
                            <input name="contact_firstname" maxlength="30" id="contact_firstname" type="text" value="{{ old('contact_firstname') }}" class="form-control"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="mobile">Mobiel</label>
                            <input name="mobile" id="mobile" type="text" maxlength="12" value="{{ old('mobile') }}" class="form-control"/>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="telephone">Telefoonnummer</label>
                            <input name="telephone" id="telephone" type="text" maxlength="12" value="{{ old('telephone') }}" class="form-control"/>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="email">Email <a style="text-decoration:none;cursor:default;">*</a></label>
                            <input name="email" id="email" type="email" value="{{ old('email') }}" class="form-control" required/>
                        </div>
                    </div>

                    @if (\BynqIO\Dynq\Models\RelationKind::find($relation->kind_id)->kind_name=='zakelijk')
                    <div class="col-md-4 company">
                        <div class="form-group">
                            <label for="contactfunction">Functie <a style="text-decoration:none;cursor:default;">*</a></label>
                            <select name="contactfunction" id="contactfunction" class="form-control pointer">
                                @foreach (\BynqIO\Dynq\Models\ContactFunction::all() as $function)
                                <option {{ (old('contactfunction') ? (old('contactfunction') == $function->id ? 'selected' : '') : $function->function_name=='directeur' ? 'selected' : '') }} value="{{ $function->id }}">{{ ucwords($function->function_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="gender" style="display:block;">Geslacht</label>
                            <select name="gender" id="gender" class="form-control pointer">
                                <option value="-1">Selecteer</option>
                                <option {{ (old('gender') == 'M' ? 'selected' : '') }} value="M">Man</option>
                                <option {{ (old('gender') == 'V' ? 'selected' : '') }} value="V">Vrouw</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-primary"><i class="fa fa-check"></i> Opslaan</button>
                    </div>

                </div>
            </form>
            </div>

        </div>

    </section>

</div>
@stop
<?php } ?>
