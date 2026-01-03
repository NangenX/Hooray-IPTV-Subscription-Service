<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'status',
        'email_verified_at',
        'current_package_id',
        'language_preference',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'email', 'status', 'current_package_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function currentPackage()
    {
        return $this->belongsTo(Package::class, 'current_package_id');
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
