<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username', 'password', 'full_name', 'first_name',
        'middle_name', 'last_name', 'email', 'mobile_no', 'address',
        'birth_date', 'barangay_id', 'role', 'profile_picture', 'site_location_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission($permissionSlug)
    {
        if ($this->permissions()->where('slug', 'admin_access')->exists()) {
            return true;
        }

        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function isAdmin()
    {
        return $this->role === 'administrator';
    }

    public function isAssistantAdmin()
    {
        return $this->role === 'assistant admin';
    }

    public function isResponder()
    {
        return $this->role === 'responder';
    }

    public function isCitizen()
    {
        return $this->role === 'citizen';
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function incidentReports()
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function responder()
    {
        return $this->hasOne(Responder::class, 'user_id', 'id');
    }

    public function siteLocation()
    {
        return $this->belongsTo(SiteLocation::class, 'site_location_id', 'id');
    }
}
