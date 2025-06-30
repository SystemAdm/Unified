<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPassword, HasRoles, MustVerifyEmail,SoftDeletes;

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        $primaryEmail = $this->emails()->wherePivot('is_primary', true)->first();

        if (!$primaryEmail) {
            $primaryEmail = $this->emails()->first();
        }

        return $primaryEmail && $primaryEmail->pivot->verified_at !== null;
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        $primaryEmail = $this->emails()->wherePivot('is_primary', true)->first();

        if (!$primaryEmail) {
            $primaryEmail = $this->emails()->first();
        }

        if (!$primaryEmail) {
            return false;
        }

        $this->emails()->updateExistingPivot($primaryEmail->id, [
            'verified_at' => $this->freshTimestamp(),
        ]);

        return true;
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string|null
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'birthday',
        'password',
        'given_name',
        'family_name',
        'additional_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'email','name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'verified' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function emails()
    {
        return $this->belongsToMany(Email::class)->withPivot(['verified_at', 'is_primary'])->withTimestamps();
    }

    public function setPrimaryEmail(Email $email): static
    {
        // First, set is_primary to false for all emails
        $this->emails()->updateExistingPivot($this->emails()->pluck('emails.id'), ['is_primary' => false]);

        // Then set is_primary to true for the given email
        $this->emails()->updateExistingPivot($email->id, ['is_primary' => true]);

        return $this;
    }

    public function getEmailAttribute(): ?string
    {
        // Check if the relationship is already loaded to avoid additional queries
        if ($this->relationLoaded('emails')) {
            // Get primary email from the loaded relationship
            $primaryEmail = $this->emails->where('pivot.is_primary', true)->first();

            // If no primary email, get first email
            if (!$primaryEmail) {
                $primaryEmail = $this->emails->first();
            }
        } else {
            // Get primary email with a query
            $primaryEmail = $this->emails()->wherePivot('is_primary', true)->first();

            // If no primary email, get first email
            if (!$primaryEmail) {
                $primaryEmail = $this->emails()->first();
            }
        }

        // Return the email address or null
        return $primaryEmail ? $primaryEmail->address : null;
    }

    public function setEmailAttribute(string $email): static
    {
        // Find or create the email record
        $emailModel = Email::firstOrCreate(['address' => $email]);

        // Check if this email is already associated with the user
        if (!$this->emails()->where('emails.id', $emailModel->id)->exists()) {
            // Associate the email with the user
            $isPrimary = $this->emails()->count() === 0; // Set as primary if it's the first email
            $this->emails()->attach($emailModel, ['is_primary' => $isPrimary]);
        }

        return $this;
    }

    /**
     * Get the email address that should be used for password reset.
     *
     * @return string|null
     */
    public function getEmailForPasswordReset(): ?string
    {
        return $this->email;
    }

    public function getNameAttribute(): string
    {
        if (!empty($this->additional_name)) {
            return $this->given_name . ' ' . $this->additional_name . ' ' . $this->family_name;
        }
        return $this->given_name . ' ' . $this->family_name;
    }

    public function setNameAttribute(string $name): static
    {
        $parts = explode(' ', $name);

        $this->attributes['given_name'] = $parts[0] ?? '';

        if (count($parts) >= 3) {
            $this->attributes['additional_name'] = $parts[1];
            $this->attributes['family_name'] = implode(' ', array_slice($parts, 2));
        } else {
            $this->attributes['additional_name'] = null;
            $this->attributes['family_name'] = $parts[1] ?? '';
        }

        return $this;
    }

    /**
     * Get the organization that the user belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
