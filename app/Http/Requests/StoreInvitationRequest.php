<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'event_id' => 'required|exists:events,id',
            'talent_id' => 'required|exists:users,id',
            'offered_price' => 'required|numeric|min:0'
        ];
    }
}
