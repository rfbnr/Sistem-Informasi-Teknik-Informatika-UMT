@extends('user.layouts.app')

@section('title', 'Alumni')

@section('content')

    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            border-radius: 15px 15px 0 0;
            object-fit: cover;
            height: 200px;
            width: 100%;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-text {
            font-size: 1rem;
            color: #6c757d;
        }

        .btn-link {
            color: #007bff;
            font-size: 1.2rem;
        }

        .btn-link:hover {
            color: #0056b3;
            text-decoration: none;
        }
    </style>

    <!-- Section Header -->
    <section id="header-section">
        <h1>Alumni</h1>
    </section>

    <!-- Section Alumni -->
    <section id="alumni" class="mt-5">
        <div class="container">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Alumni</h3>
            </div>
            <div class="row justify-content-center">
                @foreach ($alumnis as $alumni)
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <img src="{{ asset('storage/' . $alumni->image) }}" class="card-img-top"
                                alt="{{ $alumni->name }}">
                            <div class="card-body text-center">
                                <h5 class="card-title">{{ $alumni->name }}</h5>
                                <p class="card-text">{{ $alumni->jabatan }}</p>
                                <div class="d-flex justify-content-center">
                                    @if ($alumni->linkedin)
                                        <a href="{{ $alumni->linkedin }}" class="btn btn-link"><i
                                                class="bi bi-linkedin"></i></a>
                                    @endif
                                    @if ($alumni->instagram)
                                        <a href="{{ $alumni->instagram }}" class="btn btn-link"><i
                                                class="bi bi-instagram"></i></a>
                                    @endif
                                    @if ($alumni->email)
                                        <a href="mailto:{{ $alumni->email }}" class="btn btn-link"><i
                                                class="bi bi-envelope"></i></a>
                                    @endif
                                    @if ($alumni->youtube)
                                        <a href="{{ $alumni->youtube }}" class="btn btn-link"><i
                                                class="bi bi-youtube"></i></a>
                                    @endif
                                    @if ($alumni->tiktok)
                                        <a href="{{ $alumni->tiktok }}" class="btn btn-link"><i
                                                class="bi bi-tiktok"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection
