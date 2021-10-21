@extends('layouts.admin')

@section('title', trans('general.dashboard'))

@section('content')

    <div class="row">
        <div class="col-md-8">
            <!-- Account Balance List-->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('dashboard.account_balance') }}</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    @php
                    $totals = [];
                    foreach($accounts as $item)
                    {
                        if(!isset($totals[$item->currency_code]))
                        {
                            $totals[$item->currency_code] = 0;
                        }
                    }
                    @endphp
                    @if ($accounts->count())
                        <table class="table table-striped">
                            <tbody>
                            @foreach($accounts as $item)
                                @php
                                    $balance = $item->balance;
                                    $totals[$item->currency_code] += $balance;
                                @endphp
                                <tr>
                                    <td class="text-left"><a href="/banking/transactions?account_id={{ $item->id }}">{{ $item->name }}</a></td>
                                    @foreach($totals as $currency_code => $total)
                                        @if($currency_code == $item->currency_code)
                                            <td class="text-right text-no-wrap">@money($balance, $currency_code, true)</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-left">Total</th>
                                    @foreach($totals as $currency_code => $total)
                                    <th class="text-right text-no-wrap">@money($total, $currency_code, true)</th>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <h5 class="text-center">{{ trans('general.no_records') }}</h5>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">

            <!---Income-->
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-money"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ trans('dashboard.incomes_last_90') }}</span>
                    <span class="info-box-number">@money($total_incomes['total'], setting('general.default_currency'), true)</span>
                    <div class="progress-group">
                        <div class="progress sm">
                            <div class="progress-bar progress-bar-aqua" style="width: {{ $total_incomes['progress'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!---Expense-->
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-shopping-cart"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ trans('dashboard.expenses_last_90') }}</span>
                    <span class="info-box-number">@money($total_expenses['total'], setting('general.default_currency'), true)</span>

                    <div class="progress-group" >
                        <div class="progress sm">
                            <div class="progress-bar progress-bar-red" style="width: {{ $total_expenses['progress'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!---Profit-->
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-heart"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ trans('dashboard.profit_last_90') }}</span>
                    <span class="info-box-number">@money($total_profit['total'], setting('general.default_currency'), true)</span>

                    <div class="progress-group" title="{{ trans('dashboard.open_profit') }}: {{ $total_profit['open'] }}<br>{{ trans('dashboard.overdue_profit') }}: {{ $total_profit['overdue'] }}" data-toggle="tooltip" data-html="true">
                        <div class="progress sm">
                            <div class="progress-bar progress-bar-green" style="width: {{ $total_profit['progress'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <div class="row">
        <!-- Forecast Chart -->
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('dashboard.forecast') }} {{ trans('dashboard.chart') }}</h3>
                </div>
                <div class="box-body" id="forecast_chart">
                    {!! $forecast_chart->render() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Forecast Table -->
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('dashboard.forecast') }} {{ trans('dashboard.table') }}</h3>
                </div>
                <div class="box-body" id="forecast_table">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                        $balance = $current_balance
                        @endphp

                        @foreach($forecast_table as $date => $list)
                            @foreach($list as $item)
                                @php
                                if ($item['type'] == 'App\Models\Expense\Payable') {
                                    $item['amount'] *= -1;
                                }
                                $balance += $item['amount'];
                                @endphp
                            <tr>
                                <td class="text-left">{{ $date }}</td>
                                <td class="text-left">{{ $item['title'] }}</td>
                                <td class="text-right text-no-wrap">@money($item['amount'], $item['currency_code'])</td>
                                <td class="text-right text-no-wrap">@money($balance, $item['currency_code'])</td>
                            </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!---Income, Expense and Profit Line Chart-->
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('dashboard.cash_flow') }}</h3>
                    <div class="box-tools pull-right">
                        <button type="button" id="cashflow-daily" class="btn btn-default btn-sm">{{ trans('general.daily') }}</button>&nbsp;&nbsp;
                        <button type="button" id="cashflow-monthly" class="btn btn-default btn-sm">{{ trans('general.monthly') }}</button>&nbsp;&nbsp;
                        <button type="button" id="cashflow-quarterly" class="btn btn-default btn-sm">{{ trans('general.quarterly') }}</button>&nbsp;&nbsp;
                        <input type="hidden" name="period" id="period" value="day" />
                        <div class="btn btn-default btn-sm">
                            <div id="cashflow-range" class="pull-right">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                <span></span> <b class="caret"></b>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body" id="cashflow">
                    {!! $cashflow->render() !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">


        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('dashboard.incomes_by_category') }}</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    {!! $donut_incomes->render() !!}
                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('dashboard.expenses_by_category') }}</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    {!! $donut_expenses->render() !!}
                </div>
            </div>
        </div>


    </div>
@endsection

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('public/css/daterangepicker.css') }}" />
@endpush

@push('js')
{!! Charts::assets() !!}
<script type="text/javascript" src="{{ asset('public/js/moment/moment.js') }}"></script>
@if (is_file(base_path('public/js/moment/locale/' . strtolower(app()->getLocale()) . '.js')))
<script type="text/javascript" src="{{ asset('public/js/moment/locale/' . strtolower(app()->getLocale()) . '.js') }}"></script>
@elseif (is_file(base_path('public/js/moment/locale/' . language()->getShortCode() . '.js')))
<script type="text/javascript" src="{{ asset('public/js/moment/locale/' . language()->getShortCode() . '.js') }}"></script>
@endif
<script type="text/javascript" src="{{ asset('public/js/daterangepicker/daterangepicker.js') }}"></script>
@endpush

@push('scripts')
<script type="text/javascript">
    $(function() {
        var start = moment().startOf('year');
        var end = moment();

        function cb(start, end) {
            $('#cashflow-range span').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));
        }

        $('#cashflow-range').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                '{{ trans("reports.this_year") }}': [moment().startOf('year'), moment().endOf('year')],
                '{{ trans("reports.previous_year") }}': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                '{{ trans("reports.this_quarter") }}': [moment().subtract(2, 'months').startOf('month'), moment().endOf('month')],
                '{{ trans("reports.previous_quarter") }}': [moment().subtract(5, 'months').startOf('month'), moment().subtract(3, 'months').endOf('month')],
                '{{ trans("reports.last_12_months") }}': [moment().subtract(11, 'months').startOf('month'), moment().endOf('month')]
            }
        }, cb);

        cb(start, end);
    });

    $(document).ready(function () {
        $('#cashflow-range').on('apply.daterangepicker', function(ev, picker) {
            var period = $('#period').val();

            range = getRange(picker);

            $.ajax({
                url: '{{ url("common/dashboard/cashflow") }}',
                type: 'get',
                dataType: 'html',
                data: 'period=' + period + '&start=' + picker.startDate.format('YYYY-MM-DD') + '&end=' + picker.endDate.format('YYYY-MM-DD') + '&range=' + range,
                success: function(data) {
                    $('#cashflow').html(data);
                }
            });
        });

        $('#cashflow-daily').on('click', function() {
            var picker = $('#cashflow-range').data('daterangepicker');

            $('#period').val('day');

            range = getRange(picker);

            $.ajax({
                url: '{{ url("common/dashboard/cashflow") }}',
                type: 'get',
                dataType: 'html',
                data: 'period=day&start=' + picker.startDate.format('YYYY-MM-DD') + '&end=' + picker.endDate.format('YYYY-MM-DD') + '&range=' + range,
                success: function(data) {
                    $('#cashflow').html(data);
                }
            });
        });

        $('#cashflow-monthly').on('click', function() {
            var picker = $('#cashflow-range').data('daterangepicker');

            $('#period').val('month');

            range = getRange(picker);

            $.ajax({
                url: '{{ url("common/dashboard/cashflow") }}',
                type: 'get',
                dataType: 'html',
                data: 'period=month&start=' + picker.startDate.format('YYYY-MM-DD') + '&end=' + picker.endDate.format('YYYY-MM-DD') + '&range=' + range,
                success: function(data) {
                    $('#cashflow').html(data);
                }
            });
        });

        $('#cashflow-quarterly').on('click', function() {
            var picker = $('#cashflow-range').data('daterangepicker');

            $('#period').val('quarter');

            range = getRange(picker);

            $.ajax({
                url: '{{ url("common/dashboard/cashflow") }}',
                type: 'get',
                dataType: 'html',
                data: 'period=quarter&start=' + picker.startDate.format('YYYY-MM-DD') + '&end=' + picker.endDate.format('YYYY-MM-DD') + '&range=' + range,
                success: function(data) {
                    $('#cashflow').html(data);
                }
            });
        });
    });

    function getRange(picker) {
        ranges = {
            '{{ trans("reports.this_year") }}': 'this_year',
            '{{ trans("reports.previous_year") }}': 'previous_year',
            '{{ trans("reports.this_quarter") }}': 'this_quarter',
            '{{ trans("reports.previous_quarter") }}': 'previous_quarter',
            '{{ trans("reports.last_12_months") }}': 'last_12_months'
        };

        range = 'custom';

        if (ranges[picker.chosenLabel] != undefined) {
            range = ranges[picker.chosenLabel];
        }

        return range;
    }
</script>
@endpush
