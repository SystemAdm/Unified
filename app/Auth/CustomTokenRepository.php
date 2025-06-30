<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Carbon;

class CustomTokenRepository extends DatabaseTokenRepository
{
    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return int
     */
    protected function deleteExisting(CanResetPasswordContract $user)
    {
        \Illuminate\Support\Facades\Log::info('CustomTokenRepository::deleteExisting called', [
            'user_id' => $user->getAuthIdentifier(),
            'user_class' => get_class($user)
        ]);

        try {
            $result = $this->getTable()->where('user_id', $user->getAuthIdentifier())->delete();
            \Illuminate\Support\Facades\Log::info('Deleted existing tokens', ['count' => $result]);
            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in CustomTokenRepository::deleteExisting', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Build the record payload for the table.
     *
     * @param  string  $email
     * @param  string  $token
     * @return array
     */
    protected function getPayload($email, #[\SensitiveParameter] $token)
    {
        \Illuminate\Support\Facades\Log::info('CustomTokenRepository::getPayload called', [
            'email_type' => is_object($email) ? get_class($email) : 'string',
            'email_value' => is_object($email) ? 'object' : $email
        ]);

        try {
            // If the email is actually a user object (which happens when sendResetLink is called with both email and id)
            if ($email instanceof \Illuminate\Contracts\Auth\CanResetPassword) {
                \Illuminate\Support\Facades\Log::info('Email is a CanResetPassword instance', [
                    'user_id' => $email->getAuthIdentifier()
                ]);

                $payload = [
                    'user_id' => $email->getAuthIdentifier(),
                    'token' => $this->hasher->make($token),
                    'created_at' => new Carbon
                ];

                \Illuminate\Support\Facades\Log::info('Returning payload for user object', ['keys' => array_keys($payload)]);
                return $payload;
            }

            // Otherwise, try to find the user by email
            \Illuminate\Support\Facades\Log::info('Looking up user by email', ['email' => $email]);
            $user = \App\Models\User::whereHas('emails', function ($query) use ($email) {
                $query->where('address', $email);
            })->first();

            if (!$user) {
                \Illuminate\Support\Facades\Log::error('No user found for email', ['email' => $email]);
                // If no user is found, we can't create a token without a user_id
                throw new \InvalidArgumentException("Unable to find user for the given email.");
            }

            \Illuminate\Support\Facades\Log::info('Found user for email', ['user_id' => $user->id, 'email' => $email]);
            $payload = [
                'user_id' => $user->id,
                'token' => $this->hasher->make($token),
                'created_at' => new Carbon
            ];

            \Illuminate\Support\Facades\Log::info('Returning payload for email', ['keys' => array_keys($payload)]);
            return $payload;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in CustomTokenRepository::getPayload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, #[\SensitiveParameter] $token)
    {
        \Illuminate\Support\Facades\Log::info('CustomTokenRepository::exists called', [
            'user_id' => $user->getAuthIdentifier(),
            'user_class' => get_class($user),
            'token_length' => strlen($token)
        ]);

        try {
            $record = (array) $this->getTable()->where(
                'user_id', $user->getAuthIdentifier()
            )->first();

            if (!$record) {
                \Illuminate\Support\Facades\Log::error('No token record found for user', [
                    'user_id' => $user->getAuthIdentifier()
                ]);
                return false;
            }

            if ($this->tokenExpired($record['created_at'])) {
                \Illuminate\Support\Facades\Log::error('Token has expired', [
                    'user_id' => $user->getAuthIdentifier(),
                    'created_at' => $record['created_at']
                ]);
                return false;
            }

            $tokenMatches = $this->hasher->check($token, $record['token']);
            if (!$tokenMatches) {
                \Illuminate\Support\Facades\Log::error('Token does not match', [
                    'user_id' => $user->getAuthIdentifier()
                ]);
                return false;
            }

            \Illuminate\Support\Facades\Log::info('Token is valid', [
                'user_id' => $user->getAuthIdentifier()
            ]);
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in CustomTokenRepository::exists', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Determine if the given user recently created a password reset token.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return bool
     */
    public function recentlyCreatedToken(CanResetPasswordContract $user)
    {
        $record = (array) $this->getTable()->where(
            'user_id', $user->getAuthIdentifier()
        )->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    /**
     * Create a new token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return string
     */
    public function create(CanResetPasswordContract $user)
    {
        \Illuminate\Support\Facades\Log::info('CustomTokenRepository::create called', [
            'user_id' => $user->getAuthIdentifier(),
            'user_class' => get_class($user),
            'email' => $user->getEmailForPasswordReset()
        ]);

        try {
            $this->deleteExisting($user);
            \Illuminate\Support\Facades\Log::info('Existing tokens deleted');

            // We will create a new, random token for the user so that we can e-mail them
            // a safe link to the password reset form. Then we will insert a record in
            // the database so that we can verify the token within the actual reset.
            $token = $this->createNewToken();
            \Illuminate\Support\Facades\Log::info('New token created');

            // Create payload with user_id
            $payload = [
                'user_id' => $user->getAuthIdentifier(),
                'token' => $this->hasher->make($token),
                'created_at' => new Carbon
            ];
            \Illuminate\Support\Facades\Log::info('Payload created', ['payload' => array_keys($payload)]);

            // Log the table structure
            $tableColumns = $this->connection->getSchemaBuilder()->getColumnListing($this->table);
            \Illuminate\Support\Facades\Log::info('Table columns', ['table' => $this->table, 'columns' => $tableColumns]);

            $this->getTable()->insert($payload);
            \Illuminate\Support\Facades\Log::info('Token inserted into database');

            return $token;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in CustomTokenRepository::create', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a token record by user.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return void
     */
    public function delete(CanResetPasswordContract $user)
    {
        $this->deleteExisting($user);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        \Illuminate\Support\Facades\Log::info('CustomTokenRepository::tokenExpired called', [
            'created_at' => $createdAt,
            'expires' => $this->expires
        ]);

        try {
            $expiresAt = Carbon::parse($createdAt)->addSeconds($this->expires);
            $now = Carbon::now();
            $expired = $expiresAt->isPast();

            \Illuminate\Support\Facades\Log::info('Token expiration check', [
                'created_at' => $createdAt,
                'expires_at' => $expiresAt->toDateTimeString(),
                'now' => $now->toDateTimeString(),
                'expired' => $expired ? 'Yes' : 'No'
            ]);

            return $expired;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in CustomTokenRepository::tokenExpired', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return true; // Assume expired if there's an error
        }
    }

    /**
     * Debug token validation for a specific token and email.
     *
     * @param  string  $token
     * @param  string  $email
     * @return array
     */
    public function debugToken(#[\SensitiveParameter] $token, $email)
    {
        \Illuminate\Support\Facades\Log::info('CustomTokenRepository::debugToken called', [
            'email' => $email,
            'token_length' => strlen($token)
        ]);

        $debug = [
            'email' => $email,
            'token_length' => strlen($token),
            'users' => [],
            'success' => false
        ];

        try {
            // Find all users associated with the email
            $emailModel = \App\Models\Email::where('address', $email)->first();
            if (!$emailModel) {
                $debug['error'] = 'No email found';
                return $debug;
            }

            $users = $emailModel->users()->get();
            $debug['users_count'] = $users->count();

            // Check each user's token
            foreach ($users as $user) {
                $userDebug = [
                    'user_id' => $user->getAuthIdentifier(),
                    'email' => $user->getEmailForPasswordReset()
                ];

                $record = (array) $this->getTable()->where('user_id', $user->getAuthIdentifier())->first();

                if (!$record) {
                    $userDebug['error'] = 'No token record found';
                } else {
                    $userDebug['created_at'] = $record['created_at'];

                    $expiresAt = Carbon::parse($record['created_at'])->addSeconds($this->expires);
                    $now = Carbon::now();
                    $expired = $expiresAt->isPast();

                    $userDebug['expires_at'] = $expiresAt->toDateTimeString();
                    $userDebug['now'] = $now->toDateTimeString();
                    $userDebug['expired'] = $expired ? 'Yes' : 'No';

                    $tokenMatches = $this->hasher->check($token, $record['token']);
                    $userDebug['token_matches'] = $tokenMatches ? 'Yes' : 'No';

                    if (!$expired && $tokenMatches) {
                        $userDebug['valid'] = true;
                        $debug['success'] = true;
                        $debug['valid_user_id'] = $user->getAuthIdentifier();
                    } else {
                        $userDebug['valid'] = false;
                    }
                }

                $debug['users'][] = $userDebug;
            }

            return $debug;
        } catch (\Exception $e) {
            $debug['error'] = $e->getMessage();
            return $debug;
        }
    }

    /**
     * Find a user by token and email.
     *
     * @param  string  $token
     * @param  string  $email
     * @return \Illuminate\Contracts\Auth\CanResetPassword|null
     */
    public function findUserByToken(#[\SensitiveParameter] $token, $email)
    {
        \Illuminate\Support\Facades\Log::info('CustomTokenRepository::findUserByToken called', [
            'email' => $email
        ]);

        try {
            // Find all users associated with the email
            $emailModel = \App\Models\Email::where('address', $email)->first();
            if (!$emailModel) {
                \Illuminate\Support\Facades\Log::error('No email found', ['email' => $email]);
                return null;
            }

            $users = $emailModel->users()->get();
            \Illuminate\Support\Facades\Log::info('Found users for email', ['count' => $users->count()]);

            // Check each user's token
            foreach ($users as $user) {
                \Illuminate\Support\Facades\Log::info('Checking token for user', [
                    'user_id' => $user->getAuthIdentifier(),
                    'email' => $user->getEmailForPasswordReset()
                ]);

                $record = (array) $this->getTable()->where('user_id', $user->getAuthIdentifier())->first();

                if (!$record) {
                    \Illuminate\Support\Facades\Log::error('No token record found for user', [
                        'user_id' => $user->getAuthIdentifier()
                    ]);
                    continue;
                }

                \Illuminate\Support\Facades\Log::info('Found token record for user', [
                    'user_id' => $user->getAuthIdentifier(),
                    'created_at' => $record['created_at']
                ]);

                if ($this->tokenExpired($record['created_at'])) {
                    \Illuminate\Support\Facades\Log::error('Token has expired', [
                        'user_id' => $user->getAuthIdentifier(),
                        'created_at' => $record['created_at']
                    ]);
                    continue;
                }

                $tokenMatches = $this->hasher->check($token, $record['token']);
                if (!$tokenMatches) {
                    \Illuminate\Support\Facades\Log::error('Token does not match', [
                        'user_id' => $user->getAuthIdentifier()
                    ]);
                    continue;
                }

                \Illuminate\Support\Facades\Log::info('Found matching token for user', ['user_id' => $user->id]);
                return $user;
            }

            \Illuminate\Support\Facades\Log::error('No matching token found for any user');
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in CustomTokenRepository::findUserByToken', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
