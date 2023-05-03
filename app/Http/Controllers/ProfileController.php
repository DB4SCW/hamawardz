<?php

namespace App\Http\Controllers;

use App\Models\Callsign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        //load current user
        $user = auth()->user();
        $callsigns = $user->siteadmin ? Callsign::orderBy('call', 'ASC')->get() : $user->callsigns()->orderBy('call', 'ASC')->get();

        //return view
        return view('profile', ['user' => $user, 'callsigns' => $callsigns]);
    }

    public function update()
    {
        //load current user
        $user = User::find(auth()->user()->id);

        $validator = \Illuminate\Support\Facades\Validator::make(request()->all(), [
            'oldpw' => 'string|min:1', 
            'newpw1' => 'string|min:6',
            'newpw2' => 'string|min:6',
        ], 
        [
            'oldpw.string' => 'Please enter your old password.',
            'oldpw.min' => 'Please enter your old password.',
            'newpw1.string' => 'Please enter a new password.',
            'newpw1.min' => 'Your new password must be at least 6 letters long.',
            'newpw2.string' => 'Please enter a new password.',
            'newpw2.min' => 'Your new password must be at least 6 letters long.',
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator));
        }

        //get validated attributes
        $attributes = $validator->validated();

        //load new password
        $old = $attributes['oldpw'];
        $new1 = $attributes['newpw1'];
        $new2 = $attributes['newpw2'];

        //Check if input is identical
        if($new1 != $new2)
        {
            return redirect()->back()->with('danger', 'Your new password does not match.');
        }

        //Check old password
        if(!Hash::check($old, $user->password))
        {
            auth()->logout();
            return redirect()->route('login')->with('danger', 'Invalid password. We logged you out as a security measure.');
        }

        //Save new password
        $user->password = bcrypt($new1);
        $user->save();

        //Log user out and redirect to login
        auth()->logout();
        return redirect()->route('login')->with('success', 'Your password was changed successfully. Please log in again.');

    }
}
