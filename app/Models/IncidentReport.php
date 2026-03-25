<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidentReport extends Model
{
    use SoftDeletes;

    protected $table = "incident_reports";

    protected $fillable = [
        'user_id',
        'barangay_id',
        'map_coordinates',
        'emergency_id',
        'incident_id',
        'casualty_count',
        'distance',
        'distance_km',
        'site_location_id',
        'priority_score',
        'priority_level',
        'priority_label',
        'attachment',
        'responder_name',
        'responder_contact_no',
        'estimated_arrival',
        'datetime_arrived',
        'plate_no',
        'status',
        'remarks',
        'responder_remarks',
        'treatment_provided',
        'responder_attachment',
        'cancel_remarks',

        'minor_casualty_count',
        'serious_casualty_count',
        'deceased_casualty_count',

        'cancelled_by'
    ];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function emergency()
    {
        return $this->belongsTo(Emergency::class);
    }

    public function siteLocation(){
        return $this->belongsTo(SiteLocation::class, 'site_location_id', 'id');
    }
}
