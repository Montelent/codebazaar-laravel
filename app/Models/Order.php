<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'email', 'status', 'total', 'currency',
        'payment_provider', 'stripe_session_id', 'payment_reference', 'paid_with_credits',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'paid_with_credits' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
