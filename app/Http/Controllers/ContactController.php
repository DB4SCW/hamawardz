<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Hamevent;
use App\Models\Upload;
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
        $fileName = $event->slug . '_export_contacts.csv';

        //define headers
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        //define columns
        $columns = array('Eventcallsign', 'Operator', 'QSO_Datetime', 'Callsign', 'Callsign_Raw', 'Frequency', 'Band', 'Mode', 'Mainmode', 'RST_S', 'RST_R', );
        
    }
}
