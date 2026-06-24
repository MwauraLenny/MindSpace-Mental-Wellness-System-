<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reactable');
            $table->string('reaction', 32);
            $table->timestamps();

            $table->index(['user_id', 'reaction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
