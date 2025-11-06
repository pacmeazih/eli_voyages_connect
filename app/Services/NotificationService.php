<?php

namespace App\Services;

use App\Models\User;
use App\Models\Dossier;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    protected bool $whatsappEnabled;
    protected ?string $whatsappToken;
    protected ?string $whatsappPhoneId;

    public function __construct()
    {
        $this->whatsappEnabled = !empty(config('services.whatsapp.token'));
        $this->whatsappToken = config('services.whatsapp.token');
        $this->whatsappPhoneId = config('services.whatsapp.phone_number_id');
    }

    /**
     * Send notification about dossier creation
     */
    public function notifyDossierCreated(Dossier $dossier, User $client): void
    {
        $data = [
            'dossier_reference' => $dossier->reference,
            'dossier_title' => $dossier->title,
            'client_name' => $client->name,
        ];

        // Email notification
        $this->sendEmail(
            $client->email,
            'Nouveau dossier créé',
            'emails.dossier-created',
            $data
        );

        // WhatsApp notification (if enabled)
        if ($this->whatsappEnabled && $client->phone) {
            $this->sendWhatsApp(
                $client->phone,
                "Bonjour {$client->name},\n\nVotre dossier {$dossier->reference} a été créé avec succès.\nTitre: {$dossier->title}\n\nÉquipe ELI Voyages"
            );
        }
    }

    /**
     * Send notification about document upload
     */
    public function notifyDocumentUploaded(Dossier $dossier, string $documentName, User $recipient): void
    {
        $data = [
            'dossier_reference' => $dossier->reference,
            'document_name' => $documentName,
            'recipient_name' => $recipient->name,
        ];

        $this->sendEmail(
            $recipient->email,
            'Nouveau document ajouté',
            'emails.document-uploaded',
            $data
        );

        if ($this->whatsappEnabled && $recipient->phone) {
            $this->sendWhatsApp(
                $recipient->phone,
                "Bonjour,\n\nUn nouveau document ({$documentName}) a été ajouté à votre dossier {$dossier->reference}."
            );
        }
    }

    /**
     * Send notification about contract signature request
     */
    public function notifySignatureRequest(Dossier $dossier, string $signatureUrl, User $signer): void
    {
        $data = [
            'dossier_reference' => $dossier->reference,
            'signature_url' => $signatureUrl,
            'signer_name' => $signer->name,
        ];

        $this->sendEmail(
            $signer->email,
            'Signature de contrat requise',
            'emails.signature-request',
            $data
        );

        if ($this->whatsappEnabled && $signer->phone) {
            $this->sendWhatsApp(
                $signer->phone,
                "Bonjour {$signer->name},\n\nVeuillez signer votre contrat pour le dossier {$dossier->reference}.\nLien: {$signatureUrl}"
            );
        }
    }

    /**
     * Send notification about contract completion
     */
    public function notifyContractSigned(Dossier $dossier, User $recipient): void
    {
        $data = [
            'dossier_reference' => $dossier->reference,
            'recipient_name' => $recipient->name,
        ];

        $this->sendEmail(
            $recipient->email,
            'Contrat signé avec succès',
            'emails.contract-signed',
            $data
        );

        if ($this->whatsappEnabled && $recipient->phone) {
            $this->sendWhatsApp(
                $recipient->phone,
                "Bonjour,\n\nLe contrat pour votre dossier {$dossier->reference} a été signé avec succès."
            );
        }
    }

    /**
     * Send notification about dossier status change
     */
    public function notifyStatusChange(Dossier $dossier, string $oldStatus, string $newStatus, User $recipient): void
    {
        $data = [
            'dossier_reference' => $dossier->reference,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'recipient_name' => $recipient->name,
        ];

        $this->sendEmail(
            $recipient->email,
            'Mise à jour du statut de votre dossier',
            'emails.status-change',
            $data
        );

        if ($this->whatsappEnabled && $recipient->phone) {
            $this->sendWhatsApp(
                $recipient->phone,
                "Bonjour,\n\nLe statut de votre dossier {$dossier->reference} a été mis à jour: {$newStatus}"
            );
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(string $to, string $subject, string $template, array $data): void
    {
        try {
            Mail::send($template, $data, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info('Email notification sent', ['to' => $to, 'subject' => $subject]);
        } catch (\Exception $e) {
            Log::error('Email notification failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send WhatsApp message via WhatsApp Business API
     */
    protected function sendWhatsApp(string $phone, string $message): void
    {
        if (!$this->whatsappEnabled) {
            Log::info('WhatsApp not configured, skipping notification');
            return;
        }

        try {
            $response = Http::withToken($this->whatsappToken)
                ->post("https://graph.facebook.com/v17.0/{$this->whatsappPhoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $this->formatPhoneNumber($phone),
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp notification sent', ['phone' => $phone]);
            } else {
                Log::error('WhatsApp notification failed', [
                    'phone' => $phone,
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp notification exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format phone number for WhatsApp (E.164 format)
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if missing (default to +1 for Canada)
        if (strlen($phone) === 10) {
            $phone = '1' . $phone;
        }
        
        return $phone;
    }

    /**
     * Send notification to multiple users
     */
    public function notifyMultiple(array $users, string $subject, string $message): void
    {
        foreach ($users as $user) {
            $this->sendEmail($user->email, $subject, 'emails.generic', [
                'message' => $message,
                'user_name' => $user->name,
            ]);

            if ($this->whatsappEnabled && $user->phone) {
                $this->sendWhatsApp($user->phone, $message);
            }
        }
    }
}
