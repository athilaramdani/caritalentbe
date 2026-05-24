<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'budget' => 'sometimes|numeric|min:0',
            'event_date' => 'sometimes|date',
            'venue_name' => 'sometimes|string|max:255',
            'full_address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'city' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:dibuka,ditutup,selesai,dibatalkan',
            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'integer'
        ];
    }
}
