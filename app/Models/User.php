<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function is_manager_of_callsign(Callsign $callsign) : bool
    {
        //Siteadmin manages all
        if($this->siteadmin)
        {
            return true;
        }

        //creator stays manager forever
        if($callsign->creator_id = $this->id)
        {
            return true;
        }
        
        //get all events
        $events = $this->events_to_manage;
        $callsigns = new Collection();

        //load all distinct callsigns
        foreach ($events as $event) {
            foreach ($event->callsigns as $callsignx) {
                if(!$callsigns->contains($callsignx)){
                    $callsigns->add($callsignx);
                }
            }
        }

        //return true if callsign is in any managed events
        if($callsigns->contains($callsign))
        {
            return true;
        }
        
        //return false if not
        return false;
    }

}
