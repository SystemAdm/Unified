<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class YoungerThanEightTeen
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

        // If no user or no birthday, we can't proceed
        if (!$user || !$user->birthday) {
            return response()->json(['message' => 'User information is incomplete.'], 403);
        }

        // Check if the user is under 18
        if (Carbon::parse($user->birthday)->age < 18) {
            // Check if the user has at least one guardian
            if ($user->guardians()->count() === 0) {
                // If user is under 18 and has no guardian, redirect to guardian registration page
                if ($request->expectsJson()) {
                    // For API requests, return a JSON response
                    return response()->json([
                        'message' => 'Users under 18 must register a guardian.',
                        'needs_guardian' => true
                    ], 403);
                } else {
                    // For web requests, redirect to the guardian registration page
                    return redirect()->route('register.guardian');
                }
            }
        }

        return $next($request);
    }
}
