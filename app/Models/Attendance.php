<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'day',
        'employee_id',
        'status',
        'expected_time',
        'arrived',
        'check_out',
        'left_without_notice',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'left_without_notice' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
