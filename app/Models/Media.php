<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['talent_id', 'media_url', 'type'];

    public function talent()
    {
        return $this->belongsTo(Talent::class);
    }
}
