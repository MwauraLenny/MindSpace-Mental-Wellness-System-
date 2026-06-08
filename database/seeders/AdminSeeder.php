<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@mindspace.com',
            'password' => Hash::make('Admin@2026'),
            'role' => 'admin',
            'anonymous_sharing' => false,
        ]);
    }
}
