<?php

namespace App\Http\Controllers;

use App\Lib\Cashflow\Chart\CashflowChartService;
use App\Lib\Forecast\Chart\ForecastChartService;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Revenue;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $table_prefix = env('DB_PREFIX', 'ak_');

        $accounts = Account::enabled()
            ->selectRaw("
                {$table_prefix}accounts.id,
                {$table_prefix}accounts.name,
                {$table_prefix}accounts.currency_code,
                (select sum(amount) from {$table_prefix}payments p where p.account_id = {$table_prefix}accounts.id and p.deleted_at is null) as total_expenses,
                (select sum(amount) from {$table_prefix}revenues r where r.account_id = {$table_prefix}accounts.id and r.deleted_at is null) as total_incomes
            ")
            ->orderBy('name')
            ->get();

        $currencies = [];
        foreach($accounts as $item)
        {
            if(!isset($currencies[$item->currency_code]))
            {
                $currencies[$item->currency_code] = 0;
            }
        }

        return Inertia::render('dashboard/index', [
            'accounts' => $accounts,
            'currencies' => array_keys($currencies),
            'top_expense_categories' => fn() => $this->loadTopExpenseCategories($request),
            'top_income_categories' => fn() => $this->loadTopIncomeCategories($request),
        ]);
    }


    public function set_active_company(Request $request): RedirectResponse
    {
        session(['company_id' => $request->get('company_id')]);
        return to_route('dashboard');
    }

    public function forecast_chart(ForecastChartService $forecastChartService, Request $request): JsonResponse
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay()->addDays((int)$request->get('timerange', 90));
        $data = $forecastChartService->getDataOnPeriod($request->get('currency_code'), $start, $end);
        return response()->json($data);
    }

    public function cashflow_chart(CashflowChartService $cashflowChartService, Request $request): JsonResponse
    {
        $start = Carbon::today()->startOfDay()->subDays((int)$request->get('timerange', 90));
        $end = Carbon::today()->endOfDay();
        $data = $cashflowChartService->getDataOnPeriod($request->get('currency_code'), $start, $end);
        return response()->json($data);
    }

    private function loadTopExpenseCategories(Request $request): array
    {
        // TODO: cache
        $table_prefix = env('DB_PREFIX', 'ak_');
        return Payment::query()
            ->join("categories", "categories.id", '=', "payments.category_id")
            ->selectRaw("
                {$table_prefix}categories.name as name,
                {$table_prefix}categories.color as color,
                sum(amount) as value
            ")
            ->where('payments.paid_at', '>', Carbon::now()->subDays(90)->startOfDay())
            ->where('categories.type', 'expense')
            ->orderBy('value', 'desc')
            ->groupBy(["name", "color"])
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return ['name' => $item->name, 'value' => floatval($item->value), 'color' => $item->color];
            })
            ->toArray();
    }

    private function loadTopIncomeCategories(Request $request): array
    {
        // TODO: cache
        $table_prefix = env('DB_PREFIX', 'ak_');
        return Revenue::query()
            ->join("categories", "categories.id", '=', "revenues.category_id")
            ->selectRaw("
                {$table_prefix}categories.name as name,
                {$table_prefix}categories.color as color,
                sum(amount) as value
            ")
            ->where('revenues.paid_at', '>', Carbon::now()->subDays(90)->startOfDay())
            ->where('categories.type', 'income')
            ->orderBy('value', 'desc')
            ->groupBy(["name", "color"])
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return ['name' => $item->name, 'value' => floatval($item->value), 'color' => $item->color];
            })
            ->toArray();
    }
}
