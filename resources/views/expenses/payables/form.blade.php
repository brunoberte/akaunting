@extends('layouts.admin')

@section('title', trans((isset($payable) ? 'general.title.edit' : 'general.title.new'), ['type' => trans_choice('general.payables', 1)]))

@section('content')
<!-- Default box -->
<div class="box box-success">
    @if (isset($payable))
    {!! Form::model($payable, ['method' => 'PATCH', 'files' => true, 'url' => ['expenses/payables', $payable->id], 'role' => 'form', 'class' => 'form-loading-button', 'novalidate']) !!}
    @else
    {!! Form::open(['url' => 'expenses/payables', 'files' => true, 'role' => 'form', 'class' => 'form-loading-button', 'novalidate']) !!}
    @endif

    <div class="box-body">

        {{ Form::textGroup('title', trans('general.title_'), 'id-card-o', []) }}

        {{ Form::selectGroup('account_id', trans_choice('general.accounts', 1), 'money', $accounts) }}

        @stack('vendor_id_input_start')
        <div class="form-group col-md-6 required {{ $errors->has('vendor_id') ? 'has-error' : ''}}">
            {!! Form::label('vendor_id', trans_choice('general.vendors', 1), ['class' => 'control-label']) !!}
            <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-user"></i></div>
                {!! Form::select('vendor_id', $vendors, null, array_merge(['class' => 'form-control', 'placeholder' => trans('general.form.select.field', ['field' => trans_choice('general.vendors', 1)])])) !!}
                <div class="input-group-btn">
                    <button type="button" id="button-vendor" class="btn btn-default btn-icon"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            {!! $errors->first('vendor_id', '<p class="help-block">:message</p>') !!}
        </div>
        @stack('vendor_id_input_end')

        {{ Form::selectGroup('currency_code', trans_choice('general.currencies', 1), 'exchange', $currencies, setting('general.default_currency')) }}

        {{ Form::textGroup('amount', trans('payables.amount'), 'money', ['required' => 'required', 'autofocus' => 'autofocus', 'class' => 'form-control input-price']) }}

        @if (isset($payable))
        {{ Form::textGroup('due_at', trans('payables.due_date'), 'calendar',['id' => 'due_at', 'class' => 'form-control', 'required' => 'required', 'data-inputmask' => '\'alias\': \'yyyy-mm-dd\'', 'data-mask' => '', 'autocomplete' => 'off'], Date::parse($payable->due_at)->toDateString()) }}
        @else
        {{ Form::textGroup('due_at', trans('payables.due_date'), 'calendar',['id' => 'due_at', 'class' => 'form-control', 'required' => 'required', 'data-inputmask' => '\'alias\': \'yyyy-mm-dd\'', 'data-mask' => '', 'autocomplete' => 'off']) }}
        @endif

        {{ Form::textareaGroup('notes', trans_choice('general.notes', 2)) }}

        @stack('category_id_input_start')
        <div class="form-group col-md-6 required {{ $errors->has('category_id') ? 'has-error' : ''}}">
            {!! Form::label('category_id', trans_choice('general.categories', 1), ['class' => 'control-label']) !!}
            <div class="input-group">
                <div class="input-group-addon"><i class="fa fa-folder-open-o"></i></div>
                {!! Form::select('category_id', $categories, null, array_merge(['class' => 'form-control', 'placeholder' => trans('general.form.select.field', ['field' => trans_choice('general.categories', 1)])])) !!}
                <div class="input-group-btn">
                    <button type="button" id="button-category" class="btn btn-default btn-icon"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            {!! $errors->first('category_id', '<p class="help-block">:message</p>') !!}
        </div>
        @stack('category_id_input_end')

        @if (isset($payable))
        {{ Form::recurring('edit', $payable) }}
        @else
        {{ Form::recurring('create') }}
        @endif

        {{ Form::fileGroup('attachment', trans('general.attachment')) }}
    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        {{ Form::saveButtons('expenses/payables') }}
    </div>
    <!-- /.box-footer -->

    {!! Form::close() !!}
</div>
@endsection

@push('js')
    <script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
    @if (language()->getShortCode() != 'en')
    <script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/locales/bootstrap-datepicker.' . language()->getShortCode() . '.js') }}"></script>
    @endif
    <script src="{{ asset('public/js/bootstrap-fancyfile.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
    <script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/colorpicker/bootstrap-colorpicker.js') }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/datepicker/datepicker3.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap-fancyfile.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/colorpicker/bootstrap-colorpicker.css') }}">
@endpush

@push('scripts')
    <script type="text/javascript">
        $(document).on('click', '#button-add-item', function (e) {
            var currency_code = $('#currency_code').val();

            $.ajax({
                url: '{{ url("expenses/payables/addItem") }}',
                type: 'GET',
                dataType: 'JSON',
                data: {item_row: item_row, currency_code : currency_code},
                success: function(json) {
                    if (json['success']) {
                        $('#items tbody #addItem').before(json['html']);
                        //$('[rel=tooltip]').tooltip();

                        $('[data-toggle="tooltip"]').tooltip('hide');

                        var currency = json['data']['currency'];

                        $("#item-price-" + item_row).maskMoney({
                            thousands : currency.thousands_separator,
                            decimal : currency.decimal_mark,
                            precision : currency.precision,
                            allowZero : true,
                            prefix : (currency.symbol_first) ? currency.symbol : '',
                            suffix : (currency.symbol_first) ? '' : currency.symbol
                        });

                        $("#item-price-" + item_row).trigger('focusout');

                        item_row++;
                    }
                }
            });
        });

        $(document).ready(function(){

            @if (old('item'))
            $('#vendor_id').trigger('change');
            @endif

            $(".input-price").maskMoney({
                thousands : '{{ $currency->thousands_separator }}',
                decimal : '{{ $currency->decimal_mark }}',
                precision : {{ $currency->precision }},
                allowZero : true,
                @if($currency->symbol_first)
                prefix : '{{ $currency->symbol }}'
                @else
                suffix : '{{ $currency->symbol }}'
                @endif
            });

            $('.input-price').trigger('focusout');

            //Date picker
            $('#due_at').datepicker({
                format: 'yyyy-mm-dd',
                todayBtn: 'linked',
                weekStart: 1,
                autoclose: true,
                language: '{{ language()->getShortCode() }}'
            });

            $("#account_id").select2({
                placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
            });

            $("#vendor_id").select2({
                placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.vendors', 1)]) }}"
            });

            $("#currency_code").select2({
                placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.currencies', 1)]) }}"
            });

            $("#category_id").select2({
                placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.categories', 1)]) }}"
            });

            $('#attachment').fancyfile({
                text  : '{{ trans('general.form.select.file') }}',
                style : 'btn-default',
                placeholder : '{{ trans('general.form.no_file_selected') }}'
            });

            var autocomplete_path = "{{ url('common/items/autocomplete') }}";

            $(document).on('click', '.form-control.typeahead', function() {
                input_id = $(this).attr('id').split('-');

                item_id = parseInt(input_id[input_id.length-1]);

                $(this).typeahead({
                    minLength: 3,
                    displayText:function (data) {
                        return data.name + ' (' + data.sku + ')';
                    },
                    source: function (query, process) {
                        $.ajax({
                            url: autocomplete_path,
                            type: 'GET',
                            dataType: 'JSON',
                            data: 'query=' + query + '&type=payable&currency_code=' + $('#currency_code').val(),
                            success: function(data) {
                                return process(data);
                            }
                        });
                    },
                    afterSelect: function (data) {
                        $('#item-id-' + item_id).val(data.item_id);
                        $('#item-quantity-' + item_id).val('1');
                        $('#item-price-' + item_id).val(data.sale_price);

                        // This event Select2 Stylesheet
                        $('#item-price-' + item_id).trigger('focusout');

                        $('#item-total-' + item_id).html(data.total);

                        totalItem();
                    }
                });
            });

            $(document).on('change', '#currency_code, #items tbody select', function(){
                totalItem();
            });

            @if(old('item'))
                totalItem();
            @endif

            $('#title').trigger('focus');
        });

        function totalItem() {
            $.ajax({
                url: '{{ url("common/items/totalItem") }}',
                type: 'POST',
                dataType: 'JSON',
                data: $('#currency_code, #discount input[type=\'number\'], #items input[type=\'text\'],#items input[type=\'number\'],#items input[type=\'hidden\'], #items textarea, #items select').serialize(),
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(data) {
                    if (data) {
                        $.each( data.items, function( key, value ) {
                            $('#item-total-' + key).html(value);
                        });

                        $('#discount-text').text(data.discount_text);

                        $('#sub-total').html(data.sub_total);
                        $('#discount-total').html(data.discount_total);
                        $('#grand-total').html(data.grand_total);

                        $('.input-price').each(function(){
                            input_price_id = $(this).attr('id');
                            input_currency_id = input_price_id.replace('price', 'currency');

                            $('#' + input_currency_id).val(data.currency_code);

                            amount = $(this).maskMoney('unmasked')[0];

                            $(this).maskMoney({
                                thousands : data.thousands_separator,
                                decimal : data.decimal_mark,
                                precision : data.precision,
                                allowZero : true,
                                prefix : (data.symbol_first) ? data.symbol : '',
                                suffix : (data.symbol_first) ? '' : data.symbol
                            });

                            $(this).val(amount);

                            $(this).trigger('focusout');
                        });
                    }
                }
            });
        }

        $(document).on('click', '#button-vendor', function (e) {
            $('#modal-create-vendor').remove();

            $.ajax({
                url: '{{ url("modals/vendors/create") }}',
                type: 'GET',
                dataType: 'JSON',
                success: function(json) {
                    if (json['success']) {
                        $('body').append(json['html']);
                    }
                }
            });
        });

        $(document).on('click', '#button-category', function (e) {
            $('#modal-create-category').remove();

            $.ajax({
                url: '{{ url("modals/categories/create") }}',
                type: 'GET',
                dataType: 'JSON',
                data: {type: 'expense'},
                success: function(json) {
                    if (json['success']) {
                        $('body').append(json['html']);
                    }
                }
            });
        });
    </script>
@endpush
