<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use SoftDeletes;

    protected $table = "incidents";

    protected $fillable = [
        'incident_name',
        'definition',
        'severity_level',
        'base_severity',
        'base_time',
        'base_resources',
        'base_secondary',
        'emergency_id',
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function emergency()
    {
        return $this->belongsTo(Emergency::class, 'emergency_id', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
