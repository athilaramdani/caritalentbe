<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RespondInvitationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|in:accepted,rejected'
        ];
    }
}
