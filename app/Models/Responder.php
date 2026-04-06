<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responder extends Model
{
    use SoftDeletes;

    protected $table = 'responders';

    protected $fillable = ['user_id', 'is_active', 'created_by', 'updated_by', 'deleted_by'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
