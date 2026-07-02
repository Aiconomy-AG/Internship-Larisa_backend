<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = ['numit_destinatie', 'data_inceput', 'data_sfarsit'];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
