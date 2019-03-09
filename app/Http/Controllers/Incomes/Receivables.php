<?php

namespace App\Http\Controllers\Incomes;

use App\Events\ReceivableUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Income\Receivable as Request;
use App\Jobs\Income\CreateReceivable;
use App\Models\Banking\Account;
use App\Models\Income\Customer;
use App\Models\Income\Receivable;
use App\Models\Setting\Category;
use App\Models\Setting\Currency;
use App\Traits\Currencies;
use App\Traits\DateTime;
use App\Traits\Uploads;
use Illuminate\Http\Response;

class Receivables extends Controller
{
    use DateTime, Currencies, Uploads;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $receivables = Receivable::with(['customer', 'category', 'account', 'recurring'])->collect(['due_at'=> 'asc']);
        $customers = collect(Customer::enabled()->orderBy('name')->pluck('name', 'id'));
        $categories = collect(Category::enabled()->type('income')->orderBy('name')->pluck('name', 'id'));
        $accounts = collect(Account::enabled()->orderBy('name')->pluck('name', 'id'));

        return view('incomes.receivables.index', compact('receivables', 'customers', 'categories', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $customers = Customer::enabled()->orderBy('name')->pluck('name', 'id');
        $currencies = Currency::enabled()->orderBy('name')->pluck('name', 'code');
        $currency = Currency::where('code', '=', setting('general.default_currency'))->first();
        $categories = Category::enabled()->type('income')->orderBy('name')->pluck('name', 'id');
        $accounts = Account::enabled()->orderBy('name')->pluck('name', 'id');

        return view('incomes.receivables.form', compact('customers', 'currencies', 'currency', 'categories', 'accounts'));
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
        dispatch(new CreateReceivable($request));

        $message = trans('messages.success.added', ['type' => trans_choice('general.receivables', 1)]);

        flash($message)->success();

        return redirect('incomes/receivables');
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  Receivable  $receivable
     *
     * @return Response
     */
    public function duplicate(Receivable $receivable)
    {
        $clone = $receivable->duplicate();

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.receivables', 1)]);

        flash($message)->success();

        return redirect('incomes/receivables/' . $clone->id . '/edit');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Receivable  $receivable
     *
     * @return Response
     */
    public function edit(Receivable $receivable)
    {
        $customers = Customer::enabled()->orderBy('name')->pluck('name', 'id');
        $currencies = Currency::enabled()->orderBy('name')->pluck('name', 'code');
        $currency = Currency::where('code', '=', $receivable->currency_code)->first();
        $categories = Category::enabled()->type('income')->orderBy('name')->pluck('name', 'id');
        $accounts = Account::enabled()->orderBy('name')->pluck('name', 'id');

        return view('incomes.receivables.form', compact('receivable', 'customers', 'currencies', 'currency', 'categories', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Receivable  $receivable
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Receivable $receivable, Request $request)
    {
        // Upload attachment
        if ($request->file('attachment')) {
            $media = $this->getMedia($request->file('attachment'), 'receivables');

            $receivable->attachMedia($media, 'attachment');
        }

        $receivable->update($request->input());

        // Recurring
        $receivable->updateRecurring();

        // Fire the event to make it extendible
        event(new ReceivableUpdated($receivable));

        $message = trans('messages.success.updated', ['type' => trans_choice('general.receivables', 1)]);

        flash($message)->success();

        return redirect('incomes/receivables');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Receivable $receivable
     *
     * @return Response
     * @throws \Exception
     */
    public function destroy(Receivable $receivable)
    {
        $this->deleteRelationships($receivable, ['recurring']);
        $receivable->delete();

        $message = trans('messages.success.deleted', ['type' => trans_choice('general.receivables', 1)]);

        flash($message)->success();

        return redirect('incomes/receivables');
    }
}
