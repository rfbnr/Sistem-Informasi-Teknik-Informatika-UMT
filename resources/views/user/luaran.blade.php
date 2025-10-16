@extends('user.layouts.app')

@section('title', 'Luaran OBE')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Luaran OBE</h1>
    </section>
    {{-- Section Luaran OBE --}}
    <section id="jurnal">
        <div class="container-fluid mt-5">
            {{-- <div class="text-center mb-4">
                <h2 class="fs-3 fw-bolder">Luaran OBE</h3>
            </div> --}}
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <iframe src="https://docs.google.com/spreadsheets/d/1VHTK9obrNPD5zilHxuAI9LzwBilRlRIUIyzVYZ_IXaU/preview?gid=417893748&single=true"
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
