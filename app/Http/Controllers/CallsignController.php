<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use App\Models\Dxcc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CallsignController extends Controller
{
    public function index()
    {
        //Authorisation
        if(auth()->user()->events_to_manage->count() < 1)
        {
            if(!auth()->user()->siteadmin)
            {
                return redirect()->back()->with('danger', 'You do not have event manager permissions on any event.');
            }
        }
        
        //load all or 
        if(auth()->user()->siteadmin)
        {
            //Load all callsigns
            $callsigns = Callsign::orderBy('call', 'ASC')->get();
        }else
        {
            //Load all callsign ids for callsigns this user manages
            $callsign_ids = DB::table('callsign_hamevent')->whereIn('hamevent_id', auth()->user()->events_to_manage->pluck('id')->toArray())->get()->pluck('callsign_id')->toArray();
            //Load all callsigns for events with 
            $callsigns = Callsign::whereIn('id', $callsign_ids)->orWhere('creator_id', auth()->user()->id)->orderBy('call', 'ASC')->get();
        }

        //load relationships
        $callsigns->load('contacts', 'uploadusers', 'callsignapis');

        //Load all DXCCs
        $dxccs = Dxcc::orderBy('name', 'ASC')->get();

        //return view
        return view('callsign.list', ['callsigns' => $callsigns, 'dxccs' => $dxccs]);

    }

    public function create()
    {
        
        //Authorisation
        if(auth()->user()->events_to_manage->count() < 1)
        {
            if(!auth()->user()->siteadmin)
            {
                return redirect()->back()->with('danger', 'You do not have event manager permissions on any event.');
            }
        }
        
        //Validation
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'call' => 'string|min:3|max:20|unique:callsigns,call',
            'cert_holder_callsign' => 'string|min:3|max:20', 
            'dxcc_id' => 'exists:dxccs,id',
            'valid_from' => 'date|nullable',
            'valid_to' => 'date|after:valid_from|nullable'

        ], 
        [
            'call.string' => 'Callsign must be a text.',
            'call.min' => 'Callsign must be no less than 3 characters.',
            'call.max' => 'Callsign must be no more than 20 characters.',
            'call.unique' => 'Callsign is already registered.',
            'dxcc_id.exists' => 'Unknown DXCC.',
            'valid_from.date' => 'Start of validity must be a valid datetime.',
            'valid_to.date' => 'End of validity must be a valid datetime.',
            'valid_to.after' => 'End of validity must be after start of validity.'
        
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', db4scw_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //get callsign
        $callsign = new Callsign();

        $callsign->creator_id = auth()->user()->id;
        $callsign->created_at = \Carbon\Carbon::now();
        $callsign->call = strtoupper($attributes['call']);
        $callsign->cert_holder_callsign = strtoupper($attributes['cert_holder_callsign']);
        $callsign->dxcc_id = $attributes['dxcc_id'];
        $callsign->valid_from = $attributes['valid_from'] != null ? \Carbon\Carbon::parse($attributes['valid_from']) : null;
        $callsign->valid_to = $attributes['valid_to'] != null ? \Carbon\Carbon::parse($attributes['valid_to']) : null;

        //save callsign
        $callsign->save();

        //go to edit page
        return redirect()->route('showcallsigns')->with('success', 'Callsign saved successfully.');

    }

    public function show(Callsign $callsign)
    {
        
        //check permission
        if(request()->user()->cannot('manage', $callsign)) { abort(403); }

        $callsign->load('uploadusers', 'callsignapis');
        $dxccs = Dxcc::orderBy('name', 'ASC')->get();
        return view('callsign.edit', ['callsign' => $callsign, 'dxccs' => $dxccs]);
    }

    public function edit(Callsign $callsign)
    {
        //check permission
        if(request()->user()->cannot('manage', $callsign)) { abort(403); }

        //Validation
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'call' => 'string|min:3|max:20|unique:callsigns,call,' . $callsign->id,
            'cert_holder_callsign' => 'string|min:3|max:20',
            'dxcc_id' => 'exists:dxccs,id',
            'valid_from' => 'date|nullable',
            'valid_to' => 'date|after:valid_from|nullable'

        ], 
        [
            'call.string' => 'Callsign must be a text.',
            'call.min' => 'Callsign must be no less than 3 characters.',
            'call.max' => 'Callsign must be no more than 20 characters.',
            'call.unique' => 'Callsign is already registered.',
            'dxcc_id.exists' => 'Unknown DXCC.',
            'valid_from.date' => 'Start of validity must be a valid datetime.',
            'valid_to.date' => 'End of validity must be a valid datetime.',
            'valid_to.after' => 'End of validity must be after start of validity.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', db4scw_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //update callsign
        $callsign->updated_at = \Carbon\Carbon::now();
        $callsign->call = strtoupper($attributes['call']);
        $callsign->cert_holder_callsign = strtoupper($attributes['cert_holder_callsign']);
        $callsign->dxcc_id = $attributes['dxcc_id'];
        $callsign->valid_from = $attributes['valid_from'] != null ? \Carbon\Carbon::parse($attributes['valid_from']) : null;
        $callsign->valid_to = $attributes['valid_to'] != null ? \Carbon\Carbon::parse($attributes['valid_to']) : null;
        $callsign->save();

        //go to edit page
        return redirect()->route('showcallsigns')->with('success', 'Callsign saved successfully.');
    }

    public function destroy(Callsign $callsign)
    {
        //check permission
        if(request()->user()->cannot('manage', $callsign)) { abort(403); }

        //prevent deletion if callsign has contacts
        if($callsign->contacts->count() > 0)
        {
            return redirect()->back()->with('danger', 'This callsign does have registered QSOs. Cannot delete.');
        }

        //save call
        $temp = $callsign->call;

        //delete callsign
        $callsign->delete();

        //go to edit page
        return redirect()->route('showcallsigns')->with('success', 'Callsign ' . $temp . ' deleted successfully.');
    }

    public function removeuploader(Callsign $callsign, int $uploaderid)
    {
        //check permission
        if(request()->user()->cannot('manage', $callsign)) { abort(403); }

        try {
            $uploader = User::findOrFail($uploaderid);
        } catch (\Throwable $th) {
            abort(404);
        }

        //remove upload permission
        $callsign->uploadusers()->detach($uploader);

        //redirect back
        return redirect()->route('showeditcallsign', ['callsign' => $callsign->call])->with('success', 'Successfully removed user as uploader.');

    }

    public function adduploader(Callsign $callsign)
    {
        //check permission
        if(request()->user()->cannot('manage', $callsign)) { abort(403); }
        
        //validate
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'username' => 'string|min:3|max:255'
        ], 
        [
            'username.min' => 'Invalid username. Too short!',
            'username.max' => 'Invalid username. Too long!',
            'username.string' => 'Invalid username. Must be a text!'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', db4scw_validatorerrors($validator))->withInput();
        }

        //get validated attributes
        $attributes = $validator->validated();

        $new_uploaders = User::whereRaw('LOWER(username) = ?', [strtolower($attributes['username'])])->get();

        if($new_uploaders->count() != 1)
        {
            return redirect()->back()->with('danger', 'Username not found.')->withInput();
        }

        //Load new manager
        $new_uploader = $new_uploaders->first();

        //Sanity check
        if($callsign->uploadusers->contains($new_uploader))
        {
            return redirect()->back()->with('danger', 'User is already a registered uploader.')->withInput();
        }

        //attach permission
        $callsign->uploadusers()->attach($new_uploader);

        //redirect back
        return redirect()->route('showeditcallsign', ['callsign' => $callsign->call])->with('success', 'Successfully added user as uploader.');

    }


}
