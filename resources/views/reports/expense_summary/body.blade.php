<div class="box-body">
    {!! $chart->render() !!}

    <hr>

    <div class="table table-responsive">
        <table class="table table-bordered table-striped table-hover" id="tbl-report-expenses">
            <thead>
            <tr>
                <th>{{ trans_choice('general.categories', 1) }}</th>
                @foreach($dates as $date)
                    <th class="text-right">{{ $date }}</th>
                @endforeach
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @if ($expenses)
                @foreach($expenses as $category_id =>  $category)
                    @php $t = 0 @endphp
                    <tr>
                        <td>{{ $categories[$category_id] }}</td>
                        @foreach($category as $item)
                            <td class="text-right">@money($item['amount'], setting('general.default_currency'), true)</td>
                            @php $t += $item['amount'] @endphp
                        @endforeach
                        <th class="text-right">@money($t, setting('general.default_currency'), true)</th>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="13">
                        <h5 class="text-center">{{ trans('general.no_records') }}</h5>
                    </td>
                </tr>
            @endif
            </tbody>
            <tfoot>
            <tr>
                <th>{{ trans_choice('general.totals', 1) }}</th>
                @php $t = 0 @endphp
                @foreach($totals as $total)
                    <th class="text-right">@money($total['amount'], $total['currency_code'], true)</th>
                    @php $t += $total['amount'] @endphp
                @endforeach
                <th class="text-right">@money($t, $total['currency_code'], true)</th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('js')
{!! Charts::assets() !!}
@endpush
