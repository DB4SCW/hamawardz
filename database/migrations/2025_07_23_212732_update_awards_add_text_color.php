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
        Schema::table('awards', function (Blueprint $table) {
            $table->string('callsign_text_color', 200)->default('black');
            $table->string('chosen_name_text_color', 200)->default('black');
            $table->string('datetime_text_color', 200)->default('black');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn('callsign_text_color');
            $table->dropColumn('chosen_name_text_color');
            $table->dropColumn('datetime_text_color');
        });
    }
};
