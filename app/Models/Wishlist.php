<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'link',
        'cost_per_unit',
        'count',
        'deadline',
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:2',
        'count' => 'integer',
        'deadline' => 'datetime',
    ];

    /**
     * Get the name attribute with first letter of each word capitalized
     */
    public function getNameAttribute($value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Get the payments for this wishlist item
     */
    public function payments(): HasMany
    {
        return $this->hasMany(WishlistPayment::class);
    }

    /**
     * Calculate the total amount paid for this wishlist item
     */
    public function totalPaid(): float
    {
        return $this->payments()->sum('count') * $this->cost_per_unit;
    }

    /**
     * Calculate the remaining amount needed for this wishlist item
     */
    public function remainingAmount(): float
    {
        $totalCost = $this->cost_per_unit * $this->count;
        $totalPaid = $this->totalPaid();

        return max(0, $totalCost - $totalPaid);
    }

    /**
     * Check if the deadline has passed
     */
    public function isExpired(): bool
    {
        return $this->deadline !== null && $this->deadline < now();
    }

    /**
     * Check if the item is fully funded
     */
    public function isFullyFunded(): bool
    {
        return $this->remainingAmount() <= 0;
    }
}
