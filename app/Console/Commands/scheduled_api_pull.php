<?php

namespace App\Console\Commands;

use App\Models\Callsignapidetail;
use Illuminate\Console\Command;

class scheduled_api_pull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduled_api_pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls new QSOs from each active API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //load relevant APIs
        $active_apis = Callsignapidetail::whereRelation('contextuser', 'locked', false)->whereRelation('callsign', 'active', true)->where('active', true)->get();

        //collect all created uploads
        $created_uploads = [];

        //call each api
        foreach ($active_apis as $api) {
            $upload = $api->pull();
            array_push($created_uploads, $upload);
        }

    }
}
