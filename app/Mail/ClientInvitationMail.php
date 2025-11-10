<?php

namespace App\Mail;

use App\Models\ClientInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(ClientInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation à créer votre compte ELI VOYAGES',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.client-invitation',
            with: [
                'nom' => $this->invitation->nom,
                'prenom' => $this->invitation->prenom,
                'clientCode' => $this->invitation->client_code,
                'acceptUrl' => route('invitations.show', $this->invitation->invitation_token),
                'expiresAt' => $this->invitation->expires_at->format('d/m/Y'),
            ],
        );
    }
}
