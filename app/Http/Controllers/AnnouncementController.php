<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('is_published', true)
            ->where('from_datetime', '<=', now())
            ->where('to_datetime', '>=', now())
            ->orderBy('from_datetime', 'desc')
            ->get();

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements
        ]);
    }

    public function getActiveAnnouncements()
    {
        $announcements = Announcement::where('is_published', true)
            ->where('from_datetime', '<=', now())
            ->where('to_datetime', '>=', now())
            ->orderBy('from_datetime', 'desc')
            ->limit(10)
            ->get();

        return response()->json($announcements);
    }

    public function show($id)
    {
        $announcement = Announcement::where('is_published', true)
            ->where('from_datetime', '<=', now())
            ->where('to_datetime', '>=', now())
            ->findOrFail($id);

        return Inertia::render('Announcements/Show', [
            'announcement' => $announcement
        ]);
    }
}
