<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'application_id',
        'agreed_price',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
