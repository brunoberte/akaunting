<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\CategoryCreateRequest;
use App\Http\Requests\Categories\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class CategoriesController extends Controller
{
    public function index(Request $request): Response
    {
        if (!$request->exists('filter_enabled')) {
            $request->merge(['filter_enabled' => '1']);
        }
        return Inertia::render(
            'categories/index',
            [
                'categories'     => fn() => $this->loadTransactions($request),
                'filter_text'    => fn() => $request->get('filter_text', ''),
                'filter_enabled' => fn() => $request->get('filter_enabled', ''),
            ]
        );
    }

    public function new(Request $request): Response
    {
        return Inertia::render(
            'categories/form',
            [
                'category' => fn() => new Category(),
            ]
        );
    }


    public function edit(Category $category, Request $request): Response
    {
        return Inertia::render(
            'categories/form',
            [
                'category' => $category,
            ]
        );
    }

    public function update(Category $category, CategoryUpdateRequest $request): RedirectResponse
    {
        $category->update($request->validated());
        return to_route('categories.index');
    }

    public function create(CategoryCreateRequest $request): RedirectResponse
    {
        Category::create($request->validated());
        return to_route('categories.index');
    }

    private function loadTransactions(Request $request): LengthAwarePaginator
    {
        $records = Category::query()
            ->whereIn('type', ['income', 'expense'])
            ->when($request->get('filter_text'), function ($query, $value) {
                return $query->where('name', 'like', "%{$value}%");
            })
            ->when($request->exists('filter_enabled'), function ($query, $value) use ($request) {
                if ($request->get('filter_enabled') == '') {
                    return $query;
                }
                return $query->where('enabled', $request->get('filter_enabled'));
            })
            ->when($request->exists('filter_type'), function ($query, $value) use ($request) {
                if ($request->get('filter_type') == '') {
                    return $query;
                }
                return $query->where('type', $request->get('filter_type'));
            })
            ->orderBy('name')
            ->paginate()
            ->through(function (Category $item) {
                return [
                    'id'      => $item->id,
                    'name'    => $item->name,
                    'type'    => $item->type,
                    'color'   => $item->color,
                    'enabled' => $item->enabled,

                ];
            });

        $records->appends([
            'filter_text'    => $request->get('filter_text') ?? '',
            'filter_enabled' => $request->get('filter_enabled') ?? '',
        ]);

        return $records;
    }

    public function destroy(Category $category): RedirectResponse
    {
        // TODO: check if is being used
        $category->delete();
        return to_route('categories.index');
    }
}
