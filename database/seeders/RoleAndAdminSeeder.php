<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\User;
use App\Models\Mitra;
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

        // Create demo mitra
        $demoMitra = Mitra::firstOrCreate(
            ['email' => 'mitra@example.com'],
            [
                'code' => 'MTRDEMO',
                'name' => 'PT Mitra Demo',
                'phone' => '08123456789',
                'status' => 'active',
                'balance' => 1000000
            ]
        );

        // Create mitra user
        $mitraUser = User::firstOrCreate(
            ['email' => 'mitra@example.com'],
            [
                'name' => 'Mitra Demo',
                'password' => Hash::make('password'),
                'mitra_id' => $demoMitra->id,
                'status' => 'active'
            ]
        );
        
        $mitraUser->assignRole('mitra');

        $this->command->info('\u2705 Roles created: admin, mitra');
        $this->command->info('\u2705 Admin user: admin@example.com / password');
        $this->command->info('\u2705 Mitra user: mitra@example.com / password');
    }
}
