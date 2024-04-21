<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\Awardtimeframe;
use Illuminate\Http\Request;

class AwardtimeframeController extends Controller
{
    public function create(Award $award)
    {
        //check permission
        if(request()->user()->cannot('edit', $award->event)) { abort(403); }

        //Validation
        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'start' => 'date|required',
            'end' => 'date|required'

        ], 
        [
            'start.date' => 'Start must be a valid datetime.',
            'end.date' => 'End must be a valid datetime.',
            'start.required' => 'Start must be a not empty.',
            'end.required' => 'End must be a not empty.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //create timeframe
        $awardtimeframe = new Awardtimeframe();
        $awardtimeframe->award_id = $award->id;
        $awardtimeframe->start = \Carbon\Carbon::parse($attributes['start']);
        $awardtimeframe->end = \Carbon\Carbon::parse($attributes['end']);
        $awardtimeframe->save();

        //return back
        return redirect()->back()->with('success', 'Added timeframe successfully.');

    }

    public function delete(Awardtimeframe $awardtimeframe)
    {
        $awardtimeframe->delete();
        return redirect()->back()->with('success', 'Timeframe successfully deleted.');
    }
}
