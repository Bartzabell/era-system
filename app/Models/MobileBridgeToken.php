<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileBridgeToken extends Model
{
    protected $fillable = ['token', 'user_id', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return is_null($this->used_at) && $this->expires_at->isFuture();
    }
}