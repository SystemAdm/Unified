<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enum\RelationGuarded;
use App\Enum\RelationGuardian;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
// Payment functionality has been removed
// use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CanResetPassword, HasRoles, MustVerifyEmail, SoftDeletes; // Billable trait removed

    /**
     * Determine if the user has verified their email address.
     * Returns true if the user doesn't have an email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        $primaryEmail = $this->getPrimaryEmail();

        // If the user doesn't have an email, consider it as verified
        if (!$primaryEmail) {
            return true;
        }

        return $primaryEmail->pivot->verified_at !== null;
    }

    /**
     * Get the primary email for the user.
     *
     * @return \App\Models\Email|null
     */
    public function getPrimaryEmail(): ?Email
    {
        // Check if the relationship is already loaded to avoid additional queries
        if ($this->relationLoaded('emails')) {
            // Get primary email from the loaded relationship
            $primaryEmail = $this->emails->first(function ($email) {
                return $email->pivot->is_primary == true;
            });

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

        return $primaryEmail;
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified(Email $email = null): bool
    {
        $email = $email ?? $this->getPrimaryEmail();
        if (!$email) {
            \Log::debug('markEmailAsVerified called but no email found');
            return false;
        }

        \Log::debug('markEmailAsVerified called', ['email' => $email->address]);

        $this->emails()->updateExistingPivot($email->id, [
            'verified_at' => Carbon::now(),
        ]);

        // Note: The Verified event is dispatched in VerifyEmailController, so we don't need to dispatch it here

        return true;
    }

    /**
     * Mark the given user's phone as verified.
     *
     * @return bool
     */
    public function markPhoneAsVerified(Phone $phone = null): bool
    {
        $phone = $phone ?? $this->getPrimaryPhone();

        if (!$phone) {
            return false;
        }

        $this->phones()->updateExistingPivot($phone->id, [
            'verified_at' => Carbon::now(),
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
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        \Illuminate\Support\Facades\Notification::send($this, new \Illuminate\Auth\Notifications\ResetPassword($token));
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
        'account_type',
        'avatar_type',
        'avatar_path',
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
        'email', 'phone', 'name', 'avatar', 'email_verified_at'
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

    public function phones()
    {
        return $this->belongsToMany(Phone::class)->withPivot(['verified_at', 'is_primary'])->withTimestamps();
    }

    public function setPrimaryEmail(Email $email): static
    {
        // First, set is_primary to false for all emails
        $this->emails()->updateExistingPivot($this->emails()->pluck('emails.id'), ['is_primary' => false]);

        // Then set is_primary to true for the given email
        $this->emails()->updateExistingPivot($email->id, ['is_primary' => true]);

        return $this;
    }

    public function setPrimaryPhone(Phone $phone): static
    {
        // First, set is_primary to false for all phones
        $this->phones()->updateExistingPivot($this->phones()->pluck('phones.id'), ['is_primary' => false]);

        // Then set is_primary to true for the given phone
        $this->phones()->updateExistingPivot($phone->id, ['is_primary' => true]);

        return $this;
    }

    /**
     * Get the primary phone for the user.
     *
     * @return \App\Models\Phone|null
     */
    protected function getPrimaryPhone()
    {
        // Check if the relationship is already loaded to avoid additional queries
        if ($this->relationLoaded('phones')) {
            // Get primary phone from the loaded relationship
            $primaryPhone = $this->phones->first(function ($phone) {
                return $phone->pivot->is_primary == true;
            });

            // If no primary phone, get first phone
            if (!$primaryPhone) {
                $primaryPhone = $this->phones->first();
            }
        } else {
            // Get primary phone with a query
            $primaryPhone = $this->phones()->wherePivot('is_primary', true)->first();

            // If no primary phone, get first phone
            if (!$primaryPhone) {
                $primaryPhone = $this->phones()->first();
            }
        }

        return $primaryPhone;
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
            // Use the helper method to get the primary email
            $primaryEmail = $this->getPrimaryEmail();
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
        } else {
            // If the email exists and is not primary, make it primary
            $primaryEmail = $this->getPrimaryEmail();
            if (!$primaryEmail || $primaryEmail->id !== $emailModel->id) {
                $this->setPrimaryEmail($emailModel);
            }
        }

        return $this;
    }

    public function getPhoneAttribute(): ?string
    {
        // Check if the relationship is already loaded to avoid additional queries
        if ($this->relationLoaded('phones')) {
            // Get primary phone from the loaded relationship
            $primaryPhone = $this->phones->where('pivot.is_primary', true)->first();

            // If no primary phone, get first phone
            if (!$primaryPhone) {
                $primaryPhone = $this->phones->first();
            }
        } else {
            // Use the helper method to get the primary phone
            $primaryPhone = $this->getPrimaryPhone();
        }

        // Return the full phone number for tests
        if ($primaryPhone) {
            return $primaryPhone->number;
        }

        return null;
    }

    public function setPhoneAttribute(string $phone): static
    {
        // Create a new phone model and set the phone_number attribute
        $phoneModel = new Phone();
        $phoneModel->phone_number = $phone;

        // Check if a phone with the same country_code and number already exists
        $existingPhone = Phone::where('country_code', $phoneModel->country_code)
            ->where('number', $phoneModel->number)
            ->first();

        if ($existingPhone) {
            $phoneModel = $existingPhone;
        } else {
            $phoneModel->save();
        }

        // Check if this phone is already associated with the user
        if (!$this->phones()->where('phones.id', $phoneModel->id)->exists()) {
            // Associate the phone with the user
            $isPrimary = $this->phones()->count() === 0; // Set as primary if it's the first phone
            $this->phones()->attach($phoneModel, ['is_primary' => $isPrimary]);
        } else {
            // If the phone exists and is not primary, make it primary
            $primaryPhone = $this->getPrimaryPhone();
            if (!$primaryPhone || $primaryPhone->id !== $phoneModel->id) {
                $this->setPrimaryPhone($phoneModel);
            }
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
        // Trim name parts to remove any leading/trailing spaces
        $givenName = trim($this->given_name ?? '');
        $familyName = trim($this->family_name ?? '');
        $additionalName = trim($this->additional_name ?? '');

        // Build name parts array with only non-empty parts
        $nameParts = [];
        if (!empty($givenName)) $nameParts[] = $givenName;
        if (!empty($additionalName)) $nameParts[] = $additionalName;
        if (!empty($familyName)) $nameParts[] = $familyName;

        // If all name parts are empty, return empty string
        if (empty($nameParts)) {
            return '';
        }

        // Join non-empty name parts and apply case formatting
        return mb_convert_case(implode(' ', $nameParts), MB_CASE_TITLE, 'UTF-8');
    }

    public function setNameAttribute(string $name): static
    {
        // Log the incoming name for debugging
        \Log::debug('User::setNameAttribute called with name: ' . $name);

        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
        $parts = explode(' ', $name);

        $this->attributes['given_name'] = $parts[0] ?? '';

        if (count($parts) >= 3) {
            $this->attributes['additional_name'] = $parts[1];
            $this->attributes['family_name'] = implode(' ', array_slice($parts, 2));
        } else {
            $this->attributes['additional_name'] = null;
            $this->attributes['family_name'] = $parts[1] ?? '';
        }

        // Log the resulting attributes for debugging
        \Log::debug('User::setNameAttribute result:', [
            'given_name' => $this->attributes['given_name'],
            'additional_name' => $this->attributes['additional_name'],
            'family_name' => $this->attributes['family_name']
        ]);

        return $this;
    }

    /**
     * Get the organization that the user belongs to.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)->withPivot(['is_chairman', 'is_board', 'is_contact'])->withTimestamps();
    }

    public function getGivenNameAttribute($value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public function getFamilyNameAttribute($value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public function getAdditionalNameAttribute($value): ?string
    {
        return $value ? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8') : null;
    }

    /**
     * Get the avatar URL for the user.
     *
     * @return string|null
     */
    public function getAvatarAttribute(): ?string
    {
        switch ($this->avatar_type) {
            case 'gravatar':
                $email = $this->email;
                if (!$email) {
                    return null;
                }
                $hash = md5(strtolower(trim($email)));
                $gravatarKey = config('services.gravatar.key');
                return "https://secure.gravatar.com/avatar/{$hash}?s=200&d=mp&r=g" . ($gravatarKey ? "&key={$gravatarKey}" : "");
            case 'image':
                return $this->avatar_path ? asset('storage/' . $this->avatar_path) : null;
            case 'silhouette':
            case 'initials':
            default:
                return null; // Frontend will use initials or silhouette as fallback
        }
    }

    /**
     * Get the email verification timestamp.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getEmailVerifiedAtAttribute()
    {
        $primaryEmail = $this->getPrimaryEmail();

        if (!$primaryEmail) {
            return null;
        }

        return $primaryEmail->pivot->verified_at;
    }

    /**
     * Get the guardians of the user.
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_child', 'child_id', 'guardian_id')
            ->withPivot(['relation_guarded', 'relation_guardian', 'verified_at', 'verified_by'])
            ->withTimestamps();
    }

    /**
     * Get the children under the user's guardianship.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_child', 'guardian_id', 'child_id')
            ->withPivot(['relation_guarded', 'relation_guardian', 'verified_at', 'verified_by'])
            ->withTimestamps();
    }

    /**
     * Add a guardian to the user with specified relationship types.
     *
     * @param User $guardian The guardian user
     * @param RelationGuarded $relationGuarded How the child is related to the guardian
     * @param RelationGuardian $relationGuardian How the guardian is related to the child
     * @return $this
     */
    public function addGuardian(User $guardian, RelationGuarded $relationGuarded, RelationGuardian $relationGuardian): static
    {
        $this->guardians()->attach($guardian, [
            'relation_guarded' => $relationGuarded->value,
            'relation_guardian' => $relationGuardian->value,
        ]);

        return $this;
    }

    /**
     * Add a child to the user's guardianship with specified relationship types.
     *
     * @param User $child The child user
     * @param RelationGuarded $relationGuarded How the child is related to the guardian
     * @param RelationGuardian $relationGuardian How the guardian is related to the child
     * @return $this
     */
    public function addChild(User $child, RelationGuarded $relationGuarded, RelationGuardian $relationGuardian): static
    {
        $this->children()->attach($child, [
            'relation_guarded' => $relationGuarded->value,
            'relation_guardian' => $relationGuardian->value,
        ]);

        return $this;
    }

    /**
     * Verify the guardian relationship with a child.
     *
     * @param User $child The child user
     * @param User $verifier The admin user who verified the relationship
     * @return $this
     */
    public function verifyGuardianship(User $child, User $verifier): static
    {
        $this->children()->updateExistingPivot($child->id, [
            'verified_at' => now(),
            'verified_by' => $verifier->id,
        ]);

        return $this;
    }

    /**
     * Check if the user is older than 18 years.
     *
     * @return bool
     */
    public function isOlderThanEighteen(): bool
    {
        return $this->birthday && \Carbon\Carbon::parse($this->birthday)->age >= 18;
    }

    /**
     * This method previously created a setup intent for the user with explicit Stripe API key setting.
     * Payment functionality has been removed.
     *
     * @return null
     */
    public function createSetupIntentWithKey()
    {
        // Payment functionality has been removed
        return null;
    }
}
