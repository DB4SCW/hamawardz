<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contact extends Model
{
    use HasFactory;

    protected $casts = ['qso_datetime' => 'datetime'];

    public function band() : BelongsTo
    {
        return $this->belongsTo(Band::class);
    }

    public function upload() : BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function eventcallsign() : BelongsTo
    {
        return $this->belongsTo(Callsign::class, "callsign_id", "id");
    }

    public function mode() : BelongsTo
    {
        return $this->belongsTo(Mode::class);
    }

    public function dxcc() : HasOne
    {
        return $this->hasOne(Dxcc::class, "id", "dxcc_id");
    }

    public function autoimport() : HasOne
    {
        return $this->hasOne(Autoimport::class, 'id', 'autoimport_id');
    }
}
