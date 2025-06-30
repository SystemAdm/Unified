<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class OlderThanEightTeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated user
        $user = $request->user();

        // Check if the user's birthday is valid and is above 18
        if (!$user || !$user->birthday || Carbon::parse($user->birthday)->age < 18) {
            // If user is under 18, you can return a response
            return response()->json(['message' => 'User must be 18 or older.'], 403);
        }

        return $next($request);
    }
}
