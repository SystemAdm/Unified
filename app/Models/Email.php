<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'address',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(['verified_at', 'is_primary'])->withTimestamps();
    }
}
