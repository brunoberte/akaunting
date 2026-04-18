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
        return Inertia::render('receivables/index', [
            'record_list'   => fn() => $this->loadRecords($request),
            'account_list'  => fn() => $this->getAccountList(),
            'category_list' => fn() => $this->getCategoryList(),
            'customer_list' => fn() => $this->getCustomerList(),
            'filter_text'   => fn() => $request->get('filter_text', ''),
        ]);
    }

    public function new(): Response
    {
        return Inertia::render('receivables/form', [
            'receivable'    => new Receivable(),
            'account_list'  => fn() => $this->getAccountList(),
            'category_list' => fn() => $this->getCategoryList(),
            'customer_list' => fn() => $this->getCustomerList(),
        ]);
    }

    public function edit(Receivable $receivable): Response
    {
        $receivable->recurring_frequency = $receivable->recurring?->frequency;
        $receivable->recurring_count = $receivable->recurring?->count;

        return Inertia::render('receivables/form', [
            'receivable'    => $receivable,
            'account_list'  => fn() => $this->getAccountList(),
            'category_list' => fn() => $this->getCategoryList(),
            'customer_list' => fn() => $this->getCustomerList(),
        ]);
    }

    public function update(Receivable $receivable, ReceivableUpdateRequest $request): RedirectResponse
    {
        $receivable->update($request->validated());
        $receivable->updateRecurring($request->all());
        return to_route('receivables.index');
    }

    public function create(ReceivableCreateRequest $request): RedirectResponse
    {
        $receivable = Receivable::create($request->validated());
        $receivable->createRecurring($request->all());
        return to_route('receivables.index');
    }

    private function loadRecords(Request $request): array
    {
        return Receivable::query()
            ->filter($request->get('filter_text'))
            ->with(['recurring'])
            ->orderBy('due_at')
            ->get()
            ->map(fn(Receivable $item) => $item->toArrayResponse())
            ->toArray();
    }

    public function skipNext(Receivable $receivable): RedirectResponse
    {
        $receivable->skipNext();

        return to_route('receivables.index');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        $receivable->delete();

        return to_route('receivables.index');
    }

    private function getAccountList(): array
    {
        return Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->toArray();
    }

    private function getCategoryList(): array
    {
        return Category::enabled()->type('income')->orderBy('name')->select(['name', 'id'])->get()->toArray();
    }

    private function getCustomerList(): array
    {
        return Customer::enabled()->orderBy('name')->select(['name', 'id'])->get()->toArray();
    }
}
