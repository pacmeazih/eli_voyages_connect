<?php

namespace App\Http\Controllers;

use App\Models\ClientInvitation;
use App\Models\User;
use App\Mail\ClientInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ClientInvitationController extends Controller
{
    /**
     * Display invitations list (Staff only)
     */
    public function index()
    {
        $this->authorize('invite users');

        $invitations = ClientInvitation::with(['inviter', 'client', 'user'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Invitations/Index', [
            'invitations' => $invitations,
        ]);
    }

    /**
     * Show create invitation form
     */
    public function create()
    {
        $this->authorize('invite users');

        // Get available consultants for assignment
        $consultants = User::role(['SuperAdmin', 'Admin'])
            ->get(['id', 'name', 'email']);

        return Inertia::render('Invitations/Create', [
            'consultants' => $consultants,
        ]);
    }

    /**
     * Store new invitation
     */
    public function store(Request $request)
    {
        $this->authorize('invite users');

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:client_invitations,email|unique:users,email',
            'telephone' => 'required|string|max:20',
            'metadata' => 'nullable|array',
        ]);

        $invitation = ClientInvitation::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'invited_by' => auth()->id(),
            'metadata' => $validated['metadata'] ?? [],
        ]);

        // Send invitation email
        try {
            Mail::to($invitation->email)->send(new ClientInvitationMail($invitation));
            $invitation->markAsSent();

            return redirect()->route('invitations.index')
                ->with('success', "Invitation envoyée à {$invitation->prenom} {$invitation->nom} (Code: {$invitation->client_code})");
        } catch (\Exception $e) {
            return redirect()->route('invitations.index')
                ->with('error', 'Invitation créée mais l\'email n\'a pas pu être envoyé: ' . $e->getMessage());
        }
    }

    /**
     * Show invitation acceptance form (public)
     */
    public function show($token)
    {
        $invitation = ClientInvitation::where('invitation_token', $token)->firstOrFail();

        if ($invitation->status === 'accepted') {
            return Inertia::render('Invitations/AlreadyAccepted', [
                'invitation' => $invitation,
            ]);
        }

        if ($invitation->isExpired()) {
            return Inertia::render('Invitations/Expired', [
                'invitation' => $invitation,
            ]);
        }

        return Inertia::render('Invitations/Accept', [
            'invitation' => $invitation->only(['nom', 'prenom', 'email', 'telephone', 'client_code', 'expires_at']),
            'token' => $token,
        ]);
    }

    /**
     * Accept invitation and create account
     */
    public function accept(Request $request, $token)
    {
        $invitation = ClientInvitation::where('invitation_token', $token)->firstOrFail();

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'civilite' => 'required|in:M.,Mme,Mlle',
            'adresse' => 'nullable|string|max:500',
            'date_naissance' => 'nullable|date|before:today',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'profession' => 'nullable|string|max:255',
        ]);

        try {
            $result = $invitation->accept($validated);

            // Auto-login the new user
            auth()->login($result['user']);

            return redirect()->route('dashboard')
                ->with('success', 'Bienvenue ! Votre compte a été créé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Resend invitation email
     */
    public function resend(ClientInvitation $invitation)
    {
        $this->authorize('invite users');

        if ($invitation->status === 'accepted') {
            return back()->withErrors(['error' => 'Cette invitation a déjà été acceptée.']);
        }

        try {
            Mail::to($invitation->email)->send(new ClientInvitationMail($invitation));
            $invitation->markAsSent();

            return back()->with('success', 'Invitation renvoyée avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'envoi: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel/delete invitation
     */
    public function destroy(ClientInvitation $invitation)
    {
        $this->authorize('invite users');

        if ($invitation->status === 'accepted') {
            return back()->withErrors(['error' => 'Impossible de supprimer une invitation déjà acceptée.']);
        }

        $invitation->delete();

        return back()->with('success', 'Invitation annulée.');
    }
}
