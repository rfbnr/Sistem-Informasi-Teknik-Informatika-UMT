<?php

namespace App\Http\Controllers;


use App\Models\Agenda;
use App\Models\Alumni;
use App\Models\Layanan;
use App\Models\Sorotan;
use App\Models\Talenta;
use App\Models\Carousel;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\Crypt;

class HomeController extends Controller
{
    public function index()
    {
        // $ongoings = Ongoing::all();
        // $upcomings = Upcoming::all();
        // $moments = Moment::all();
        // $contacts = Contact::first();

        $agendas = Agenda::orderBy('created_at', 'desc')->get();;
        $talentas = Talenta::all();
        $sorotans = Sorotan::all();
        $alumnis = Alumni::all();
        $dashboards = Dashboard::all();
        $carousels = Carousel::all();
        $layanans = Layanan::all();

        return view('user.landingpage', compact(
            'agendas',
            'talentas',
            'sorotans',
            'alumnis',
            'dashboards',
            'carousels',
            'layanans',
        ));
    }

    public function verif(Request $request, $id)
    {
        // Dekripsi ID
        $decryptedId = Crypt::decryptString($id);

        $user = ApprovalRequest::with(['User'])->findOrFail($decryptedId);
        return view('approval_requests.barcode', compact('user'));
    }



    public function pengabdian()
    {
        return view('user.pengabdian');
    }

    public function download()
    {
        return view('user.download');
    }

    public function dpa()
    {
        return view('user.dpa');
    }

    public function kurikulum()
    {
        return view('user.kurikulum');
    }

    public function luaran()
    {
        return view('user.luaran');
    }


    public function agenda_detail($id)
    {
        $agendas = Agenda::orderBy('created_at', 'desc')->get();
        $encryptedId = decrypt($id);

        $agenda = Agenda::findOrFail($encryptedId);
        return view('user.detail_agenda', compact('agenda','agendas'));
    }

}
