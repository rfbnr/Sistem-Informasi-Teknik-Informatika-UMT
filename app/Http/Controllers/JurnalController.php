<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Illuminate\Http\Request;

class JurnalController extends Controller
{
    public function index()
    {
        $jurnals = Jurnal::all();
        return view('user.jurnal', ['jurnals' => $jurnals]);
    }
}
