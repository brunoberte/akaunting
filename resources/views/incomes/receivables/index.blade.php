@extends('layouts.admin')

@section('title', trans_choice('general.receivables', 2))

@section('new_button')
@permission('create-incomes-receivables')
<span class="new-button"><a href="{{ url('incomes/receivables/create') }}" class="btn btn-success btn-sm"><span class="fa fa-plus"></span> &nbsp;{{ trans('general.add_new') }}</a></span>
@endpermission
@endsection

@section('content')
<!-- Default box -->
<div class="box box-success">
    <div class="box-header with-border">
        {!! Form::open(['url' => 'incomes/receivables', 'role' => 'form', 'method' => 'GET']) !!}
        <div id="items" class="pull-left box-filter">
            <span class="title-filter hidden-xs">{{ trans('general.search') }}:</span>
            {!! Form::text('search', request('search'), ['class' => 'form-control input-filter input-sm', 'placeholder' => trans('general.search_placeholder')]) !!}
            {!! Form::select('accounts[]', $accounts, request('accounts'), ['id' => 'filter-accounts', 'class' => 'form-control input-filter input-lg', 'multiple' => 'multiple']) !!}
            {!! Form::select('customers[]', $customers, request('customers'), ['id' => 'filter-customers', 'class' => 'form-control input-filter input-lg', 'multiple' => 'multiple']) !!}
            {!! Form::select('categories[]', $categories, request('categories'), ['id' => 'filter-categories', 'class' => 'form-control input-filter input-lg', 'multiple' => 'multiple']) !!}
            {!! Form::dateRange('due_date', trans('receivables.due_at'), 'calendar', []) !!}
            {!! Form::button('<span class="fa fa-filter"></span> &nbsp;' . trans('general.filter'), ['type' => 'submit', 'class' => 'btn btn-sm btn-default btn-filter']) !!}
        </div>
        <div class="pull-right">
            <span class="title-filter hidden-xs">{{ trans('general.show') }}:</span>
            {!! Form::select('limit', $limits, request('limit', setting('general.list_limit', '25')), ['class' => 'form-control input-filter input-sm', 'onchange' => 'this.form.submit()']) !!}
        </div>
        {!! Form::close() !!}
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table table-responsive">
            <table class="table table-striped table-hover" id="tbl-receivables">
                <thead>
                    <tr>
                        <th class="col-md-2">{{ trans_choice('general.accounts', 1) }}</th>
                        <th class="col-md-2">@sortablelink('title', trans_choice('general.title_', 1))</th>
                        <th class="col-md-2">@sortablelink('customer_name', trans_choice('general.customers', 1))</th>
                        <th class="col-md-2">@sortablelink('category_name', trans_choice('general.categories', 1))</th>
                        <th class="col-md-1 text-center">{{ trans_choice('general.currencies', 1) }}</th>
                        <th class="col-md-2 text-right amount-space">@sortablelink('amount', trans('general.amount'))</th>
                        <th class="col-md-2">@sortablelink('due_at', trans('receivables.due_at'))</th>
                        <th class="col-md-1 text-center">{{ trans('recurring.recurring') }}</th>
                        <th class="col-md-1 text-center">{{ trans('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($receivables as $item)
                    @php $paid = $item->paid; @endphp
                    <tr>
                        <td>{{ $item->account->name }}</td>
                        <td><a href="{{ url('incomes/receivables/' . $item->id . '/edit') }}">{{ $item->title }}</a></td>
                        <td>{{ $item->customer->name }}</td>
                        <td>{{ $item->category->name }}</td>
                        <td>{{ $item->currency_code }}</td>
                        <td class="text-right amount-space">@money($item->amount, $item->currency_code)</td>
                        <td>{{ Date::parse($item->due_at)->format($date_format) }}</td>
                        <td>@if($item->recurring)
                            {{ $item->recurring->toString() }}
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-toggle-position="left" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    @if (!$item->reconciled)
                                    <li><a href="{{ url('incomes/receivables/' . $item->id . '/edit') }}">{{ trans('general.edit') }}</a></li>
                                    @endif
                                    @permission('create-incomes-receivables')
                                    <li class="divider"></li>
                                    <li><a href="{{ url('incomes/receivables/' . $item->id . '/duplicate') }}">{{ trans('general.duplicate') }}</a></li>
                                    @endpermission
                                    @permission('delete-incomes-receivables')
                                    @if (!$item->reconciled)
                                    <li class="divider"></li>
                                    <li>{!! Form::deleteLink($item, 'incomes/receivables') !!}</li>
                                    @endif
                                    @endpermission
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        @include('partials.admin.pagination', ['items' => $receivables, 'type' => 'receivables'])
    </div>
    <!-- /.box-footer -->
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
        $("#filter-categories").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.categories', 1)]) }}"
        });

        $("#filter-customers").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.customers', 1)]) }}"
        });

        $("#filter-accounts").select2({
            placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        });
    });
</script>
@endpush

