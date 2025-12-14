<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Vendor;
use App\Settings\SettingHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TransactionsController extends Controller
{
    public function index(Request $request): Response
    {
        if (!$request->exists('account_id') || $request->get('account_id', '') == '') {
            $account_id = SettingHelper::get('general.default_account');
            $request->merge(['account_id' => $account_id]);
        }
        return Inertia::render(
            'transactions/index',
            [
                'pagination_data' => fn() => $this->loadTransactions($request),
                'account_list'    => fn() => Account::enabled()->orderBy('name')->select(['name', 'currency_code', 'id'])->get()->collect()->toArray(),
                'account_id'      => fn() => $request->get('account_id', SettingHelper::get('general.default_account')),
                'category_list'   => fn() => Category::enabled()->orderBy('name')->select(['id', 'name', 'type'])->get()->collect()->toArray(),
            ]
        );
    }

    private function loadTransactions(Request $request): LengthAwarePaginator
    {
        /** @var Account $account */
        $account = Account::query()->find($request->get('account_id'));
        $transfer_category = Category::transfer();
        $table_prefix = env('DB_PREFIX', 'ak_');
        $payments = DB::table('payments')
            ->selectRaw(<<<SQL
                {$table_prefix}payments.id,
                'Payment' as record_type,
                {$table_prefix}payments.paid_at,
                {$table_prefix}payments.created_at,
                {$table_prefix}payments.amount,
                {$table_prefix}payments.currency_code,
                {$table_prefix}payments.account_id,
                {$table_prefix}payments.category_id,
                null as customer_id,
                {$table_prefix}payments.vendor_id,
                {$table_prefix}payments.description,
                {$table_prefix}revenues.account_id as transfer_account_id
            SQL
            )
            ->leftJoin('transfers', 'transfers.payment_id', '=', 'payments.id')
            ->leftJoin('revenues', 'revenues.id', '=', 'transfers.revenue_id')
            ->where('payments.account_id', $account->id)
            ->whereNull('payments.deleted_at')
            // FIXME: company scope
            ->orderBy('paid_at', 'desc');
        $revenues = DB::table('revenues')
            ->selectRaw(<<<SQL
                {$table_prefix}revenues.id,
                'Revenue' as record_type,
                {$table_prefix}revenues.paid_at,
                {$table_prefix}revenues.created_at,
                {$table_prefix}revenues.amount,
                {$table_prefix}revenues.currency_code,
                {$table_prefix}revenues.account_id,
                {$table_prefix}revenues.category_id,
                {$table_prefix}revenues.customer_id,
                null as vendor_id,
                {$table_prefix}revenues.description,
                {$table_prefix}payments.account_id as transfer_account_id
            SQL
            )
            ->leftJoin('transfers', 'transfers.revenue_id', '=', 'revenues.id')
            ->leftJoin('payments', 'payments.id', '=', 'transfers.payment_id')
            ->where('revenues.account_id', $account->id)
            ->whereNull('revenues.deleted_at')
            // FIXME: company scope
            ->orderBy('paid_at', 'desc');

        $balance = $account->getBalanceAttribute();
        $pagination_data = $revenues
            ->union($payments)
            ->orderBy('paid_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function ($item) use ($transfer_category, &$balance) {
                $is_payment = $item->record_type == 'Payment';
                $is_transfer = $item->category_id == $transfer_category;
                $ret = [
                    'id'                  => $item->id,
                    'record_type'         => $is_transfer ? 'Transfer' . $item->record_type : $item->record_type,
                    'paid_at'             => Carbon::createFromFormat('Y-m-d H:i:s', $item->paid_at)->format('Y-m-d'),
                    'is_transfer'         => $is_transfer,
                    'credit'              => !$is_payment ? $item->amount : null,
                    'debit'               => $is_payment ? $item->amount : null,
                    'balance'             => $balance,
                    'currency_code'       => $item->currency_code,
                    'account_id'          => $item->account_id,
                    'category_id'         => $item->category_id,
                    'vendor_id'           => $item->vendor_id,
                    'customer_id'         => $item->customer_id,
                    'description'         => $item->description,
                    'transfer_account_id' => $item->transfer_account_id,
                ];
                $balance -= (!$is_payment ? $item->amount : ($item->amount * -1));
                return $ret;
            });

        $pagination_data->appends([
            'account_id' => $request->get('account_id') ?? '',
        ]);

        return $pagination_data;
    }
}
