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
        Schema::create('confautoimport', function (Blueprint $table) {
            $table->id();
            $table->string('callsign', 200);
            $table->string('databasename', 255);
            $table->string('tablename', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confautoimport');
    }
};
