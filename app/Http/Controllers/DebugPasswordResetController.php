<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\Email;

class DebugPasswordResetController extends Controller
{
    /**
     * Debug the password reset token creation process.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function debug(Request $request)
    {
        $email = $request->input('email', 'test@example.com');

        Log::info('Starting password reset debug for email: ' . $email);

        // Find the email model
        $emailModel = Email::where('address', $email)->first();
        if (!$emailModel) {
            Log::error('Email not found: ' . $email);
            return response()->json(['error' => 'Email not found'], 404);
        }

        Log::info('Found email model', ['email_id' => $emailModel->id, 'address' => $emailModel->address]);

        // Get users associated with the email
        $users = $emailModel->users()->get();
        if ($users->count() === 0) {
            Log::error('No users associated with email: ' . $email);
            return response()->json(['error' => 'No users found for this email'], 404);
        }

        Log::info('Found users', ['count' => $users->count()]);

        // Get the first user
        $user = $users->first();
        Log::info('Using user', ['user_id' => $user->id, 'name' => $user->given_name . ' ' . $user->family_name]);

        // Get the broker
        $broker = Password::broker();
        Log::info('Got password broker', ['class' => get_class($broker)]);

        // Get the token repository from the broker
        $reflection = new \ReflectionClass($broker);
        $tokensProperty = $reflection->getProperty('tokens');
        $tokensProperty->setAccessible(true);
        $tokens = $tokensProperty->getValue($broker);
        Log::info('Got token repository', ['class' => get_class($tokens)]);

        try {
            // Try to create a token
            Log::info('Attempting to create token');

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

            // Use our custom token repository to create the token
            $token = $customTokenRepo->create($user);
            Log::info('Token created successfully using CustomTokenRepository', ['token_length' => strlen($token)]);

            return response()->json([
                'success' => true,
                'message' => 'Token created successfully using CustomTokenRepository',
                'token_length' => strlen($token),
                'user_id' => $user->id,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating token', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'class' => get_class($e)
            ], 500);
        }
    }
}
