{{-- resources/views/digital-signature/user/my-signatures/details.blade.php --}}
@extends('user.layouts.app')

@section('title', 'Signature Details')

@section('content')
<!-- Section Header -->
<section id="header-section">
    <h1>Signature Details</h1>
</section>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-file-signature me-3"></i>
                    Signature Details
                </h1>
                <p class="mb-0 opacity-75">{{ $documentSignature->approvalRequest->document_name }}</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('user.signature.my.signatures.index') }}" class="btn btn-outline-warning">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($documentSignature->signature_status === 'verified')
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Verified!</strong> Your document signature has been verified and is ready for download.
        </div>
    @elseif($documentSignature->signature_status === 'signed')
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-clock me-2"></i>
            <strong>Pending Verification:</strong> Your signature is awaiting final verification by Kaprodi.
        </div>
    {{-- @elseif($documentSignature->signature_status === 'rejected')
        <div class="alert alert-danger alert-dismissible fade show">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-3x me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-times-circle me-1"></i> Signature Rejected
                    </h5>
                    <p class="mb-2">
                        <strong>Rejection Reason:</strong><br>
                        {{ $documentSignature->rejection_reason }}
                    </p>
                    @if($documentSignature->rejected_at)
                    <p class="mb-2 small">
                        <strong>Rejected On:</strong> {{ $documentSignature->rejected_at->format('d F Y, H:i') }}
                        ({{ $documentSignature->rejected_at->diffForHumans() }})
                    </p>
                    @endif
                    @if($documentSignature->rejector)
                    <p class="mb-2 small">
                        <strong>Rejected By:</strong> {{ $documentSignature->rejector->name }} ({{ $documentSignature->rejector->email }})
                    </p>
                    @endif
                    <hr class="my-2">
                    <div class="mb-2">
                        <strong><i class="fas fa-lightbulb me-1"></i> Common Issues:</strong>
                        <ul class="mb-2 mt-1">
                            <li class="small">Signature placement is too far left or right</li>
                            <li class="small">Signature size is too large and overlaps content</li>
                            <li class="small">Signature quality is poor or distorted</li>
                            <li class="small">Signature is not in the designated area</li>
                        </ul>
                    </div>
                    <hr class="my-2">
                    <p class="mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Next Steps:</strong> Please submit a new document request with the necessary corrections.
                        Make sure to carefully place your signature in the correct position and ensure good quality.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('user.signature.approval.request') }}" class="btn btn-danger">
                            <i class="fas fa-redo me-1"></i> Submit New Request
                        </a>
                        <a href="{{ route('user.signature.my.signatures.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> Back to My Signatures
                        </a>
                    </div>
                </div>
            </div>
        </div> --}}
    @elseif($documentSignature->signature_status === 'invalid')
        <div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Invalid Signature:</strong> This signature has been marked as invalid.
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Document Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Document Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Document Name:</strong><br>
                            {{ $documentSignature->approvalRequest->document_name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Document Type:</strong><br>
                            @if($documentSignature->approvalRequest->document_type)
                                <span class="badge bg-secondary">
                                    {{ $documentSignature->approvalRequest->document_type }}
                                </span>
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                        {{-- <div class="col-md-6">
                            <strong>Document Number:</strong><br>
                            <code>{{ $documentSignature->approvalRequest->full_document_number }}</code>
                        </div> --}}
                    </div>

                    {{-- <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Document Type:</strong><br>
                            @if($documentSignature->approvalRequest->document_type)
                                <span class="badge bg-secondary">
                                    {{ $documentSignature->approvalRequest->document_type }}
                                </span>
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Priority:</strong><br>
                            <span class="priority-badge priority-{{ $documentSignature->approvalRequest->priority }}">
                                {{ ucfirst($documentSignature->approvalRequest->priority) }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Department:</strong><br>
                            {{ $documentSignature->approvalRequest->department ?? 'N/A' }}
                        </div>
                    </div> --}}

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submission Date:</strong><br>
                            {{ $documentSignature->approvalRequest->created_at->format('d F Y, H:i') }}
                            <small class="text-muted d-block">{{ $documentSignature->approvalRequest->created_at->diffForHumans() }}</small>
                        </div>
                        {{-- <div class="col-md-6">
                            <strong>Deadline:</strong><br>
                            @if($documentSignature->approvalRequest->deadline)
                                {{ $documentSignature->approvalRequest->deadline->format('d F Y, H:i') }}
                                @if($documentSignature->approvalRequest->isOverdue())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @elseif($documentSignature->approvalRequest->isNearDeadline())
                                    <span class="badge bg-warning ms-2">Near Deadline</span>
                                @endif
                            @else
                                <span class="text-muted">No deadline set</span>
                            @endif
                        </div> --}}
                    </div>

                    <div class="row mb-3">
                        @if($documentSignature->approvalRequest->notes)
                        <div class="col-md-6">
                            <div class="col-12">
                                <strong>Submission Notes:</strong><br>
                                <p class="text-muted mb-0">{{ $documentSignature->approvalRequest->notes }}</p>
                            </div>
                        </div>
                        @endif

                        @if($documentSignature->approvalRequest->approval_notes)
                        <div class="col-md-6">
                            <div class="col-12">
                                <strong>Approval Notes:</strong><br>
                                <div class="mb-0">
                                    {{ $documentSignature->approvalRequest->approval_notes }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($documentSignature->approvalRequest->admin_notes)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Admin Notes:</strong><br>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $documentSignature->approvalRequest->admin_notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Document Files Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Original Document:</strong><br>
                            @if($documentSignature->approvalRequest->document_path)
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small flex-grow-1" style="word-break: break-all;">
                                        {{ basename($documentSignature->approvalRequest->document_path) }}
                                    </code>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="previewDocument('original')"
                                            title="Preview Original Document">
                                        <i class="fas fa-eye"></i> Show
                                    </button>
                                </div>
                            @else
                                <span class="text-muted">Not available</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Signed Document:</strong><br>
                            @if($documentSignature->approvalRequest->signed_document_path || $documentSignature->final_pdf_path)
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small flex-grow-1" style="word-break: break-all;">
                                        {{ basename($documentSignature->approvalRequest->signed_document_path ?? $documentSignature->final_pdf_path) }}
                                    </code>
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                            onclick="previewDocument('signed')"
                                            title="Preview Signed Document">
                                        <i class="fas fa-eye"></i> Show
                                    </button>
                                </div>
                            @else
                                <span class="text-muted">Not yet signed</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Digital Signature Information -->
            @if($documentSignature->signature_status !== 'pending')
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-signature me-2"></i>
                            Digital Signature Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Unique Encryption Key:</strong> This document is secured with a unique RSA-2048 digital signature key that was automatically generated specifically for this document. Each signed document has its own independent encryption key for maximum security.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signature ID:</strong><br>
                                <code>{{ $documentSignature->digitalSignature->signature_id ?? 'N/A' }}</code>
                                <br>
                                <span class="badge bg-primary mt-1">
                                    <i class="fas fa-key me-1"></i> Auto-Generated Unique Key
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Signature Status:</strong><br>
                                <span class="status-badge status-{{ strtolower($documentSignature->signature_status) }}">
                                    {{ ucfirst($documentSignature->signature_status) }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signed By:</strong><br>
                                @if($documentSignature->signer)
                                    {{ $documentSignature->signer->name }}<br>
                                    <small class="text-muted">
                                        NIDN: {{ $documentSignature->signer->NIDN ?? '-' }}<br>
                                        Email: {{ $documentSignature->signer->email }}
                                    </small>
                                @else
                                    <span class="text-muted">Not signed yet</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Signed At:</strong><br>
                                @if($documentSignature->signed_at)
                                    {{ $documentSignature->signed_at->format('d F Y, H:i:s') }}
                                    <br><small class="text-muted">{{ $documentSignature->signed_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Not signed yet</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Signature Algorithm:</strong><br>
                                <span class="badge bg-info">
                                    {{ $documentSignature->digitalSignature->algorithm ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Key Length:</strong><br>
                                <span class="badge bg-success">
                                    {{ $documentSignature->digitalSignature->key_length ?? 'N/A' }} bits
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Certificate Status:</strong><br>
                                @if($documentSignature->digitalSignature && $documentSignature->digitalSignature->isValid())
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Valid
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Invalid
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($documentSignature->digitalSignature)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Certificate Valid From:</strong><br>
                                {{ $documentSignature->digitalSignature->valid_from->format('d F Y, H:i') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Certificate Valid Until:</strong><br>
                                {{ $documentSignature->digitalSignature->valid_until->format('d F Y, H:i') }}
                                @if($documentSignature->digitalSignature->isExpiringSoon())
                                    <span class="badge bg-warning ms-2">Expiring Soon</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signing Method:</strong><br>
                                @if(isset($documentSignature->signature_metadata['placement_method']))
                                    @if($documentSignature->signature_metadata['placement_method'] === 'drag_drop_qr')
                                        <span class="badge bg-success">
                                            <i class="fas fa-qrcode me-1"></i> QR Code Drag & Drop
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ ucwords(str_replace('_', ' ', $documentSignature->signature_metadata['placement_method'])) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Signed Via:</strong><br>
                                @if(isset($documentSignature->signature_metadata['signed_via']))
                                    <span class="badge bg-info">
                                        <i class="fas fa-globe me-1"></i> {{ ucwords(str_replace('_', ' ', $documentSignature->signature_metadata['signed_via'])) }}
                                    </span>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                        </div>

                        {{-- <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Document Hash:</strong><br>
                                <code class="small">{{ substr($documentSignature->document_hash, 0, 32) }}...</code>
                            </div>
                            <div class="col-md-6">
                                <strong>Signature Value:</strong><br>
                                <code class="small">{{ substr($documentSignature->signature_value, 0, 32) }}...</code>
                            </div>
                        </div> --}}

                        @if($documentSignature->verified_at)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Verified At:</strong><br>
                                {{ $documentSignature->verified_at->format('d F Y, H:i:s') }}
                                <br><small class="text-muted">{{ $documentSignature->verified_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Verified By:</strong><br>
                                @if($documentSignature->verifier)
                                    {{ $documentSignature->verifier->name }}<br>
                                    <small class="text-muted">{{ $documentSignature->verifier->email }}</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Approval Workflow Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Approval Workflow Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="status-timeline">
                        <!-- Submitted -->
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Document Submitted</strong>
                                <div class="small text-muted">
                                    {{ $documentSignature->approvalRequest->created_at->format('d M Y, H:i') }}
                                </div>
                                <div class="small text-muted">
                                    By: {{ $documentSignature->approvalRequest->user->name }}
                                </div>
                            </div>
                        </div>

                        <!-- Approved -->
                        @if($documentSignature->approvalRequest->approved_at)
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Approved by Kaprodi</strong>
                                <div class="small text-muted">
                                    {{ $documentSignature->approvalRequest->approved_at->format('d M Y, H:i') }}
                                </div>
                                @if($documentSignature->approvalRequest->approver)
                                <div class="small text-muted">
                                    By: {{ $documentSignature->approvalRequest->approver->name }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Signed -->
                        @if($documentSignature->signed_at)
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Digitally Signed</strong>
                                <div class="small text-muted">
                                    {{ $documentSignature->signed_at->format('d M Y, H:i') }}
                                </div>
                                @if($documentSignature->signer)
                                <div class="small text-muted">
                                    By: {{ $documentSignature->signer->name }} (NIDN: {{ $documentSignature->signer->NIDN ?? '-' }})
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Signature Approved -->
                        @if($documentSignature->approvalRequest->sign_approved_at)
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Signature Approved & Finalized</strong>
                                <div class="small text-muted">
                                    {{ $documentSignature->approvalRequest->sign_approved_at->format('d M Y, H:i') }}
                                </div>
                                @if($documentSignature->approvalRequest->signApprover)
                                <div class="small text-muted">
                                    By: {{ $documentSignature->approvalRequest->signApprover->name }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Verified -->
                        @if($documentSignature->verified_at)
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Signature Verified</strong>
                                <div class="small text-success">
                                    {{ $documentSignature->verified_at->format('d M Y, H:i') }}
                                </div>
                                @if($documentSignature->verifier)
                                <div class="small text-muted">
                                    By: {{ $documentSignature->verifier->name }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Rejected -->
                        @if($documentSignature->rejected_at)
                        <div class="timeline-item">
                            <div class="timeline-dot rejected"></div>
                            <div>
                                <strong class="text-danger">Signature Rejected</strong>
                                <div class="small text-danger">
                                    {{ $documentSignature->rejected_at->format('d M Y, H:i') }}
                                </div>
                                @if($documentSignature->rejector)
                                <div class="small text-muted">
                                    By: {{ $documentSignature->rejector->name }}
                                </div>
                                @endif
                                <div class="small mt-2 p-2 bg-danger bg-opacity-10 rounded">
                                    <strong>Reason:</strong> {{ $documentSignature->rejection_reason }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Technical Details -->
            {{-- <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Technical Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Verification Token:</strong><br>
                            <code class="small">{{ substr($documentSignature->verification_token, 0, 32) }}...</code>
                        </div>
                        <div class="col-md-6">
                            <strong>Document Signature ID:</strong><br>
                            <code class="small">#{{ $documentSignature->id }}</code>
                        </div>
                    </div>

                    @if($documentSignature->positioning_data)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Signature Positioning Data:</strong><br>
                            <div class="bg-light p-2 rounded">
                                <code class="small">{{ json_encode($documentSignature->positioning_data, JSON_PRETTY_PRINT) }}</code>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($documentSignature->signature_metadata)
                    <div class="row">
                        <div class="col-12">
                            <strong>Signature Metadata:</strong><br>
                            <div class="bg-light p-2 rounded">
                                <code class="small">{{ json_encode($documentSignature->signature_metadata, JSON_PRETTY_PRINT) }}</code>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div> --}}

            <!-- Canvas Signature Preview -->
            {{-- @if($documentSignature->canvas_data_path)
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-paint-brush me-2"></i>
                        Signature Preview
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ Storage::url($documentSignature->canvas_data_path) }}" alt="Your Signature"
                         class="img-fluid" style="max-height: 200px; border: 2px dashed #dee2e6; border-radius: 0.5rem; padding: 1rem;">
                    <p class="small text-muted mt-2 mb-0">Your digital signature canvas</p>
                </div>
            </div>
            @endif --}}
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header {{ $documentSignature->signature_status === 'rejected' ? 'bg-danger' : 'bg-primary' }} text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($documentSignature->signature_status === 'rejected')
                            {{-- REJECTED: Show resubmit options --}}
                            <a href="{{ route('user.signature.approval.request') }}" class="btn btn-danger">
                                <i class="fas fa-redo me-2"></i> Submit New Request
                            </a>
                            <a href="{{ route('user.signature.my.signatures.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i> Back to List
                            </a>
                            <button class="btn btn-outline-info" onclick="showRejectionHelp()">
                                <i class="fas fa-question-circle me-2"></i> Need Help?
                            </button>
                        @else
                            {{-- NORMAL: Show download and verification options --}}
                            @if($documentSignature->approvalRequest->signed_document_path || $documentSignature->final_pdf_path && in_array($documentSignature->signature_status, ['verified']))
                                <a href="{{ route('user.signature.my.signatures.download', $documentSignature->id) }}"
                                   class="btn btn-success">
                                    <i class="fas fa-download me-2"></i> Download Signed Document
                                </a>
                            @endif

                            @if($documentSignature->qr_code_path && in_array($documentSignature->signature_status, ['verified']))
                                <a href="{{ route('user.signature.my.signatures.qr', $documentSignature->id) }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-qrcode me-2"></i> Download QR Code
                                </a>
                            @endif

                            @if($documentSignature->verification_url && in_array($documentSignature->signature_status, ['verified']))
                                <button class="btn btn-outline-info" onclick="copyVerificationLink()">
                                    <i class="fas fa-link me-2"></i> Copy Verification Link
                                </button>
                            @endif

                            <a href="{{ route('signature.verify.page') }}" class="btn btn-outline-warning" target="_blank">
                                <i class="fas fa-shield-alt me-2"></i> Verify Document
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rejection Details Card -->
            @if($documentSignature->signature_status === 'rejected')
            <div class="card mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Rejection Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong class="d-block mb-2">Rejection Reason:</strong>
                        <div class="alert alert-danger mb-0 py-2">
                            {{ $documentSignature->rejection_reason }}
                        </div>
                    </div>
                    @if($documentSignature->rejected_at)
                    <div class="mb-3">
                        <strong>Rejected On:</strong><br>
                        <small>{{ $documentSignature->rejected_at->format('d F Y, H:i') }}</small><br>
                        <small class="text-muted">{{ $documentSignature->rejected_at->diffForHumans() }}</small>
                    </div>
                    @endif
                    @if($documentSignature->rejector)
                    <div class="mb-3">
                        <strong>Rejected By:</strong><br>
                        <small>{{ $documentSignature->rejector->name }}</small><br>
                        <small class="text-muted">{{ $documentSignature->rejector->email }}</small>
                    </div>
                    @endif
                    <hr>
                    <div class="small">
                        <strong><i class="fas fa-info-circle me-1"></i> What to do:</strong>
                        <ol class="mb-0 ps-3 mt-2">
                            <li>Review the rejection reason carefully</li>
                            <li>Prepare a corrected document</li>
                            <li>Submit a new approval request</li>
                            <li>Ensure proper signature placement and quality</li>
                        </ol>
                    </div>
                </div>
            </div>
            @endif

            <!-- QR Code Display (Hidden for Rejected) -->
            @if($documentSignature->qr_code_path && in_array($documentSignature->signature_status, ['verified']))
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Verification QR Code
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ Storage::url($documentSignature->qr_code_path) }}"
                         alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="small text-muted mb-0">
                        Scan this QR code to verify document authenticity
                    </p>
                </div>
            </div>
            @endif

            <!-- Verification URL (Hidden for Rejected) -->
            @if($documentSignature->verification_url && in_array($documentSignature->signature_status, ['verified']))
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-link me-2"></i>
                        Verification Link
                    </h5>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="verificationUrl"
                               value="{{ $documentSignature->verification_url }}" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyVerificationLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Share this link to allow others to verify your document
                    </small>
                </div>
            </div>
            @endif

            <!-- Document Status Summary -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Status Summary
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Approval Status:</strong><br>
                            <span class="badge status-badge status-approval-{{ strtolower($documentSignature->approvalRequest->status) }}">
                                {{ str_replace('_', ' ', ucwords($documentSignature->approvalRequest->status, '_')) }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Signature Status:</strong><br>
                            <span class="badge status-badge status-{{ strtolower($documentSignature->signature_status) }}">
                                {{ ucfirst($documentSignature->signature_status) }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Certificate Status:</strong><br>
                            @if($documentSignature->digitalSignature && $documentSignature->digitalSignature->isValid())
                                <span class="badge bg-success">Valid</span>
                            @else
                                <span class="badge bg-danger">Invalid/Expired</span>
                            @endif
                        </li>
                        {{-- <li class="mb-2">
                            <strong>Revision Count:</strong><br>
                            <span class="badge bg-secondary">{{ $documentSignature->approvalRequest->revision_count ?? 0 }} Revisions</span>
                        </li> --}}
                        <li class="mb-2">
                            <strong>Progress:</strong><br>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ $documentSignature->approvalRequest->workflow_progress }}%"
                                     aria-valuenow="{{ $documentSignature->approvalRequest->workflow_progress }}"
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ $documentSignature->approvalRequest->workflow_progress }}%
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentPreviewModalLabel">
                    <i class="fas fa-file-pdf me-2"></i>
                    <span id="previewTitle">Document Preview</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <div id="pdfLoadingIndicator" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading document...</p>
                </div>
                <iframe id="documentPreviewFrame"
                        style="width: 100%; height: 100%; border: none; display: none;"
                        frameborder="0">
                </iframe>
                <div id="previewError" class="alert alert-danger m-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> <span id="errorMessage">Unable to load document preview.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a id="downloadDocumentBtn" href="#" class="btn btn-success" target="_blank">
                    <i class="fas fa-download me-1"></i> Download Document
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Document paths for preview
const documentPaths = {
    original: @json($documentSignature->approvalRequest->document_path ? Storage::url($documentSignature->approvalRequest->document_path) : null),
    signed: @json(
        $documentSignature->approvalRequest->signed_document_path
            ? Storage::url($documentSignature->approvalRequest->signed_document_path)
            : ($documentSignature->final_pdf_path ? Storage::url($documentSignature->final_pdf_path) : null)
    )
};

function previewDocument(type) {
    const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
    const iframe = document.getElementById('documentPreviewFrame');
    const loading = document.getElementById('pdfLoadingIndicator');
    const errorDiv = document.getElementById('previewError');
    const titleSpan = document.getElementById('previewTitle');
    const downloadBtn = document.getElementById('downloadDocumentBtn');

    // Reset modal state
    iframe.style.display = 'none';
    errorDiv.style.display = 'none';
    loading.style.display = 'block';

    const originalFileName = '{{ basename($documentSignature->approvalRequest->document_path) }}';
    const signedFileName = '{{ $documentSignature->final_pdf_path ? basename($documentSignature->final_pdf_path) : "Not Signed Yet" }}';

    // Set title based on type
    if (type === 'original') {
        titleSpan.textContent = 'Original Document Preview';
    } else {
        titleSpan.textContent = 'Signed Document Preview';
    }

    // Get document path
    const docPath = documentPaths[type];

    if (!docPath) {
        loading.style.display = 'none';
        errorDiv.style.display = 'block';
        document.getElementById('errorMessage').textContent = 'Document not available for preview.';
        modal.show();
        return;
    }

    // Set download button
    downloadBtn.href = docPath;

    if(type === 'signed' && !{{ in_array($documentSignature->signature_status, ['verified']) ? 'true' : 'false' }}) {
        downloadBtn.style.display = 'none';
    } else {
        downloadBtn.style.display = 'inline-block';
    }

    // Show modal
    modal.show();

    // Load PDF in iframe
    iframe.onload = function() {
        loading.style.display = 'none';
        iframe.style.display = 'block';
    };

    iframe.onerror = function() {
        loading.style.display = 'none';
        errorDiv.style.display = 'block';
        document.getElementById('errorMessage').textContent = 'Failed to load document. The file may be corrupted or not accessible.';
    };

    // Set iframe source (add #toolbar=0 to hide PDF toolbar for cleaner view)
    iframe.src = docPath + '#toolbar=0';

    // Fallback timeout in case onload doesn't fire
    setTimeout(function() {
        if (loading.style.display !== 'none') {
            loading.style.display = 'none';
            iframe.style.display = 'block';
        }
    }, 3000);
}

function copyVerificationLink() {
    const input = document.getElementById('verificationUrl');
    input.select();
    input.setSelectionRange(0, 99999);

    try {
        document.execCommand('copy');

        // Show success feedback
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary', 'btn-outline-info');

        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    } catch (err) {
        alert('Failed to copy. Please copy manually.');
    }
}

function showRejectionHelp() {
    const helpText = `
📋 Why was my signature rejected?

Common reasons for rejection:
• Signature placement is incorrect (too far left/right)
• Signature size is too large and overlaps document content
• Signature quality is poor or distorted
• Signature is not in the designated signature area

💡 How to fix this:

1. Read the rejection reason carefully
2. Prepare a new corrected document
3. When signing:
   - Place your signature in the center of the signature box
   - Don't zoom in too much (keep signature at normal size)
   - Draw clearly with smooth strokes
   - Ensure signature is within the designated area

4. Submit a new approval request

Need more help? Contact your Kaprodi directly.
    `;

    alert(helpText);
}
</script>
@endpush

@push('styles')
<style>
.status-timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 2rem;
    width: 2px;
    height: calc(100% - 1rem);
    background: #e9ecef;
}

.timeline-dot {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.timeline-dot.completed {
    background: #28a745;
}

.timeline-dot.current {
    background: #007bff;
    animation: pulse 2s infinite;
}

.timeline-dot.pending {
    background: #6c757d;
}

.timeline-dot.rejected {
    background: #dc3545;
    animation: pulse-red 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.priority-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.priority-low { background-color: #e9ecef; color: #495057; }
.priority-normal { background-color: #cfe2ff; color: #084298; }
.priority-high { background-color: #fff3cd; color: #664d03; }
.priority-urgent { background-color: #f8d7da; color: #842029; }

.status-badge {
    padding: 0.35rem 0.65rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.status-pending { background-color: #fff3cd; color: #664d03; }
.status-signed { background-color: #cfe2ff; color: #084298; }
.status-verified { background-color: #d1e7dd; color: #0f5132; }
.status-rejected { background-color: #f8d7da; color: #842029; }
.status-invalid { background-color: #f8d7da; color: #842029; }

.status-approval-pending { background-color: #fff3cd; color: #664d03; }
.status-approval-approved { background-color: #d1e7dd; color: #0f5132; }
.status-approval-rejected { background-color: #f8d7da; color: #842029; }
.status-approval-user_signed { background-color: #cfe2ff; color: #084298; }
.status-approval-sign_approved { background-color: #d1e7dd; color: #0f5132; }
.status-approval-cancelled { background-color: #f8d7da; color: #842029; }

/* Document Preview Styling */
.modal-xl {
    max-width: 90%;
}

/* Compact button styling for document preview */
.btn-sm.btn-outline-primary,
.btn-sm.btn-outline-success {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    white-space: nowrap;
}

/* Loading indicator animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush
