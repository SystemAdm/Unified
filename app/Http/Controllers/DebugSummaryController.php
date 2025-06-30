<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Email;

class DebugSummaryController extends Controller
{
    /**
     * Provide a summary of the debugging setup and what we've learned.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $summary = [
            'issue' => 'Password reset tokens are not being created',
            'error_from_logs' => 'SQLSTATE[HY000]: General error: 1 table password_reset_tokens has no column named email',
            'debugging_approach' => [
                'Created debugging controllers to trace the password reset process',
                'Added extensive logging to CustomTokenRepository methods',
                'Added extensive logging to CustomPasswordBroker methods',
                'Added extensive logging to PasswordResetLinkController',
                'Created a test controller to bypass the broker and directly test token creation'
            ],
            'possible_causes' => [
                'The default Laravel DatabaseTokenRepository is being used instead of our CustomTokenRepository',
                'Our CustomTokenRepository is not correctly overriding all the necessary methods',
                'The migration might not have been run correctly',
                'There might be a mismatch between the expected and actual table structure'
            ],
            'database_structure' => $this->getDatabaseStructure(),
            'custom_implementations' => $this->getCustomImplementations(),
            'next_steps' => [
                'Test the direct token creation to see if the database structure is correct',
                'Test the password reset functionality with the debugging in place',
                'Examine the logs to understand why tokens aren\'t being created',
                'Fix the issue based on the findings'
            ]
        ];

        return response()->json($summary);
    }

    /**
     * Get the database structure for relevant tables.
     *
     * @return array
     */
    private function getDatabaseStructure()
    {
        $structure = [];

        // Check if the password_reset_tokens table exists
        if (Schema::hasTable('password_reset_tokens')) {
            $structure['password_reset_tokens'] = [
                'exists' => true,
                'columns' => Schema::getColumnListing('password_reset_tokens')
            ];

            // Get a sample row if available
            $sampleRow = DB::table('password_reset_tokens')->first();
            if ($sampleRow) {
                $structure['password_reset_tokens']['sample_row'] = json_decode(json_encode($sampleRow), true);
            } else {
                $structure['password_reset_tokens']['sample_row'] = 'No rows found';
            }
        } else {
            $structure['password_reset_tokens'] = [
                'exists' => false
            ];
        }

        return $structure;
    }

    /**
     * Get information about custom implementations.
     *
     * @return array
     */
    private function getCustomImplementations()
    {
        $implementations = [];

        // Check if CustomTokenRepository exists
        if (class_exists('\\App\\Auth\\CustomTokenRepository')) {
            $implementations['CustomTokenRepository'] = [
                'exists' => true,
                'methods' => get_class_methods('\\App\\Auth\\CustomTokenRepository')
            ];
        } else {
            $implementations['CustomTokenRepository'] = [
                'exists' => false
            ];
        }

        // Check if CustomPasswordBroker exists
        if (class_exists('\\App\\Auth\\CustomPasswordBroker')) {
            $implementations['CustomPasswordBroker'] = [
                'exists' => true,
                'methods' => get_class_methods('\\App\\Auth\\CustomPasswordBroker')
            ];
        } else {
            $implementations['CustomPasswordBroker'] = [
                'exists' => false
            ];
        }

        // Check if CustomPasswordBrokerManager exists
        if (class_exists('\\App\\Auth\\CustomPasswordBrokerManager')) {
            $implementations['CustomPasswordBrokerManager'] = [
                'exists' => true,
                'methods' => get_class_methods('\\App\\Auth\\CustomPasswordBrokerManager')
            ];
        } else {
            $implementations['CustomPasswordBrokerManager'] = [
                'exists' => false
            ];
        }

        return $implementations;
    }
}
