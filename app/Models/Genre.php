<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = ['name'];

    public function talents()
    {
        return $this->belongsToMany(Talent::class, 'genre_talent');
    }
}
