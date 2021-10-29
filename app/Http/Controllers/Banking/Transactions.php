<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Models\Banking\Account;
use App\Models\Expense\Payment;
use App\Models\Income\Revenue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Transactions extends Controller
{

    public $transactions = [];

    public function index(Request $request): View
    {
        $accounts = collect(Account::enabled()->orderBy('name')->pluck('name', 'id'));

        if (!$request->exists('date')) {
            $start = Carbon::today()->subDays(60)->format('Y-m-d');
            $end = Carbon::today()->format('Y-m-d');
            $request->merge(['date' => $start . "_" . $end]);
        }
        if (!$request->exists('account_id')) {
            $account_id = setting()->get('general.default_account');
            $request->merge(['account_id' => $account_id]);
        }
        /** @var Account $account */
        $account = Account::query()->find($request->get('account_id'));
        $date = Carbon::createFromFormat('Y-m-d', explode('_', $request->get('date'))[0])->subDay();
        $balance = $account->getBalanceOnDate($date);

        $input = $request->input();

        /** @var Collection $payments */
        $payments = Payment::query()
            ->filter($input)
            ->get();

        /** @var Collection $revenues */
        $revenues = Revenue::query()
            ->filter($input)
            ->get();

        $transactions = $payments->concat($revenues)
            ->sortBy('paid_at');

        return view('banking.transactions.index', compact('transactions', 'accounts', 'balance', 'account'));
    }

}
