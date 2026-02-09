<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management (Admin only)
            'users.view', 'users.create', 'users.update', 'users.delete',
            
            // Role & Permission Management (Admin only)
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'permissions.view', 'permissions.assign',
            
            // Partner/Mitra Management (Admin only)
            'partners.view', 'partners.create', 'partners.update', 'partners.delete',
            'partners.approve', 'partners.reject', 'partners.fee',
            
            // Topup Management
            'topups.view', 'topups.create', 'topups.approve', 'topups.reject',
            
            // Transaction Management
            'transactions.view', 'transactions.create', 'transactions.search',
            'transactions.book', 'transactions.pay', 'transactions.issue', 'transactions.cancel',
            'transactions.seat-map',
            
            // Balance & Ledger
            'balance.view', 'balance.histories', 'fee-ledgers.view',
            
            // Reports (Admin only)
            'reports.transactions', 'reports.topups', 'reports.fees', 'reports.balances',
            
            // Dashboard
            'dashboard.admin', 'dashboard.partner',
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

        if ($adminRole) {
            // Admin gets all permissions
            $adminRole->syncPermissions([
                'users.view', 'users.create', 'users.update', 'users.delete',
                'roles.view', 'roles.create', 'roles.update', 'roles.delete',
                'permissions.view', 'permissions.assign',
                'partners.view', 'partners.create', 'partners.update', 'partners.delete',
                'partners.approve', 'partners.reject', 'partners.fee',
                'topups.view', 'topups.approve', 'topups.reject',
                'transactions.view', 'transactions.cancel',
                'balance.view', 'balance.histories', 'fee-ledgers.view',
                'reports.transactions', 'reports.topups', 'reports.fees', 'reports.balances',
                'dashboard.admin',
            ]);
        }

        if ($mitraRole) {
            // Mitra gets limited permissions
            $mitraRole->syncPermissions([
                'topups.view', 'topups.create',
                'transactions.view', 'transactions.create', 'transactions.search',
                'transactions.book', 'transactions.pay', 'transactions.issue', 'transactions.cancel',
                'transactions.seat-map',
                'balance.view', 'balance.histories', 'fee-ledgers.view',
                'dashboard.partner',
            ]);
        }

        $this->command->info('Permissions created and assigned successfully');
    }
}
