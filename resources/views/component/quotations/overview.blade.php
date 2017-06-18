@inject('calculus', 'BynqIO\Dynq\Calculus\CalculationEndresult')
@inject('carbon', 'Carbon\Carbon')

@push('style')
<link media="all" type="text/css" rel="stylesheet" href="/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">
@endpush

@push('scripts')
<script src="/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
@endpush

@push('jsinline')
<script type="text/javascript">
$(document).ready(function() {
    $('[name=date]').datepicker({format: '{{ \BynqIO\Dynq\Services\FormatService::dateFormatJS() }}'});
});
</script>
@endpush

@if ($offer_last)
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="/quotation/confirm" method="post">
                {!! csrf_field() !!}

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel2">Opdracht bevestiging</h4>
                </div>

                <div class="modal-body">

                    <div class="form-horizontal">
                        <div class="form-group">
                            <div class="col-md-12">
                                <label>Bevestig {{ $offer_last->offer_code }} op</label>
                            </div>
                            <div class="col-md-12">
                                <div class="input-group input-append date" id="dateRangePicker">
                                    <input type="text" class="form-control" name="date" value="{{ $carbon::now()->toDateString() }}" />
                                    <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
                                </div>
                                <input value="{{ $project->id }}" type="hidden" name="project" />
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Opslaan</button>
                </div>

            </form>

        </div>
    </div>
</div>
@endif

@if ($offer_last)
@if (number_format($calculus::totalProject($project), 3, ",",".") != number_format($offer_last->offer_total, 3, ",","."))
<div class="alert alert-warning">
    <i class="fa fa-fa fa-info-circle"></i> Gegevens zijn gewijzigd ten op zichte van de laaste offerte
</div>
@endif
@endif

@if ($offer_last && !$offer_last->offer_finish)
<div class="alert alert-info">
    <i class="fa fa-fa fa-info-circle"></i> Zend na aanpassing van de calculatie een nieuwe offerte naar uw opdrachtgever.
</div>
@endif

@section('component_buttons')
<div class="pull-right">

    @if ($offer_last && !$offer_last->offer_finish && !$project->project_close)
    <div class="btn-group">
        <button type="button" class="btn btn-primary"><i class="fa fa-paper-plane" aria-hidden="true"></i>Versturen</button>
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li><a data-toggle="modal" data-target="#confirmModal"><i class="fa fa-check-square-o">&nbsp;</i>Opdracht Bevestigen</a></li>
        </ul>
    </div>
    @endif

    @if (!($offer_last && $offer_last->offer_finish) && !$project->project_close)
    <a href="/project/{{ $project->id }}-{{ $project->slug() }}/quotations/new" class="btn btn-primary btn"><i class="fa fa-pencil-square-o"></i>Nieuwe Offerte</a>
    @endif

</div>
@endsection

<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-md-4">Offertenummer</th>
            <th class="col-md-3">Datum</th>
            <th class="col-md-3">Offertebedrag (excl. BTW)</th>
            <th class="col-md-3"></th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; ?>
        @foreach($project->quotations()->orderBy('created_at')->get() as $offer)
        <?php $i++; ?>
        <tr>
            <td class="col-md-4"><a href="/project/{{ $project->id }}-{{ $project->slug() }}/quotations/detail?id={{ $offer->id }}">{{ $offer->offer_code }}</a> @if ($offer->offer_finish)<span class="label label-default">Definitief</span>@endif</td>
            <td class="col-md-3">{{ $carbon::parse($offer->offer_make)->toDateString() }}</td>
            <td class="col-md-3">@money($offer->offer_total)</td>
            <td class="col-md-3 text-right"><a href="/res-{{ ($offer->resource_id) }}/download" class="btn btn-primary btn-xs"><i class="fa fa-download fa-fw"></i> Downloaden</a></td>
        </tr>
        @endforeach
        @if (!$i)
        <tr>
            <td colspan="4" style="text-align: center;">Er zijn nog geen offertes gemaakt</td>
        </tr>
        @endif
    </tbody>
</table>
