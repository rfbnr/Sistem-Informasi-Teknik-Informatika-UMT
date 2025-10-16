@extends('user.layouts.app')

@section('title', 'Struktur Organisasi')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Struktur Organisasi</h1>
    </section>
    <section>
        <div class="container-fluid">
            <div class="row justify-content-center">
                @foreach ($strukturs as $struktur)
                    <div class="col-12 col-md-12">
                        <div class="text-center my-4">
                            <img src="{{ asset('storage/' . $struktur->image) }}" class="img-fluid" alt="Uploaded Image">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
