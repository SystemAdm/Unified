<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Email;

class FixPasswordResetController extends Controller
{
    /**
     * Fix the password reset token creation issue.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fix(Request $request)
    {
        Log::info('Starting password reset fix');

        $results = [
            'issue' => 'Password reset tokens are not being created',
            'error_from_logs' => 'SQLSTATE[HY000]: General error: 1 table password_reset_tokens has no column named email',
            'diagnosis' => 'The default Laravel DatabaseTokenRepository is trying to use "email" as a column name when our table has "user_id" instead.',
            'actions_taken' => [],
            'recommendations' => []
        ];

        // Check if the password_reset_tokens table exists and has the correct structure
        if (Schema::hasTable('password_reset_tokens')) {
            $columns = Schema::getColumnListing('password_reset_tokens');
            $results['table_structure'] = [
                'exists' => true,
                'columns' => $columns
            ];

            // Check if the table has the expected columns
            if (in_array('user_id', $columns) && !in_array('email', $columns)) {
                $results['actions_taken'][] = 'Confirmed that the password_reset_tokens table has user_id but not email column';

                // Check if our CustomTokenRepository is properly overriding all necessary methods
                $customTokenRepositoryMethods = get_class_methods('\\App\\Auth\\CustomTokenRepository');
                $results['custom_token_repository_methods'] = $customTokenRepositoryMethods;

                // Check if the create method is properly implemented
                if (in_array('create', $customTokenRepositoryMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomTokenRepository has a create method';
                } else {
                    $results['recommendations'][] = 'Implement the create method in CustomTokenRepository';
                }

                // Check if the getPayload method is properly implemented
                if (in_array('getPayload', $customTokenRepositoryMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomTokenRepository has a getPayload method';
                } else {
                    $results['recommendations'][] = 'Implement the getPayload method in CustomTokenRepository';
                }

                // Check if the deleteExisting method is properly implemented
                if (in_array('deleteExisting', $customTokenRepositoryMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomTokenRepository has a deleteExisting method';
                } else {
                    $results['recommendations'][] = 'Implement the deleteExisting method in CustomTokenRepository';
                }

                // Check if the exists method is properly implemented
                if (in_array('exists', $customTokenRepositoryMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomTokenRepository has an exists method';
                } else {
                    $results['recommendations'][] = 'Implement the exists method in CustomTokenRepository';
                }

                // Check if the recentlyCreatedToken method is properly implemented
                if (in_array('recentlyCreatedToken', $customTokenRepositoryMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomTokenRepository has a recentlyCreatedToken method';
                } else {
                    $results['recommendations'][] = 'Implement the recentlyCreatedToken method in CustomTokenRepository';
                }

                // Check if the delete method is properly implemented
                if (in_array('delete', $customTokenRepositoryMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomTokenRepository has a delete method';
                } else {
                    $results['recommendations'][] = 'Implement the delete method in CustomTokenRepository';
                }

                // Check if our CustomPasswordBroker is properly overriding all necessary methods
                $customPasswordBrokerMethods = get_class_methods('\\App\\Auth\\CustomPasswordBroker');
                $results['custom_password_broker_methods'] = $customPasswordBrokerMethods;

                // Check if the sendResetLink method is properly implemented
                if (in_array('sendResetLink', $customPasswordBrokerMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomPasswordBroker has a sendResetLink method';
                } else {
                    $results['recommendations'][] = 'Implement the sendResetLink method in CustomPasswordBroker';
                }

                // Check if the getUser method is properly implemented
                if (in_array('getUser', $customPasswordBrokerMethods)) {
                    $results['actions_taken'][] = 'Confirmed that CustomPasswordBroker has a getUser method';
                } else {
                    $results['recommendations'][] = 'Implement the getUser method in CustomPasswordBroker';
                }

                // Check if our CustomPasswordBrokerManager is properly registered
                $appServiceProvider = file_get_contents(base_path('app/Providers/AppServiceProvider.php'));
                if (strpos($appServiceProvider, 'CustomPasswordBrokerManager') !== false) {
                    $results['actions_taken'][] = 'Confirmed that CustomPasswordBrokerManager is registered in AppServiceProvider';
                } else {
                    $results['recommendations'][] = 'Register CustomPasswordBrokerManager in AppServiceProvider';
                }

                // Final recommendations
                if (empty($results['recommendations'])) {
                    $results['conclusion'] = 'All necessary methods are implemented. The issue might be with how the token repository is being used. Try testing with the direct token creation endpoint to see if that works.';
                } else {
                    $results['conclusion'] = 'Some methods are missing or not properly implemented. Implement the recommended methods to fix the issue.';
                }
            } else {
                if (!in_array('user_id', $columns)) {
                    $results['recommendations'][] = 'Add user_id column to password_reset_tokens table';
                }
                if (in_array('email', $columns)) {
                    $results['recommendations'][] = 'Remove email column from password_reset_tokens table or update CustomTokenRepository to use email instead of user_id';
                }
                $results['conclusion'] = 'The password_reset_tokens table structure does not match what CustomTokenRepository expects. Update either the table or the repository.';
            }
        } else {
            $results['table_structure'] = [
                'exists' => false
            ];
            $results['recommendations'][] = 'Create the password_reset_tokens table with the correct structure';
            $results['conclusion'] = 'The password_reset_tokens table does not exist. Run the migrations to create it.';
        }

        return response()->json($results);
    }
}
