<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gamertag')->unique();
            $table->text('bio')->nullable();
            $table->string('platform')->nullable(); // PC / PS / Xbox / Switch
            $table->string('region')->nullable();   // EU / NA / Asia ...
            $table->string('playstyle')->nullable(); // Casual / Competitive
            $table->json('games')->nullable();       // ["Valorant","CS2"]
            $table->string('avatar_color')->default('#7c3aed');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('profiles'); }
};
