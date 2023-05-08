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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('upload_id')->nullable()->index();
            $table->unsignedBigInteger('callsign_id')->index();
            $table->string('operator', 20);
            $table->dateTimeTz('qso_datetime');
            $table->string('callsign', 20);
            $table->string('raw_callsign', 20)->nullable();
            $table->unsignedDecimal('freq', 10, 4);
            $table->unsignedBigInteger('band_id')->index();
            $table->unsignedBigInteger('mode_id')->index();
            $table->string('rst_s', 20);
            $table->string('rst_r', 20);
            $table->unsignedBigInteger('autoimport_id')->nullable()->index();
            $table->unsignedBigInteger('autoimport_foreign_id')->nullable();
            $table->unsignedBigInteger('dxcc_id')->default(0);

            $table->unique(['callsign_id', 'qso_datetime', 'callsign', 'band_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
