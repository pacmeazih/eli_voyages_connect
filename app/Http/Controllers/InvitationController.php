<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use App\Mail\InvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller
{
    /**
     * Display a listing of invitations.
     */
    public function index()
    {
        $this->authorize('invite users');
        
        $invitations = Invitation::with(['inviter', 'dossier'])
            ->latest()
            ->paginate(20);

        $stats = [
            'pending' => Invitation::whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->count(),
            'accepted' => Invitation::whereNotNull('accepted_at')->count(),
            'expired' => Invitation::whereNull('accepted_at')
                ->where('expires_at', '<=', now())
                ->count(),
        ];

        return inertia('Invitations/Index', [
            'invitations' => $invitations,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new invitation.
     */
    public function create()
    {
        $this->authorize('invite users');

        return inertia('Invitations/Create');
    }

    /**
     * Store a newly created invitation in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('invite users');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(['Consultant', 'Agent', 'Client', 'Guarantor'])],
            'dossier_id' => ['nullable', 'exists:dossiers,id'],
        ]);

        // Check if user already exists
        if (User::where('email', $validated['email'])->exists()) {
            return back()->withErrors([
                'email' => 'A user with this email already exists.',
            ]);
        }

        // Check if there's already a valid invitation
        $existingInvitation = Invitation::where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return back()->withErrors([
                'email' => 'An active invitation already exists for this email.',
            ]);
        }

        // Create invitation
        $invitation = Invitation::createInvitation(
            $validated['email'],
            $validated['role'],
            auth()->id(),
            $validated['dossier_id'] ?? null
        );

        // Send invitation email
        Mail::to($validated['email'])->send(new InvitationMail($invitation));

        return redirect()->route('invitations.index')
            ->with('success', 'Invitation sent successfully!');
    }

    /**
     * Show the invitation acceptance form.
     */
    public function show(string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (!$invitation->isValid()) {
            return inertia('Invitations/Expired');
        }

        return inertia('Invitations/Accept', [
            'invitation' => $invitation,
        ]);
    }

    /**
     * Accept the invitation and create user account.
     */
    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (!$invitation->isValid()) {
            return back()->withErrors([
                'token' => 'This invitation has expired or has already been used.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($invitation, $validated) {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            // Assign role
            $user->assignRole($invitation->role);

            // Mark invitation as accepted
            $invitation->markAsAccepted();

            // Log the user in
            auth()->login($user);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account has been created successfully.');
    }

    /**
     * Cancel/delete an invitation.
     */
    public function destroy(Invitation $invitation)
    {
        $this->authorize('invite users');

        $invitation->delete();

        return back()->with('success', 'Invitation cancelled successfully.');
    }

    /**
     * Resend an invitation email.
     */
    public function resend(Invitation $invitation)
    {
        $this->authorize('invite users');

        if (!$invitation->isValid()) {
            // regenerate token and extend expiry by 7 days
            $invitation->token = Invitation::generateToken();
            $invitation->expires_at = now()->addDays(7);
            $invitation->accepted_at = null;
            $invitation->save();
        }

        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        return back()->with('success', 'Invitation resent successfully.');
    }
}
