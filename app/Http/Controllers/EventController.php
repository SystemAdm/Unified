<?php

namespace App\Http\Controllers;

use App\Enum\Permission;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index()
    {
        $this->authorize(Permission::INDEX_EVENT->value);

        $events = Event::where('status', 'published')
            ->with(['location', 'organizers'])
            ->orderBy('start_date', 'asc')
            ->paginate(10);


        // Transform the events to include the location name and user
        $events->through(function ($event) {
            $event->location = $event->location ? $event->location->name : null;
            // Get the first organizer as the user
            $organizer = $event->organizers->first();
            $event->user = $organizer ? ['id' => $organizer->id, 'name' => $organizer->name] : null;
            return $event;
        });

        return Inertia::render('events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $this->authorize(Permission::SHOW_EVENT->value);
        // Only show published events
        if ($event->status !== 'published') {
            abort(404);
        }

        // Load the organizers relationship
        $event->load(['location', 'organizers']);

        // Transform the event to include the location name and user
        $event->location = $event->location ? $event->location->name : null;
        // Get the first organizer as the user
        $organizer = $event->organizers->first();
        $event->user = $organizer ? ['id' => $organizer->id, 'name' => $organizer->name] : null;

        return Inertia::render('events/Show', [
            'event' => $event,
        ]);
    }

    /**
     * Sign up for an event.
     */
    public function signup(Event $event)
    {
        // Check if the event has signup enabled
        if (!$event->has_signup) {
            abort(403, 'Signup is not enabled for this event.');
        }

        // Check if signup period is open
        $now = now();
        if ($event->signup_start_date && $now < $event->signup_start_date) {
            abort(403, 'Signup period has not started yet.');
        }
        if ($event->signup_end_date && $now > $event->signup_end_date) {
            abort(403, 'Signup period has ended.');
        }

        // Add the user to the signupped list if not already there
        if (!$event->signupped->contains(auth()->id())) {
            $event->signupped()->attach(auth()->id());
        }

        return redirect()->route('events.show', ['event' => $event->id]);
    }

    /**
     * Remove signup from an event.
     */
    public function removeSignup(Event $event)
    {
        // Remove the user from the signupped list
        $event->signupped()->detach(auth()->id());

        return redirect()->route('events.show', ['event' => $event->id]);
    }

    /**
     * Join an event.
     */
    public function join(Event $event)
    {
        // Check if the event has started
        if ($event->start_date > now()) {
            abort(403, 'Event has not started yet.');
            return; // Ensure we exit the method after aborting
        }

        // Add the user to the registered list if not already there
        if (!$event->registered->contains(auth()->id())) {
            $event->registered()->attach(auth()->id());
        }

        return redirect()->route('events.show', ['event' => $event->id]);
    }
}
