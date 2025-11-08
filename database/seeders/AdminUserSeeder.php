<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SuperAdmin role if it doesn't exist
        $superAdminRole = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $agentRole = Role::firstOrCreate(['name' => 'Agent']);
        $clientRole = Role::firstOrCreate(['name' => 'Client']);

        // Create default SuperAdmin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@eli-voyages.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (!$superAdmin->hasRole('SuperAdmin')) {
            $superAdmin->assignRole('SuperAdmin');
        }

        $this->command->info('✅ SuperAdmin créé avec succès !');
        $this->command->info('📧 Email: admin@eli-voyages.com');
        $this->command->info('🔑 Mot de passe: password');
        $this->command->info('');

        // Create default Admin user
        $admin = User::firstOrCreate(
            ['email' => 'koffi@eli-voyages.com'],
            [
                'name' => 'AZIH Koffi Pacôme',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        $this->command->info('✅ Admin créé avec succès !');
        $this->command->info('📧 Email: koffi@eli-voyages.com');
        $this->command->info('🔑 Mot de passe: password123');
        $this->command->info('');

        // Create default Agent user
        $agent = User::firstOrCreate(
            ['email' => 'agent@eli-voyages.com'],
            [
                'name' => 'Agent Test',
                'password' => Hash::make('agent123'),
                'email_verified_at' => now(),
            ]
        );

        if (!$agent->hasRole('Agent')) {
            $agent->assignRole('Agent');
        }

        $this->command->info('✅ Agent créé avec succès !');
        $this->command->info('📧 Email: agent@eli-voyages.com');
        $this->command->info('🔑 Mot de passe: agent123');
        $this->command->info('');

        // Create default Client user
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client Test',
                'password' => Hash::make('client123'),
                'email_verified_at' => now(),
            ]
        );

        if (!$client->hasRole('Client')) {
            $client->assignRole('Client');
        }

        $this->command->info('✅ Client créé avec succès !');
        $this->command->info('📧 Email: client@example.com');
        $this->command->info('🔑 Mot de passe: client123');
    }
}
