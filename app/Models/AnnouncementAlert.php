<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnouncementAlert extends Model
{
    use SoftDeletes;

    protected $table = "announcement_alerts";

    protected $fillable = [
        'announcement_title',
        'announcement_message',
        'for_citizens',
        'for_responders',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'announcement_alert_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
