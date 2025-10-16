<?php

namespace App\Http\Controllers;

use App\Models\Lomba;
use Illuminate\Http\Request;

class LombaController extends Controller
{
    public function index()
    {
         $lombas = Lomba::all();


         return view('user.lomba', compact('lombas'));
    }
}
