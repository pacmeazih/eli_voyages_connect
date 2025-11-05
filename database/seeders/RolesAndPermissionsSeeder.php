<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Basic roles
        $roles = ['SuperAdmin', 'Consultant', 'Agent', 'Client'];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // Example permissions (expand as needed)
        $perms = [
            'manage users',
            'manage clients',
            'manage packages',
            'view documents',
            'manage documents'
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // Assign all permissions to SuperAdmin
        $super = Role::where('name', 'SuperAdmin')->first();
        if ($super) {
            $super->syncPermissions(Permission::all());
        }

        // If test user exists, make it SuperAdmin
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        if ($user && $super) {
            $user->assignRole($super->name);
        }
    }
}
