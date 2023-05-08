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
            $table->string('table_id', 52)->default('id');
            $table->string('operator', 52)->nullable();
            $table->string('qsodate', 52);
            $table->string('qsotime', 52);
            $table->string('qsopartner_callsign', 52);
            $table->string('frequency', 52);
            $table->string('band', 52)->nullable();
            $table->string('mode', 52);
            $table->string('rst_s', 52);
            $table->string('rst_r', 52);
            $table->string('dxcc', 52)->nullable();
            
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
