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
        Schema::create('callsignapidetails', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('callsign_id');
            $table->unsignedBigInteger('context_userid');
            $table->string('type', 100);
            $table->string('url', 255);
            $table->string('payload', 255)->nullable();
            $table->string('goalpost', 255)->nullable();
            $table->dateTimeTz('last_run')->nullable();
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('callsignapidetails');
    }
};
