<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class TeleportController extends Controller
{
    public function dumpalluploads()
    {
        //only siteadmin may do that
        if(!auth()->user()->siteadmin) { abort(503); }
        
        //get all uploads
        $uploads = Upload::all();

        //define header
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="adifdump.zip"',
        ];

        //dump all adifs, zip it and stream it
        $response = new StreamedResponse(function() use ($uploads) {
            $zip = new \ZipArchive();
            $zipFileName = tempnam(sys_get_temp_dir(), '') . '.zip';
            $zip->open($zipFileName, \ZipArchive::CREATE);

            foreach ($uploads as $upload) {
                $fileName = $upload->id . '.adif';
                $fileContent = $upload->file_content;
                $zip->addFromString($fileName, $fileContent);
            }

            $zip->close();

            readfile($zipFileName);
            unlink($zipFileName);
        }, 200, $headers);

        //return stream
        return $response;
    }

    public function teleportout()
    {
        $tables = ['autoimports', 'awardlogs', 'awards', 'awardtimeframes', 'bands', 'callsigns', 'callsign_hamevent', 'callsign_user', 'contacts', 'dxccs', 'hamevents', 'hamevent_user', 'modes', 'phonetics', 'uploads', 'users']; 

        $response = new StreamedResponse(function() use ($tables) {
            $handle = fopen('php://output', 'w');

            $data = [];
            foreach ($tables as $table) {
                $data[$table] = DB::table($table)->get()->toArray();
            }

            fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));
            fclose($handle);
        }, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="teleport_out.json"',
        ]);

        return $response;
    }

    public function teleportin()
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'data_file' => 'required|file'
        ], 
        [
            'data_file.file' => 'No input file given.'
        ]);

        //,'data_file.mimes' => 'Input file has to be in json format.'

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        $file = request()->file('data_file');
        $jsonContent = file_get_contents($file->getRealPath());
        $data = json_decode($jsonContent, true);

        $databaseType = DB::getDriverName();

            // Disable foreign key checks
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($databaseType === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            }

            foreach ($data as $table => $rows) {
                DB::table($table)->truncate();
                foreach ($rows as $row) {
                    DB::table($table)->insert($row);
                }
            }

            // Enable foreign key checks
            if ($databaseType === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($databaseType === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }

        return redirect()->route('showprofile')->with('success', 'Data imported successfully.');
    }
}