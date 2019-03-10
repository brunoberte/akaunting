<?php

namespace App\Http\Controllers\Modals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\Vendor as Request;
use App\Models\Expense\Vendor;
use App\Models\Setting\Currency;
use App\Traits\Uploads;

class Vendors extends Controller
{
    use Uploads;

    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Add CRUD permission check
        $this->middleware('permission:create-expenses-vendors')->only(['create', 'store', 'duplicate', 'import']);
        $this->middleware('permission:read-expenses-vendors')->only(['index', 'show', 'edit', 'export']);
        $this->middleware('permission:update-expenses-vendors')->only(['update', 'enable', 'disable']);
        $this->middleware('permission:delete-expenses-vendors')->only('destroy');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $currencies = Currency::enabled()->pluck('name', 'code');

        $html = view('modals.vendors.create', compact('currencies'))->render();

        return response()->json([
            'success' => true,
            'error' => false,
            'message' => 'null',
            'html' => $html,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $vendor = Vendor::create($request->all());

        // Upload logo
        if ($request->file('logo')) {
            $media = $this->getMedia($request->file('logo'), 'vendors');

            $vendor->attachMedia($media, 'logo');
        }

        $message = trans('messages.success.added', ['type' => trans_choice('general.vendors', 1)]);

        return response()->json([
            'success' => true,
            'error' => false,
            'data' => $vendor,
            'message' => $message,
            'html' => 'null',
        ]);
    }
}
