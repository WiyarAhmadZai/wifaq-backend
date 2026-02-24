<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Planner extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'date',
        'day',
        'time',
        'description',
        'event_type',
        'target_audience',
        'location',
        'branch',
        'attendance',
        'notify_emails',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
