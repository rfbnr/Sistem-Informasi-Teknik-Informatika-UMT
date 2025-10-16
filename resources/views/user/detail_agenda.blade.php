@extends('user.layouts.app')

@section('title', 'Detail Agenda')

@section('content')
<style>
    /* CSS untuk halaman detail agenda */
    .job-detail-card, .company-info-card, .apply-form-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 30px;
    }

    .job-title {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }

    .company-info {
        font-size: 1rem;
        color: #666;
        margin-bottom: 15px;
    }

    .job-description {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .job-salary {
        font-weight: bold;
        font-size: 1.2rem;
        color: #ff2200;
        margin-bottom: 20px;
    }

    .apply-button {
        margin-top: 20px;
    }

    .company-info-card img {
        max-width: 100%;
        height: auto;
        margin-bottom: 20px;
    }

    .apply-form-card input,
    .apply-form-card textarea {
        border-radius: 5px;
        border: 1px solid #ddd;
        padding: 10px;
        width: 100%;
        box-sizing: border-box;
    }

    .apply-form-card label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    .apply-form-card button {
        padding: 10px 20px;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        width: 100%;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
        width: 100%; /* Ensure buttons take full width */
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .agenda-item {
        position: relative;
        overflow: hidden;
        border-radius: 15px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .agenda-item:hover {
        transform: scale(1.05);
    }
</style>

<div class="container-fluid mt-5">
    <section id="job-detail">
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2>Detail Agenda</h2>
        </div>
        <div class="row">
            <div class="col-lg-9">
                <div class="job-detail-card p-4">
                    <div>
                        <img src="{{ Storage::url($agenda->image) }}" alt="image" class="img-fluid">
                    </div>
                    <h3 class="job-title mt-4">{{ $agenda->title }}</h3>
                    <div class="company-info mb-4">
                        <p class="text-muted mb-1">
                            <i class="far fa-calendar-alt me-2"></i>
                            <span class="company-name">{{ $agenda->created_at->isoformat('DD MMMM Y') }}</span>
                        </p>
                        <p class="text-muted">
                            <i class="far fa-user me-2"></i>
                            <span class="job-location">Author: Admin</span>
                        </p>
                    </div>

                    <div class="job-description mb-4">
                        <h4>Deskripsi:</h4>
                        <p>{!! $agenda->description !!}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="company-info-card">
                    <h5 class="text-center">Agenda Lainnya</h5>
                    @foreach ($agendas->take(4) as $agenda)
                        <div class="col-md-12 agenda-item mt-3">
                            <div class="card mb-3">
                                <img src="{{ Storage::url($agenda->image) }}" class="card-img-top" alt="agenda Image">
                                <div class="card-body">
                                    <h5 class="card-title"><b>{{ $agenda->title }}</b></h5>
                                    <p class="card-text">{!! Str::limit($agenda->description, 50) !!}</p>
                                    <a href="{{ url('agenda-detail', encrypt($agenda->id)) }}" class="btn-xs btn-primary text-decoration-none">Baca Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
