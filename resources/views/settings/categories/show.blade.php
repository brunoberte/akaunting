@extends('layouts.admin')

@section('title', $category->name)
@section('new_button')
    <span class="new-button">
        <a href="{{ url('settings/categories/' . $category->id . '/edit') }}" class="btn btn-success btn-sm"><span class="fa fa-edit"></span> &nbsp;{{ trans('general.edit') }}</a>
    </span>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-3">
            <!-- Stats -->
            <div class="box box-success">
                <div class="box-body box-profile">
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Type: </b> <span class="pull-right">{{ $category->type }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Color: </b> <span class="pull-right"><i class="fa fa-2x fa-circle" style="color:{{ $category->color }};"></i></span>
                        </li>
                        <li class="list-group-item">
                            <b>Total records: </b> <a class="pull-right">{{ $list->total() }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Total amount: </b> <a class="pull-right">{{ $total_amount }}</a>
                        </li>
                    </ul>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
        <div class="col-md-9">
            <div class="table table-responsive">
                <table class="table table-striped table-hover" id="tbl-revenues">
                    <thead>
                    <tr>
                        <th class="col-md-3 hidden-xs">{{ trans_choice('general.accounts', 1) }}</th>
                        <th class="col-md-3">{{ trans('general.date') }}</th>
                        <th class="col-md-3 text-right amount-space">{{ trans('general.amount') }}</th>
                        <th width="1%"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list as $item)
                        <tr>
                            <td class="hidden-xs">{{ $item->account->name }}</td>
                            <td>{{ Date::parse($item->paid_at)->format($date_format) }}</td>
                            <td class="text-right amount-space">@money($item->amount, $item->currency_code, true)</td>
                            <td>
                                @php
                                    $url = false;
                                    switch ($category->type) {
                                        case 'income':
                                            $url = url('incomes/revenues/' . $item->id . '/edit');
                                            break;
                                        case 'expense':
                                            $url = url('expenses/payments/' . $item->id . '/edit');
                                            break;
                                    }
                                @endphp
                                @if ($url)
                                    <a class="btn btn-sm btn-default" href="{{ $url }}"><i class="fa fa-edit"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @include('partials.admin.pagination', ['items' => $list, 'type' => 'revenues'])
        </div>
    </div>
@endsection
