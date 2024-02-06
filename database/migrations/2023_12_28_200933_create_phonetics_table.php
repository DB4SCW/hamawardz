<?php

use App\Models\Phonetic;
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
        Schema::create('phonetics', function (Blueprint $table) {
            $table->id();
            $table->string('letter', 1)->unique();
            $table->string('code',200)->unique();
        });

        Phonetic::create(['letter' => 'A', 'code' => 'Alpha']);
        Phonetic::create(['letter' => 'B', 'code' => 'Bravo']);
        Phonetic::create(['letter' => 'C', 'code' => 'Charlie']);
        Phonetic::create(['letter' => 'D', 'code' => 'Delta']);
        Phonetic::create(['letter' => 'E', 'code' => 'Echo']);
        Phonetic::create(['letter' => 'F', 'code' => 'Foxtrot']);
        Phonetic::create(['letter' => 'G', 'code' => 'Golf']);
        Phonetic::create(['letter' => 'H', 'code' => 'Hotel']);
        Phonetic::create(['letter' => 'I', 'code' => 'India']);
        Phonetic::create(['letter' => 'J', 'code' => 'Juliett']);
        Phonetic::create(['letter' => 'K', 'code' => 'Kilo']);
        Phonetic::create(['letter' => 'L', 'code' => 'Lima']);
        Phonetic::create(['letter' => 'M', 'code' => 'Mike']);
        Phonetic::create(['letter' => 'N', 'code' => 'November']);
        Phonetic::create(['letter' => 'O', 'code' => 'Oscar']);
        Phonetic::create(['letter' => 'P', 'code' => 'Papa']);
        Phonetic::create(['letter' => 'Q', 'code' => 'Quebec']);
        Phonetic::create(['letter' => 'R', 'code' => 'Romeo']);
        Phonetic::create(['letter' => 'S', 'code' => 'Sierra']);
        Phonetic::create(['letter' => 'T', 'code' => 'Tango']);
        Phonetic::create(['letter' => 'U', 'code' => 'Uniform']);
        Phonetic::create(['letter' => 'V', 'code' => 'Victor']);
        Phonetic::create(['letter' => 'W', 'code' => 'Hiskey']);
        Phonetic::create(['letter' => 'X', 'code' => 'Xray']);
        Phonetic::create(['letter' => 'Y', 'code' => 'Yankee']);
        Phonetic::create(['letter' => 'Z', 'code' => 'Zulu']);
        Phonetic::create(['letter' => '/', 'code' => 'Slash']);
        Phonetic::create(['letter' => '-', 'code' => 'Stroke']);
        Phonetic::create(['letter' => '1', 'code' => 'One']);
        Phonetic::create(['letter' => '2', 'code' => 'Two']);
        Phonetic::create(['letter' => '3', 'code' => 'Three']);
        Phonetic::create(['letter' => '4', 'code' => 'Four']);
        Phonetic::create(['letter' => '5', 'code' => 'Five']);
        Phonetic::create(['letter' => '6', 'code' => 'Six']);
        Phonetic::create(['letter' => '7', 'code' => 'Seven']);
        Phonetic::create(['letter' => '8', 'code' => 'Eight']);
        Phonetic::create(['letter' => '9', 'code' => 'Nine']);
        Phonetic::create(['letter' => '0', 'code' => 'Zero']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phonetics');
    }
};
