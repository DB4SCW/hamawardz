<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Autoimport extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function callsign() : HasOne
    {
        return $this->hasOne(Callsign::class, 'id', 'callsign_id');
    }

    public function contacts() : HasMany
    {
        return $this->hasMany(Contact::class, 'autoimport_id', 'id');
    }
}
