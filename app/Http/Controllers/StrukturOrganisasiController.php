<?php

namespace App\Http\Controllers;

use App\Models\StrukturOrganisasi;
use Illuminate\Http\Request;

class StrukturOrganisasiController extends Controller
{
    public function index()
    {
        $SOS = StrukturOrganisasi::all();
        return view('user.struktur', ['strukturs' => $SOS]);
    }
}
