<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Game;
use App\Models\User;
use App\Models\Banner;
use App\Models\Announcement;
use App\Models\News;
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
        $events = Event::published()->upcoming()->where('is_cancelled', false)->orderBy('start_date', 'asc')->with(['location'])->take(3)->get();
        // Remove appended attributes from the collection to reduce queries
        $events->each->setAppends([]);

        // Fetch active banners
        $banners = Banner::where('is_published', true)
            ->where('from_datetime', '<=', now())
            ->where('to_datetime', '>=', now())
            ->orderBy('from_datetime', 'desc')
            ->limit(5)
            ->get();

        // Add 'activating' property to each banner for the frontend
        $banners->each(function ($banner) {
            $banner->activating = $banner->is_published;
        });

        // Fetch active announcements
        $announcements = Announcement::where('activating', true)
            ->where('from_datetime', '<=', now())
            ->where('to_datetime', '>=', now())
            ->orderBy('from_datetime', 'desc')
            ->limit(10)
            ->get();

        // Fetch latest published news where published date has passed
        $news = News::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return Inertia::render('Welcome', [
            'games' => $games,
            'events' => $events,
            'banners' => $banners,
            'announcements' => $announcements,
            'news' => $news
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
