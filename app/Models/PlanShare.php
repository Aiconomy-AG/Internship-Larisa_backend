<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanShare extends Model
{
    const EXPIRY_MINUTES = 10;

    protected $fillable = ['owner_id', 'viewer_id', 'first_accessed_at'];

    protected function casts(): array
    {
        return [
            'first_accessed_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function isExpired(): bool
    {
        return $this->first_accessed_at !== null
            && $this->first_accessed_at->addMinutes(self::EXPIRY_MINUTES)->isPast();
    }
}
