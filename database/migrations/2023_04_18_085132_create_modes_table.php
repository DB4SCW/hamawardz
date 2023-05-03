<?php

use App\Models\Mode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('mode', 20);
            $table->string('submode', 20);
            $table->string('mainmode', 20)->default('DIGITAL');
        });

        //Import modes from adif specifications
        Mode::create(['submode' => 'AM', 'mode' => 'AM']);
        Mode::create(['submode' => 'ARDOP', 'mode' => 'ARDOP']);
        Mode::create(['submode' => 'ATV', 'mode' => 'ATV']);
        Mode::create(['submode' => 'C4FM', 'mode' => 'C4FM']);
        Mode::create(['submode' => 'CHIP', 'mode' => 'CHIP']);
        Mode::create(['submode' => 'CLO', 'mode' => 'CLO']);
        Mode::create(['submode' => 'CONTESTI', 'mode' => 'CONTESTI']);
        Mode::create(['submode' => 'CW', 'mode' => 'CW']);
        Mode::create(['submode' => 'DIGITALVOICE', 'mode' => 'DIGITALVOICE']);
        Mode::create(['submode' => 'DOMINO', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DSTAR', 'mode' => 'DSTAR']);
        Mode::create(['submode' => 'FAX', 'mode' => 'FAX']);
        Mode::create(['submode' => 'FM', 'mode' => 'FM']);
        Mode::create(['submode' => 'FSK441', 'mode' => 'FSK441']);
        Mode::create(['submode' => 'FT8', 'mode' => 'FT8']);
        Mode::create(['submode' => 'HELL', 'mode' => 'HELL']);
        Mode::create(['submode' => 'ISCAT', 'mode' => 'ISCAT']);
        Mode::create(['submode' => 'JT4', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT6M', 'mode' => 'JT6M']);
        Mode::create(['submode' => 'JT9', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT44', 'mode' => 'JT44']);
        Mode::create(['submode' => 'JT65', 'mode' => 'JT65']);
        Mode::create(['submode' => 'MFSK', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MSK144', 'mode' => 'MSK144']);
        Mode::create(['submode' => 'MT63', 'mode' => 'MT63']);
        Mode::create(['submode' => 'OLIVIA', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OPERA', 'mode' => 'OPERA']);
        Mode::create(['submode' => 'PAC', 'mode' => 'PAC']);
        Mode::create(['submode' => 'PAX', 'mode' => 'PAX']);
        Mode::create(['submode' => 'PKT', 'mode' => 'PKT']);
        Mode::create(['submode' => 'PSK', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK2K', 'mode' => 'PSK2K']);
        Mode::create(['submode' => 'Q15', 'mode' => 'Q15']);
        Mode::create(['submode' => 'QRA64', 'mode' => 'QRA64']);
        Mode::create(['submode' => 'ROS', 'mode' => 'ROS']);
        Mode::create(['submode' => 'RTTY', 'mode' => 'RTTY']);
        Mode::create(['submode' => 'RTTYM', 'mode' => 'RTTYM']);
        Mode::create(['submode' => 'SSB', 'mode' => 'SSB']);
        Mode::create(['submode' => 'SSTV', 'mode' => 'SSTV']);
        Mode::create(['submode' => 'T10', 'mode' => 'T10']);
        Mode::create(['submode' => 'THOR', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THRB', 'mode' => 'THRB']);
        Mode::create(['submode' => 'TOR', 'mode' => 'TOR']);
        Mode::create(['submode' => 'V4', 'mode' => 'V4']);
        Mode::create(['submode' => 'VOI', 'mode' => 'VOI']);
        Mode::create(['submode' => 'WINMOR', 'mode' => 'WINMOR']);
        Mode::create(['submode' => 'WSPR', 'mode' => 'WSPR']);
        Mode::create(['submode' => '8PSK125', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK125F', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK125FL', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK250', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK250F', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK250FL', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK500', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK500F', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK1000', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK1000F', 'mode' => 'PSK']);
        Mode::create(['submode' => '8PSK1200F', 'mode' => 'PSK']);
        Mode::create(['submode' => 'AMTORFEC', 'mode' => 'TOR']);
        Mode::create(['submode' => 'ASCI', 'mode' => 'RTTY']);
        Mode::create(['submode' => 'CHIP64', 'mode' => 'CHIP']);
        Mode::create(['submode' => 'CHIP128', 'mode' => 'CHIP']);
        Mode::create(['submode' => 'DOM-M', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM4', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM5', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM8', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM11', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM16', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM22', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM44', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOM88', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOMINOEX', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'DOMINOF', 'mode' => 'DOMINO']);
        Mode::create(['submode' => 'FMHELL', 'mode' => 'HELL']);
        Mode::create(['submode' => 'FSK31', 'mode' => 'PSK']);
        Mode::create(['submode' => 'FSKHELL', 'mode' => 'HELL']);
        Mode::create(['submode' => 'FSQCALL', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'FST4', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'FST4W', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'FT4', 'mode' => 'FT4']); //FT4 as seperate mode, not submode of MFSK
        Mode::create(['submode' => 'GTOR', 'mode' => 'TOR']);
        Mode::create(['submode' => 'HELL80', 'mode' => 'HELL']);
        Mode::create(['submode' => 'HELLX5', 'mode' => 'HELL']);
        Mode::create(['submode' => 'HELLX9', 'mode' => 'HELL']);
        Mode::create(['submode' => 'HFSK', 'mode' => 'HELL']);
        Mode::create(['submode' => 'ISCAT-A', 'mode' => 'ISCAT']);
        Mode::create(['submode' => 'ISCAT-B', 'mode' => 'ISCAT']);
        Mode::create(['submode' => 'JS8', 'mode' => 'JS8']); //JS8 as seperate mode, not submode of MFSK
        Mode::create(['submode' => 'JT4A', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT4B', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT4C', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT4D', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT4E', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT4F', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT4G', 'mode' => 'JT4']);
        Mode::create(['submode' => 'JT9-1', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9-2', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9-5', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9-10', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9-30', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9A', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9B', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9C', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9D', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9E', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9E FAST', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9F', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9F FAST', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9G', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9G FAST', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9H', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT9H FAST', 'mode' => 'JT9']);
        Mode::create(['submode' => 'JT65A', 'mode' => 'JT65']);
        Mode::create(['submode' => 'JT65B', 'mode' => 'JT65']);
        Mode::create(['submode' => 'JT65B2', 'mode' => 'JT65']);
        Mode::create(['submode' => 'JT65C', 'mode' => 'JT65']);
        Mode::create(['submode' => 'JT65C2', 'mode' => 'JT65']);
        Mode::create(['submode' => 'JTMS', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'LSB', 'mode' => 'SSB']);
        Mode::create(['submode' => 'MFSK4', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK8', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK11', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK16', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK22', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK31', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK32', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK64', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK64L', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK128', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'MFSK128L', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'NAVTEX', 'mode' => 'TOR']);
        Mode::create(['submode' => 'OLIVIA 4/125', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OLIVIA 4/250', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OLIVIA 8/250', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OLIVIA 8/500', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OLIVIA 16/500', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OLIVIA 16/1000', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OLIVIA 32/1000', 'mode' => 'OLIVIA']);
        Mode::create(['submode' => 'OPERA-BEACON', 'mode' => 'OPERA']);
        Mode::create(['submode' => 'OPERA-QSO', 'mode' => 'OPERA']);
        Mode::create(['submode' => 'PAC2', 'mode' => 'PAC']);
        Mode::create(['submode' => 'PAC3', 'mode' => 'PAC']);
        Mode::create(['submode' => 'PAC4', 'mode' => 'PAC']);
        Mode::create(['submode' => 'PAX2', 'mode' => 'PAX']);
        Mode::create(['submode' => 'PCW', 'mode' => 'CW']);
        Mode::create(['submode' => 'PSK10', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK31', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK63', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK63F', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK63RC10', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK63RC20', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK63RC32', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK63RC4', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK63RC5', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK125', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK125RC10', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK125RC12', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK125RC16', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK125RC4', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK125RC5', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK250', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK250RC2', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK250RC3', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK250RC5', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK250RC6', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK250RC7', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK500', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK500RC2', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK500RC3', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK500RC4', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK800RC2', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK1000', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSK1000RC2', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSKAM10', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSKAM31', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSKAM50', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSKFEC31', 'mode' => 'PSK']);
        Mode::create(['submode' => 'PSKHELL', 'mode' => 'HELL']);
        Mode::create(['submode' => 'QPSK31', 'mode' => 'PSK']);
        Mode::create(['submode' => 'Q65', 'mode' => 'MFSK']);
        Mode::create(['submode' => 'QPSK63', 'mode' => 'PSK']);
        Mode::create(['submode' => 'QPSK125', 'mode' => 'PSK']);
        Mode::create(['submode' => 'QPSK250', 'mode' => 'PSK']);
        Mode::create(['submode' => 'QPSK500', 'mode' => 'PSK']);
        Mode::create(['submode' => 'QRA64A', 'mode' => 'QRA64']);
        Mode::create(['submode' => 'QRA64B', 'mode' => 'QRA64']);
        Mode::create(['submode' => 'QRA64C', 'mode' => 'QRA64']);
        Mode::create(['submode' => 'QRA64D', 'mode' => 'QRA64']);
        Mode::create(['submode' => 'QRA64E', 'mode' => 'QRA64']);
        Mode::create(['submode' => 'ROS-EME', 'mode' => 'ROS']);
        Mode::create(['submode' => 'ROS-HF', 'mode' => 'ROS']);
        Mode::create(['submode' => 'ROS-MF', 'mode' => 'ROS']);
        Mode::create(['submode' => 'SIM31', 'mode' => 'PSK']);
        Mode::create(['submode' => 'SITORB', 'mode' => 'TOR']);
        Mode::create(['submode' => 'SLOWHELL', 'mode' => 'HELL']);
        Mode::create(['submode' => 'THOR-M', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR4', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR5', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR8', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR11', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR16', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR22', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR25X4', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR50X1', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR50X2', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THOR100', 'mode' => 'THOR']);
        Mode::create(['submode' => 'THRBX', 'mode' => 'THRB']);
        Mode::create(['submode' => 'THRBX1', 'mode' => 'THRB']);
        Mode::create(['submode' => 'THRBX2', 'mode' => 'THRB']);
        Mode::create(['submode' => 'THRBX4', 'mode' => 'THRB']);
        Mode::create(['submode' => 'THROB1', 'mode' => 'THRB']);
        Mode::create(['submode' => 'THROB2', 'mode' => 'THRB']);
        Mode::create(['submode' => 'THROB4', 'mode' => 'THRB']);
        Mode::create(['submode' => 'USB', 'mode' => 'SSB']);
        Mode::create(['submode' => 'YSF', 'mode' => 'DIGITALVOICE']);
        Mode::create(['submode' => 'D-STAR', 'mode' => 'DIGITALVOICE']);
        Mode::create(['submode' => 'DMR', 'mode' => 'DIGITALVOICE']);

        //Mainmode classification
        DB::table('modes')->where('mode', 'SSB')->orWhere('mode', 'DIGITALVOICE')->orWhere('mode', 'AM')->orWhere('mode', 'FM')->update(['mainmode' => 'VOICE']);
        DB::table('modes')->where('mode', 'CW')->update(['mainmode' => 'CW']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modes');
    }
};
