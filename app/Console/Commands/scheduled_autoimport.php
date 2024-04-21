<?php

namespace App\Console\Commands;

use App\Models\Autoimport;
use App\Models\Band;
use App\Models\Callsign;
use App\Models\Contact;
use App\Models\Dxcc;
use App\Models\Mode;
use App\Models\Upload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class scheduled_autoimport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduled_autoimport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import specified autoimport data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get config from database
        $autoimportconfig = Autoimport::where('active', 1)->get();

        //repeat for every import
        foreach ($autoimportconfig as $config) {

            //load internal Callsign id
            $call = Callsign::where('id', $config->callsign_id)->where('active', 1)->first();

            //skip if callsign not configured or inactive
            if($call == null)
            {
                continue;
            }

            //get current max autoimport foreign id
            $maxid = $config->contacts->max('autoimport_foreign_id') ?? 0;

            //get all records that need to be imported
            $records_to_import = DB::table($config->databasename . '.' . $config->tablename)->where($config->table_id, '>', $maxid)->orderBy($config->table_id, 'ASC')->get();

            //go to next database, if nothing is ready to import
            if($records_to_import->count() < 1)
            {
                continue;
            }

            //create upload record
            $upload = new Upload();
            $upload->uploader_id = 1;
            $upload->callsign_id = $call->id;
            $upload->file_content = 'AUTO-IMPORT of ' . $records_to_import->count() . ' QSO records.';
            $upload->save();

            //create new internal contact record for each new qso
            foreach ($records_to_import as $record) {
                
                //create new contact
                $c = new Contact();
                
                //set Callsign id
                $c->callsign_id = $call->id;
                
                //set upload id
                $c->upload_id = $upload->id;

                //get operator callsign from DB. If invalid, use callsign of eventcall
                $c->operator = getAutoImportFieldContent($config, "operator", $record) ?? $call->call;
                
                //if Date or Time is null, abort this crecord
                if(getAutoImportFieldContent($config, "qsodate", $record) == null || getAutoImportFieldContent($config, "qsotime", $record) == null )
                {
                    continue;
                }

                //Set datetime of QSO
                $c->qso_datetime = getAutoImportFieldContent($config, "qsodate", $record) . ' ' . getAutoImportFieldContent($config, "qsotime", $record);
                
                //get raw callsign, abort if empty
                $c->raw_callsign = getAutoImportFieldContent($config, "qsopartner_callsign", $record);

                if($c->raw_callsign == null)
                {
                    continue;
                }

                //strip prefix and suffix from raw callsign
                $c->callsign = swolf_getcallsignwithoutadditionalinfo($c->raw_callsign);
                
                //get frequency
                $c->freq = getAutoImportFieldContent($config, "frequency", $record);
                
                //get band, abort if empty
                $band = Band::where('band', strtolower(getAutoImportFieldContent($config, "band", $record) ?? ""))->first();

                if($band == null) 
                {
                    continue;
                }

                $c->band_id = $band->id;

                //get mode, abort if empty
                $mode = Mode::where('submode', getAutoImportFieldContent($config, "mode", $record) ?? "")->first();

                if($mode == null)
                {
                    continue;
                }

                $c->mode_id = $mode->id;
                
                //get RSTs
                $c->rst_s = getAutoImportFieldContent($config, "rst_s", $record) ?? "";
                $c->rst_r = getAutoImportFieldContent($config, "rst_r", $record) ?? "";
                
                //get DXCC, 0 if empty
                $dxcc = Dxcc::where('dxcc', getAutoImportFieldContent($config, "dxcc", $record) ?? "0")->first();
                $c->dxcc_id = $dxcc == null ? 0 : $dxcc->id;
                
                //set database and foreign id
                $c->autoimport_id = $config->id;
                $c->autoimport_foreign_id = $record->id_cqrlog_main;
                
                
                try {
                    //if saving fails because of indexes, omit qso
                    $c->save();
                } catch (\Throwable $th) {
                    continue;
                }
                
            }

            $upload->overall_qso_count = $upload->contacts->count();
            $upload->save();

            
            Callsign::find($upload->callsign_id)->setlastupload();

        }
    }
}
