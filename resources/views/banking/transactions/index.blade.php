@extends('layouts.admin')

@section('title', trans_choice('general.transactions', 2))

@section('new_button')
    @permission('create-expenses-payments')
    <span class="new-button"><a href="{{ url('expenses/payments/create') }}" class="btn btn-success btn-sm"><span class="fa fa-plus"></span> &nbsp;{{ trans('general.add_new') }} payment</a></span>
    @endpermission
    @permission('create-incomes-revenues')
    <span class="new-button"><a href="{{ url('incomes/revenues/create') }}" class="btn btn-success btn-sm"><span class="fa fa-plus"></span> &nbsp;{{ trans('general.add_new') }} revenue</a></span>
    @endpermission
    @permission('create-banking-transfers')
    <span class="new-button"><a href="{{ url('banking/transfers/create') }}" class="btn btn-success btn-sm"><span class="fa fa-plus"></span> &nbsp;{{ trans('general.add_new') }} transfer</a></span>
    @endpermission
@endsection

@section('content')
<!-- Default box -->
<div class="box box-success">
    <div class="box-header with-border">
        {!! Form::open(['url' => 'banking/transactions', 'role' => 'form', 'method' => 'GET', 'id' => 'frm-filter']) !!}
        <div id="items" class="pull-left box-filter">
            <span class="title-filter hidden-xs">{{ trans('general.search') }}:</span>
            {!! Form::dateRange('date', trans('general.date'), 'calendar', []) !!}
            {!! Form::select('account_id', $accounts, request('account_id'), ['id' => 'filter-account', 'class' => 'form-control input-filter input-sm']) !!}
            {!! Form::button('<span class="fa fa-filter"></span> &nbsp;' . trans('general.filter'), ['type' => 'submit', 'class' => 'btn btn-sm btn-default btn-filter']) !!}
        </div>
        {!! Form::close() !!}
    </div>
    <!-- /.box-header -->

    <div class="box-body">
        <div class="table table-responsive">
            <table class="table table-striped table-hover" id="tbl-transactions">
                <thead>
                    <tr>
                        <th class="col-md-2">Date <i class="fa fa-long-arrow-down sort-icon"></i></th>
                        <th class="col-md-2">Account Name</th>
                        <th class="col-md-2">Type</th>
                        <th class="col-md-2">Category</th>
                        <th class="col-md-2">Description</th>
                        <th class="col-md-2 text-right">Credit</th>
                        <th class="col-md-2 text-right">Debit</th>
                        <th class="col-md-2 text-right">Balance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="7" class="text-right">Open balance:</td>
                    <td class="money-column">@money($balance, $account->currency_code)</td>
                    <td></td>
                </tr>
                @foreach($transactions as $item)
                    <tr>
                        <td>{{ Date::parse($item->paid_at)->format($date_format) }}</td>
                        <td>{{ $item->account->name }}</td>
                        <td>
                            @if(!$item->is_transfer)
                                {{ $item->type }}
                            @endif
                            @if($item->is_transfer && $item->type === 'Revenue')
                                Transfer from {{ $item->transfer->payment->account->name }}
                            @endif
                            @if($item->is_transfer && $item->type === 'Payment')
                                Transfer to {{ $item->transfer->revenue->account->name }}
                            @endif
                        </td>
                        <td>{{ $item->category->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-right money-column">
                            @if($item->type === 'Revenue')
                            @money($item->amount, $item->currency_code)
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-right money-column">
                            @if($item->type === 'Payment')
                            @money($item->amount, $item->currency_code)
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-right money-column">
                            @if($item->type === 'Payment')
                                @money($balance -= $item->amount, $item->currency_code)
                            @else
                                @money($balance += $item->amount, $item->currency_code)
                            @endif
                        </td>
                        <td>
                            @php
                            if ($item->is_transfer) {
                                $url = url('banking/transfers/' . $item->transfer->id);
                            } else {
                                if ($item->type == 'Revenue') {
                                    $url = url('incomes/revenues/' . $item->id);
                                }
                                if ($item->type == 'Payment') {
                                    $url = url('expenses/payments/' . $item->id);
                                }
                            }
                            @endphp
                            {!! Form::open([
                                'method' => 'DELETE',
                                'url' => $url,
                            ]) !!}
                            <div class="btn-group" style="width: 70px;">
                            @if ($item->is_transfer)
                            <a class="btn btn-sm btn-default" href="{{ url('banking/transfers/' . $item->transfer->id . '/edit') }}"><i class="fa fa-edit"></i></a>
                            @endif
                            @if (!$item->is_transfer && $item->type == 'Revenue')
                            <a class="btn btn-sm btn-default" href="{{ url('incomes/revenues/' . $item->id . '/edit') }}"><i class="fa fa-edit"></i></a>
                            @endif
                            @if (!$item->is_transfer && $item->type == 'Payment')
                            <a class="btn btn-sm btn-default" href="{{ url('expenses/payments/' . $item->id . '/edit') }}"><i class="fa fa-edit"></i></a>
                            @endif
                            <button type="submit"
                                    class="btn btn-sm btn-default"
                                    onclick="if(!confirm('Tem certeza?')) { return false; }"><i class="fa fa-trash"></i></button>
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="7" class="text-right">Final balance:</td>
                    <td class="money-column">@money($balance, $account->currency_code)</td>
                    <td></td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- /.box-body -->

</div>
<!-- /.box -->
@endsection

@push('js')
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/daterangepicker/moment.js') }}"></script>
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
@if (language()->getShortCode() != 'en')
<script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/locales/bootstrap-datepicker.' . language()->getShortCode() . '.js') }}"></script>
@endif
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/datepicker3.css') }}">
@endpush

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $("#filter-account").change(function(){
                $("#frm-filter").submit();
            });
            $('.date-range-btn').on('apply.daterangepicker', function(ev, picker) {
                $("#frm-filter").submit();
            });
        });
    </script>
@endpush
