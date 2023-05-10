<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Hamevent;
use Illuminate\Http\Request;

class LogcheckController extends Controller
{

    public function home()
    {
        $events = Hamevent::where('hide', 0)->orderBy('title', 'ASC')->get();

        return view('logcheck.home', ['events' => $events]);
    }

    public function chooseindex()
    {
        
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'eventid' => 'exists:hamevents,id'
        ], 
        [
            'eventid.exists' => 'This event does not exist. Sorry.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->route('home')->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //get event
        $event = Hamevent::where('id', $attributes['eventid'])->first();

        //show event
        return redirect()->route('select_logcheck', ['event' => $event->slug]);
    }
    
    public function index(Hamevent $event)
    {
        //eager-load relations
        $event->load('callsigns.dxcc');
        
        //render view
        return view('logcheck.selectcall', ['event' => $event]);
    }

    public function select(Hamevent $event)
    {
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'callsign' => 'string|max:20|min:3'
        ], 
        [
            'callsign.max' => 'The entered callsign is too long.',
            'callsign.string' => 'The callsign has to be a text.',
            'callsign.min' => 'The callsign is too short.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->route('select_logcheck', ['event' => $event->slug])->with('danger', swolf_validatorerrors($validator))->withInput();
        }

        //get validated attributes
        $attributes = $validator->validated();

        //get owner callsign 
        $callsign = getcallsignwithoutadditionalinfo($attributes['callsign']);

        //check valid callsign
        if(!preg_match("/^[A-Z, 0-9]{3,20}$/", $callsign))
        {
            return redirect()->route('select_logcheck', ['event' => $event->slug])->with('danger', 'Callsign contains invalid characters.')->withInput();
        }

        //return redirect
        return redirect()->route('singlelogcheck', ['event' => $event->slug, 'callsign' => $callsign]);
    }

    public function check(Hamevent $event, string $callsign)
    {
        $checkvalues = ['callsign' => $callsign];
        
        $validator = \Illuminate\Support\Facades\Validator::make($checkvalues, [
            'callsign' => 'string|max:20|min:3'
        ], 
        [
            'callsign.max' => 'The entered callsign is too long.',
            'callsign.string' => 'The callsign has to be a text.',
            'callsign.min' => 'The callsign is too short.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->route('select_logcheck', ['event' => $event->slug])->with('danger', swolf_validatorerrors($validator))->withInput();
        }

        //check valid callsign
        if(!preg_match("/^[A-Z, 0-9]{3,20}$/", $callsign))
        {
            return redirect()->route('select_logcheck', ['event' => $event->slug])->with('danger', 'Callsign contains invalid characters.')->withInput();
        }
        
        //get awards in ranking order
        $awards = $event->awards()->where('active', 1)->orderBy('ranking', 'ASC')->get();

        //get all contacts for this event
        $contacts = $event->getcontactsfor($callsign);

        //return view
        return view('logcheck.check', ['event' => $event, 'callsign' => $callsign, 'awards' => $awards, 'contacts' => $contacts]);
    }
}
