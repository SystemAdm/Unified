<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Admin\AdminController;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends AdminController
{
    /**
     * Constructor to authorize admin access
     */
    public function __construct()
    {
        $this->authorize(Permission::ADMIN_USER->value);
    }

    /**
     * Display a listing of the users.
     */
    public function index(): Response
    {
        $this->authorize(Permission::INDEX_USER->value);

        return Inertia::render('admin/users/Index', [
            'users' => User::with(['roles', 'emails', 'phones'])->paginate(10)->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'emails' => $user->emails->map(function ($email) {
                        return [
                            'id' => $email->id,
                            'address' => $email->address,
                            'is_primary' => $email->pivot->is_primary,
                            'is_verified' => $email->pivot->verified_at !== null,
                        ];
                    }),
                    'phones' => $user->phones->map(function ($phone) {
                        return [
                            'id' => $phone->id,
                            'number' => $phone->number,
                            'is_primary' => $phone->pivot->is_primary,
                            'is_verified' => $phone->pivot->verified_at !== null,
                        ];
                    }),
                    'roles' => $user->roles->pluck('name'),
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        $this->authorize(Permission::CREATE_USER->value);

        return Inertia::render('admin/users/Create', [
            'roles' => Role::all()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize(Permission::CREATE_USER->value);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string'],
            'birthday' => ['nullable', 'date'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Create user with basic attributes
        $user = new User();
        $user->name = $validated['name']; // This will trigger the name mutator
        $user->password = bcrypt($validated['password']);

        // Use provided birthday or set a default if not provided
        $user->birthday = !empty($validated['birthday']) ? $validated['birthday'] : now();
        $user->save();

        // Set email after saving to avoid integrity constraint violation
        if (!empty($validated['email'])) {
            // First, find or create the email record
            $emailModel = \App\Models\Email::firstOrCreate(['address' => $validated['email']]);

            // Attach the email as primary
            $user->emails()->attach($emailModel, ['is_primary' => true]);

            // Save the user to ensure changes are persisted
            $user->save();
        }

        // Set phone if provided
        if (!empty($validated['phone'])) {
            // Create a new phone model and set the phone_number attribute
            $phoneModel = new \App\Models\Phone();
            $phoneModel->phone_number = $validated['phone'];

            // Check if a phone with the same country_code and number already exists
            $existingPhone = \App\Models\Phone::where('country_code', $phoneModel->country_code)
                                  ->where('number', $phoneModel->number)
                                  ->first();

            if ($existingPhone) {
                $phoneModel = $existingPhone;
            } else {
                $phoneModel->save();
            }

            // Attach the phone as primary
            $user->phones()->attach($phoneModel, ['is_primary' => true]);

            // Save the user to ensure changes are persisted
            $user->save();
        }

        $roles = Role::whereIn('id', $validated['roles'])->get();
        $user->syncRoles($roles);

        return Redirect::route('admin.users.index');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        $this->authorize(Permission::UPDATE_USER->value);

        // Eager load the relationships
        $user->load(['emails', 'phones', 'roles']);

        // Debug the user and roles
        \Log::debug('User: ' . json_encode($user));
        \Log::debug('User roles: ' . json_encode($user->roles));
        \Log::debug('User roles pluck id: ' . json_encode($user->roles->pluck('id')));

        // Get all roles
        $allRoles = Role::all();

        // Map user data
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'birthday' => $user->birthday ? $user->birthday->format('Y-m-d') : null,
            'emails' => $user->emails->map(function ($email) {
                return [
                    'id' => $email->id,
                    'address' => $email->address,
                    'is_primary' => $email->pivot->is_primary,
                    'is_verified' => $email->pivot->verified_at !== null,
                ];
            }),
            'phones' => $user->phones->map(function ($phone) {
                return [
                    'id' => $phone->id,
                    'number' => $phone->number,
                    'is_primary' => $phone->pivot->is_primary,
                    'is_verified' => $phone->pivot->verified_at !== null,
                ];
            }),
            'roles' => $user->roles->pluck('id')->toArray(), // Ensure it's an array
        ];

        // Map roles data
        $rolesData = $allRoles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        })->values()->toArray(); // Ensure it's an array

        return Inertia::render('admin/users/Edit', [
            'user' => $userData,
            'roles' => $rolesData,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize(Permission::UPDATE_USER->value);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string'],
            'birthday' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Set name using the accessor method
        $user->name = $validated['name'];

        // Update birthday if provided
        if (isset($validated['birthday'])) {
            $user->birthday = $validated['birthday'];
        }

        $user->save();

        // Refresh the user to ensure we have the latest data
        $user->refresh();
        \Log::debug('After update - User phone: ' . $user->phone);

        // Get the primary phone directly from the database for debugging
        $primaryPhone = \DB::table('phone_user')
            ->where('user_id', $user->id)
            ->where('is_primary', true)
            ->join('phones', 'phones.id', '=', 'phone_user.phone_id')
            ->select('phones.number')
            ->first();
        \Log::debug('Primary phone from DB: ' . ($primaryPhone ? $primaryPhone->number : 'null'));

        if (!empty($validated['password'])) {
            $user->update([
                'password' => bcrypt($validated['password']),
            ]);
        }

        $roles = Role::whereIn('id', $validated['roles'])->get();
        $user->syncRoles($roles);

        return Redirect::route('admin.users.index');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize(Permission::DELETE_USER->value);

        $user->delete();

        return Redirect::route('admin.users.index');
    }
}
