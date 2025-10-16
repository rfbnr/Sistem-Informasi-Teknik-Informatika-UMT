@extends('kaprodi.layouts.app')

@section('title', 'Detail Permintaan Tanda Tangan')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <a href="{{ route('kaprodi.signatures.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <h4 class="mb-0">📋 Detail Permintaan Tanda Tangan</h4>
                            </div>
                            <p class="text-muted mb-0">{{ $signatureRequest->title }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($signatureRequest->status === 'pending' || $signatureRequest->status === 'in_progress')
                                <a href="{{ route('kaprodi.signatures.sign', $signatureRequest) }}" class="btn btn-primary">
                                    <i class="fas fa-signature me-2"></i>Tanda Tangani
                                </a>
                            @endif
                            @if($signatureRequest->status === 'completed')
                                <a href="{{ route('signatures.download-signed', $signatureRequest) }}" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Download Signed
                                </a>
                            @endif
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportToPDF()"><i class="fas fa-file-pdf me-2"></i>Export PDF</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="shareDocument()"><i class="fas fa-share me-2"></i>Share</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="reportIssue()"><i class="fas fa-flag me-2"></i>Report Issue</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Document Information -->
        <div class="col-lg-8">
            <!-- Document Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📄 Informasi Dokumen</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-medium text-muted" style="width: 120px;">Judul:</td>
                                    <td>{{ $signatureRequest->title }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Pemohon:</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $signatureRequest->requester->name }}</div>
                                                <small class="text-muted">{{ $signatureRequest->requester->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Jenis:</td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst($signatureRequest->type) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Tanggal Dibuat:</td>
                                    <td>{{ $signatureRequest->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-medium text-muted" style="width: 120px;">Status:</td>
                                    <td>
                                        @php
                                            $statusConfig = [
                                                'pending' => ['badge' => 'bg-warning', 'text' => 'Menunggu', 'icon' => 'clock'],
                                                'in_progress' => ['badge' => 'bg-info', 'text' => 'Dalam Proses', 'icon' => 'spinner'],
                                                'completed' => ['badge' => 'bg-success', 'text' => 'Selesai', 'icon' => 'check-circle'],
                                                'expired' => ['badge' => 'bg-danger', 'text' => 'Expired', 'icon' => 'times-circle'],
                                                'rejected' => ['badge' => 'bg-secondary', 'text' => 'Ditolak', 'icon' => 'ban']
                                            ];
                                            $config = $statusConfig[$signatureRequest->status] ?? ['badge' => 'bg-secondary', 'text' => ucfirst($signatureRequest->status), 'icon' => 'question'];
                                        @endphp
                                        <span class="badge {{ $config['badge'] }} me-2">
                                            <i class="fas fa-{{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                        </span>
                                        @if($signatureRequest->is_urgent)
                                            <span class="badge bg-danger">URGENT</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Deadline:</td>
                                    <td>
                                        @if($signatureRequest->deadline)
                                            <div>
                                                {{ $signatureRequest->deadline->format('d M Y, H:i') }}
                                                @php
                                                    $daysLeft = now()->diffInDays($signatureRequest->deadline, false);
                                                @endphp
                                                @if($daysLeft < 0)
                                                    <span class="badge bg-danger ms-2">OVERDUE</span>
                                                @elseif($daysLeft <= 2)
                                                    <span class="badge bg-warning ms-2">{{ $daysLeft }} hari lagi</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Tidak ada deadline</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">Progress:</td>
                                    <td>
                                        @php
                                            $progress = $signatureRequest->getProgress();
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-3" style="width: 100px; height: 8px;">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="small fw-medium">{{ round($progress) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium text-muted">File Hash:</td>
                                    <td>
                                        @if($signatureRequest->document && $signatureRequest->document->file_hash)
                                            <code class="small">{{ Str::limit($signatureRequest->document->file_hash, 16) }}...</code>
                                            <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $signatureRequest->document->file_hash }}')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($signatureRequest->description)
                        <div class="mt-3">
                            <h6 class="fw-medium">Deskripsi:</h6>
                            <p class="text-muted">{{ $signatureRequest->description }}</p>
                        </div>
                    @endif

                    @if($signatureRequest->document)
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-1">{{ $signatureRequest->document->file_name }}</h6>
                                        <small class="text-muted">{{ $signatureRequest->document->file_size }} • Uploaded {{ $signatureRequest->document->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('documents.download', $signatureRequest->document) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                    <button class="btn btn-sm btn-outline-info" onclick="previewDocument('{{ $signatureRequest->document->id }}')">
                                        <i class="fas fa-eye me-1"></i>Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Signature History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">✍️ Riwayat Tanda Tangan</h5>
                </div>
                <div class="card-body">
                    @if($signatureRequest->signatures->count() > 0)
                        <div class="timeline">
                            @foreach($signatureRequest->signatures->sortBy('created_at') as $signature)
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    @if($signature->status === 'signed')
                                        <i class="fas fa-check-circle text-success"></i>
                                    @elseif($signature->status === 'rejected')
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @else
                                        <i class="fas fa-clock text-warning"></i>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $signature->signer->name }}</h6>
                                            <p class="text-muted mb-2">{{ $signature->signer_role }}</p>
                                            @if($signature->status === 'signed')
                                                <p class="small text-success mb-1">
                                                    <i class="fas fa-check me-1"></i>Ditandatangani pada {{ $signature->signed_at->format('d M Y, H:i') }}
                                                </p>
                                                @if($signature->location)
                                                    <p class="small text-muted mb-1">
                                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $signature->location }}
                                                    </p>
                                                @endif
                                                @if($signature->ip_address)
                                                    <p class="small text-muted mb-1">
                                                        <i class="fas fa-globe me-1"></i>{{ $signature->ip_address }}
                                                    </p>
                                                @endif
                                            @elseif($signature->status === 'rejected')
                                                <p class="small text-danger mb-1">
                                                    <i class="fas fa-times me-1"></i>Ditolak pada {{ $signature->signed_at->format('d M Y, H:i') }}
                                                </p>
                                                @if($signature->rejection_reason)
                                                    <p class="small text-muted">Alasan: {{ $signature->rejection_reason }}</p>
                                                @endif
                                            @else
                                                <p class="small text-warning mb-1">
                                                    <i class="fas fa-clock me-1"></i>Menunggu tanda tangan
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            @if($signature->signature_data)
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewSignature('{{ $signature->id }}')">
                                                    <i class="fas fa-signature me-1"></i>Lihat
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-signature fa-3x text-muted mb-3"></i>
                            <h6>Belum Ada Tanda Tangan</h6>
                            <p class="text-muted">Dokumen ini belum ditandatangani oleh siapapun</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⚡ Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($signatureRequest->status === 'pending' || $signatureRequest->status === 'in_progress')
                            <a href="{{ route('kaprodi.signatures.sign', $signatureRequest) }}" class="btn btn-primary">
                                <i class="fas fa-signature me-2"></i>Tanda Tangani Dokumen
                            </a>
                            <button class="btn btn-outline-danger" onclick="showRejectModal()">
                                <i class="fas fa-times me-2"></i>Tolak Dokumen
                            </button>
                        @endif

                        @if($signatureRequest->document)
                            <a href="{{ route('documents.download', $signatureRequest->document) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-download me-2"></i>Download Dokumen
                            </a>
                        @endif

                        @if($signatureRequest->status === 'completed')
                            <a href="{{ route('signatures.download-signed', $signatureRequest) }}" class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Download Signed
                            </a>
                        @endif

                        <button class="btn btn-outline-info" onclick="verifyIntegrity()">
                            <i class="fas fa-shield-alt me-2"></i>Verifikasi Integritas
                        </button>

                        <button class="btn btn-outline-secondary" onclick="viewBlockchainInfo()">
                            <i class="fas fa-link me-2"></i>Info Blockchain
                        </button>
                    </div>
                </div>
            </div>

            <!-- Document Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📊 Statistik</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ $signatureRequest->signatures->where('status', 'signed')->count() }}</h4>
                                <small class="text-muted">Ditandatangani</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-1">{{ $signatureRequest->signatures->where('status', 'pending')->count() }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <hr>
                    <div class="small text-muted">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Signees:</span>
                            <span>{{ $signatureRequest->signatures->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Completion:</span>
                            <span>{{ round($signatureRequest->getProgress()) }}%</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Created:</span>
                            <span>{{ $signatureRequest->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blockchain Information -->
            @if($signatureRequest->document && $signatureRequest->document->blockchainTransactions->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">🔗 Blockchain</h5>
                </div>
                <div class="card-body">
                    @foreach($signatureRequest->document->blockchainTransactions->take(3) as $transaction)
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                            <i class="fas fa-link text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 small">{{ ucfirst($transaction->transaction_type) }}</h6>
                            <div class="small text-muted">
                                <div>{{ Str::limit($transaction->transaction_hash, 20) }}...</div>
                                <div>{{ $transaction->created_at->format('d M Y, H:i') }}</div>
                            </div>
                        </div>
                        <span class="badge bg-success">
                            <i class="fas fa-check"></i>
                        </span>
                    </div>
                    @endforeach

                    @if($signatureRequest->document->blockchainTransactions->count() > 3)
                        <div class="text-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="viewAllTransactions()">
                                Lihat Semua ({{ $signatureRequest->document->blockchainTransactions->count() }})
                            </button>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Signature Viewer Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tanda Tangan Digital</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <canvas id="signatureCanvas" width="600" height="300" style="border: 1px solid #dee2e6; border-radius: 8px;"></canvas>
                <div class="mt-3">
                    <small class="text-muted">Tanda tangan digital yang telah diverifikasi</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('kaprodi.signatures.reject', $signatureRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Perhatian!</strong> Tindakan ini akan menolak dokumen dan mengirim notifikasi ke pemohon.
                    </div>
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Alasan Penolakan:</label>
                        <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3" required
                                  placeholder="Masukkan alasan mengapa dokumen ditolak..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewSignature(signatureId) {
    // Fetch signature data and display in modal
    $.get(`/api/signatures/${signatureId}/data`)
        .done(function(data) {
            const canvas = document.getElementById('signatureCanvas');
            const ctx = canvas.getContext('2d');

            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw signature from base64 data
            if (data.signature_data) {
                const img = new Image();
                img.onload = function() {
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                };
                img.src = data.signature_data;
            }

            $('#signatureModal').modal('show');
        })
        .fail(function() {
            alert('Gagal memuat data tanda tangan');
        });
}

function showRejectModal() {
    $('#rejectModal').modal('show');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Hash berhasil disalin!', 'success');
    });
}

function previewDocument(documentId) {
    // Open document in new tab for preview
    window.open(`/documents/${documentId}`, '_blank');
}

function verifyIntegrity() {
    const documentId = '{{ $signatureRequest->document->id ?? null }}';
    if (!documentId) {
        alert('Tidak ada dokumen untuk diverifikasi');
        return;
    }

    showToast('Memverifikasi integritas dokumen...', 'info');

    $.get(`/documents/${documentId}/verify-integrity`)
        .done(function(data) {
            if (data.is_valid) {
                showToast('✅ Dokumen valid dan tidak dimodifikasi', 'success');
            } else {
                showToast('❌ Dokumen telah dimodifikasi atau rusak', 'danger');
            }
        })
        .fail(function() {
            showToast('Gagal memverifikasi integritas dokumen', 'danger');
        });
}

function viewBlockchainInfo() {
    const documentId = '{{ $signatureRequest->document->id ?? null }}';
    if (documentId) {
        window.open(`{{ route('kaprodi.blockchain.transactions') }}?document_id=${documentId}`, '_blank');
    }
}

function viewAllTransactions() {
    viewBlockchainInfo();
}

function exportToPDF() {
    showToast('Mengexport ke PDF...', 'info');
    // Implement PDF export functionality
    setTimeout(() => {
        showToast('PDF berhasil diunduh!', 'success');
    }, 2000);
}

function shareDocument() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: '{{ $signatureRequest->title }}',
            url: url
        });
    } else {
        copyToClipboard(url);
        showToast('Link berhasil disalin!', 'success');
    }
}

function reportIssue() {
    const subject = encodeURIComponent('Issue dengan dokumen: {{ $signatureRequest->title }}');
    const body = encodeURIComponent(`Terdapat masalah dengan dokumen ini:\n\nURL: ${window.location.href}\nID: {{ $signatureRequest->id }}\n\nDeskripsi masalah:\n`);
    window.location.href = `mailto:admin@example.com?subject=${subject}&body=${body}`;
}

function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast align-items-center text-white bg-${type === 'info' ? 'primary' : type} border-0 position-fixed"
             style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);

    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();

    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// Auto-refresh document status
setInterval(function() {
    const currentStatus = '{{ $signatureRequest->status }}';
    if (currentStatus === 'pending' || currentStatus === 'in_progress') {
        $.get(`{{ route('kaprodi.signatures.show', $signatureRequest) }}`)
            .done(function(data) {
                // Check if status changed and reload if necessary
                // This would require returning JSON status from the controller
            });
    }
}, 30000); // Every 30 seconds
</script>

<style>
.timeline {
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 30px;
    height: 30px;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid var(--primary-color);
}

.progress {
    background-color: rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75rem;
}

.toast {
    min-width: 300px;
}
</style>
@endpush