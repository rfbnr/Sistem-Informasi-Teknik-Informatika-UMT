{{-- resources/views/digital-signature/admin/document-signatures.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Document Signatures Management')

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
                    <i class="fas fa-file-signature me-3"></i>
                    Document Signatures Management
                </h1>
                <p class="mb-0 opacity-75">Monitor and verify digitally signed documents</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="btn-group">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#batchVerifyModal">
                        <i class="fas fa-check-double me-1"></i> Batch Verify
                    </button>
                    <a href="{{ route('admin.signature.documents.export') }}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i> Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2-4">
            <div class="stats-card">
                <div class="stats-number text-warning">{{ $statusCounts['pending'] }}</div>
                <div class="text-muted">Pending</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card">
                <div class="stats-number text-info">{{ $statusCounts['signed'] }}</div>
                <div class="text-muted">Signed</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $statusCounts['verified'] }}</div>
                <div class="text-muted">Verified</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card">
                <div class="stats-number text-danger">{{ $statusCounts['rejected'] }}</div>
                <div class="text-muted">Rejected</div>
            </div>
        </div>
        <div class="col-md-2-4">
            <div class="stats-card">
                <div class="stats-number text-secondary">{{ $statusCounts['invalid'] }}</div>
                <div class="text-muted">Invalid</div>
            </div>
        </div>
    </div>

    <style>
        .col-md-2-4 {
            flex: 0 0 20%;
            max-width: 20%;
        }
        @media (max-width: 768px) {
            .col-md-2-4 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.signature.documents.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search documents..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="signed" {{ request('status') == 'signed' ? 'selected' : '' }}>Signed</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="invalid" {{ request('status') == 'invalid' ? 'selected' : '' }}>Invalid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_from"
                           placeholder="From Date" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="date_to"
                           placeholder="To Date" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Document Signatures Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Signed Documents ({{ $documentSignatures->total() }})
            </h5>
        </div>
        <div class="card-body">
            @if($documentSignatures->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                                </th>
                                <th>Document</th>
                                <th>Signed By</th>
                                <th>Algorithm</th>
                                <th>Signed At</th>
                                <th>Status</th>
                                <th>PDF Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentSignatures as $docSig)
                            <tr>
                                <td>
                                    <input type="checkbox" class="signature-checkbox" value="{{ $docSig->id }}">
                                </td>
                                <td>
                                    <strong>{{ $docSig->approvalRequest->document_name }}</strong><br>
                                    <small class="text-muted">{{ $docSig->approvalRequest->full_document_number }}</small>
                                </td>
                                <td>{{ $docSig->signer->name ?? 'Unknown' }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $docSig->digitalSignature->algorithm ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($docSig->signed_at)
                                        {{ $docSig->signed_at->format('d M Y H:i') }}
                                        <br><small class="text-muted">{{ $docSig->signed_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Not signed</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($docSig->signature_status) }}">
                                        {{ ucfirst($docSig->signature_status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($docSig->final_pdf_path)
                                        <span class="badge bg-success" title="Signed PDF available">
                                            <i class="fas fa-file-pdf me-1"></i> Signed PDF
                                        </span>
                                    @else
                                        <span class="badge bg-secondary" title="Original document only">
                                            <i class="fas fa-file me-1"></i> Original
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($docSig->signature_status !== 'pending')
                                            <button class="btn btn-outline-warning"
                                                    onclick="quickPreviewDocument({{ $docSig->id }})"
                                                    title="Quick Preview & Verify">
                                                <i class="fas fa-bolt"></i>
                                            </button>
                                            <a href="{{ route('admin.signature.documents.show', $docSig->id) }}"
                                            class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                        @if($docSig->signature_status === 'signed')
                                            <button class="btn btn-outline-success"
                                                    onclick="verifySignature({{ $docSig->id }})"
                                                    title="Verify Signature">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger"
                                                    onclick="rejectSignature({{ $docSig->id }})"
                                                    title="Reject Signature">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.signature.documents.download', $docSig->id) }}"
                                           class="btn btn-outline-info" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if(in_array($docSig->signature_status, ['verified']))
                                            <button class="btn btn-outline-secondary"
                                                    onclick="invalidateSignature({{ $docSig->id }})"
                                                    title="Invalidate">
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

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $documentSignatures->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Document Signatures Found</h5>
                    <p class="text-muted">Document signatures will appear here once created</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Batch Verify Modal -->
<div class="modal fade" id="batchVerifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batch Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select signatures from the list below and click verify to perform batch verification.</p>
                <div id="selectedSignatures"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="performBatchVerify()">
                    <i class="fas fa-check-double me-1"></i> Verify Selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Signature Modal -->
{{-- <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Document Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Signature Rejection</strong><br>
                        Rejecting this signature will also reject the approval request. The user will need to re-sign the document with correct placement.
                    </div>
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason *</label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="4" required
                                  placeholder="Example: Signature placement is incorrect - too far to the left"></textarea>
                        <small class="text-muted">Please specify the issue (placement, size, quality, etc.)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Common Rejection Reasons:</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature placement is incorrect - positioned too far to the left')">
                                Placement too far left
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature placement is incorrect - positioned too far to the right')">
                                Placement too far right
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature size is too large and overlaps with document content')">
                                Signature too large
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature quality is poor - image appears distorted or pixelated')">
                                Poor signature quality
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature does not match the designated signature area')">
                                Not in designated area
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject Signature
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}

<!-- Invalidate Modal -->
<div class="modal fade" id="invalidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Invalidate Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="invalidateForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action will mark the signature as invalid and cannot be undone.
                    </div>
                    <div class="mb-3">
                        <label for="invalidate_reason" class="form-label">Reason *</label>
                        <textarea class="form-control" id="invalidate_reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Invalidate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('digital-signature.admin.partials.quick-preview-signed-modal')
@include('digital-signature.admin.partials.reject-signed-modal')

<!-- Quick Preview Modal -->
{{-- <div class="modal fade" id="quickPreviewModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Preview & Verification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickPreviewContent">
                <!-- Loading State -->
                <div class="text-center py-5" id="quickPreviewLoading">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                    <p class="text-muted">Loading document and running verification checks...</p>
                </div>

                <!-- Content will be loaded here -->
                <div id="quickPreviewData" style="display: none;">
                    <!-- Verification Status Alert -->
                    <div id="verificationAlert"></div>

                    <div class="row">
                        <!-- Left Column: Document Info & PDF Preview -->
                        <div class="col-lg-8">
                            <!-- Document Information Card -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Document Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="documentInfo">
                                        <!-- Will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <!-- PDF Preview -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-file-pdf me-2"></i>Document Preview</h6>
                                </div>
                                <div class="card-body p-0">
                                    <iframe id="pdfPreviewFrame" style="width: 100%; height: 600px; border: none;"></iframe>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Verification Checks & Actions -->
                        <div class="col-lg-4">
                            <!-- Verification Summary Card -->
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Verification Summary</h6>
                                </div>
                                <div class="card-body" id="verificationSummary">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                            </div>

                            <!-- Verification Checks List -->
                            <div class="card mb-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-list-check me-2"></i>Security Checks (7)</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush" id="verificationChecks">
                                        <!-- Will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <!-- Signature Info Card -->
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-signature me-2"></i>Signature Details</h6>
                                </div>
                                <div class="card-body small" id="signatureInfo">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2" id="quickActions">
                                        <!-- Will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewDetailBtn" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i> View Full Details
                </a>
            </div>
        </div>
    </div>
</div> --}}
@endsection

@push('scripts')
<script>
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.signature-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.signature-checkbox:checked');
    const container = document.getElementById('selectedSignatures');
    container.innerHTML = `<strong>${selected.length}</strong> signature(s) selected`;
}

function verifySignature(id) {
    if (confirm('Verify this signature?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(`/admin/signature/documents/${id}/verify`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ Signature verified successfully!');
                location.reload();
            } else {
                alert('❌ Verification failed: ' + (data.message || 'Unknown error'));
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            console.error('Verification error:', error);
            alert('❌ Network error: Failed to verify signature. Please check your connection and try again.');
            button.disabled = false;
            button.innerHTML = originalHtml;
        });
    }
}

function invalidateSignature(id) {
    const modal = document.getElementById('invalidateModal');
    const form = document.getElementById('invalidateForm');

    // Set form action dynamically
    form.action = `/admin/signature/documents/${id}/invalidate`;

    // Clear previous input
    document.getElementById('invalidate_reason').value = '';

    // Show modal
    new bootstrap.Modal(modal).show();

    // Handle form submission
    form.onsubmit = function(e) {
        e.preventDefault();

        const reason = document.getElementById('invalidate_reason').value;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ Signature invalidated successfully!');
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert('❌ Invalidation failed: ' + (data.message || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        })
        .catch(error => {
            console.error('Invalidation error:', error);
            alert('❌ Network error: Failed to invalidate signature. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        });
    };
}

function performBatchVerify() {
    const selected = Array.from(document.querySelectorAll('.signature-checkbox:checked'))
        .map(cb => cb.value);

    if (selected.length === 0) {
        alert('⚠️ Please select at least one signature');
        return;
    }

    if (!confirm(`Verify ${selected.length} signature(s)?`)) {
        return;
    }

    // Show loading modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('batchVerifyModal'));
    const verifyBtn = document.querySelector('#batchVerifyModal button.btn-success');
    const originalBtnHtml = verifyBtn.innerHTML;
    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verifying...';

    fetch('/admin/signature/documents/batch-verify', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ signature_ids: selected })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success !== undefined && !data.success) {
            alert('❌ Batch verification failed: ' + (data.message || 'Unknown error'));
        } else {
            alert('✅ ' + (data.message || 'Batch verification completed!'));
            if (modal) modal.hide();
            location.reload();
        }
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = originalBtnHtml;
    })
    .catch(error => {
        console.error('Batch verify error:', error);
        alert('❌ Network error: Failed to perform batch verification. Please try again.');
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = originalBtnHtml;
    });
}

// Update selected count when checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.signature-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
});


</script>
@endpush



{{--

<!-- Reject Signature Modal Script -->
function rejectSignature(id) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');

    // Set form action dynamically
    form.action = `/admin/signature/documents/${id}/reject`;

    // Clear previous input
    document.getElementById('reject_reason').value = '';

    // Show modal
    new bootstrap.Modal(modal).show();

    // Handle form submission
    form.onsubmit = function(e) {
        e.preventDefault();

        const reason = document.getElementById('reject_reason').value;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;

        console.log('Submitting rejection with reason:', reason);
        console.log('Form action URL:', form.action);
        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);
        console.log('Request Body:', JSON.stringify({ reason: reason }));

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Rejecting...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert('❌ Rejection failed: ' + (data.message || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        })
        .catch(error => {
            console.error('Rejection error:', error);
            alert('❌ Network error: Failed to reject signature. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        });
    };
}

function setRejectReason(reason) {
    document.getElementById('reject_reason').value = reason;
}

// Quick Preview Document Function
function quickPreviewDocument(id) {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('quickPreviewModal'));
    modal.show();

    // Show loading, hide content
    document.getElementById('quickPreviewLoading').style.display = 'block';
    document.getElementById('quickPreviewData').style.display = 'none';

    // Fetch document data with verification
    fetch(`/admin/signature/documents/${id}/quick-preview`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(response => {
        if (!response.success) {
            throw new Error(response.message || 'Failed to load preview');
        }

        const data = response.data;

        console.log('Quick Preview Data:', data);

        // Hide loading, show content
        document.getElementById('quickPreviewLoading').style.display = 'none';
        document.getElementById('quickPreviewData').style.display = 'block';

        // Populate Verification Alert
        const alertDiv = document.getElementById('verificationAlert');
        if (data.verification.is_valid) {
            alertDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>✓ Signature Valid:</strong> ${data.verification.message}
                </div>
            `;
        } else {
            alertDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>✗ Verification Failed:</strong> ${data.verification.message}
                </div>
            `;
        }

        // Populate Document Info
        const docInfo = document.getElementById('documentInfo');
        docInfo.innerHTML = `
            <div class="col-md-6 mb-2">
                <strong>Document Name:</strong><br>
                <span class="text-muted">${data.document.name}</span>
            </div>
            <div class="col-md-6 mb-2">
                <strong>Document Number:</strong><br>
                <span class="text-muted">${data.document.number}</span>
            </div>
            <div class="col-md-6 mb-2">
                <strong>Submitted By:</strong><br>
                <span class="text-muted">${data.document.submitted_by}</span>
            </div>
            <div class="col-md-6 mb-2">
                <strong>Submitted At:</strong><br>
                <span class="text-muted">${data.document.submitted_at}</span>
            </div>
            ${data.document.notes ? `
                <div class="col-12 mb-2">
                    <strong>Notes:</strong><br>
                    <span class="text-muted">${data.document.notes}</span>
                </div>
            ` : ''}
        `;

        // Load PDF Preview
        document.getElementById('pdfPreviewFrame').src = data.urls.view;

        // Populate Verification Summary
        const summary = data.verification.summary;
        const summaryDiv = document.getElementById('verificationSummary');
        const successRate = summary.success_rate || 0;
        const statusClass = data.verification.is_valid ? 'success' : 'danger';

        summaryDiv.innerHTML = `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">
                        <span class="badge bg-${statusClass}">
                            ${summary.overall_status || 'UNKNOWN'}
                        </span>
                    </h5>
                    <span class="text-muted">${successRate}% passed</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-${statusClass}" style="width: ${successRate}%" role="progressbar"></div>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-6">
                    <div class="h4 text-success mb-0">${summary.checks_passed || 0}</div>
                    <small class="text-muted">Passed</small>
                </div>
                <div class="col-6">
                    <div class="h4 text-danger mb-0">${summary.checks_failed || 0}</div>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        `;

        // Populate Verification Checks
        const checksDiv = document.getElementById('verificationChecks');
        let checksHtml = '';

        for (const [checkName, check] of Object.entries(data.verification.checks)) {
            const iconClass = check.status ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
            const checkTitle = checkName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            checksHtml += `
                <div class="list-group-item">
                    <div class="d-flex align-items-start">
                        <div class="me-3 mt-1">
                            <i class="fas ${iconClass}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">${checkTitle}</div>
                            <small class="text-muted">${check.message}</small>
                        </div>
                    </div>
                </div>
            `;
        }
        checksDiv.innerHTML = checksHtml;

        // Populate Signature Info
        const sigInfo = document.getElementById('signatureInfo');
        sigInfo.innerHTML = `
            <div class="mb-2">
                <strong>Status:</strong>
                <span class="badge bg-${data.signature.status === 'verified' ? 'success' : (data.signature.status === 'signed' ? 'info' : 'warning')} ms-1">
                    ${data.signature.status.toUpperCase()}
                </span>
            </div>
            <div class="mb-2">
                <strong>Signed By:</strong><br>
                ${data.signature.signed_by}
            </div>
            <div class="mb-2">
                <strong>Signed At:</strong><br>
                ${data.signature.signed_at || 'N/A'}
                ${data.signature.signed_at_human ? `<br><small class="text-muted">${data.signature.signed_at_human}</small>` : ''}
            </div>
            <div class="mb-2">
                <strong>Algorithm:</strong>
                <span class="badge bg-info ms-1">${data.signature.algorithm}</span>
            </div>
            <div class="mb-2">
                <strong>Key Length:</strong>
                <span class="badge bg-success ms-1">${data.signature.key_length} bits</span>
            </div>
            <div class="mb-2">
                <strong>Hash:</strong><br>
                <code class="small">${data.signature.document_hash}</code>
            </div>
            <div class="mb-0">
                <strong>PDF Status:</strong>
                ${data.signature.has_signed_pdf ?
                    '<span class="badge bg-success ms-1"><i class="fas fa-check-circle me-1"></i>Signed PDF</span>' :
                    '<span class="badge bg-secondary ms-1"><i class="fas fa-file me-1"></i>Original</span>'
                }
            </div>
        `;

        // Populate Quick Actions
        const actionsDiv = document.getElementById('quickActions');
        let actionsHtml = '';

        // Verify & Reject buttons (only for 'signed' status)
        if (data.signature.status === 'signed') {
            // Verify button (only if valid)
            if (data.verification.is_valid) {
                actionsHtml += `
                    <button class="btn btn-success" onclick="quickVerifyFromModal(${data.document.id}, '${data.urls.verify}')">
                        <i class="fas fa-check-circle me-2"></i> Verify Signature Now
                    </button>
                `;
            }

            // Reject button (always show for signed status)
            actionsHtml += `
                <button class="btn btn-danger" onclick="quickRejectFromModal(${data.document.id})">
                    <i class="fas fa-times me-2"></i> Reject Signature
                </button>
            `;
        }

        // Download button
        actionsHtml += `
            <a href="${data.urls.download}" class="btn btn-info">
                <i class="fas fa-download me-2"></i> Download Document
            </a>
        `;

        // View detail button (already in footer, but can add here too)
        actionsHtml += `
            <a href="${data.urls.detail}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i> Open Full Detail Page
            </a>
        `;

        actionsDiv.innerHTML = actionsHtml;

        // Update footer detail link
        document.getElementById('viewDetailBtn').href = data.urls.detail;

    })
    .catch(error => {
        console.error('Quick preview error:', error);
        document.getElementById('quickPreviewLoading').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

// Quick Verify from Modal
function quickVerifyFromModal(id, verifyUrl) {
    if (!confirm('Verify this signature now?')) {
        return;
    }

    // Get button
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verifying...';

    fetch(verifyUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ Signature verified successfully!');
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('quickPreviewModal')).hide();
            location.reload();
        } else {
            alert('❌ Verification failed: ' + (data.message || 'Unknown error'));
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Verification error:', error);
        alert('❌ Network error: Failed to verify signature. Please try again.');
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
}

// Quick Reject from Modal
function quickRejectFromModal(id) {
    // Close quick preview modal
    bootstrap.Modal.getInstance(document.getElementById('quickPreviewModal')).hide();

    // Small delay then open reject modal
    setTimeout(() => {
        rejectSignature(id);
    }, 300);
}


--}}
