<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'branch',
        'category',
        'urgency',
        'item',
        'quantity',
        'reason',
        'estimated_cost',
        'notes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'estimated_cost' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
