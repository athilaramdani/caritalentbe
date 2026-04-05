<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|in:accepted,rejected',
            'agreed_price' => 'required_if:status,accepted|numeric|min:0'
        ];
    }
}
