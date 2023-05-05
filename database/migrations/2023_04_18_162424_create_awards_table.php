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
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('creator_id')->index();
            $table->unsignedBigInteger('hamevent_id')->index();
            $table->string('slug', 200);
            $table->string('title', 200);
            $table->boolean('active')->default(true);
            $table->text('description');
            $table->unsignedInteger('ranking')->default(0);
            $table->string('background_image', 200)->default('Blank.jpg')->nullable(false);
            $table->integer('mode');
            $table->unsignedInteger('min_threshold')->nullable();
            $table->decimal('callsign_top_percent', 6,3)->default(30);
            $table->boolean('callsign_bold')->default(true);
            $table->unsignedInteger('callsign_font_size_px')->default(100);
            $table->decimal('chosen_name_top_percent', 6,3)->default(60);
            $table->boolean('chosen_name_bold')->default(false);
            $table->unsignedInteger('chosen_name_font_size_px')->default(50);
            $table->decimal('datetime_top_percent', 6,3)->default(87);
            $table->decimal('datetime_left_percent', 6,3)->default(3.5);
            $table->unsignedInteger('datetime_font_size_px')->default(20);
            $table->unsignedInteger('dxcc_id')->nullable();
            $table->string('dxcc_querystring')->nullable();

            $table->unique(['hamevent_id', 'slug']);
            $table->unique(['hamevent_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
