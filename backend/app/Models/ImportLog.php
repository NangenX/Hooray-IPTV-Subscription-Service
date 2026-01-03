<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_size',
        'total_processed',
        'imported',
        'skipped',
        'errors',
        'log_file_path',
        'error_details',
        'created_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'total_processed' => 'integer',
        'imported' => 'integer',
        'skipped' => 'integer',
        'errors' => 'integer',
        'error_details' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_processed === 0) {
            return 0;
        }

        return round(($this->imported / $this->total_processed) * 100, 2);
    }
}
