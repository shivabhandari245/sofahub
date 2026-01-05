<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage products',
            'manage orders',
            'view reports',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole  = Role::firstOrCreate(['name' => 'user']);

        // Assign permissions
        $adminRole->syncPermissions(Permission::all()); // admin gets all permissions
        $userRole->syncPermissions(['view dashboard']); // user only gets limited permission(s)

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // change this
                'email_verified_at' => now(),
                'approved' => true,
            ]
        );
        $admin->assignRole($adminRole);

        // Create normal user
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Normal User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'approved' => true,
            ]
        );
        $user->assignRole($userRole);

        $this->command->info('Admin and User roles seeded with permissions!');
    }
}
