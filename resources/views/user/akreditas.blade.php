@extends('user.layouts.app')

@section('title', 'Akreditasi')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Akreditasi</h1>
    </section>

    <section>
        <div class="container">
            <div class="row justify-content-center">
                @foreach ($akreditasis as $akreditasi)
                    <div class="col-12 col-md-8">
                        <div class="text-center my-4">
                            {{-- <img src="{{ asset('storage/' . $akreditasi->image) }}" class="img-fluid" alt="Uploaded Image"> --}}
                            <img src="{{ Storage::url($akreditasi->image) }}" class="img-fluid" alt="Uploaded Image">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
