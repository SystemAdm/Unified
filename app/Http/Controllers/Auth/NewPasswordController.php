<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Show the password reset page.
     */
    public function create(Request $request): Response
    {
        \Illuminate\Support\Facades\Log::info('NewPasswordController::create called', [
            'email' => $request->email,
            'token' => $request->route('token')
        ]);

        $email = $request->email;
        $token = $request->route('token');
        $errors = [];

        // Check if the email exists
        $emailModel = \App\Models\Email::where('address', $email)->first();
        if (!$emailModel) {
            \Illuminate\Support\Facades\Log::error('No email found', ['email' => $email]);
            $errors['email'] = __('We can\'t find a user with that email address.');
        } else {
            // Check if there are multiple users with this email
            $users = $emailModel->users()->get();
            $usersCount = $users->count();
            \Illuminate\Support\Facades\Log::info('Found users for email', ['count' => $usersCount]);

            if ($usersCount > 1) {
                // Create a custom token repository to find the user by token and email
                $config = config('auth.passwords.users');
                $tokenRepo = new \App\Auth\CustomTokenRepository(
                    \Illuminate\Support\Facades\DB::connection(),
                    \Illuminate\Support\Facades\Hash::getFacadeRoot(),
                    $config['table'],
                    config('app.key'),
                    ($config['expire'] ?? 60) * 60,
                    $config['throttle'] ?? 0
                );

                // Debug the token
                $debug = $tokenRepo->debugToken($token, $email);
                \Illuminate\Support\Facades\Log::info('Token debug result', ['debug' => $debug]);

                if ($debug['success']) {
                    // Find the user with the valid token
                    $user = \App\Models\User::find($debug['valid_user_id']);
                    \Illuminate\Support\Facades\Log::info('Found user by token and email', ['user_id' => $user->id]);
                } else {
                    \Illuminate\Support\Facades\Log::error('No valid token found for any user');
                    $errors['email'] = __('Invalid or expired password reset link.');
                }
            } else if ($usersCount === 1) {
                // If there's only one user, check if the token is valid
                $user = $users->first();

                // Create a custom token repository
                $config = config('auth.passwords.users');
                $tokenRepo = new \App\Auth\CustomTokenRepository(
                    \Illuminate\Support\Facades\DB::connection(),
                    \Illuminate\Support\Facades\Hash::getFacadeRoot(),
                    $config['table'],
                    config('app.key'),
                    ($config['expire'] ?? 60) * 60,
                    $config['throttle'] ?? 0
                );

                // Debug the token
                $debug = $tokenRepo->debugToken($token, $email);
                \Illuminate\Support\Facades\Log::info('Token debug result for single user', ['debug' => $debug]);

                if (!$debug['success']) {
                    \Illuminate\Support\Facades\Log::error('Invalid token for user');
                    $errors['email'] = __('Invalid or expired password reset link.');
                }
            } else {
                \Illuminate\Support\Facades\Log::error('No users found for email');
                $errors['email'] = __('We can\'t find a user with that email address.');
            }
        }

        return Inertia::render('auth/ResetPassword', [
            'email' => $email,
            'token' => $token,
            'errors' => $errors
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('NewPasswordController::store called', [
            'email' => $request->input('email')
        ]);

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Check if there are multiple users with this email
        $emailModel = \App\Models\Email::where('address', $request->input('email'))->first();
        if ($emailModel && $emailModel->users()->count() > 1) {
            \Illuminate\Support\Facades\Log::info('Multiple users found for email, using custom reset logic', [
                'email' => $request->input('email'),
                'users_count' => $emailModel->users()->count()
            ]);

            // Create a custom password broker
            $config = config('auth.passwords.users');
            $tokenRepo = new \App\Auth\CustomTokenRepository(
                \Illuminate\Support\Facades\DB::connection(),
                \Illuminate\Support\Facades\Hash::getFacadeRoot(),
                $config['table'],
                config('app.key'),
                ($config['expire'] ?? 60) * 60,
                $config['throttle'] ?? 0
            );

            $broker = new \App\Auth\CustomPasswordBroker(
                $tokenRepo,
                \Illuminate\Support\Facades\Auth::createUserProvider($config['provider'] ?? null),
                \Illuminate\Support\Facades\Event::getFacadeRoot(),
                null,
                config('auth.timebox_duration', 200000)
            );

            // Debug the token first
            $debug = $tokenRepo->debugToken($request->input('token'), $request->input('email'));
            \Illuminate\Support\Facades\Log::info('Token debug result for reset', ['debug' => $debug]);

            if ($debug['success']) {
                // Find the user with the valid token
                $user = \App\Models\User::find($debug['valid_user_id']);
                \Illuminate\Support\Facades\Log::info('Found user by token and email for reset', ['user_id' => $user->id]);

                // Reset the password directly
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Delete the token
                $tokenRepo->delete($user);

                event(new PasswordReset($user));

                $status = Password::PASSWORD_RESET;
            } else {
                \Illuminate\Support\Facades\Log::error('No valid token found for any user during reset');
                $status = Password::INVALID_TOKEN;
            }
        } else {
            \Illuminate\Support\Facades\Log::info('Single or no user found for email, using standard reset logic', [
                'email' => $request->input('email')
            ]);

            // Create a custom token repository
            $config = config('auth.passwords.users');
            $tokenRepo = new \App\Auth\CustomTokenRepository(
                \Illuminate\Support\Facades\DB::connection(),
                \Illuminate\Support\Facades\Hash::getFacadeRoot(),
                $config['table'],
                config('app.key'),
                ($config['expire'] ?? 60) * 60,
                $config['throttle'] ?? 0
            );

            // Debug the token first
            $debug = $tokenRepo->debugToken($request->input('token'), $request->input('email'));
            \Illuminate\Support\Facades\Log::info('Token debug result for single user reset', ['debug' => $debug]);

            if ($debug['success']) {
                // Find the user with the valid token
                $user = \App\Models\User::find($debug['valid_user_id']);
                \Illuminate\Support\Facades\Log::info('Found user by token and email for single user reset', ['user_id' => $user->id]);

                // Reset the password directly
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Delete the token
                $tokenRepo->delete($user);

                event(new PasswordReset($user));

                $status = Password::PASSWORD_RESET;
            } else {
                \Illuminate\Support\Facades\Log::error('No valid token found for single user during reset');
                $status = Password::INVALID_TOKEN;
            }
        }

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PasswordReset) {
            return to_route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
