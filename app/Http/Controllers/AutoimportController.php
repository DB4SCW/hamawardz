<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use Illuminate\Http\Request;
use \App\Models\Autoimport;
use stdClass;

class AutoimportController extends Controller
{
    public function trigger()
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }
        
        //Trigger autoimport
        \Illuminate\Support\Facades\Artisan::call('app:scheduled_autoimport', []);

        //return to view
        return redirect()->route('participant_dashboard')->with('success', 'Autoimport successful.');
    }
    
    public function index()
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }
        
        //Get all autoimport configs
        $autoimports = Autoimport::with('callsign')->get();

        //Load view
        return view('autoimport.list', ['autoimports' => $autoimports]);
    }

    
    public function showedit(Autoimport $autoimport)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Load active callsigns
        $callsigns = Callsign::where('active', 1)->orderBy('call', 'ASC')->get();

        //Load view
        return view('autoimport.edit', ['callsigns' => $callsigns, 'autoimport' => $autoimport]);
    }

    public function edit(Autoimport $autoimport)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Validate request
        $validationresult = $this->validateautoimportdata(request()->all(), $autoimport->id);

        //return redirect if failed
        if(!$validationresult->result)
        {
            return $validationresult->redirect;
        }

        //fetch attributes
        $attributes = $validationresult->attributes;

        //edit fields
        $autoimport->databasename = $attributes['databasename'];
        $autoimport->tablename = $attributes['tablename'];
        $autoimport->operator = $attributes['operator'];
        $autoimport->qsodate = $attributes['qsodate'];
        $autoimport->qsotime = $attributes['qsotime'];
        $autoimport->qsopartner_callsign = $attributes['qsopartner_callsign'];
        $autoimport->frequency = $attributes['frequency'];
        $autoimport->band = $attributes['band'];
        $autoimport->mode = $attributes['mode'];
        $autoimport->rst_s = $attributes['rst_s'];
        $autoimport->rst_r = $attributes['rst_r'];
        $autoimport->dxcc = $attributes['dxcc'];
        $autoimport->table_id = $attributes['table_id'];
        $autoimport->callsign_id = $attributes['callsign_id'];
        $autoimport->active = $attributes['active'];
        $autoimport->save();

        //return to config view
        return redirect()->route('showeditautoimport', ['autoimport' => $autoimport->id])->with('success', 'Autoimport-Configuration was saved successfully.');
    }

    public function showcreate()
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Load active callsigns which are still valid for a new autoimport config (prevent duplicates)
        $callsigns = Callsign::where('active', 1)->whereNotIn('id', Autoimport::all()->pluck('callsign_id'))->orderBy('call', 'ASC')->get();

        //Load view
        return view('autoimport.create', ['callsigns' => $callsigns]);
    }

    public function create()
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Validate request
        $validationresult = $this->validateautoimportdata(request()->all(), null);

        //return redirect if failed
        if(!$validationresult->result)
        {
            return $validationresult->redirect;
        }

        //fetch attributes
        $attributes = $validationresult->attributes;

        //create new autoimport config
        $config = new Autoimport();
        $config->databasename = $attributes['databasename'];
        $config->tablename = $attributes['tablename'];
        $config->operator = $attributes['operator'];
        $config->qsodate = $attributes['qsodate'];
        $config->qsotime = $attributes['qsotime'];
        $config->qsopartner_callsign = $attributes['qsopartner_callsign'];
        $config->frequency = $attributes['frequency'];
        $config->band = $attributes['band'];
        $config->mode = $attributes['mode'];
        $config->rst_s = $attributes['rst_s'];
        $config->rst_r = $attributes['rst_r'];
        $config->dxcc = $attributes['dxcc'];
        $config->table_id = $attributes['table_id'];
        $config->callsign_id = $attributes['callsign_id'];
        $config->active = $attributes['active'];
        $config->save();

        //return to config view
        return redirect()->route('showeditautoimport', ['autoimport' => $config->id])->with('success', 'Autoimport-Configuration was saved successfully.');

    }

    public function destroy(Autoimport $autoimport)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Delete autoimport config
        $autoimport->delete();

        //go back to list
        return redirect()->route('listautoimports')->with('success', 'Successfully removed autoimport configuration.');

    }

    public function toggle(Autoimport $autoimport)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions();
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //toggle import
        $autoimport->active = !$autoimport->active;
        $autoimport->save();

        //return to view
        return redirect()->route('listautoimports')->with('success', 'Successfully toggled autoimport.');

    }

    public function validateautoimportdata($requestinput, $autoimport_id = null)
    {
        
        $validator = \Illuminate\Support\Facades\Validator::make($requestinput, [
            'callsign_id' => 'integer|unique:autoimports,callsign_id' . ($autoimport_id == null ? '' : ',' . $autoimport_id),
            'databasename' => 'string|min:1|max:255|unique:autoimports,databasename' . ($autoimport_id == null ? '' : ',' . $autoimport_id),
            'tablename' => 'string|min:1|max:255',
            'table_id' => 'string|max:52',
            'operator' => 'nullable|string|max:52',
            'band' => 'nullable|string|max:52',
            'qsodate' => 'string|max:52',
            'qsotime' => 'string|max:52',
            'qsopartner_callsign' => 'string|max:52',
            'frequency' => 'string|max:52',
            'mode' => 'string|max:52',
            'rst_s' => 'string|max:52',
            'rst_r' => 'string|max:52',
            'dxcc' => 'string|max:52',
            'active' => 'integer|min:0|max:1'

        ], 
        [
            //custom validation messages here... but thats admin stuff... they should know what the default ones mean
        ]);

        //open return class
        $returnvalue = new stdClass;

        //handle validation failure
        if ($validator->fails()) {
            $returnvalue->redirect = redirect()->back()->with('danger', db4scw_validatorerrors($validator))->withInput();
            $returnvalue->attributes = null;
            $returnvalue->result = false;
            return $returnvalue;
        }

        //get validated attributes
        $returnvalue->redirect = null;
        $returnvalue->result = true;
        $returnvalue->attributes = $validator->validated();

        //return validation result
        return $returnvalue;

    }

    public function checkpermissions()
    {
        if(!auth()->user()->siteadmin)
        {
            return redirect()->back()->with('danger', 'You do not have site admin permissions.');
        }

        return null;
    }
}
