<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wishlists = Wishlist::select('id', 'name', 'image', 'description', 'link', 'cost_per_unit', 'count', 'deadline', 'created_at', 'updated_at')
            ->orderBy('deadline', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Calculate additional data for each wishlist item
        $wishlists->getCollection()->transform(function ($wishlist) {
            $wishlist->total_cost = $wishlist->cost_per_unit * $wishlist->count;
            $wishlist->total_paid = $wishlist->totalPaid();
            $wishlist->remaining_amount = $wishlist->remainingAmount();
            $wishlist->is_expired = $wishlist->isExpired();
            $wishlist->is_fully_funded = $wishlist->isFullyFunded();
            return $wishlist;
        });

        return Inertia::render('Wishlist/Index', [
            'wishlists' => $wishlists
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $wishlist = Wishlist::select('id', 'name', 'image', 'description', 'link', 'cost_per_unit', 'count', 'deadline', 'created_at', 'updated_at')
            ->with('payments.user:id,name')
            ->findOrFail($id);

        // Calculate additional data
        $wishlist->total_cost = $wishlist->cost_per_unit * $wishlist->count;
        $wishlist->total_paid = $wishlist->totalPaid();
        $wishlist->remaining_amount = $wishlist->remainingAmount();
        $wishlist->is_expired = $wishlist->isExpired();
        $wishlist->is_fully_funded = $wishlist->isFullyFunded();

        return Inertia::render('Wishlist/Show', [
            'wishlist' => $wishlist
        ]);
    }
}
