@extends('user.layouts.app')

@section('title', 'Sorotan')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Daftar Sorotan Terkini</h1>
    </section>
    <section>
        <div class="container mt-5 mb-5">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Sorotan Informasi</h3>
            </div>
            <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                @foreach ($sorotans as $sorotan)
                    <div class="col d-flex justify-content-center">
                        <div class="card h-100">
                            <div class="home-card-img-top">
                                <img src="{{ asset('storage/' . $sorotan->image) }}" alt="Placeholder image"
                                    class="card-img-top">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $sorotan->title }}</h5>
                                <p class="card-text">{{ $sorotan->description }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
