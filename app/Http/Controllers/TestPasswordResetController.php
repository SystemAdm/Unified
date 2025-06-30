<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Email;

class TestPasswordResetController extends Controller
{
    /**
     * Test direct token creation to bypass the broker.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testDirectTokenCreation(Request $request)
    {
        $email = $request->input('email', 'test@example.com');

        Log::info('Starting direct token creation test for email: ' . $email);

        // Find the email model
        $emailModel = Email::where('address', $email)->first();
        if (!$emailModel) {
            Log::error('Email not found: ' . $email);
            return response()->json(['error' => 'Email not found'], 404);
        }

        // Get users associated with the email
        $users = $emailModel->users()->get();
        if ($users->count() === 0) {
            Log::error('No users associated with email: ' . $email);
            return response()->json(['error' => 'No users found for this email'], 404);
        }

        // Get the first user
        $user = $users->first();

        try {
            // Try to directly insert a token into the database
            $token = bin2hex(random_bytes(16));
            $hashedToken = password_hash($token, PASSWORD_BCRYPT);

            // First, delete any existing tokens for this user
            DB::table('password_reset_tokens')->where('user_id', $user->id)->delete();

            // Log the table structure
            $tableColumns = DB::getSchemaBuilder()->getColumnListing('password_reset_tokens');
            Log::info('Table columns', ['table' => 'password_reset_tokens', 'columns' => $tableColumns]);

            // Insert the new token
            DB::table('password_reset_tokens')->insert([
                'user_id' => $user->id,
                'token' => $hashedToken,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token created directly in database',
                'token_length' => strlen($token),
                'token' => $token,
                'user_id' => $user->id,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Error in direct token creation', [
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

    /**
     * Debug a specific token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function debugToken(Request $request)
    {
        Log::info('Starting token debug', [
            'request_data' => $request->all()
        ]);

        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email'
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

            // Debug the token
            $debug = $tokenRepo->debugToken($request->input('token'), $request->input('email'));

            return response()->json([
                'success' => true,
                'debug' => $debug
            ]);
        } catch (\Exception $e) {
            Log::error('Error in token debug', [
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

    /**
     * Test password reset with multiple users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testPasswordReset(Request $request)
    {
        Log::info('Starting password reset test', [
            'request_data' => $request->all()
        ]);

        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|same:password'
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

            // Reset the password
            $status = $broker->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => \Illuminate\Support\Str::random(60),
                    ])->save();

                    Log::info('Password reset successful for user', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                }
            );

            if ($status === \Illuminate\Auth\Passwords\PasswordBroker::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset successful'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $status
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error in password reset test', [
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
