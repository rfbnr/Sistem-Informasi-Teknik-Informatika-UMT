<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi;
use Illuminate\Http\Request;

class AkreditasiController extends Controller
{
    public function index()
    {
        $akreditasis = Akreditasi::all();
        return view('user.akreditas', ['akreditasis' => $akreditasis]);
    }
}
