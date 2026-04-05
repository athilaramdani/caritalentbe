<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        return true; // We perform authorization in controller logic
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'event_date' => 'required|date',
            'venue_name' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'city' => 'required|string|max:255',
            'status' => 'nullable|in:draft,open,closed,completed,cancelled',
            'genre_ids' => 'nullable|array',
            'genre_ids.*' => 'integer'
        ];
    }
}
