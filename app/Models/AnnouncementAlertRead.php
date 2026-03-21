<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementAlertRead extends Model
{
    protected $table = 'announcement_alert_reads';

    public $timestamps = false;

    protected $fillable = [
        'announcement_alert_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
