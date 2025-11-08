<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@eli-voyages.com')->first();
        if (!$user) {
            $this->command->warn('Admin user not found, skipping NotificationSeeder');
            return;
        }

        // Create 5 generic database notifications
        for ($i = 1; $i <= 5; $i++) {
            $user->notifications()->create([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\GenericInfoNotification',
                'data' => [
                    'title' => "Info #$i",
                    'message' => "Ceci est une notification de démonstration numéro $i.",
                    'action_url' => url('/dossiers'),
                    'action_text' => 'Ouvrir',
                ],
            ]);
        }

        $this->command->info('Sample notifications created for admin@eli-voyages.com');
    }
}
