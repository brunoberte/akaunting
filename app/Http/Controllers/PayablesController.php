<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payables\PayableCreateRequest;
use App\Http\Requests\Payables\PayableUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Payable;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayablesController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render(
            'payables/index',
            [
                'record_list'   => fn() => $this->loadRecords($request),
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->type('expense')->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'vendor_list'   => fn() => Vendor::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'filter_text'   => fn() => $request->get('filter_text', ''),
            ]
        );
    }

    public function new(Request $request): Response
    {
        $payable = new Payable();
        return Inertia::render(
            'payables/form',
            [
                'payable'       => $payable,
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->type('expense')->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'vendor_list'   => fn() => Vendor::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
            ]
        );
    }

    public function edit(Payable $payable): Response
    {
        $payable->recurring_frequency = $payable->recurring?->frequency;
        return Inertia::render(
            'payables/form',
            [
                'payable'       => $payable,
                'account_list'  => fn() => Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->collect()->toArray(),
                'category_list' => fn() => Category::enabled()->type('expense')->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
                'vendor_list'   => fn() => Vendor::enabled()->orderBy('name')->select(['name', 'id'])->get()->collect()->toArray(),
            ]
        );
    }

    public function update(Payable $payable, PayableUpdateRequest $request): RedirectResponse
    {
        $payable->update($request->validated());
        $payable->updateRecurring();
        return to_route('payables.index');
    }

    public function create(PayableCreateRequest $request): RedirectResponse
    {
        $payable = Payable::create($request->validated());
        $payable->createRecurring();
        return to_route('payables.index');
    }

    private function loadRecords(Request $request): array
    {
        $transactions = Payable::query()
            ->when($request->get('filter_text'), function ($query, $value) {
                return $query->where('title', 'like', "%{$value}%");
            })
            ->with(['recurring'])
            ->orderBy('due_at')
            ->get()
            ->map(function (Payable $item) {
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

    public function destroy(Payable $payable): RedirectResponse
    {
        $payable->delete();
        return to_route('payables.index');
    }

    public function skipNext(Payable $payable): RedirectResponse
    {
        $recurring = $payable->recurring;
        if (!$recurring) {
            return to_route('payables.index');
        }

        switch ($recurring->frequency) {
            case 'daily':
                $payable->due_at = $payable->due_at->addDays($recurring->interval);
                break;
            case 'weekly':
                $payable->due_at = $payable->due_at->addWeeks($recurring->interval);
                break;
            case 'monthly':
                $payable->due_at = $payable->due_at->addMonths($recurring->interval);
                break;
            case 'yearly':
                $payable->due_at = $payable->due_at->addYears($recurring->interval);
                break;
        }

        $payable->save();

        if ($recurring->count > 0) {
            $recurring->count--;
            $recurring->save();
        }

        return to_route('payables.index');
    }
}
