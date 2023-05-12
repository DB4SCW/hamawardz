<?php

namespace App\Http\Controllers;

use App\Models\Band;
use App\Models\Callsign;
use App\Models\Contact;
use App\Models\Dxcc;
use App\Models\Mode;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use j4nr6n\ADIF\Parser;

class UploadController extends Controller
{
    public function index()
    {
        //load all callsigns the user may upload to
        if(auth()->user()->siteadmin)
        {
            $callsigns = Callsign::where('active', 1)->orderBy('call', 'ASC')->get();
        }else{
            $callsigns = auth()->user()->callsigns()->where('active', 1)->orderBy('call', 'ASC')->get();
        }

        return view('upload', ['callsigns' => $callsigns]);
    }

    public function upload()
    {
        //validate
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'callsignid' => 'exists:callsigns,id',
            'operator' => 'string|min:3|max:20',
            'file' => 'required', 
            'ignoreduplicates' => 'integer|min:0|max:1'
        ], 
        [
            'callsignid.exists' => 'This callsign does not exist. Sorry.',
            'operator.string' => 'Operator must be a text.',
            'operator.min' => 'Operator must be at least 3 characters long.',
            'operator.max' => 'Operator must be at most 20 characters long.',
            'file.required' => 'No file provided.',
            'ignoreduplicates.string' => 'Invalid flag to hide duplicate error messages.',
            'ignoreduplicates.min' => 'Invalid flag to hide duplicate error messages.',
            'ignoreduplicates.max' => 'Invalid flag to hide duplicate error messages.',
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //load ignore duplicate flag
        $ignore_duplicates = $attributes['ignoreduplicates'] == 1 ? true : false;

        //Load Callsign
        $callsign = Callsign::where('id', $attributes['callsignid'])->where('active', 1)->first();

        //check invalid callsign
        if($callsign == null)
        {
            return redirect()->back()->with('danger', 'Callsign is inactive.');
        }
        
        //check if user is allowed to upload for this callsign
        $allowed_callsigns = auth()->user()->callsigns;
        
        if(!$allowed_callsigns->contains($callsign))
        {
            if(!auth()->user()->siteadmin)
            {
                return redirect()->back()->with('danger', 'You are not allowed to upload logs for that callsign.');
            }
        }
        
        //parse adif
        $file = request()->file('file');
        $contents = file_get_contents($file);
        $data = (new Parser())->parse($contents);
     
        //create a new upload record
        $upload = new Upload();
        $upload->uploader_id = auth()->user()->id;
        $upload->callsign_id = $callsign->id;
        $upload->file_content = $contents;
        $upload->overall_qso_count = count($data);
        $upload->save();

        //prepare Error Collection
        $errors = [];

        //loop through each record
        $i = 0;
        $correct = 0;
        foreach ($data as $record) {

            //sanity check for parsed adif file
            $requiredfields = ['CALL', 'QSO_DATE', 'TIME_ON', 'FREQ', 'RST_SENT', 'RST_RCVD', 'MODE']; 

            $check = true;
            foreach ($requiredfields as $field) {
                
                if(!array_key_exists($field, $record))
                {
                    $check = false;
                    array_push($errors, 'Record ' . $i+1 . ' Is missing required field <' . $field . '>. Skipping.');
                }
            }

            //skip record if required fields are not present
            if(!$check)
            {
                $i++;
                continue;
            }

            //populate Contact
            $contact = new Contact();
            $contact->callsign_id = $callsign->id;
            $contact->upload_id = $upload->id;
            $contact->operator = strtoupper($attributes['operator']);
            $contact->qso_datetime = \Carbon\Carbon::parse($record['QSO_DATE'] . ' ' . substr($record['TIME_ON'],0,2) . ':' . substr($record['TIME_ON'], 2, 2));
            $contact->raw_callsign = $record['CALL'];
            $contact->callsign = getcallsignwithoutadditionalinfo($record['CALL']);
            $contact->freq = $record['FREQ'];
            $contact->rst_s = $record['RST_SENT'];
            $contact->rst_r = $record['RST_RCVD'];
            

            //try to get Band, mode and DXCC
            $band = Band::where([['start', '<=', $contact->freq], ['end', '>=', $contact->freq]])->first();
            $mode = Mode::where('submode', $record['MODE'])->first();
            $dxcc = Dxcc::where('dxcc', array_key_exists('DXCC', $record) ? $record['DXCC'] : 0)->first();

            //Check for errors
            if($band == null)
            {
                array_push($errors, 'Record ' . $i+1 . ', Callsign ' . $contact->raw_callsign . ' on ' . $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC: Frequency ' . $contact->freq . ' is out of bandplan. Skipping.');
                $i++;
                continue;
            }

            if($mode == null)
            {
                array_push($errors, 'Record ' . $i+1 . ', Callsign ' . $contact->raw_callsign . ' on ' . $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC: Mode ' . $record['MODE'] . ' is not recognised. Skipping.');
                $i++;
                continue;
            }

            if($dxcc == null)
            {
                $contact->dxcc_id = Dxcc::where('dxcc', 0)->first();
            }

            //insert band, mode and dxcc to contact
            $contact->band_id = $band->id;
            $contact->mode_id = $mode->id;
            $contact->dxcc_id = $dxcc->id;

            //duplicate-check
            $alreadythere = Contact::where([['callsign_id', $contact->callsign_id], ['qso_datetime', $contact->qso_datetime], ['callsign', $contact->callsign], ['band_id', $contact->band_id]]);

            if($alreadythere->count() > 0)
            {
                if(!$ignore_duplicates)
                {
                    array_push($errors, 'Record ' . $i+1 . ', Callsign ' . $contact->raw_callsign . ' on ' . $contact->qso_datetime->format('Y-m-d @ H:i') . ' UTC: QSO already exists in the database. Skipping.');
                }
                
                $i++;
                continue;
            }

            //save the contact
            $contact->save();
            
            //increment counters
            $i++;
            $correct++;

        }

        //write errors and statistics to database
        $upload->errors = count($errors) > 0 ? implode("|", $errors) : '';

        //Save upload record
        $upload->save();

        //return to dashboard with appropriate message
        if($correct == count($data))
        {
            return redirect()->route('participant_dashboard')->with('success', '' . $correct . ' out of ' . count($data) . ' QSOs got imported successfully. Woohooo!');
        }else {
            if($correct == 0)
            {
                return redirect()->route('participant_dashboard')->with('danger', '' . $correct . ' out of ' . count($data) . ' QSOs got imported successfully. Please check your file and upload again!');
            }else {
                return redirect()->route('participant_dashboard')->with('warning', '' . $correct . ' out of ' . count($data) . ' QSOs got imported successfully. Please check the error list and consider uploading again.');
            }
        }
        
    }

    public function delete()
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'uploadId' => 'exists:uploads,id'
        ], 
        [
            'uploadId.exists' => 'This upload does not exist.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //Load upload
        $upload = Upload::find($attributes['uploadId']);

        //dont let anyone delete something they did not upload themselves
        if(auth()->user()->id != $upload->uploader_id)
        {
            return redirect()->route('home')->with('danger', 'Cheeky. But no.');
        }

        //delete all contacts of that upload
        DB::table('contacts')->where('upload_id', $upload->id)->delete();

        //delete upload record itself
        $id = $upload->id;
        $upload->delete();

        //return back
        return redirect()->back()->with('success', 'Upload ' . $id . ' was successfully deleted.');

    }
}
