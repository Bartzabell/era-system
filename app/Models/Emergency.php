<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Emergency extends Model
{
    use SoftDeletes;

    protected $table = "emergencies";

    protected $fillable = [
        'emergency_name',
        'definition',
        'severity_level'
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
