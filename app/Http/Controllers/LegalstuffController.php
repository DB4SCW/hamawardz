<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalstuffController extends Controller
{
    public function cookie_policy()
    {
        //return view
        return view('legalstuff.cookie_policy');

    }
}
