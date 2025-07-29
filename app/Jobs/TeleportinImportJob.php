<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TeleportinImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $max_duration;
    public $timeout = 30000;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        
        //set timeout
        $this->max_duration = 30;

        //get database driver
        $databaseType = DB::getDriverName();

        //mark import as "in progress"
        Cache::put('teleportin_in_progress', true, now()->addMinutes($this->max_duration));

        try {

            //prep data for progress
            $totalTables = count($this->data);
            $processedTables = 0;

            //database preprocessing
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($databaseType === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            }

            //data processing
            foreach ($this->data as $table => $rows) {

                //table internal progress
                $totalRows = count($rows);
                $current = 0;
                
                //write progress to cache
                Cache::put('teleportin_progress_status', "Importing table: $table", now()->addMinutes($this->max_duration));
                
                //delete table
                DB::table($table)->truncate();
                
                //MSSQL-specific preprocessing
                if ($databaseType === 'sqlsrv') {
                    DB::unprepared('SET IDENTITY_INSERT dbo.' . $table . ' ON;');
                }
                
                //insert data
                foreach (array_chunk($rows, 100) as $chunk) {
                    
                    //do the insert
                    DB::table($table)->insert($chunk);
                    
                    //progress
                    $current++;

                    if ($current % 100 === 0 || $current === $totalRows) {
                        $percent = intval(($current / $totalRows) * 100);
                        Cache::put('teleportin_progress_status', "Importing $table: $current / $totalRows", now()->addMinutes($this->max_duration));
                        Cache::put('teleportin_contacts_percent', $percent, now()->addMinutes($this->max_duration));
                    }

                }
                
                //MSSQL-specific postprocessing
                if ($databaseType === 'sqlsrv') {
                    DB::unprepared('SET IDENTITY_INSERT dbo.' . $table . ' OFF;');
                }

                //calculate progress
                $processedTables++;
                $percent = intval(($processedTables / $totalTables) * 100);
                Cache::put('teleportin_progress_percent', $percent, now()->addMinutes($this->max_duration));
            }

            //database postprocessing
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($databaseType === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }

            //final progress
            Cache::put('teleportin_progress_percent', 100, now()->addMinutes(1));
            Cache::put('teleportin_progress_status', 'Done.', now()->addMinutes(1));
        } finally {
            //remove lock and progress
            Cache::forget('teleportin_in_progress');
            Cache::forget('teleportin_progress_percent');
        }
    }
}
