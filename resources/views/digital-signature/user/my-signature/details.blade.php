{{-- resources/views/digital-signature/user/my-signatures/details.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Signature Details')

@section('content')
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
                <a href="{{ route('user.signature.my.signatures.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($documentSignature->signature_status === 'verified')
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Verified!</strong> Your document signature has been verified and is ready for download.
        </div>
    @elseif($documentSignature->signature_status === 'signed')
        <div class="alert alert-info">
            <i class="fas fa-clock me-2"></i>
            <strong>Pending Verification:</strong> Your signature is awaiting final verification by Kaprodi.
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
                            <strong>Document Number:</strong><br>
                            <code>{{ $documentSignature->approvalRequest->full_document_number }}</code>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submission Date:</strong><br>
                            {{ $documentSignature->approvalRequest->created_at->format('d F Y, H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Priority:</strong><br>
                            <span class="priority-badge priority-{{ $documentSignature->approvalRequest->priority }}">
                                {{ ucfirst($documentSignature->approvalRequest->priority) }}
                            </span>
                        </div>
                    </div>

                    @if($documentSignature->approvalRequest->notes)
                    <div class="row">
                        <div class="col-12">
                            <strong>Notes:</strong><br>
                            <p class="text-muted mb-0">{{ $documentSignature->approvalRequest->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Signature Information -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-signature me-2"></i>
                        Digital Signature Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Signature Status:</strong><br>
                            <span class="status-badge status-{{ strtolower($documentSignature->signature_status) }}">
                                {{ ucfirst($documentSignature->signature_status) }}
                            </span>
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
                        <div class="col-md-6">
                            <strong>Signature Algorithm:</strong><br>
                            <span class="badge bg-info">
                                {{ $documentSignature->digitalSignature->algorithm ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Key Length:</strong><br>
                            <span class="badge bg-success">
                                {{ $documentSignature->digitalSignature->key_length ?? 'N/A' }} bits
                            </span>
                        </div>
                    </div>

                    @if($documentSignature->verified_at)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Verified At:</strong><br>
                            {{ $documentSignature->verified_at->format('d F Y, H:i:s') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Document Hash:</strong><br>
                            <code class="small">{{ substr($documentSignature->document_hash, 0, 20) }}...</code>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approval Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Document Timeline
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
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Signature Preview -->
            @if($documentSignature->canvas_data)
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-paint-brush me-2"></i>
                        Your Signature
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $documentSignature->canvas_data }}" alt="Your Signature"
                         class="img-fluid" style="max-height: 200px; border: 2px dashed #dee2e6; border-radius: 0.5rem; padding: 1rem;">
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($documentSignature->approvalRequest->signed_document_path || $documentSignature->final_pdf_path)
                            <a href="{{ route('user.signature.my.signatures.download', $documentSignature->id) }}"
                               class="btn btn-success">
                                <i class="fas fa-download me-2"></i> Download Signed Document
                            </a>
                        @endif

                        @if($documentSignature->qr_code_path)
                            <a href="{{ route('user.signature.my.signatures.qr', $documentSignature->id) }}"
                               class="btn btn-outline-secondary">
                                <i class="fas fa-qrcode me-2"></i> Download QR Code
                            </a>
                        @endif

                        @if($documentSignature->verification_url)
                            <button class="btn btn-outline-info" onclick="copyVerificationLink()">
                                <i class="fas fa-link me-2"></i> Copy Verification Link
                            </button>
                        @endif

                        <a href="{{ route('signature.verify.page') }}" class="btn btn-outline-warning" target="_blank">
                            <i class="fas fa-shield-alt me-2"></i> Verify Document
                        </a>
                    </div>
                </div>
            </div>

            <!-- QR Code Display -->
            @if($documentSignature->qr_code_path)
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

            <!-- Verification URL -->
            @if($documentSignature->verification_url)
            <div class="card">
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
        }, 2000);
    } catch (err) {
        alert('Failed to copy. Please copy manually.');
    }
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

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}
</style>
@endpush
