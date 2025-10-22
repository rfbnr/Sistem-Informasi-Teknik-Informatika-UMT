{{-- @extends('layouts.app')

@section('title', 'Digital Signature Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Digital Signature Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        <li class="breadcrumb-item active">Digital Signature</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Signatures</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_signatures']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Signatures</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_signatures']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Documents Signed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_documents_signed']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Verification</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['pending_signatures']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Signatures -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Digital Signatures</h6>
                    <a href="{{ route('admin.signature.key-management') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-cog"></i> Manage Keys
                    </a>
                </div>
                <div class="card-body">
                    @if($recentSignatures->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" id="recentSignaturesTable">
                                <thead>
                                    <tr>
                                        <th>Signature ID</th>
                                        <th>Algorithm</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Valid Until</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSignatures as $signature)
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold">{{ $signature->signature_id }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $signature->algorithm }}</span>
                                        </td>
                                        <td>
                                            @if($signature->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @elseif($signature->status === 'expired')
                                                <span class="badge badge-warning">Expired</span>
                                            @else
                                                <span class="badge badge-danger">Revoked</span>
                                            @endif
                                        </td>
                                        <td>{{ $signature->creator->name ?? 'System' }}</td>
                                        <td>
                                            <span class="@if($signature->isExpiringSoon()) text-warning @endif">
                                                {{ $signature->valid_until->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.signature.view', $signature->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-certificate fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No digital signatures found</p>
                            <a href="{{ route('admin.signature.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Signature
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alerts & Notifications -->
        <div class="col-lg-4 mb-4">
            <!-- Expiring Signatures Alert -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Expiring Soon
                    </h6>
                </div>
                <div class="card-body">
                    @if($expiringSignatures->count() > 0)
                        @foreach($expiringSignatures as $signature)
                        <div class="d-flex align-items-center py-2 border-bottom">
                            <div class="mr-3">
                                <i class="fas fa-certificate text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small font-weight-bold">{{ $signature->signature_id }}</div>
                                <div class="small text-muted">
                                    Expires: {{ $signature->valid_until->format('d M Y') }}
                                    ({{ $signature->valid_until->diffForHumans() }})
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.signature.key-management', ['filter' => 'expiring']) }}"
                               class="btn btn-warning btn-sm">
                                View All Expiring
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="mb-0">No signatures expiring soon</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.signature.create') }}" class="btn btn-success btn-block">
                            <i class="fas fa-plus"></i> Create New Signature Key
                        </a>
                        <a href="{{ route('admin.signature.verification-tools') }}" class="btn btn-info btn-block">
                            <i class="fas fa-search"></i> Verification Tools
                        </a>
                        <a href="{{ route('admin.signature.templates') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-palette"></i> Manage Templates
                        </a>
                        <a href="{{ route('admin.signature.export') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-download"></i> Export Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Verification Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="h4 font-weight-bold text-primary">
                                {{ $verificationStats['total_signatures'] }}
                            </div>
                            <div class="text-muted">Total Signatures</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h4 font-weight-bold text-success">
                                {{ $verificationStats['verified_signatures'] }}
                            </div>
                            <div class="text-muted">Verified</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h4 font-weight-bold text-info">
                                {{ $verificationStats['verification_rate'] }}%
                            </div>
                            <div class="text-muted">Verification Rate</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h4 font-weight-bold text-warning">
                                {{ $verificationStats['period_days'] }}
                            </div>
                            <div class="text-muted">Days Period</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.card {
    border: 0;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    color: #5a5c69;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#recentSignaturesTable').DataTable({
        pageLength: 10,
        order: [[4, 'desc']], // Order by created date
        columnDefs: [
            { orderable: false, targets: [5] } // Disable ordering for Actions column
        ]
    });

    // Auto-refresh statistics every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush --}}

{{-- resources/views/digital-signature/admin/dashboard.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Digital Signature Dashboard')

@section('sidebar')
    @include('digital-signature.admin.partials.sidebar')
@endsection

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
                    {{-- <a href="{{ route('admin.signature.create') }}" class="btn btn-warning">
                        <i class="fas fa-plus me-1"></i> New Signature
                    </a> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-number text-primary">{{ $stats['total_signatures'] ?? 0 }}</div>
                <div class="text-muted">Total Signatures</div>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i>
                        {{ $stats['active_signatures'] ?? 0 }} Active
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-number text-info">{{ $stats['total_documents_signed'] ?? 0 }}</div>
                <div class="text-muted">Documents Signed</div>
                <div class="mt-2">
                    <small class="text-warning">
                        <i class="fas fa-clock"></i>
                        {{ $stats['pending_signatures'] ?? 0 }} Pending
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $stats['verified_signatures'] ?? 0 }}</div>
                <div class="text-muted">Verified Signatures</div>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="fas fa-shield-alt"></i>
                        {{ $verificationStats['verification_rate'] ?? 0 }}% Rate
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-number text-warning">{{ $stats['expired_signatures'] ?? 0 }}</div>
                <div class="text-muted">Expired Signatures</div>
                <div class="mt-2">
                    <small class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Need Renewal
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Signatures -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Recent Digital Signatures
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentSignatures && $recentSignatures->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Signature ID</th>
                                        <th>Algorithm</th>
                                        <th>Created By</th>
                                        <th>Status</th>
                                        <th>Valid Until</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSignatures as $signature)
                                    <tr>
                                        <td>
                                            <code>{{ Str::limit($signature->signature_id, 20) }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $signature->algorithm }}</span>
                                        </td>
                                        <td>{{ $signature->creator->name ?? 'System' }}</td>
                                        <td>
                                            <span class="status-badge status-{{ strtolower($signature->status) }}">
                                                {{ ucfirst($signature->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $signature->valid_until->format('d M Y') }}
                                                @if($signature->valid_until->isPast())
                                                    <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            {{-- Test --}}
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.signature.keys.index', $signature->id) }}"
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($signature->status === 'active')
                                                    <button class="btn btn-outline-warning"
                                                            onclick="revokeSignature({{ $signature->id }})">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            {{-- <a href="{{ route('admin.signature.key-management') }}" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Manage All Signatures
                            </a> --}}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Digital Signatures</h5>
                            <p class="text-muted">Create your first digital signature to get started</p>
                            {{-- <a href="{{ route('admin.signature.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Create Signature
                            </a> --}}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Alerts -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        {{-- <a href="{{ route('admin.signature.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i> Create New Signature
                        </a>
                        <a href="{{ route('admin.signature.documents.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-file-signature me-2"></i> View Documents
                        </a>
                        <a href="{{ route('admin.signature.verification-tools') }}" class="btn btn-outline-warning">
                            <i class="fas fa-shield-alt me-2"></i> Verification Tools
                        </a>
                        <a href="{{ route('admin.signature.templates.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-template me-2"></i> Manage Templates
                        </a> --}}
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
    </div>

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

<!-- Revoke Signature Modal -->
<div class="modal fade" id="revokeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Revoke Digital Signature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="revokeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Revoking this signature will invalidate all documents signed with it.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for revocation:</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required
                                  placeholder="Enter the reason for revoking this signature..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Revoke Signature
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshDashboard() {
    location.reload();
}

function revokeSignature(signatureId) {
    const modal = new bootstrap.Modal(document.getElementById('revokeModal'));
    const form = document.getElementById('revokeForm');
    form.action = `/admin/signature/keys/${signatureId}/revoke`;
    modal.show();
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    const lastRefresh = localStorage.getItem('dashboardLastRefresh');
    const now = Date.now();

    if (!lastRefresh || (now - lastRefresh) > 300000) { // 5 minutes
        refreshDashboard();
        localStorage.setItem('dashboardLastRefresh', now);
    }
}, 300000);
</script>
@endpush
