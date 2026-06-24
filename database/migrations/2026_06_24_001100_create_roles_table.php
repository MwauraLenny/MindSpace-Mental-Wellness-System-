<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')
                ->nullable()
                ->after('role')
                ->constrained('roles')
                ->nullOnDelete();
        });

        DB::table('roles')->insert([
            [
                'name' => 'User',
                'slug' => 'user',
                'permissions' => json_encode(['community', 'journals', 'mood_tracking']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'permissions' => json_encode(['all']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $roleMap = DB::table('roles')->pluck('id', 'slug');

        DB::table('users')
            ->where('role', 'admin')
            ->update(['role_id' => $roleMap['admin'] ?? null]);

        DB::table('users')
            ->where('role', '!=', 'admin')
            ->orWhereNull('role')
            ->update(['role_id' => $roleMap['user'] ?? null]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
