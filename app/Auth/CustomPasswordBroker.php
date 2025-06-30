<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\UserProvider;
use App\Models\User;
use App\Models\Email;

class CustomPasswordBroker extends PasswordBroker
{
    /**
     * Reset the password for the given token.
     *
     * @param  array  $credentials
     * @param  \Closure  $callback
     * @return string
     */
    public function reset(#[\SensitiveParameter] array $credentials, \Closure $callback)
    {
        \Illuminate\Support\Facades\Log::info('CustomPasswordBroker::reset called', [
            'credentials_keys' => array_keys($credentials)
        ]);

        return $this->timebox->call(function ($timebox) use ($credentials, $callback) {
            try {
                // First try the standard validation
                $user = $this->validateReset($credentials);

                // If the user is not found but we have a valid email and token, try to find the user by token
                if (!$user instanceof CanResetPassword && isset($credentials['email']) && isset($credentials['token'])) {
                    \Illuminate\Support\Facades\Log::info('Standard validation failed, trying to find user by token and email');

                    // Check if there are multiple users with this email
                    $email = Email::where('address', $credentials['email'])->first();
                    if ($email) {
                        $usersCount = $email->users()->count();
                        \Illuminate\Support\Facades\Log::info('Found email with users', ['count' => $usersCount]);

                        if ($usersCount > 1) {
                            // Try to find the user by token and email
                            $user = $this->tokens->findUserByToken($credentials['token'], $credentials['email']);

                            if ($user) {
                                \Illuminate\Support\Facades\Log::info('Found user by token and email', ['user_id' => $user->getAuthIdentifier()]);
                            } else {
                                \Illuminate\Support\Facades\Log::error('No user found by token and email');
                                return static::INVALID_TOKEN;
                            }
                        }
                    }
                }

                // If we still don't have a valid user, return the error
                if (!$user instanceof CanResetPassword) {
                    \Illuminate\Support\Facades\Log::error('No valid user found for reset');
                    return $user ?: static::INVALID_USER;
                }

                $password = $credentials['password'];

                // Once the reset has been validated, we'll call the given callback with the
                // new password. This gives the user an opportunity to store the password
                // in their persistent storage. Then we'll delete the token and return.
                $callback($user, $password);

                $this->tokens->delete($user);

                $timebox->returnEarly();

                \Illuminate\Support\Facades\Log::info('Password reset successful');
                return static::PASSWORD_RESET;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error in CustomPasswordBroker::reset', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }, $this->timeboxDuration);
    }

    /**
     * Send a password reset link to a user.
     *
     * @param  array  $credentials
     * @param  \Closure|null  $callback
     * @return string
     */
    public function sendResetLink(#[\SensitiveParameter] array $credentials, ?\Closure $callback = null)
    {
        \Illuminate\Support\Facades\Log::info('CustomPasswordBroker::sendResetLink called', [
            'credentials_keys' => array_keys($credentials)
        ]);

        return $this->timebox->call(function () use ($credentials, $callback) {
            try {
                // First, we will check to see if we found a user at the given credentials and
                // if we did not we will redirect back to this current URI with a piece of
                // "flash" data in the session to indicate to the developers the errors.
                $user = $this->getUser($credentials);

                if (is_null($user)) {
                    \Illuminate\Support\Facades\Log::info('No user found for credentials');
                    return static::INVALID_USER;
                }

                \Illuminate\Support\Facades\Log::info('Found user for credentials', [
                    'user_id' => $user->getAuthIdentifier(),
                    'user_class' => get_class($user)
                ]);

                if ($this->tokens->recentlyCreatedToken($user)) {
                    \Illuminate\Support\Facades\Log::info('Token recently created for user');
                    return static::RESET_THROTTLED;
                }

                \Illuminate\Support\Facades\Log::info('Creating token for user', [
                    'token_repository_class' => get_class($this->tokens)
                ]);

                $token = $this->tokens->create($user);
                \Illuminate\Support\Facades\Log::info('Token created successfully', ['token_length' => strlen($token)]);

                if ($callback) {
                    \Illuminate\Support\Facades\Log::info('Executing callback');
                    return $callback($user, $token) ?? static::RESET_LINK_SENT;
                }

                // Once we have the reset token, we are ready to send the message out to this
                // user with a link to reset their password. We will then redirect back to
                // the current URI having nothing set in the session to indicate errors.
                \Illuminate\Support\Facades\Log::info('Sending password reset notification to user');
                $user->sendPasswordResetNotification($token);

                if ($this->events) {
                    \Illuminate\Support\Facades\Log::info('Dispatching PasswordResetLinkSent event');
                    $this->events->dispatch(new \Illuminate\Auth\Events\PasswordResetLinkSent($user));
                }

                \Illuminate\Support\Facades\Log::info('Reset link sent successfully');
                return static::RESET_LINK_SENT;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error in CustomPasswordBroker::sendResetLink', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }, $this->timeboxDuration);
    }

    /**
     * Get the user for the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\CanResetPassword|null
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        \Illuminate\Support\Facades\Log::info('CustomPasswordBroker::getUser called', [
            'credentials_keys' => array_keys($credentials)
        ]);

        try {
            $credentials = (array) $credentials;

            // If an ID is provided, we'll use that to find the user
            if (isset($credentials['id'])) {
                \Illuminate\Support\Facades\Log::info('Looking up user by ID', ['id' => $credentials['id']]);
                $user = User::find($credentials['id']);
                if ($user) {
                    \Illuminate\Support\Facades\Log::info('Found user by ID', ['user_id' => $user->id]);
                    return $user;
                } else {
                    \Illuminate\Support\Facades\Log::info('No user found for ID', ['id' => $credentials['id']]);
                }
            }

            // If no ID is provided, we'll try to find the user by email
            if (isset($credentials['email'])) {
                \Illuminate\Support\Facades\Log::info('Looking up email', ['email' => $credentials['email']]);
                $email = Email::where('address', $credentials['email'])->first();
                if ($email) {
                    \Illuminate\Support\Facades\Log::info('Found email', ['email_id' => $email->id]);
                    $users = $email->users()->get();
                    \Illuminate\Support\Facades\Log::info('Found users for email', ['count' => $users->count()]);
                    if ($users->count() === 1) {
                        $user = $users->first();
                        \Illuminate\Support\Facades\Log::info('Returning single user for email', ['user_id' => $user->id]);
                        return $user;
                    } else {
                        \Illuminate\Support\Facades\Log::info('Multiple users found for email, not returning any');
                    }
                } else {
                    \Illuminate\Support\Facades\Log::info('No email found', ['email' => $credentials['email']]);
                }
            }

            \Illuminate\Support\Facades\Log::info('No user found for credentials');
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in CustomPasswordBroker::getUser', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
