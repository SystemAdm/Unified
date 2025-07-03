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
}
