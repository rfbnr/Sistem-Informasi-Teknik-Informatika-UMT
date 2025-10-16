<?php

namespace App\Http\Controllers;

use App\Models\Sorotan;
use Illuminate\Http\Request;

class SorotanController extends Controller
{
    public function index()
    {
        $sorotans = Sorotan::all();
        return view('user.sorotan', ['sorotans' => $sorotans]);
    }
}
