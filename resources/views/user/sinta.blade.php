@extends('user.layouts.app')

@section('title', 'Sinta')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Profil Sinta</h1>
    </section>
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">SINTA Journals</h5>
            </div>
            <div class="card-body">
                <div class="iframe-container">
                    <iframe src="https://sinta.kemdikbud.go.id/journals/profile/7016" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
