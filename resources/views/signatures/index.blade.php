@extends('user.layouts.app')

@section('title', 'Tanda Tangan Digital')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h3 mb-1 text-primary">🖋️ Tanda Tangan Digital</h2>
                            <p class="text-muted mb-0">Kelola permintaan tanda tangan dan verifikasi dokumen</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-file-alt me-2"></i>Dokumen Saya
                            </a>
                            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Upload Dokumen
                            </a>
                        </div>
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
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h5 class="card-title">{{ $pendingSignatures->total() }}</h5>
                    <p class="card-text text-muted">Menunggu Tanda Tangan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h5 class="card-title">{{ $completedSignatures->total() }}</h5>
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
                    <h5 class="card-title">{{ $pendingSignatures->total() + $completedSignatures->total() }}</h5>
                    <p class="card-text text-muted">Total Terverifikasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                    <h5 class="card-title">{{ $pendingSignatures->where('created_at', '>=', now()->startOfMonth())->count() }}</h5>
                    <p class="card-text text-muted">Bulan Ini</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" id="signatureTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab"
                                    data-bs-target="#pending" type="button" role="tab">
                                <i class="fas fa-clock me-2"></i>
                                Menunggu Tanda Tangan <span class="badge bg-warning ms-2">{{ $pendingSignatures->total() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="completed-tab" data-bs-toggle="tab"
                                    data-bs-target="#completed" type="button" role="tab">
                                <i class="fas fa-check-circle me-2"></i>
                                Sudah Ditandatangani <span class="badge bg-success ms-2">{{ $completedSignatures->total() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="signatureTabsContent">
                        <!-- Pending Signatures Tab -->
                        <div class="tab-pane fade show active" id="pending" role="tabpanel">
                            @if($pendingSignatures->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Dokumen</th>
                                                <th>Peminta</th>
                                                <th>Prioritas</th>
                                                <th>Deadline</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingSignatures as $request)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                                                            <i class="fas fa-file-pdf text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $request->title }}</h6>
                                                            <small class="text-muted">{{ $request->document->title }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-semibold">{{ $request->requester->name }}</div>
                                                        <small class="text-muted">{{ $request->requester->NIM }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $priorityColors = [
                                                            'low' => 'secondary',
                                                            'medium' => 'info',
                                                            'high' => 'warning',
                                                            'urgent' => 'danger'
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $priorityColors[$request->priority] ?? 'secondary' }}">
                                                        {{ ucfirst($request->priority) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($request->deadline)
                                                        <small class="text-muted">
                                                            {{ $request->deadline->format('d M Y') }}<br>
                                                            <span class="text-{{ $request->deadline->isPast() ? 'danger' : 'success' }}">
                                                                {{ $request->deadline->diffForHumans() }}
                                                            </span>
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('signatures.show', $request) }}">
                                                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-primary"
                                                                   href="{{ route('signatures.sign', $request) }}">
                                                                    <i class="fas fa-signature me-2"></i>Tanda Tangani
                                                                </a>
                                                            </li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger"
                                                                   href="#"
                                                                   onclick="rejectSignature({{ $request->id }})">
                                                                    <i class="fas fa-times me-2"></i>Tolak
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
                                    {{ $pendingSignatures->appends(['tab' => 'pending'])->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="fas fa-inbox fa-3x"></i>
                                    </div>
                                    <h5>Tidak Ada Permintaan Pending</h5>
                                    <p class="text-muted">Saat ini tidak ada dokumen yang menunggu tanda tangan Anda</p>
                                </div>
                            @endif
                        </div>

                        <!-- Completed Signatures Tab -->
                        <div class="tab-pane fade" id="completed" role="tabpanel">
                            @if($completedSignatures->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Dokumen</th>
                                                <th>Peminta</th>
                                                <th>Tanggal TTD</th>
                                                <th>Verifikasi</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($completedSignatures as $request)
                                            @php
                                                $mySignature = $request->signatures->where('signer_id', auth()->id())->first();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                                                            <i class="fas fa-file-pdf text-success"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $request->title }}</h6>
                                                            <small class="text-muted">{{ $request->document->title }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <div class="fw-semibold">{{ $request->requester->name }}</div>
                                                        <small class="text-muted">{{ $request->requester->NIM }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($mySignature)
                                                        <small class="text-muted">
                                                            {{ $mySignature->signed_at->format('d M Y') }}<br>
                                                            {{ $mySignature->signed_at->format('H:i') }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($mySignature && $mySignature->verification_code)
                                                        <small class="font-monospace text-success">
                                                            {{ $mySignature->verification_code }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Signed
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('signatures.show', $request) }}">
                                                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                                                </a>
                                                            </li>
                                                            @if($request->status === 'completed')
                                                            <li>
                                                                <a class="dropdown-item"
                                                                   href="{{ route('signatures.download-signed', $request) }}">
                                                                    <i class="fas fa-download me-2"></i>Download Signed
                                                                </a>
                                                            </li>
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item"
                                                                   href="#"
                                                                   onclick="verifySignature('{{ $mySignature->verification_code ?? '' }}')">
                                                                    <i class="fas fa-shield-alt me-2"></i>Verifikasi
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
                                    {{ $completedSignatures->appends(['tab' => 'completed'])->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="fas fa-signature fa-3x"></i>
                                    </div>
                                    <h5>Belum Ada Tanda Tangan</h5>
                                    <p class="text-muted">Anda belum menandatangani dokumen apapun</p>
                                </div>
                            @endif
                        </div>
                    </div>
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
                <h5 class="modal-title">Verifikasi Tanda Tangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="verificationContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle tab switching with URL persistence
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');

    if (activeTab === 'completed') {
        $('#completed-tab').tab('show');
    }

    // Update URL when tab changes
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const tabId = e.target.getAttribute('data-bs-target').replace('#', '');
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    });
});

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

function verifySignature(verificationCode) {
    if (!verificationCode) {
        Swal.fire({
            icon: 'error',
            title: 'Kode Verifikasi Tidak Ditemukan',
            text: 'Tanda tangan ini belum memiliki kode verifikasi.'
        });
        return;
    }

    $('#verificationModal').modal('show');
    $('#verificationContent').html(`
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memverifikasi tanda tangan...</p>
        </div>
    `);

    $.post('{{ route("signatures.verify") }}', {
        verification_code: verificationCode,
        _token: '{{ csrf_token() }}'
    })
    .done(function(data) {
        if (data.success) {
            const signature = data.signature;
            let statusIcon = signature.overall_status === 'verified' ?
                '<i class="fas fa-check-circle text-success fa-2x"></i>' :
                '<i class="fas fa-exclamation-triangle text-danger fa-2x"></i>';

            let statusText = signature.overall_status === 'verified' ?
                'Tanda Tangan Valid dan Terpercaya' : 'Tanda Tangan Bermasalah';

            $('#verificationContent').html(`
                <div class="text-center">
                    ${statusIcon}
                    <h5 class="mt-3">${statusText}</h5>
                    <div class="row mt-4">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Verifikasi Lokal</h6>
                                <span class="badge ${signature.local_verification ? 'bg-success' : 'bg-danger'}">
                                    ${signature.local_verification ? 'Valid' : 'Invalid'}
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6>Verifikasi Blockchain</h6>
                                <span class="badge ${signature.blockchain_verification ? 'bg-success' : 'bg-danger'}">
                                    ${signature.blockchain_verification ? 'Valid' : 'Invalid'}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="border rounded p-3 bg-light">
                            <h6>Detail Tanda Tangan</h6>
                            <div class="text-start">
                                <small class="text-muted">
                                    <strong>Penandatangan:</strong> ${signature.signer_name}<br>
                                    <strong>Tanggal:</strong> ${new Date(signature.signed_at).toLocaleString()}<br>
                                    <strong>Dokumen:</strong> ${signature.document_title}<br>
                                    <strong>Metode:</strong> ${signature.signature_method}<br>
                                    <strong>Kode Verifikasi:</strong> ${signature.verification_code}
                                </small>
                            </div>
                        </div>
                    </div>
                    ${signature.blockchain_tx_hash ? `
                        <div class="mt-3">
                            <a href="https://polygonscan.com/tx/${signature.blockchain_tx_hash}"
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-2"></i>Lihat di Blockchain Explorer
                            </a>
                        </div>
                    ` : ''}
                </div>
            `);
        } else {
            $('#verificationContent').html(`
                <div class="text-center">
                    <i class="fas fa-times-circle text-danger fa-2x"></i>
                    <h5 class="mt-3">Verifikasi Gagal</h5>
                    <p class="text-muted">${data.message || 'Terjadi kesalahan saat verifikasi'}</p>
                </div>
            `);
        }
    })
    .fail(function() {
        $('#verificationContent').html(`
            <div class="text-center">
                <i class="fas fa-times-circle text-danger fa-2x"></i>
                <h5 class="mt-3">Gagal Memverifikasi</h5>
                <p class="text-muted">Terjadi kesalahan saat memverifikasi tanda tangan</p>
            </div>
        `);
    });
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush