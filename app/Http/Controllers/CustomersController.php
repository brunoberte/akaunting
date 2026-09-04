<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\CustomerCreateRequest;
use App\Http\Requests\Customers\CustomerUpdateRequest;
use App\Models\Currency;
use App\Models\Customer;
use App\Settings\SettingHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class CustomersController extends Controller
{
    public function index(Request $request): Response
    {
        if (!$request->exists('filter_enabled')) {
            $request->merge(['filter_enabled' => '1']);
        }
        return Inertia::render(
            'customers/index',
            [
                'customers'      => fn() => $this->loadCustomers($request),
                'filter_text'    => fn() => $request->get('filter_text', ''),
                'filter_enabled' => fn() => $request->get('filter_enabled', ''),
            ]
        );
    }

    public function new(Request $request): Response
    {
        $defaultCurrency = SettingHelper::get('general.default_currency', 'BRL');
        $customer = new Customer();
        $customer->currency_code = $defaultCurrency;
        $customer->enabled = true;

        return Inertia::render(
            'customers/form',
            [
                'customer'   => fn() => $customer,
                'currencies' => fn() => $this->getCurrencyList(),
            ]
        );
    }

    public function edit(Customer $customer, Request $request): Response
    {
        return Inertia::render(
            'customers/form',
            [
                'customer'   => $customer,
                'currencies' => fn() => $this->getCurrencyList(),
            ]
        );
    }

    public function update(Customer $customer, CustomerUpdateRequest $request): RedirectResponse
    {
        $customer->update($request->validated());
        return to_route('customers.index');
    }

    public function create(CustomerCreateRequest $request): RedirectResponse
    {
        Customer::create($request->validated());
        return to_route('customers.index');
    }

    private function loadCustomers(Request $request): LengthAwarePaginator
    {
        $records = Customer::query()
            ->when($request->get('filter_text'), function ($query, $value) {
                return $query->where(function ($subQuery) use ($value) {
                    $subQuery->where('name', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                });
            })
            ->when($request->exists('filter_enabled'), function ($query, $value) use ($request) {
                if ($request->get('filter_enabled') == '') {
                    return $query;
                }
                return $query->where('enabled', $request->get('filter_enabled'));
            })
            ->orderBy('name')
            ->paginate()
            ->through(function (Customer $item) {
                return [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'email'         => $item->email,
                    'phone'         => $item->phone,
                    'tax_number'    => $item->tax_number,
                    'currency_code' => $item->currency_code,
                    'address'       => $item->address,
                    'website'       => $item->website,
                    'reference'     => $item->reference,
                    'enabled'       => $item->enabled,
                ];
            });

        $records->appends([
            'filter_text'    => $request->get('filter_text') ?? '',
            'filter_enabled' => $request->get('filter_enabled') ?? '',
        ]);

        return $records;
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->revenues()->exists()
            || $customer->receivables()->exists()
        ) {
            return back()->withErrors(['customer' => 'This customer is in use and cannot be deleted.']);
        }

        $customer->delete();

        return to_route('customers.index');
    }

    private function getCurrencyList(): array
    {
        return Currency::enabled()->orderBy('name')->select(['code', 'name'])->get()->toArray();
    }
}
