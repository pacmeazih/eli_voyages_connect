<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders
                            {--hours=24 : Send reminders for appointments within X hours}';

    protected $description = 'Send reminders for upcoming appointments';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $this->info("📅 Envoi de rappels pour les rendez-vous dans les {$hours} prochaines heures...");

        // Find appointments scheduled within the next X hours
        $upcomingAppointments = Appointment::with(['client', 'agent'])
            ->whereBetween('scheduled_at', [now(), now()->addHours($hours)])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereNull('reminder_sent_at')
            ->get();

        if ($upcomingAppointments->isEmpty()) {
            $this->info('✅ Aucun rendez-vous nécessitant un rappel.');
            return Command::SUCCESS;
        }

        $this->warn("📋 {$upcomingAppointments->count()} rendez-vous trouvé(s)");
        $count = 0;

        foreach ($upcomingAppointments as $appointment) {
            try {
                // Send reminder to client
                $appointment->client->notify(new AppointmentReminderNotification($appointment));
                
                // Send reminder to agent (optional)
                if ($appointment->agent) {
                    $appointment->agent->notify(new AppointmentReminderNotification($appointment));
                }
                
                // Mark reminder as sent
                $appointment->sendReminder();
                
                activity()
                    ->performedOn($appointment)
                    ->causedBy(null)
                    ->log('Rappel de rendez-vous envoyé automatiquement');
                
                $scheduledDate = $appointment->scheduled_at->format('d/m/Y H:i');
                $this->line("  ✅ Rappel envoyé pour RDV #{$appointment->id} ({$scheduledDate})");
                
                $count++;
            } catch (\Exception $e) {
                Log::error("Erreur rappel rendez-vous #{$appointment->id}: {$e->getMessage()}");
                $this->error("  ❌ Erreur pour le rendez-vous #{$appointment->id}");
            }
        }

        $this->newLine();
        $this->info("✨ {$count} rappel(s) envoyé(s) avec succès.");
        
        return Command::SUCCESS;
    }
}
