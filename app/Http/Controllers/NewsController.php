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
            ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
            ->with('author:id,name')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return Inertia::render('News/Index', [
            'news' => $news
        ]);
    }

    public function getLatestNews()
    {
        $news = News::published()
            ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
            ->with('author:id,name')
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return response()->json($news);
    }

    public function show($id)
    {
        $news = News::published()
            ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
            ->with('author:id,name')
            ->findOrFail($id);
        return Inertia::render('News/Show', [
            'news' => $news
        ]);
    }
}
