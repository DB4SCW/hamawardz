<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function events_to_manage() : BelongsToMany
    {
        return $this->belongsToMany(Hamevent::class);
    }

    public function callsigns() : BelongsToMany
    {
        return $this->belongsToMany(Callsign::class);
    }

    public function created_users() : HasMany
    {
        return $this->hasMany(User::class, 'creator_id', 'id');
    }

    public function callsignapis() : HasMany
    {
        return $this->hasMany(Callsignapidetail::class, 'context_userid', 'id');
    }

    public function is_manager_of_callsign(Callsign $callsign) : bool
    {
        //Siteadmin manages all
        if($this->siteadmin)
        {
            return true;
        }

        //creator stays manager forever
        if($callsign->creator_id == $this->id)
        {
            return true;
        }
        
        //get all events
        $events = $this->events_to_manage()->with('callsigns')->get();

        //load all distinct callsigns
        foreach ($events as $event) {
            if($event->callsigns->contains($callsign))
            {
                return true;
            }
        }

        //return false if not
        return false;
    }

}
