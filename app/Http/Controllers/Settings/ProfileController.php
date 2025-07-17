<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        // Log the incoming request data for debugging
        \Log::debug('ProfileController@update - Request data:', [
            'all' => $request->all(),
            'validated' => $request->validated(),
            'files' => $request->allFiles(),
            'headers' => $request->header()
        ]);

        $validated = $request->validated();
        $user = $request->user();

        // Explicitly set name to trigger setNameAttribute
        if (isset($validated['name']) && !empty($validated['name'])) {
            $user->name = $validated['name'];
        }

        // Explicitly set email to trigger setEmailAttribute
        if (isset($validated['email'])) {
            $oldEmail = $user->email;

            // If email is changed, create or update the email
            if ($oldEmail !== $validated['email']) {
                // Find or create the email
                $emailModel = \App\Models\Email::firstOrCreate(['address' => $validated['email']]);

                // Check if this email is already associated with the user
                if (!$user->emails()->where('emails.id', $emailModel->id)->exists()) {
                    // Detach all existing emails
                    $user->emails()->detach();

                    // Attach the new email as primary and unverified
                    $user->emails()->attach($emailModel, [
                        'is_primary' => true,
                        'verified_at' => null
                    ]);
                } else {
                    // If the email exists, make it primary and unverified
                    $user->emails()->updateExistingPivot($emailModel->id, [
                        'is_primary' => true,
                        'verified_at' => null
                    ]);

                    // Make all other emails non-primary
                    $user->emails()->where('emails.id', '!=', $emailModel->id)
                        ->update(['email_user.is_primary' => false]);
                }
            }
            // If email is not changed, do nothing to preserve verification status
        }

        // Handle avatar image upload if avatar_type is 'image'
        if (isset($validated['avatar_type']) && $validated['avatar_type'] === 'image' && $request->hasFile('avatar_image')) {
            // Store the uploaded image
            $path = $request->file('avatar_image')->store('avatars', 'public');
            $validated['avatar_path'] = $path;
        }

        // Log the user model before filling
        \Log::debug('ProfileController@update - User before filling:', [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_type' => $user->avatar_type,
            'avatar_path' => $user->avatar_path,
            'attributes' => $user->getAttributes()
        ]);

        // Fill other validated fields (excluding name and email which we've already handled)
        $fillableValidated = array_diff_key($validated, array_flip(['name', 'email']));
        $user->fill($fillableValidated);

        // Log the user model after filling but before saving
        \Log::debug('ProfileController@update - User after filling but before saving:', [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_type' => $user->avatar_type,
            'avatar_path' => $user->avatar_path,
            'attributes' => $user->getAttributes(),
            'dirty' => $user->getDirty()
        ]);

        $saved = $user->save();

        // Log the user model after saving
        \Log::debug('ProfileController@update - User after saving:', [
            'saved' => $saved,
            'id' => $user->id,
            'name' => $user->name,
            'avatar_type' => $user->avatar_type,
            'avatar_path' => $user->avatar_path,
            'attributes' => $user->getAttributes()
        ]);

        // If this is an AJAX request, return the updated user data
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ]);
        }

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Force delete the user instead of soft deleting
        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
    /**
     * Add a guardian to the user.
     */
    public function addGuardian(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'relation_guarded' => 'required|string|in:' . implode(',', $this->getRelationGuardedValues()),
            'relation_guardian' => 'required|string|in:' . implode(',', $this->getRelationGuardianValues()),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get the authenticated user
        $user = $request->user();

        // Create a temporary guardian user (will be properly registered when they follow the email link)
        $guardian = \App\Models\User::create([
            'given_name' => $request->name,
            'family_name' => '',
            'account_type' => 'guardian',
        ]);

        // Set the guardian's email
        $guardian->email = $request->email;

        // Create and associate the email with the guardian
        $email = \App\Models\Email::firstOrCreate(['address' => $request->email]);
        $guardian->emails()->attach($email->id, ['is_primary' => true, 'verified_at' => now()]);
        $guardian->setPrimaryEmail($email);

        // Add the guardian-child relationship
        $relationGuarded = \App\Enum\RelationGuarded::from($request->relation_guarded);
        $relationGuardian = \App\Enum\RelationGuardian::from($request->relation_guardian);

        $user->addGuardian($guardian, $relationGuarded, $relationGuardian);

        // Send email to the guardian
        $this->sendGuardianEmail($guardian, $user);

        return redirect()->route('profile.edit')->with('status', 'guardian-added');
    }

    /**
     * Send an email to the guardian with instructions on how to register their account.
     */
    private function sendGuardianEmail(\App\Models\User $guardian, \App\Models\User $user)
    {
        // Log the email sending attempt
        \Log::info('Sending guardian registration email', [
            'guardian_email' => $guardian->email,
            'user_name' => $user->name,
        ]);

        // Send the email
        \Illuminate\Support\Facades\Mail::to($guardian->email)->send(new \App\Mail\GuardianRegistrationMail($guardian, $user));

        // Log success
        \Log::info('Guardian registration email sent successfully');
    }

    /**
     * Get the available relation guarded options for the dropdown.
     */
    private function getRelationGuardedOptions(): array
    {
        $options = [];
        foreach (\App\Enum\RelationGuarded::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => ucfirst($case->value),
            ];
        }
        return $options;
    }

    /**
     * Get the available relation guardian options for the dropdown.
     */
    private function getRelationGuardianOptions(): array
    {
        $options = [];
        foreach (\App\Enum\RelationGuardian::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => ucfirst($case->value),
            ];
        }
        return $options;
    }

    /**
     * Get the raw values of the RelationGuarded enum for validation.
     */
    private function getRelationGuardedValues(): array
    {
        return array_map(fn($case) => $case->value, \App\Enum\RelationGuarded::cases());
    }

    /**
     * Get the raw values of the RelationGuardian enum for validation.
     */
    private function getRelationGuardianValues(): array
    {
        return array_map(fn($case) => $case->value, \App\Enum\RelationGuardian::cases());
    }

    /**
     * Add a guarded user (guest) to the guardian.
     */
    public function addGuardedUser(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Check if the user is a guest (either by account_type or specific user ID)
        if ($user->account_type === 'guest' || $user->id === 8) {
            return redirect()->back()->with('error', 'Guest users cannot add guarded users.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:255',
            'birthday' => 'required|date',
            'relation_guarded' => 'required|string|in:' . implode(',', $this->getRelationGuardedValues()),
            'relation_guardian' => 'required|string|in:' . implode(',', $this->getRelationGuardianValues()),
        ], [
            'email.required_without' => 'Either email or phone is required.',
            'phone.required_without' => 'Either email or phone is required.',
        ]);

        // Add custom validation to require either email or phone
        $validator->after(function ($validator) use ($request) {
            if (empty($request->email) && empty($request->phone)) {
                $validator->errors()->add('email', 'Either email or phone is required.');
                $validator->errors()->add('phone', 'Either email or phone is required.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get the authenticated user (guardian)
        $guardian = $request->user();

        // Create a guarded user (guest)
        $guarded = \App\Models\User::create([
            'given_name' => $request->name,
            'family_name' => '',
            'account_type' => 'guest',
            'birthday' => $request->birthday,
        ]);

        // Set the guarded user's email if provided
        if (!empty($request->email)) {
            $guarded->email = $request->email;
        }

        // Set the guarded user's phone if provided
        if (!empty($request->phone)) {
            $guarded->phone = $request->phone;
        }

        // Add the guardian-child relationship
        $relationGuarded = \App\Enum\RelationGuarded::from($request->relation_guarded);
        $relationGuardian = \App\Enum\RelationGuardian::from($request->relation_guardian);

        $guardian->addChild($guarded, $relationGuarded, $relationGuardian);

        return redirect()->route('guardian.edit')->with('status', 'guarded-user-added');
    }

    /**
     * Show the guardian settings page.
     */
    public function guardian(Request $request): Response
    {
        $user = $request->user();

        // Get the user's guardians with their relationship information
        $guardians = $user->guardians()->get()->map(function ($guardian) {
            return [
                'id' => $guardian->id,
                'name' => $guardian->name,
                'relation' => $guardian->pivot->relation_guardian,
                'isVerified' => !is_null($guardian->pivot->verified_at),
                'verifiedBy' => $guardian->pivot->verified_by,
                'verifiedAt' => $guardian->pivot->verified_at,
            ];
        });

        // Get the users the current user is guarding
        $guardedUsers = $user->children()->get()->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'relation' => $child->pivot->relation_guarded,
                'isVerified' => !is_null($child->pivot->verified_at),
                'verifiedBy' => $child->pivot->verified_by,
                'verifiedAt' => $child->pivot->verified_at,
            ];
        });

        // Get the available relation options for the dropdowns
        $relationGuardedOptions = $this->getRelationGuardedOptions();
        $relationGuardianOptions = $this->getRelationGuardianOptions();

        return Inertia::render('settings/Guardian', [
            'guardians' => $guardians,
            'guardedUsers' => $guardedUsers,
            'relationGuardedOptions' => $relationGuardedOptions,
            'relationGuardianOptions' => $relationGuardianOptions,
            'user' => [
                'id' => $user->id,
                'account_type' => $user->account_type,
                'roles' => $user->roles->pluck('name'),
            ],
        ]);
    }
}
