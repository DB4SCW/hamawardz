<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upload extends Model
{
    use HasFactory;

    public function contacts() : HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function start_date()
    {
        if($this->contacts->count() < 1)
        {
            return \Carbon\Carbon::parse('1900-01-01');
        }

        return $this->contacts->min('qso_datetime');
    }

    public function end_date()
    {
        if($this->contacts->count() < 1)
        {
            return \Carbon\Carbon::parse('1900-01-01');
        }

        return $this->contacts->max('qso_datetime');
    }

    public function uploader() : BelongsTo
    {
        return $this->belongsTo(User::class, "uploader_id", "id");
    }

    public function callsign() : BelongsTo
    {
        return $this->belongsTo(Callsign::class, 'callsign_id', 'id');
    }
}
