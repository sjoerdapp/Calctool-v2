@extends('layout.master')

@section('content')

<?php
$user = User::find(Route::input('user_id'));
?>

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

	$("[name='toggle-api']").bootstrapSwitch();
	$("[name='toggle-active']").bootstrapSwitch();
});
</script>

<div id="wrapper">

	<section class="container">

		<div class="col-md-12">

			<div>
			<ol class="breadcrumb">
			  <li><a href="/">Home</a></li>
			  <li><a href="/admin">Admin CP</a></li>
			  <li><a href="/admin/user">Gebruikers</a></li>
			  <li class="active">{{ $user->username }}</li>
			</ol>
			<div>
			<br />

			@if(Session::get('success'))
			<div class="alert alert-success">
				<i class="fa fa-check-circle"></i>
				<strong>Opgeslagen</strong>
			</div>
			@endif

			@if($errors->has())
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
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				  Opties
				  <span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
				  <li><a href="/admin/user-{{ $user->id }}/switch">Gebruiker overnemen</a></li>
				  <li><a href="/admin/user-{{ $user->id }}/demo">Demo project laden</a></li>
				</ul>
				</div>
			</div>

			<h2><strong>Gebruiker</strong> {{ $user->username }}</h2>
			<div class="white-row">
				<form method="post" action="">

				<h4 class="company">Gebruikersgegevens</h4>
				<div class="row company">


					<div class="col-md-5">
						<div class="form-group">
							<label for="company_name">Gebruikersnaam</label>
							<input name="username" id="username" type="text" value="{{ Input::old('username') ? Input::old('username') : $user->username}}" class="form-control" />
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="user_type">Gebruikers type</label>
							<select name="type" id="type" class="form-control pointer">
								@foreach (UserType::all() as $type)
									<option {{ $user->user_type==$type->id ? 'selected' : '' }} value="{{ $type->id }}">{{ ucwords($type->user_type) }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="secret">Wachtwoord</label>
							<input name="secret" type="password" id="secret" class="form-control">
						</div>
					</div>

				</div>

				<h4>Contactgegevens</h4>
				<div class="row">

					<div class="col-md-2">
						<div class="form-group">
							<label for="firstname">Voornaam</label>
							<input name="firstname" id="firstname" type="text" value="{{ Input::old('firstname') ? Input::old('firstname') : $user->firstname }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="lastname">Achternaam</label>
							<input name="lastname" id="lastname" type="text" value="{{ Input::old('lastname') ? Input::old('lastname') : $user->lastname }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label for="mobile">Mobiel</label>
							<input name="mobile" id="mobile" type="text" maxlength="12" value="{{ Input::old('mobile') ? Input::old('mobile') : $user->mobile}}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label for="telephone">Telefoonnummer</label>
							<input name="telephone" id="telephone" type="text" maxlength="12" value="{{ Input::old('telephone') ? Input::old('telephone') : $user->phone }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="email">Email</label>
							<input name="email" id="email" type="email" value="{{ Input::old('email') ? Input::old('email') : $user->email }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="website">Website</label>
							<input name="website" id="website" type="url" value="{{ Input::old('website') ? Input::old('website') : $user->website }}" class="form-control"/>
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

				<h4>Adresgegevens</h4>
				<div class="row">

					<div class="col-md-4">
						<div class="form-group">
							<label for="address_street">Straat</label>
							<input name="address_street" id="address_street" type="text" value="{{ Input::old('address_street') ? Input::old('address_street') : $user->address_street }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-1">
						<div class="form-group">
							<label for="address_number">Huis nr.</label>
							<input name="address_number" id="address_number" type="text" value="{{ Input::old('address_number') ? Input::old('address_number') : $user->address_number}}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label for="address_zipcode">Postcode</label>
							<input name="address_zipcode" id="address_zipcode" maxlength="6" type="text" value="{{ Input::old('address_zipcode') ? Input::old('address_zipcode') : $user->address_postal }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="address_city">Plaats</label>
							<input name="address_city" id="address_city" type="text" value="{{ Input::old('address_city') ? Input::old('address_city') : $user->address_city }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label for="province">Provincie</label>
							<select name="province" id="province" class="form-control pointer">
								@foreach (Province::all() as $province)
									<option {{ $user->province_id==$province->id ? 'selected' : '' }} value="{{ $province->id }}">{{ ucwords($province->province_name) }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="country">Land</label>
							<select name="country" id="country" class="form-control pointer">
								@foreach (Country::all() as $country)
									<option {{ $user->country_id==$country->id ? 'selected' : '' }} value="{{ $country->id }}">{{ ucwords($country->country_name) }}</option>
								@endforeach
							</select>
						</div>
					</div>

				</div>

				<h4>Overig</h4>
				<div class="row">

					<div class="col-md-3">
						<div class="form-group">
							<label for="iban">Abonnement verloopdatum</label>
							<input name="expdate" id="expdate" type="date" value="{{ Input::old('expdate') ? Input::old('expdate') : date('Y-m-d', strtotime($user->expiration_date)) }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="iban">Activeringsdatum</label>
							<input name="confirmdate" id="confirmdate" type="date" value="{{ Input::old('confirmdate') ? Input::old('confirmdate') : date('Y-m-d', strtotime($user->confirmed_mail)) }}" class="form-control"/>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="iban">Blokkeeringsdatum</label>
							<input name="bandate" id="bandate" type="date" value="{{ ($user->banned ? date('Y-m-d', strtotime($user->banned)) : '') }}" class="form-control"/>
						</div>
					</div>
				</div>
				<div class="row">

					<div class="col-md-2">
						<div class="form-group">
							<label for="toggle-api" style="display:block;">API toegang</label>
							<input name="toggle-api" type="checkbox" {{ $user->api_access ? 'checked' : '' }}>
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
							<label for="address_city">API key</label>
							<input type="text" value="{{ $user->api }}" disabled class="form-control"/>
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="address_city">IP adres</label>
							<input type="text" value="{{ $user->ip }}" disabled class="form-control"/>
						</div>
					</div>

				</div>

				<h4>Opmerkingen</h4>
				<div class="row">
					<div class="form-group">
						<div class="col-md-12">
							<textarea name="note" id="note" rows="10" class="form-control">{{ Input::old('note') ? Input::old('note') : $user->note }}</textarea>
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

		</div>

	</section>

</div>

@stop
