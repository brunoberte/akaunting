<?php

namespace App\Http\Requests\Companies;

use App\Http\Requests\AppCustomRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CompanyCreateRequest extends AppCustomRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:191'],
            'domain'       => ['required', 'string', 'max:191'],
            'enabled'      => ['nullable', 'boolean'],
        ];
    }
}
