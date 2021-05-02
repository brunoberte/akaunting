@stack('save_buttons_start')

@php
if ($cancel === 'banking/transactions') {
    $cancel = route('banking.transactions.index', ['account_id' => request('account_id')]);
} else {
    $cancel = url($cancel);
}
@endphp

<div class="{{ $col }}">
    <div class="form-group no-margin">
        {!! Form::button('<span class="fa fa-save"></span> &nbsp;' . trans('general.save'), ['type' => 'submit', 'class' => 'btn btn-success  button-submit', 'data-loading-text' => trans('general.loading')]) !!}
        <a href="{{ $cancel }}" class="btn btn-default"><span class="fa fa-times-circle"></span> &nbsp;{{ trans('general.cancel') }}</a>
    </div>
</div>

@stack('save_buttons_end')
