<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DiscordBotController extends Controller
{
    /**
     * Create a new event
     */
    public function createEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_date' => 'required|string',
            'custom_title' => 'required|string|max:255',
            'restricted' => 'required|string|in:members,crew,public'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // Parse the date from DD/MM/YYYY format
            $eventDate = Carbon::createFromFormat('d/m/Y', $request->event_date);

            // Check if event already exists for this date
            $existingEvent = Event::whereDate('start_date', $eventDate->format('Y-m-d'))->first();
            if ($existingEvent) {
                return response()->json([
                    'error' => 'Event already exists for this date'
                ], 409);
            }

            // Create the event
            $event = Event::create([
                'title' => $request->custom_title,
                'start_date' => $eventDate,
                'end_date' => $eventDate->copy()->addHours(2), // Default 2 hour duration
                'restriction' => $request->restricted,
                'status' => 'published',
                'has_signup' => true,
                'signup_start_date' => now(),
                'signup_end_date' => $eventDate->copy()->subHour(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_date->format('d/m/Y'),
                    'restriction' => $event->restriction
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create event',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an event
     */
    public function cancelEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_date' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // Parse the date from DD/MM/YYYY format
            $eventDate = Carbon::createFromFormat('d/m/Y', $request->event_date);

            // Find the event
            $event = Event::whereDate('start_date', $eventDate->format('Y-m-d'))->first();
            if (!$event) {
                return response()->json([
                    'error' => 'Event not found for the specified date'
                ], 404);
            }

            // Cancel the event
            $event->update([
                'is_cancelled' => true,
                'cancelled_at' => now(),
                'cancellation_reason' => 'Cancelled via Discord bot'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event cancelled successfully',
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_date->format('d/m/Y'),
                    'cancelled' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to cancel event',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an event
     */
    public function deleteEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_date' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // Parse the date from DD/MM/YYYY format
            $eventDate = Carbon::createFromFormat('d/m/Y', $request->event_date);

            // Find the event
            $event = Event::whereDate('start_date', $eventDate->format('Y-m-d'))->first();
            if (!$event) {
                return response()->json([
                    'error' => 'Event not found for the specified date'
                ], 404);
            }

            $eventTitle = $event->title;
            $eventDateFormatted = $event->start_date->format('d/m/Y');

            // Delete the event
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully',
                'deleted_event' => [
                    'title' => $eventTitle,
                    'date' => $eventDateFormatted
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete event',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update event restriction
     */
    public function updateEventRestriction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_date' => 'required|string',
            'restriction' => 'required|string|in:members,crew,public'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // Parse the date from DD/MM/YYYY format
            $eventDate = Carbon::createFromFormat('d/m/Y', $request->event_date);

            // Find the event
            $event = Event::whereDate('start_date', $eventDate->format('Y-m-d'))->first();
            if (!$event) {
                return response()->json([
                    'error' => 'Event not found for the specified date'
                ], 404);
            }

            // Update the restriction
            $event->update([
                'restriction' => $request->restriction
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event restriction updated successfully',
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_date->format('d/m/Y'),
                    'restriction' => $event->restriction
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update event restriction',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update event details
     */
    public function updateEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_date' => 'required|string',
            'new_title' => 'sometimes|string|max:255',
            'new_date' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            // Parse the original date from DD/MM/YYYY format
            $eventDate = Carbon::createFromFormat('d/m/Y', $request->event_date);

            // Find the event
            $event = Event::whereDate('start_date', $eventDate->format('Y-m-d'))->first();
            if (!$event) {
                return response()->json([
                    'error' => 'Event not found for the specified date'
                ], 404);
            }

            $updateData = [];

            // Update title if provided
            if ($request->has('new_title')) {
                $updateData['title'] = $request->new_title;
            }

            // Update date if provided
            if ($request->has('new_date')) {
                $newDate = Carbon::createFromFormat('d/m/Y', $request->new_date);

                // Check if another event exists on the new date
                $existingEvent = Event::whereDate('start_date', $newDate->format('Y-m-d'))
                    ->where('id', '!=', $event->id)
                    ->first();

                if ($existingEvent) {
                    return response()->json([
                        'error' => 'Another event already exists on the new date'
                    ], 409);
                }

                $updateData['start_date'] = $newDate;
                $updateData['end_date'] = $newDate->copy()->addHours(2);
                $updateData['signup_end_date'] = $newDate->copy()->subHour();
            }

            if (empty($updateData)) {
                return response()->json([
                    'error' => 'No update data provided'
                ], 400);
            }

            // Update the event
            $event->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->start_date->format('d/m/Y'),
                    'restriction' => $event->restriction
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update event',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
