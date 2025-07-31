<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Console;
use App\Models\Email;
use App\Models\Event;
use App\Models\Game;
use App\Models\Location;
use App\Models\News;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Phone;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $modelData = [
            'users' => [
                'title' => 'Users',
                'modelType' => 'users',
                'totalCount' => User::count(),
                'newCount' => User::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.users.index'),
                'createRoute' => route('admin.users.create'),
            ],
            'events' => [
                'title' => 'Events',
                'modelType' => 'events',
                'totalCount' => Event::count(),
                'newCount' => Event::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.events.index'),
                'createRoute' => route('admin.events.create'),
            ],
            'organizations' => [
                'title' => 'Organizations',
                'modelType' => 'organizations',
                'totalCount' => Organization::count(),
                'newCount' => Organization::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.organizations.index'),
                'createRoute' => route('admin.organizations.create'),
            ],
            'news' => [
                'title' => 'News',
                'modelType' => 'news',
                'totalCount' => News::count(),
                'newCount' => News::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.news.index'),
                'createRoute' => route('admin.news.create'),
            ],
            'banners' => [
                'title' => 'Banners',
                'modelType' => 'banners',
                'totalCount' => Banner::count(),
                'newCount' => Banner::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.banners.index'),
                'createRoute' => route('admin.banners.create'),
            ],
            'announcements' => [
                'title' => 'Announcements',
                'modelType' => 'announcements',
                'totalCount' => Announcement::count(),
                'newCount' => Announcement::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.announcements.index'),
                'createRoute' => route('admin.announcements.create'),
            ],
            'games' => [
                'title' => 'Games',
                'modelType' => 'games',
                'totalCount' => Game::count(),
                'newCount' => Game::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.games.index'),
                'createRoute' => route('admin.games.create'),
            ],
            'consoles' => [
                'title' => 'Consoles',
                'modelType' => 'consoles',
                'totalCount' => Console::count(),
                'newCount' => Console::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.consoles.index'),
                'createRoute' => route('admin.consoles.create'),
            ],
            'locations' => [
                'title' => 'Locations',
                'modelType' => 'locations',
                'totalCount' => Location::count(),
                'newCount' => Location::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.locations.index'),
                'createRoute' => route('admin.locations.create'),
            ],
            'roles' => [
                'title' => 'Roles',
                'modelType' => 'roles',
                'totalCount' => Role::count(),
                'newCount' => Role::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.roles.index'),
                'createRoute' => route('admin.roles.create'),
            ],
            'permissions' => [
                'title' => 'Permissions',
                'modelType' => 'permissions',
                'totalCount' => Permission::count(),
                'newCount' => Permission::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.permissions.index'),
                'createRoute' => route('admin.permissions.index'), // No create route for permissions
            ],
            'phones' => [
                'title' => 'Phones',
                'modelType' => 'phones',
                'totalCount' => Phone::count(),
                'newCount' => Phone::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.phones.index'),
                'createRoute' => route('admin.phones.create'),
            ],
            'emails' => [
                'title' => 'Emails',
                'modelType' => 'emails',
                'totalCount' => Email::count(),
                'newCount' => Email::where('created_at', '>=', now()->subWeek())->count(),
                'indexRoute' => route('admin.emails.index'),
                'createRoute' => route('admin.emails.create'),
            ],
        ];

        return Inertia::render('admin/Index', [
            'modelData' => $modelData,
        ]);
    }
}
