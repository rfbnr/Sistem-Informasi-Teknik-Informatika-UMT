@extends('user.layouts.app')

@section('title', 'Dosen')

@section('content')

    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            border-radius: 50%; /* Membuat gambar berbentuk bulat */
            object-fit: cover; /* Menjaga proporsi gambar dan mengisi area */
            height: 200px; /* Tinggi gambar */
            width: 200px; /* Lebar gambar */
            margin: 0 auto; /* Memposisikan gambar di tengah */
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

        .btn {
            font-size: 1.2rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-dark {
            background-color: #343a40;
            border-color: #343a40;
        }
    </style>

    <!-- Section Header -->
    <section id="header-section">
        <h1>Daftar Dosen</h1>
    </section>

    <!-- Section Dosen -->
    <section id="dosen" class="mt-5">
        <div class="container">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Dosen</h3>
            </div>
            <div class="row justify-content-center">
                @foreach ($dosens as $index => $dosen)
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 text-center">
                            {{-- <img src="{{ asset('storage/' . $dosen->image) }}" class="card-img-top"
                                alt="{{ $dosen->name }}"> --}}
                            <img src="{{ Storage::url($dosen->image) }}" class="card-img-top"
                                alt="{{ $dosen->name }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $dosen->name }}</h5>
                                <p class="card-text">{{ $dosen->jabatan }}</p>
                                <div class="d-flex justify-content-center">
                                    <a href="{{ $dosen->linkedin }}" target="_blank" class="btn btn-primary">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="{{ $dosen->instagram }}" target="_blank" class="btn btn-danger">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="mailto:{{ $dosen->email }}" target="_blank" class="btn btn-secondary">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    <a href="{{ $dosen->youtube }}" target="_blank" class="btn btn-danger">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                    <a href="{{ $dosen->tiktok }}" target="_blank" class="btn btn-dark">
                                        <i class="fab fa-tiktok"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                                            </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection

