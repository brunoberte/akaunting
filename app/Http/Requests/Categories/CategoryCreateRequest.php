<?php

namespace App\Http\Requests\Categories;

use App\Http\Requests\AppCustomRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CategoryCreateRequest extends AppCustomRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'numeric'],
            'name'       => ['required', 'string', 'max:191'],
            'type'       => ['required', 'string', Rule::in(['income', 'expense'])],
            'color'      => ['required', 'string', 'max:7'],
            'enabled'    => ['nullable', 'boolean'],
        ];
    }
}
