<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $dossier;
    protected $documentNames;
    protected $locale;

    /**
     * Create a new notification instance.
     */
    public function __construct(Dossier $dossier, array $documentNames, string $locale = 'fr')
    {
        $this->dossier = $dossier;
        $this->documentNames = $documentNames;
        $this->locale = $locale;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('client.tracking.show', $this->dossier->id);

        if ($this->locale === 'fr') {
            $message = (new MailMessage)
                ->subject('📄 Documents requis - ' . $this->dossier->reference)
                ->greeting('Bonjour ' . $notifiable->name . ',')
                ->line('Des documents sont requis pour compléter votre dossier.')
                ->line('**Référence:** ' . $this->dossier->reference);

            if (count($this->documentNames) > 0) {
                $message->line('**Documents manquants:**');
                foreach ($this->documentNames as $docName) {
                    $message->line('• ' . $docName);
                }
            }

            return $message
                ->action('Télécharger les documents', $url)
                ->line('Veuillez télécharger ces documents dès que possible pour accélérer le traitement de votre dossier.')
                ->salutation('Cordialement, L\'équipe ELI Voyages');
        } else {
            $message = (new MailMessage)
                ->subject('📄 Documents Required - ' . $this->dossier->reference)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Documents are required to complete your application.')
                ->line('**Reference:** ' . $this->dossier->reference);

            if (count($this->documentNames) > 0) {
                $message->line('**Missing documents:**');
                foreach ($this->documentNames as $docName) {
                    $message->line('• ' . $docName);
                }
            }

            return $message
                ->action('Upload Documents', $url)
                ->line('Please upload these documents as soon as possible to expedite the processing of your application.')
                ->salutation('Best regards, The ELI Voyages Team');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'dossier_id' => $this->dossier->id,
            'dossier_reference' => $this->dossier->reference,
            'document_names' => $this->documentNames,
            'message' => $this->locale === 'fr'
                ? count($this->documentNames) . ' document(s) requis pour le dossier ' . $this->dossier->reference
                : count($this->documentNames) . ' document(s) required for application ' . $this->dossier->reference,
        ];
    }
}
