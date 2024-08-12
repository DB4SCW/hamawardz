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

            //react to a faulty answer from the HamQTH API - return the error code
            if($dxcc->dxcc < 0) { return $dxcc->dxcc; }
            
            //write data to contact and save without updating timestamps
            Contact::withoutTimestamps(function () use($contact, $dxcc) {
                $contact->dxcc_id = $dxcc->id;
                $contact->save();
            });
        }

        //get missing DXCCs on Awardlogs
        $todo = Awardlog::where('dxcc_id', null)->get();

        //fix Awardlogs
        foreach ($todo as $awardlog) {

            //get dxcc from API
            $dxcc = $this->getdxcc($awardlog->callsign);

            //react to a faulty answer from the HamQTH API - return the error code
            if($dxcc->dxcc < 0) { return $dxcc->dxcc; }
            
            //write data to contact and save without updating timestamps
            Awardlog::withoutTimestamps(function () use($awardlog, $dxcc) {
                $awardlog->dxcc_id = $dxcc->id;
                $awardlog->save();
            });
        }

        //return no error code
        return 0;

    }

    function getdxcc(string $callsign) : Dxcc {
        
        //load info from API - return dummy answer in case API does not answer
        try {
            $dxccinfo = file_get_contents("https://www.hamqth.com/dxcc.php?callsign=" . urlencode($callsign));
        } catch (\Throwable $th) {
            $dummyanswer = new Dxcc();
            $dummyanswer->dxcc = -1;
            return $dummyanswer;
        }
        
        //read XML anser
        $xmlObject = simplexml_load_string($dxccinfo);
        
        //get ADIF info - return dummy answer in case API does not provide the expected information
        try {
            $adif = (integer)$xmlObject->dxcc->adif;
        } catch (\Throwable $th) {
            $dummyanswer = new Dxcc();
            $dummyanswer->dxcc = -2;
            return $dummyanswer;
        }
        
        //Load DXCC Model - return null if there is something wrong
        return Dxcc::where('dxcc', $adif)->first();
    }
}
