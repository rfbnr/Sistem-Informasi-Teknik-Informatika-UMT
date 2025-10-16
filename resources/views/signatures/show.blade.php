@extends('user.layouts.app')

@section('title', 'Detail Permintaan Tanda Tangan')

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
                                <i class="fas fa-signature text-primary fa-lg"></i>
                            </div>
                            <div>
                                <h2 class="h4 mb-1">{{ $signatureRequest->title }}</h2>
                                <p class="text-muted mb-0">
                                    Diminta oleh: {{ $signatureRequest->requester->name }}
                                    @if($signatureRequest->deadline)
                                    | Deadline: {{ $signatureRequest->deadline->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-end">
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'expired' => 'danger',
                                    'cancelled' => 'dark',
                                    'rejected' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$signatureRequest->status] ?? 'secondary' }} fs-6">
                                {{ ucfirst($signatureRequest->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Request Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📋 Detail Permintaan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Judul Permintaan:</small>
                                <p class="mb-0">{{ $signatureRequest->title }}</p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Workflow Type:</small>
                                <p class="mb-0">{{ ucfirst($signatureRequest->workflow_type) }}</p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Prioritas:</small>
                                <span class="badge bg-{{ $statusColors[$signatureRequest->priority] ?? 'secondary' }}">
                                    {{ ucfirst($signatureRequest->priority) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Tanggal Dibuat:</small>
                                <p class="mb-0">{{ $signatureRequest->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Deadline:</small>
                                <p class="mb-0">
                                    @if($signatureRequest->deadline)
                                        {{ $signatureRequest->deadline->format('d M Y, H:i') }}
                                        <small class="text-{{ $signatureRequest->deadline->isPast() ? 'danger' : 'success' }}">
                                            ({{ $signatureRequest->deadline->diffForHumans() }})
                                        </small>
                                    @else
                                        <span class="text-muted">Tidak ada deadline</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted fw-bold">Progress:</small>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $signatureRequest->getProgress() }}%"></div>
                                </div>
                                <small class="text-muted">{{ round($signatureRequest->getProgress()) }}% selesai</small>
                            </div>
                        </div>
                    </div>

                    @if($signatureRequest->description)
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted fw-bold">Deskripsi:</small>
                        <p class="mb-0">{{ $signatureRequest->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Document Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📄 Dokumen Terkait</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 rounded p-3 me-3">
                            <i class="fas fa-file-pdf text-danger fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $signatureRequest->document->title }}</h6>
                            <p class="text-muted mb-1">{{ ucfirst(str_replace('_', ' ', $signatureRequest->document->category)) }}</p>
                            <small class="text-muted">{{ number_format($signatureRequest->document->file_size / 1024, 0) }} KB</small>
                        </div>
                        <div>
                            <a href="{{ route('documents.download', $signatureRequest->document) }}"
                               class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>Buka Dokumen
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signees List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">👥 Daftar Penandatangan</h5>
                </div>
                <div class="card-body p-0">
                    @if($signatureRequest->signees->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($signatureRequest->signees->sortBy('order') as $signee)
                            <div class="list-group-item border-0 px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @php
                                            $signeeStatusColors = [
                                                'pending' => 'text-warning',
                                                'notified' => 'text-info',
                                                'viewed' => 'text-primary',
                                                'signed' => 'text-success',
                                                'rejected' => 'text-danger',
                                                'skipped' => 'text-secondary'
                                            ];
                                        @endphp
                                        <i class="fas fa-user-circle fa-2x {{ $signeeStatusColors[$signee->pivot->status] ?? 'text-muted' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $signee->name }}</h6>
                                                <small class="text-muted">
                                                    {{ ucfirst($signee->pivot->role) }}
                                                    @if($signatureRequest->workflow_type === 'sequential')
                                                    | Urutan: {{ $signee->pivot->order }}
                                                    @endif
                                                    @if($signee->pivot->required)
                                                    | <span class="text-danger">Wajib</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $statusColors[$signee->pivot->status] ?? 'secondary' }}">
                                                    {{ ucfirst($signee->pivot->status) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Timeline -->
                                        <div class="mt-2">
                                            <div class="row text-center">
                                                @if($signee->pivot->notified_at)
                                                <div class="col-3">
                                                    <small class="text-muted d-block">Diberitahu</small>
                                                    <small class="text-success">{{ $signee->pivot->notified_at->format('d/m H:i') }}</small>
                                                </div>
                                                @endif

                                                @if($signee->pivot->viewed_at)
                                                <div class="col-3">
                                                    <small class="text-muted d-block">Dilihat</small>
                                                    <small class="text-info">{{ $signee->pivot->viewed_at->format('d/m H:i') }}</small>
                                                </div>
                                                @endif

                                                @if($signee->pivot->responded_at)
                                                <div class="col-3">
                                                    <small class="text-muted d-block">Direspon</small>
                                                    <small class="text-primary">{{ $signee->pivot->responded_at->format('d/m H:i') }}</small>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if($signee->pivot->rejection_reason)
                                        <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded">
                                            <small class="text-danger">
                                                <strong>Alasan Penolakan:</strong> {{ $signee->pivot->rejection_reason }}
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h6>Belum Ada Penandatangan</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Panel -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⚡ Panel Aksi</h5>
                </div>
                <div class="card-body">
                    @php
                        $currentUser = auth()->user();
                        $isSignee = $signatureRequest->signees->contains('id', $currentUser->id);
                        $userSigneeStatus = $isSignee ? $signatureRequest->signees->where('id', $currentUser->id)->first()->pivot->status : null;
                        $canSign = $isSignee && in_array($userSigneeStatus, ['pending', 'notified', 'viewed']) &&
                                   in_array($signatureRequest->status, ['pending', 'in_progress']);
                    @endphp

                    <div class="d-grid gap-2">
                        @if($canSign)
                            @if($signatureRequest->workflow_type === 'sequential')
                                @php $nextSigner = $signatureRequest->getNextSigner(); @endphp
                                @if($nextSigner && $nextSigner->id === $currentUser->id)
                                    <a href="{{ route('signatures.sign', $signatureRequest) }}" class="btn btn-success">
                                        <i class="fas fa-signature me-2"></i>Tanda Tangani Sekarang
                                    </a>
                                @else
                                    <button class="btn btn-warning" disabled>
                                        <i class="fas fa-clock me-2"></i>Menunggu Giliran
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('signatures.sign', $signatureRequest) }}" class="btn btn-success">
                                    <i class="fas fa-signature me-2"></i>Tanda Tangani Sekarang
                                </a>
                            @endif

                            <button type="button" class="btn btn-outline-danger"
                                    onclick="rejectSignature({{ $signatureRequest->id }})">
                                <i class="fas fa-times me-2"></i>Tolak Permintaan
                            </button>
                        @elseif($userSigneeStatus === 'signed')
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Anda sudah menandatangani dokumen ini
                            </div>
                        @elseif($userSigneeStatus === 'rejected')
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                Anda telah menolak permintaan ini
                            </div>
                        @elseif(!$isSignee)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Anda bukan penandatangan untuk dokumen ini
                            </div>
                        @endif

                        <a href="{{ route('documents.download', $signatureRequest->document) }}"
                           class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Buka Dokumen
                        </a>

                        @if($signatureRequest->status === 'completed')
                        <a href="{{ route('signatures.download-signed', $signatureRequest) }}"
                           class="btn btn-outline-success">
                            <i class="fas fa-download me-2"></i>Download Signed
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Request Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">ℹ️ Informasi Permintaan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">ID Permintaan:</small>
                                <small class="font-monospace">#{{ str_pad($signatureRequest->id, 4, '0', STR_PAD_LEFT) }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Workflow:</small>
                                <small>{{ ucfirst($signatureRequest->workflow_type) }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Total Signees:</small>
                                <small>{{ $signatureRequest->signees->count() }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Sudah Signed:</small>
                                <small class="text-success">{{ $signatureRequest->signatures->where('status', 'signed')->count() }}</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Created:</small>
                                <small>{{ $signatureRequest->created_at->format('d M Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blockchain Info -->
            @if($signatureRequest->blockchain_tx_hash)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">🔗 Blockchain</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-link fa-2x text-primary mb-3"></i>
                        <h6>Tersimpan di Blockchain</h6>
                        <p class="text-muted small mb-3">
                            Permintaan ini telah disimpan dengan aman di blockchain untuk audit trail.
                        </p>
                        <a href="https://polygonscan.com/tx/{{ $signatureRequest->blockchain_tx_hash }}"
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>View on Explorer
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function rejectSignature(requestId) {
    Swal.fire({
        title: 'Tolak Permintaan Tanda Tangan?',
        text: 'Anda yakin ingin menolak permintaan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
        input: 'textarea',
        inputPlaceholder: 'Berikan alasan penolakan...',
        inputValidator: (value) => {
            if (!value) {
                return 'Alasan penolakan harus diisi!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/signatures/${requestId}/reject`;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = result.value;

            form.appendChild(csrfInput);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush