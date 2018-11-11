<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\Request;
use Date;

class Payable extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Check if store or update
        if ($this->getMethod() == 'PATCH') {
            $id = $this->payable; //->getAttribute('id');
        } else {
            $id = null;
        }

        return [
            'account_id' => 'required',
            'title' => 'required',
            'due_at' => 'required|date_format:Y-m-d H:i:s',
            'amount' => 'required',
            'currency_code' => 'required|string|currency',
            'vendor_id' => 'required|integer',
            'category_id' => 'required|integer',
            'attachment' => 'mimes:' . setting('general.file_types') . '|between:0,' . setting('general.file_size') * 1024,
        ];
    }

    public function withValidator($validator)
    {
        if ($validator->errors()->count()) {
            // Set date
            $due_at = Date::parse($this->request->get('due_at'))->format('Y-m-d');

            $this->request->set('due_at', $due_at);
        }
    }
}
