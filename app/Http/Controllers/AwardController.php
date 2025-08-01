<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Awardlog;
use App\Models\Dxcc;
use App\Models\Hamevent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PDF;
use stdClass;

class AwardController extends Controller
{

    //possible text colors on awards
    const TEXT_COLORS = ['black', 'white', 'red', 'green', 'blue', 'yellow'];

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
            return redirect()->back()->with('danger', db4scw_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //get data from attributes
        $callsign = $attributes['callsign'];
        $chosenname = array_key_exists('chosenname', $attributes) ? $attributes['chosenname'] : '';

        //Prepare data array
        $data = [
            'callsign' => $callsign, //str_replace("0", "Ø", $callsign),
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
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }    

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

    public function previewexample(Award $award)
    {
        //check permissions
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }    

        //Prepare data array
        $data = [
            'callsign' => 'P51KJU', 
            'chosenname' => 'Kim Jong-un', 
            'issue_datetime' => \Carbon\Carbon::now(),
            'award' => $award
        ];

        //stream PDF
        return view('award', ['data' => $data, 'award' => $award, 'callsign' => $data['callsign'], 'chosenname' => $data['chosenname'], 'issue_datetime' => $data['issue_datetime']]);
    }

    public function duplicate(Award $award)
    {
        //check permissions
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }

        //create a not-yet-saved duplicate of the award
        $new_award = $award->duplicate();

        //save it
        $new_award->save();

        //return to award view
        return redirect()->route('showeditaward', ['award' => $new_award->slug])->with('success', 'Award was successfully duplicated.');

    }
    
    public function showcreate(Hamevent $event)
    {
        //check permissions
        if(request()->user()->cannot('edit', $event)) { abort(403); }
        
        //Load all DXCCs
        $dxccs = Dxcc::orderBy('name', 'ASC')->get();

        //return to event view
        return view('award.create', ['event' => $event, 'dxccs' => $dxccs, 'text_colors' => self::TEXT_COLORS]);
    }

    public function destroy(Award $award)
    {
        //check permissions
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }

        //dont delete if there are already awards printed
        if($award->issued_awards->count() > 0)
        {
            return redirect()->back()->with('danger', 'There are already awards issued for this award.');
        }

        //save event
        $event = $award->event;
        
        //delete awardbackground
        if($award->background_image != 'Blank.jpg'){
            if(\Illuminate\Support\Facades\File::exists(str_replace('public/', 'storage/', $award->background_image))){
                \Illuminate\Support\Facades\File::delete(str_replace('public/', 'storage/', $award->background_image));
            }
        }

        //delete award
        $award->delete();

        //return to event view
        return redirect()->route('showeditevent', ['event' => $event->slug])->with('success', 'Award was deleted successfully.');
    }

    public function create(Hamevent $event)
    {
        //check permissions
        if(request()->user()->cannot('edit', $event)) { abort(403); }

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
        $award->excluded_callsigns = db4scw_sanitizecallsignstring($attributes['excluded_callsigns']);
        $award->callsign_top_percent = $attributes['callsign_top_percent'];
        $award->callsign_bold = $attributes['callsign_bold'];
        $award->callsign_font_size_px = $attributes['callsign_font_size_px'];
        $award->chosen_name_top_percent = $attributes['chosen_name_top_percent'];
        $award->chosen_name_bold = $attributes['chosen_name_bold'];
        $award->chosen_name_font_size_px = $attributes['chosen_name_font_size_px'];
        $award->datetime_print = $attributes['datetime_print'];
        $award->datetime_top_percent = $attributes['datetime_top_percent'];
        $award->datetime_left_percent = $attributes['datetime_left_percent'];
        $award->datetime_font_size_px = $attributes['datetime_font_size_px'];
        $award->active = $attributes['active'];
        $award->dxcc_id = $attributes['dxcc_id'];
        $award->dxcc_querystring = $attributes['dxcc_querystring'];
        $award->callsign_centered_horizontal = $attributes['callsign_centered_horizontal'];
        $award->callsign_left_percent = $attributes['callsign_centered_horizontal'] == 1 ? null : $attributes['callsign_left_percent'];
        $award->chosen_name_centered_horizontal = $attributes['chosen_name_centered_horizontal'];
        $award->chosen_name_left_percent = $attributes['chosen_name_centered_horizontal'] == 1 ? null : $attributes['chosen_name_left_percent'];
        $award->callsign_text_color = $attributes['callsign_text_color'];
        $award->chosen_name_text_color = $attributes['chosen_name_text_color'];
        $award->datetime_text_color = $attributes['datetime_text_color'];

        //Save award
        $award->save();

        //return to award view
        return redirect()->route('showeditaward', ['award' => $award->slug])->with('success', 'Award was successfully created.');

    }

    public function showedit(Award $award)
    {
        //check permissions
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }

        //load issued awards
        $award->load('issued_awards');

        //Load all DXCCs
        $dxccs = Dxcc::orderBy('name', 'ASC')->get();

        //return view
        return view('award.edit', ['award' => $award, 'dxccs' => $dxccs, 'text_colors' => self::TEXT_COLORS]);

    }

    public function edit(Award $award)
    {
        //check permissions
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }

        //Validate request
        $validationresult = $this->validateawardrequest(request()->all(), $award->id);

        //return redirect if failed
        if(!$validationresult->result)
        {
            return $validationresult->redirect;
        }

        //fetch attributes
        $attributes = $validationresult->attributes;

        //delete all award subtimeframes if award no longer uses this ruleset
        if($award->mode == 9 and $attributes['mode'] != 9)
        {
            foreach ($award->awardtimeframes as $timeframe) {
                $timeframe->delete();
            }
        }

        //edit attributes of award
        $award->updated_at = \Carbon\Carbon::now();
        $award->slug = $attributes['slug'];
        $award->title = $attributes['title'];
        $award->description = $attributes['description'];
        $award->ranking = $attributes['ranking'];
        $award->mode = $attributes['mode'];
        $award->min_threshold = $attributes['min_threshold'];
        $award->excluded_callsigns = db4scw_sanitizecallsignstring($attributes['excluded_callsigns']);
        $award->callsign_top_percent = $attributes['callsign_top_percent'];
        $award->callsign_bold = $attributes['callsign_bold'];
        $award->callsign_font_size_px = $attributes['callsign_font_size_px'];
        $award->chosen_name_top_percent = $attributes['chosen_name_top_percent'];
        $award->chosen_name_bold = $attributes['chosen_name_bold'];
        $award->chosen_name_font_size_px = $attributes['chosen_name_font_size_px'];
        $award->datetime_print = $attributes['datetime_print'];
        $award->datetime_top_percent = $attributes['datetime_top_percent'];
        $award->datetime_left_percent = $attributes['datetime_left_percent'];
        $award->datetime_font_size_px = $attributes['datetime_font_size_px'];
        $award->active = $attributes['active'];
        $award->dxcc_id = $attributes['dxcc_id'];
        $award->dxcc_querystring = $attributes['dxcc_querystring'];
        $award->callsign_centered_horizontal = $attributes['callsign_centered_horizontal'];
        $award->callsign_left_percent = $attributes['callsign_centered_horizontal'] == 1 ? null : $attributes['callsign_left_percent'];
        $award->chosen_name_centered_horizontal = $attributes['chosen_name_centered_horizontal'];
        $award->chosen_name_left_percent = $attributes['chosen_name_centered_horizontal'] == 1 ? null : $attributes['chosen_name_left_percent'];
        $award->callsign_text_color = $attributes['callsign_text_color'];
        $award->chosen_name_text_color = $attributes['chosen_name_text_color'];
        $award->datetime_text_color = $attributes['datetime_text_color'];

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
            'mode' => 'integer|min:0|max:' . db4scw_getmaxmode(),
            'min_threshold' => 'integer|min:1',
            'excluded_callsigns' => 'string|max:255|nullable',
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
            'dxcc_id' => 'nullable|exists:dxccs,id',
            'dxcc_querystring' => 'nullable|string|max:20',
            'datetime_print' => 'integer|min:0|max:1',
            'callsign_left_percent' => 'decimal:0,3|min:0|max:100|nullable',
            'chosen_name_left_percent' => 'decimal:0,3|min:0|max:100|nullable',
            'callsign_centered_horizontal' => 'integer|min:0|max:1',
            'chosen_name_centered_horizontal' => 'integer|min:0|max:1',
            'callsign_text_color' => ['required', Rule::in(self::TEXT_COLORS)],
            'chosen_name_text_color' => ['required', Rule::in(self::TEXT_COLORS)],
            'datetime_text_color' => ['required', Rule::in(self::TEXT_COLORS)]
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
            'excluded_callsigns.string' => 'Excluded callsigns must be a valid string.',
            'excluded_callsigns.max' => 'List of excluded callsigns may not be longer than 255 characters.',
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
            'active.max' => 'Active must be yes or no.',
            'dxcc_id.exists' => 'Invalid DXCC.',
            'dxcc_querystring.string' => 'DXCC Querystring must be a text.',
            'dxcc_querystring.max' => 'DXCC Querystring must be at most 20 characters long.',
            'datetime_print.integer' => 'Invalid datetime print type.',
            'datetime_print.min' => 'Invalid datetime print type.',
            'datetime_print.max' => 'Invalid datetime print type.',
            'callsign_centered_horizontal.integer' => 'Invalid callsign center type.',
            'callsign_centered_horizontal.min' => 'Invalid callsign center type.',
            'callsign_centered_horizontal.max' => 'Invalid callsign center type.',
            'chosen_name_centered_horizontal.integer' => 'Invalid chosen name center type.',
            'chosen_name_centered_horizontal.min' => 'Invalid chosen name center type.',
            'chosen_name_centered_horizontal.max' => 'Invalid chosen name center type.',
            'callsign_left_percent.decimal' => 'Callsign left % must be a number.',
            'callsign_left_percent.min' => 'Callsign left % must be at least 0%.',
            'callsign_left_percent.max' => 'Callsign left % must be at most 100%.',
            'chosen_name_left_percent.decimal' => 'Chosen name left % must be a number.',
            'chosen_name_left_percent.min' => 'Chosen name left % must be at least 0%.',
            'chosen_name_left_percent.max' => 'Chosen name left % must be at most 100%.',
            'callsign_text_color.in' => 'Invalid callsign text color',
            'chosen_name_text_color.in' => 'Invalid chosen name text color',
            'datetime_text_color.in' => 'Invalid datetime text color'
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

    public function uploadbackground(Award $award)
    {
        
        //check permissions
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }
        
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
            return redirect()->back()->with('danger', db4scw_validatorerrors($validator));
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
