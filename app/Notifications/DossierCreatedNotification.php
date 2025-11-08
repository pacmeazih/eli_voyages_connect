<?php

namespace App\Notifications;

use App\Models\Dossier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DossierCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $dossier;
    protected $locale;

    /**
     * Create a new notification instance.
     */
    public function __construct(Dossier $dossier, string $locale = 'fr')
    {
        $this->dossier = $dossier;
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
            return (new MailMessage)
                ->subject('✅ Votre dossier a été créé - ' . $this->dossier->reference)
                ->greeting('Bonjour ' . $notifiable->name . ',')
                ->line('Votre dossier d\'immigration a été créé avec succès.')
                ->line('**Référence:** ' . $this->dossier->reference)
                ->line('**Titre:** ' . $this->dossier->title)
                ->line('**Statut:** ' . $this->getStatusLabel($this->dossier->status))
                ->action('Voir mon dossier', $url)
                ->line('Notre équipe va traiter votre demande dans les plus brefs délais.')
                ->line('Vous recevrez des notifications à chaque étape du processus.')
                ->salutation('Cordialement, L\'équipe ELI Voyages');
        } else {
            return (new MailMessage)
                ->subject('✅ Your application has been created - ' . $this->dossier->reference)
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your immigration application has been successfully created.')
                ->line('**Reference:** ' . $this->dossier->reference)
                ->line('**Title:** ' . $this->dossier->title)
                ->line('**Status:** ' . $this->getStatusLabel($this->dossier->status))
                ->action('View My Application', $url)
                ->line('Our team will process your request as soon as possible.')
                ->line('You will receive notifications at each step of the process.')
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
            'title' => $this->locale === 'fr' 
                ? '✅ Nouveau dossier créé'
                : '✅ New Application Created',
            'message' => $this->locale === 'fr' 
                ? 'Votre dossier ' . $this->dossier->reference . ' a été créé avec succès'
                : 'Your application ' . $this->dossier->reference . ' has been created successfully',
            'action_url' => route('client.tracking.show', $this->dossier->id),
            'action_text' => $this->locale === 'fr' ? 'Voir le dossier' : 'View Application',
            'dossier_id' => $this->dossier->id,
            'dossier_reference' => $this->dossier->reference,
            'dossier_title' => $this->dossier->title,
            'status' => $this->dossier->status,
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
