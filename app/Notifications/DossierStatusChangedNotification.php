<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DossierStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $dossier;
    protected $oldStatus;
    protected $newStatus;
    protected $locale;

    /**
     * Create a new notification instance.
     */
    public function __construct(Dossier $dossier, string $oldStatus, string $newStatus, string $locale = 'fr')
    {
        $this->dossier = $dossier;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $oldStatusLabel = $this->getStatusLabel($this->oldStatus);
        $newStatusLabel = $this->getStatusLabel($this->newStatus);

        if ($this->locale === 'fr') {
            $message = (new MailMessage)
                ->subject('🔄 Changement de statut - ' . $this->dossier->reference)
                ->greeting('Bonjour ' . $notifiable->name . ',')
                ->line('Le statut de votre dossier a été mis à jour.')
                ->line('**Référence:** ' . $this->dossier->reference)
                ->line('**Ancien statut:** ' . $oldStatusLabel)
                ->line('**Nouveau statut:** ' . $newStatusLabel);

            // Message spécifique selon le statut
            $message = $this->addStatusSpecificMessage($message);

            return $message
                ->action('Voir mon dossier', $url)
                ->salutation('Cordialement, L\'équipe ELI Voyages');
        } else {
            $message = (new MailMessage)
                ->subject('🔄 Status Update - ' . $this->dossier->reference)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('The status of your application has been updated.')
                ->line('**Reference:** ' . $this->dossier->reference)
                ->line('**Old status:** ' . $oldStatusLabel)
                ->line('**New status:** ' . $newStatusLabel);

            $message = $this->addStatusSpecificMessage($message);

            return $message
                ->action('View My Application', $url)
                ->salutation('Best regards, The ELI Voyages Team');
        }
    }

    /**
     * Add status-specific messages
     */
    private function addStatusSpecificMessage(MailMessage $message): MailMessage
    {
        if ($this->locale === 'fr') {
            switch ($this->newStatus) {
                case 'pending':
                    $message->line('✅ Votre dossier est en attente de vérification.');
                    $message->line('Notre équipe va examiner vos documents prochainement.');
                    break;
                case 'in_progress':
                    $message->line('⚙️ Votre dossier est maintenant en cours de traitement.');
                    $message->line('Nous travaillons activement sur votre demande.');
                    break;
                case 'approved':
                    $message->line('🎉 Félicitations! Votre dossier a été approuvé.');
                    $message->line('Vous recevrez prochainement les instructions pour la suite.');
                    break;
                case 'rejected':
                    $message->line('❌ Malheureusement, votre dossier a été rejeté.');
                    $message->line('Contactez-nous pour plus d\'informations.');
                    break;
                case 'completed':
                    $message->line('✅ Votre dossier est maintenant terminé.');
                    $message->line('Merci de votre confiance!');
                    break;
            }
        } else {
            switch ($this->newStatus) {
                case 'pending':
                    $message->line('✅ Your application is pending verification.');
                    $message->line('Our team will review your documents shortly.');
                    break;
                case 'in_progress':
                    $message->line('⚙️ Your application is now being processed.');
                    $message->line('We are actively working on your request.');
                    break;
                case 'approved':
                    $message->line('🎉 Congratulations! Your application has been approved.');
                    $message->line('You will soon receive instructions for next steps.');
                    break;
                case 'rejected':
                    $message->line('❌ Unfortunately, your application has been rejected.');
                    $message->line('Please contact us for more information.');
                    break;
                case 'completed':
                    $message->line('✅ Your application is now completed.');
                    $message->line('Thank you for your trust!');
                    break;
            }
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusEmojis = [
            'draft' => '📝',
            'pending' => '⏳',
            'in_progress' => '⚙️',
            'approved' => '✅',
            'rejected' => '❌',
            'completed' => '🎉',
        ];

        return [
            'title' => ($statusEmojis[$this->newStatus] ?? '📋') . ' ' . 
                ($this->locale === 'fr' ? 'Statut modifié' : 'Status Changed'),
            'message' => $this->locale === 'fr'
                ? 'Votre dossier ' . $this->dossier->reference . ' est maintenant: ' . $this->getStatusLabel($this->newStatus)
                : 'Your application ' . $this->dossier->reference . ' is now: ' . $this->getStatusLabel($this->newStatus),
            'action_url' => route('client.tracking.show', $this->dossier->id),
            'action_text' => $this->locale === 'fr' ? 'Voir le dossier' : 'View Application',
            'dossier_id' => $this->dossier->id,
            'dossier_reference' => $this->dossier->reference,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel($status): string
    {
        $labels = [
            'fr' => [
                'draft' => 'Brouillon',
                'pending' => 'En attente',
                'in_progress' => 'En cours',
                'approved' => 'Approuvé',
                'rejected' => 'Rejeté',
                'completed' => 'Terminé',
            ],
            'en' => [
                'draft' => 'Draft',
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'completed' => 'Completed',
            ],
        ];

        return $labels[$this->locale][$status] ?? $status;
    }
}
