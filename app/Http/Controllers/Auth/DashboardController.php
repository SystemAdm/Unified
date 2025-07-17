<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Game;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the welcome page
     */
    public function welcome()
    {
        $games = Game::where('is_active', true)->orderBy('updated_at')->limit(4)->get();

        // Disable appended attributes for the welcome page to reduce queries
        $events = Event::published()->upcoming()->with(['location'])->take(3)->get();
        // Remove appended attributes from the collection to reduce queries
        $events->each->setAppends([]);

        return Inertia::render('Welcome', [
            'games' => $games,
            'events' => $events
        ]);
    }

    /**
     * Display the authenticated user dashboard
     */
    public function index()
    {
        $user = User::with(['roles', 'guardians', 'children'])->findOrFail(auth()?->user()?->id);
        $events = Event::where('status', 'published')
            ->with(['location', 'organizations', 'users', 'signupped'])
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        // Add guardian status information for the dashboard
        $guardianStatus = null;
        if ($user->isOlderThanEighteen()) {
            $guardianStatus = [
                'isAdult' => true,
                'hasGuardians' => false,
                'guardians' => []
            ];
        } else {
            $guardians = $user->guardians;
            $guardianStatus = [
                'isAdult' => false,
                'hasGuardians' => $guardians->count() > 0,
                'guardians' => $guardians->map(function ($guardian) {
                    return [
                        'id' => $guardian->id,
                        'name' => $guardian->name,
                        'relation' => $guardian->pivot->relation_guardian,
                        'isVerified' => $guardian->pivot->verified_at !== null,
                        'verifiedBy' => $guardian->pivot->verified_by,
                        'verifiedAt' => $guardian->pivot->verified_at
                    ];
                })
            ];
        }

        // Add guarded users information for guardians
        $guardedUsers = null;
        if ($user->children->count() > 0) {
            $guardedUsers = $user->children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'relation' => $child->pivot->relation_guarded,
                    'isVerified' => $child->pivot->verified_at !== null,
                    'verifiedBy' => $child->pivot->verified_by,
                    'verifiedAt' => $child->pivot->verified_at
                ];
            });
        }

        return Inertia::render('Dashboard', compact(['user', 'events', 'guardianStatus', 'guardedUsers']));
    }
}
