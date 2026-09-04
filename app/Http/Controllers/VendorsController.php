<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vendors\VendorCreateRequest;
use App\Http\Requests\Vendors\VendorUpdateRequest;
use App\Models\Currency;
use App\Models\Vendor;
use App\Settings\SettingHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class VendorsController extends Controller
{
    public function index(Request $request): Response
    {
        if (!$request->exists('filter_enabled')) {
            $request->merge(['filter_enabled' => '1']);
        }
        return Inertia::render(
            'vendors/index',
            [
                'vendors'        => fn() => $this->loadVendors($request),
                'filter_text'    => fn() => $request->get('filter_text', ''),
                'filter_enabled' => fn() => $request->get('filter_enabled', ''),
            ]
        );
    }

    public function new(Request $request): Response
    {
        $defaultCurrency = SettingHelper::get('general.default_currency', 'BRL');
        $vendor = new Vendor();
        $vendor->currency_code = $defaultCurrency;
        $vendor->enabled = true;

        return Inertia::render(
            'vendors/form',
            [
                'vendor'     => fn() => $vendor,
                'currencies' => fn() => $this->getCurrencyList(),
            ]
        );
    }

    public function edit(Vendor $vendor, Request $request): Response
    {
        return Inertia::render(
            'vendors/form',
            [
                'vendor'     => $vendor,
                'currencies' => fn() => $this->getCurrencyList(),
            ]
        );
    }

    public function update(Vendor $vendor, VendorUpdateRequest $request): RedirectResponse
    {
        $vendor->update($request->validated());
        return to_route('vendors.index');
    }

    public function create(VendorCreateRequest $request): RedirectResponse
    {
        Vendor::create($request->validated());
        return to_route('vendors.index');
    }

    private function loadVendors(Request $request): LengthAwarePaginator
    {
        $records = Vendor::query()
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
            ->through(function (Vendor $item) {
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

    public function destroy(Vendor $vendor): RedirectResponse
    {
        if ($vendor->payments()->exists()
            || $vendor->payables()->exists()
        ) {
            return back()->withErrors(['vendor' => 'This vendor is in use and cannot be deleted.']);
        }

        $vendor->delete();

        return to_route('vendors.index');
    }

    private function getCurrencyList(): array
    {
        return Currency::enabled()->orderBy('name')->select(['code', 'name'])->get()->toArray();
    }
}
