@extends('user.layouts.app')

@section('title', 'Welcome')

@section('content')

    <style>
    /* Animasi turun dari atas */
    @keyframes slideDown {
        0% {
            transform: translateY(-150%);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .cardprodi .col {
        animation: slideDown 1.5s ease-in-out forwards; /* Durasi animasi lebih lama dan smooth */
        opacity: 0; /* Agar elemen tidak terlihat sebelum animasi dimulai */
    }

    /* Tambahkan delay yang lebih lambat untuk setiap card */
    .cardprodi .col:nth-child(1) {
        animation-delay: 0.5s;
    }

    .cardprodi .col:nth-child(2) {
        animation-delay: 1s;
    }

    .cardprodi .col:nth-child(3) {
        animation-delay: 1.5s;
    }

    .cardprodi .col:nth-child(4) {
        animation-delay: 2s;
    }

    .cardprodi .col:nth-child(5) {
        animation-delay: 2.5s;
    }

    .cardprodi .col:nth-child(6) {
        animation-delay: 3s;
    }

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

        .card-img-top,
        .img-fluid {
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
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

        .badge-success {
            background-color: #28a745;
            color: #fff;
            padding: 0.5em 1em;
            border-radius: 0.25em;
        }

        .badge-danger {
            background-color: #dc3545;
            color: #fff;
            padding: 0.5em 1em;
            border-radius: 0.25em;
        }

        section.container-fluid {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        section.container-fluid h2 i {
            color: #ffdd57; /* Gold color for the graduation cap icon */
            margin-right: 10px;
        }

        section.container-fluid .text-white a {
            color: #ffdd57; /* Matching gold color for the link */
            text-decoration: underline;
        }

        section.container-fluid .text-white a:hover {
            color: #ffffff; /* Change color on hover */
        }


    </style>

    <section id="hero" class="d-flex align-items-center" style="width: 100%; height: 70vh; overflow: hidden;">
        <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel" style="width: 100%; height: 100%;">
            <div class="carousel-inner" style="width: 100%; height: 100%;">
                @foreach ($carousels as $index => $carousel)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="width: 100%; height: 100%;">
                        <img src="{{ asset('storage/' . $carousel->image) }}" class="d-block w-100" alt="carousel-image" style="object-fit: cover; width: 100%; height: 100%;">
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>


<section>
    <div class="cardprodi container position-relative" style="margin-top: -140px;">
        <div class="row row-cols-1 row-cols-md-4 g-4 justify-content-center">
            @foreach ($layanans as $layanan)
                <div class="col">
                    <div class="card h-100 position-relative shadow-sm" style="border-radius: 6px; overflow: hidden; padding: 8px;">
                        <div style="position: absolute; top: 6px; left: 6px; background-color: #ff9900; border-radius:5px; padding:3px 3px;">{{$layanan->jabatan}}</div>
                        @if ($layanan->status == 'Ada')
                            <div style="position: absolute; top: 6px; right: 6px; background-color: #28a745; border-radius: 50%; width: 20px; height: 20px;"></div>
                        @else
                            <div style="position: absolute; top: 6px; right: 6px; background-color: #dc3545; border-radius: 50%; width: 20px; height: 20px;"></div>
                        @endif

                        <div class="home-card-img-top text-center pt-3">
                            <img src="{{ asset('storage/' . $layanan->image) }}" alt="Placeholder image"
                                class="img-fluid" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                        <div class="card-body text-center" style="padding: 10px;">
                            <h6 class="card-title" style="font-size: 0.9rem;">{{ $layanan->name }}</h6>
                            <p style="margin-bottom: 5px;">
                                @if ($layanan->status == 'Ada')
                                    <span class="badge badge-success"> {{ $layanan->status }}</span>
                                @else
                                    <span class="badge badge-danger"> {{ $layanan->status }}</span>
                                @endif
                            </p>
                            <p class="card-text" style="font-size: 0.8rem;">{!! $layanan->keterangan !!}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>





<section id="ap">
    <div class="container mt-5">
        <div class="text-center mb-4">
            <h3 class="fs-3 fw-bolder">Agenda Dan Pengumuman</h3>
        </div>

        <!-- Row to display the first few agenda items -->
        <div class="row justify-content-center" id="agenda-container">
            @foreach ($agendas->take(4) as $agenda)
                <div class="col-md-3 d-flex justify-content-center agenda-item">
                    <div class="card card-body">
                        <div class="home-card-img-top text-center p-3">
                            <img src="{{ asset('storage/' . $agenda->image) }}" alt="Agenda image"
                                class="card-img-top img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1">
                                <i>
                                <i class="far fa-calendar-alt me-2"></i>
                                <span class="company-name">{{ $agenda->created_at->isoformat('DD MMMM Y') }}</span></i>
                            </p>
                            <p class="text-muted">
                                <i>
                                <i class="far fa-user me-2"></i>
                                <span class="job-location">Author: Admin</span></i>
                            </p>
                            <h5 class="card-title">{{ $agenda->title }}</h5>
                            <p class="card-text">{!! Str::limit($agenda->description, 100) !!}</p>
                            <a href="{{ url('agenda-detail', encrypt($agenda->id))}}" class="btn btn-xs btn-primary">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Row for the remaining agenda items, initially hidden -->
        <div class="row justify-content-center d-none mt-4" id="semua-agenda">
            @foreach ($agendas->skip(4) as $agenda)
                <div class="col-md-3 d-flex justify-content-center agenda-item">
                    <div class="card card-body h-100">
                        <div class="home-card-img-top text-center p-3">
                            <img src="{{ asset('storage/' . $agenda->image) }}" alt="Agenda image"
                                class="card-img-top img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-1">
                                <i>
                                <i class="far fa-calendar-alt me-2"></i>
                                <span class="company-name">{{ $agenda->created_at->isoformat('DD MMMM Y') }}</span></i>
                            </p>
                            <p class="text-muted">
                                <i>
                                <i class="far fa-user me-2"></i>
                                <span class="job-location">Author: Admin</span></i>
                            </p>
                            <h5 class="card-title">{{ $agenda->title }}</h5>
                            <p class="card-text">{!! Str::limit($agenda->description, 100) !!}</p>
                            <a href="{{ url('agenda-detail', encrypt($agenda->id))}}" class="btn btn-xs btn-primary">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
<!-- "Tampilkan Semua" Button -->
@if($agendas->count() > 4)
    <div class="text-center" style="margin-top: 60px">
        <button id="show-more-btn" class="btn btn-primary">Tampilkan Semua</button>
    </div>
@endif

    </div>
</section>




        <section id="pmb" class="container-fluid text-white text-center p-5 mb-5 mt-5" style="background-color: #05184E">
        <div class="row">
            <div class="col">
                <h2><i class="bi bi-mortarboard-fill"></i> Penerimaan Mahasiswa Baru</h2>
                <h2 style="color: #ffdd57;">Universitas Muhammadiyah Tangerang</h2>
                <a href="https://umt.ac.id/" class="text-white" style="text-decoration: none; color: inherit;">
                    <i><b>www.umt.ac.id</b></i>
                </a>

            </div>
        </div>
    </section>

    <section id="tt" class="mb-5">
        <div class="container mt-5">
            <div class="col">
                <h3 class="fs-3 fw-bolder mb-4 text-center">Talenta Terbaik</h3>
            </div>
            <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                @foreach ($talentas as $talenta)
                    <div class="col">
                        <div class="card h-100">
                            <div class="home-card-img-top text-center p-3">
                                <img src="{{ asset('storage/' . $talenta->image) }}" alt="Placeholder image"
                                    class="img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $talenta->title }}</h5>
                                <p class="card-text">{!! $talenta->description !!}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="alumni" class="mb-5">
        <div class="container mt-5">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Alumni</h3>
            </div>
            <div class="row row-cols-1 row-cols-md-4 g-4 justify-content-center">
                @foreach ($alumnis as $alumni)
                    <div class="col mb-4">
                        <div class="card h-100 text-center">
                            <img src="{{ asset('storage/' . $alumni->image) }}" class="card-img-top img-fluid"
                                alt="{{ $alumni->name }}" style="width: 150px; height: 150px; object-fit: cover; margin: 20px auto;">
                            <div class="card-body">
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
                                    @if ($alumni->twitter)
                                        <a href="{{ $alumni->twitter }}" class="btn btn-link"><i
                                                class="bi bi-twitter"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

<script>
document.getElementById('show-more-btn').addEventListener('click', function() {
    var semuaAgenda = document.getElementById('semua-agenda');
    if (semuaAgenda.classList.contains('d-none')) {
        semuaAgenda.classList.remove('d-none');
        this.textContent = 'Tampilkan Lebih Sedikit';
    } else {
        semuaAgenda.classList.add('d-none');
        this.textContent = 'Tampilkan Semua';
    }
});




</script>
@endsection
