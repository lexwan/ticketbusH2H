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

        // Create permissions
        $permissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Create roles and assign permissions for both guards
        $adminRoleWeb = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRoleWeb->givePermissionTo(Permission::where('guard_name', 'web')->get());
        
        $adminRoleApi = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRoleApi->givePermissionTo(Permission::where('guard_name', 'api')->get());

        $userRoleWeb = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRoleWeb->givePermissionTo(['view products', 'view categories']);
        
        $userRoleApi = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        $userRoleApi->givePermissionTo([
            Permission::where('name', 'view products')->where('guard_name', 'api')->first(),
            Permission::where('name', 'view categories')->where('guard_name', 'api')->first(),
        ]);
    
        $this->command->info('Roles and permissions created successfully!');
    }
}
