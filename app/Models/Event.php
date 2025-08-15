<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'has_signup',
        'signup_start_date',
        'signup_end_date',
        'seats',
        'location_id',
        'min_age',
        'max_age',
        'class_restriction',
        'restriction',
        'is_cancelled',
        'cancelled_at',
        'cancellation_reason',
        'status',
    ];
    protected $appends = ['organizer'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'signup_start_date' => 'datetime',
        'signup_end_date' => 'datetime',
        'has_signup' => 'boolean',
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime',
        'seats' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    /**
     * Get the title attribute with first letter of each word capitalized
     */
    public function getTitleAttribute($value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Get the location associated with the event.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the users organizing the event.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    /**
     * Get the organizations organizing the event.
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class)
            ->withTimestamps();
    }

    /**
     * Get the users who are organizers of the event.
     */
    public function organizers()
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withTimestamps();
    }

    /**
     * Get the users who have signed up for the event.
     */
    public function signupped()
    {
        return $this->belongsToMany(User::class, 'event_signupped_user')
            ->withTimestamps();
    }

    /**
     * Get the users who have registered at the gate.
     */
    public function registered()
    {
        return $this->belongsToMany(User::class, 'event_registered_user')
            ->withTimestamps();
    }

    /**
     * Get the users who are attending the event.
     */
    public function attending()
    {
        return $this->belongsToMany(User::class, 'event_attending_user')
            ->withTimestamps();
    }

    /**
     * Get the users who have visited the event.
     */
    public function visited()
    {
        return $this->belongsToMany(User::class, 'event_visited_user')
            ->withTimestamps();
    }

    /**
     * Get the users who are inside the event.
     */
    public function inside()
    {
        return $this->belongsToMany(User::class, 'event_inside_user')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include past events.
     */
    public function scopePast($query)
    {
        return $query->where('start_date', '<', now());
    }

    /**
     * Scope a query to only include events with open signup.
     */
    public function scopeOpenSignup($query)
    {
        return $query->where('has_signup', true)
            ->where('signup_start_date', '<=', now())
            ->where('signup_end_date', '>', now());
    }

    /**
     * Check if the event has available seats.
     */
    public function hasAvailableSeats()
    {
        if ($this->seats === null || $this->seats < 0) {
            return true; // Unlimited seats
        }

        // Check if the relationship is already loaded to avoid additional queries
        if ($this->relationLoaded('signupped')) {
            return $this->getRelation('signupped')->count() < $this->seats;
        }

        // If not loaded, load it once and cache the result
        return $this->signupped()->count() < $this->seats;
    }

    /**
     * For backward compatibility with existing code
     */
    public function getUserAttribute()
    {
        // Prefer already-loaded relation to avoid extra queries
        if ($this->relationLoaded('users')) {
            return $this->getRelation('users')->first();
        }
        return $this->users()->first();
    }

    public function getOrganizationAttribute()
    {
        // Prefer already-loaded relation to avoid extra queries
        if ($this->relationLoaded('organizations')) {
            return $this->getRelation('organizations')->first();
        }
        return $this->organizations()->first();
    }

    public function getOrganizerAttribute()
    {
        // Ensure both relations are loaded to avoid N+1 issues
        if (!$this->relationLoaded('organizations') || !$this->relationLoaded('users')) {
            $this->loadMissing(['organizations', 'users']);
        }

        $link = null;
        $name = null;

        // Collect all names
        $organizationNames = $this->organizations?->pluck('name')->filter()->values()->all() ?? [];
        $userNames = $this->users?->pluck('name')->filter()->values()->all() ?? [];

        // Build display name: organizations first, then users in parentheses if both exist
        if (!empty($organizationNames) && !empty($userNames)) {
            $name = implode(', ', $organizationNames) . ' (' . implode(', ', $userNames) . ')';
        } elseif (!empty($organizationNames)) {
            $name = implode(', ', $organizationNames);
        } elseif (!empty($userNames)) {
            $name = implode(', ', $userNames);
        } else {
            $name = null;
        }

        // Set link to the first organization if present; otherwise first user
        if (!empty($organizationNames) && $this->organizations->isNotEmpty()) {
            $firstOrg = $this->organizations->first();
            $link = '/admin/organizations/' . $firstOrg->id . '/edit';
        } elseif (!empty($userNames) && $this->users->isNotEmpty()) {
            $firstUser = $this->users->first();
            $link = '/admin/users/' . $firstUser->id;
        }

        if ($name === null) {
            return null;
        }

        return [
            'link' => $link,
            'name' => $name,
        ];
    }

    /**
     * Get the number of available seats for the event.
     *
     * @return int|null
     */
    public function getAvailableSeatsAttribute()
    {
        if ($this->seats === null || $this->seats < 0) {
            return null; // Unlimited seats
        }

        // Check if the relationship is already loaded to avoid additional queries
        if ($this->relationLoaded('signupped')) {
            return $this->seats - $this->getRelation('signupped')->count();
        }

        // If not loaded, load it once and cache the result
        return $this->seats - $this->signupped()->count();
    }
}
