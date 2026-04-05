<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'organizer_id', 'title', 'description', 'budget', 'event_date', 
        'venue_name', 'latitude', 'longitude', 'city', 'status'
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function genres()
    {
        // Assuming Genre model is created by Athila
        return $this->belongsToMany(Genre::class, 'event_genre');
    }
    
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
