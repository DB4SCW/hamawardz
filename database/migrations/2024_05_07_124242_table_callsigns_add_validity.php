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
        Schema::table('callsigns', function (Blueprint $table) {
            $table->dateTimeTz('valid_from')->nullable();
            $table->dateTimeTz('valid_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('callsigns', function (Blueprint $table) {
            $table->dropColumn('valid_from');
            $table->dropColumn('valid_to');
        });
    }
};
