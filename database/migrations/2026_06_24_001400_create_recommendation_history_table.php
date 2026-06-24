<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mood_log_id')->nullable()->constrained('mood_logs')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->unsignedInteger('score')->default(0);
            $table->timestamp('shown_at')->useCurrent();
            $table->timestamp('acted_at')->nullable();
            $table->string('action_taken', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'shown_at']);
            $table->index('routine_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_history');
    }
};
