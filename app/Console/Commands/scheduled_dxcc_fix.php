<?php

namespace App\Console\Commands;

use App\Models\Awardlog;
use App\Models\Contact;
use App\Models\Dxcc;
use Illuminate\Console\Command;

class scheduled_dxcc_fix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduled_dxcc_fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes missing dxccs on contacts and awardlogs';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        //get missing DXCCs on contacts
        $todo = Contact::where('dxcc_id', 1)->get();

        //fix contacts
        foreach ($todo as $contact) {
            
            //get dxcc from API
            $dxcc = $this->getdxcc($contact->raw_callsign);
            
            //write data to contact an dsave
            $contact->dxcc_id = $dxcc->id;
            $contact->save();

        }

        //get missing DXCCs on Awardlogs
        $todo = Awardlog::where('dxcc_id', null)->get();

        //fix Awardlogs
        foreach ($todo as $awardlog) {

            //get dxcc from API
            $dxcc = $this->getdxcc($awardlog->callsign);
            
            //write data to contact an dsave
            $awardlog->dxcc_id = $dxcc->id;
            $awardlog->save();
        }

    }

    function getdxcc(string $callsign) : Dxcc {
        //load info from API
        $dxccinfo = file_get_contents("https://www.hamqth.com/dxcc.php?callsign=" . urlencode($callsign));
        $xmlObject = simplexml_load_string($dxccinfo);
        $adif = (integer)$xmlObject->dxcc->adif;
        
        //Load DXCC Model
        return Dxcc::where('dxcc', $adif)->first();
    }
}
