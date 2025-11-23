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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('badge_type'); // 'contribution_count', 'streak', 'variety'
            $table->integer('badge_level'); // 1=10 contributions, 2=100, 3=1000, etc.
            $table->string('badge_name');
            $table->string('badge_emoji');
            $table->string('badge_color');
            $table->integer('contributions_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
