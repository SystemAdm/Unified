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
    protected $appends = [];

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
        if ($this->seats === null) {
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
        return $this->users()->first();
    }

    public function getOrganizationAttribute()
    {
        return $this->organizations()->first();
    }

    public function getOrganizerAttribute()
    {
        // Load organizations if not already loaded
        if (!$this->relationLoaded('organizations')) {
            $this->load('organizations');
        }

        $link = null;
        $name = null;
        $organizationNames = [];

        // Get primary organization or user name
        $primaryName = $this->organization?->name ?? $this->user?->name ?? null;
        $primaryOrgId = $this->organization?->id ?? null;

        // Get all organization names, excluding the primary one to avoid duplication
        if ($this->organizations->isNotEmpty()) {
            foreach ($this->organizations as $organization) {
                // Skip the primary organization to avoid duplication
                if ($primaryOrgId && $organization->id === $primaryOrgId) {
                    continue;
                }
                $organizationNames[] = $organization->name;
            }
        }

        // If we have additional organizations, combine them with the primary name
        if (!empty($organizationNames)) {
            $name = $primaryName ? $primaryName . ' (' . implode(', ', $organizationNames) . ')' : implode(', ', $organizationNames);
        } else {
            $name = $primaryName;
        }

        // Set link to primary organizer
        $type = ($this->organization ?'/organizations':null) ?? ($this->user?'/users':null);
        $id= ($this->organization ? '/'.$this->organization->id.'/edit':null)??($this->user?'/'.$this->user->id:null);
        if ($type && $id) {
            $link = '/admin'.$type.$id;
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
        if ($this->seats === null) {
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
