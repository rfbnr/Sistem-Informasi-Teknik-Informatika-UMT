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
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-warning">{{ $statusCounts['pending'] }}</div>
                <div class="text-muted">Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-info">{{ $statusCounts['signed'] }}</div>
                <div class="text-muted">Signed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $statusCounts['verified'] }}</div>
                <div class="text-muted">Verified</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-danger">{{ $statusCounts['invalid'] }}</div>
                <div class="text-muted">Invalid</div>
            </div>
        </div>
    </div>

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
                                        @endif
                                        <a href="{{ route('admin.signature.documents.download', $docSig->id) }}"
                                           class="btn btn-outline-info" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if(in_array($docSig->signature_status, ['signed', 'verified']))
                                            <button class="btn btn-outline-danger"
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
