<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Hamevent;
use App\Models\Upload;
use App\Models\Dxcc;
use Illuminate\Http\Request;

class ContactController extends Controller
{

    public function show(Upload $upload)
    {

        //disable viewing of uploads of other users unless user is siteadmin
        if($upload->uploader_id != auth()->user()->id)
        {
            if(!auth()->user()->siteadmin)
            {
                abort(403);
            }
        }

        //get all contacts of this upload
        $contacts = Contact::where('upload_id', $upload->id)->with('band', 'mode', 'eventcallsign')->orderBy('qso_datetime', 'ASC')->get();

        //return view
        return view('contacts', ['contacts' => $contacts, 'upload' => $upload]);
    }

    public function showerrors(Upload $upload)
    {
        //disable viewing of uploads of other users unless user is siteadmin
        if($upload->uploader_id != auth()->user()->id)
        {
            if(!auth()->user()->siteadmin)
            {
                abort(403);
            }
        }
        
        //get errors from imploded text
        $errors = explode("|", $upload->errors);

        //return view
        return view('uploaderrors', ['errors' => $errors, 'upload' => $upload]);
    }

    public function exportcontacts(Hamevent $event)
    {

        //Load eventmanagers
        $event->load('eventmanagers', 'callsigns');

        //permission check
        if(!auth()->user()->siteadmin)
        {
            if($event->creator_id != auth()->user()->id)
            {
                if(!$event->eventmanagers->contains(auth()->user()))
                {
                    return redirect()->back()->with('danger', 'You do not have permission to export the contacts for this event.');
                }
            }
        }

        //Load contacts with relations
        $contacts = Contact::where([['qso_datetime', '>=', $event->start], ['qso_datetime', '<=', $event->end]])->whereIn('callsign_id', $event->callsigns->pluck('id')->toArray())->orderBy('qso_datetime', 'ASC')->get();
        $contacts->load('eventcallsign', 'mode', 'dxcc');

        //get filename
        $fileName = \Carbon\Carbon::now()->format('Ymd') . "_" . $event->slug . '_export_contacts.csv';

        //define headers
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        //define columns
        $columns = array('Eventcallsign', 'Operator', 'QSO_Datetime', 'Callsign', 'Callsign_Raw', 'Frequency', 'Band', 'Mode', 'Mainmode', 'RST_S', 'RST_R', 'DXCC_ID', 'DXCC', 'Continent');

        //write file
        $callback = function() use($contacts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($contacts as $contact) {

                $row = array();
                $row['Eventcallsign']  = $contact->eventcallsign->call;
                $row['Operator']    = $contact->operator;
                $row['QSO_Datetime']    = $contact->qso_datetime;
                $row['Callsign']  = $contact->callsign;
                $row['Callsign_Raw']  = $contact->raw_callsign;
                $row['Frequency']  = $contact->freq;
                $row['Band']  = $contact->band->band;
                $row['Mode']  = $contact->mode->mode;
                $row['Mainmode']  = $contact->mode->mainmode;
                $row['RST_S']  = $contact->rst_s;
                $row['RST_R']  = $contact->rst_r;
                $row['DXCC_ID']  = $contact->dxcc->dxcc;
                $row['DXCC']  = $contact->dxcc->name;
                $row['Continent']  = $contact->dxcc->cont;

                fputcsv($file, array($row['Eventcallsign'], $row['Operator'], $row['QSO_Datetime'], $row['Callsign'], $row['Callsign_Raw'], $row['Frequency'], $row['Band'], $row['Mode'], $row['Mainmode'], $row['RST_S'], $row['RST_R'], $row['DXCC_ID'], $row['DXCC'], $row['Continent']));
            }

            fclose($file);
        };

        //return file
        return response()->stream($callback, 200, $headers);
        
    }

    function fixmissingdxccs() {
        
        //only for admins
        if(!auth()->user()->siteadmin)
        {
            return redirect()->back();
        }

        //Trigger dxcc fix
        \Illuminate\Support\Facades\Artisan::call('app:scheduled_dxcc_fix', []);

        //return to view
        return redirect()->route('showprofile')->with('success', 'Fixed DXCCs');

    }
}
