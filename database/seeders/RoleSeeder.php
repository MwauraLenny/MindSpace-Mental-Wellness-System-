<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'permissions' => ['community', 'journals', 'mood_tracking'],
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'permissions' => ['all'],
            ]
        );

        $roleMap = Role::query()->pluck('id', 'slug');

        DB::table('users')
            ->where('role', 'admin')
            ->update(['role_id' => $roleMap['admin'] ?? null]);

        DB::table('users')
            ->where(function ($query): void {
                $query->where('role', '!=', 'admin')
                    ->orWhereNull('role');
            })
            ->update(['role_id' => $roleMap['user'] ?? null]);
    }
}
