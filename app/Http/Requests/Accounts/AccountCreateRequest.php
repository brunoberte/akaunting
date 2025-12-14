<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\AppCustomRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class AccountCreateRequest extends AppCustomRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'      => ['required', 'numeric'],
            'name'            => ['required', 'string', 'max:191'],
            'number'          => ['nullable', 'string', 'max:191'],
            'currency_code'   => ['required', 'string', 'max:191'],
            'opening_balance' => ['required', 'numeric'],
            'bank_name'       => ['nullable', 'string', 'max:191'],
            'bank_phone'      => ['nullable', 'string', 'max:191'],
            'bank_address'    => ['nullable', 'string'],
            'enabled'         => ['nullable', 'boolean'],
        ];
    }
}
