{{-- resources/views/digital-signature/user/my-signatures/index.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'My Digital Signatures')

@section('content')
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
                    <a href="{{ route('user.signature.approval.request') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                    <a href="{{ route('user.signature.approval.status') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i> All Status
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-primary">{{ $statistics['total'] ?? 0 }}</div>
                <div class="text-muted">Total Signatures</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $statistics['verified'] ?? 0 }}</div>
                <div class="text-muted">Verified</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-info">{{ $statistics['pending'] ?? 0 }}</div>
                <div class="text-muted">Pending</div>
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
                <div class="card h-100">
                    <div class="card-header {{ $signature->signature_status === 'verified' ? 'bg-success' : 'bg-info' }} text-white">
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
                        <div class="mb-3">
                            <strong>Document Number:</strong><br>
                            <code>{{ $signature->approvalRequest->full_document_number }}</code>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Signed At:</strong><br>
                                <small class="text-muted">
                                    @if($signature->signed_at)
                                        {{ $signature->signed_at->format('d M Y H:i') }}
                                    @else
                                        <span class="text-muted">Not signed yet</span>
                                    @endif
                                </small>
                            </div>
                            <div class="col-6">
                                <strong>Algorithm:</strong><br>
                                <span class="badge bg-info">
                                    {{ $signature->digitalSignature->algorithm ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        @if($signature->verified_at)
                        <div class="mb-3">
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Verified</strong> on {{ $signature->verified_at->format('d M Y H:i') }}
                            </div>
                        </div>
                        @endif

                        <!-- QR Code Preview -->
                        @if($signature->qr_code_path)
                        <div class="text-center mb-3 p-3 bg-light rounded">
                            <img src="{{ Storage::url($signature->qr_code_path) }}" alt="QR Code" style="max-width: 120px;">
                            <div class="small text-muted mt-2">Scan to verify</div>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <a href="{{ route('user.signature.my.signatures.show', $signature->id) }}"
                               class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fas fa-eye"></i> Details
                            </a>
                            @if($signature->approvalRequest->signed_document_path || $signature->final_pdf_path)
                                <a href="{{ route('user.signature.my.signatures.download', $signature->id) }}"
                                   class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            @endif
                            @if($signature->qr_code_path)
                                <a href="{{ route('user.signature.my.signatures.qr', $signature->id) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-muted small">
                        <i class="fas fa-clock me-1"></i>
                        Created {{ $signature->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $signatures->links() }}
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
