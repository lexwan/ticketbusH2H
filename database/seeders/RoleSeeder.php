<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // permissions
        $permissions = [
            // Product permissions
            'view products',
            'create products',
            'edit products',
            'delete products',
            
            // Category permissions
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Order permissions
            'view orders',
            'create orders',
            'edit orders',
            'cancel orders',
            'manage all orders', // Admin only - view all users' orders
            
            // Cart permissions
            'manage cart',
            
            // Payment permissions
            'create payments',
            'view payments',
            'confirm payments',
            
            // User management
            'manage users',
            'view users',
            'edit users',
            'delete users',
            
            // Profile permissions
            'view profile',
            'edit profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // roles and assign permissions for both guards
        $adminRoleWeb = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRoleWeb->givePermissionTo(Permission::where('guard_name', 'web')->get());
        
        $adminRoleApi = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRoleApi->givePermissionTo(Permission::where('guard_name', 'api')->get());

        $userRoleWeb = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRoleWeb->givePermissionTo([
            'view products',
            'view categories', 
            'view orders',
            'create orders',
            'edit orders',
            'cancel orders',
            'manage cart',
            'create payments',
            'view payments',
            'confirm payments',
            'view profile',
            'edit profile',
        ]);
        
        $userRoleApi = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        $userPermissions = [
            'view products', 'view categories', 'view orders', 'create orders', 
            'edit orders', 'cancel orders', 'manage cart', 'create payments',
            'view payments', 'confirm payments', 'view profile', 'edit profile'
        ];
        
        foreach ($userPermissions as $permission) {
            $perm = Permission::where('name', $permission)->where('guard_name', 'api')->first();
            if ($perm) $userRoleApi->givePermissionTo($perm);
        }
    
        $this->command->info('Roles and permissions created successfully!');
    }
}
