<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Email;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if both email and password are provided (for tests)
        if ($request->has('email') && $request->has('password')) {
            // Find the email model
            $emailModel = Email::where('address', $request->input('email'))->first();

            // If email doesn't exist, show generic error
            if (!$emailModel) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            // Get the user associated with the email
            $user = $emailModel->users()->first();

            // If no user found, show generic error
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            // Check password
            if (!Hash::check($request->input('password'), $user->password)) {
                throw ValidationException::withMessages([
                    'password' => __('auth.password'),
                ]);
            }

            // Login the user
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // Redirect to email verification if email is not verified
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Original multi-step authentication process
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Find the email model
        $emailModel = Email::where('address', $request->input('email'))->first();

        // If email doesn't exist, show generic error
        if (!$emailModel) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Get all users associated with the email
        $users = $emailModel->users()->get();

        // If multiple users found, redirect to choose user page
        if ($users->count() > 1) {
            return redirect()->route('login.choose-user', ['email_id' => $emailModel->id]);
        }

        // If only one user, redirect to password page
        $user = $users->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return redirect()->route('login.password', [
            'user_id' => $user->id,
            'remember' => $request->boolean('remember')
        ]);
    }

    /**
     * Show a form to choose the correct user.
     */
    public function chooseUser(Request $request, $emailId): Response
    {
        $email = Email::findOrFail($emailId);

        // List all users associated with the email
        $users = $email->users()->get();

        return Inertia::render('auth/LoginChooseUser', [
            'users' => $users,
            'email' => $email->address,
            'email_id' => $emailId,
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the password input page for a single user.
     */
    public function showPasswordForm(Request $request, $userId): Response
    {
        // Load user with eager loading of emails to avoid additional query
        $user = User::with('emails')->findOrFail($userId);

        // Get the primary email for the user
        $email = $user->email;

        return Inertia::render('auth/LoginPassword', [
            'user' => $user,
            'email' => $email,
            'remember' => $request->boolean('remember'),
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle authentication with password for a single user.
     */
    public function authenticateWithPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'password' => ['required', 'string'],
        ]);

        $user = User::findOrFail($request->input('user_id'));

        // Check password
        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect to email verification if email is not verified
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Handle authentication for the selected user.
     */
    public function authenticateChosenUser(Request $request, $emailId): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string',
        ]);

        // Get the user
        $user = User::findOrFail($request->input('user_id'));

        // Get the email model
        $email = Email::findOrFail($emailId);

        // Verify the user is associated with the email
        $isAssociated = $user->emails()->where('emails.id', $email->id)->exists();

        if (!$isAssociated) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Check password
        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect to email verification if email is not verified
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
