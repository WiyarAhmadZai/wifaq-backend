<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'position_applied',
        'qualification',
        'experience',
        'expected_salary',
        'cv_path',
        'status',
        'notes',
        'reviewed_by',
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
