<?php

namespace App\Http\Controllers;

use App\Models\Hamevent;
use App\Models\Upload;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        //Get uploads of current user
        $uploads = Upload::with('callsign', 'uploader')->where('uploader_id', auth()->user()->id)->orderBy('created_at', 'DESC')->get();

        //Load view
        return view('dashboard', ['uploads' => $uploads]);
    }

    public function autoimport()
    {
        //Trigger autoimport
        \Illuminate\Support\Facades\Artisan::call('app:scheduled_cqrlog_import', []);

        //return to view
        return redirect()->route('participant_dashboard')->with('success', 'Autoimport successful.');
    }
}