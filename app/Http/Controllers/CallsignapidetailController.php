<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use App\Models\User;
use App\Models\Callsignapidetail;
use Illuminate\Http\Request;
use stdClass;

class CallsignapidetailController extends Controller
{
    public function show(Callsignapidetail $api)
    {
        //Load associated callsign
        $api->load('callsign', 'contextuser');

        //check permission
        if(request()->user()->cannot('manage', $api->callsign)) { abort(403); }

        //return view
        return view('callsignapi.edit', ['api' => $api]);

    }

    public function create(Callsign $callsign)
    {
        //check permission
        if(request()->user()->cannot('manage', $callsign)) { abort(403); }

        //Validation
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'username' => 'string|min:1|max:255',
            'type' => 'string|in:wavelog',
            'url' => 'url:http,https',
            'payload' => 'string|nullable|max:255',
            'goalpost' => 'string|nullable|max:255',
            'active' => 'integer|in:0,1'

        ], 
        [
            'username.string' => 'Username must be a text.',
            'username.min' => 'Username must be no less than 1 characters.',
            'username.max' => 'Username must be no more than 255 characters.',
            'url.url' => 'API url must conform to URL standards',
            'payload.string' => 'Payload must be a string',
            'payload.max' => 'Payload must be no more than 255 characters long',
            'goalpost.string' => 'Goalpost must be a string',
            'goalpost.max' => 'Goalpost must be no more than 255 characters long',
            'active.integer' => 'Active may only be yes or no.',
            'active.in' => 'Active may only be yes or no.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', db4scw_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //check if user exists
        $user = User::where('username', strtoupper($attributes['username']))->where('locked', false)->first();

        //abort if user is not found
        if($user == null)
        {
            return redirect()->back()->with('danger', 'Username ' . $attributes['username'] . ' is not an active user.');
        }

        //check if there are missing fields in the payload
        $fieldcheckresult = $this->checkpayloadfields($attributes['payload'], strtolower($attributes['type']));
        
        //react to the check result
        if(!$fieldcheckresult->passed)
        {
            //check for complete failure and offer a more helpful message
            if(count($fieldcheckresult->minimum_fields) == count($fieldcheckresult->missing_fields))
            {
                return redirect()->back()->with('danger', 'Invalid payload JSON or all required fields are missing. Check your data.');
            }
        
            //tell the user which fields they missed
            return redirect()->back()->with('danger', 'The following fields are missing in the payload field: ' . implode(", ", $fieldcheckresult->missing_fields));
        }

        //create API config and save
        $api = new Callsignapidetail();
        $api->callsign_id = $callsign->id;
        $api->context_userid = $user->id;
        $api->type = strtolower($attributes['type']);
        $api->url = $attributes['url'];
        $api->payload = $attributes['payload'];
        $api->goalpost = $attributes['goalpost'];
        $api->active = ($attributes['active'] == 1);
        $api->save();

        //redirect back with success
        return redirect()->route('showeditcallsign', ['callsign' => $callsign->call])->with('success', 'Successfully added Callsign API.');

    }

    public function edit(Callsignapidetail $api)
    {
        //check permission
        if(request()->user()->cannot('manage', $api->callsign)) { abort(403); }

        //Validation
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'username' => 'string|min:1|max:255',
            'type' => 'string|in:wavelog',
            'url' => 'url:http,https',
            'payload' => 'string|nullable|max:255',
            'goalpost' => 'string|nullable|max:255',
            'active' => 'integer|in:0,1'

        ], 
        [
            'username.string' => 'Username must be a text.',
            'username.min' => 'Username must be no less than 1 characters.',
            'username.max' => 'Username must be no more than 255 characters.',
            'url.url' => 'API url must conform to URL standards',
            'payload.string' => 'Payload must be a string',
            'payload.max' => 'Payload must be no more than 255 characters long',
            'goalpost.string' => 'Goalpost must be a string',
            'goalpost.max' => 'Goalpost must be no more than 255 characters long',
            'active.integer' => 'Active may only be yes or no.',
            'active.in' => 'Active may only be yes or no.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', db4scw_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //check if user exists
        $user = User::where('username', strtoupper($attributes['username']))->where('locked', false)->first();

        //abort if user is not found
        if($user == null)
        {
            return redirect()->back()->with('danger', 'Username ' . $attributes['username'] . ' is not an active user.');
        }

        //check if there are missing fields in the payload
        $fieldcheckresult = $this->checkpayloadfields($attributes['payload'], strtolower($attributes['type']));
        
        //react to the check result
        if(!$fieldcheckresult->passed)
        {
            //check for complete failure and offer a more helpful message
            if(count($fieldcheckresult->minimum_fields) == count($fieldcheckresult->missing_fields))
            {
                return redirect()->back()->with('danger', 'Invalid payload JSON or all required fields are missing. Check your data.');
            }
        
            //tell the user which fields they missed
            return redirect()->back()->with('danger', 'The following fields are missing in the payload field: ' . implode(", ", $fieldcheckresult->missing_fields));
        }

        //update api data
        $api->context_userid = $user->id;
        $api->type = strtolower($attributes['type']);
        $api->url = $attributes['url'];
        $api->payload = $attributes['payload'];
        $api->goalpost = $attributes['goalpost'];
        $api->active = ($attributes['active'] == 1);
        $api->save();

        //redirect back with success
        return redirect()->route('showcallsignapi', ['api' => $api->id])->with('success', 'Successfully saved Callsign API.');

    }

    public function destroy(Callsignapidetail $api)
    {
        //check permission
        if(request()->user()->cannot('manage', $api->callsign)) { abort(403); }

        //save callsign data
        $callsignraw = $api->callsign->call;

        //delete API definition
        $api->delete();

        //return back to callsign view
        return redirect()->route('showeditcallsign', ['callsign' => $callsignraw])->with('success', 'Successfully added Callsign API.');
    }

    public function runtask() {
        
        //Trigger dxcc fix
        \Illuminate\Support\Facades\Artisan::call('app:scheduled_api_pull', []);

        //return to view
        return redirect()->back()->with('success', 'Pull APIs successfull.');

    }

    public function run(Callsignapidetail $api)
    {
        //check permission
        if(request()->user()->cannot('manage', $api->callsign)) { abort(403); }

        //check if API is active
        if($api->active == false)
        {
            //return to view
            return redirect()->back()->with('danger', 'API cannot be run because it is inactive.');
        }

        //check if locks on relations prohibit api run
        if($api->contextuser->locked == true or $api->callsign->active == false)
        {
            //return to view
            return redirect()->back()->with('danger', 'Username to store upload is locked or callsign itself is not active.');
        }

        //run API
        $result = $api->pull();

        //check for general error
        if($result == null)
        {
            //return to view
            return redirect()->back()->with('danger', 'API ran into an error.');
        }

        //check if there was just plain old 0 QSOs
        if($result->overall_qso_count == 0)
        {
            //return to view
            return redirect()->back()->with('success', 'API ran successfully and returned no QSOs.');
        }

        //check for validity error
        if($result->overall_qso_count == -1)
        {
            //return to view
            return redirect()->back()->with('success', 'API ran successfully but no QSOs were inside the specified validity period of the callsign.');
        }
        
        //return to view with success message
        return redirect()->back()->with('success', 'API ran successfully and returned a new Upload with ' .  $result->overall_qso_count . ' QSO(s).');
        
    }

    public function checkpayloadfields(string $payploadstring, string $type) : stdClass
    {
        //declare return object and fill with dummy data if anything goes wrong
        $result = new stdClass();
        $result->minimum_fields = [];
        $result->missing_fields = [];
        $result->passed = false;

        //decode payloadstring as array
        $payload = json_decode($payploadstring, true);
        
        //if json decode fails, get an empty array
        $payload = $payload == null ? [] : $payload;

        //check minimum required fields depending on API type
        switch ($type) {
            case 'wavelog':
                
                //define minimum fields
                $result->minimum_fields = ['key', 'station_id'];

                //return the missing fields
                $result->missing_fields = array_diff($result->minimum_fields, array_keys($payload));

                //set flag if passed
                $result->passed = (count($result->missing_fields) == 0);
            
            default:
                //return empty result class with passed = false;
                return $result;
        }
    }

}
