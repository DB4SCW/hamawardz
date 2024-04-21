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
        Schema::create('awardtimeframes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('award_id');
            $table->dateTimeTz('start');
            $table->dateTimeTz('end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awardtimeframes');
    }
};
