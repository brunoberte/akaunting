<?php

namespace App\Http\Requests\Payables;

use App\Http\Requests\AppCustomRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class PayableCreateRequest extends AppCustomRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'          => ['required', 'exists:companies,id'],
            'account_id'          => ['required', 'exists:accounts,id'],
            'due_at'              => ['required', 'date', 'date_format:Y-m-d'],
            'title'               => ['required', 'string', 'max:191'],
            'currency_code'       => ['required', 'string', 'max:191'],
            'amount'              => ['required', 'numeric:strict', 'gt:0'],
            'vendor_id'           => ['required', 'exists:vendors,id'],
            'category_id'         => ['required', 'exists:categories,id'],
            'notes'               => ['nullable', 'string', 'max:2000'],
            'recurring_frequency' => ['required', 'string', Rule::in(['no', 'weekly', 'monthly', 'yearly'])],
        ];
    }
}
