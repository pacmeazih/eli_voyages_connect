<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create comprehensive permissions
        $permissions = [
            // User management
            'manage users',
            'invite users',
            'view users',
            
            // Client management
            'create clients',
            'edit clients',
            'view clients',
            'delete clients',
            
            // Dossier management
            'create dossiers',
            'edit dossiers',
            'view dossiers',
            'delete dossiers',
            'validate dossiers',
            'approve dossiers',
            
            // Document management
            'upload documents',
            'view documents',
            'edit documents',
            'delete documents',
            'download documents',
            
            // Contract management
            'generate contracts',
            'send contracts',
            'view contracts',
            'sign contracts',
            
            // Package management
            'manage packages',
            
            // System administration
            'view audit logs',
            'export data',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // SuperAdmin - Full access
        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Consultant - Review and approval permissions
        $consultant = Role::firstOrCreate(['name' => 'Consultant']);
        $consultant->givePermissionTo([
            'view users',
            'view clients',
            'view dossiers',
            'validate dossiers',
            'approve dossiers',
            'view documents',
            'download documents',
            'view contracts',
            'view audit logs',
        ]);

        // Agent - Core operational access
        $agent = Role::firstOrCreate(['name' => 'Agent']);
        $agent->givePermissionTo([
            'invite users',
            'create clients',
            'edit clients',
            'view clients',
            'create dossiers',
            'edit dossiers',
            'view dossiers',
            'upload documents',
            'view documents',
            'edit documents',
            'download documents',
            'generate contracts',
            'send contracts',
            'view contracts',
            'manage packages',
        ]);

        // Client - Limited read and upload
        $client = Role::firstOrCreate(['name' => 'Client']);
        $client->givePermissionTo([
            'view dossiers', // Filtered to own
            'upload documents',
            'view documents', // Filtered to own
            'view contracts', // Filtered to own
            'sign contracts',
        ]);

        // Guarantor - Similar to client
        $guarantor = Role::firstOrCreate(['name' => 'Guarantor']);
        $guarantor->givePermissionTo([
            'view dossiers', // Filtered to related
            'view documents', // Filtered to related
            'view contracts', // Filtered to related
            'sign contracts',
        ]);

        // If test user exists, make it SuperAdmin
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        if ($user && $superAdmin) {
            $user->assignRole($superAdmin->name);
        }

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('✓ 5 roles created: SuperAdmin, Consultant, Agent, Client, Guarantor');
        $this->command->info('✓ ' . count($permissions) . ' permissions created');
    }
}
