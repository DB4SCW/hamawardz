<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Awardtimeframe extends Model
{
    use HasFactory;
    protected $casts = ['start' => 'datetime', 'end' => 'datetime'];

    public function award() : HasOne
    {
        return $this->hasOne(Award::class, "id", "award_id");
    }
}
