<?php

namespace App\Http\Requests\Transfers;

use App\Http\Requests\AppCustomRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class TransferCreateRequest extends AppCustomRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'      => ['required', 'exists:companies,id'],
            'from_account_id' => ['required', 'exists:accounts,id'],
            'to_account_id'   => ['required', 'exists:accounts,id'],
            'transferred_at'  => ['required', 'date', 'date_format:Y-m-d'],
            'amount'          => ['required', 'numeric:strict', 'gt:0'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ];
    }
}
