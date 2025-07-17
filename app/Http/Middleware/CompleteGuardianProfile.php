<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompleteGuardianProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log the request method and route
        \Log::info('CompleteGuardianProfile middleware called', [
            'method' => $request->method(),
            'url' => $request->url(),
            'route' => $request->route() ? $request->route()->getName() : 'unknown'
        ]);

        // Get the authenticated user
        $user = $request->user();

        // If no user, proceed with the request
        if (!$user) {
            \Log::info('No authenticated user, proceeding with request');
            return $next($request);
        }

        // Check if the user is a guardian with incomplete profile
        if ($user->account_type === 'guardian' &&
            ($user->password === null || $user->birthday === null || $user->phones()->count() === 0)) {

            \Log::info('User is a guardian with incomplete profile', [
                'account_type' => $user->account_type,
                'password' => $user->password === null ? 'null' : 'set',
                'birthday' => $user->birthday === null ? 'null' : 'set',
                'phones_count' => $user->phones()->count()
            ]);

            // Skip redirection if already on the complete profile route, submitting the form, or logging out
            if ($request->routeIs('guardian.complete-profile') || $request->routeIs('guardian.complete-profile.store') || $request->routeIs('logout')) {
                \Log::info('Already on complete profile route, submitting the form, or logging out, proceeding with request');
                return $next($request);
            }

            // Redirect to the complete profile page
            \Log::info('Redirecting to complete profile page');
            return redirect()->route('guardian.complete-profile');
        }

        \Log::info('User profile is complete or not a guardian, proceeding with request');
        return $next($request);
    }
}
