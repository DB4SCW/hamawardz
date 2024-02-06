<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Hamevent;
use Illuminate\Auth\Access\Response;

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
