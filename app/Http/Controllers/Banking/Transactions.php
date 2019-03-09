<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Models\Banking\Account;
use App\Models\Banking\Transaction;
use App\Models\Expense\Payment;
use App\Models\Income\Revenue;
use App\Models\Setting\Category;

class Transactions extends Controller
{

    public $transactions = [];

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $request = request();

        $accounts = collect(Account::enabled()->pluck('name', 'id'));

        $types = collect(['expense' => 'Expense', 'income' => 'Income'])
            ->prepend(trans('general.all_type', ['type' => trans_choice('general.types', 2)]), '');

        $type = $request->get('type');

        $type_cats = empty($type) ? ['income', 'expense'] : $type;
        $categories = collect(Category::enabled()->type($type_cats)->pluck('name', 'id'));

        $input = $request->input();
        $limit = $request->get('limit', setting('general.list_limit', '25'));

        $transactions = Transaction::query()
            ->filter($input)
            ->orderBy('paid_at', 'desc')
            ->paginate($limit);

        return view('banking.transactions.index', compact('transactions', 'accounts', 'types', 'categories'));
    }

}
