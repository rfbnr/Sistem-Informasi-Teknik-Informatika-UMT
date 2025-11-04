{{-- resources/views/digital-signature/admin/dashboard.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Digital Signature Dashboard')

@section('sidebar')
    @include('digital-signature.admin.partials.sidebar')
@endsection

@push('styles')
<style>
.stats-card.clickable {
    cursor: pointer;
    transition: all 0.3s ease;
}

.stats-card.clickable:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
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
                    <i class="fas fa-tachometer-alt me-3"></i>
                    Digital Signature Dashboard
                </h1>
                <p class="mb-0 opacity-75">Manage digital signatures, documents, and verification processes</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-light" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    {{-- <a href="{{ route('admin.signature.keys.create') }}" class="btn btn-warning">
                        <i class="fas fa-plus me-1"></i> New Signature
                    </a> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-primary">{{ $stats['total_signatures'] ?? 0 }}</div>
                <div class="text-muted small">Total Signatures</div>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="fas fa-check-circle"></i>
                        {{ $stats['active_signatures'] ?? 0 }} Active
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="stats-card clickable" onclick="window.location='{{ route('admin.signature.approval.index') }}'">
                <div class="stats-number text-warning">{{ $stats['pending_approvals'] ?? 0 }}</div>
                <div class="text-muted small">Pending Approvals</div>
                <div class="mt-2">
                    <small class="text-primary">
                        <i class="fas fa-hand-pointer"></i>
                        Need Action
                    </small>
                </div>
            </div>
        </div>
        {{-- <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card clickable" onclick="window.location='{{ route('admin.signature.documents.index', ['status' => 'signed']) }}'">
                <div class="stats-number text-info">{{ $stats['need_verification'] ?? 0 }}</div>
                <div class="text-muted small">Need Verification</div>
                <div class="mt-2">
                    <small class="text-warning">
                        <i class="fas fa-clipboard-check"></i>
                        To Verify
                    </small>
                </div>
            </div>
        </div> --}}
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $stats['verified_signatures'] ?? 0 }}</div>
                <div class="text-muted small">Verified</div>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="fas fa-shield-alt"></i>
                        {{ $verificationStats['verification_rate'] ?? 0 }}%
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-danger">{{ $stats['rejected_signatures'] ?? 0 }}</div>
                <div class="text-muted small">Rejected</div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-times-circle"></i>
                        Signatures
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-secondary">{{ $stats['expired_signatures'] ?? 0 }}</div>
                <div class="text-muted small">Expired Keys</div>
                <div class="mt-2">
                    <small class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Renew
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Approvals - Quick Approve -->
        <div class="col-lg-8 mb-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check me-2"></i>
                            Pending Approvals
                        </h5>
                        <span class="badge bg-dark">{{ $stats['pending_approvals'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingApprovals && $pendingApprovals->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($pendingApprovals as $approval)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="fas fa-file-alt me-1 text-primary"></i>
                                            {{ Str::limit($approval->document_name, 40) }}
                                        </h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-file-signature me-1"></i>
                                            Type: {{ ucfirst(str_replace('_', ' ', $approval->document_type)) }}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $approval->user->name ?? 'Unknown' }} ({{ $approval->user->NIM ?? 'N/A' }})
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $approval->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <button class="btn btn-sm btn-success mb-1 w-100"
                                                onclick="showApproveModal({{ $approval->id }}, '{{ $approval->document_name }}', '{{ $approval->document_type }}')">
                                            <i class="fas fa-check me-1"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-info mb-1 w-100"
                                        onclick="viewDocument({{ $approval->id }}, '{{ asset('storage/' . $approval->document_path) }}')">
                                            <i class="fas fa-file-alt me-1"></i> View
                                        </button>
                                        <a href="{{ route('admin.signature.approval.show', $approval->id) }}"
                                           class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-eye me-1"></i> Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.signature.approval.index') }}" class="btn btn-warning">
                                <i class="fas fa-list me-1"></i> View All Pending ({{ $stats['pending_approvals'] }})
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">All Caught Up!</h5>
                            <p class="text-muted">No pending approval requests at the moment</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Digital Signature Keys Widget -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            Signature Keys
                        </h5>
                        <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Key Stats -->
                    <div class="row text-center mb-3">
                        <div class="col-6 mb-2">
                            <div class="h5 text-success mb-0">{{ $keyStats['active_keys'] }}</div>
                            <small class="text-muted">Active Keys</small>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="h5 text-secondary mb-0">{{ $keyStats['revoked_keys'] }}</div>
                            <small class="text-muted">Revoked</small>
                        </div>
                    </div>

                    <!-- Alert Section -->
                    @if($keyStats['urgent_expiry'] > 0)
                    <div class="alert alert-danger mb-3 py-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>{{ $keyStats['urgent_expiry'] }}</strong> keys expire in < 7 days!
                    </div>
                    @endif

                    @if($keyStats['expiring_soon'] > 0 && $keyStats['urgent_expiry'] == 0)
                    <div class="alert alert-warning mb-3 py-2">
                        <i class="fas fa-clock me-1"></i>
                        <strong>{{ $keyStats['expiring_soon'] }}</strong> keys expiring soon (30d)
                    </div>
                    @endif

                    <!-- Expiring Keys List -->
                    @if($expiringKeys->count() > 0)
                    <div class="mb-2">
                        <strong class="small">Expiring Soon:</strong>
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                        @foreach($expiringKeys as $key)
                        @php
                            $daysLeft = (int) now()->diffInDays($key->valid_until, false);
                        @endphp
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="small">
                                        <code class="text-primary">{{ Str::limit($key->signature_id, 12) }}</code>
                                    </div>
                                    @if($key->documentSignature && $key->documentSignature->approvalRequest)
                                    <div class="small text-muted">
                                        {{ Str::limit($key->documentSignature->approvalRequest->document_name, 25) }}
                                    </div>
                                    @endif
                                </div>
                                <div class="ms-2">
                                    @if($daysLeft <= 7)
                                        <span class="badge bg-danger small">{{ $daysLeft }} d</span>
                                    @else
                                        <span class="badge bg-warning small">{{ $daysLeft }} d</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="small text-muted mb-0">All keys are healthy!</p>
                    </div>
                    @endif

                    <!-- Action Button -->
                    <div class="mt-3 d-grid">
                        <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-key me-1"></i> Manage All Keys
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <!-- Need Verification - Quick Verify -->
        {{-- <div class="col-lg-6 mb-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Need Verification
                        </h5>
                        <span class="badge bg-dark">{{ $stats['need_verification'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($needVerification && $needVerification->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($needVerification as $docSig)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="fas fa-file-signature me-1 text-info"></i>
                                            {{ Str::limit($docSig->approvalRequest->document_name ?? 'N/A', 40) }}
                                        </h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $docSig->approvalRequest->user->name ?? 'Unknown' }} ({{ $docSig->approvalRequest->user->NIM }})
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-pen-fancy me-1"></i>
                                            Signed by: {{ $docSig->signer->name ?? 'N/A' }}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $docSig->signed_at ? $docSig->signed_at->diffForHumans() : 'Unknown' }}
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <button class="btn btn-sm btn-primary mb-1 w-100"
                                                onclick="quickPreviewDocument({{ $docSig->id }})"
                                                    title="Quick Preview & Verify">
                                            <i class="fas fa-check-double me-1"></i> Preview & Verify
                                        </button>
                                        <a href="{{ route('admin.signature.documents.show', $docSig->id) }}"
                                           class="btn btn-sm btn-outline-info w-100">
                                            <i class="fas fa-eye me-1"></i> Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.signature.documents.index', ['status' => 'signed']) }}" class="btn btn-info">
                                <i class="fas fa-list me-1"></i> View All Signed ({{ $stats['need_verification'] }})
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-double fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">All Verified!</h5>
                            <p class="text-muted">No signed documents needing verification</p>
                        </div>
                    @endif
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Second Row: Recent Signatures & Quick Actions -->
    {{-- <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Recent Digital Signature Keys
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentSignatures && $recentSignatures->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Signature ID</th>
                                        <th>Algorithm</th>
                                        <th>Status</th>
                                        <th>Valid Until</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSignatures->take(5) as $signature)
                                    <tr>
                                        <td><code class="small">{{ Str::limit($signature->signature_id, 20) }}</code></td>
                                        <td><span class="badge bg-info">{{ $signature->algorithm }}</span></td>
                                        <td>
                                            <span class="status-badge status-{{ strtolower($signature->status) }}">
                                                {{ ucfirst($signature->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $signature->valid_until->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.signature.keys.view', $signature->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Manage All Signature Keys
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Digital Signatures</h5>
                            <p class="text-muted">Create your first digital signature to get started</p>
                            <a href="{{ route('admin.signature.keys.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Create Signature
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & System Status -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.signature.keys.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i> Create New Signature
                        </a>
                        <a href="{{ route('admin.signature.documents.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-file-signature me-2"></i> View Documents
                        </a>
                        <a href="{{ route('admin.signature.reports.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-chart-bar me-2"></i> Reports & Analytics
                        </a>
                        <a href="{{ route('admin.signature.templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-palette me-2"></i> Manage Templates
                        </a>
                    </div>
                </div>
            </div>

            <!-- Expiring Signatures Alert -->
            @if($expiringSignatures && $expiringSignatures->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Expiring Signatures
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($expiringSignatures as $signature)
                    <div class="alert alert-warning alert-dismissible fade show mb-2" role="alert">
                        <small>
                            <strong>{{ Str::limit($signature->signature_id, 15) }}</strong><br>
                            Expires: {{ $signature->valid_until->format('d M Y') }}
                            <span class="text-muted">
                                ({{ $signature->valid_until->diffForHumans() }})
                            </span>
                        </small>
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                    </div>
                    @endforeach
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.signature.keys.index') }}?filter=expiring" class="btn btn-warning btn-sm">
                            <i class="fas fa-clock me-1"></i> View All Expiring
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- System Status -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2"></i>
                        System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="mb-2">
                                <i class="fas fa-circle text-success"></i>
                                <span class="ms-1">System</span>
                            </div>
                            <small class="text-muted">Online</small>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <i class="fas fa-circle text-success"></i>
                                <span class="ms-1">Verification</span>
                            </div>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            Last updated: {{ now()->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Verification Statistics Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Verification Statistics (Last 30 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 text-center mb-3">
                            <div class="h4 text-info">{{ $verificationStats['total_signatures'] ?? 0 }}</div>
                            <div class="text-muted">Total Signatures</div>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center mb-3">
                            <div class="h4 text-success">{{ $verificationStats['verified_signatures'] ?? 0 }}</div>
                            <div class="text-muted">Verified</div>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center mb-3">
                            <div class="h4 text-primary">{{ $verificationStats['verification_rate'] ?? 0 }}%</div>
                            <div class="text-muted">Success Rate</div>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center mb-3">
                            <div class="h4 text-warning">{{ $verificationStats['period_days'] ?? 30 }}</div>
                            <div class="text-muted">Days Period</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Approve Modal -->
{{-- <div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Quick Approve Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickApproveForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You are about to approve this document request.
                    </div>
                    <div class="mb-3">
                        <strong>Document:</strong>
                        <div id="approveDocumentName" class="text-muted"></div>
                    </div>
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label">Approval Notes (Optional)</label>
                        <textarea class="form-control" id="approve_notes" name="notes" rows="3"
                                  placeholder="Add any notes for this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Approve Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<!-- Quick Verify Modal -->
{{-- <div class="modal fade" id="quickVerifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-shield-alt me-2"></i>
                    Quick Verify Signature
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Verifying the document signature will run all 7 security checks.
                </div>
                <div class="mb-3">
                    <strong>Document:</strong>
                    <div id="verifyDocumentName" class="text-muted"></div>
                </div>
                <div id="verifyProgressDiv" class="d-none">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                             style="width: 100%">Verifying...</div>
                    </div>
                </div>
                <div id="verifyResultDiv" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="verifyBtn" class="btn btn-primary" onclick="executeVerify()">
                    <i class="fas fa-check-double me-1"></i> Verify Now
                </button>
            </div>
        </div>
    </div>
</div> --}}

<!-- Include Modals -->
@include('digital-signature.admin.partials.quick-preview-signed-modal')
@include('digital-signature.admin.partials.reject-signed-modal')

@include('digital-signature.admin.approval-requests.partials.view-document-modal')
@include('digital-signature.admin.approval-requests.partials.approve-modal')

{{-- @include('digital-signature.admin.approval-requests.partials.reject-modal') --}}
{{-- @include('digital-signature.admin.approval-requests.partials.approve-signature-modal') --}}

@endsection

@push('scripts')
<script>
let currentApprovalId = null;
let currentVerifyId = null;

function refreshDashboard() {
    location.reload();
}

// Show Approve Modal
function showApproveModal(id, documentName, documentType) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    document.getElementById('approveRequestId').value = id;
    document.getElementById('approveDocumentName').textContent = documentName;
    document.getElementById('approveDocumentType').textContent = documentType;
    modal.show();
}

// View Document in Modal
function viewDocument(id, documentUrl) {
    const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
    const iframe = document.getElementById('documentIframe');
    const downloadBtn = document.getElementById('downloadDocumentBtn');

    iframe.src = documentUrl;
    downloadBtn.href = documentUrl;

    modal.show();
}

// QUICK VERIFY
function quickVerify(docSigId, documentName) {
    currentVerifyId = docSigId;
    document.getElementById('verifyDocumentName').textContent = documentName;
    document.getElementById('verifyProgressDiv').classList.add('d-none');
    document.getElementById('verifyResultDiv').classList.add('d-none');
    document.getElementById('verifyResultDiv').innerHTML = '';
    document.getElementById('verifyBtn').disabled = false;

    const modal = new bootstrap.Modal(document.getElementById('quickVerifyModal'));
    modal.show();
}

function executeVerify() {
    const progressDiv = document.getElementById('verifyProgressDiv');
    const resultDiv = document.getElementById('verifyResultDiv');
    const verifyBtn = document.getElementById('verifyBtn');

    progressDiv.classList.remove('d-none');
    resultDiv.classList.add('d-none');
    verifyBtn.disabled = true;

    fetch(`/admin/signature/documents/${currentVerifyId}/verify`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        progressDiv.classList.add('d-none');
        resultDiv.classList.remove('d-none');

        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Verification Successful!</h6>
                    <p class="mb-0 small">The document signature has been verified and approved.</p>
                </div>
            `;
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('quickVerifyModal')).hide();
                refreshDashboard();
            }, 2000);
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-times-circle me-2"></i>Verification Failed</h6>
                    <p class="mb-0 small">${data.message || data.error || 'Unknown error occurred'}</p>
                </div>
            `;
            verifyBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Verify error:', error);
        progressDiv.classList.add('d-none');
        resultDiv.classList.remove('d-none');
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Network Error</h6>
                <p class="mb-0 small">Failed to verify signature. Please try again.</p>
            </div>
        `;
        verifyBtn.disabled = false;
    });
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    const container = document.querySelector('.main-content');
    const firstChild = container.firstElementChild;
    firstChild.insertAdjacentHTML('afterend', alertHtml);

    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        });
    }, 5000);
}

// Auto-refresh dashboard every 3 minutes
setInterval(function() {
    const lastRefresh = localStorage.getItem('dashboardLastRefresh');
    const now = Date.now();

    if (!lastRefresh || (now - lastRefresh) > 180000) { // 3 minutes
        refreshDashboard();
        localStorage.setItem('dashboardLastRefresh', now);
    }
}, 180000);
</script>
@endpush
