<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\AnnouncementType;
use App\Enum\Access;
use App\Enum\Role;
use Pest\Factories\Concerns\HigherOrderable;

class Banner extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_published',
        'is_recurring',
        'from_datetime',
        'to_datetime',
        'title',
        'description',
        'type',
        'visible_to_access',
        'visible_to_role',
        'link_norwegian',
        'link_english',
    ];

    // Ensure API responses include backend-computed status/labels
    protected $appends = [
        'is_active',
        'relative_day',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_recurring' => 'boolean',
        'from_datetime' => 'datetime',
        'to_datetime' => 'datetime',
        'type' => AnnouncementType::class,
        'visible_to_access' => 'array',
        'visible_to_role' => 'array',
    ];

    /**
     * Get the title attribute with first letter of each word capitalized
     */
    public function getTitleAttribute($value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public function isActive(): bool
    {
        $now = now();

        // For non-recurring banners, check if current date is within the date range
        if (!$this->is_recurring) {
            return $this->is_published &&
                   $this->from_datetime <= $now &&
                   $this->to_datetime >= $now;
        }

        // For recurring banners, check if current month and day match the date range
        $fromMonth = $this->from_datetime->month;
        $fromDay = $this->from_datetime->day;
        $toMonth = $this->to_datetime->month;
        $toDay = $this->to_datetime->day;
        $currentMonth = $now->month;
        $currentDay = $now->day;

        // If the recurring period spans across years (e.g., Dec 15 to Jan 15)
        if ($fromMonth > $toMonth || ($fromMonth == $toMonth && $fromDay > $toDay)) {
            return $this->is_published && (
                // Current date is after or on the start month/day
                ($currentMonth > $fromMonth || ($currentMonth == $fromMonth && $currentDay >= $fromDay)) ||
                // Current date is before or on the end month/day
                ($currentMonth < $toMonth || ($currentMonth == $toMonth && $currentDay <= $toDay))
            );
        } else {
            // Normal case: the period is within the same year
            return $this->is_published && (
                // Current date is within the month/day range
                ($currentMonth > $fromMonth || ($currentMonth == $fromMonth && $currentDay >= $fromDay)) &&
                ($currentMonth < $toMonth || ($currentMonth == $toMonth && $currentDay <= $toDay))
            );
        }
    }

    /**
     * Check if the banner is for today
     */
    public function isToday(): bool
    {
        $now = now();

        // For non-recurring banners
        if (!$this->is_recurring) {
            return $this->is_published &&
                   $this->from_datetime->isSameDay($now) &&
                   $this->to_datetime >= $now;
        }

        // For recurring banners
        return $this->is_published &&
               $this->from_datetime->month == $now->month &&
               $this->from_datetime->day == $now->day;
    }

    /**
     * Check if the banner is for tomorrow
     */
    public function isTomorrow(): bool
    {
        $tomorrow = now()->addDay();

        // For non-recurring banners
        if (!$this->is_recurring) {
            return $this->is_published &&
                   $this->from_datetime->isSameDay($tomorrow);
        }

        // For recurring banners
        return $this->is_published &&
               $this->from_datetime->month == $tomorrow->month &&
               $this->from_datetime->day == $tomorrow->day;
    }

    /**
     * Check if the banner was for yesterday
     */
    public function isYesterday(): bool
    {
        $yesterday = now()->subDay();

        // For non-recurring banners
        if (!$this->is_recurring) {
            return $this->is_published &&
                   $this->from_datetime->isSameDay($yesterday) &&
                   $this->to_datetime >= $yesterday;
        }

        // For recurring banners
        return $this->is_published &&
               $this->from_datetime->month == $yesterday->month &&
               $this->from_datetime->day == $yesterday->day;
    }

    /**
     * Get the relative day label for the banner (today, tomorrow, yesterday)
     */
    public function getRelativeDayLabel(): ?string
    {
        if ($this->isToday()) {
            return 'today';
        } elseif ($this->isTomorrow()) {
            return 'tomorrow';
        } elseif ($this->isYesterday()) {
            return 'yesterday';
        }

        return null;
    }

    // Accessors for serialization
    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    public function getRelativeDayAttribute(): ?string
    {
        return $this->getRelativeDayLabel();
    }
}
