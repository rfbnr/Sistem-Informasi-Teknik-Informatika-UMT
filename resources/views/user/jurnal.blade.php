@extends('user.layouts.app')

@section('title', 'Penelitian')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Daftar Penelitian</h1>
    </section>
    {{-- Section Penelitian --}}
    <section id="jurnal">
        <div class="container-fluid mt-5">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Daftar Penelitian</h3>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <p>Dimana mahasiswa dapat mem-publish atau menerbitkan artikel ilmiah ? Laman ini menampilkan informasi
                        mengenai Penelitian bidang Informatika dari seluruh Indonesia. Penelitian dikelompokkan berdasarkan waktu
                        terbit dan tingkat akreditasi Sinta, dengan urutan Sinta 1 lebih baik apabila dibandingkan Sinta 6.
                        Beberapa diantaranya terakreditas Scopus. Khusus mahasiswa S1 Prodi Informatika, Penelitian
                        terakreditasi Sinta 3 merupakan syarat minimal yang harus dipenuhi apabila hendak menempuh jalur
                        kelulusan non skripsi atau non reguler kategori Scientist. Melalui skema Scientist, segala biaya
                        yang timbul ditanggung oleh Mahasiswa</p>

                    <!-- Menampilkan data jurnal dalam bentuk list -->
                    <ul>
                        @foreach ($jurnals as $jurnal)
                            <li>{{ $jurnal->name }} - {{ $jurnal->category }}</li>
                        @endforeach
                    </ul>

                </div>
                <div class="col-md-10">
                    <iframe src="https://docs.google.com/spreadsheets/d/1Q6PuKbb2oukjZvl-pUS9IJqerZsWZWp-rIpoDZm7dpY/preview?gid=417893748&single=true"
                        frameborder="0"
                        width="100%"
                        height="800"
                        allowfullscreen
                        style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                    </iframe>
                </div>



            </div>
        </div>
    </section>

@endsection
