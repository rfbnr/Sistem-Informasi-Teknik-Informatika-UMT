{{-- resources/views/digital-signature/admin/signature-details.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Document Signature Details')

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
                    Document Signature Details
                </h1>
                <p class="mb-0 opacity-75">{{ $documentSignature->approvalRequest->document_name }}</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.documents.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <a href="{{ route('admin.signature.documents.download', $documentSignature->id) }}" class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>

    <!-- Verification Result -->
    @if($verificationResult)
        @if($verificationResult['is_valid'])
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Signature Verified:</strong> {{ $verificationResult['message'] }}
            </div>
        @else
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <strong>Verification Failed:</strong> {{ $verificationResult['message'] }}
            </div>
        @endif
    @endif

    <div class="row">
        <!-- Main Information -->
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
                            {{ $documentSignature->approvalRequest->full_document_number }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submitted By:</strong><br>
                            {{ $documentSignature->approvalRequest->user->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Submission Date:</strong><br>
                            {{ $documentSignature->approvalRequest->created_at->format('d F Y H:i') }}
                        </div>
                    </div>

                    @if($documentSignature->approvalRequest->notes)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Notes:</strong><br>
                            <p class="text-muted">{{ $documentSignature->approvalRequest->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <strong>PDF Status:</strong><br>
                            @if($documentSignature->final_pdf_path)
                                <div class="alert alert-success mb-0 mt-2">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Signed PDF Available</strong> - Document has been merged with digital signature
                                    <br>
                                    <small class="text-muted mt-1 d-block">
                                        Path: <code>{{ $documentSignature->final_pdf_path }}</code>
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-warning mb-0 mt-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Original Document Only</strong> - Signed PDF not yet generated
                                    <br>
                                    <small class="text-muted mt-1 d-block">
                                        Using original document: <code>{{ $documentSignature->approvalRequest->document_path }}</code>
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
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
                            <strong>Signed By:</strong><br>
                            {{ $documentSignature->signer->name ?? 'Unknown' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Signed At:</strong><br>
                            @if($documentSignature->signed_at)
                                {{ $documentSignature->signed_at->format('d F Y H:i:s') }}
                                <br><small class="text-muted">{{ $documentSignature->signed_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">Not signed yet</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Algorithm:</strong><br>
                            <span class="badge bg-info">{{ $documentSignature->digitalSignature->algorithm ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Key Length:</strong><br>
                            <span class="badge bg-success">{{ $documentSignature->digitalSignature->key_length ?? 'N/A' }} bits</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Signature Status:</strong><br>
                            <span class="status-badge status-{{ strtolower($documentSignature->signature_status) }}">
                                {{ ucfirst($documentSignature->signature_status) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Document Hash:</strong><br>
                            <code>{{ substr($documentSignature->document_hash, 0, 16) }}...</code>
                        </div>
                    </div>

                    @if($documentSignature->verified_at)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Verified At:</strong><br>
                            {{ $documentSignature->verified_at->format('d F Y H:i:s') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Verified By:</strong><br>
                            {{ $documentSignature->verifier->name ?? 'System' }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Verification Checks -->
            @if($verificationResult && isset($verificationResult['details']['checks']))
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        Verification Checks
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($verificationResult['details']['checks'] as $checkName => $check)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                @if($check['status'])
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                @else
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <strong>{{ ucwords(str_replace('_', ' ', $checkName)) }}</strong>
                                <div class="small text-muted">{{ $check['message'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- PDF Preview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf me-2"></i>
                        Document Preview
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="pdf-preview-container" style="position: relative; width: 100%; height: 800px;">
                        <iframe
                            src="{{ route('admin.signature.documents.view', $documentSignature->id) }}"
                            style="width: 100%; height: 100%; border: none;"
                            title="Document Preview"
                            id="pdfPreview">
                        </iframe>
                        <div class="pdf-loading-overlay" id="pdfLoading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; align-items: center; justify-content: center;">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                                <p class="text-muted">Loading PDF preview...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            @if($documentSignature->final_pdf_path)
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-check-circle me-1"></i> Signed PDF
                                </span>
                            @else
                                <span class="badge bg-secondary me-2">
                                    <i class="fas fa-file me-1"></i> Original Document
                                </span>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('admin.signature.documents.view', $documentSignature->id) }}"
                               class="btn btn-sm btn-outline-primary"
                               target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i> Open in New Tab
                            </a>
                            <a href="{{ route('admin.signature.documents.download', $documentSignature->id) }}"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-download me-1"></i> Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Image Preview -->
            @if($documentSignature->canvas_data)
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-paint-brush me-2"></i>
                        Signature Template Preview
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $documentSignature->canvas_data }}" alt="Signature" class="img-fluid" style="max-height: 200px; border: 2px dashed #dee2e6; border-radius: 0.5rem; padding: 1rem;">
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
                        @if($documentSignature->signature_status === 'signed')
                            <button class="btn btn-success" onclick="verifySignature()">
                                <i class="fas fa-check me-2"></i> Verify Signature
                            </button>
                        @endif

                        <a href="{{ route('admin.signature.documents.download', $documentSignature->id) }}" class="btn btn-info">
                            <i class="fas fa-download me-2"></i> Download Document
                        </a>

                        @if($documentSignature->qr_code_path)
                            <a href="{{ route('admin.signature.documents.qr.download', $documentSignature->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-qrcode me-2"></i> Download QR Code
                            </a>
                        @else
                            <button class="btn btn-outline-secondary" onclick="regenerateQR()">
                                <i class="fas fa-qrcode me-2"></i> Generate QR Code
                            </button>
                        @endif

                        @if(in_array($documentSignature->signature_status, ['signed', 'verified']))
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#invalidateModal">
                                <i class="fas fa-ban me-2"></i> Invalidate Signature
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($documentSignature->qr_code_path)
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Verification QR Code
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ Storage::url($documentSignature->qr_code_path) }}" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="small text-muted">Scan to verify document authenticity</p>
                    <div class="small">
                        <strong>Verification URL:</strong><br>
                        <input type="text" class="form-control form-control-sm mt-2" readonly value="{{ $documentSignature->verification_url }}">
                    </div>
                </div>
            </div>
            @endif

            <!-- Audit Trail -->
            @if($documentSignature->auditLogs && $documentSignature->auditLogs->count() > 0)
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Audit Trail
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($documentSignature->auditLogs->take(5) as $log)
                        <div class="mb-2 pb-2 border-bottom">
                            <small>
                                <strong>{{ $log->action }}</strong><br>
                                <span class="text-muted">
                                    {{ $log->performed_at->format('d M Y H:i') }}
                                    by {{ $log->user->name ?? 'System' }}
                                </span>
                            </small>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
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
            <form action="{{ route('admin.signature.documents.invalidate', $documentSignature->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will mark the signature as invalid and cannot be undone.
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
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
// Hide PDF loading overlay when iframe is loaded
document.addEventListener('DOMContentLoaded', function() {
    const pdfIframe = document.getElementById('pdfPreview');
    const loadingOverlay = document.getElementById('pdfLoading');

    if (pdfIframe && loadingOverlay) {
        pdfIframe.addEventListener('load', function() {
            // Hide loading overlay after 500ms delay for smooth transition
            setTimeout(function() {
                loadingOverlay.style.display = 'none';
            }, 500);
        });

        // Also hide on error
        pdfIframe.addEventListener('error', function() {
            loadingOverlay.innerHTML = '<div class="text-center"><i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i><p class="text-danger">Failed to load PDF preview</p></div>';
        });
    }
});

function verifySignature() {
    if (confirm('Verify this document signature?')) {
        fetch('{{ route("admin.signature.documents.verify", $documentSignature->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                alert('Signature verified successfully!');
                location.reload();
            } else {
                alert('Verification failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error during verification:', error);
            alert('An error occurred during verification.');
        });
    }
}

function regenerateQR() {
    if (confirm('Generate QR code for this document?')) {
        fetch('{{ route("admin.signature.documents.qr.regenerate", $documentSignature->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('QR code generated successfully!');
                location.reload();
            } else {
                alert('QR generation failed');
            }
        });
    }
}
</script>
@endpush
