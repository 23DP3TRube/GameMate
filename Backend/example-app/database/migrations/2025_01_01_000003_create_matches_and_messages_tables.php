<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('matches')) {
            Schema::create('matches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_a_id');
                $table->unsignedBigInteger('user_b_id');
                $table->timestamps();
                $table->unique(['user_a_id', 'user_b_id']);
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('match_id');
                $table->unsignedBigInteger('sender_id');
                $table->text('body');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('matches');
    }
};
