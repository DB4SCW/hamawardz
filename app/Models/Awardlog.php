<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Awardlog extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function dxcc() : HasOne
    {
        return $this->hasOne(Dxcc::class, "id", "dxcc_id");
    }
}
