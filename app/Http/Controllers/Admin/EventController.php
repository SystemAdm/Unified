<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Events\SendEventToDiscord;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // No authorization check in constructor
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize(Permission::INDEX_EVENT->value);

        $query = Event::with(['location', 'organizations', 'users']);

        // Filter by date range
        if ($request->has('from_date') && $request->from_date !== '' && $request->from_date !== null) {
            $query->where('start_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date !== '' && $request->to_date !== null) {
            $query->where('start_date', '<=', $request->to_date);
        }

        // Filter by location
        if ($request->has('location_id') && $request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by organizer (organization or user)
        if ($request->has('organizer_type') && $request->has('organizer_id') && $request->organizer_id) {
            if ($request->organizer_type === 'user') {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('users.id', $request->organizer_id);
                });
            } elseif ($request->organizer_type === 'organization') {
                $query->whereHas('organizations', function ($q) use ($request) {
                    $q->where('organizations.id', $request->organizer_id);
                });
            }
        }

        // Sorting
        $sortField = $request->input('sort_field', 'start_date');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['title', 'start_date', 'status'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'start_date';
        }

        // Validate sort direction
        $allowedSortDirections = ['asc', 'desc'];
        if (!in_array($sortDirection, $allowedSortDirections)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        $events = $query->paginate(10)->withQueryString();

        // Transform the events to include the location name
        $events->through(function ($event) {
            return $event;
        });

        // Get locations for filter dropdown
        $locations = \App\Models\Location::where('is_active', true)->get();

        // Get users and organizations for organizer filter
        $users = User::all();
        $organizations = \App\Models\Organization::all();

        return Inertia::render('admin/events/Index', [
            'events' => $events,
            'filters' => $request->only(['from_date', 'to_date', 'location_id', 'status', 'organizer_type', 'organizer_id', 'sort_field', 'sort_direction']),
            'locations' => $locations,
            'users' => $users,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize(Permission::CREATE_EVENT->value);

        $users = User::all();
        $locations = \App\Models\Location::where('is_active', true)->get();
        $organizations = \App\Models\Organization::all();

        return Inertia::render('admin/events/Create', [
            'users' => $users,
            'locations' => $locations,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize(Permission::CREATE_EVENT->value);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location_id' => 'nullable|exists:locations,id',
            'status' => 'required|in:published,draft,cancelled',
            'has_signup' => 'boolean',
            'signup_start_date' => 'nullable|date',
            'signup_end_date' => 'nullable|date',
            'seats' => 'nullable|integer',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0',
            'class_restriction' => 'nullable|string',
            'restriction' => 'nullable|in:everyone,members,crew',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'organization_ids' => 'nullable|array',
            'organization_ids.*' => 'exists:organizations,id',
        ]);

        // Create the event
        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location_id' => $validated['location_id'],
            'status' => $validated['status'],
            'has_signup' => $validated['has_signup'] ?? false,
            'signup_start_date' => $validated['signup_start_date'],
            'signup_end_date' => $validated['signup_end_date'],
            'seats' => $validated['seats'],
            'min_age' => $validated['min_age'],
            'max_age' => $validated['max_age'],
            'class_restriction' => $validated['class_restriction'],
            'restriction' => $validated['restriction'] ?? 'everyone',
        ]);

        // Attach user organizers
        if (!empty($validated['user_ids'])) {
            $event->organizers()->attach($validated['user_ids']);
        }

        // Attach organization organizers
        if (!empty($validated['organization_ids'])) {
            $event->organizations()->attach($validated['organization_ids']);
        }

        // Dispatch event to Discord if the event is published
        if ($event->status === 'published') {
            SendEventToDiscord::dispatch($event);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        // Load the relationships
        $event->load(['location', 'users', 'organizations', 'signupped', 'registered', 'attending', 'inside']);

        return Inertia::render('admin/events/Show', [
            'event' => $event,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $users = User::all();
        $locations = \App\Models\Location::where('is_active', true)->get();
        $organizations = \App\Models\Organization::all();

        // Load the event's relationships
        $event->load(['users', 'organizations']);

        return Inertia::render('admin/events/Edit', [
            'event' => $event,
            'users' => $users,
            'locations' => $locations,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location_id' => 'nullable|exists:locations,id',
            'status' => 'required|in:published,draft,cancelled',
            'has_signup' => 'boolean',
            'signup_start_date' => 'nullable|date',
            'signup_end_date' => 'nullable|date',
            'seats' => 'nullable|integer',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0',
            'class_restriction' => 'nullable|string',
            'restriction' => 'nullable|in:everyone,members,crew',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'organization_ids' => 'nullable|array',
            'organization_ids.*' => 'exists:organizations,id',
        ]);

        // Check if the event status is changing to published
        $wasPublished = $event->status === 'published';
        $isNowPublished = $validated['status'] === 'published';

        // Update the event
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'location_id' => $validated['location_id'],
            'status' => $validated['status'],
            'has_signup' => $validated['has_signup'] ?? false,
            'signup_start_date' => $validated['signup_start_date'],
            'signup_end_date' => $validated['signup_end_date'],
            'seats' => $validated['seats'],
            'min_age' => $validated['min_age'],
            'max_age' => $validated['max_age'],
            'class_restriction' => $validated['class_restriction'],
            'restriction' => $validated['restriction'] ?? 'everyone',
        ]);

        // Update user organizers
        if (isset($validated['user_ids'])) {
            $event->organizers()->sync($validated['user_ids']);
        }

        // Update organization organizers
        if (isset($validated['organization_ids'])) {
            $event->organizations()->sync($validated['organization_ids']);
        }

        // Dispatch event to Discord if the event is now published and wasn't before,
        // or if it was already published but has been updated
        if ($isNowPublished) {
            if (!$wasPublished) {
                // Event is being published for the first time
                SendEventToDiscord::dispatch($event, 'create');
            } else {
                // Event was already published and is being updated
                // Check if only restriction changed
                $originalEvent = $event->getOriginal();
                $restrictionChanged = $originalEvent['restriction'] !== $validated['restriction'];
                $titleChanged = $originalEvent['title'] !== $validated['title'];
                $dateChanged = $originalEvent['start_date'] !== $validated['start_date'];

                if ($restrictionChanged && !$titleChanged && !$dateChanged) {
                    // Only restriction changed
                    SendEventToDiscord::dispatch($event, 'update-restriction');
                } else {
                    // General update (title, date, or multiple fields changed)
                    $additionalData = [];
                    if ($dateChanged) {
                        $additionalData['new_date'] = \Carbon\Carbon::parse($validated['start_date'])->format('d/m/Y');
                    }
                    SendEventToDiscord::dispatch($event, 'update', $additionalData);
                }
            }
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize(Permission::DELETE_EVENT->value);

        // Send delete notification to Discord if the event was published
        if ($event->status === 'published') {
            SendEventToDiscord::dispatch($event, 'delete');
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Force start signup for an event.
     */
    public function forceStartSignup(Event $event)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->has_signup = true;
        $event->signup_start_date = now();
        $event->save();

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'Event signup has been started.');
    }

    /**
     * Force end signup for an event.
     */
    public function forceEndSignup(Event $event)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->signup_end_date = now();
        $event->save();

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'Event signup has been ended.');
    }

    /**
     * Force start an event.
     */
    public function forceStartEvent(Event $event)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->start_date = now();
        $event->save();

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'Event has been started.');
    }

    /**
     * Force end an event.
     */
    public function forceEndEvent(Event $event)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->end_date = now();
        $event->save();

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'Event has been ended.');
    }

    /**
     * Cancel an event with a reason.
     */
    public function cancel(Request $request, Event $event)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $event->is_cancelled = true;
        $event->cancelled_at = now();
        $event->cancellation_reason = $validated['cancellation_reason'];
        $event->save();

        // Send cancel notification to Discord if the event was published
        if ($event->status === 'published') {
            SendEventToDiscord::dispatch($event, 'cancel');
        }

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'Event has been cancelled.');
    }

    /**
     * Copy a user from signup to registered.
     */
    public function copyToRegistered(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        // Get the current registered users
        $registeredUsers = $event->registered->pluck('id')->toArray();

        // Add the new user if not already registered
        if (!in_array($user->id, $registeredUsers)) {
            $registeredUsers[] = $user->id;
        }

        // Sync the registered users
        $event->registered()->sync($registeredUsers);

        // Refresh the event model to get the updated relationships
        $event->refresh();

        // Broadcast the updated registered users list
        event(new \App\Events\EventUserListUpdated($event, 'registered'));

        return redirect()->route('admin.events.users.all', ['event' => $event->id])
            ->with('success', 'User has been registered for the event.');
    }

    /**
     * Remove a user from all event lists (signup, registered, attending).
     */
    public function removeFromAll(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->signupped()->detach($user->id);
        $event->registered()->detach($user->id);
        $event->attending()->detach($user->id);

        return redirect()->route('admin.events.users.all', ['event' => $event->id])
            ->with('success', 'User has been removed from all event lists.');
    }

    /**
     * Copy a user from registered to attending.
     */
    public function copyToAttending(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        // Get the current attending users
        $attendingUsers = $event->attending->pluck('id')->toArray();

        // Add the new user if not already attending
        if (!in_array($user->id, $attendingUsers)) {
            $attendingUsers[] = $user->id;
        }

        // Sync the attending users
        $event->attending()->sync($attendingUsers);

        // Refresh the event model to get the updated relationships
        $event->refresh();

        // Broadcast the updated attending users list
        event(new \App\Events\EventUserListUpdated($event, 'attending'));

        // Check if the request wants JSON response
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User has been marked as attending the event.',
                'event' => $event->load(['registered', 'attending', 'inside'])
            ]);
        }

        return redirect()->route('admin.events.users.all', ['event' => $event->id])
            ->with('success', 'User has been marked as attending the event.');
    }

    /**
     * Remove a user from registered and attending lists.
     */
    public function removeFromRegisteredAttending(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->registered()->detach($user->id);
        $event->attending()->detach($user->id);

        return redirect()->route('admin.events.users.all', ['event' => $event->id])
            ->with('success', 'User has been removed from registered and attending lists.');
    }

    /**
     * Remove a user from the attending list.
     */
    public function removeFromAttending(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->attending()->detach($user->id);

        return redirect()->route('admin.events.users.all', ['event' => $event->id])
            ->with('success', 'User has been removed from the attending list.');
    }

    /**
     * Copy a user from attending to inside.
     */
    public function copyToInside(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        // Get the current inside users
        $insideUsers = $event->inside->pluck('id')->toArray();

        // Add the new user if not already inside
        if (!in_array($user->id, $insideUsers)) {
            $insideUsers[] = $user->id;
        }

        // Sync the inside users
        $event->inside()->sync($insideUsers);

        // Refresh the event model to get the updated relationships
        $event->refresh();

        // Broadcast the updated inside users list
        event(new \App\Events\EventUserListUpdated($event, 'inside'));

        // Check if the request wants JSON response
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User has been marked as inside the event.',
                'event' => $event->load(['registered', 'attending', 'inside'])
            ]);
        }

        return redirect()->route('admin.events.users.all', ['event' => $event->id])
            ->with('success', 'User has been marked as inside the event.');
    }

    /**
     * Remove a user from the inside list.
     */
    public function removeFromInside(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->inside()->detach($user->id);

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'User has been removed from the inside list.');
    }

    /**
     * Show registered users for an event.
     */
    public function showRegisteredUsers(Event $event)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        $event->load(['registered']);

        return Inertia::render('admin/events/UserList', [
            'event' => $event,
            'users' => $event->registered,
            'listType' => 'registered',
            'title' => 'Registered Users'
        ]);
    }

    /**
     * Show attending users for an event.
     */
    public function showAttendingUsers(Event $event)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        $event->load(['attending']);

        return Inertia::render('admin/events/UserList', [
            'event' => $event,
            'users' => $event->attending,
            'listType' => 'attending',
            'title' => 'Attending Users'
        ]);
    }

    /**
     * Show inside users for an event.
     */
    public function showInsideUsers(Event $event)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        $event->load(['inside']);

        return Inertia::render('admin/events/UserList', [
            'event' => $event,
            'users' => $event->inside,
            'listType' => 'inside',
            'title' => 'Inside Users'
        ]);
    }

    /**
     * Show all user types for an event (signupped, registered, attending, inside).
     */
    public function showAllUsers(Event $event)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        // Only select the columns we need for display
        $columns = ['users.id', 'users.given_name', 'users.additional_name', 'users.family_name'];

        // Load all relationships in a single query with the necessary columns
        $event->loadMissing([
            'registered' => function ($query) use ($columns) {
                $query->select($columns)->withPivot('created_at');
            },
            'attending' => function ($query) use ($columns) {
                $query->select($columns)->withPivot('created_at');
            },
            'inside' => function ($query) use ($columns) {
                $query->select($columns)->withPivot('created_at');
            }
        ]);

        return Inertia::render('admin/events/AllUsersList', [
            'event' => $event,
            'signuppedUsers' => $event->signupped,
            'registeredUsers' => $event->registered,
            'attendingUsers' => $event->attending,
            'insideUsers' => $event->inside,
            'title' => 'Users'
        ]);
    }

    /**
     * Show the encrypted text validation page.
     */
    public function showValidateText(Event $event, Request $request)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        return Inertia::render('admin/events/ValidateText', [
            'event' => $event
        ]);
    }

    /**
     * Validate encrypted text.
     */
    public function validateText(Event $event, Request $request)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        $validated = $request->validate([
            'encrypted_text' => 'required|string',
        ]);

        try {
            // First try the new AES-256-GCM encryption method
            try {
                // Decode the base64 string
                $binaryData = base64_decode($validated['encrypted_text']);
                if ($binaryData === false) {
                    throw new \Exception('Invalid base64 encoded data');
                }

                // Extract IV (first 12 bytes)
                $iv = substr($binaryData, 0, 12);

                // In AES-256-GCM, the authentication tag is 16 bytes and is appended to the ciphertext
                // The Web Crypto API in JavaScript automatically appends the tag to the ciphertext

                // Web Crypto API combines the ciphertext and tag in a specific way
                // The tag is appended to the ciphertext, not separated
                // So we need to extract the ciphertext+tag as a single unit
                $ciphertext = substr($binaryData, 12);

                // For openssl_decrypt with AES-GCM, we need to provide the tag separately
                // The tag is the last 16 bytes of the ciphertext
                $totalLength = strlen($ciphertext);
                $tagLength = 16; // GCM tag is 16 bytes
                $tag = substr($ciphertext, $totalLength - $tagLength, $tagLength);

                // The actual ciphertext is everything except the tag
                $ciphertext = substr($ciphertext, 0, $totalLength - $tagLength);

                // Get the APP_KEY and prepare it for decryption
                $appKey = config('app.key');
                // Remove 'base64:' prefix if present
                $appKey = str_replace('base64:', '', $appKey);
                // Decode the base64 key
                $keyBinary = base64_decode($appKey);
                // Use first 32 bytes for AES-256
                $key = substr($keyBinary, 0, 32);

                // Decrypt the data using AES-256-GCM with the authentication tag
                $decryptedData = openssl_decrypt(
                    $ciphertext,
                    'aes-256-gcm',
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv,
                    $tag
                );

                if ($decryptedData === false) {
                    throw new \Exception('Decryption failed');
                }

                // Parse the JSON data
                $userData = json_decode($decryptedData, true);
                if ($userData === null) {
                    throw new \Exception('Invalid JSON data');
                }

                // Extract the user ID from the decrypted data
                if (!isset($userData['id'])) {
                    throw new \Exception('User ID not found in decrypted data');
                }

                $userId = $userData['id'];
            } catch (\Exception $e) {
                // If AES-256-GCM decryption fails, try the old method (simple base64 encoding)
                $userId = base64_decode($validated['encrypted_text']);

                // Check if the decoded value is a valid user ID
                if (!is_numeric($userId)) {
                    throw new \Exception('Failed to decrypt user ID: ' . $e->getMessage());
                }
            }

            // Find the user by ID
            $user = User::find($userId);

            if ($user) {
                // Perform validation checks
                $validationResults = [];
                $canRegister = true;

                // 1. Check age requirement
                if ($event->min_age !== null || $event->max_age !== null) {
                    $userAge = $user->birthday ? $user->birthday->diffInYears(now()) : null;

                    if ($userAge === null) {
                        $validationResults[] = [
                            'check' => 'Age requirement',
                            'status' => 'warning',
                            'message' => 'User age unknown (no birthday)'
                        ];
                    } else {
                        if ($event->min_age !== null && $userAge < $event->min_age) {
                            $validationResults[] = [
                                'check' => 'Age requirement',
                                'status' => 'failed',
                                'message' => "User age ($userAge) is below minimum required age ({$event->min_age})"
                            ];
                            $canRegister = false;
                        } elseif ($event->max_age !== null && $userAge > $event->max_age) {
                            $validationResults[] = [
                                'check' => 'Age requirement',
                                'status' => 'failed',
                                'message' => "User age ($userAge) is above maximum allowed age ({$event->max_age})"
                            ];
                            $canRegister = false;
                        } else {
                            $validationResults[] = [
                                'check' => 'Age requirement',
                                'status' => 'passed',
                                'message' => "User meets age requirements"
                            ];
                        }
                    }
                } else {
                    $validationResults[] = [
                        'check' => 'Age requirement',
                        'status' => 'passed',
                        'message' => "No age restrictions for this event"
                    ];
                }

                // 2. Check restriction
                if ($event->restriction && $event->restriction !== 'everyone') {
                    $meetsRestriction = false;

                    if ($event->restriction === 'members' && $user->hasRole('member')) {
                        $meetsRestriction = true;
                    } elseif ($event->restriction === 'crew' && $user->hasRole('crew')) {
                        $meetsRestriction = true;
                    }

                    if ($meetsRestriction) {
                        $validationResults[] = [
                            'check' => 'Restriction',
                            'status' => 'passed',
                            'message' => "User meets event restrictions ({$event->restriction})"
                        ];
                    } else {
                        $validationResults[] = [
                            'check' => 'Restriction',
                            'status' => 'failed',
                            'message' => "User does not meet event restrictions: {$event->restriction}"
                        ];
                        $canRegister = false;
                    }
                } else {
                    $validationResults[] = [
                        'check' => 'Restriction',
                        'status' => 'passed',
                        'message' => "No restrictions for this event"
                    ];
                }

                // 3. Check signup
                if ($event->has_signup) {
                    $validationResults[] = [
                        'check' => 'Has signup',
                        'status' => 'passed',
                        'message' => "Event has signup enabled"
                    ];

                    // 3.1 Check if already signed up
                    $alreadySignedUp = $event->signupped->contains($user->id);
                    if ($alreadySignedUp) {
                        $validationResults[] = [
                            'check' => 'Already signed up',
                            'status' => 'passed',
                            'message' => "User has already signed up for this event"
                        ];
                    } else {
                        $validationResults[] = [
                            'check' => 'Already signed up',
                            'status' => 'warning',
                            'message' => "User has not signed up for this event"
                        ];
                    }

                    // 3.2 Check signup timestamps
                    $now = now();
                    if ($event->signup_start_date && $now < $event->signup_start_date) {
                        $validationResults[] = [
                            'check' => 'Signup timestamps',
                            'status' => 'failed',
                            'message' => "Signup period has not started yet"
                        ];
                        $canRegister = false;
                    } elseif ($event->signup_end_date && $now > $event->signup_end_date) {
                        $validationResults[] = [
                            'check' => 'Signup timestamps',
                            'status' => 'failed',
                            'message' => "Signup period has ended"
                        ];
                        $canRegister = false;
                    } else {
                        $validationResults[] = [
                            'check' => 'Signup timestamps',
                            'status' => 'passed',
                            'message' => "Signup period is active"
                        ];
                    }

                    // 3.3 Check seats available
                    if ($event->hasAvailableSeats()) {
                        $validationResults[] = [
                            'check' => 'Seats available',
                            'status' => 'passed',
                            'message' => "Seats are available ({$event->available_seats} remaining)"
                        ];
                    } else {
                        $validationResults[] = [
                            'check' => 'Seats available',
                            'status' => 'failed',
                            'message' => "No seats available"
                        ];
                        $canRegister = false;
                    }
                } else {
                    $validationResults[] = [
                        'check' => 'Has signup',
                        'status' => 'warning',
                        'message' => "Event does not have signup enabled"
                    ];
                }

                // Get the current registered users
                $registeredUsers = $event->registered->pluck('id')->toArray();

                // Check if already registered
                $alreadyRegistered = in_array($user->id, $registeredUsers);

                if ($alreadyRegistered) {
                    return response()->json([
                        'success' => true,
                        'message' => "User validated successfully (already registered for the event): {$user->given_name} {$user->family_name}",
                        'user' => $user,
                        'validation_results' => $validationResults
                    ]);
                } elseif ($canRegister) {
                    $registeredUsers[] = $user->id;

                    // Sync the registered users
                    $event->registered()->sync($registeredUsers);

                    // Refresh the event model to get the updated relationships
                    $event->refresh();

                    return response()->json([
                        'success' => true,
                        'message' => "User validated successfully and registered for the event: {$user->given_name} {$user->family_name}",
                        'user' => $user,
                        'validation_results' => $validationResults
                    ]);
                } else {
                    // Find the first failed validation check
                    $failedCheck = null;
                    foreach ($validationResults as $result) {
                        if ($result['status'] === 'failed') {
                            $failedCheck = $result;
                            break;
                        }
                    }

                    $failedReason = $failedCheck ? " Reason: {$failedCheck['check']} - {$failedCheck['message']}" : "";

                    return response()->json([
                        'success' => false,
                        'message' => "User validation failed. Cannot register for the event: {$user->given_name} {$user->family_name}.{$failedReason}",
                        'user' => $user,
                        'validation_results' => $validationResults
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found for the provided encrypted text.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid encrypted text format: ' . $e->getMessage()
            ]);
        }
    }
}
