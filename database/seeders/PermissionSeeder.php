<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Lead permissions
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.delete',
            'leads.assign',
            'leads.export',
            'leads.import',

            // Task permissions
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
            'tasks.complete',

            // Pipeline permissions
            'pipelines.view',
            'pipelines.create',
            'pipelines.edit',
            'pipelines.delete',

            // User management permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'manage-users',

            // Settings permissions
            'settings.view',
            'settings.edit',
            'settings.integrations',

            // Dashboard permissions
            'dashboard.view',
            'dashboard.team-performance',

            // AI permissions
            'ai.view',
            'ai.generate',

            // Notifications permissions
            'notifications.view',
            'notifications.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'leads.export',
            'leads.import',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.complete',
            'pipelines.view',
            'pipelines.create',
            'pipelines.edit',
            'users.view',
            'users.create',
            'users.edit',
            'settings.view',
            'settings.edit',
            'dashboard.view',
            'dashboard.team-performance',
            'ai.view',
            'ai.generate',
            'notifications.view',
            'notifications.manage',
        ]);

        $salesRole = Role::create(['name' => 'sales']);
        $salesRole->givePermissionTo([
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'leads.export',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.complete',
            'pipelines.view',
            'dashboard.view',
            'ai.view',
            'notifications.view',
        ]);

        $marketingRole = Role::create(['name' => 'marketing']);
        $marketingRole->givePermissionTo([
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.export',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'pipelines.view',
            'dashboard.view',
            'ai.view',
            'notifications.view',
        ]);

        $leadManagerRole = Role::create(['name' => 'lead_manager']);
        $leadManagerRole->givePermissionTo([
            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.assign',
            'leads.export',
            'leads.import',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.complete',
            'pipelines.view',
            'pipelines.create',
            'pipelines.edit',
            'dashboard.view',
            'ai.view',
            'ai.generate',
            'notifications.view',
        ]);
    }
}
