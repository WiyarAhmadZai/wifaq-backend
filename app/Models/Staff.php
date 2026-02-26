<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'employee_id',
        'full_name',
        'email',
        'phone',
        'password',
        'gender',
        'date_of_birth',
        'national_id',
        'nationality',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'role',
        'department',
        'designation',
        'hire_date',
        'employment_type',
        'status',
        'base_salary',
        'bank_account',
        'bank_name',
        'qualifications',
        'skills',
        'profile_photo',
        'supervisor_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
    ];

    public function supervisor()
    {
        return $this->belongsTo(Staff::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Staff::class, 'supervisor_id');
    }

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

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isHrManager()
    {
        return $this->role === 'hr_manager';
    }

    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    public function isObserver()
    {
        return $this->role === 'observer';
    }

    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }
}
