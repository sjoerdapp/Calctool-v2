@inject('agent', 'Jenssegers\Agent\Agent')

@extends('layout.master')

@section('title', __('core.dashboard'))

@push('jsinline')
<script type="text/javascript">
$(document).ready(function() {
    /* Remove contents from modal on close */
    $(document).on('hidden.bs.modal', function (e) {
        $(e.target).removeData('bs.modal');
    });
});
</script>
@endpush

@section('content')
<div class="modal fade" id="asyncModal" tabindex="-1" role="dialog" aria-labelledby="asyncModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"></div>
    </div>
</div>

<div id="wrapper">

    <div id="shop">
        <section class="container">

            @if ($systemMessage && $systemMessage->level == 1)
            <div class="alert alert-warning">
                <i class="fa fa-fa fa-info-circle"></i>
                {{ $systemMessage->content }}
            </div>
            @elseif ($systemMessage && $systemMessage->level > 1)
            <div class="alert alert-danger">
                <i class="fa fa-warning"></i>
                <strong>{{ $systemMessage->content }}</strong>
            </div>
            @endif

            @include('layout.message')

            @if ($agent->isMobile())
            <div class="alert alert-warning">
                <i class="fa fa-warning"></i>
                <strong>@lang('core.mobilewarning')</strong>
            </div>
            @endif

            @if (Auth::user()->isNewPeriod())
            <div class="pull-right" style="margin: 10px 0 20px 0">
                <a href="/support/gethelp" class="btn btn-default hidden-sm hidden-xs" type="button"><i class="fa fa-support"></i>@lang('core.needhelp')</a>
            </div>
            @endif

            <h2 style="margin: 10px 0 20px 0;"><strong>{{ $welcomeMessage }}</strong> {{ Auth::user()->firstname }}</h2>

            <div class="row">
                @include('dashboard.widgets')
            </div>

            <div id="wrapper" class="nopadding-top">

                @if ($projectCount)
                <div class="white-row" >

                    <div class="pull-right">
                        @if (Cookie::has('beta'))
                        <a href="/inline/projecttypes?package=dashboard" data-toggle="modal" data-target="#asyncModal" class="btn btn-primary btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> @lang('core.new') {{ trans_choice('core.project', 1) }}</a>
                        @else
                        <a href="/project/new?type=calculate" class="btn btn-primary btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> @lang('core.new') {{ trans_choice('core.project', 1) }}</a>
                        @endif

                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fa fa-ellipsis-h" aria-hidden="true"></span></button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="/project/all">Alle Projecten</a></i>
                                <li><a href="/project/all?status=open">Open Projecten</a></i>
                                <li><a href="/project/all?status=closed">Gesloten Projecten</a></i>
                                <li><a href="/project/all">Geavanceerde Selecties</a></i>
                            </ul>
                        </div>

                    </div>

                    <h3>@lang('core.recent_projects')</h3>

                    @if (count($projects))
                    <div id="cartContent">
                        <div class="item head">
                            <span class="product_name fsize13 bold">@lang('core.projectname')</span>
                            <span class="remove_item fsize13 bold" style="width: 90px;"></span>
                            <span class="total_price fsize13 bold" style="text-align:left;">@lang('core.type')</span>
                            <span class="qty fsize13 bold" style="text-align:left;">@lang('core.customer')</span>
                            <div class="clearfix"></div>
                        </div>
                        @foreach($projects as $project)
                        <div class="item">
                            <a href="/project/{{ $project->id }}-{{ $project->slug() }}/details" class="product_name">{{ $project->project_name }}</a>
                            <a href="/project/close?id={{ $project->id }}&csrf={{ csrf_token() }}" onclick="return confirm('Project sluiten?')" class="btn btn-default btn-xs" style="float: right;margin: 10px;">@lang('core.close')</a>
                            <div class="total_price" style="text-align:left;">{{ ucfirst($project->type->type_name) }}</div>
                            <div class="qty" style="text-align:left;">{{ $project->client->name() }}</div>
                            <div class="clearfix"></div>
                        </div>
                        @endforeach
                        <div class="clearfix"></div>
                    </div>

                    @if (count($projects) == 5 &&$projectCount > 5)
                    <h5><strong><i class="fa fa-info-circle" aria-hidden="true"></i> Niet alle projecten worden weergegeven</strong></h5>
                    @endif

                    @else
                    <div class="text-center fsize18">Geen open projecten</div>
                    @endif
                </div>

                @else

                <h2><strong>@lang('core.firststep')</strong></h2>
                <div class="bs-callout text-center whiteBg" style="margin:0">
                    @if (Cookie::has('beta'))
                    <h3><a href="/inline/projecttypes?package=dashboard" data-toggle="modal" data-target="#asyncModal" class="btn btn-primary btn-lg">@lang('core.crefirstprod') <i class="fa fa-arrow-right"></i></a></h3>
                    @else
                    <h3><a href="/project/new?type=calculate" class="btn btn-primary btn-lg">@lang('core.crefirstprod') <i class="fa fa-arrow-right"></i></a></h3>
                    @endif
                </div>

                @endif
            </div>
        </section>
    </div>

</div>
@stop
