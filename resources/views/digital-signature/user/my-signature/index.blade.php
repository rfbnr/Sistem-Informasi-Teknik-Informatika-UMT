{{-- resources/views/digital-signature/user/my-signatures/index.blade.php --}}
{{-- @extends('digital-signature.layouts.app') --}}
@extends('user.layouts.app')


@section('title', 'My Digital Signatures')

@section('content')
<!-- Section Header -->
<section id="header-section">
    <h1>My Digital Signatures</h1>
</section>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-signature me-3"></i>
                    My Digital Signatures
                </h1>
                <p class="mb-0 opacity-75">View and manage your signed documents</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="btn-group">
                    <a href="{{ route('user.signature.approval.request') }}" class="btn btn-warning">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                    <a href="{{ route('user.signature.approval.status') }}" class="btn btn-outline-warning">
                        <i class="fas fa-list me-1"></i> All Status
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stats-card">
                <div class="stats-number text-primary">{{ $statistics['total'] ?? 0 }}</div>
                <div class="text-muted">Total</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $statistics['verified'] ?? 0 }}</div>
                <div class="text-muted">Verified</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card">
                <div class="stats-number text-info">{{ $statistics['pending'] ?? 0 }}</div>
                <div class="text-muted">Pending</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card">
                <div class="stats-number text-danger">{{ $statistics['rejected'] ?? 0 }}</div>
                <div class="text-muted">Rejected</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-warning">{{ $statistics['this_month'] ?? 0 }}</div>
                <div class="text-muted">This Month</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.signature.my.signatures.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search documents..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="signed" {{ request('status') == 'signed' ? 'selected' : '' }}>Signed</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="month">
                        <option value="">All Time</option>
                        <option value="current" {{ request('month') == 'current' ? 'selected' : '' }}>This Month</option>
                        <option value="last" {{ request('month') == 'last' ? 'selected' : '' }}>Last Month</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Signatures Grid -->
    @if($signatures->count() > 0)
        <div class="row">
            @foreach($signatures as $signature)
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header {{
                        $signature->signature_status === 'verified' ? 'bg-success' :
                        ($signature->signature_status === 'signed' ? 'bg-info' :
                        ($signature->signature_status === 'rejected' ? 'bg-danger' : 'bg-warning'))
                    }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-file-signature me-2"></i>
                                {{ $signature->approvalRequest->document_name }}
                            </h6>
                            <span class="badge bg-light text-dark">
                                {{ ucfirst($signature->signature_status) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Document Number & Signature ID -->
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <strong class="small">Document Number:</strong><br>
                                    <code class="small">{{ $signature->approvalRequest->full_document_number }}</code>
                                </div>
                                <div class="col-6">
                                    <strong class="small">Signature ID:</strong><br>
                                    <code class="small">{{ $signature->digitalSignature->signature_id ?? 'N/A' }}</code>
                                </div>
                            </div>
                        </div>

                        <!-- Document Type & Priority -->
                        @if($signature->approvalRequest->document_type || $signature->approvalRequest->priority)
                        <div class="mb-3">
                            @if($signature->approvalRequest->document_type)
                                <span class="badge bg-secondary me-1">
                                    <i class="fas fa-file-alt me-1"></i>{{ $signature->approvalRequest->document_type }}
                                </span>
                            @endif
                            @if($signature->approvalRequest->priority)
                                <span class="badge
                                    @if($signature->approvalRequest->priority === 'urgent') bg-danger
                                    @elseif($signature->approvalRequest->priority === 'high') bg-warning
                                    @elseif($signature->approvalRequest->priority === 'low') bg-light text-dark
                                    @else bg-secondary
                                    @endif">
                                    <i class="fas fa-flag me-1"></i>{{ ucfirst($signature->approvalRequest->priority) }}
                                </span>
                            @endif
                        </div>
                        @endif

                        <!-- Signer & Algorithm -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong class="small">Signed By:</strong><br>
                                <small class="text-muted">
                                    @if($signature->signer)
                                        {{ $signature->signer->name }}<br>
                                        <span class="text-muted" style="font-size: 0.75rem;">NIDN: {{ $signature->signer->NIDN ?? '-' }}</span>
                                    @else
                                        <span class="text-muted">Not signed yet</span>
                                    @endif
                                </small>
                            </div>
                            <div class="col-6">
                                <strong class="small">Algorithm:</strong><br>
                                <span class="badge bg-info">
                                    {{ $signature->digitalSignature->algorithm ?? 'N/A' }}
                                </span>
                                <br>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    {{ $signature->digitalSignature->key_length ?? 'N/A' }} bits
                                </small>
                            </div>
                        </div>

                        <!-- Signed At & Certificate Validity -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong class="small">Signed At:</strong><br>
                                <small class="text-muted">
                                    @if($signature->signed_at)
                                        {{ $signature->signed_at->format('d M Y H:i') }}
                                    @else
                                        <span class="text-muted">Not signed yet</span>
                                    @endif
                                </small>
                            </div>
                            <div class="col-6">
                                <strong class="small">Certificate:</strong><br>
                                @if($signature->digitalSignature)
                                    @if($signature->digitalSignature->isValid())
                                        <span class="badge bg-success small">
                                            <i class="fas fa-check-circle"></i> Valid
                                        </span>
                                    @else
                                        <span class="badge bg-danger small">
                                            <i class="fas fa-times-circle"></i> Invalid
                                        </span>
                                    @endif
                                    <br>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        Until {{ $signature->digitalSignature->valid_until->format('d M Y') }}
                                    </small>
                                @else
                                    <span class="badge bg-secondary small">N/A</span>
                                @endif
                            </div>
                        </div>

                        <!-- Rejection Status -->
                        @if($signature->signature_status === 'rejected')
                        <div class="mb-3">
                            <div class="alert alert-danger mb-0 py-2">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong class="small">Signature Rejected</strong>
                                <small class="d-block mt-1" style="font-size: 0.75rem;">
                                    <strong>Reason:</strong> {{ $signature->rejection_reason }}
                                </small>
                                @if($signature->rejected_at)
                                <small class="d-block text-muted" style="font-size: 0.75rem;">
                                    on {{ $signature->rejected_at->format('d M Y H:i') }}
                                    @if($signature->rejector)
                                        by {{ $signature->rejector->name }}
                                    @endif
                                </small>
                                @endif
                                <hr class="my-2">
                                <small class="d-block" style="font-size: 0.7rem;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Please submit a new request with the necessary corrections.
                                </small>
                            </div>
                        </div>
                        @endif

                        <!-- Verification Status -->
                        @if($signature->verified_at)
                        <div class="mb-3">
                            <div class="alert alert-success mb-0 py-2">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong class="small">Verified</strong>
                                <small class="d-block text-muted" style="font-size: 0.75rem;">
                                    on {{ $signature->verified_at->format('d M Y H:i') }}
                                    @if($signature->verifier)
                                        by {{ $signature->verifier->name }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        @endif

                        <!-- Approval Request Status -->
                        @if($signature->approvalRequest->status !== 'sign_approved')
                        <div class="mb-3">
                            <div class="alert alert-info mb-0 py-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong class="small">Document Status:</strong>
                                <span class="badge {{ $signature->approvalRequest->status_badge_class }} ms-1 text-black">
                                    {{ $signature->approvalRequest->status_label }}
                                </span>
                            </div>
                        </div>
                        @endif

                        <!-- QR Code Preview -->
                        @if($signature->qr_code_path && in_array($signature->signature_status, ['verified']))
                        <div class="text-center mb-3 p-3 bg-light rounded">
                            <img src="{{ Storage::url($signature->qr_code_path) }}" alt="QR Code" style="max-width: 120px;">
                            <div class="small text-muted mt-2">Scan to verify</div>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            {{-- REJECTED: Show submit new request button --}}
                            @if($signature->signature_status === 'rejected')
                                <a href="{{ route('user.signature.approval.request') }}"
                                   class="btn btn-sm btn-danger flex-fill">
                                    <i class="fas fa-redo"></i> Submit New Request
                                </a>
                                <a href="{{ route('user.signature.my.signatures.show', $signature->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            @else
                                <a href="{{ route('user.signature.my.signatures.show', $signature->id) }}"
                                   class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                                {{-- if pending user can self signing  --}}
                                @if($signature->signature_status === 'pending')
                                    <a href="{{ route('user.signature.sign.document', $signature->approvalRequest->id) }}"
                                       class="btn btn-sm btn-primary flex-fill">
                                        <i class="fas fa-signature"></i> Sign Now
                                    </a>
                                @endif
                            @endif
                            @if(($signature->approvalRequest->signed_document_path || $signature->final_pdf_path) && $signature->signature_status === 'verified')
                                <a href="{{ route('user.signature.my.signatures.download', $signature->id) }}"
                                   class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            @endif
                            @if($signature->qr_code_path && $signature->signature_status === 'verified')
                                <a href="{{ route('user.signature.my.signatures.qr', $signature->id) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-muted small">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-clock me-1"></i>
                                Created {{ $signature->created_at->diffForHumans() }}
                            </span>
                            {{-- @if($signature->approvalRequest->deadline)
                                @if($signature->approvalRequest->isOverdue())
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                    </span>
                                @elseif($signature->approvalRequest->isNearDeadline())
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock"></i> Deadline Soon
                                    </span>
                                @endif
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        {{-- <div class="mt-4">
            {{ $signatures->links() }}
        </div> --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $signatures->withQueryString()->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-signature fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Signatures Yet</h4>
                <p class="text-muted">You haven't signed any documents yet.</p>
                <a href="{{ route('user.signature.approval.request') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Submit Your First Document
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh if there are pending signatures
@if($statistics['pending'] > 0)
    setInterval(function() {
        if (!document.hidden) {
            location.reload();
        }
    }, 60000); // Refresh every minute
@endif
</script>
@endpush
