<?php

namespace App\Http\Controllers\Expenses;

use App\Events\PayableUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\Payable as Request;
use App\Jobs\Expense\CreatePayable;
use App\Models\Banking\Account;
use App\Models\Expense\Payable;
use App\Models\Expense\Vendor;
use App\Models\Setting\Category;
use App\Models\Setting\Currency;
use App\Traits\DateTime;
use App\Traits\Uploads;
use Illuminate\Http\Response;

class Payables extends Controller
{
    use DateTime, Uploads;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $payables = Payable::with(['vendor', 'category', 'account', 'recurring'])->collect(['due_at'=> 'asc']);
        $vendors = collect(Vendor::enabled()->orderBy('name')->pluck('name', 'id'));
        $categories = collect(Category::enabled()->type('expense')->orderBy('name')->pluck('name', 'id'));
        $accounts = collect(Account::enabled()->orderBy('name')->pluck('name', 'id'));

        return view('expenses.payables.index', compact('payables', 'vendors', 'categories', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $vendors = Vendor::enabled()->orderBy('name')->pluck('name', 'id');
        $currencies = Currency::enabled()->orderBy('name')->pluck('name', 'code');
        $currency = Currency::where('code', '=', setting('general.default_currency'))->first();
        $categories = Category::enabled()->type('expense')->orderBy('name')->pluck('name', 'id');
        $accounts = Account::enabled()->orderBy('name')->pluck('name', 'id');

        return view('expenses.payables.form', compact('vendors', 'currencies', 'currency', 'categories', 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        dispatch(new CreatePayable($request));

        $message = trans('messages.success.added', ['type' => trans_choice('general.payables', 1)]);

        flash($message)->success();

        return redirect('expenses/payables');
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  Payable  $payable
     *
     * @return Response
     */
    public function duplicate(Payable $payable)
    {
        $clone = $payable->duplicate();

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.payables', 1)]);

        flash($message)->success();

        return redirect('expenses/payables/' . $clone->id . '/edit');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Payable  $payable
     *
     * @return Response
     */
    public function edit(Payable $payable)
    {
        $vendors = Vendor::enabled()->orderBy('name')->pluck('name', 'id');
        $currencies = Currency::enabled()->orderBy('name')->pluck('name', 'code');
        $currency = Currency::where('code', '=', $payable->currency_code)->first();
        $categories = Category::enabled()->type('expense')->orderBy('name')->pluck('name', 'id');
        $accounts = Account::enabled()->orderBy('name')->pluck('name', 'id');

        return view('expenses.payables.form', compact('payable', 'vendors', 'currencies', 'currency', 'categories', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Payable  $payable
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Payable $payable, Request $request)
    {
        // Upload attachment
        if ($request->file('attachment')) {
            $media = $this->getMedia($request->file('attachment'), 'payables');

            $payable->attachMedia($media, 'attachment');
        }

        $payable->update($request->input());

        // Recurring
        $payable->updateRecurring();

        // Fire the event to make it extendible
        event(new PayableUpdated($payable));

        $message = trans('messages.success.updated', ['type' => trans_choice('general.payables', 1)]);

        flash($message)->success();

        return redirect('expenses/payables');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Payable $payable
     *
     * @return Response
     * @throws \Exception
     */
    public function destroy(Payable $payable)
    {
        $this->deleteRelationships($payable, ['recurring']);
        $payable->delete();

        $message = trans('messages.success.deleted', ['type' => trans_choice('general.payables', 1)]);

        flash($message)->success();

        return redirect('expenses/payables');
    }
}
