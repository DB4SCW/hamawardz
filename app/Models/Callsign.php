<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Callsign extends Model
{
    use HasFactory;

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

}
