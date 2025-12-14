<?php

namespace App\Http\Requests\Revenues;

use App\Http\Requests\AppCustomRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class RevenueUpdateRequest extends AppCustomRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id'            => ['required', 'numeric'],
            'company_id'    => ['required', 'numeric'],
            'account_id'    => ['required', 'numeric'],
            'category_id'   => ['required', 'numeric'],
            'customer_id'   => ['nullable', 'numeric'],
            'description'   => ['nullable', 'string'],
            'paid_at'       => ['required', Rule::date()->format('Y-m-d')],
            'currency_code' => ['required', 'string', 'max:3'],
            'amount'        => ['required', 'numeric'],
        ];
    }
}
