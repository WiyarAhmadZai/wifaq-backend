<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'staff_name',
        'task',
        'status',
        'started',
        'completed',
        'quality',
        'notes',
        'assigned_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
