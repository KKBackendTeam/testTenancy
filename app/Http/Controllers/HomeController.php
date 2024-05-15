<?php

namespace App\Http\Controllers;

use App\Models\Agency;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $all_agencies = Agency::all();
        return view('home', compact('all_agencies'));
    }
}
