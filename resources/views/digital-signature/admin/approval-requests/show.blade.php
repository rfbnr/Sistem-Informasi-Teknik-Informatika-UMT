{{-- resources/views/digital-signature/admin/approval-requests/show.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Approval Request Details')

@section('sidebar')
    @include('digital-signature.admin.partials.sidebar')
@endsection

@section('content')
<div class="main-content">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.signature.dashboard') }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.signature.approval.index') }}">
                    <i class="fas fa-clipboard-check"></i> Approval Requests
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $approvalRequest->document_name }}
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-file-contract me-3"></i>
                    {{ $approvalRequest->document_name }}
                </h1>
                <p class="mb-0 opacity-75">
                    Submitted {{ $approvalRequest->created_at->format('d M Y') }}
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <span class="status-badge status-{{ str_replace('_', '-', strtolower($approvalRequest->status)) }} fs-5">
                    {{ str_replace('_', ' ', ucfirst($approvalRequest->status)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- REJECTION ALERT - Approval Request Rejected -->
    @if($approvalRequest->status === 'rejected')
        <div class="alert alert-danger border-danger">
            <div class="d-flex align-items-start">
                <i class="fas fa-times-circle fa-3x me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Approval Request Rejected
                    </h5>
                    <p class="mb-2"><strong>Rejection Reason:</strong></p>
                    <div class="alert alert-light border mb-2">
                        {{ $approvalRequest->rejection_reason ?? 'No reason provided' }}
                    </div>
                    @if($approvalRequest->rejected_at)
                        <p class="mb-1 small">
                            <i class="fas fa-clock me-1"></i>
                            <strong>Rejected On:</strong> {{ $approvalRequest->rejected_at->format('d F Y, H:i') }}
                            ({{ $approvalRequest->rejected_at->diffForHumans() }})
                        </p>
                    @endif
                    @if($approvalRequest->rejectedBy)
                        <p class="mb-0 small">
                            <i class="fas fa-user me-1"></i>
                            <strong>Rejected By:</strong> {{ $approvalRequest->rejectedBy->name }} ({{ $approvalRequest->rejectedBy->email }})
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- REJECTION ALERT - Document Signature Rejected -->
    @if($approvalRequest->documentSignature && $approvalRequest->documentSignature->signature_status === 'rejected')
        <div class="alert alert-danger border-danger">
            <div class="d-flex align-items-start">
                <i class="fas fa-ban fa-3x me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-signature me-2"></i>
                        Document Signature Rejected
                    </h5>
                    <p class="mb-2">
                        The signed document has been rejected due to signature quality or placement issues.
                    </p>
                    <p class="mb-2"><strong>Rejection Reason:</strong></p>
                    <div class="alert alert-light border mb-2">
                        {{ $approvalRequest->documentSignature->rejection_reason ?? 'No reason provided' }}
                    </div>
                    @if($approvalRequest->documentSignature->rejected_at)
                        <p class="mb-1 small">
                            <i class="fas fa-clock me-1"></i>
                            <strong>Rejected On:</strong> {{ $approvalRequest->documentSignature->rejected_at->format('d F Y, H:i') }}
                            ({{ $approvalRequest->documentSignature->rejected_at->diffForHumans() }})
                        </p>
                    @endif
                    @if($approvalRequest->documentSignature->rejector)
                        <p class="mb-2 small">
                            <i class="fas fa-user me-1"></i>
                            <strong>Rejected By:</strong> {{ $approvalRequest->documentSignature->rejector->name }} ({{ $approvalRequest->documentSignature->rejector->email }})
                        </p>
                    @endif
                    <hr class="my-2">
                    <p class="mb-0 small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        The user needs to re-sign the document with corrections. The approval request status has been updated to "rejected".
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Left Column: Document Details & Preview -->
        <div class="col-lg-8">
            <!-- Document Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Document Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- <div class="col-md-6">
                            <label class="text-muted small">Document Number</label>
                            <p class="mb-0 fw-bold">{{ $approvalRequest->nomor ?? 'Not assigned yet' }}</p>
                        </div> --}}
                        <div class="col-md-6">
                            <label class="text-muted small">Document Type</label>
                            <p class="mb-0 fw-bold">{{ $approvalRequest->document_type }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Submitted By</label>
                            <p class="mb-0 fw-bold">
                                {{ $approvalRequest->user->name ?? 'Unknown' }}
                                @if($approvalRequest->user && $approvalRequest->user->NIM)
                                    <br><small class="text-muted">NIM: {{ $approvalRequest->user->NIM }}</small>
                                @endif
                            </p>
                        </div>
                        {{-- <div class="col-md-6">
                            <label class="text-muted small">Department</label>
                            <p class="mb-0">{{ $approvalRequest->department ?? 'Teknik Informatika' }}</p>
                        </div> --}}
                        <div class="col-md-6">
                            <label class="text-muted small">Submission Date</label>
                            <p class="mb-0">
                                {{ $approvalRequest->created_at->format('d M Y, H:i') }}
                                <br><small class="text-muted">{{ $approvalRequest->created_at->diffForHumans() }}</small>
                            </p>
                        </div>
                        {{-- <div class="col-md-6">
                            <label class="text-muted small">Priority</label>
                            <p class="mb-0">
                                @php
                                    $priorityColors = [
                                        'low' => 'secondary',
                                        'normal' => 'primary',
                                        'high' => 'warning',
                                        'urgent' => 'danger'
                                    ];
                                    $priorityColor = $priorityColors[$approvalRequest->priority] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $priorityColor }}">
                                    {{ ucfirst($approvalRequest->priority ?? 'Normal') }}
                                </span>
                            </p>
                        </div> --}}
                        {{-- @if($approvalRequest->deadline)
                            <div class="col-md-12">
                                <label class="text-muted small">Deadline</label>
                                <p class="mb-0">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ \Carbon\Carbon::parse($approvalRequest->deadline)->format('d M Y') }}
                                    @if(\Carbon\Carbon::parse($approvalRequest->deadline)->isPast())
                                        <span class="badge bg-danger ms-2">Overdue</span>
                                    @endif
                                </p>
                            </div>
                        @endif --}}
                        @if($approvalRequest->notes)
                            <div class="col-12">
                                <label class="text-muted small">Notes from Student</label>
                                <div class="alert alert-info border-0 mb-0 fade-in alert-dismissible">
                                    <i class="fas fa-comment-dots me-2"></i>
                                    {{ $approvalRequest->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Document Preview Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf me-2"></i>
                        Document Preview
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.signature.approval.download', $approvalRequest->id) }}"
                           class="btn btn-light btn-sm">
                            <i class="fas fa-download me-1"></i> Download Original
                        </a>
                        @if($approvalRequest->documentSignature && $approvalRequest->documentSignature->final_pdf_path)
                            <a href="{{ route('admin.signature.documents.download', $approvalRequest->documentSignature->id) }}"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-download me-1"></i> Download Signed
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div style="height: 600px; position: relative;">
                        <iframe src="{{ asset('storage/' . $approvalRequest->document_path) }}"
                                style="width:100%; height:100%; border:none;"
                                frameborder="0">
                        </iframe>
                    </div>
                </div>
            </div>

            <!-- Signature Verification (if signed) -->
            @if($approvalRequest->documentSignature && $verificationResult)
                <div class="card mb-4 border-{{ $verificationResult['is_valid'] ? 'success' : 'danger' }}">
                    <div class="card-header bg-{{ $verificationResult['is_valid'] ? 'success' : 'danger' }} text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Signature Verification
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($verificationResult['is_valid'])
                            <div class="alert alert-success border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle fa-3x me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-1">Signature is Valid</h5>
                                        <p class="mb-0">{{ $verificationResult['message'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-danger border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle fa-3x me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-1">Signature Verification Failed</h5>
                                        <p class="mb-0">{{ $verificationResult['message'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="text-muted small">Algorithm</label>
                                <p class="mb-0">{{ $approvalRequest->documentSignature->digitalSignature->algorithm ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Key Length</label>
                                <p class="mb-0">{{ $approvalRequest->documentSignature->digitalSignature->key_length ?? 'N/A' }} bits</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Signed At</label>
                                <p class="mb-0">{{ $approvalRequest->documentSignature->signed_at?->format('d M Y, H:i') ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Signed By</label>
                                <p class="mb-0">{{ $approvalRequest->documentSignature->signer->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Timeline & Actions -->
        <div class="col-lg-4">
            <!-- Action Buttons Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($approvalRequest->status === 'pending')
                        <button class="btn btn-success w-100 mb-2"
                                onclick="showApproveModal({{ $approvalRequest->id }}, '{{ $approvalRequest->document_name }}', '{{ $approvalRequest->document_type }}')">
                            <i class="fas fa-check me-2"></i> Approve Request
                        </button>
                        <button class="btn btn-danger w-100 mb-2"
                                onclick="showRejectModal({{ $approvalRequest->id }}, '{{ $approvalRequest->document_name }}')">
                            <i class="fas fa-times me-2"></i> Reject Request
                        </button>
                    @elseif($approvalRequest->status === 'user_signed')
                        <button class="btn btn-primary w-100 mb-2"
                                onclick="showApproveSignatureModal({{ $approvalRequest->id }}, '{{ $approvalRequest->document_name }}')">
                            <i class="fas fa-stamp me-2"></i> Approve Signature
                        </button>
                    @endif

                    <a href="{{ route('admin.signature.approval.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>

            <!-- Workflow Timeline Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Workflow History
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="timeline">
                        @foreach($timeline as $index => $event)
                            <div class="timeline-item {{ $event['status'] }}">
                                <div class="timeline-marker bg-{{ $event['color'] }}">
                                    <i class="fas {{ $event['icon'] }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ $event['title'] }}</h6>
                                    <p class="mb-1 small">{{ $event['description'] }}</p>
                                    @if($event['user'])
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i> {{ $event['user'] }}
                                        </small>
                                    @endif
                                    @if($event['timestamp'])
                                        <br><small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $event['timestamp']->format('d M Y, H:i') }}
                                            <span class="opacity-75">({{ $event['timestamp']->diffForHumans() }})</span>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Document Metadata Card -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-database me-2"></i>
                        Document Metadata
                    </h6>
                </div>
                <div class="card-body">
                    @if($approvalRequest->workflow_metadata)
                        <small class="text-muted">
                            @if(isset($approvalRequest->workflow_metadata['original_filename']))
                                <strong>Original File:</strong><br>
                                {{ $approvalRequest->workflow_metadata['original_filename'] }}<br><br>
                            @endif
                            @if(isset($approvalRequest->workflow_metadata['file_size']))
                                <strong>File Size:</strong><br>
                                {{ number_format($approvalRequest->workflow_metadata['file_size'] / 1024, 2) }} KB<br><br>
                            @endif
                            @if(isset($approvalRequest->workflow_metadata['document_hash']))
                                <strong>Document Hash:</strong><br>
                                <code class="small">{{ Str::limit($approvalRequest->workflow_metadata['document_hash'], 30) }}</code><br><br>
                            @endif
                            @if(isset($approvalRequest->workflow_metadata['upload_timestamp']))
                                <strong>Upload Time:</strong><br>
                                {{ date('Y-m-d H:i:s', $approvalRequest->workflow_metadata['upload_timestamp']) }}
                            @endif
                        </small>
                    @else
                        <small class="text-muted">No metadata available</small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('digital-signature.admin.approval-requests.partials.approve-modal')
@include('digital-signature.admin.approval-requests.partials.reject-modal')
@include('digital-signature.admin.approval-requests.partials.approve-signature-modal')

@endsection

@push('styles')
<style>
/* Timeline Styles */
.timeline {
    position: relative;
    padding: 1.5rem 0 1.5rem 3rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 1.5rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -2.25rem;
    top: 0;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 1;
}

.timeline-item.pending .timeline-marker {
    opacity: 0.5;
    animation: pulse 2s ease-in-out infinite;
}

.timeline-content {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.timeline-content h6 {
    color: #333;
    font-weight: 600;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.5;
    }
    50% {
        opacity: 0.8;
    }
}

/* Breadcrumb Styles */
.breadcrumb {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.breadcrumb-item a {
    color: #667eea;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #764ba2;
    text-decoration: underline;
}

/* Document Preview Frame */
iframe {
    border-radius: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .timeline {
        padding-left: 2.5rem;
    }

    .timeline::before {
        left: 1rem;
    }

    .timeline-marker {
        left: -1.5rem;
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Import modal functions from index page
function showApproveModal(id, documentName, documentType) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    document.getElementById('approveRequestId').value = id;
    document.getElementById('approveDocumentName').textContent = documentName;
    document.getElementById('approveDocumentType').textContent = documentType;
    modal.show();
}

function showRejectModal(id, documentName) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    document.getElementById('rejectRequestId').value = id;
    document.getElementById('rejectDocumentName').textContent = documentName;
    document.getElementById('rejection_reason').value = '';
    modal.show();
}

function showApproveSignatureModal(id, documentName) {
    const modal = new bootstrap.Modal(document.getElementById('approveSignatureModal'));
    document.getElementById('approveSignatureRequestId').value = id;
    document.getElementById('approveSignatureDocumentName').textContent = documentName;
    modal.show();
}

function performApproveSignature() {
    const requestId = document.getElementById('approveSignatureRequestId').value;
    const notes = document.getElementById('approve_signature_notes').value;

    fetch(`/admin/signature/approval-requests/${requestId}/approve-signature`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ approval_notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || !data.error) {
            alert('Signature approved successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to approve signature');
        }
        bootstrap.Modal.getInstance(document.getElementById('approveSignatureModal')).hide();
    })
    .catch(error => {
        alert('An error occurred while approving the signature');
        console.error('Error:', error);
    });
}

// Show Alert Helper
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

// Update selected count when checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.request-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
});
</script>
@endpush
