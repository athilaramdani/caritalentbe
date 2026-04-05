<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'event_id', 'talent_id', 'source', 'message', 'proposed_price', 'offered_price', 'status'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function talent()
    {
        return $this->belongsTo(User::class, 'talent_id');
    }
}
