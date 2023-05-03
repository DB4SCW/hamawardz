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
        Schema::create('hamevents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('creator_id')->index();
            $table->string('slug', 200)->unique();
            $table->string('title', 200)->unique();
            $table->dateTimeTz('start');
            $table->dateTimeTz('end');
            $table->boolean('homepage_default')->default(false);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hamevents');
    }
};
