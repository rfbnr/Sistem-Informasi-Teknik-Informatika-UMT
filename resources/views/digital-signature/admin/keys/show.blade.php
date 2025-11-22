{{-- resources/views/digital-signature/admin/keys/show.blade.php --}}
{{--
    IMPROVED CERTIFICATE VIEWER

    Fitur yang telah diperbaiki:
    1. Certificate Modal - Menampilkan informasi REAL dari X.509 certificate
       - Subject DN (Common Name, OU, Organization, Country, State, Locality, Email)
       - Issuer DN (Common Name, OU, Organization, Country)
       - Certificate Version (X.509 v3)
       - Serial Number (real dari OpenSSL, bukan MD5 hash)
       - Validity Period dengan status badge (expired/expiring/valid)
       - Cryptographic Algorithms (Public Key & Signature Algorithm)
       - SHA-256 & SHA-1 Fingerprints (real dari OpenSSL)
       - Self-Signed Certificate detection
       - Days remaining calculation

    2. UI/UX Improvements:
       - Modal size diubah ke modal-xl untuk lebih luas
       - Informasi dikategorikan dalam cards dengan warna berbeda
       - Copy to clipboard dengan toast notification
       - Distinguished Name (DN) ditampilkan dalam format standar
       - Badge untuk validity status
       - Responsive design
       - Security information section

    3. Data Source: Real OpenSSL parsing dari DigitalSignatureController::parseCertificateInfo()
--}}
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

/* Certificate Modal Styles */
#certificateModal .card {
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

#certificateModal .card-header {
    font-weight: 600;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

#certificateModal code {
    background: #f8f9fc;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
}

.certificate-details .copy-btn {
    cursor: pointer;
    color: #4e73df;
    transition: all 0.2s;
}

.certificate-details .copy-btn:hover {
    color: #2e59d9;
    transform: scale(1.2);
}

/* Toast notification styles */
.toast {
    min-width: 250px;
}

.toast-header {
    border-bottom: none;
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
                    {{-- <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-outline-light me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a> --}}
                    <div>
                        <h1 class="mb-1">
                            <i class="fas fa-key me-2"></i>
                            Signature Key Details
                        </h1>
                        <p class="mb-0 opacity-75">{{ $key->signature_id }}</p>
                    </div>
                </div>
            </div>
            {{-- Back Button --}}
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to Keys
                </a>
            </div>
            {{-- <div class="col-lg-4 text-end">
                @if($key->status != 'revoked')
                    <button class="btn btn-danger" onclick="showRevokeModal()">
                        <i class="fas fa-ban me-1"></i> Revoke Key
                    </button>
                @endif
            </div> --}}
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
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Requester:</strong></div>
                            <div class="col-md-8">
                                {{
                                    $key->user->name ?? 'N/A'
                                }}
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="row">
                            <div class="col-md-4"><strong>Email Requester:</strong></div>
                            <div class="col-md-8">
                                {{
                                    $key->user->email ?? 'N/A'
                                }}
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

                    {{-- @if($key->status == 'revoked')
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
                    @endif --}}

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
                    {{-- <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('{{ $key->getPublicKeyFingerprint() }}')">
                            <i class="fas fa-copy"></i> Copy Fingerprint
                        </button>
                        <a href="{{ route('admin.signature.keys.export.public', $key->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i> Export Public Key
                        </a>
                    </div> --}}
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

                    {{-- ✅ NEW: Signature Format Information --}}
                    @if($signatureFormat)
                        <div class="info-row">
                            <div class="row">
                                <div class="col-md-4"><strong>Signature Format:</strong></div>
                                <div class="col-md-8">
                                    @if($signatureFormat === 'pkcs7_cms_detached')
                                        <span class="badge bg-success">
                                            <i class="fas fa-certificate me-1"></i> PKCS#7 CMS Detached
                                        </span>
                                        <p class="small text-muted mb-0 mt-1">Industry-standard cryptographic message syntax</p>
                                    @elseif($signatureFormat === 'legacy_hash_only')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-hashtag me-1"></i> Legacy Hash-Only
                                        </span>
                                    @else
                                        <span class="text-muted">{{ $signatureFormat }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ✅ NEW: Signing Implementation Method --}}
                    @if($signingMethod)
                        <div class="info-row">
                            <div class="row">
                                <div class="col-md-4"><strong>Implementation:</strong></div>
                                <div class="col-md-8">
                                    @if($signingMethod === 'one_pass_tcpdf')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-bolt me-1"></i> ONE-PASS TCPDF
                                        </span>
                                        <p class="small text-muted mb-0 mt-1">Combined QR embedding and PDF signing</p>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ strtoupper(str_replace('_', ' ', $signingMethod)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('admin.signature.documents.show', $key->documentSignature->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Document Details
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- ✅ NEW: CMS Signature Status Card --}}
            @if($key->documentSignature && $key->documentSignature->signed_at && $key->documentSignature->cms_signature)
            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>
                        CMS PKCS#7 Signature Status
                    </h5>
                </div>
                <div class="card-body">
                    @if($signatureFormat === 'pkcs7_cms_detached')
                        <div class="alert alert-success mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                <div>
                                    <strong>PKCS#7 CMS Signature Active</strong>
                                    <div class="small mt-1">Industry-standard cryptographic message syntax</div>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="row">
                                <div class="col-md-5"><strong>Signature Format:</strong></div>
                                <div class="col-md-7">
                                    <code class="badge bg-primary">pkcs7_cms_detached</code>
                                </div>
                            </div>
                        </div>

                        @if($signingMethod === 'one_pass_tcpdf')
                            <div class="info-row">
                                <div class="row">
                                    <div class="col-md-5"><strong>Implementation:</strong></div>
                                    <div class="col-md-7">
                                        <span class="badge bg-success">ONE-PASS TCPDF</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="info-row">
                            <div class="row">
                                <div class="col-md-5"><strong>Signature Created:</strong></div>
                                <div class="col-md-7">
                                    <small>{{ $key->documentSignature->signed_at->format('d M Y H:i:s') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 p-2 bg-light rounded">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                This document uses PKCS#7 CMS (Cryptographic Message Syntax) for digital signatures,
                                ensuring compatibility with Adobe Reader and other PDF viewers.
                            </small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Legacy Signature Format</strong>
                            <div class="small mt-1">This document uses legacy hash-only signature format</div>
                        </div>
                    @endif
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
                        {{-- @if($key->certificate)
                        <button class="btn btn-primary" onclick="viewCertificate()">
                            <i class="fas fa-certificate"></i> View Certificate
                        </button>
                        @endif --}}

                        {{-- <a href="{{ route('admin.signature.keys.export.public', $key->id) }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Export Public Key
                        </a> --}}

                        <a href="{{ route('admin.signature.keys.audit', $key->id) }}" class="btn btn-info">
                            <i class="fas fa-history"></i> View Audit Log
                        </a>

                        {{-- @if($key->status != 'revoked')
                        <button class="btn btn-danger" onclick="showRevokeModal()">
                            <i class="fas fa-ban"></i> Revoke This Key
                        </button>
                        @endif --}}
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

                    {{-- ✅ NEW: Certificate Extensions Summary Badge --}}
                    @if($certificateInfo && isset($certificateInfo['extensions_validation']))
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Extensions Status:</small>
                            @if($certificateInfo['extensions_validation']['all_valid'])
                                <span class="badge bg-success w-100 py-2">
                                    <i class="fas fa-check-circle"></i> All Extensions Valid
                                </span>
                            @elseif($certificateInfo['extensions_validation']['critical_valid'])
                                <span class="badge bg-warning w-100 py-2">
                                    <i class="fas fa-exclamation-triangle"></i> Critical Valid
                                </span>
                                <small class="text-muted d-block mt-1">Some non-critical extensions missing</small>
                            @else
                                <span class="badge bg-danger w-100 py-2">
                                    <i class="fas fa-times-circle"></i> Extensions Invalid
                                </span>
                            @endif
                        </div>
                    @endif

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
{{-- <div class="modal fade" id="revokeModal" tabindex="-1">
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
</div> --}}

<!-- Certificate Modal -->
<div class="modal fade" id="certificateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-certificate me-2"></i>
                    Digital Certificate Details - X.509
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="certificateContent" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat informasi sertifikat digital...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto">
                    <i class="fas fa-info-circle me-1"></i>
                    Informasi ini diambil dari sertifikat X.509 yang ter-generate secara otomatis
                </small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// function showRevokeModal() {
//     const modal = new bootstrap.Modal(document.getElementById('revokeModal'));
//     modal.show();
// }

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

            // Build Subject DN string
            let subjectDN = '';
            if (cert.subject.CN) subjectDN += `CN=${cert.subject.CN}, `;
            if (cert.subject.OU) subjectDN += `OU=${cert.subject.OU}, `;
            if (cert.subject.O) subjectDN += `O=${cert.subject.O}, `;
            if (cert.subject.L) subjectDN += `L=${cert.subject.L}, `;
            if (cert.subject.ST) subjectDN += `ST=${cert.subject.ST}, `;
            if (cert.subject.C) subjectDN += `C=${cert.subject.C}`;
            subjectDN = subjectDN.replace(/, $/, '');

            // Build Issuer DN string
            let issuerDN = '';
            if (cert.issuer.CN) issuerDN += `CN=${cert.issuer.CN}, `;
            if (cert.issuer.OU) issuerDN += `OU=${cert.issuer.OU}, `;
            if (cert.issuer.O) issuerDN += `O=${cert.issuer.O}, `;
            if (cert.issuer.C) issuerDN += `C=${cert.issuer.C}`;
            issuerDN = issuerDN.replace(/, $/, '');

            // Check if certificate is expired or expiring
            const validUntilDate = new Date(cert.valid_until);
            const now = new Date();
            const daysLeft = Math.ceil((validUntilDate - now) / (1000 * 60 * 60 * 24));

            let validityBadge = '';
            if (daysLeft < 0) {
                validityBadge = '<span class="badge bg-danger ms-2"><i class="fas fa-exclamation-triangle"></i> EXPIRED</span>';
            } else if (daysLeft <= 7) {
                validityBadge = `<span class="badge bg-danger ms-2"><i class="fas fa-clock"></i> ${daysLeft} days left</span>`;
            } else if (daysLeft <= 30) {
                validityBadge = `<span class="badge bg-warning ms-2"><i class="fas fa-exclamation-circle"></i> ${daysLeft} days left</span>`;
            } else {
                validityBadge = `<span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> Valid</span>`;
            }

            const html = `
                <div class="certificate-details">
                    <!-- Certificate Overview -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-certificate fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">X.509 Digital Certificate</h6>
                                <small>Sertifikat digital ini digunakan untuk memverifikasi keaslian tanda tangan digital</small>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi Dasar Sertifikat</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Version:</strong></div>
                                <div class="col-md-8">
                                    <span class="badge bg-secondary">X.509 v${cert.version}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Serial Number:</strong></div>
                                <div class="col-md-8">
                                    <code class="text-primary">${cert.serial_number}</code>
                                    {{--
                                    <i class="fas fa-copy ms-2 copy-btn" onclick="copyCertData('${cert.serial_number}', 'Serial Number')" title="Copy"></i>
                                    --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-user-circle me-2"></i>
                            <strong>Subject (Pemilik Sertifikat)</strong>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light mb-3">
                                <small class="text-muted">Distinguished Name (DN):</small><br>
                                <code class="text-dark">${subjectDN}</code>
                                {{--
                                <i class="fas fa-copy ms-2 copy-btn" onclick="copyCertData('${subjectDN}', 'Subject DN')" title="Copy"></i>
                                --}}
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Common Name (CN):</strong></div>
                                <div class="col-md-8">${cert.subject.CN || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organizational Unit (OU):</strong></div>
                                <div class="col-md-8">${cert.subject.OU || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organization (O):</strong></div>
                                <div class="col-md-8">${cert.subject.O || 'N/A'}</div>
                            </div>
                            ${cert.subject.L ? `<div class="row mb-2">
                                <div class="col-md-4"><strong>Locality (L):</strong></div>
                                <div class="col-md-8">${cert.subject.L}</div>
                            </div>` : ''}
                            ${cert.subject.ST ? `<div class="row mb-2">
                                <div class="col-md-4"><strong>State/Province (ST):</strong></div>
                                <div class="col-md-8">${cert.subject.ST}</div>
                            </div>` : ''}
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Country (C):</strong></div>
                                <div class="col-md-8">${cert.subject.C || 'N/A'}</div>
                            </div>
                            ${cert.subject.emailAddress ? `<div class="row mb-2">
                                <div class="col-md-4"><strong>Email Address:</strong></div>
                                <div class="col-md-8"><a href="mailto:${cert.subject.emailAddress}">${cert.subject.emailAddress}</a></div>
                            </div>` : ''}
                        </div>
                    </div>

                    <!-- Issuer Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-building me-2"></i>
                            <strong>Issuer (Penerbit Sertifikat)</strong>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light mb-3">
                                <small class="text-muted">Distinguished Name (DN):</small><br>
                                <code class="text-dark">${issuerDN}</code>
                                {{--
                                <i class="fas fa-copy ms-2 copy-btn" onclick="copyCertData('${issuerDN}', 'Issuer DN')" title="Copy"></i>
                                --}}
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Common Name (CN):</strong></div>
                                <div class="col-md-8">${cert.issuer.CN || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organizational Unit (OU):</strong></div>
                                <div class="col-md-8">${cert.issuer.OU || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organization (O):</strong></div>
                                <div class="col-md-8">${cert.issuer.O || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Country (C):</strong></div>
                                <div class="col-md-8">${cert.issuer.C || 'N/A'}</div>
                            </div>

                            ${cert.issuer.CN === cert.subject.CN ?
                                '<div class="alert alert-info mt-2 mb-0"><i class="fas fa-info-circle me-2"></i><small><strong>Self-Signed Certificate</strong> - Issuer dan Subject sama (sertifikat ditandatangani sendiri)</small></div>'
                                : ''}
                        </div>
                    </div>

                    <!-- Validity Period -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Periode Validitas</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Valid From:</strong></div>
                                <div class="col-md-8">
                                    <i class="fas fa-calendar-check text-success me-2"></i>
                                    ${cert.valid_from}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Valid Until:</strong></div>
                                <div class="col-md-8">
                                    <i class="fas fa-calendar-times text-danger me-2"></i>
                                    ${cert.valid_until}
                                    ${validityBadge}
                                </div>
                            </div>
                            ${daysLeft >= 0 ? `
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Days Remaining:</strong></div>
                                <div class="col-md-8">
                                    <strong class="${daysLeft <= 7 ? 'text-danger' : (daysLeft <= 30 ? 'text-warning' : 'text-success')}">${daysLeft} hari</strong>
                                </div>
                            </div>` : `
                            <div class="alert alert-danger mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Sertifikat ini telah EXPIRED!</strong> Tidak dapat digunakan untuk verifikasi.
                            </div>`}
                        </div>
                    </div>

                    <!-- Cryptographic Algorithms -->
                    <div class="card mb-3">
                        <div class="card-header bg-dark text-white">
                            <i class="fas fa-lock me-2"></i>
                            <strong>Algoritma Kriptografi</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Public Key Algorithm:</strong></div>
                                <div class="col-md-8">
                                    <span class="badge bg-primary">${cert.public_key_algorithm}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Signature Algorithm:</strong></div>
                                <div class="col-md-8">
                                    <span class="badge bg-primary">${cert.signature_algorithm}</span>
                                </div>
                            </div>
                            <div class="alert alert-light mt-3 mb-0">
                                <small><i class="fas fa-shield-alt me-2"></i>Algoritma ini memastikan keamanan dan integritas tanda tangan digital</small>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ NEW: X.509 v3 Extensions -->
                    ${cert.extensions ? `
                    <div class="card mb-3">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>X.509 v3 Certificate Extensions</strong>
                        </div>
                        <div class="card-body">
                            ${cert.extensions.summary ? `
                            <div class="alert alert-${cert.extensions.all_valid ? 'success' : (cert.extensions.critical_valid ? 'warning' : 'danger')} mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-${cert.extensions.all_valid ? 'check-circle' : (cert.extensions.critical_valid ? 'exclamation-triangle' : 'times-circle')} fa-2x me-3"></i>
                                    <div>
                                        <strong>${cert.extensions.summary}</strong>
                                        ${cert.extensions.warnings && cert.extensions.warnings.length > 0 ? `
                                        <div class="small mt-1">
                                            ${cert.extensions.warnings.map(w => `<div><i class="fas fa-info-circle me-1"></i> ${w}</div>`).join('')}
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            ` : ''}

                            ${cert.extensions.checks ? Object.entries(cert.extensions.checks).map(([extName, extCheck]) => `
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="me-3 mt-1">
                                        ${extCheck.valid ?
                                            '<i class="fas fa-check-circle fa-lg text-success"></i>' :
                                            '<i class="fas fa-times-circle fa-lg text-danger"></i>'}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <strong>${extCheck.name}</strong>
                                            ${extCheck.critical ?
                                                '<span class="badge bg-danger">CRITICAL</span>' :
                                                '<span class="badge bg-secondary">Non-Critical</span>'}
                                        </div>
                                        <div class="small text-muted mb-2">${extCheck.description}</div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">Expected:</small><br>
                                                <code class="small">${extCheck.expected}</code>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Current Value:</small><br>
                                                ${extCheck.present ?
                                                    `<code class="small">${extCheck.value}</code>` :
                                                    '<span class="text-danger small">Not Present</span>'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `).join('') : ''}

                            <div class="small text-muted mt-3">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Note:</strong> X.509 v3 extensions provide additional constraints and capabilities for the certificate.
                                Critical extensions must be valid for the certificate to be trusted.
                            </div>
                        </div>
                    </div>
                    ` : ''}

                    <!-- Certificate Fingerprints -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-fingerprint me-2"></i>
                            <strong>Certificate Fingerprints</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>SHA-256 Fingerprint:</strong>
                                    {{--
                                    <button class="btn btn-sm btn-outline-primary" onclick="copyCertData('${cert.fingerprints.sha256}', 'SHA-256 Fingerprint')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    --}}
                                </div>
                                <code class="d-block bg-light p-2 rounded" style="word-break: break-all; font-size: 11px;">${cert.fingerprints.sha256}</code>
                                <small class="text-muted">Digunakan untuk verifikasi keaslian sertifikat</small>
                            </div>
                            <div class="mb-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>SHA-1 Fingerprint:</strong>
                                    {{--
                                    <button class="btn btn-sm btn-outline-primary" onclick="copyCertData('${cert.fingerprints.sha1}', 'SHA-1 Fingerprint')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                    --}}
                                </div>
                                <code class="d-block bg-light p-2 rounded" style="word-break: break-all; font-size: 11px;">${cert.fingerprints.sha1}</code>
                                <small class="text-muted">Fingerprint alternatif untuk kompatibilitas</small>
                            </div>
                        </div>
                    </div>

                    <!-- Security Information -->
                    <div class="alert alert-success mb-0">
                        <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Informasi Keamanan</h6>
                        <hr>
                        <ul class="mb-0 small">
                            <li>Sertifikat ini menggunakan enkripsi RSA dengan panjang kunci yang aman</li>
                            <li>Fingerprint dapat digunakan untuk memverifikasi keaslian sertifikat</li>
                            <li>Tanda tangan digital yang dibuat dengan key ini dapat diverifikasi secara publik</li>
                            ${cert.is_fallback ? '<li class="text-warning"><strong>Note:</strong> Format sertifikat fallback terdeteksi</li>' : ''}
                        </ul>
                    </div>
                </div>
            `;
            document.getElementById('certificateContent').innerHTML = html;
        } else {
            document.getElementById('certificateContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error:</strong> ${data.message || 'Gagal memuat informasi sertifikat'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Certificate fetch error:', error);
        document.getElementById('certificateContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Error:</strong> Gagal memuat detail sertifikat. Silakan coba lagi.
            </div>
        `;
    });
}

function copyCertData(text, label) {
    navigator.clipboard.writeText(text).then(() => {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Copied!</strong>
                    <button type="button" class="btn-close btn-close-white" onclick="this.closest('.position-fixed').remove()"></button>
                </div>
                <div class="toast-body">
                    ${label} berhasil disalin ke clipboard
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }).catch(() => {
        alert('Gagal menyalin ke clipboard');
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
