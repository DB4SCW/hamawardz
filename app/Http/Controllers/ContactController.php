<?php

namespace App\Http\Controllers;

use App\Models\Contact;
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
}
