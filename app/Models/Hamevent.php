<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Hamevent extends Model
{
    use HasFactory;

    protected $casts = ['start' => 'datetime', 'end' => 'datetime'];

    public function awards() : HasMany
    {
        return $this->hasMany(Award::class);
    }

    public function callsigns() : BelongsToMany
    {
        return $this->belongsToMany(Callsign::class);
    }

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, "creator_id", "id");
    }

    public function eventmanagers() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function getcontactsfor(string $callsign)
    {
        
        //load callsign ids
        $callsigns = $this->callsigns->pluck('id')->toArray();

        //load contacts inside of event duration
        $contacts = Contact::where([['qso_datetime', '>=', $this->start], ['qso_datetime', '<=', $this->end], ['callsign', $callsign]])->whereIn('callsign_id', $callsigns)->orderBy('qso_datetime', 'ASC')->get();

        return $contacts;
    }
}
