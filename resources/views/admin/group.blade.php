@extends('layout.master')

@section('title', 'Groepen')

@section('content')
<div id="wrapper">

    <section class="container">
        <div class="col-md-12">

            <div>
            <ol class="breadcrumb">
              <li><a href="/">Dashboard</a></li>
              <li><a href="/admin">Admin Dashboard</a></li>
              <li class="active">Groepen</li>
            </ol>
            <div>

            <h2><strong>Groepen</strong></h2>

            <div class="white-row">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="col-md-4">Groepnaam</th>
                        <th class="col-md-2">Accountprijs</th>
                        <th class="col-md-2">Gebruikers</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($selection = \BynqIO\Dynq\Models\UserGroup::orderBy('id')->get() as $group)
                    <tr>
                        <td class="col-md-4"><a href="{{ '/admin/group-'.$group->id.'/edit' }}">{{ ucfirst($group->name) }}</a></td>
                        <td class="col-md-2">{{ '&euro; '.number_format($group->subscription_amount, 2,",",".") }}</td>
                        <td class="col-md-2">{{ \BynqIO\Dynq\Models\User::where('user_group', $group->id)->where('active','=','true')->count() }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-12">
                    <a href="/admin/group/new" class="btn btn-primary"><i class="fa fa-pencil"></i> Nieuwe groep</a>
                </div>
            </div>
            </div>
        </div>

    </section>

</div>
@stop
