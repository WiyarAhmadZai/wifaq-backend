<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'work_type',
        'contact',
        'address',
        'quality_rating',
        'price_rating',
        'deadline_rating',
        'response_rating',
        'payment_terms',
        'recommended_by',
        'date_engaged',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_engaged' => 'date',
        'quality_rating' => 'integer',
        'price_rating' => 'integer',
        'deadline_rating' => 'integer',
        'response_rating' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class);
    }
}
