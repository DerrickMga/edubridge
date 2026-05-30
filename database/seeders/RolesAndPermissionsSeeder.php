<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // courses
            'view courses', 'create courses', 'edit courses', 'delete courses',
            // lessons
            'view lessons', 'create lessons', 'edit lessons', 'delete lessons',
            // live sessions
            'view live sessions', 'create live sessions',
            // users
            'view users', 'manage users',
            // payments
            'view payments', 'manage payments',
            // companion
            'use companion',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $student = Role::firstOrCreate(['name' => 'student']);
        $student->syncPermissions(['view courses', 'view lessons', 'view live sessions', 'use companion']);

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->syncPermissions([
            'view courses', 'create courses', 'edit courses',
            'view lessons', 'create lessons', 'edit lessons',
            'view live sessions', 'create live sessions',
            'view payments',
        ]);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());
    }
}
