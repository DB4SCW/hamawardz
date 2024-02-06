<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Callsign;
use Illuminate\Auth\Access\Response;

class CallsignPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function manage(User $user, Callsign $callsign) : Response
    {
        if($user->is_manager_of_callsign($callsign))
        {
            return Response::allow();
        }

        return Response::deny('You do not have permission to manage this callsign.');
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
