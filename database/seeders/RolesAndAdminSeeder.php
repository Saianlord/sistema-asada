<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'junta',
            'administration',
            'secretariat',
            'operations',
            'fiscal',
        ];

        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        $adminUser = User::create([
            'name' => 'Master Admin',
            'email' => 'admin@asada.org',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $adminUser->assignRole('admin');
    }
}
