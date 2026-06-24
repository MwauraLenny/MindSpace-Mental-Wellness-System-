<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->foreignId('mood_log_id')
                ->nullable()
                ->after('mood_id')
                ->constrained('mood_logs')
                ->nullOnDelete();

            $table->index(['user_id', 'mood_log_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'mood_log_id']);
            $table->dropConstrainedForeignId('mood_log_id');
        });
    }
};
