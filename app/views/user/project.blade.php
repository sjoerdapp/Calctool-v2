@extends('layout.master')

@section('content')
<?# -- WRAPPER -- ?>
<div id="wrapper">

	<section class="container">

		<div class="col-md-12">

			<div>
				<ol class="breadcrumb">
				  <li><a href="/">Home</a></li>
				  <li class="active">Projecten</li>
				</ol>
			<div>
			<br>

			<h2><strong>Projecten</strong></h2>

			<table class="table table-striped">
				<?# -- table head -- ?>
				<thead>
					<tr>
						<th class="col-md-3">Projectnaam</th>
						<th class="col-md-2">Opdrachtgever</th>
						<th class="col-md-1">Type</th>
						<th class="col-md-3">Adres</th>
						<th class="col-md-2">Plaats</th>
						<th class="col-md-1">Status</th>
					</tr>
				</thead>

				<!-- table items -->
				<tbody>
				@foreach (Project::where('user_id','=', Auth::user()->id)->orderBy('created_at', 'desc')->get() as $project)
					<tr>
						<td class="col-md-3">{{ HTML::link('/project-'.$project->id.'/edit', $project->project_name) }}</td>
						<td class="col-md-2">{{ $project->contactor->company_name }}</td>
						<td class="col-md-1">{{ $project->type->type_name }}</td>
						<td class="col-md-3">{{ $project->address_street }} {{ $project->address_number }}</td>
						<td class="col-md-2">{{ $project->address_city }}</td>
						<td class="col-md-1">{{ $project->project_close ? 'Gesloten' : 'Open' }}</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			<div class="row">
				<div class="col-md-12">
					<a href="project/new" class="btn btn-primary"><i class="fa fa-pencil"></i> Nieuw project</a>
				</div>
			</div>
		</div>

	</section>

</div>
<!-- /WRAPPER -->
@stop
