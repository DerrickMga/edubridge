<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@edubridge.co.zw'],
            [
                'name'     => 'EduBridge Admin',
                'password' => Hash::make('changeme-immediately'),
                'role'     => 'admin',
                'country'  => 'ZW',
            ]
        );
        $admin->assignRole('admin');
    }
}
