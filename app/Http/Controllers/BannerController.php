<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::where('activating', true)
            ->where('from_datetime', '<=', now())
            ->where('to_datetime', '>=', now())
            ->orderBy('from_datetime', 'desc')
            ->get();

        return response()->json($banners);
    }

    public function getActiveBanners()
    {
        $banners = Banner::where('activating', true)
            ->where('from_datetime', '<=', now())
            ->where('to_datetime', '>=', now())
            ->orderBy('from_datetime', 'desc')
            ->limit(5)
            ->get();

        return response()->json($banners);
    }
}
