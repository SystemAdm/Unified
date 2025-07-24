<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('News/Index', [
            'news' => $news
        ]);
    }

    public function getLatestNews()
    {
        $news = News::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return response()->json($news);
    }

    public function show($id)
    {
        $news = News::published()->with('author')->findOrFail($id);
        return Inertia::render('News/Show', [
            'news' => $news
        ]);
    }
}
