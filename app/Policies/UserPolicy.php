<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user) : Response
    {
        if (!$user->events_to_manage->count() > 0) { return Response::deny('No permission to see users'); };
        return Response::allow();
    }
    
    public function see(User $user) : Response
    {
        if (!$user->events_to_manage->count() > 0) { return Response::deny('No permission to see users'); };
        return Response::allow();
    }

    public function updatebasic(User $user, User $target) : Response
    {
        //you may update yourself
        if($target->id == $user->id)
        {
            return Response::allow();
        }

        //you may update every user you created
        if($target->creator_id == $user->id)
        {
            return Response::allow();
        }

        //all others are denied
        return Response::deny('No permission to update user data');
    }

    public function updateadmindata(User $user, User $target) : Response
    {
        //only siteadmin may update admindata for users - no exceptions
        return Response::deny('No permission to update admin data for user');
    }

    public function destroy(User $user, User $target) : Response
    {
        //only siteadmin may delete users - no exceptions
        return Response::deny('No permission to delete user');
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
