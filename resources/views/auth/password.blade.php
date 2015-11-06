@extends('layout.master')

@section('content')
<?# -- WRAPPER -- ?>
<div id="wrapper">

	<div id="shop">

		<section class="container">

			<div class="row">

				<!-- REGISTER -->
				<div class="col-md-6">

					<h2>Nieuwe <strong>Wachtwoord</strong></h2>

					{{ Form::open(array('class' => 'white-row')) }}

						@if(Session::get('success'))
						<div class="alert alert-success">
							<i class="fa fa-check-circle"></i>
							<strong>{{ Session::get('success') }}</strong>
						</div>
						@endif

						<?# -- alert failed -- ?>
						@if($errors->any())
						<div class="alert alert-danger">
							<i class="fa fa-frown-o"></i>
							@foreach ($errors->all() as $error)
								{{ $error }}
							@endforeach
						</div>
						@endif

						<div class="row">
							<div class="form-group">
								<div class="col-md-12">
									<label></label>
									{{ Form::label('secret', 'Wachtwoord') }}
									{{ Form::password('secret', array('class' => 'form-control')) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="form-group">
								<div class="col-md-12">
									<label></label>
									{{ Form::label('secret_confirmation', 'Herhaal wachtwoord') }}
									{{ Form::password('secret_confirmation', array('class' => 'form-control')) }}
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<input type="submit" value="Opslaan" class="btn btn-primary pull-right push-bottom" data-loading-text="Loading...">
							</div>
						</div>

					{{ Form::close() }}

				</div>
				<!-- /REGISTER -->

				<!-- WHY? -->
				<div class="col-md-6">

					<h2>&nbsp;</h2>

					<div class="white-row">

						<h4>Registreren is snel, makkelijk en gratis</h4>

						<p>Als je eenmaal geregistreerd bent, kun je:</p>
						<ul class="list-icon check">
							<li>Alle opties van het programma gebruiken.</li>
							<li>Calculaties van A-Z opzetten.</li>
							<li>In één handomdraai offertes en facturen genereren.</li>
							<li>Een totale administratie voeren voor elk gewenst project.</li>
						</ul>

						<hr class="half-margins">

						<p>
							Heb je al een account?
							<a href="/login">log dan hier in</a>
						</p>
					</div>

					</div>
				<!-- /WHY? -->

			</div>

			<div class="white-row">
						<h4>Klantenservice</h4>
						<p>
							Als u op zoek bent naar hulp of gewoon een vraag wilt stellen, neem dan <a href="about">contact</a> met ons op.
						</p>
					</div>
		</section>

	</div>
</div>
<?# -- /WRAPPER -- ?>
@stop