<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Upload;
use Illuminate\Http\Request;

class ContactController extends Controller
{

    public function show(Upload $upload)
    {
        $contacts = Contact::where('upload_id', $upload->id)->with('band', 'mode', 'eventcallsign')->orderBy('qso_datetime', 'ASC')->get();

        return view('contacts', ['contacts' => $contacts, 'upload' => $upload]);
    }

    public function showerrors(Upload $upload)
    {
        $errors = explode("|", $upload->errors);

        return view('uploaderrors', ['errors' => $errors, 'upload' => $upload]);
    }
}
