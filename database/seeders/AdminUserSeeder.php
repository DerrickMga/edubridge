<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the Spatie 'admin' role exists
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => 'info@kmgvitallinks.co.uk'],
            [
                'name'              => 'KMG Admin',
                'password'          => Hash::make('Tayari2026!'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info("Admin user ready: {$admin->email}");
    }
}
