<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Channel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'stream_url',
        'logo_url',
        'category',
        'language',
        'country',
        'is_active',
        'quality',
        'tvg_id',
        'tvg_name',
        'tvg_logo',
        'group_title',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'stream_url', 'is_active', 'group_title'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_channels');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGroup($query, $groupTitle)
    {
        return $query->where('group_title', $groupTitle);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
