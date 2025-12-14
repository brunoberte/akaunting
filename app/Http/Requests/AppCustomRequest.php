<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppCustomRequest extends FormRequest
{

    /**
     * Set the company id to the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $data = $this->all();
        $data['company_id'] = session('company_id') ?? '1'; // FIXME
        $this->getInputSource()->replace($data);
        return parent::getValidatorInstance();
    }
}
