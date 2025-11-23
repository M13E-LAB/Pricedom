<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->string('emoji', 10); // Émoji (ex: 😍, 👍, 😂)
            $table->timestamps();
            
            // Un utilisateur ne peut réagir qu'une fois avec le même émoji sur un post
            $table->unique(['user_id', 'post_id', 'emoji']);
            $table->index(['post_id', 'emoji']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
}; 