<?php

namespace App\Http\Controllers;

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

        //Attempt login - return with error message if unsuccessful
        if (! auth()->attempt($attributes)) {
            return redirect()->route('loginpage')->with('danger', 'Login unsuccessfull.')->withInput();
        }

        //regenerate session
        session()->regenerate();

        //redirect to homepage
        return redirect()->route('home')->with('success', 'Login successfull. Welcome back!');
    }

    public function logout()
    {
        //Logout user
        if(auth()->check())
        {
            auth()->logout();
        }

        //return to home page
        return redirect()->route('home')->with('success', 'Logout successfull. Hope to see you again soon.');
    }
}
