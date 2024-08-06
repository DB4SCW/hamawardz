<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use App\Models\User;
use App\Models\Callsignapidetail;
use Illuminate\Http\Request;

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
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
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

        //create API config and save
        $api = new Callsignapidetail();
        $api->callsign_id = $callsign->id;
        $api->context_userid = $user->id;
        $api->type = $attributes['type'];
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
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
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

        //update api data
        $api->context_userid = $user->id;
        $api->type = $attributes['type'];
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

        //run API
        $result = $api->pull();

        //return result
        if($result != null)
        {
            //return to view
            return redirect()->back()->with('success', 'API ran successfully and returned a new Upload.');
        }else{
            //return to view
            return redirect()->back()->with('danger', 'API ran into an error or did not return any QSOs.');
        }
    }

}
