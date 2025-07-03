<?php

namespace App\Http\Controllers\Admin;

use App\Enum\Permission;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Constructor to authorize admin access
     */
    public function __construct()
    {
        $this->authorize(Permission::ADMIN_EVENT->value);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize(Permission::INDEX_EVENT->value);

        $events = Event::with(['location', 'organizers'])
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        // Transform the events to include the location name and user
        $events->through(function ($event) {
            $event->location = $event->location ? $event->location->name : null;
            // Get the first organizer as the user
            $organizer = $event->organizers->first();
            $event->user = $organizer ? ['id' => $organizer->id, 'name' => $organizer->name] : null;
            return $event;
        });

        return Inertia::render('admin/events/Index', [
            'events' => $events,
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

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $this->authorize(Permission::ADMIN_EVENT->value);

        // Load the organizers relationship
        $event->load(['location', 'organizers']);

        // Transform the event to include the location name and user
        $event->location = $event->location ? $event->location->name : null;
        // Get the first organizer as the user
        $organizer = $event->organizers->first();
        $event->user = $organizer ? ['id' => $organizer->id, 'name' => $organizer->name] : null;

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
        $event->load(['organizers', 'organizations']);

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

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize(Permission::DELETE_EVENT->value);

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

        return redirect()->route('admin.events.show', ['event' => $event->id])
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

        return redirect()->route('admin.events.show', ['event' => $event->id])
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

        return redirect()->route('admin.events.show', ['event' => $event->id])
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

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'User has been removed from registered and attending lists.');
    }

    /**
     * Remove a user from the attending list.
     */
    public function removeFromAttending(Event $event, User $user)
    {
        $this->authorize(Permission::UPDATE_EVENT->value);

        $event->attending()->detach($user->id);

        return redirect()->route('admin.events.show', ['event' => $event->id])
            ->with('success', 'User has been removed from the attending list.');
    }
}
