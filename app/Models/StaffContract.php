<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'staff_id',
        'contract_number',
        'contract_type',
        'start_date',
        'end_date',
        'probation_period_days',
        'probation_end_date',
        'probation_status',
        'salary',
        'allowances',
        'benefits',
        'job_description',
        'terms_conditions',
        'contract_file',
        'status',
        'renewal_alert_sent',
        'renewal_alert_date',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'probation_end_date' => 'date',
        'approved_at' => 'datetime',
        'renewal_alert_date' => 'date',
        'salary' => 'decimal:2',
        'allowances' => 'array',
        'benefits' => 'array',
        'renewal_alert_sent' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(Staff::class, 'approved_by');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isExpired()
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function isInProbation()
    {
        return $this->probation_status === 'pending' && 
               $this->probation_end_date && 
               $this->probation_end_date->isFuture();
    }

    public function daysUntilExpiry()
    {
        if (!$this->end_date) {
            return null;
        }
        return now()->diffInDays($this->end_date, false);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', now());
    }

    public function scopeNeedsRenewalAlert($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(30))
            ->where(function ($q) {
                $q->whereNull('renewal_alert_date')
                  ->orWhere('renewal_alert_date', '<', now()->subDays(7));
            });
    }
}
