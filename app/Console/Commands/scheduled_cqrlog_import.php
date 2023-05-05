<?php

namespace App\Console\Commands;

use App\Models\Band;
use App\Models\Callsign;
use App\Models\Contact;
use App\Models\Dxcc;
use App\Models\Mode;
use App\Models\Upload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class scheduled_cqrlog_import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduled_cqrlog_import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import specified cqrlog data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get config from database
        $autoimportconfig = DB::table('confautoimport')->get();

        //repeat for every import
        foreach ($autoimportconfig as $config) {

            $callsign = strtoupper($config->callsign);
            $database = $config->databasename;

            //load internal Callsign id
            $call = Callsign::where('call', $callsign)->first();

            //skip if callsign not configured
            if($call == null)
            {
                continue;
            }

            //get current max legacy id
            $maxid = Contact::where('callsign_id', $call->id)->max('autoimport_foreign_id') ?? 0;

            //get all records that need to be imported
            $records_to_import = DB::table($database . '.' . $config->tablename)->where('id_cqrlog_main', '>', $maxid)->where('qsl_s', '!=', '')->orderBy('id_cqrlog_main', 'ASC')->get();

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
                $c = new Contact();
                $c->callsign_id = $call->id;
                $c->upload_id = $upload->id;
                $c->operator = $record->operator ?? $call->call;
                $c->qso_datetime = $record->qsodate . ' ' . $record->time_on;
                $c->raw_callsign = $record->callsign;
                $c->callsign = getcallsignwithoutadditionalinfo($record->callsign);
                $c->freq = $record->freq;
                $c->band_id = Band::where('band', strtolower($record->band))->first()->id;
                $c->mode_id = Mode::where('submode', $record->mode)->first()->id;
                $c->rst_s = $record->rst_s;
                $c->rst_r = $record->rst_r;
                $c->autoimport_db_name = $database;
                $c->autoimport_foreign_id = $record->id_cqrlog_main;
                $c->dxcc_id = Dxcc::where('dxcc', $record->adif)->first()->id;
                
                try {
                    //if saving fails because of indexes, omit qso
                    $c->save();
                } catch (\Throwable $th) {
                    continue;
                }
                
            }

            $upload->overall_qso_count = $upload->contacts->count();
            $upload->save();

        }
    }
}
