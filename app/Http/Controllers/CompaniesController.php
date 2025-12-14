<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\CompanyCreateRequest;
use App\Http\Requests\Companies\CompanyUpdateRequest;
use App\Models\Company;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompaniesController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render(
            'companies/index',
            [
                'record_list' => fn() => $this->loadRecords($request),
                'filter_text' => fn() => $request->get('filter_text', ''),
            ]
        );
    }

    public function new(Request $request): Response
    {
        $company = new Company();
        return Inertia::render(
            'companies/form',
            [
                'company' => $company,
            ]
        );
    }

    public function edit(Company $company): Response
    {
        $company->setSettings();
        return Inertia::render(
            'companies/form',
            [
                'company' => $company,
            ]
        );
    }

    public function update(Company $company, CompanyUpdateRequest $request): RedirectResponse
    {
        Setting::query()
            ->upsert(
                [
                    'company_id' => $company->id,
                    'key'        => 'general.company_name',
                    'value'      => $request->get('company_name'),
                ],
                ['company_id', 'key'],
                ['value']
            );
        $company->update($request->validated());
        return to_route('companies.index');
    }

    public function create(CompanyCreateRequest $request): RedirectResponse
    {
        $company = Company::create($request->validated());
        Setting::query()
            ->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'key'        => 'general.company_name',
                ],
                [
                    'value' => $request->get('company_name'),
                ]
            );
        return to_route('companies.index');
    }

    private function loadRecords(Request $request): array
    {
        $transactions = Company::query()
            ->when($request->get('filter_text'), function ($query, $value) {
                return $query->where('title', 'like', "%{$value}%");
            })
            ->get()
            ->map(function (Company $item) {
                $item->setSettings();
                return [
                    'id'         => $item->id,
                    'title'      => $item->company_name,
                    'domain'     => $item->domain,
                    'enabled'    => $item->enabled,
                    'user_count' => $item->users->count(),
                ];
            })
            ->collect()
            ->toArray();

        return array_values($transactions);
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();
        return to_route('companies.index');
    }
}
