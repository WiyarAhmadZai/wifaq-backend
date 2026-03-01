<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'employee_id',
        'full_name',
        'department',
        'employment_type',
        'status',
        'base_salary',
        'required_time',
        'track_attendance',
        'total_classes',
        'rate_per_class',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
    ];

    public function contracts()
    {
        return $this->hasMany(StaffContract::class, 'staff_id');
    }

    public function activeContract()
    {
        return $this->hasOne(StaffContract::class, 'staff_id')
            ->where('status', 'active')
            ->latest('start_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
}
