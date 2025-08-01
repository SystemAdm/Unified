<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SelfHostedApp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'public_link',
        'admin_link',
        'demo_username',
        'demo_password',
        'image',
        'status',
        'visibility',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the managers for the self-hosted app.
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'self_hosted_app_user')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include published apps.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft apps.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if the app is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the app is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
