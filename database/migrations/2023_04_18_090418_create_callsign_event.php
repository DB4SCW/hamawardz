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
        Schema::create('callsign_hamevent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('callsign_id');
            $table->unsignedBigInteger('hamevent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('callsign_hamevent');
    }
};
