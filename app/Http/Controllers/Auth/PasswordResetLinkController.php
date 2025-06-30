<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('PasswordResetLinkController::store called', [
            'email' => $request->input('email')
        ]);

        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            // Find all users with the given email
            $emailModel = \App\Models\Email::where('address', $request->input('email'))->first();
            if (!$emailModel) {
                // If no email record is found, notify the user
                \Illuminate\Support\Facades\Log::info('No email record found', ['email' => $request->input('email')]);
                return back()->withErrors(['email' => __('No account associated with this email address.')]);
            }

            \Illuminate\Support\Facades\Log::info('Found email model', ['email_id' => $emailModel->id, 'address' => $emailModel->address]);

            // Get all users associated with the email
            $users = $emailModel->users()->get();
            if ($users->count() > 1) {
                // If multiple users exist, return a view to choose a user
                \Illuminate\Support\Facades\Log::info('Multiple users found for email', ['count' => $users->count()]);
                return redirect()->route('password.choose-user', ['email_id' => $emailModel->id]);
            }

            // If there is only one user, notify them with the reset link
            $user = $users->first();
            \Illuminate\Support\Facades\Log::info('Using user', ['user_id' => $user->id, 'name' => $user->given_name . ' ' . $user->family_name]);

            // Get the broker
            $broker = Password::broker();
            \Illuminate\Support\Facades\Log::info('Got password broker', ['class' => get_class($broker)]);

            // Get the token repository from the broker
            $reflection = new \ReflectionClass($broker);
            $tokensProperty = $reflection->getProperty('tokens');
            $tokensProperty->setAccessible(true);
            $tokens = $tokensProperty->getValue($broker);
            \Illuminate\Support\Facades\Log::info('Got token repository', ['class' => get_class($tokens)]);

            // Send reset link with both email and user_id to ensure proper token creation
            \Illuminate\Support\Facades\Log::info('Sending reset link', [
                'email' => $emailModel->address,
                'user_id' => $user->id
            ]);

            // Create a custom token repository directly
            $config = config('auth.passwords.users');
            $customTokenRepo = new \App\Auth\CustomTokenRepository(
                \Illuminate\Support\Facades\DB::connection(),
                \Illuminate\Support\Facades\Hash::getFacadeRoot(),
                $config['table'],
                config('app.key'),
                ($config['expire'] ?? 60) * 60,
                $config['throttle'] ?? 0
            );

            // Create a token using our custom token repository
            $token = $customTokenRepo->create($user);
            \Illuminate\Support\Facades\Log::info('Token created successfully using CustomTokenRepository', ['token_length' => strlen($token)]);

            // Send the password reset notification
            $user->sendPasswordResetNotification($token);
            \Illuminate\Support\Facades\Log::info('Password reset notification sent');

            $result = Password::RESET_LINK_SENT;
            \Illuminate\Support\Facades\Log::info('Reset link result', ['result' => $result]);

            return back()->with('status', __('A reset link will be sent if the account exists.'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in PasswordResetLinkController::store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['email' => __('An error occurred while processing your request.')]);
        }
    }

    /**
     * Show a form to choose the correct user.
     */
    public function chooseUser(Request $request, $emailId)
    {
        $email = \App\Models\Email::findOrFail($emailId);

        // List all users associated with the email
        $users = $email->users()->get();

        return Inertia::render('auth/ChooseUser', [
            'users' => $users,
            'email' => $email->address,
            'email_id' => $emailId,
        ]);
    }

    /**
     * Handle sending a reset link for the selected user.
     */
    public function sendChosenUserResetLink(Request $request, $emailId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Get the user
        $user = \App\Models\User::findOrFail($request->input('user_id'));

        // Get the email model
        $email = \App\Models\Email::findOrFail($emailId);

        // Create a custom token repository directly
        $config = config('auth.passwords.users');
        $customTokenRepo = new \App\Auth\CustomTokenRepository(
            \Illuminate\Support\Facades\DB::connection(),
            \Illuminate\Support\Facades\Hash::getFacadeRoot(),
            $config['table'],
            config('app.key'),
            ($config['expire'] ?? 60) * 60,
            $config['throttle'] ?? 0
        );

        // Create a token using our custom token repository
        $token = $customTokenRepo->create($user);
        \Illuminate\Support\Facades\Log::info('Token created successfully using CustomTokenRepository', ['token_length' => strlen($token)]);

        // Send the password reset notification
        $user->sendPasswordResetNotification($token);
        \Illuminate\Support\Facades\Log::info('Password reset notification sent');

        return redirect()->route('login')->with('status', __('A reset link will be sent if the account exists.'));
    }
}
