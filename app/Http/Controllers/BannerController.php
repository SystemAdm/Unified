<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_published', true)
            ->get()
            ->filter(function ($banner) {
                return $banner->isActive();
            })
            ->sortByDesc('from_datetime')
            ->values();

        // Add relative day labels to banners
        $banners->each(function ($banner) {
            $banner->relative_day = $banner->getRelativeDayLabel();
        });

        return response()->json($banners);
    }

    public function getActiveBanners()
    {
        $banners = Banner::where('is_published', true)
            ->get()
            ->filter(function ($banner) {
                return $banner->isActive();
            })
            ->sortByDesc('from_datetime')
            ->take(5)
            ->values();

        // Add relative day labels to banners
        $banners->each(function ($banner) {
            $banner->relative_day = $banner->getRelativeDayLabel();
        });

        return response()->json($banners);
    }
}
