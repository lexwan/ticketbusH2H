<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles dengan Spatie
        $adminRole = SpatieRole::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'api']
        );
        
        $mitraRole = SpatieRole::firstOrCreate(
            ['name' => 'mitra', 'guard_name' => 'api']
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'status' => 'active'
            ]
        );
        
        $admin->assignRole('admin');

        $this->command->info('Roles created: admin, mitra');
        $this->command->info('Admin user: admin@example.com / password');
    }
}
