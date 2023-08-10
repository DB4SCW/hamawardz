<?php

use App\Models\Callsign;
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
            $table->dateTimeTz('last_upload')->nullable(true);
        });

        //fill historical data
        $callsigns = Callsign::all();
        foreach ($callsigns as $callsign) {
            $callsign->setlastupload();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('callsigns', function (Blueprint $table) {
            $table->dropColumn('last_upload');
        });
    }
};
