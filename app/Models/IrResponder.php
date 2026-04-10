<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IrResponder extends Model
{
    use SoftDeletes;

    protected $table = "ir_responders";

    protected $fillable = [
        'ir_id',
        'responder_id',
        'responder_name',
        'responder_type',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function incidentReport()
    {
        return $this->belongsTo(IncidentReport::class, 'ir_id', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'responder_id', 'id');
    }
}
