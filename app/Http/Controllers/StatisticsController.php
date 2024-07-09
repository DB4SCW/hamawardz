<?php

namespace App\Http\Controllers;

use App\Models\Awardlog;
use App\Models\Hamevent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function statisticsdashboard(Hamevent $event)
    {

        //return view
        return view('statistics.dashboard', ['event' => $event]);
    }

    public function qso_leaderboard(Hamevent $event)
    {
        //Define header
        $description = 'Callsign';
        $dataheader = 'QSO Count';
        $header = 'QSO Count for each participant';

        $stats = DB::table('contacts')
        ->selectRaw('contacts.callsign AS Callsign, COUNT(contacts.id) AS QSO_Count')
        ->whereRaw("contacts.qso_datetime >= ? AND contacts.qso_datetime <= ? AND contacts.callsign_id IN (SELECT callsign_hamevent.callsign_id FROM callsign_hamevent WHERE callsign_hamevent.hamevent_id = ?)", [$event->start, $event->end, $event->id])
        ->groupBy('contacts.callsign')  
        ->orderByraw('COUNT(contacts.id) DESC')
        ->take(100)
        ->get();

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }

    public function callsign_leaderboard(Hamevent $event)
    {
        //Define header
        $description = 'Event Callsign';
        $dataheader = 'QSO Count';
        $header = 'QSO Count for each event callsign';

        $stats = DB::table('contacts')
        ->selectRaw('callsigns.call AS Event_Call, COUNT(contacts.id) AS QSO_Count')
        ->join('callsigns', 'callsigns.id', '=', 'contacts.callsign_id')
        ->whereRaw("contacts.qso_datetime >= ? AND contacts.qso_datetime <= ? AND contacts.callsign_id IN (SELECT callsign_hamevent.callsign_id FROM callsign_hamevent WHERE callsign_hamevent.hamevent_id = ?)", [$event->start, $event->end, $event->id])
        ->groupBy('callsigns.call')  
        ->orderByraw('COUNT(contacts.id) DESC')
        ->get();

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }

    public function dxcc_leaderboard(Hamevent $event)
    {
        //Define header
        $description = 'DXCC';
        $dataheader = 'QSO Count';
        $header = 'QSO Count for each DXCC';

        $stats = DB::table('contacts')
        ->selectRaw('dxccs.name AS DXCC, COUNT(contacts.id) AS QSO_Count')
        ->join('dxccs', 'dxccs.id', '=', 'contacts.dxcc_id')
        ->whereRaw("contacts.qso_datetime >= ? AND contacts.qso_datetime <= ? AND contacts.callsign_id IN (SELECT callsign_hamevent.callsign_id FROM callsign_hamevent WHERE callsign_hamevent.hamevent_id = ?)", [$event->start, $event->end, $event->id])
        ->groupBy('dxccs.name')  
        ->orderByraw('COUNT(contacts.id) DESC')
        ->get();

        $header = $header . ' (' . $stats->count() . ' worked)';

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }

    public function continent_leaderboard(Hamevent $event)
    {
        //Define header
        $description = 'Continent';
        $dataheader = 'QSO Count';
        $header = 'QSO Count for each Continent';

        $stats = DB::table('contacts')
        ->selectRaw('dxccs.cont AS Continent, COUNT(contacts.id) AS QSO_Count')
        ->join('dxccs', 'dxccs.id', '=', 'contacts.dxcc_id')
        ->whereRaw("contacts.qso_datetime >= ? AND contacts.qso_datetime <= ? AND contacts.callsign_id IN (SELECT callsign_hamevent.callsign_id FROM callsign_hamevent WHERE callsign_hamevent.hamevent_id = ?)", [$event->start, $event->end, $event->id])
        ->groupBy('dxccs.cont')  
        ->orderByraw('COUNT(contacts.id) DESC')
        ->get();

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }

    public function band_leaderboard(Hamevent $event)
    {
        //Define header
        $description = 'Band';
        $dataheader = 'QSO Count';
        $header = 'QSO Count for each Band';

        $stats = DB::table('contacts')
        ->selectRaw('bands.band AS Band, COUNT(contacts.id) AS QSO_Count')
        ->join('bands', 'bands.id', '=', 'contacts.band_id')
        ->whereRaw("contacts.qso_datetime >= ? AND contacts.qso_datetime <= ? AND contacts.callsign_id IN (SELECT callsign_hamevent.callsign_id FROM callsign_hamevent WHERE callsign_hamevent.hamevent_id = ?)", [$event->start, $event->end, $event->id])
        ->groupBy('bands.band')  
        ->orderByraw('COUNT(contacts.id) DESC')
        ->get();

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }

    public function mode_leaderboard(Hamevent $event)
    {
        //Define header
        $description = 'Mode';
        $dataheader = 'QSO Count';
        $header = 'QSO Count for each Mode';

        $stats = DB::table('contacts')
        ->selectRaw('modes.mode AS Mode, COUNT(contacts.id) AS QSO_Count')
        ->join('modes', 'modes.id', '=', 'contacts.mode_id')
        ->whereRaw("contacts.qso_datetime >= ? AND contacts.qso_datetime <= ? AND contacts.callsign_id IN (SELECT callsign_hamevent.callsign_id FROM callsign_hamevent WHERE callsign_hamevent.hamevent_id = ?)", [$event->start, $event->end, $event->id])
        ->groupBy('modes.mode')  
        ->orderByraw('COUNT(contacts.id) DESC')
        ->get();

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }

    public function lastuploads(Hamevent $event)
    {
        //Define header
        $description = 'Callsign';
        $dataheader = 'Last Upload';
        $header = 'Last ADIF upload for each callsign';

        $stats = DB::table('uploads')
        ->selectRaw('callsigns.call AS Event_Call,MAX(uploads.created_at) AS Last_Upload')
        ->join('callsigns', 'callsigns.id', '=', 'uploads.callsign_id')
        ->whereRaw("uploads.callsign_id IN (SELECT callsign_hamevent.callsign_id FROM callsign_hamevent WHERE callsign_hamevent.hamevent_id = ?)", [$event->id])
        ->groupBy('callsigns.call')  
        ->orderByraw('MAX(uploads.created_at) DESC')
        ->get();

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }

    public function createdawards(Hamevent $event)
    {
        //define header
        $description = 'Award';
        $dataheader = 'Callsign, name and (re)creation date';
        $header = 'Created awards';

        //load award ids for event
        $awardids = $event->awards->pluck('id');

        //get database type
        $databaseType = DB::getDriverName();
        if($databaseType === 'mysql')
        {
            //load data
            $stats = DB::table('awardlogs')
            ->selectRaw('awards.title, CONCAT(awardlogs.callsign, " - ", awardlogs.chosen_name, " @ ", awardlogs.updated_at) as Data')
            ->join('awards', 'awards.id', 'award_id')
            ->whereIn('award_id', $awardids)
            ->orderBy('awardlogs.updated_at', 'DESC')
            ->get();
        }elseif($databaseType === 'sqlite')
        {
            //load data
            $stats = DB::table('awardlogs')
            ->selectRaw('awards.title, awardlogs.callsign || " - " || awardlogs.chosen_name || " @ " || awardlogs.updated_at as Data')
            ->join('awards', 'awards.id', 'award_id')
            ->whereIn('award_id', $awardids)
            ->orderBy('awardlogs.updated_at', 'DESC')
            ->get();
        }elseif($databaseType === 'pgsql')
        {
            //load data
            $stats = DB::table('awardlogs')
            ->selectRaw('awards.title, CONCAT(awardlogs.callsign, \' - \', awardlogs.chosen_name, \' @ \', awardlogs.updated_at) as Data')
            ->join('awards', 'awards.id', 'award_id')
            ->whereIn('award_id', $awardids)
            ->orderBy('awardlogs.updated_at', 'DESC')
            ->get();
        }
        else
        {
            return redirect()->back()->with('warning', 'Only Sqlite, Postgresql and MySQL databases are supported for this function');
        }

        //return view
        return view('statistics.blankstatpage', ['event' => $event, 'descriptionheader' => $description, 'dataheader' => $dataheader, 'header' => $header, 'stats' => $stats]);
    }
}
