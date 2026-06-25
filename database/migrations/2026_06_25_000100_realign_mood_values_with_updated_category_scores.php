<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("\n            UPDATE mood_logs\n            SET mood_value = CASE mood_category\n                WHEN 'happy' THEN 5\n                WHEN 'excited' THEN 5\n                WHEN 'relaxed' THEN 4\n                WHEN 'anxious' THEN 3\n                WHEN 'tired' THEN 3\n                WHEN 'angry' THEN 2\n                WHEN 'stressed' THEN 2\n                WHEN 'sad' THEN 1\n                ELSE mood_value\n            END\n            WHERE mood_category IS NOT NULL\n        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("\n            UPDATE mood_logs\n            SET mood_value = CASE mood_category\n                WHEN 'happy' THEN 5\n                WHEN 'excited' THEN 5\n                WHEN 'relaxed' THEN 4\n                WHEN 'anxious' THEN 2\n                WHEN 'tired' THEN 3\n                WHEN 'angry' THEN 2\n                WHEN 'stressed' THEN 2\n                WHEN 'sad' THEN 2\n                ELSE mood_value\n            END\n            WHERE mood_category IS NOT NULL\n        ");
    }
};
