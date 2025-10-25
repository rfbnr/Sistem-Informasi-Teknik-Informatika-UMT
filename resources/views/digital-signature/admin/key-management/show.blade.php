{{-- resources/views/digital-signature/admin/key-details.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Signature Key Details')

@section('sidebar')
    @include('digital-signature.admin.partials.sidebar')
@endsection

@push('styles')
<style>
    .status-badge {
        padding: 0.25em 0.5em;
        border-radius: 0.25rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    .status-expired {
        background-color: #fff3cd;
        color: #856404;
    }
    .status-revoked {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-key me-3"></i>
                    Signature Key Details
                </h1>
                <p class="mb-0 opacity-75">{{ $signature->signature_id }}</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Keys
                </a>
                @if($signature->status === 'active')
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#revokeModal">
                        <i class="fas fa-ban me-1"></i> Revoke Key
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($signature->status === 'expired')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> This signature key has expired on {{ $signature->valid_until->format('d F Y') }}
        </div>
    @elseif($signature->status === 'revoked')
        <div class="alert alert-danger">
            <i class="fas fa-ban me-2"></i>
            <strong>Revoked:</strong> This signature key was revoked on {{ $signature->revoked_at->format('d F Y H:i') }}
            @if($signature->revocation_reason)
                <br><small>Reason: {{ $signature->revocation_reason }}</small>
            @endif
        </div>
    @elseif($signature->valid_until->diffInDays() < 30)
        <div class="alert alert-warning">
            <i class="fas fa-clock me-2"></i>
            <strong>Expiring Soon:</strong> This key will expire in {{ $signature->valid_until->diffForHumans() }}
        </div>
    @endif

    <div class="row">
        <!-- Key Information -->
        <div class="col-lg-8">
            <!-- Usage Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Usage Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="h3 text-primary">{{ $stats['total_documents_signed'] ?? 0 }}</div>
                            <small class="text-muted">Total Signatures</small>
                        </div>
                        <div class="col-md-4">
                            <div class="h3 text-success">{{ $stats['successful_signatures'] ?? 0 }}</div>
                            <small class="text-muted">Verified</small>
                        </div>
                        <div class="col-md-4">
                            <div class="h3 text-warning">{{ $stats['pending_signatures'] ?? 0 }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Key Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Signature ID:</strong><br>
                            <code>{{ $signature->signature_id }}</code>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong><br>
                            <span class="status-badge status-{{ strtolower($signature->status) }}">
                                {{ ucfirst($signature->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Algorithm:</strong><br>
                            <span class="badge bg-info">{{ $signature->algorithm }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Key Length:</strong><br>
                            <span class="badge bg-success">{{ $signature->key_length }} bits</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Purpose:</strong><br>
                            {{ $signature->signature_purpose }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Created:</strong><br>
                            {{ $signature->created_at->format('d F Y H:i:s') }}
                            <br><small class="text-muted">{{ $signature->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-6">
                            <strong>Valid Until:</strong><br>
                            {{ $signature->valid_until->format('d F Y H:i:s') }}
                            <br><small class="text-muted">{{ $signature->valid_until->diffForHumans() }}</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Created By:</strong><br>
                            {{ $signature->creator->name ?? 'System' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Key Fingerprint:</strong><br>
                            <code>{{ substr(hash('sha256', $signature->public_key), 0, 16) }}...</code>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Public Key -->
            {{-- <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-code me-2"></i>
                        Public Key (PEM Format)
                    </h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ $signature->public_key }}</code></pre>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyPublicKey()">
                        <i class="fas fa-copy me-1"></i> Copy to Clipboard
                    </button>
                </div>
            </div> --}}
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.signature.documents.index') }}?signature_id={{ $signature->id }}"
                           class="btn btn-outline-primary">
                            <i class="fas fa-file-signature me-2"></i> View Signed Documents
                        </a>
                        {{-- <button class="btn btn-outline-info" onclick="downloadPublicKey()">
                            <i class="fas fa-download me-2"></i> Download Public Key
                        </button>
                        @if($signature->status === 'active')
                            <button class="btn btn-outline-secondary" onclick="generateCertificate()">
                                <i class="fas fa-certificate me-2"></i> Generate Certificate
                            </button>
                        @endif --}}
                    </div>
                </div>
            </div>

            <!-- Key Health -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat me-2"></i>
                        Key Health
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Validity</span>
                            <span>{{ $signature->status === 'active' ? '100%' : '0%' }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $signature->status === 'active' ? 'bg-success' : 'bg-danger' }}"
                                 style="width: {{ $signature->status === 'active' ? '100' : '0' }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Time Remaining</span>
                            @php
                                $totalDays = $signature->created_at->diffInDays($signature->valid_until);
                                $remainingDays = now()->diffInDays($signature->valid_until, false);
                                $percentage = $totalDays > 0 ? max(0, ($remainingDays / $totalDays) * 100) : 0;
                            @endphp
                            <span>{{ round($percentage) }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $percentage > 30 ? 'bg-success' : 'bg-warning' }}"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <div class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Key is {{ $signature->status === 'active' ? 'healthy and operational' : 'no longer operational' }}
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    @if($signature->documentSignatures->count() > 0)
                        @foreach($signature->documentSignatures->take(5) as $docSig)
                            <div class="mb-2 pb-2 border-bottom">
                                <small>
                                    <strong>{{ $docSig->approvalRequest->document_name }}</strong><br>
                                    <span class="text-muted">
                                        {{ $docSig->signed_at ? $docSig->signed_at->format('d M Y H:i') : 'Pending' }}
                                    </span>
                                </small>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No activity yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revoke Key Modal -->
<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Revoke Digital Signature Key</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.signature.keys.revoke', $signature->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Revoking this key will invalidate all documents signed with it.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Revocation *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required
                                  placeholder="Enter detailed reason for revoking this signature key..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmRevoke" required>
                        <label class="form-check-label" for="confirmRevoke">
                            I understand that this action cannot be undone
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Revoke Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyPublicKey() {
    const publicKey = document.querySelector('pre code').textContent;
    navigator.clipboard.writeText(publicKey).then(() => {
        alert('Public key copied to clipboard!');
    });
}

function downloadPublicKey() {
    const publicKey = document.querySelector('pre code').textContent;
    const blob = new Blob([publicKey], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '{{ $signature->signature_id }}_public.pem';
    a.click();
    URL.revokeObjectURL(url);
}

function generateCertificate() {
    alert('Certificate generation feature coming soon!');
}
</script>
@endpush
