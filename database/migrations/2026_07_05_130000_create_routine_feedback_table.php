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
        Schema::create('routine_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_id')->constrained()->cascadeOnDelete();
            $table->boolean('helped')->nullable();
            $table->unsignedTinyInteger('before_mood_value')->nullable();
            $table->unsignedTinyInteger('after_mood_value')->nullable();
            $table->integer('mood_delta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'routine_id']);
            $table->index(['helped', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routine_feedback');
    }
};
