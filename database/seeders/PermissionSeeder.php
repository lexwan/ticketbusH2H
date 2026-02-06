<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions
        $permissions = [
            // User Management
            'users.view', 'users.create', 'users.update', 'users.delete',
            
            // Mitra Management
            'mitra.view', 'mitra.create', 'mitra.update', 'mitra.delete',
            
            // Topup Management
            'topups.view', 'topups.create', 'topups.approve', 'topups.reject',
            
            // Transaction Management
            'transactions.view', 'transactions.create', 'transactions.update', 'transactions.cancel',
            
            // Reports
            'reports.view', 'reports.export',
            
            // Balance
            'balance.view', 'balance.update',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api'
            ]);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $mitraRole = Role::where('name', 'mitra')->where('guard_name', 'api')->first();

        // Admin gets all permissions
        $adminRole->syncPermissions($permissions);

        // Mitra gets limited permissions
        $mitraRole->syncPermissions([
            'transactions.view',
            'transactions.create',
            'balance.view',
            'topups.view',
            'topups.create',
        ]);

        $this->command->info('✅ Permissions created and assigned');
    }
}
