<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotline extends Model
{
    use SoftDeletes;

    protected $table = "hotlines";

    protected $fillable = [
        'hotline_name',
        'hotline_no',
        'description'
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
