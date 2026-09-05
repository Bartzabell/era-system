<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutyLog extends Model
{
    protected $fillable = ['user_id', 'duty_date', 'checked_in_at', 'checked_out_at'];

    protected $casts = [
        'duty_date' => 'date',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}