{{-- resources/views/digital-signature/admin/keys/show.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Signature Key Details - ' . $key->signature_id)

@section('sidebar')
    @include('digital-signature.admin.partials.sidebar')
@endsection

@push('styles')
<style>
.key-info-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.key-info-card.status-active {
    border-left-color: #1cc88a;
}

.key-info-card.status-revoked {
    border-left-color: #858796;
}

.key-info-card.status-expiring {
    border-left-color: #f6c23e;
}

.key-info-card.status-expired {
    border-left-color: #e74a3b;
}

.info-row {
    padding: 10px 0;
    border-bottom: 1px solid #e3e6f0;
}

.info-row:last-child {
    border-bottom: none;
}

.fingerprint-box {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 15px;
    font-family: 'Courier New', monospace;
    word-break: break-all;
}

.copy-btn {
    cursor: pointer;
    transition: all 0.2s;
}

.copy-btn:hover {
    transform: scale(1.1);
}

.cert-badge {
    padding: 8px 12px;
    border-radius: 6px;
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-outline-light me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="mb-1">
                            <i class="fas fa-key me-2"></i>
                            Signature Key Details
                        </h1>
                        <p class="mb-0 opacity-75">{{ $key->signature_id }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-end">
                @if($key->status != 'revoked')
                    <button class="btn btn-danger" onclick="showRevokeModal()">
                        <i class="fas fa-ban me-1"></i> Revoke Key
                    </button>
                @endif
            </div>
        </div>
    </div>

    @php
        $statusClass = 'active';

        if ($key->status == 'revoked') {
            $statusClass = 'status-revoked';
        } elseif ($daysUntilExpiry < 0) {
            $statusClass = 'expired';
        } elseif ($daysUntilExpiry <= 30) {
            $statusClass = 'status-expiring';
        }
    @endphp

    <!-- Key Information -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm key-info-card status-{{ $statusClass }}">
                <div class="card-header bg-gradient-primary text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Key Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Signature ID:</strong></div>
                            <div class="col-md-8">
                                <code class="text-primary">{{ $key->signature_id }}</code>
                                <i class="fas fa-copy copy-btn ms-2" onclick="copyToClipboard('{{ $key->signature_id }}')" title="Copy ID"></i>
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Algorithm:</strong></div>
                            <div class="col-md-8">{{ $key->algorithm }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Key Length:</strong></div>
                            <div class="col-md-8">{{ $key->key_length }} bits</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Status:</strong></div>
                            <div class="col-md-8">
                                @if($key->status == 'active')
                                    @if($daysUntilExpiry < 0)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle"></i> EXPIRED
                                        </span>
                                    @elseif($daysUntilExpiry <= 7)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-clock"></i> URGENT - Expires in {{ $daysUntilExpiry }} days
                                        </span>
                                    @elseif($daysUntilExpiry <= 30)
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-circle"></i> Expiring Soon ({{ $daysUntilExpiry }} days)
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    @endif
                                @elseif($key->status == 'revoked')
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-ban"></i> Revoked
                                    </span>
                                @else
                                    <span class="badge bg-info">{{ ucfirst($key->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Valid From:</strong></div>
                            <div class="col-md-8">{{ $key->valid_from->format('d F Y H:i:s') }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Valid Until:</strong></div>
                            <div class="col-md-8">
                                {{ $key->valid_until->format('d F Y H:i:s') }}
                                <br>
                                @if($daysUntilExpiry < 0)
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Expired {{ abs($daysUntilExpiry) }} days ago
                                    </small>
                                @elseif($daysUntilExpiry <= 30)
                                    <small class="text-warning">
                                        <i class="fas fa-clock"></i>
                                        {{ $daysUntilExpiry }} days remaining
                                    </small>
                                @else
                                    <small class="text-success">
                                        <i class="fas fa-check"></i>
                                        {{ $daysUntilExpiry }} days remaining
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($key->status == 'revoked')
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Revoked At:</strong></div>
                            <div class="col-md-8">
                                {{ $key->revoked_at ? $key->revoked_at->format('d F Y H:i:s') : 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Revocation Reason:</strong></div>
                            <div class="col-md-8">
                                <span class="text-danger">{{ $key->revocation_reason ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Created At:</strong></div>
                            <div class="col-md-8">{{ $key->created_at->format('d F Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Public Key Fingerprint -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-fingerprint me-2"></i>
                        Public Key Fingerprint
                    </h5>
                </div>
                <div class="card-body">
                    <div class="fingerprint-box">
                        <strong>SHA-256:</strong><br>
                        {{ $key->getPublicKeyFingerprint() }}
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('{{ $key->getPublicKeyFingerprint() }}')">
                            <i class="fas fa-copy"></i> Copy Fingerprint
                        </button>
                        <a href="{{ route('admin.signature.keys.export.public', $key->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i> Export Public Key
                        </a>
                    </div>
                </div>
            </div>

            <!-- Document Information -->
            @if($key->documentSignature && $key->documentSignature->approvalRequest)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Associated Document
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Document Name:</strong></div>
                            <div class="col-md-8">{{ $key->documentSignature->approvalRequest->document_name }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Document Type:</strong></div>
                            <div class="col-md-8">{{ ucfirst(str_replace('_', ' ', $key->documentSignature->approvalRequest->document_type)) }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Submitted By:</strong></div>
                            <div class="col-md-8">
                                {{ $key->documentSignature->approvalRequest->user->name ?? 'N/A' }}
                                ({{ $key->documentSignature->approvalRequest->user->NIM ?? 'N/A' }})
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Signed By:</strong></div>
                            <div class="col-md-8">{{ $key->documentSignature->signer->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Signed At:</strong></div>
                            <div class="col-md-8">
                                {{ $key->documentSignature->signed_at ? $key->documentSignature->signed_at->format('d F Y H:i:s') : 'Not signed yet' }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.signature.documents.show', $key->documentSignature->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Document Details
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Usage Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Usage Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-6 text-primary">
                            {{ $usageStats['total_verifications'] }}
                        </div>
                        <small class="text-muted">Total Verifications</small>
                    </div>

                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 text-success">{{ $usageStats['successful_verifications'] }}</div>
                            <small class="text-muted">Successful</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 text-info">{{ $usageStats['verification_rate'] }}%</div>
                            <small class="text-muted">Success Rate</small>
                        </div>
                    </div>

                    @if($usageStats['last_verification'])
                    <div class="mt-3 p-2 bg-light rounded">
                        <small class="text-muted">Last Verification:</small><br>
                        <strong>{{ $usageStats['last_verification']->diffForHumans() }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($key->certificate)
                        <button class="btn btn-primary" onclick="viewCertificate()">
                            <i class="fas fa-certificate"></i> View Certificate
                        </button>
                        @endif

                        <a href="{{ route('admin.signature.keys.export.public', $key->id) }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Export Public Key
                        </a>

                        <a href="{{ route('admin.signature.keys.audit', $key->id) }}" class="btn btn-info">
                            <i class="fas fa-history"></i> View Audit Log
                        </a>

                        @if($key->status != 'revoked')
                        <button class="btn btn-danger" onclick="showRevokeModal()">
                            <i class="fas fa-ban"></i> Revoke This Key
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Certificate Info -->
            @if($key->certificate)
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-certificate me-2"></i>
                        Certificate
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Digital certificate tersedia untuk key ini</p>
                    <button class="btn btn-primary w-100" onclick="viewCertificate()">
                        <i class="fas fa-eye"></i> View Certificate Details
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Revoke Modal -->
<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.signature.keys.revoke', $key->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-ban me-2"></i>
                        Revoke Signature Key
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Revoking this key akan menginvalidasi semua dokumen yang menggunakan key ini.
                    </div>

                    <div class="mb-3">
                        <strong>Key ID:</strong> {{ $key->signature_id }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alasan Revoke <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Jelaskan alasan melakukan revoke key ini (minimal 10 karakter)"></textarea>
                        <small class="text-muted">Minimal 10 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Ya, Revoke Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Certificate Modal -->
<div class="modal fade" id="certificateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-certificate me-2"></i>
                    Digital Certificate Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="certificateContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading certificate information...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showRevokeModal() {
    const modal = new bootstrap.Modal(document.getElementById('revokeModal'));
    modal.show();
}

function viewCertificate() {
    const modal = new bootstrap.Modal(document.getElementById('certificateModal'));
    modal.show();

    // Fetch certificate details via AJAX
    fetch('{{ route('admin.signature.keys.certificate', $key->id) }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cert = data.certificate;
            const html = `
                <div class="certificate-details">
                    <h6 class="border-bottom pb-2 mb-3">Certificate Information</h6>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Version:</strong></div>
                        <div class="col-8">${cert.version}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Serial Number:</strong></div>
                        <div class="col-8"><code>${cert.serial_number}</code></div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">Subject</h6>
                    <div class="row mb-2">
                        <div class="col-4"><strong>CN:</strong></div>
                        <div class="col-8">${cert.subject.CN}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>OU:</strong></div>
                        <div class="col-8">${cert.subject.OU}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>O:</strong></div>
                        <div class="col-8">${cert.subject.O}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>C:</strong></div>
                        <div class="col-8">${cert.subject.C}</div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">Issuer</h6>
                    <div class="row mb-2">
                        <div class="col-4"><strong>CN:</strong></div>
                        <div class="col-8">${cert.issuer.CN}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>O:</strong></div>
                        <div class="col-8">${cert.issuer.O}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>C:</strong></div>
                        <div class="col-8">${cert.issuer.C}</div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">Algorithms</h6>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Public Key:</strong></div>
                        <div class="col-8">${cert.public_key_algorithm}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Signature:</strong></div>
                        <div class="col-8">${cert.signature_algorithm}</div>
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">Fingerprints</h6>
                    <div class="mb-3">
                        <strong>SHA-256:</strong><br>
                        <code class="d-block bg-light p-2 rounded" style="word-break: break-all;">${cert.fingerprints.sha256}</code>
                    </div>
                    <div class="mb-3">
                        <strong>SHA-1:</strong><br>
                        <code class="d-block bg-light p-2 rounded" style="word-break: break-all;">${cert.fingerprints.sha1}</code>
                    </div>
                </div>
            `;
            document.getElementById('certificateContent').innerHTML = html;
        } else {
            document.getElementById('certificateContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('certificateContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                Failed to load certificate details
            </div>
        `;
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show toast or alert
        alert('Copied to clipboard!');
    });
}
</script>
@endpush
@endsection
