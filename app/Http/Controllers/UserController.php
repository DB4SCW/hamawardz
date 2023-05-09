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

    public function checkpermissions()
    {
        if(!auth()->user()->siteadmin)
        {
            return redirect()->back()->with('danger', 'You do not have site administrator privileges.');
        }

        return null;
    }
}
