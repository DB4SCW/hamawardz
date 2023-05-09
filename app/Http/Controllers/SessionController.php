<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index()
    {
        //show login page
        return view('login');
    }

    public function login()
    {
        //validate request data
        $attributes = request()->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        //check for non-locked candidates for login check
        $candidate = User::where([['username', $attributes['username']], ['locked', 0]])->count();

        //return error if no non-locked-users are available
        if($candidate != 1)
        {
            return redirect()->route('loginpage')->with('danger', 'Login unsuccessful.')->withInput();
        }

        //Attempt login - return with error message if unsuccessful
        if (! auth()->attempt($attributes)) {
            return redirect()->route('loginpage')->with('danger', 'Login unsuccessful.')->withInput();
        }

        //regenerate session
        session()->regenerate();

        //redirect to homepage
        return redirect()->route('home')->with('success', 'Login successful. Welcome back!');
    }

    public function logout()
    {
        //Logout user
        if(auth()->check())
        {
            auth()->logout();
        }

        //return to home page
        return redirect()->route('home')->with('success', 'Logout successful. Hope to see you again soon.');
    }
}
