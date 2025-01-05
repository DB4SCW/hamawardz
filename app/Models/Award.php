<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

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
        return db4scw_getawardmodetext($this->mode, $this->min_threshold ?? 0);
    }

    public function eligible(string $callsign) : bool
    {
        //cannot be eligible if award is inactive   
        if(!$this->active)
        {
            return false;
        }

        //check eligibility
        switch ($this->mode) {
            case 0:
                return $this->eligible_mode_0($callsign);
            case 1:
                return $this->eligible_mode_1($callsign);
            case 2:
                return $this->eligible_mode_2($callsign);
            case 3:
                return $this->eligible_mode_3($callsign);
            case 4:
                return $this->eligible_mode_4($callsign);
            case 5:
                return $this->eligible_mode_5($callsign);
            case 6:
                return $this->eligible_mode_6($callsign);
            case 7:
                return $this->eligible_mode_7($callsign);
            case 8:
                return $this->eligible_mode_8($callsign);
            case 9:
                return $this->eligible_mode_9($callsign);
            default:
                return false;
        }

    }

    public function aggregate_count(string $callsign) : int
    {
        
        switch ($this->mode) {
            case 0:
                return $this->aggregate_count_mode_0($callsign);
            case 1:
                return $this->aggregate_count_mode_1($callsign);
            case 2:
                return $this->aggregate_count_mode_2($callsign);
            case 3:
                return $this->aggregate_count_mode_3($callsign);
            case 4:
                return $this->aggregate_count_mode_4($callsign);
            case 5:
                return $this->aggregate_count_mode_5($callsign);
            case 6:
                return $this->aggregate_count_mode_6($callsign);
            case 7:
                return $this->aggregate_count_mode_7($callsign);
            case 8:
                return $this->aggregate_count_mode_8($callsign);
            case 9:
                return $this->aggregate_count_mode_9($callsign);
            default:
                return 0;
        }

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
        $callsigns_raw = db4scw_getcallsignsfromstring($this->excluded_callsigns ?? '') ;
        $callsignids = Callsign::whereIn('call', $callsigns_raw)->get()->pluck('id');
        return $callsignids->toArray();
    }

    public function aggregate_count_mode_0(string $callsign) : int
    {
        return Contact::where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])->whereIn('callsign_id', $this->event->callsigns->pluck('id')->toArray())->count();
    }

    public function aggregate_count_mode_1(string $callsign) : int
    {
        return DB::table('contacts')->select(DB::raw('callsign_id, count(id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('callsign_id')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_2(string $callsign) : int
    {
        return DB::table('contacts')
        ->join('modes', 'modes.id', '=', 'contacts.mode_id')
        ->select(DB::raw('contacts.callsign_id, modes.mode, count(contacts.id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('contacts.callsign_id', 'modes.mode')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_3(string $callsign) : int
    {
        return DB::table('contacts')
        ->join('bands', 'bands.id', '=', 'contacts.band_id')
        ->select(DB::raw('contacts.callsign_id, bands.band, count(contacts.id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('contacts.callsign_id', 'bands.band')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_4(string $callsign) : int
    {
        return DB::table('contacts')
        ->join('bands', 'bands.id', '=', 'contacts.band_id')
        ->join('modes', 'modes.id', '=', 'contacts.mode_id')
        ->select(DB::raw('contacts.callsign_id, bands.band, modes.mode, count(contacts.id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('contacts.callsign_id', 'bands.band', 'modes.mode')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_5(string $callsign) : int
    {
        return DB::table('contacts')
        ->join('modes', 'modes.id', '=', 'contacts.mode_id')
        ->select(DB::raw('contacts.callsign_id, modes.mainmode, count(contacts.id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('contacts.callsign_id', 'modes.mainmode')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_6(string $callsign) : int
    {
        return DB::table('contacts')
        ->join('bands', 'bands.id', '=', 'contacts.band_id')
        ->join('modes', 'modes.id', '=', 'contacts.mode_id')
        ->select(DB::raw('contacts.callsign_id, bands.band, modes.mainmode, count(contacts.id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('contacts.callsign_id', 'bands.band', 'modes.mainmode')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_7(string $callsign) : int
    {
        return DB::table('contacts')->select(DB::raw('callsign_id, count(id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns()->where('dxcc_id', $this->dxcc_id)->get()->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('callsign_id')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_8(string $callsign) : int
    {
        return DB::table('contacts')->select(DB::raw('callsign_id, count(id) as count'))
        ->where([['qso_datetime', '>=', $this->event->start], ['qso_datetime', '<=', $this->event->end], ['callsign', $callsign]])
        ->whereIn('callsign_id', array_diff($this->event->callsigns()->whereRelation('dxcc', 'cont', $this->dxcc_querystring)->get()->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('callsign_id')
        ->get()
        ->count();
    }

    public function aggregate_count_mode_9(string $callsign) : int
    {
        return DB::table('awardtimeframes')
        ->leftJoin('contacts', function($join) {
            $join->on('contacts.qso_datetime', '>=', 'awardtimeframes.start')
                 ->on('contacts.qso_datetime', '<=', 'awardtimeframes.end');
        })
        ->select('awardtimeframes.id', DB::raw('COUNT(contacts.id) AS COUNT'))
        ->where([['awardtimeframes.award_id', $this->id], ['contacts.callsign', $callsign]])
        ->whereIn('contacts.callsign_id', array_diff($this->event->callsigns->pluck('id')->toArray(), $this->getexcludedcallsignids()))
        ->groupBy('awardtimeframes.id')
        ->get()
        ->count();
    }

    public function eligible_mode_0(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_0($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_1(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_1($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_2(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_2($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_3(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_3($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_4(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_4($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_5(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_5($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_6(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_6($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_7(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_7($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_8(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_8($callsign) >= $this->min_threshold;
    }

    public function eligible_mode_9(string $callsign) : bool
    {
        if($this->min_threshold == null)
        {
            return false;
        }

        return $this->aggregate_count_mode_9($callsign) >= $this->min_threshold;
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
