<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PhoneController extends AdminController
{
    /**
     * Constructor to authorize admin access
     */
    public function __construct()
    {
        $this->authorize(Permission::ADMIN_PHONE->value);
    }

    /**
     * Display a listing of all phone numbers.
     */
    public function index(Request $request): Response
    {
        $this->authorize(Permission::INDEX_PHONE->value);

        // Get all phone numbers with their associated users
        $phones = Phone::with(['users' => function ($query) {
            $query->withPivot(['verified_at', 'is_primary']);
        }])->paginate(10);

        return Inertia::render('admin/phones/Index', [
            'phones' => $phones,
        ]);
    }

    /**
     * Show the form for creating a new phone number.
     */
    public function create(): Response
    {
        $this->authorize(Permission::CREATE_PHONE->value);

        // Get all users for the dropdown with all attributes to ensure name is computed correctly
        $users = User::all();

        return Inertia::render('admin/phones/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created phone number.
     */
    public function store(Request $request)
    {
        $this->authorize(Permission::CREATE_PHONE->value);

        $request->validate([
            'phone_number' => ['required', 'string'],
            'user_id' => ['required', 'exists:users,id'],
            'is_primary' => ['boolean'],
        ]);

        // Find or create the phone record
        $phone = new Phone();
        $phone->phone_number = $request->phone_number;
        $phone->save();

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this phone is already associated with the user
        if (!$user->phones()->where('phones.id', $phone->id)->exists()) {
            // Associate the phone with the user
            $isPrimary = $request->is_primary ?? ($user->phones()->count() === 0);
            $user->phones()->attach($phone, [
                'is_primary' => $isPrimary,
                'verified_at' => $request->is_verified ? now() : null,
            ]);
        }

        return Redirect::route('admin.phones.index');
    }

    /**
     * Display the specified phone number.
     */
    public function show(Phone $phone): Response
    {
        $this->authorize(Permission::SHOW_PHONE->value);

        // Load the users relationship with pivot data
        $phone->load(['users' => function ($query) {
            $query->withPivot(['verified_at', 'is_primary']);
        }]);

        return Inertia::render('admin/phones/Show', [
            'phone' => $phone,
        ]);
    }

    /**
     * Show the form for editing the specified phone number.
     */
    public function edit(Phone $phone): Response
    {
        $this->authorize(Permission::UPDATE_PHONE->value);

        // Load the users relationship with pivot data
        $phone->load(['users' => function ($query) {
            $query->withPivot(['verified_at', 'is_primary']);
        }]);

        // Get all users for the dropdown with all attributes to ensure name is computed correctly
        $users = User::all();

        return Inertia::render('admin/phones/Edit', [
            'phone' => $phone,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified phone number.
     */
    public function update(Request $request, Phone $phone)
    {
        $this->authorize(Permission::UPDATE_PHONE->value);

        $request->validate([
            'phone_number' => ['required', 'string'],
        ]);

        // Update the phone record
        $phone->phone_number = $request->phone_number;
        $phone->save();

        return Redirect::route('admin.phones.index');
    }

    /**
     * Remove the specified phone number from storage.
     */
    public function destroy(Phone $phone)
    {
        $this->authorize(Permission::DELETE_PHONE->value);

        // Detach all users from this phone
        $phone->users()->detach();

        // Delete the phone
        $phone->delete();

        return Redirect::route('admin.phones.index');
    }

    /**
     * Attach a phone number to a user.
     */
    public function attachUser(Request $request, Phone $phone)
    {
        $this->authorize(Permission::UPDATE_PHONE->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'is_primary' => ['boolean'],
            'is_verified' => ['boolean'],
        ]);

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this phone is already associated with the user
        if (!$user->phones()->where('phones.id', $phone->id)->exists()) {
            // Associate the phone with the user
            $isPrimary = $request->is_primary ?? false;
            $user->phones()->attach($phone, [
                'is_primary' => $isPrimary,
                'verified_at' => $request->is_verified ? now() : null,
            ]);

            // If this is set as primary, update other phones for this user
            if ($isPrimary) {
                foreach ($user->phones()->where('phones.id', '!=', $phone->id)->get() as $otherPhone) {
                    $user->phones()->updateExistingPivot($otherPhone->id, [
                        'is_primary' => false,
                    ]);
                }
            }
        }

        return Redirect::route('admin.phones.edit', $phone);
    }

    /**
     * Detach a phone number from a user.
     */
    public function detachUser(Request $request, Phone $phone)
    {
        $this->authorize(Permission::UPDATE_PHONE->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Detach the user from this phone
        $phone->users()->detach($request->user_id);

        return Redirect::route('admin.phones.edit', $phone);
    }

    /**
     * Set a phone number as primary for a user.
     */
    public function setPrimary(Request $request, Phone $phone)
    {
        $this->authorize(Permission::UPDATE_PHONE->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this phone is associated with the user
        if ($user->phones()->where('phones.id', $phone->id)->exists()) {
            // Set this phone as primary for the user
            $user->phones()->updateExistingPivot($phone->id, [
                'is_primary' => true,
            ]);

            // Set all other phones as not primary for this user
            foreach ($user->phones()->where('phones.id', '!=', $phone->id)->get() as $otherPhone) {
                $user->phones()->updateExistingPivot($otherPhone->id, [
                    'is_primary' => false,
                ]);
            }
        }

        return Redirect::route('admin.phones.edit', $phone);
    }

    /**
     * Set a phone number as verified for a user.
     */
    public function setVerified(Request $request, Phone $phone)
    {
        $this->authorize(Permission::UPDATE_PHONE->value);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Get the user
        $user = User::findOrFail($request->user_id);

        // Check if this phone is associated with the user
        if ($user->phones()->where('phones.id', $phone->id)->exists()) {
            // Set this phone as verified for the user
            $user->phones()->updateExistingPivot($phone->id, [
                'verified_at' => now(),
            ]);
        }

        return Redirect::route('admin.phones.edit', $phone);
    }
}
