<?php

namespace App\Notifications;

use App\Models\Dossier;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $dossier;
    protected $contract;
    protected $locale;

    /**
     * Create a new notification instance.
     */
    public function __construct(Dossier $dossier, Document $contract, string $locale = 'fr')
    {
        $this->dossier = $dossier;
        $this->contract = $contract;
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
        $url = route('documents.show', $this->contract->id);
        $downloadUrl = route('documents.download', $this->contract->id);

        if ($this->locale === 'fr') {
            return (new MailMessage)
                ->subject('✍️ Votre contrat est prêt - ' . $this->dossier->reference)
                ->greeting('Bonjour ' . $notifiable->name . ',')
                ->line('Excellente nouvelle! Votre contrat est maintenant prêt.')
                ->line('**Référence du dossier:** ' . $this->dossier->reference)
                ->line('**Contrat:** ' . $this->contract->title)
                ->line('Veuillez le consulter et le signer pour continuer le traitement de votre dossier.')
                ->action('Voir le contrat', $url)
                ->line('Vous pouvez également le télécharger directement.')
                ->action('Télécharger le contrat', $downloadUrl)
                ->line('Si vous avez des questions, n\'hésitez pas à nous contacter.')
                ->salutation('Cordialement, L\'équipe ELI Voyages');
        } else {
            return (new MailMessage)
                ->subject('✍️ Your contract is ready - ' . $this->dossier->reference)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Great news! Your contract is now ready.')
                ->line('**Application reference:** ' . $this->dossier->reference)
                ->line('**Contract:** ' . $this->contract->title)
                ->line('Please review and sign it to continue processing your application.')
                ->action('View Contract', $url)
                ->line('You can also download it directly.')
                ->action('Download Contract', $downloadUrl)
                ->line('If you have any questions, please don\'t hesitate to contact us.')
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
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'message' => $this->locale === 'fr'
                ? 'Le contrat pour le dossier ' . $this->dossier->reference . ' est prêt'
                : 'The contract for application ' . $this->dossier->reference . ' is ready',
        ];
    }
}
