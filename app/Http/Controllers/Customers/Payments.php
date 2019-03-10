<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Income\Revenue as Payment;
use App\Models\Setting\Category;
use App\Models\Banking\Account;

use Auth;

class Payments extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $payments = Payment::with(['account', 'category'])->where('customer_id', '=', Auth::user()->customer->id)->paginate();

        $categories = collect(Category::enabled()->type('income')->pluck('name', 'id'))
            ->prepend(trans('general.all_type', ['type' => trans_choice('general.categories', 2)]), '');

        $accounts = collect(Account::enabled()->pluck('name', 'id'))
            ->prepend(trans('general.all_type', ['type' => trans_choice('general.accounts', 2)]), '');

        return view('customers.payments.index', compact('payments', 'categories', 'accounts'));
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @param  Payment  $payment
     *
     * @return Response
     */
    public function show(Payment $payment)
    {
        return view('customers.payments.show', compact('payment'));
    }
}
