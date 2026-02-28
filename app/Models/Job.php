<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_postings';

    protected $fillable = [
        'position',
        'department',
        'location',
        'employment_type',
        'seats',
        'salary_range',
        'description',
        'requirements',
        'responsibilities',
        'benefits',
        'deadline',
        'status',
        'status_message',
        'created_by'
    ];

    protected $casts = [
        'deadline' => 'date',
        'seats' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship with User (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}