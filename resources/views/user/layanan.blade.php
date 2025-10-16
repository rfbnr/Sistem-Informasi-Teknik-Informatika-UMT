@extends('user.layouts.app')

@section('title', 'Layanan')

@section('content')

<style>
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

    .img-circle {
        width: 100px;
        height: 100px;
        object-fit: cover; /* Ensures the image covers the entire circle */
        border-radius: 50%; /* Makes the image circular */
    }
</style>

<!-- Section Header -->
<section id="header-section">
    <h1>Layanan Prodi</h1>
</section>

<!-- Section Aproval Kaprodi -->
<section id="aproval-kaprodi" class="mt-5 mb-5">
    <div class="container">
        <div class="text-center mb-4">
            <h3 class="fs-3 fw-bolder">Layanan Prodi</h3>
        </div>
        <div class="table-responsive">
            <table id="Layanan" class="table table-bordered table-striped table-sm text-center">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($layanans as $layanan)
                        <tr>
                            <td><img src="{{ Storage::url($layanan->image) }}" alt="foto" class="img-circle"></td>
                            <td>{{ $layanan->name }}</td>
                            <td>{{ $layanan->jabatan }}</td>
                            <td>
                                @if ($layanan->status == 'Ada')
                                    <span class="badge badge-success"> {{ $layanan->status }}</span>
                                @else
                                    <span class="badge badge-danger"> {{ $layanan->status }}</span>
                                @endif
                            </td>
                            <td>{!! $layanan->keterangan !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Coming Soon</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

@endsection
