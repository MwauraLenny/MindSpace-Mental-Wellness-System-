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
        Schema::table('mood_logs', function (Blueprint $table) {
            $table->string('mood_category', 32)->nullable()->after('user_id');
            $table->index(['user_id', 'mood_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mood_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'mood_category']);
            $table->dropColumn('mood_category');
        });
    }
};
