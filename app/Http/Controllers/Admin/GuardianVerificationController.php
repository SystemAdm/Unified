<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GuardianVerificationController extends Controller
{
    /**
     * Display a listing of guardian relationships that need verification.
     */
    public function index()
    {
        // Get all guardian-child relationships that are not verified
        $relationships = \DB::table('guardian_child')
            ->whereNull('verified_at')
            ->join('users as children', 'guardian_child.child_id', '=', 'children.id')
            ->join('users as guardians', 'guardian_child.guardian_id', '=', 'guardians.id')
            ->select(
                'guardian_child.id',
                'guardian_child.child_id',
                'guardian_child.guardian_id',
                'guardian_child.relation_guarded',
                'guardian_child.relation_guardian',
                'children.given_name as child_given_name',
                'children.family_name as child_family_name',
                'guardians.given_name as guardian_given_name',
                'guardians.family_name as guardian_family_name'
            )
            ->get();

        return Inertia::render('Admin/GuardianVerification/Index', [
            'relationships' => $relationships
        ]);
    }

    /**
     * Verify a guardian-child relationship.
     */
    public function verify(Request $request, $id)
    {
        // Find the relationship in the pivot table
        $relationship = \DB::table('guardian_child')->where('id', $id)->first();

        if (!$relationship) {
            return response()->json(['message' => 'Relationship not found'], 404);
        }

        // Get the guardian and child users
        $guardian = User::findOrFail($relationship->guardian_id);
        $child = User::findOrFail($relationship->child_id);

        // Verify the relationship
        $guardian->verifyGuardianship($child, Auth::user());

        return redirect()->back()->with('success', 'Guardian relationship verified successfully');
    }
}
