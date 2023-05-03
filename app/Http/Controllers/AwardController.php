<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Awardlog;
use App\Models\Hamevent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;
use stdClass;

class AwardController extends Controller
{

    public function print(Award $award)
    {
        
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'callsign' => 'string|min:3',
            'chosenname' => 'nullable|string|max:50'
        ], 
        [
            'callsign.min' => 'This callsign is too short.',
            'callsign.string' => 'Callsign has to be a text.',
            'chosenname.string' => 'Your chosen name has to be a text.',
            'chosenname.max' => 'Your chosen name is too long.',
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //get data from attributes
        $callsign = $attributes['callsign'];
        $chosenname = array_key_exists('chosenname', $attributes) ? $attributes['chosenname'] : '';

        //Prepare data array
        $data = [
            'callsign' => $callsign,
            'chosenname' => $chosenname, 
            'issue_datetime' => \Carbon\Carbon::now(),
            'award' => $award
        ];

        //check if Callsign is eligable for chosen award
        if(!$award->eligible($callsign))
        {
            return redirect()->back()->with('danger', 'Nice try mate.');
        }

        //Keep log of handed out awards
        $log = Awardlog::where('award_id', $award->id)->where('callsign', $callsign)->first();

        //if no log is present up to now, create a new one.
        if($log == null)
        {
            $log = new Awardlog();
            $log->award_id = $award->id;
            $log->callsign = $callsign;
        }
        
        $log->updated_at = \Carbon\Carbon::now();
        $log->chosen_name = substr($chosenname, 0, 200);
        $log->save();

        //create PDF View
        $pdf = PDF::loadView('award', $data, [], [
            'margin_left'                => 0,
            'margin_right'               => 0,
            'margin_top'                 => 0,
            'margin_bottom'              => 0,
            'format'                     => 'A4',
            'orientation'                => 'L'  
        ]);

        //stream PDF
        return $pdf->stream( $data['issue_datetime']->format('Ymd') . '_' . $callsign . '_' . $award->slug . '_award.pdf');
    }

    public function printexample(Award $award)
    {
        
        //check permissions
        $permissioncheck = $this->checkpermissions($award->event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Prepare data array
        $data = [
            'callsign' => 'P51KJU', 
            'chosenname' => 'Kim Jong-un', 
            'issue_datetime' => \Carbon\Carbon::now(),
            'award' => $award
        ];

        //create PDF View
        $pdf = PDF::loadView('award', $data, [], [
            'margin_left'                => 0,
            'margin_right'               => 0,
            'margin_top'                 => 0,
            'margin_bottom'              => 0,
            'format'                     => 'A4',
            'orientation'                => 'L'  
        ]);

        //stream PDF
        return $pdf->stream( $data['issue_datetime']->format('Ymd') . '_EXAMPLE_' . $award->slug . '_award.pdf');
    }

    public function checkpermissions(Hamevent $event)
    {
        if(!auth()->user()->siteadmin)
        {
            if(!auth()->user()->id == $event->creator_id ) 
            {
                if(!$event->eventmanagers->contains(auth()->user()))
                {
                    return redirect()->back()->with('danger', 'You do not have event manager permissions for this event.');
                }
            }
        }

        return null;
    }

    public function showcreate(Hamevent $event)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }
        
        //return to event view
        return view('award.create', ['event' => $event]);
    }

    public function destroy(Award $award)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions($award->event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //dont delete if there are already awards printed
        if($award->issued_awards->count() > 0)
        {
            return redirect()->back()->with('danger', 'There are already awards issued for this award.');
        }

        //save event
        $event = $award->event;

        //delete award
        $award->delete();

        //return to event view
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'Award was deleted successfully.');
    }

    public function create(Hamevent $event)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions($event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Validate request
        $validationresult = $this->validateawardrequest(request()->all());

        //return redirect if failed
        if(!$validationresult->result)
        {
            return $validationresult->redirect;
        }

        //fetch attributes
        $attributes = $validationresult->attributes;

        //Create new award
        $award = new Award();

        //Fill award
        $award->creator_id = auth()->user()->id;
        $award->hamevent_id = $event->id;
        $award->slug = $attributes['slug'];
        $award->title = $attributes['title'];
        $award->description = $attributes['description'];
        $award->ranking = $attributes['ranking'];
        $award->mode = $attributes['mode'];
        $award->min_threshold = $attributes['min_threshold'];
        $award->callsign_top_percent = $attributes['callsign_top_percent'];
        $award->callsign_bold = $attributes['callsign_bold'];
        $award->callsign_font_size_px = $attributes['callsign_font_size_px'];
        $award->chosen_name_top_percent = $attributes['chosen_name_top_percent'];
        $award->chosen_name_bold = $attributes['chosen_name_bold'];
        $award->chosen_name_font_size_px = $attributes['chosen_name_font_size_px'];
        $award->datetime_top_percent = $attributes['datetime_top_percent'];
        $award->datetime_left_percent = $attributes['datetime_left_percent'];
        $award->datetime_font_size_px = $attributes['datetime_font_size_px'];
        $award->active = $attributes['active'];

        //Save award
        $award->save();

        //return to award view
        return redirect()->route('showeditaward', ['award' => $award->slug])->with('success', 'Award was successfully created.');

    }

    public function showedit(Award $award)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions($award->event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //load issued awards
        $award->load('issued_awards');

        //return view
        return view('award.edit', ['award' => $award]);

    }

    public function edit(Award $award)
    {
        //check permissions
        $permissioncheck = $this->checkpermissions($award->event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }

        //Validate request
        $validationresult = $this->validateawardrequest(request()->all(), $award->id);

        //return redirect if failed
        if(!$validationresult->result)
        {
            return $validationresult->redirect;
        }

        //fetch attributes
        $attributes = $validationresult->attributes;

        $award->updated_at = \Carbon\Carbon::now();
        $award->slug = $attributes['slug'];
        $award->title = $attributes['title'];
        $award->description = $attributes['description'];
        $award->ranking = $attributes['ranking'];
        $award->mode = $attributes['mode'];
        $award->min_threshold = $attributes['min_threshold'];
        $award->callsign_top_percent = $attributes['callsign_top_percent'];
        $award->callsign_bold = $attributes['callsign_bold'];
        $award->callsign_font_size_px = $attributes['callsign_font_size_px'];
        $award->chosen_name_top_percent = $attributes['chosen_name_top_percent'];
        $award->chosen_name_bold = $attributes['chosen_name_bold'];
        $award->chosen_name_font_size_px = $attributes['chosen_name_font_size_px'];
        $award->datetime_top_percent = $attributes['datetime_top_percent'];
        $award->datetime_left_percent = $attributes['datetime_left_percent'];
        $award->datetime_font_size_px = $attributes['datetime_font_size_px'];
        $award->active = $attributes['active'];

        //save award
        $award->save();

        //return to award view
        return redirect()->route('showeditaward', ['award' => $award->slug])->with('success', 'Award was saved successfully.');

    }

    public function validateawardrequest($requestinput, $awardid = null) : stdClass
    {
        //manipulate the slug before validation
        $requestinput['slug'] = $requestinput['slug'] == '' ? Str::slug($requestinput['title']) : Str::slug($requestinput['slug']);

        //validate
        $validator = \Illuminate\Support\Facades\Validator::make($requestinput, [
            'title' => 'string|min:3|max:200|unique:awards,title' . ($awardid != null ? (',' . $awardid) : ''),
            'slug' => 'string|min:3|max:200|unique:awards,slug' . ($awardid != null ? (',' . $awardid) : ''),
            'description' => 'string|max:200',
            'ranking' => 'integer|min:0',
            'mode' => 'integer|min:0|max:' . swolf_getmaxmode(),
            'min_threshold' => 'integer|min:1',
            'callsign_top_percent' => 'decimal:0,3|min:0|max:100',
            'callsign_bold' => 'integer|min:0|max:1',
            'callsign_font_size_px' => 'integer|min:1',
            'chosen_name_top_percent' => 'decimal:0,3|min:0|max:100',
            'chosen_name_bold' => 'integer|min:0|max:1',
            'chosen_name_font_size_px' => 'integer|min:1',
            'datetime_top_percent' => 'decimal:0,3|min:0|max:100',
            'datetime_left_percent' => 'decimal:0,3|min:0|max:100',
            'datetime_font_size_px' => 'integer|min:1',
            'active' => 'integer|min:0|max:1',
        ], 
        [
            'title.string' => 'Title must be a text.',
            'title.min' => 'Title must be at least 3 characters long.',
            'title.max' => 'Title must be at most 200 characters long.',
            'title.unique' => 'This title already exists.',
            'slug.string' => 'Slug must be a text.',
            'slug.min' => 'Slug must be at least 3 characters long.',
            'slug.max' => 'Slug must be at most 200 characters long.',
            'slug.unique' => 'Slug title already exists.',
            'description.string' => 'Description must a text.',
            'description.max' => 'Description must be at most 200 characters long.',
            'ranking.integer' => 'Ranking must be a whole number',
            'ranking.min' => 'Ranking must be a positive number',
            'mode.integer' => 'Invalid mode.',
            'mode.min' => 'Invalid mode.',
            'mode.max' => 'Invalid mode.',
            'min_threshold.integer' => 'Threshold must be a whole number.',
            'min_threshold.min' => 'Threshold must be a positive number.',
            'callsign_top_percent.decimal' => 'Callsign Top % must be a number.',
            'callsign_top_percent.min' => 'Callsign Top % must be at least 0%.',
            'callsign_top_percent.max' => 'Callsign Top % must be at most 100%.',
            'callsign_bold.integer' => 'Invalid boldness type for callsign.',
            'callsign_bold.min' => 'Invalid boldness type for callsign.',
            'callsign_bold.max' => 'Invalid boldness type for callsign.',
            'callsign_font_size_px.integer' => 'Callsign font size must be a number.',
            'callsign_font_size_px.min' => 'Callsign font size must be at least 1',
            'chosen_name_top_percent.decimal' => 'Chosen name Top % must be a number.',
            'chosen_name_top_percent.min' => 'Chosen name Top % must be at least 0%.',
            'chosen_name_top_percent.max' => 'Chosen name Top % must be at most 100%.',
            'chosen_name_bold.integer' => 'Invalid boldness type for chosen name.',
            'chosen_name_bold.min' => 'Invalid boldness type for chosen name.',
            'chosen_name_bold.max' => 'Invalid boldness type for chosen name.',
            'chosen_name_font_size_px.integer' => 'Chosen name font size must be a number.',
            'chosen_name_font_size_px.min' => 'Chosen name font size must be at least 1',
            'datetime_top_percent.decimal' => 'Datetime Top % must be a number.',
            'datetime_top_percent.min' => 'Datetime Top % must be at least 0%.',
            'datetime_top_percent.max' => 'Datetime Top % must be at most 100%.',
            'datetime_left_percent.decimal' => 'Datetime Left % must be a number.',
            'datetime_left_percent.min' => 'Datetime Left % must be at least 0%.',
            'datetime_left_percent.max' => 'Datetime Left % must be at most 100%.',
            'datetime_font_size_px.integer' => 'Datetime font size must be a number.',
            'datetime_font_size_px.min' => 'Datetime font size must be at least 1',
            'active.integer' => 'Active must be yes or no.', 
            'active.min' => 'Active must be yes or no.', 
            'active.max' => 'Active must be yes or no.'
        ]);

        //open return class
        $returnvalue = new stdClass;

        //handle validation failure
        if ($validator->fails()) {
            $returnvalue->redirect = redirect()->back()->with('danger', swolf_validatorerrors($validator))->withInput();
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

    public function uploadbackground(Award $award)
    {
        
        //check permissions
        $permissioncheck = $this->checkpermissions($award->event);
        if($permissioncheck != null)
        {
            return $permissioncheck;
        }
        
        //Validation
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'file' => 'required|image|mimes:png,jpg,jpeg' 

        ], 
        [
            'file.image' => 'Background must be an image file.',
            'file.mimes' => 'Background must be an image file.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //load image
        $image = request()->file('file');

        //save current background image
        $current_background = $award->background_image;

        //store image
        $path = $image->store('public/images');

        //delete current background, if not default blank
        //don't ask me why this storage shit is so complicated...
        if($current_background != 'Blank.jpg'){
            if(\Illuminate\Support\Facades\File::exists(str_replace('public/', 'storage/', $current_background))){
                \Illuminate\Support\Facades\File::delete(str_replace('public/', 'storage/', $current_background));
            }
        }

        //set new path information
        $award->background_image = $path;
        $award->updated_at = \Carbon\Carbon::now();
        $award->save();

        //return to award
        return redirect()->route('showeditaward', ['award' => $award->slug])->with('success', 'Background image was changed successfully.');
    }

}
