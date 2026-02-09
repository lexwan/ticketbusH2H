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

        // === CREATE MITRA USER ===
        $mitra = User::firstOrCreate(
            ['email' => 'mitra@example.com'],
            [
                'name' => 'Mitra Demo',
                'password' => Hash::make('password'),
                'status' => 'active',
                // 'partner_id' => 1, // aktifkan jika tabel partners sudah ada
            ]
        );

        if (! $mitra->hasRole('mitra')) {
            $mitra->assignRole($mitraRole);
        }

        // === INFO CLI ===
        $this->command->info('Roles created: admin, mitra');
        $this->command->info('Admin user  : admin@example.com / password');
        $this->command->info('Mitra user  : mitra@example.com / password');
    }
}
