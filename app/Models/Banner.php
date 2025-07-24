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

    protected $casts = [
        'is_published' => 'boolean',
        'from_datetime' => 'datetime',
        'to_datetime' => 'datetime',
        'type' => AnnouncementType::class,
        'visible_to_access' => 'array',
        'visible_to_role' => 'array',
    ];

    public function isActive(): bool
    {
        $now = now();
        return $this->is_published &&
               $this->from_datetime <= $now &&
               $this->to_datetime >= $now;
    }
}
