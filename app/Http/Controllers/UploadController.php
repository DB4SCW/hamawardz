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
            'operator' => 'nullable|string|min:3|max:20',
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
        $upload->type = 'Manual ADIF Upload';

        //Before saving, check if there are QSOs in the upload
        if($upload->overall_qso_count < 1)
        {
            return redirect()->back()->with('warning', 'There were no QSOs in this ADIF.');
        }

        //optional check if at least some qsos inside of the ADIF live inside the validity of the callsign
        //deactivated because this accesses fields of the ADIF which may or may not be there
        if(false) 
        { 
            if(!checkadifinsidevalidityperiod($data, $callsign))
            {
                return redirect()->back()->with('danger', 'There were no QSOs found that was inside the validity period of this event callsign.');
            }
        }

        //save upload, no that we are safe that we got any blockers out of the way
        $upload->save();

        //load ignore duplicate flag
        $ignore_duplicates = ($attributes['ignoreduplicates'] == 1);

        //process upload
        $correct = $upload->process($attributes['operator'], $ignore_duplicates);

        //write data to callsign
        $callsign->setlastupload();

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
        $callsign_id = $upload->callsign_id;
        $upload->delete();

        //write data to callsign
        Callsign::find($callsign_id)->setlastupload();

        //return back
        return redirect()->back()->with('success', 'Upload ' . $id . ' was successfully deleted.');

    }

    
}
