<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enum\Role;

class News extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_published',
        'published_at',
        'title',
        'excerpt',
        'content',
        'author_id',
        'featured_image',
        'visible_to_role',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'visible_to_role' => 'array',
    ];

    public function isPublished(): bool
    {
        return $this->is_published &&
               ($this->published_at === null || $this->published_at <= now());
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where(function($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
