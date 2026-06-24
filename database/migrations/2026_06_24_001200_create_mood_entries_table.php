<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mood_category', 32)->nullable();
            $table->tinyInteger('mood_value');
            $table->text('journal_note')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'logged_at']);
            $table->index('mood_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_entries');
    }
};
