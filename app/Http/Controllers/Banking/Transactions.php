<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Models\Banking\Account;
use App\Models\Banking\Transfer;
use App\Models\Expense\Payment;
use App\Models\Income\Revenue;
use App\Models\Setting\Category;
use Carbon\Carbon;

class Transactions extends Controller
{

    public $transactions = [];

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $accounts = collect(Account::enabled()->orderBy('name')->pluck('name', 'id'));

        if (!$request->exists('date')) {
            $start = Carbon::today()->subDays(60)->format('Y-m-d');
            $end = Carbon::today()->format('Y-m-d');
            $request->merge(['date' => "{$start}_{$end}"]);
        }
        if (!$request->exists('account_id')) {
            $account_id = setting()->get('general.default_account');
            $request->merge(['account_id' => $account_id]);
        }
        $account = Account::query()->find($request->get('account_id'));
        $date = Carbon::createFromFormat('Y-m-d', explode('_', $request->get('date'))[0])->subDay();
        $balance = $account->getBalanceOnDate($date);

        $input = $request->input();

        $payments = Payment::query()
            ->filter($input)
            ->get();

        $revenues = Revenue::query()
            ->filter($input)
            ->get();

        $transactions = $payments->concat($revenues)
            ->sortBy('paid_at');

        return view('banking.transactions.index', compact('transactions', 'accounts', 'balance', 'account'));
    }

}
