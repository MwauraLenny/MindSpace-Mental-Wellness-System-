<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = Role::query()->where('slug', 'admin')->value('id');

        User::updateOrCreate(
            ['email' => 'admin@mindspace.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@2026'),
                'role' => 'admin',
                'role_id' => $adminRoleId,
                'anonymous_sharing' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
