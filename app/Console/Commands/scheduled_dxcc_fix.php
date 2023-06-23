<?php

namespace App\Console\Commands;

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
    protected $description = 'Fixes missing dxccs on contacts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get output
        $output = [];

        //missing DXCCs holen
        $todo = Contact::where('dxcc_id', 1)->get();

        //fix contacts
        foreach ($todo as $contact) {
            //load info from API
            $dxccinfo = file_get_contents("https://www.hamqth.com/dxcc.php?callsign=" . urlencode($contact->raw_callsign));
            $xmlObject = simplexml_load_string($dxccinfo);
            $adif = (integer)$xmlObject->dxcc->adif;
            
            //Load DXCC Model
            $dxcc = Dxcc::where('dxcc', $adif)->first();
            
            //write data to contact an dsave
            $contact->dxcc_id = $dxcc->id;
            $contact->save();

        }

    }
}
