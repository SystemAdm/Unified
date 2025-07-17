<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;

class MembershipController extends Controller
{
    /**
     * Show the user's membership settings page.
     */
    public function edit(Request $request): Response
    {
        // Payment functionality has been removed
        $user = $request->user();

        // Define membership option
        $membershipTiers = [
            [
                'id' => 1,
                'name' => 'Membership',
                'price' => 50, // Annual price in NOK
                'price_display' => '50 NOK/year',
                'features' => ['Access to community events', 'Game discounts', 'Online forum access', 'Priority event registration'],
            ],
        ];

        return Inertia::render('settings/Membership', [
            'membershipTiers' => $membershipTiers,
            'currentMembership' => null, // This would be replaced with actual user membership data
            'status' => $request->session()->get('status'),
            // Payment configurations have been removed
        ]);
    }

    /**
     * Process a membership purchase.
     * Payment functionality has been removed.
     */
    public function purchase(Request $request): RedirectResponse
    {
        $request->validate([
            'tier_id' => ['required', 'integer', 'in:1'],
        ]);

        // Payment functionality has been removed
        return Redirect::route('membership.edit')->with('status', 'payment-unavailable');
    }

    /**
     * Handle the callback from payment provider.
     * Payment functionality has been removed.
     */
    public function handleCallback(Request $request): RedirectResponse
    {
        // Payment functionality has been removed
        return Redirect::route('membership.edit')->with('status', 'payment-unavailable');
    }

    /**
     * Process a membership purchase with Stripe.
     * Payment functionality has been removed.
     */
    public function purchaseWithStripe(Request $request): RedirectResponse
    {
        // Payment functionality has been removed
        return Redirect::route('membership.edit')->with('status', 'payment-unavailable');
    }

    /**
     * Handle payment webhook events.
     * Payment functionality has been removed.
     */
    public function handleWebhook(Request $request)
    {
        // Payment functionality has been removed
        return response()->json(['message' => 'Payment functionality has been removed'], 200);
    }

    /**
     * Cancel a membership.
     * Payment functionality has been removed.
     */
    public function cancel(Request $request): RedirectResponse
    {
        // Payment functionality has been removed
        return Redirect::route('membership.edit')->with('status', 'payment-unavailable');
    }
}
