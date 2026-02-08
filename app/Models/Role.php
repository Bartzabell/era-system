<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = ['name', 'created_by', 'updated_by', 'deleted_by'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
