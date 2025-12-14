<?php

namespace App\Http\Controllers;

use App\Http\Requests\Accounts\AccountCreateRequest;
use App\Http\Requests\Accounts\AccountUpdateRequest;
use App\Models\Account;
use App\Settings\SettingHelper;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class AccountsController extends Controller
{
    public function index(Request $request): Response
    {
        if (!$request->exists('filter_enabled')) {
            $request->merge(['filter_enabled' => '1']);
        }
        return Inertia::render(
            'accounts/index',
            [
                'accounts' => fn() => $this->loadAccounts($request),
                'default_currency' => fn() => SettingHelper::get('general.default_currency'),
                'filter_text' => fn() => $request->get('filter_text', ''),
                'filter_enabled' => fn() => $request->get('filter_enabled', ''),
            ]
        );
    }

    public function new(Request $request): Response
    {
        return Inertia::render(
            'accounts/form',
            [
                'account' => fn() => new Account(),
            ]
        );
    }


    public function edit(Account $account, Request $request): Response
    {
        return Inertia::render(
            'accounts/form',
            [
                'account' => $account,
            ]
        );
    }

    public function update(Account $account, AccountUpdateRequest $request): RedirectResponse
    {
        $account->update($request->validated());
        // TODO: handle default account
        return to_route('accounts.index');
    }

    public function create(AccountCreateRequest $request): RedirectResponse
    {
        Account::create($request->validated());
        // TODO: handle default account
        return to_route('accounts.index');
    }

    private function loadAccounts(Request $request): LengthAwarePaginator
    {
        $accounts = Account::query()
            ->orderBy('name')
            ->when($request->get('filter_text'), function ($query, $value) {
                return $query->where('name', 'like', "%{$value}%");
            })
            ->when($request->exists('filter_enabled'), function ($query, $value) use ($request) {
                if ($request->get('filter_enabled') == ''){
                    return $query;
                }
                return $query->where('enabled', $request->get('filter_enabled'));
            })
            ->paginate()
            ->through(function (Account $item) {
                return [
                    'id'              => $item->id,
                    'name'            => $item->name,
                    'number'          => $item->number,
                    'currency_code'   => $item->currency_code,
                    'opening_balance' => $item->opening_balance,
                    'current_balance' => $item->getBalanceOnDate(Carbon::now()),
                    'bank_name'       => $item->bank_name,
                    'bank_phone'      => $item->bank_phone,
                    'bank_address'    => $item->bank_address,
                    'enabled'         => $item->enabled,
                ];
            });

        $accounts->appends([
            'filter_text' => $request->get('filter_text') ?? '',
            'filter_enabled' => $request->get('filter_enabled') ?? '',
        ]);

        return $accounts;
    }

    public function destroy(Account $account): RedirectResponse
    {
        $account->delete();
        return to_route('accounts.index');
    }
}
