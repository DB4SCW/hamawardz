<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('callsignapierrorlogs', function (Blueprint $table) {
            if (!Schema::hasColumn('callsignapierrorlogs', 'logmessage')) {
                $table->string('logmessage', 255)->nullable();
            }
        });

        if (Schema::hasColumn('callsignapierrorlogs', 'message') && Schema::hasColumn('callsignapierrorlogs', 'logmessage')) {
            // Copy data from message to logmessage
            DB::table('callsignapierrorlogs')->update([
                'logmessage' => DB::raw('message')
            ]);
        }

        Schema::table('callsignapierrorlogs', function (Blueprint $table) {
            if (Schema::hasColumn('callsignapierrorlogs', 'message')) {
                $table->dropColumn('message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('callsignapierrorlogs', function (Blueprint $table) {
            if (!Schema::hasColumn('callsignapierrorlogs', 'message')) {
                $table->string('message')->nullable();
            }
        });

        if (Schema::hasColumn('callsignapierrorlogs', 'logmessage') && Schema::hasColumn('callsignapierrorlogs', 'message')) {
            // Restore data from logmessage to message
            DB::table('callsignapierrorlogs')->update([
                'message' => DB::raw('logmessage')
            ]);
        }

        Schema::table('callsignapierrorlogs', function (Blueprint $table) {
            if (Schema::hasColumn('callsignapierrorlogs', 'logmessage')) {
                $table->dropColumn('logmessage');
            }
        });
    }
};
