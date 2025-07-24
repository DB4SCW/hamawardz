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

        //get environment variables
        $wavelog_url = config('app.db4scw_wavelog_url');
        $wavelog_key = config('app.db4scw_wavelog_api_key');

        //determine mode
        $use_wavelog = (db4scw_determine_dxcc_api_mode() == 'Wavelog');

        //get missing DXCCs on contacts
        $todo = Contact::where('dxcc_id', 1)->get();

        //fix contacts
        foreach ($todo as $contact) {
            
            //get dxcc from API
            $dxcc =  $use_wavelog ? db4scw_getdxcc_wavelog($contact->raw_callsign, $wavelog_url, $wavelog_key) : db4scw_getdxcc($contact->raw_callsign);

            //react to a faulty answer from the HamQTH or Wavelog API - return the error code
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
            $dxcc = $use_wavelog ? db4scw_getdxcc_wavelog($awardlog->callsign, $wavelog_url, $wavelog_key) : db4scw_getdxcc($awardlog->callsign);

            //react to a faulty answer from the HamQTH or Wavelog API - return the error code
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
}
