<?php

namespace App\Http\Requests\Customers;

use App\Http\Requests\AppCustomRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerCreateRequest extends AppCustomRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id'    => ['required', 'numeric'],
            'name'          => ['required', 'string', 'max:191'],
            'email'         => ['nullable', 'email', 'max:191'],
            'phone'         => ['nullable', 'string', 'max:191'],
            'tax_number'    => ['nullable', 'string', 'max:191'],
            'currency_code' => ['required', 'string', 'max:191'],
            'website'       => ['nullable', 'string', 'max:191'],
            'address'       => ['nullable', 'string'],
            'reference'     => ['nullable', 'string', 'max:191'],
            'enabled'       => ['nullable', 'boolean'],
        ];
    }
}
