<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed roles & permissions first
        if (class_exists(\Database\Seeders\RolesAndPermissionsSeeder::class)) {
            $this->call(\Database\Seeders\RolesAndPermissionsSeeder::class);
        }

        // Create or update a test user (idempotent seed)
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Ensure test user has SuperAdmin role if roles exist
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $role = \Spatie\Permission\Models\Role::firstWhere('name', 'SuperAdmin');
            if ($role) {
                $user->assignRole($role->name);
            }
        }

        // Seed clients and packages
        if (class_exists(\Database\Seeders\ClientSeeder::class)) {
            $this->call(\Database\Seeders\ClientSeeder::class);
        }

        if (class_exists(\Database\Seeders\PackageSeeder::class)) {
            $this->call(\Database\Seeders\PackageSeeder::class);
        }
    }
}
