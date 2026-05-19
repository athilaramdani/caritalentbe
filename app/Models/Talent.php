<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talent extends Model
{
    protected $table = 'talents';

    protected $fillable = [
        'id', 'user_id', 'stage_name', 'price_min', 'price_max', 'city',
        'bio', 'portfolio_link', 'verified', 'average_rating', 'total_reviews'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_talent');
    }
}
