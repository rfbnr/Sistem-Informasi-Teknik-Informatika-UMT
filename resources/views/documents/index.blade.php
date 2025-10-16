@extends('user.layouts.app')

@section('title', 'Dokumen Saya')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h3 mb-1 text-primary">📄 Dokumen Saya</h2>
                            <p class="text-muted mb-0">Kelola dokumen dan permintaan tanda tangan digital</p>
                        </div>
                        <a href="{{ route('documents.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Upload Dokumen Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-file-upload fa-2x"></i>
                    </div>
                    <h5 class="card-title">{{ $documents->where('status', 'uploaded')->count() }}</h5>
                    <p class="card-text text-muted">Dokumen Terupload</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h5 class="card-title">{{ $documents->where('status', 'ready_for_signature')->count() }}</h5>
                    <p class="card-text text-muted">Menunggu TTD</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h5 class="card-title">{{ $documents->where('status', 'signed')->count() }}</h5>
                    <p class="card-text text-muted">Sudah Ditandatangani</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                    <h5 class="card-title">{{ $documents->count() }}</h5>
                    <p class="card-text text-muted">Terverifikasi Blockchain</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Dokumen</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="uploaded">Terupload</option>
                                <option value="ready_for_signature">Menunggu TTD</option>
                                <option value="signed">Sudah Ditandatangani</option>
                            </select>
                            <select class="form-select form-select-sm" id="categoryFilter">
                                <option value="">Semua Kategori</option>
                                <option value="academic_transcript">Transkrip Akademik</option>
                                <option value="certificate">Sertifikat</option>
                                <option value="thesis">Skripsi</option>
                                <option value="research_proposal">Proposal Penelitian</option>
                                <option value="internship_report">Laporan Magang</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Dokumen</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>TTD Progress</th>
                                        <th>Tanggal Upload</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                                                    <i class="fas fa-file-pdf text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $document->title }}</h6>
                                                    <small class="text-muted">
                                                        {{ number_format($document->file_size / 1024, 0) }} KB
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucfirst(str_replace('_', ' ', $document->category)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusConfig = [
                                                    'uploaded' => ['badge' => 'bg-info', 'icon' => 'upload', 'text' => 'Terupload'],
                                                    'ready_for_signature' => ['badge' => 'bg-warning', 'icon' => 'clock', 'text' => 'Menunggu TTD'],
                                                    'signed' => ['badge' => 'bg-success', 'icon' => 'check-circle', 'text' => 'Ditandatangani'],
                                                    'archived' => ['badge' => 'bg-secondary', 'icon' => 'archive', 'text' => 'Diarsipkan']
                                                ];
                                                $config = $statusConfig[$document->status] ?? ['badge' => 'bg-secondary', 'icon' => 'question', 'text' => ucfirst($document->status)];
                                            @endphp
                                            <span class="badge {{ $config['badge'] }}">
                                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                                {{ $config['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($document->signatureRequests->count() > 0)
                                                @php
                                                    $latestRequest = $document->signatureRequests->first();
                                                    $progress = $latestRequest->getProgress();
                                                @endphp
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ round($progress) }}% selesai</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $document->created_at->format('d M Y') }}<br>
                                                {{ $document->created_at->format('H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('documents.show', $document) }}">
                                                            <i class="fas fa-eye me-2"></i>Lihat Detail
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('documents.download', $document) }}">
                                                            <i class="fas fa-download me-2"></i>Download
                                                        </a>
                                                    </li>
                                                    @if($document->status === 'uploaded')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-primary"
                                                           href="{{ route('documents.signature-request.create', $document) }}">
                                                            <i class="fas fa-signature me-2"></i>Minta Tanda Tangan
                                                        </a>
                                                    </li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item verify-integrity"
                                                           href="#"
                                                           data-document-id="{{ $document->id }}">
                                                            <i class="fas fa-shield-alt me-2"></i>Verifikasi Integritas
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center p-3">
                            {{ $documents->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="text-muted mb-3">
                                <i class="fas fa-file-alt fa-3x"></i>
                            </div>
                            <h5>Belum Ada Dokumen</h5>
                            <p class="text-muted mb-4">Mulai dengan mengupload dokumen pertama Anda</p>
                            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Upload Dokumen
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verifikasi Integritas Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="verificationContent">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memverifikasi integritas dokumen...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#statusFilter, #categoryFilter').change(function() {
        // Implementation for filtering
        console.log('Filter changed');
    });

    // Verify integrity
    $('.verify-integrity').click(function(e) {
        e.preventDefault();
        const documentId = $(this).data('document-id');

        $('#verificationModal').modal('show');

        $.get(`/documents/${documentId}/verify-integrity`)
            .done(function(data) {
                let statusIcon = data.overall_status === 'verified' ?
                    '<i class="fas fa-check-circle text-success fa-2x"></i>' :
                    '<i class="fas fa-exclamation-triangle text-danger fa-2x"></i>';

                let statusText = data.overall_status === 'verified' ?
                    'Dokumen Valid dan Terpercaya' : 'Dokumen Bermasalah';

                $('#verificationContent').html(`
                    <div class="text-center">
                        ${statusIcon}
                        <h5 class="mt-3">${statusText}</h5>
                        <div class="row mt-4">
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6>Verifikasi Lokal</h6>
                                    <span class="badge ${data.local_integrity ? 'bg-success' : 'bg-danger'}">
                                        ${data.local_integrity ? 'Valid' : 'Invalid'}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h6>Verifikasi Blockchain</h6>
                                    <span class="badge ${data.blockchain_integrity ? 'bg-success' : 'bg-danger'}">
                                        ${data.blockchain_integrity ? 'Valid' : 'Invalid'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                Hash: ${data.file_hash}<br>
                                Diverifikasi: ${new Date(data.verified_at).toLocaleString()}
                            </small>
                        </div>
                    </div>
                `);
            })
            .fail(function() {
                $('#verificationContent').html(`
                    <div class="text-center">
                        <i class="fas fa-times-circle text-danger fa-2x"></i>
                        <h5 class="mt-3">Gagal Memverifikasi</h5>
                        <p class="text-muted">Terjadi kesalahan saat memverifikasi dokumen</p>
                    </div>
                `);
            });
    });
});
</script>
@endpush