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
        return Inertia::render('payables/index', [
            'record_list'   => fn() => $this->loadRecords($request),
            'account_list'  => fn() => $this->getAccountList(),
            'category_list' => fn() => $this->getCategoryList(),
            'vendor_list'   => fn() => $this->getVendorList(),
            'filter_text'   => fn() => $request->get('filter_text', ''),
        ]);
    }

    public function new(): Response
    {
        return Inertia::render('payables/form', [
            'payable'       => new Payable(),
            'account_list'  => fn() => $this->getAccountList(),
            'category_list' => fn() => $this->getCategoryList(),
            'vendor_list'   => fn() => $this->getVendorList(),
        ]);
    }

    public function edit(Payable $payable): Response
    {
        $payable->recurring_frequency = $payable->recurring?->frequency;
        $payable->recurring_count = $payable->recurring?->count;

        return Inertia::render('payables/form', [
            'payable'       => $payable,
            'account_list'  => fn() => $this->getAccountList(),
            'category_list' => fn() => $this->getCategoryList(),
            'vendor_list'   => fn() => $this->getVendorList(),
        ]);
    }

    public function update(Payable $payable, PayableUpdateRequest $request): RedirectResponse
    {
        $payable->update($request->validated());
        $payable->updateRecurring($request->all());
        return to_route('payables.index');
    }

    public function create(PayableCreateRequest $request): RedirectResponse
    {
        $payable = Payable::create($request->validated());
        $payable->createRecurring($request->all());
        return to_route('payables.index');
    }

    private function loadRecords(Request $request): array
    {
        return Payable::query()
            ->filter($request->get('filter_text'))
            ->with(['recurring'])
            ->orderBy('due_at')
            ->get()
            ->map(fn(Payable $item) => $item->toArrayResponse())
            ->toArray();
    }

    public function skipNext(Payable $payable): RedirectResponse
    {
        $payable->skipNext();

        return to_route('payables.index');
    }

    public function destroy(Payable $payable): RedirectResponse
    {
        $payable->delete();

        return to_route('payables.index');
    }

    private function getAccountList(): array
    {
        return Account::enabled()->orderBy('name')->select(['id', 'name', 'currency_code'])->get()->toArray();
    }

    private function getCategoryList(): array
    {
        return Category::enabled()->type('expense')->orderBy('name')->select(['name', 'id'])->get()->toArray();
    }

    private function getVendorList(): array
    {
        return Vendor::enabled()->orderBy('name')->select(['name', 'id'])->get()->toArray();
    }
}
