<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

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

        //Username manipulation before checking. "administrator" is the only legal lowercase user
        if(strtolower($attributes['username']) != 'administrator')
        {
            $attributes['username'] = strtoupper($attributes['username']);
        }else{
            $attributes['username'] = strtolower($attributes['username']);
        }

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

        //get locally installed version
        $versioninfo_path = storage_path('app/version.txt');
        $installed_version = File::get($versioninfo_path);
        $installed_version = preg_replace('/\s+/', ' ', trim($installed_version));

        //get globally available version
        $available_version = $installed_version;
        try {
            $available_version = Http::get('https://hamawardz.de/versionfiles/hamawardz_version.txt')->body();
            $available_version = preg_replace('/\s+/', ' ', trim($available_version));
        } catch (\Throwable $th) {
            // do nothing, cannot reach info for updated version
        }
        
        //check if upgrade is needed and set updateinfo for display on GUI
        if(version_compare($available_version, $installed_version, '>'))
        {
            //redirect to homepage
            return redirect()->intended('/')->with('success', 'Login successful. Welcome back!')->with('updateinfo', $available_version);
        }else{
            //redirect to homepage
            return redirect()->intended('/')->with('success', 'Login successful. Welcome back!');
        }

        
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
