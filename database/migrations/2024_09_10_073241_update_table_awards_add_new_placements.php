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
            $table->boolean('callsign_centered_horizontal')->default(true);
            $table->decimal('callsign_left_percent', 6,3)->nullable();
            $table->boolean('chosen_name_centered_horizontal')->default(true);
            $table->decimal('chosen_name_left_percent', 6,3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn('callsign_centered_horizontal');
            $table->dropColumn('callsign_left_percent');
            $table->dropColumn('chosen_name_centered_horizontal');
            $table->dropColumn('chosen_name_left_percent');
        });
    }
};
