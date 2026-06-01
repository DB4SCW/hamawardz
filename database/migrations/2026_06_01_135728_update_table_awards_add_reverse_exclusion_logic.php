<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Award;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //create new columns
        Schema::table('awards', function (Blueprint $table) {
             $table->integer('specific_callsign_computation')->default(-1);
             $table->string('specific_callsigns', 255)->nullable(true);
        });

        //get all awards
        $awards = Award::all();

        //migrate data to new columns
        foreach ($awards as $award) {
            if($award->excluded_callsigns != null)
            {
                $award->specific_callsign_computation = 0;
                $award->specific_callsigns = $award->excluded_callsigns;
                $award->save();
            }
        }

        //drop old column
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn('excluded_callsigns');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        //resture old column
        Schema::table('awards', function (Blueprint $table) {
             $table->string('excluded_callsigns', 255)->nullable(true);
        });

        //get all awards
        $awards = Award::all();

        //migrate data back (as far as we can do, only able to reverse back to exclusion mode)
        foreach ($awards as $award) {
            if($award->specific_callsigns != null)
            {
                if($award->specific_callsign_computation == 0)
                {
                    $award->excluded_callsigns = $award->specific_callsigns;
                    $award->save();    
                }
            }
        }
    
        //drop new columns
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn('specific_callsign_computation');
            $table->dropColumn('specific_callsigns');
        });
    }
};
