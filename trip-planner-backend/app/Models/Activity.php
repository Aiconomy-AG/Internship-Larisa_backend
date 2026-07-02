<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['trip_id', 'titlu_activitate', 'tip', 'descriere', 'ora', 'bifat'];
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}












