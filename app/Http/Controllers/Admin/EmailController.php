<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends AdminController
{
    public function __construct()
    {
        $this->authorize(Permission::ADMIN_EMAIL->value);
    }

    /**
     * Display a listing of all email addresses.
     */
    public function index(Request $request): Response
    {
        $this->authorize(Permission::INDEX_EMAIL->value);

        // Get all email addresses with their associated users
        $emails = Email::with(['users' => function ($query) {
            $query->withPivot(['verified_at', 'is_primary']);
        }])->paginate(10);

        return Inertia::render('admin/emails/Index', [
            'emails' => $emails,
        ]);
    }

    /**
     * Show the form for creating a new email address.
     */
    public function create(): Response
    {
        $this->authorize(Permission::ADMIN_USER->value);

        // Get all users for the dropdown
        $users = User::all(['id', 'name']);

        return Inertia::render('admin/emails/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created email address.
     */
    public function store(Request $request)
    {
        $this->authorize(Permission::ADMIN_USER->value);

        $request->validate([
            'address' => ['required', 'string', 'email', 'max:255'],
            'user_id' => ['required', 'exists:users,id'],
            'is_primary' => ['boolean'],
        ]);

        // Find or create the email record
        $email = Email::firstOrCreate(['address' => $request->address]);

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this email is already associated with the user
        if (!$user->emails()->where('emails.id', $email->id)->exists()) {
            // Associate the email with the user
            $isPrimary = $request->is_primary ?? ($user->emails()->count() === 0);
            $user->emails()->attach($email, [
                'is_primary' => $isPrimary,
                'verified_at' => $request->is_verified ? now() : null,
            ]);
        }

        return Redirect::route('admin.emails.index');
    }

    /**
     * Display the specified email address.
     */
    public function show(Email $email): Response
    {
        $this->authorize(Permission::ADMIN_USER->value);

        // Load the users relationship with pivot data
        $email->load(['users' => function ($query) {
            $query->withPivot(['verified_at', 'is_primary']);
        }]);

        return Inertia::render('admin/emails/Show', [
            'email' => $email,
        ]);
    }

    /**
     * Show the form for editing the specified email address.
     */
    public function edit(Email $email): Response
    {
        $this->authorize(Permission::ADMIN_USER->value);

        // Load the users relationship with pivot data
        $email->load(['users' => function ($query) {
            $query->withPivot(['verified_at', 'is_primary']);
        }]);

        // Get all users for the dropdown
        $users = User::all(['id', 'name']);

        return Inertia::render('admin/emails/Edit', [
            'email' => $email,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified email address.
     */
    public function update(Request $request, Email $email)
    {
        $this->authorize(Permission::ADMIN_USER->value);

        $request->validate([
            'address' => ['required', 'string', 'email', 'max:255'],
        ]);

        // Create a new email record if the address has changed
        if ($email->address !== $request->address) {
            $newEmail = Email::firstOrCreate(['address' => $request->address]);

            // Transfer all user associations from the old email to the new one
            foreach ($email->users as $user) {
                $pivotData = [
                    'is_primary' => $user->pivot->is_primary,
                    'verified_at' => $user->pivot->verified_at,
                ];

                // Attach the user to the new email if not already attached
                if (!$user->emails()->where('emails.id', $newEmail->id)->exists()) {
                    $user->emails()->attach($newEmail, $pivotData);
                }

                // Detach the user from the old email
                $user->emails()->detach($email->id);
            }

            // Delete the old email if it has no more users
            if ($email->users()->count() === 0) {
                $email->delete();
            }

            return Redirect::route('admin.emails.index');
        }

        return Redirect::route('admin.emails.index');
    }

    /**
     * Remove the specified email address from storage.
     */
    public function destroy(Email $email)
    {
        $this->authorize(Permission::ADMIN_USER->value);

        // Detach all users from this email
        $email->users()->detach();

        // Delete the email
        $email->delete();

        return Redirect::route('admin.emails.index');
    }

    /**
     * Attach an email address to a user.
     */
    public function attachUser(Request $request, Email $email)
    {
        $this->authorize(Permission::ADMIN_USER->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'is_primary' => ['boolean'],
            'is_verified' => ['boolean'],
        ]);

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this email is already associated with the user
        if (!$user->emails()->where('emails.id', $email->id)->exists()) {
            // Associate the email with the user
            $isPrimary = $request->is_primary ?? false;
            $user->emails()->attach($email, [
                'is_primary' => $isPrimary,
                'verified_at' => $request->is_verified ? now() : null,
            ]);

            // If this is set as primary, update other emails for this user
            if ($isPrimary) {
                foreach ($user->emails()->where('emails.id', '!=', $email->id)->get() as $otherEmail) {
                    $user->emails()->updateExistingPivot($otherEmail->id, [
                        'is_primary' => false,
                    ]);
                }
            }
        }

        return Redirect::route('admin.emails.edit', $email);
    }

    /**
     * Detach an email address from a user.
     */
    public function detachUser(Request $request, Email $email)
    {
        $this->authorize(Permission::ADMIN_USER->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Detach the user from this email
        $email->users()->detach($request->user_id);

        return Redirect::route('admin.emails.edit', $email);
    }

    /**
     * Set an email address as primary for a user.
     */
    public function setPrimary(Request $request, Email $email)
    {
        $this->authorize(Permission::ADMIN_USER->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this email is associated with the user
        if ($user->emails()->where('emails.id', $email->id)->exists()) {
            // Set this email as primary for the user
            $user->emails()->updateExistingPivot($email->id, [
                'is_primary' => true,
            ]);

            // Set all other emails as not primary for this user
            foreach ($user->emails()->where('emails.id', '!=', $email->id)->get() as $otherEmail) {
                $user->emails()->updateExistingPivot($otherEmail->id, [
                    'is_primary' => false,
                ]);
            }
        }

        return Redirect::route('admin.emails.edit', $email);
    }

    /**
     * Set an email address as verified for a user.
     */
    public function setVerified(Request $request, Email $email)
    {
        $this->authorize(Permission::ADMIN_USER->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this email is associated with the user
        if ($user->emails()->where('emails.id', $email->id)->exists()) {
            // Set this email as verified for the user
            $user->emails()->updateExistingPivot($email->id, [
                'verified_at' => now(),
            ]);
        }

        return Redirect::route('admin.emails.edit', $email);
    }
}
