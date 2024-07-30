<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Callsignapierrorlog extends Model
{
    use HasFactory;

    public function callsignapidetail() : BelongsTo
    {
        return $this->belongsTo(Callsignapidetail::class, "callsignapidetail_id", "id");
    }
}
