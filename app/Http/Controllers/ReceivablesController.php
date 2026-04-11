<?php

namespace App\Http\Controllers;

use App\Http\Requests\Receivables\ReceivableCreateRequest;
use App\Http\Requests\Receivables\ReceivableUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Receivable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReceivablesController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render(
            'receivables/index',
            [
                'record_list'   => fn() => $this->loadRecords($request),
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->type('income')->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'customer_list' => fn() => Customer::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'filter_text'   => fn() => $request->get('filter_text', ''),
            ]
        );
    }

    public function new(Request $request): Response
    {
        $receivable = new Receivable();
        return Inertia::render(
            'receivables/form',
            [
                'receivable'    => $receivable,
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->type('income')->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'customer_list' => fn() => Customer::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
            ]
        );
    }

    public function edit(Receivable $receivable): Response
    {
        $receivable->recurring_frequency = $receivable->recurring?->frequency;
        $receivable->recurring_count = $receivable->recurring?->count;
        return Inertia::render(
            'receivables/form',
            [
                'receivable'    => $receivable,
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->type('income')->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'customer_list' => fn() => Customer::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
            ]
        );
    }

    public function update(Receivable $receivable, ReceivableUpdateRequest $request): RedirectResponse
    {
        $receivable->update($request->validated());
        $receivable->updateRecurring();
        return to_route('receivables.index');
    }

    public function create(ReceivableCreateRequest $request): RedirectResponse
    {
        $receivable = Receivable::create($request->validated());
        $receivable->createRecurring();
        return to_route('receivables.index');
    }

    private function loadRecords(Request $request): array
    {
        $transactions = Receivable::query()
            ->when($request->get('filter_text'), function ($query, $value) {
                return $query->where('title', 'like', "%{$value}%");
            })
            ->with(['recurring'])
            ->orderBy('due_at')
            ->get()
            ->map(function (Receivable $item) {
                return [
                    'id'                  => $item->id,
                    'account_id'          => $item->account_id,
                    'due_at'              => $item->due_at->format('Y-m-d'),
                    'currency_code'       => $item->currency_code,
                    'amount'              => $item->amount,
                    'title'               => $item->title,
                    'vendor_id'           => $item->vendor_id,
                    'category_id'         => $item->category_id,
                    'notes'               => $item->notes,
                    'recurring_frequency' => $item->recurring?->frequency,
                    'recurring_interval'  => $item->recurring?->interval,
                    'recurring_count'     => $item->recurring?->count,
                    //                    'attachment'    => $item->attachment,
                ];
            })
            ->collect()
            ->toArray();

        return array_values($transactions);
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        // TODO: check if is being used
        $receivable->delete();
        return to_route('receivables.index');
    }

    public function skipNext(Receivable $receivable): RedirectResponse
    {
        $recurring = $receivable->recurring;
        if (!$recurring) {
            return to_route('receivables.index');
        }

        switch ($recurring->frequency) {
            case 'daily':
                $receivable->due_at = $receivable->due_at->addDays($recurring->interval);
                break;
            case 'weekly':
                $receivable->due_at = $receivable->due_at->addWeeks($recurring->interval);
                break;
            case 'monthly':
                $receivable->due_at = $receivable->due_at->addMonths($recurring->interval);
                break;
            case 'yearly':
                $receivable->due_at = $receivable->due_at->addYears($recurring->interval);
                break;
        }

        $receivable->save();

        if ($recurring->count > 0) {
            $recurring->count--;
            $recurring->save();
        }

        return to_route('receivables.index');
    }
}
