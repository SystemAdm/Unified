<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wishlist_id',
        'count',
        'payment_timestamp',
    ];

    protected $casts = [
        'count' => 'integer',
        'payment_timestamp' => 'datetime',
    ];

    /**
     * Get the user who made the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wishlist item this payment is for
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * Calculate the total amount of this payment
     */
    public function amount(): float
    {
        return $this->count * ($this->wishlist->cost_per_unit ?? 0);
    }
}
