<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

abstract class AdminController extends Controller
{
    /**
     * Authorize a given action for the current user.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return \Illuminate\Auth\Access\Response|\Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize($ability, $arguments = [])
    {
        // Check if the user is authenticated first
        if (!Auth::check()) {
            // Redirect to login page if not authenticated
            return redirect()->route('login');
        }

        // If authenticated, proceed with normal authorization
        return parent::authorize($ability, $arguments);
    }
}
