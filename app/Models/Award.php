<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use App\Support\SqlHelper;

class Award extends Model
{
    use HasFactory;

    public function event() : BelongsTo
    {
        return $this->belongsTo(Hamevent::class, "hamevent_id", "id");
    }

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, "creator_id", "id");
    }

    public function issued_awards() : HasMany
    {
        return $this->hasMany(Awardlog::class);
    }

    public function awardtimeframes() : HasMany
    {
        return $this->hasMany(Awardtimeframe::class);
    }

    public function mode_text()
    {
        return db4scw_getawardmodetext($this->mode, $this->min_threshold ?? 0, $this->resets_daily);
    }

    public function eligible(string $callsign) : bool
    {
        //cannot be eligible if award is inactive   
        if(!$this->active)
        {
            return false;
        }

        //cannot be eligible if there is no threshold
        if($this->min_threshold == null)
        {
            return false;
        }

        //only eligible if aggregate count greater than threshold
        return $this->aggregate_count($callsign) >= $this->min_threshold;

    }

    public function backgroundimage_assetpath()
    {
        //return Blank image or fixed filepath
        if($this->background_image == "Blank.jpg")
        {
            return $this->background_image;
        }else{
            return str_replace('http://', 'https://', str_replace("public/", 'storage/', asset($this->background_image)));
        }
        
    }

    public function getexcludedcallsignids()
    {
        //extract excluded callsign ids to array
        $callsigns_raw = db4scw_getcallsignsfromstring($this->excluded_callsigns ?? '') ;
        $callsignids = Callsign::whereIn('call', $callsigns_raw)->get()->pluck('id');
        return $callsignids->toArray();
    }

    public function eventcallsignids()
    {
        //diff event callsign ids with excluded call ids
        return array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids());
    }


    public function aggregate_count(string $callsign) : int 
    {
    
        //get database specific date expression
        $dateExpression = SqlHelper::dateOnly('contacts.qso_datetime');

        switch ($this->mode) {
            case 0:
                return Contact::where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                        ->whereIn('callsign_id', $this->event->callsigns->pluck('id')
                        ->toArray())
                        ->count();
            case 1:
                return DB::table('contacts')->select(DB::raw("callsign_id, count(id) as count" . ($this->resets_daily ? ", $dateExpression" : "")))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', $this->eventcallsignids())
                    ->groupBy('callsign_id')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 2:
                return DB::table('contacts')
                    ->join('modes', 'modes.id', '=', 'contacts.mode_id')
                    ->select(DB::raw('contacts.callsign_id, modes.mode, count(contacts.id) as count' . ($this->resets_daily ? ", $dateExpression" : "")))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', $this->eventcallsignids())
                    ->groupBy('contacts.callsign_id', 'modes.mode')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 3:
                return DB::table('contacts')
                    ->join('bands', 'bands.id', '=', 'contacts.band_id')
                    ->select(DB::raw('contacts.callsign_id, bands.band, count(contacts.id) as count' . ($this->resets_daily ? ", $dateExpression" : "")))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', $this->eventcallsignids())
                    ->groupBy('contacts.callsign_id', 'bands.band')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 4:
                return DB::table('contacts')
                    ->join('bands', 'bands.id', '=', 'contacts.band_id')
                    ->join('modes', 'modes.id', '=', 'contacts.mode_id')
                    ->select(DB::raw('contacts.callsign_id, bands.band, modes.mode, count(contacts.id) as count . ($this->resets_daily ? ", $dateExpression" : "")'))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', $this->eventcallsignids())
                    ->groupBy('contacts.callsign_id', 'bands.band', 'modes.mode')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 5:
                return DB::table('contacts')
                    ->join('modes', 'modes.id', '=', 'contacts.mode_id')
                    ->select(DB::raw('contacts.callsign_id, modes.mainmode, count(contacts.id) as count' . ($this->resets_daily ? ", $dateExpression" : "")))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', $this->eventcallsignids())
                    ->groupBy('contacts.callsign_id', 'modes.mainmode')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 6:
                return DB::table('contacts')
                    ->join('bands', 'bands.id', '=', 'contacts.band_id')
                    ->join('modes', 'modes.id', '=', 'contacts.mode_id')
                    ->select(DB::raw('contacts.callsign_id, bands.band, modes.mainmode, count(contacts.id) as count' . ($this->resets_daily ? ", $dateExpression" : "")))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', $this->eventcallsignids())
                    ->groupBy('contacts.callsign_id', 'bands.band', 'modes.mainmode')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 7:
                return DB::table('contacts')->select(DB::raw('callsign_id, count(id) as count' . ($this->resets_daily ? ", $dateExpression" : "")))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', array_diff($this->event->callsigns()->where('dxcc_id', $this->dxcc_id)->get()->pluck('id')->toArray(), $this->getexcludedcallsignids()))
                    ->groupBy('callsign_id')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 8:
                return DB::table('contacts')->select(DB::raw('callsign_id, count(id) as count' . ($this->resets_daily ? ", $dateExpression" : "")))
                    ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
                    ->whereIn('callsign_id', array_diff($this->event->callsigns()->whereRelation('dxcc', 'cont', $this->dxcc_querystring)->get()->pluck('id')->toArray(), $this->getexcludedcallsignids()))
                    ->groupBy('callsign_id')
                    ->when($this->resets_daily, function ($query) use ($dateExpression) {
                        $query->groupBy(DB::raw($dateExpression));
                    })
                    ->get()
                    ->count();
            case 9:
                return DB::table('awardtimeframes')
                    ->leftJoin('contacts', function($join) {
                        $join->on('contacts.qso_datetime', '>=', 'awardtimeframes.start')
                            ->on('contacts.qso_datetime', '<=', 'awardtimeframes.end');
                    })
                    ->select('awardtimeframes.id', DB::raw('COUNT(contacts.id) AS COUNT'))
                    ->where([['awardtimeframes.award_id', $this->id], ['contacts.callsign', $callsign]])
                    ->whereIn('contacts.callsign_id', $this->eventcallsignids())
                    ->groupBy('awardtimeframes.id')
                    ->get()
                    ->count(); 
            default:
                return 0;
        }
    }

    public function duplicate() : Award
    {
        //replicate this award
        $new_award = $this->replicate();

        //create a unique id for the fields that have to be unique
        $uid = str_replace('.','', uniqid('', true));

        //change relevant fields
        $new_award->creator_id = auth()->user()->id;
        $new_award->created_at = \Carbon\Carbon::now();
        $new_award->updated_at = \Carbon\Carbon::now();
        $new_award->background_image = 'Blank.jpg';
        $new_award->title = $uid;
        $new_award->slug = $uid;
        
        //return new award. Save must be done in controller
        return $new_award;
    }


}
