<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        //check permissions
        $check = $this->checkpermissions();
        if($check != null)
        {
            return $check;
        }
        
        //Load all users
        $users = User::all();

        //Load view
        return view('users.list', ['users' => $users]);
    }

    public function toggle(User $user)
    {
        //check permissions
        $check = $this->checkpermissions();
        if($check != null)
        {
            return $check;
        }

        //prevent locking of the last admin user
        if(!$user->locked)
        {
            $other_admins = User::whereNotIn('id', [$user->id])->where('siteadmin', true)->count();

            if($other_admins < 1)
            {
                return redirect()->route('listusers')->with('danger', 'You cannot lock the last administration user.');
            }

            //Logout user if current user is the one being locked
            if(auth()->user()->id == $user->id)
            {
                //Logout
                auth()->logout();
                
                //return to home page
                return redirect()->route('home')->with('success', 'User was logged out due to being locked. Goodbye.');
            }
        }
        
        //toggle locked flag
        $user->locked = !$user->locked;
        $user->save();

        //back to list view
        return redirect()->route('listusers')->with('success', 'Userlock successfully toggled.');
    }

    public function create()
    {
        //check permissions
        $check = $this->checkpermissions();
        if($check != null)
        {
            return $check;
        }
        
        //manipulate request data before validation
        $data = request()->all();
        $data['username'] = strtoupper($data['username']);

        //validate
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'username' => 'string|min:3|max:255|unique:users,username',
            'password' => 'string|min:6',
            'siteadmin' => 'integer|min:0|max:1',
            'cancreateevents' => 'integer|min:0|max:1'
        ], 
        [
            'username.string' => 'Username must be a text.',
            'username.min' => 'Username must be at least 3 characters long.',
            'username.max' => 'Username must be at most 255 characters long.',
            'username.unique' => 'Username is already taken.',
            'password.string' => 'Password must be a text',
            'password.min' => 'Password must contain at least 6 characters.',
            'siteadmin.min' => 'Siteadmin flag not valid.',
            'siteadmin.max' => 'Siteadmin flag not valid.',
            'siteadmin.integer' => 'Siteadmin flag not valid.',
            'cancreateevents.min' => 'Cancreateevents flag not valid.',
            'cancreateevents.max' => 'Cancreateevents flag not valid.',
            'cancreateevents.integer' => 'Cancreateevents flag not valid.',
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator))->withInput();
        }

        //get validated attributes
        $attributes = $validator->validated();

        $user = new User();
        $user->username = $attributes['username'];
        $user->password = bcrypt($attributes['password']);
        $user->siteadmin = $attributes['siteadmin'];
        $user->cancreateevents = $attributes['cancreateevents'];
        $user->locked = false;
        $user->save();

        //back to list view
        return redirect()->route('listusers')->with('success', 'User successfully registered.');

    }

    public function showedit(User $user)
    {
        return view('users.edit', ['user' => $user]);
    }

    public function edit(User $user)
    {
        //check permissions
        $check = $this->checkpermissions();
        if($check != null)
        {
            return $check;
        }
        
        //manipulate request data before validation
        $data = request()->all();
        $data['username'] = strtoupper($data['username']);

        //validate
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'username' => 'string|min:3|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'siteadmin' => 'integer|min:0|max:1',
            'cancreateevents' => 'integer|min:0|max:1',
            'locked' => 'integer|min:0|max:1'
        ], 
        [
            'username.string' => 'Username must be a text.',
            'username.min' => 'Username must be at least 3 characters long.',
            'username.max' => 'Username must be at most 255 characters long.',
            'username.unique' => 'Username is already taken.',
            'password.string' => 'Password must be a text',
            'password.min' => 'Password must contain at least 6 characters.',
            'siteadmin.min' => 'Siteadmin flag not valid.',
            'siteadmin.max' => 'Siteadmin flag not valid.',
            'siteadmin.integer' => 'Siteadmin flag not valid.',
            'cancreateevents.min' => 'Cancreateevents flag not valid.',
            'cancreateevents.max' => 'Cancreateevents flag not valid.',
            'cancreateevents.integer' => 'Cancreateevents flag not valid.',
            'locked.min' => 'Locked flag not valid.',
            'locked.max' => 'Locked flag not valid.',
            'locked.integer' => 'Locked flag not valid.'
        ]);

        //handle validation failure
        if ($validator->fails()) {
            return redirect()->back()->with('danger', swolf_validatorerrors($validator))->withInput();
        }

        //get validated attributes
        $attributes = $validator->validated();

        //if new password is set, validate lenght seperately
        if ($attributes['password'] != null) {
            if(\Illuminate\Support\Str::length($attributes['password']) < 6)
            {
                return redirect()->back()->with('danger', 'Password must contain at least 6 characters.')->withInput();
            }
        }

        //save new data
        $user->username = $attributes['username'];
        $user->password = $attributes['password'] == null ? $user->password : bcrypt($attributes['password']);
        $user->siteadmin = $attributes['siteadmin'];
        $user->cancreateevents = $attributes['cancreateevents'];
        $user->locked = $attributes['locked'];
        $user->save();

        //back to list view
        return redirect()->route('listusers')->with('success', 'User saved successfully.');

    }

    public function checkpermissions()
    {
        if(!auth()->user()->siteadmin)
        {
            return redirect()->back()->with('danger', 'You do not have site administrator privileges.');
        }

        return null;
    }
}
