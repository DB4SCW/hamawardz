<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Hamevent;
use Illuminate\Auth\Access\Response;

use function PHPUnit\Framework\isEmpty;

class HameventPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function eventadmin(User $user) : Response
    {
        if ($user->events_to_manage->count() <= 0) { return Response::deny('No permission to show events'); };
        return Response::allow();
    }

    public function create(User $user) : Response
    {
        if (!$user->cancreateevents) { return Response::deny('No permission to create events'); };
        return Response::allow();
    }

    public function viewstatistics(User $user, Hamevent $hamevent) : Response
    {
        //load callsign info
        $usercalls = $user->callsigns->pluck('id');
        $eventcalls = $hamevent->callsigns->pluck('id');
        
        //check if these intersect
        $intersectingCalls = $usercalls->intersect($eventcalls);

        if($intersectingCalls->isEmpty())
        {
            return Response::deny('You do not have permission to view statistics for this event.');;
        }

        return Response::allow();

    }

    public function edit(User $user, Hamevent $hamevent) : Response
    {
        if($hamevent->creator_id != $user->id)
        {
            if(!$hamevent->eventmanagers->contains($user))
            {
                return Response::deny('You do not have permission to edit this event.');;
            }
        }

        return Response::allow();

    }

    //Administrator is allowed for anything
    public function before(User $user, string $ability) : bool|null
    {
        if ($user->siteadmin) {
            return true;
        }
    
        return null;
    }
}
