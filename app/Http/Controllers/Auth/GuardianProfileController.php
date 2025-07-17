<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class GuardianProfileController extends Controller
{
    /**
     * Show the guardian profile completion form.
     */
    public function create()
    {
        \Log::info('GuardianProfileController::create method called');
        return Inertia::render('auth/CompleteGuardianProfile', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Handle the guardian profile completion.
     */
    public function store(Request $request)
    {
        \Log::info('GuardianProfileController::store method called');
        \Log::info('Request data: ' . json_encode($request->all()));
        $user = auth()->user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'birthday' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'phone' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update the user's password and birthday
        $user->password = Hash::make($request->password);
        $user->birthday = $request->birthday;

        // Save the user to persist password and birthday changes
        $user->save();

        // Directly create and associate the phone
        $phoneNumber = trim($request->phone);

        // Create a new phone model
        $phoneModel = new Phone();
        $phoneModel->phone_number = $phoneNumber;

        // Check if a phone with the same country_code and number already exists
        $existingPhone = Phone::where('country_code', $phoneModel->country_code)
            ->where('number', $phoneModel->number)
            ->first();

        if ($existingPhone) {
            $phoneModel = $existingPhone;
        } else {
            $phoneModel->save();
        }

        // Check if this phone is already associated with the user
        if (!$user->phones()->where('phones.id', $phoneModel->id)->exists()) {
            // Associate the phone with the user
            $isPrimary = $user->phones()->count() === 0; // Set as primary if it's the first phone
            $user->phones()->attach($phoneModel, ['is_primary' => $isPrimary]);
        } else {
            // If the phone exists, make it primary
            // First, set is_primary to false for all phones
            $user->phones()->updateExistingPivot($user->phones()->pluck('phones.id'), ['is_primary' => false]);

            // Then set is_primary to true for this phone
            $user->phones()->updateExistingPivot($phoneModel->id, ['is_primary' => true]);
        }

        // Refresh the user model to ensure we have the latest data
        $user = $user->fresh();

        // Verify that the phone was successfully created and associated
        if ($user->phones()->count() === 0) {
            return redirect()->back()->withErrors(['phone' => 'Failed to associate phone number with your account.'])->withInput();
        }

        return redirect()->route('dashboard')->with('status', 'profile-completed');
    }
}
