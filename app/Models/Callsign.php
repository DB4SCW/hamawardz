<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Callsign extends Model
{
    use HasFactory;

    protected $casts = ['last_upload' => 'datetime'];

    public function contacts() : HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function events() : BelongsToMany
    {
        return $this->belongsToMany(Hamevent::class);
    }

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, "creator_id", "id");
    }

    public function uploadusers() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function dxcc() : BelongsTo
    {
        return $this->belongsTo(Dxcc::class);
    }

    public function autoimport() : HasOne
    {
        return $this->hasOne(Autoimport::class, 'callsign_id', 'id');
    }

    public function uploads() : HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function callsignapis() : HasMany
    {
        return $this->hasMany(Callsignapidetail::class, 'callsign_id', 'id');
    }

    public function setlastupload()
    {
        $this->refresh();
        $this->last_upload = $this->uploads->max('created_at');
        $this->save();
    }

}
