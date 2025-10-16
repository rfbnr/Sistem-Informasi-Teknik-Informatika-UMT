@extends('user.layouts.app')

@section('title', 'Approval')

@section('content')
    <!-- Section Header -->
    <section id="header-section">
        <h1>Approval Kaprodi</h1>
    </section>

    <!-- Section Aproval Kaprodi -->
    <section id="aproval-kaprodi" class="mt-5 mb-5">
        <div class="container">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Approval Kaprodi</h3>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('approval-request.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control"
                                placeholder="Masukkan nama surat" value="{{auth()->user()->name}}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Keterangan</label>
                            <select class="form-control" name="document_name" id="document_name">
                                <option disabled selected>--Pilih Jenis Surat--</option>
                                <option value="Dispensasi">Dispensasi</option>
                                <option value="Peminjaman">Peminjaman</option>
                                <option value="Kartu Ujian">Kartu Ujian</option>
                                <option value="lainnya">lainnya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Prihal</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Masukkan catatan">{{ old('notes') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">Upload File</label>
                            <input class="form-control" type="file" id="document" name="document">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        @if ($hasApprovalRequests)
                            <div class="status-link">
                                <p>Mohon cek <a href="{{ route('approval-request.status') }}">status dokumen</a>.</p>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
