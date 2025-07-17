<?php

namespace App\Http\Controllers\Auth;

use App\Enum\RelationGuarded;
use App\Enum\RelationGuardian;
use App\Http\Controllers\Controller;
use App\Mail\GuardianRegistrationMail;
use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class GuardianRegistrationController extends Controller
{
    /**
     * Show the guardian registration form.
     */
    public function create(): Response
    {
        return Inertia::render('auth/RegisterGuardian', [
            'relationGuardedOptions' => $this->getRelationGuardedOptions(),
            'relationGuardianOptions' => $this->getRelationGuardianOptions(),
        ]);
    }

    /**
     * Handle the guardian registration.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'relation_guarded' => 'required|string|in:' . implode(',', $this->getRelationGuardedValues()),
            'relation_guardian' => 'required|string|in:' . implode(',', $this->getRelationGuardianValues()),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get the authenticated user (child)
        $child = Auth::user();

        // Create a temporary guardian user (will be properly registered when they follow the email link)
        $guardian = User::create([
            'given_name' => $request->name,
            'family_name' => '',
            'account_type' => 'guardian',
        ]);

        // Set the guardian's email
        $guardian->email = $request->email;

        // Create and associate the email with the guardian
        $email = Email::firstOrCreate(['address' => $request->email]);
        $guardian->emails()->attach($email->id, ['is_primary' => true, 'verified_at' => now()]);
        $guardian->setPrimaryEmail($email);

        // Add the guardian-child relationship
        $relationGuarded = RelationGuarded::from($request->relation_guarded);
        $relationGuardian = RelationGuardian::from($request->relation_guardian);

        $child->addGuardian($guardian, $relationGuarded, $relationGuardian);

        // Send email to the guardian
        $this->sendGuardianEmail($guardian, $child);

        return redirect()->route('dashboard')->with('status', 'guardian-registered');
    }

    /**
     * Send an email to the guardian with instructions on how to register their account.
     */
    private function sendGuardianEmail(User $guardian, User $child)
    {
        // Log the email sending attempt
        \Log::info('Sending guardian registration email', [
            'guardian_email' => $guardian->email,
            'child_name' => $child->name,
        ]);

        // Send the email
        Mail::to($guardian->email)->send(new GuardianRegistrationMail($guardian, $child));

        // Log success
        \Log::info('Guardian registration email sent successfully');
    }

    /**
     * Get the available relation guarded options for the dropdown.
     */
    private function getRelationGuardedOptions(): array
    {
        $options = [];
        foreach (RelationGuarded::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => ucfirst($case->value),
            ];
        }
        return $options;
    }

    /**
     * Get the available relation guardian options for the dropdown.
     */
    private function getRelationGuardianOptions(): array
    {
        $options = [];
        foreach (RelationGuardian::cases() as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => ucfirst($case->value),
            ];
        }
        return $options;
    }

    /**
     * Get the raw values of the RelationGuarded enum for validation.
     */
    private function getRelationGuardedValues(): array
    {
        return array_map(fn($case) => $case->value, RelationGuarded::cases());
    }

    /**
     * Get the raw values of the RelationGuardian enum for validation.
     */
    private function getRelationGuardianValues(): array
    {
        return array_map(fn($case) => $case->value, RelationGuardian::cases());
    }
}
