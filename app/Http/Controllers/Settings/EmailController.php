<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Notifications\VerifyAdditionalEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends Controller
{
    /**
     * Show the user's email settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        // Eager load emails with pivot data
        $user->load(['emails' => function ($query) {
            $query->withPivot(['verified_at', 'is_primary']);
        }]);

        return Inertia::render('settings/Email', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'emails' => $user->emails->map(function ($email) {
                return [
                    'id' => $email->id,
                    'address' => $email->address,
                    'is_primary' => $email->pivot->is_primary == 1,
                    'is_verified' => $email->pivot->verified_at !== null,
                    'verified_at' => $email->pivot->verified_at,
                ];
            }),
        ]);
    }
    /**
     * Store a newly created email address.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'address' => ['required', 'string', 'email', 'max:255', Rule::unique('emails', 'address')->where(function ($query) {
                return $query->whereHas('users', function ($query) {
                    return $query->where('user_id', Auth::id());
                });
            })],
        ]);

        // Find or create the email record
        $email = Email::firstOrCreate(['address' => $request->address]);

        // Check if this email is already associated with the user
        if (!$request->user()->emails()->where('emails.id', $email->id)->exists()) {
            // Associate the email with the user
            $isPrimary = $request->user()->emails()->count() === 0; // Set as primary if it's the first email
            $request->user()->emails()->attach($email, ['is_primary' => $isPrimary]);
        }

        return Redirect::route('email.edit');
    }

    /**
     * Set an email as primary.
     */
    public function setPrimary(Request $request, Email $email): RedirectResponse
    {
        // Verify the user owns this email
        $pivot = $request->user()->emails()->where('emails.id', $email->id)->first()?->pivot;

        if (!$pivot) {
            abort(403, 'You do not own this email address.');
        }

        // Set this email as primary
        $request->user()->setPrimaryEmail($email);

        return Redirect::route('email.edit');
    }

    /**
     * Update the specified email address.
     */
    public function update(Request $request, Email $email): RedirectResponse
    {
        // Verify the user owns this email
        $pivot = $request->user()->emails()->where('emails.id', $email->id)->first()?->pivot;

        if (!$pivot) {
            abort(403, 'You do not own this email address.');
        }

        // Check if this is the primary email
        if ($pivot->is_primary) {
            abort(403, 'You cannot change your primary email address.');
        }

        $request->validate([
            'address' => ['required', 'string', 'email', 'max:255', Rule::unique('emails', 'address')->where(function ($query) {
                return $query->whereHas('users', function ($query) {
                    return $query->where('user_id', Auth::id());
                });
            })],
        ]);

        // Create a new email record
        $newEmail = Email::firstOrCreate(['address' => $request->address]);

        // Detach the old email
        $request->user()->emails()->detach($email->id);

        // Attach the new email with the same verification status (but reset to unverified)
        $request->user()->emails()->attach($newEmail, ['is_primary' => false]);

        return Redirect::route('email.edit');
    }

    /**
     * Send a verification email for the specified email address.
     */
    public function sendVerification(Request $request, Email $email): RedirectResponse
    {
        // Verify the user owns this email
        $pivot = $request->user()->emails()->where('emails.id', $email->id)->first()?->pivot;

        if (!$pivot) {
            abort(403, 'You do not own this email address.');
        }

        // Check if the email is already verified
        if ($pivot->verified_at) {
            return Redirect::route('email.edit')->with('status', 'email-already-verified');
        }

        // Send verification email
        $request->user()->notify(new VerifyAdditionalEmail($email));

        return Redirect::route('email.edit')->with('status', 'verification-link-sent');
    }

    /**
     * Remove the specified email from the user.
     */
    public function destroy(Request $request, Email $email): RedirectResponse
    {
        // Verify the user owns this email
        $pivot = $request->user()->emails()->where('emails.id', $email->id)->first()?->pivot;

        if (!$pivot) {
            abort(403, 'You do not own this email address.');
        }

        // Check if this is the primary email
        if ($pivot->is_primary) {
            abort(403, 'You cannot delete your primary email address.');
        }

        // Detach the email from the user
        $request->user()->emails()->detach($email->id);

        return Redirect::route('email.edit');
    }

    /**
     * Verify an email address.
     */
    public function verify(Request $request, $id, $email_id): RedirectResponse
    {
        // Get the user
        $user = Auth::user();

        if (!$user || $user->id != $id) {
            abort(403, 'Unauthorized action.');
        }

        // Get the email
        $email = Email::findOrFail($email_id);

        // Verify the user owns this email
        $pivot = $user->emails()->where('emails.id', $email->id)->first()?->pivot;

        if (!$pivot) {
            abort(403, 'You do not own this email address.');
        }

        // Check if the hash matches
        if (!hash_equals(sha1($email->address), $request->hash)) {
            abort(403, 'Invalid verification link.');
        }

        // Mark the email as verified
        if (!$pivot->verified_at) {
            $user->emails()->updateExistingPivot($email->id, [
                'verified_at' => now(),
            ]);

            event(new Verified($user));
        }

        return Redirect::route('email.edit')->with('status', 'email-verified');
    }
}
