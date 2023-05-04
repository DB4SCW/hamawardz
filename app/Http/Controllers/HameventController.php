<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use App\Models\Hamevent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HameventController extends Controller
{
    public function index()
    {

        //load all events if siteadmin
        $siteadmin = auth()->user()->siteadmin;
        if($siteadmin)
        {
            $events = Hamevent::with('awards.issued_awards', 'callsigns', 'creator', 'eventmanagers')->get();
            //return view
            return view('hamevent.list', ['events' => $events]);
        }else{
            $events = auth()->user()->events_to_manage()->with('awards.issued_awards', 'callsigns', 'creator', 'eventmanagers')->get();

            //Sanity-check
            if($events->count() < 1)
            {
                return back()->with('danger', 'You are not allowed to manage events on this website.');
            }

            //return view
            return view('hamevent.list', ['events' => $events]);
        }

    }
    
    public function showcreate()
    {
        //permission check for creation
        $check = $this->permissioncheck_create();
        if($check != null)
        {
            return $check;
        }
        
        //return view
        return view('hamevent.create');
    }

    public function create()
    {
        
        //permission check for creation
        $check = $this->permissioncheck_create();
        if($check != null)
        {
            return $check;
        }

        //manipulate the slug before validation
        $requestinput = request()->all();
        $requestinput['slug'] = $requestinput['slug'] == '' ? Str::slug($requestinput['title']) : Str::slug($requestinput['slug']);

        $validator = \Illuminate\Support\Facades\Validator::make($requestinput, [
            'title' => 'string|min:5|max:200|unique:hamevents,title',
            'slug' => 'string|min:3|max:200|unique:hamevents,slug',
            'description' => 'nullable|string',
            'start' => 'date', 
            'end' => 'date'
        ], 
        [
            'title.unique' => 'This event title does already exist.',
            'slug.unique' => 'This event slug does already exist.',
            'title.min' => 'The event title must contain at least 5 characters.',
            'slug.min' => 'The event slug must contain at least 3 characters.',
            'title.max' => 'The event title must be less than 200 characters.',
            'slug.max' => 'The event slug must be less than 200 characters.',
            'title.string' => 'The event title must be a valid text.',
            'slug.string' => 'The event slug must be a valid text.',
            'start.date' => 'The event start must be a valid datetime.',
            'end.date' => 'The event start must be a valid datetime.',
            'description.string' => 'Description has to be a string'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator))->withInput();
        }

        //get validated attributes
        $attributes = $validator->validated();

        //create new event
        $event = new Hamevent();

        //fill event
        $event->title = $attributes['title'];
        $event->slug = $attributes['slug'];
        $event->description = array_key_exists('description', $attributes) ? $attributes['description'] : null;
        $event->creator_id = auth()->user()->id;
        $event->start = \Carbon\Carbon::parse($attributes['start']);
        $event->end = \Carbon\Carbon::parse($attributes['end']);
        $event->updated_at = \Carbon\Carbon::now();
        $event->save();

        //Add creator to event managers
        $event->eventmanagers()->attach(auth()->user());

        //return to edit UI
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'Event ' . $event->title . ' has been saved successfully.');
    }

    public function showedit(Hamevent $event)
    {
        //permission check
        $permissioncheck = $this->permissioncheck($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }
        
        //return view
        return view('hamevent.edit', ['event' => $event]);
    }

    public function edit(Hamevent $event)
    {
        //permission check
        $permissioncheck = $this->permissioncheck($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //manipulate the slug before validation
        $requestinput = request()->all();
        $requestinput['slug'] = $requestinput['slug'] == '' ? Str::slug($requestinput['title']) : Str::slug($requestinput['slug']);

        $validator = \Illuminate\Support\Facades\Validator::make($requestinput, [
            'title' => 'string|min:5|max:200|unique:hamevents,title,' . $event->id,
            'slug' => 'string|min:3|max:200|unique:hamevents,slug,' . $event->id,
            'description' => 'nullable|string',
            'start' => 'date', 
            'end' => 'date'
        ], 
        [
            'title.unique' => 'This event title does already exist.',
            'slug.unique' => 'This event slug does already exist.',
            'title.min' => 'The event title must contain at least 5 characters.',
            'slug.min' => 'The event slug must contain at least 3 characters.',
            'title.max' => 'The event title must be less than 200 characters.',
            'slug.max' => 'The event slug must be less than 200 characters.',
            'title.string' => 'The event title must be a valid text.',
            'slug.string' => 'The event slug must be a valid text.',
            'start.date' => 'The event start must be a valid datetime.',
            'end.date' => 'The event start must be a valid datetime.',
            'description.string' => 'Description has to be a string'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //update event
        $event->title = $attributes['title'];
        $event->slug = $attributes['slug'];
        $event->description = array_key_exists('description', $attributes) ? $attributes['description'] : null;
        $event->start = \Carbon\Carbon::parse($attributes['start']);
        $event->end = \Carbon\Carbon::parse($attributes['end']);
        $event->updated_at = \Carbon\Carbon::now();
        $event->save();

        //return to list
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'Event ' . $event->title . ' has been saved successfully.');
    }

    public function permissioncheck(Hamevent $event)
    {
        //permission check
        if(!auth()->user()->siteadmin)
        {
            if($event->creator_id != auth()->user()->id)
            {
                if(!$event->eventmanagers->contains(auth()->user()))
                {
                    return redirect()->back()->with('danger', 'You do not have permission to edit this event.');
                }
            }
        }

        return null;
    }

    public function permissioncheck_create()
    {
        if(!auth()->user()->siteadmin)
        {
            if(!auth()->user()->cancreateevents)
            {
                return back()->with('danger', 'You are not allowed to create events on this website.');
            }
        }

        return null;
    }

    public function removemanager(Hamevent $event, int $managerid)
    {

        //permission check
        $permissioncheck = $this->permissioncheck($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }
        
        //dont know why I can't use the autofind in function header but here we are...
        try {
            $manager = User::findOrFail($managerid);
        } catch (\Throwable $th) {
            abort(404);
        }
        
        //check if User is even a current eventmanger
        if(!$event->eventmanagers->contains($manager))
        {
            return redirect()->back()->with('danger', 'Nice try.');
        }

        //sanity-Check
        if($manager->siteadmin || $event->creator_id == $manager->id || $manager->id == auth()->user()->id)
        {
            return redirect()->route('showeditevent', ['event' => $event->slug])->with('danger', 'You cannot remove admins, event creators or yourself from this list.');
        }

        //delete manager permission
        $event->eventmanagers()->detach($manager);

        //return to view
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'User ' . $manager->username . ' is no longer an event manager.');

    }

    public function addmanager(Hamevent $event)
    {
        //permission check
        $permissioncheck = $this->permissioncheck($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }        
        
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
            return redirect()->back()->with('danger', swolf_validatorerrors($validator))->withInput();
        }

        //get validated attributes
        $attributes = $validator->validated();

        $new_managers = User::whereRaw('LOWER(username) = ?', [strtolower($attributes['username'])])->get();

        if($new_managers->count() != 1)
        {
            return redirect()->back()->with('danger', 'Username not found.')->withInput();
        }

        //Load new manager
        $new_manager = $new_managers->first();

        //Sanity check
        if($event->eventmanagers->contains($new_manager))
        {
            return redirect()->back()->with('danger', 'User is already a registered event manager.')->withInput();
        }

        //attach permission
        $event->eventmanagers()->attach($new_manager);

        //return to view
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'User ' . $new_manager->username . ' is now an event manager.');

    }

    public function removeeventparticipant(Hamevent $event, Callsign $callsign)
    {

        //permission check
        $permissioncheck = $this->permissioncheck($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }
        
        //check if User is even a current eventmanger
        if(!$event->callsigns->contains($callsign))
        {
            return redirect()->back()->with('danger', 'Nice try.');
        }

        //delete manager permission
        $event->callsigns()->detach($callsign);

        //return to view
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'Callsign ' . $callsign->call . ' is no longer an event participant.');

    }

    public function addeventparticipant(Hamevent $event)
    {
        //permission check
        $permissioncheck = $this->permissioncheck($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }        
        
        //validate
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'callsign' => 'string|min:3|max:255'
        ], 
        [
            'callsign.min' => 'Invalid callsign. Too short!',
            'callsign.max' => 'Invalid callsign. Too long!',
            'callsign.string' => 'Invalid callsign. Must be a text!'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator))->withInput();
        }

        //get validated attributes
        $attributes = $validator->validated();

        $new_participants = Callsign::whereRaw("LOWER(`call`) = ?", [strtolower($attributes['callsign'])])->get();

        if($new_participants->count() != 1)
        {
            return redirect()->back()->with('danger', 'Callsign not found.')->withInput();
        }

        //Load new manager
        $new_participant = $new_participants->first();

        //Sanity check
        if($event->callsigns->contains($new_participant))
        {
            return redirect()->back()->with('danger', 'Callsign is already a registered event participant.')->withInput();
        }

        //attach permission
        $event->callsigns()->attach($new_participant);

        //return to view
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'User ' . $new_participant->call . ' is now an event participant.');

    }

    public function destroy(Hamevent $event)
    {
        //permission check
        $permissioncheck = $this->permissioncheck($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }      

        //check relations
        if($event->callsigns->count() > 0 || $event->awards->count() > 0)
        {
            return redirect()->back()->with('danger', 'This event still has callsigns and/or awards registered to it. Please delete that first.');
        }

        //detach all event managers
        $event->eventmanagers()->sync([]);

        //Delete the event
        $event->delete();

        //go back to list
        return redirect()->route('listevents')->with('Event was successfully deleted.');
    }
}
