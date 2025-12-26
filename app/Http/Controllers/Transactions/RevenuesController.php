<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Revenues\RevenueCreateRequest;
use App\Http\Requests\Revenues\RevenueUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Revenue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RevenuesController extends Controller
{
    public function update(Revenue $revenue, RevenueUpdateRequest $request): RedirectResponse
    {
        $revenue->update($request->validated());
        return to_route('transactions.index', ['account_id' => $request->get('account_id')]);
    }

    public function create(RevenueCreateRequest $request): RedirectResponse
    {
        Revenue::create(array_merge(
            $request->validated(),
            ['currency_rate' => '0']
        ));
        return to_route('transactions.index', ['account_id' => $request->get('account_id')]);
    }

    public function new(Request $request): Response
    {
        $revenue = new Revenue();
        $revenue->account_id = $request->get('account_id', '');
        $revenue->paid_at = Carbon::today();
        return Inertia::render(
            'transactions/revenue-form',
            [
                'revenue'       => $revenue,
                'customer_list' => fn() => Customer::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['name', 'currency_code', 'id'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->orderBy('name')->where('type', 'income')->select(['id', 'name'])->get()->collect()->toArray(),
            ]
        );
    }

    public function edit(Revenue $revenue): Response
    {
        return Inertia::render(
            'transactions/revenue-form',
            [
                'revenue'       => $revenue,
                'customer_list' => fn() => Customer::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['name', 'currency_code', 'id'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->orderBy('name')->where('type', 'income')->select(['id', 'name'])->get()->collect()->toArray(),
            ]
        );
    }

    public function destroy(Revenue $revenue): RedirectResponse
    {
        $revenue->delete();
        return to_route('transactions.index', ['account_id' => $revenue->account_id]);
    }
}
