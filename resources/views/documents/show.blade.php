@extends('user.layouts.app')

@section('title', 'Detail Dokumen')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-file-alt text-primary fa-lg"></i>
                            </div>
                            <div>
                                <h2 class="h4 mb-1">{{ $document->title }}</h2>
                                <p class="text-muted mb-0">{{ ucfirst(str_replace('_', ' ', $document->category)) }}</p>
                            </div>
                        </div>
                        <div class="text-end">
                            @php
                                $statusConfig = [
                                    'uploaded' => ['badge' => 'bg-info', 'icon' => 'upload', 'text' => 'Terupload'],
                                    'ready_for_signature' => ['badge' => 'bg-warning', 'icon' => 'clock', 'text' => 'Menunggu TTD'],
                                    'signed' => ['badge' => 'bg-success', 'icon' => 'check-circle', 'text' => 'Ditandatangani'],
                                    'archived' => ['badge' => 'bg-secondary', 'icon' => 'archive', 'text' => 'Diarsipkan']
                                ];
                                $config = $statusConfig[$document->status] ?? ['badge' => 'bg-secondary', 'icon' => 'question', 'text' => ucfirst($document->status)];
                            @endphp
                            <span class="badge {{ $config['badge'] }} fs-6">
                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                {{ $config['text'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Document Details -->
        <div class="col-lg-8">
            <!-- Document Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📄 Informasi Dokumen</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Judul Dokumen:</small>
                                <p class="mb-0">{{ $document->title }}</p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Kategori:</small>
                                <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $document->category)) }}</p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Ukuran File:</small>
                                <p class="mb-0">{{ number_format($document->file_size / 1024, 2) }} KB</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Tanggal Upload:</small>
                                <p class="mb-0">{{ $document->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Hash File:</small>
                                <p class="mb-0 font-monospace small">{{ substr($document->file_hash, 0, 16) }}...</p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Status:</small>
                                <p class="mb-0">{{ $config['text'] }}</p>
                            </div>
                        </div>
                    </div>

                    @if($document->description)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted fw-bold">Deskripsi:</small>
                        <p class="mb-0">{{ $document->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Document Preview -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">👁️ Preview Dokumen</h5>
                </div>
                <div class="card-body text-center p-5">
                    <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                    <h6>{{ $document->title }}</h6>
                    <p class="text-muted mb-4">File PDF - {{ number_format($document->file_size / 1024, 0) }} KB</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('documents.download', $document) }}"
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Buka Dokumen
                        </a>
                        <a href="{{ route('documents.download', $document) }}"
                           class="btn btn-outline-secondary" download>
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                </div>
            </div>

            <!-- Signature Requests -->
            @if($signatureRequests->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📝 Riwayat Permintaan Tanda Tangan</h5>
                </div>
                <div class="card-body p-0">
                    @foreach($signatureRequests as $request)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $request->title }}</h6>
                                <small class="text-muted">
                                    Dibuat: {{ $request->created_at->format('d M Y, H:i') }}
                                    @if($request->deadline)
                                    | Deadline: {{ $request->deadline->format('d M Y') }}
                                    @endif
                                </small>
                            </div>
                            <div class="text-end">
                                @php
                                    $progress = $request->getProgress();
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'completed' => 'success',
                                        'expired' => 'danger',
                                        'cancelled' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                        </div>

                        @if($request->description)
                        <p class="mt-2 mb-2 text-muted small">{{ $request->description }}</p>
                        @endif

                        <!-- Progress Bar -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Progress Tanda Tangan</small>
                                <small class="text-muted">{{ round($progress) }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        <!-- Signatures -->
                        @if($request->signatures->count() > 0)
                        <div class="mt-3">
                            <small class="text-muted fw-bold">Tanda Tangan:</small>
                            <div class="mt-2">
                                @foreach($request->signatures as $signature)
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-signature text-success me-2"></i>
                                    <span class="small">{{ $signature->signer->name }}</span>
                                    <small class="text-muted ms-auto">{{ $signature->signed_at->format('d M Y') }}</small>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⚡ Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('documents.download', $document) }}"
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-eye me-2"></i>Lihat Dokumen
                        </a>

                        <a href="{{ route('documents.download', $document) }}"
                           class="btn btn-outline-secondary" download>
                            <i class="fas fa-download me-2"></i>Download PDF
                        </a>

                        @if($document->status === 'uploaded')
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#signatureRequestModal">
                            <i class="fas fa-signature me-2"></i>Minta Tanda Tangan
                        </button>
                        @endif

                        <button type="button" class="btn btn-info" onclick="verifyIntegrity()">
                            <i class="fas fa-shield-alt me-2"></i>Verifikasi Integritas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Document Security -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">🔐 Keamanan Dokumen</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <i class="fas fa-check-circle text-success fa-lg mb-2"></i>
                                <h6 class="small mb-0">Blockchain</h6>
                                <small class="text-muted">Verified</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <i class="fas fa-lock text-primary fa-lg mb-2"></i>
                                <h6 class="small mb-0">Encryption</h6>
                                <small class="text-muted">SHA-256</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            Hash: <code>{{ substr($document->file_hash, 0, 16) }}...</code>
                        </small>
                    </div>
                </div>
            </div>

            <!-- File Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">ℹ️ Informasi File</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Nama File:</small>
                                <small>{{ $document->metadata['original_name'] ?? 'document.pdf' }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">MIME Type:</small>
                                <small>{{ $document->mime_type }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Upload IP:</small>
                                <small>{{ $document->metadata['ip_address'] ?? 'N/A' }}</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Last Modified:</small>
                                <small>{{ $document->updated_at->format('d M Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Request Modal -->
<div class="modal fade" id="signatureRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Permintaan Tanda Tangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('documents.signature-request.create', $document) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Request Title -->
                    <div class="mb-3">
                        <label class="form-label">Judul Permintaan</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <!-- Request Description -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                    <!-- Workflow Type -->
                    <div class="mb-3">
                        <label class="form-label">Tipe Workflow</label>
                        <select class="form-select" name="workflow_type" required>
                            <option value="sequential">Sequential (berurutan)</option>
                            <option value="parallel">Parallel (bersamaan)</option>
                        </select>
                    </div>

                    <!-- Priority -->
                    <div class="mb-3">
                        <label class="form-label">Prioritas</label>
                        <select class="form-select" name="priority" required>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <!-- Deadline -->
                    <div class="mb-3">
                        <label class="form-label">Deadline (Opsional)</label>
                        <input type="datetime-local" class="form-control" name="deadline">
                    </div>

                    <!-- Signees (simplified - only Kaprodi for now) -->
                    <div class="mb-3">
                        <label class="form-label">Penandatangan</label>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Permintaan akan dikirim ke Kaprodi untuk persetujuan dan tanda tangan.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function verifyIntegrity() {
    Swal.fire({
        title: 'Memverifikasi Integritas...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.get('{{ route("documents.verify-integrity", $document) }}')
        .done(function(data) {
            let icon = data.overall_status === 'verified' ? 'success' : 'error';
            let title = data.overall_status === 'verified' ? 'Dokumen Valid!' : 'Dokumen Bermasalah!';

            Swal.fire({
                icon: icon,
                title: title,
                html: `
                    <div class="row mt-3">
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
                        <small class="text-muted">Hash: ${data.file_hash}</small>
                    </div>
                `
            });
        })
        .fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Verifikasi',
                text: 'Terjadi kesalahan saat memverifikasi dokumen'
            });
        });
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush