<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
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
        $scheduledAt = $this->appointment->scheduled_at->locale('fr');
        $hoursUntil = now()->diffInHours($this->appointment->scheduled_at);
        
        return (new MailMessage)
            ->subject("Rappel: Rendez-vous dans {$hoursUntil}h")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Rappel de votre rendez-vous prévu le {$scheduledAt->format('d/m/Y')} à {$scheduledAt->format('H:i')}.")
            ->line("**Type:** " . $this->getTypeLabel($this->appointment->type))
            ->line("**Durée:** {$this->appointment->duration_minutes} minutes")
            ->when($this->appointment->meeting_link, function ($mail) {
                return $mail->line("**Lien de réunion:** {$this->appointment->meeting_link}");
            })
            ->when($this->appointment->location, function ($mail) {
                return $mail->line("**Lieu:** {$this->appointment->location}");
            })
            ->when($this->appointment->notes, function ($mail) {
                return $mail->line("**Notes:** {$this->appointment->notes}");
            })
            ->action('Voir le rendez-vous', url('/appointments'))
            ->line('Merci de votre confiance !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'scheduled_at' => $this->appointment->scheduled_at,
            'type' => $this->appointment->type,
            'duration_minutes' => $this->appointment->duration_minutes,
            'agent_name' => $this->appointment->agent->name,
            'client_name' => $this->appointment->client->name,
            'message' => "Rendez-vous prévu le {$this->appointment->scheduled_at->format('d/m/Y à H:i')}"
        ];
    }

    private function getTypeLabel($type): string
    {
        return match($type) {
            'consultation' => 'Consultation',
            'document_review' => 'Revue de documents',
            'signing' => 'Signature',
            'follow_up' => 'Suivi',
            default => ucfirst($type)
        };
    }
}
