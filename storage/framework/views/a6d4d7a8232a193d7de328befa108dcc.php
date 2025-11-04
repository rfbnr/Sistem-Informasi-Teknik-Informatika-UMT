<?php $__env->startSection('title', 'Document Signature Details'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('digital-signature.admin.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-file-signature me-3"></i>
                    Document Signature Details
                </h1>
                <p class="mb-0 opacity-75"><?php echo e($documentSignature->approvalRequest->document_name); ?></p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?php echo e(route('admin.signature.documents.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <a href="<?php echo e(route('admin.signature.documents.download', $documentSignature->id)); ?>" class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>

    <!-- Verification Result -->
    <?php if($verificationResult): ?>
        <?php if($verificationResult['is_valid']): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Signature Verified:</strong> <?php echo e($verificationResult['message']); ?>

            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <strong>Verification Failed:</strong> <?php echo e($verificationResult['message']); ?>

            </div>
        <?php endif; ?>
    <?php endif; ?>

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
                            <?php echo e($documentSignature->approvalRequest->document_name); ?>

                        </div>
                        <div class="col-md-6">
                            <strong>Document Type:</strong><br>
                            <?php if($documentSignature->approvalRequest->document_type): ?>
                                <span class="badge bg-secondary">
                                    <?php echo e($documentSignature->approvalRequest->document_type); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submission Date:</strong><br>
                            <?php echo e($documentSignature->approvalRequest->created_at->format('d F Y, H:i')); ?>

                            <small class="text-muted d-block"><?php echo e($documentSignature->approvalRequest->created_at->diffForHumans()); ?></small>
                        </div>
                        
                    </div>

                    <div class="row mb-3">
                        <?php if($documentSignature->approvalRequest->notes): ?>
                        <div class="col-md-6">
                            <div class="col-12">
                                <strong>Submission Notes:</strong><br>
                                <p class="text-muted mb-0"><?php echo e($documentSignature->approvalRequest->notes); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($documentSignature->approvalRequest->approval_notes): ?>
                        <div class="col-md-6">
                            <div class="col-12">
                                <strong>Approval Notes:</strong><br>
                                <div class="mb-0">
                                    <?php echo e($documentSignature->approvalRequest->approval_notes); ?>

                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if($documentSignature->approvalRequest->admin_notes): ?>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Admin Notes:</strong><br>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo e($documentSignature->approvalRequest->admin_notes); ?>

                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Document Files Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Original Document:</strong><br>
                            <?php if($documentSignature->approvalRequest->document_path): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small flex-grow-1" style="word-break: break-all;">
                                        <?php echo e(basename($documentSignature->approvalRequest->document_path)); ?>

                                    </code>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="previewDocument('original')"
                                            title="Preview Original Document">
                                        <i class="fas fa-eye"></i> Show
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Not available</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Signed Document:</strong><br>
                            <?php if($documentSignature->approvalRequest->signed_document_path || $documentSignature->final_pdf_path): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small flex-grow-1" style="word-break: break-all;">
                                        <?php echo e(basename($documentSignature->approvalRequest->signed_document_path ?? $documentSignature->final_pdf_path)); ?>

                                    </code>
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                            onclick="previewDocument('signed')"
                                            title="Preview Signed Document">
                                        <i class="fas fa-eye"></i> Show
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Not yet signed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Digital Signature Information -->
            <?php if($documentSignature->signature_status !== 'pending'): ?>
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
                                <code><?php echo e($documentSignature->digitalSignature->signature_id ?? 'N/A'); ?></code>
                                <br>
                                <span class="badge bg-primary mt-1">
                                    <i class="fas fa-key me-1"></i> Auto-Generated Unique Key
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Signature Status:</strong><br>
                                <span class="status-badge status-<?php echo e(strtolower($documentSignature->signature_status)); ?>">
                                    <?php echo e(ucfirst($documentSignature->signature_status)); ?>

                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signed By:</strong><br>
                                <?php if($documentSignature->signer): ?>
                                    <?php echo e($documentSignature->signer->name); ?><br>
                                    <small class="text-muted">
                                        NIDN: <?php echo e($documentSignature->signer->NIDN ?? '-'); ?><br>
                                        Email: <?php echo e($documentSignature->signer->email); ?>

                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">Not signed yet</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Signed At:</strong><br>
                                <?php if($documentSignature->signed_at): ?>
                                    <?php echo e($documentSignature->signed_at->format('d F Y, H:i:s')); ?>

                                    <br><small class="text-muted"><?php echo e($documentSignature->signed_at->diffForHumans()); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Not signed yet</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Signature Algorithm:</strong><br>
                                <span class="badge bg-info">
                                    <?php echo e($documentSignature->digitalSignature->algorithm ?? 'N/A'); ?>

                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Key Length:</strong><br>
                                <span class="badge bg-success">
                                    <?php echo e($documentSignature->digitalSignature->key_length ?? 'N/A'); ?> bits
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Certificate Status:</strong><br>
                                <?php if($documentSignature->digitalSignature && $documentSignature->digitalSignature->isValid()): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Valid
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Invalid
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if($documentSignature->digitalSignature): ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Certificate Valid From:</strong><br>
                                <?php echo e($documentSignature->digitalSignature->valid_from->format('d F Y, H:i')); ?>

                            </div>
                            <div class="col-md-6">
                                <strong>Certificate Valid Until:</strong><br>
                                <?php echo e($documentSignature->digitalSignature->valid_until->format('d F Y, H:i')); ?>

                                <?php if($documentSignature->digitalSignature->isExpiringSoon()): ?>
                                    <span class="badge bg-warning ms-2">Expiring Soon</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signing Method:</strong><br>
                                <?php if(isset($documentSignature->signature_metadata['placement_method'])): ?>
                                    <?php if($documentSignature->signature_metadata['placement_method'] === 'drag_drop_qr'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-qrcode me-1"></i> QR Code Drag & Drop
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <?php echo e(ucwords(str_replace('_', ' ', $documentSignature->signature_metadata['placement_method']))); ?>

                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Not specified</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Signed Via:</strong><br>
                                <?php if(isset($documentSignature->signature_metadata['signed_via'])): ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-globe me-1"></i> <?php echo e(ucwords(str_replace('_', ' ', $documentSignature->signature_metadata['signed_via']))); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Not specified</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Document Hash:</strong><br>
                                <code class="small"><?php echo e(substr($documentSignature->document_hash, 0, 32)); ?>...</code>
                            </div>
                            <div class="col-md-6">
                                <strong>Signature Value:</strong><br>
                                <code class="small"><?php echo e(substr($documentSignature->signature_value, 0, 32)); ?>...</code>
                            </div>
                        </div>

                        <?php if($documentSignature->verified_at): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Verified At:</strong><br>
                                <?php echo e($documentSignature->verified_at->format('d F Y, H:i:s')); ?>

                                <br><small class="text-muted"><?php echo e($documentSignature->verified_at->diffForHumans()); ?></small>
                            </div>
                            <div class="col-md-6">
                                <strong>Verified By:</strong><br>
                                <?php if($documentSignature->verifier): ?>
                                    <?php echo e($documentSignature->verifier->name); ?><br>
                                    <small class="text-muted"><?php echo e($documentSignature->verifier->email); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">System</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Verification Checks -->
            <?php if($verificationResult && isset($verificationResult['details']['checks'])): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        Verification Checks
                    </h5>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $verificationResult['details']['checks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checkName => $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                <?php if($check['status']): ?>
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <strong><?php echo e(ucwords(str_replace('_', ' ', $checkName))); ?></strong>
                                <div class="small text-muted"><?php echo e($check['message']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Signature Image Preview -->
            <?php if($documentSignature->canvas_data): ?>
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-paint-brush me-2"></i>
                        Signature Template Preview
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo e($documentSignature->canvas_data); ?>" alt="Signature" class="img-fluid" style="max-height: 200px; border: 2px dashed #dee2e6; border-radius: 0.5rem; padding: 1rem;">
                </div>
            </div>
            <?php endif; ?>
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
                        

                        <a href="<?php echo e(route('admin.signature.documents.download', $documentSignature->id)); ?>" class="btn btn-info">
                            <i class="fas fa-download me-2"></i> Download Document
                        </a>

                        <?php if($documentSignature->qr_code_path): ?>
                            <a href="<?php echo e(route('admin.signature.documents.qr.download', $documentSignature->id)); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-qrcode me-2"></i> Download QR Code
                            </a>
                        
                        <?php endif; ?>

                        <?php if(in_array($documentSignature->signature_status, ['signed', 'verified'])): ?>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#invalidateModal">
                                <i class="fas fa-ban me-2"></i> Invalidate Signature
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <?php if($documentSignature->qr_code_path): ?>
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Verification QR Code
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo e(Storage::url($documentSignature->qr_code_path)); ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="small text-muted">Scan to verify document authenticity</p>
                    <div class="small">
                        <strong>Verification URL:</strong><br>
                        <input type="text" class="form-control form-control-sm mt-2" readonly value="<?php echo e($documentSignature->verification_url); ?>">
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Audit Trail -->
            <?php if($documentSignature->auditLogs && $documentSignature->auditLogs->count() > 0): ?>
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Audit Trail
                    </h5>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $documentSignature->auditLogs->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-2 pb-2 border-bottom">
                            <small>
                                <strong><?php echo e($log->action); ?></strong><br>
                                <span class="text-muted">
                                    <?php echo e($log->performed_at->format('d M Y H:i')); ?>

                                    by <?php echo e($log->user->name ?? 'System'); ?>

                                </span>
                            </small>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>
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
            <form action="<?php echo e(route('admin.signature.documents.invalidate', $documentSignature->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
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

<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentPreviewModalLabel">
                    <i class="fas fa-file-pdf me-2"></i>
                    <span id="previewTitle">
                        Document Preview
                        <?php echo e($documentSignature->approvalRequest->document_name); ?>

                    </span>
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

<!-- Reject Signature Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Document Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <?php echo csrf_field(); ?>
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
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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

const documentPaths = {
    original: <?php echo json_encode($documentSignature->approvalRequest->document_path ? Storage::url($documentSignature->approvalRequest->document_path) : null, 15, 512) ?>,
    signed: <?php echo json_encode(
        $documentSignature->approvalRequest->signed_document_path
            ? Storage::url($documentSignature->approvalRequest->signed_document_path)
            : ($documentSignature->final_pdf_path ? Storage::url($documentSignature->final_pdf_path) : null)
    , 15, 512) ?>
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

    const originalFileName = '<?php echo e(basename($documentSignature->approvalRequest->document_path)); ?>';
    const signedFileName = '<?php echo e($documentSignature->final_pdf_path ? basename($documentSignature->final_pdf_path) : "Not Signed Yet"); ?>';

    // Set title based on type
    if (type === 'original') {
        titleSpan.textContent = 'Original Document Preview | ' + originalFileName;
    } else {
        titleSpan.textContent = 'Signed Document Preview | ' + signedFileName;
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

function setRejectReason(reason) {
    document.getElementById('reject_reason').value = reason;
}

// Quick Reject from Modal
function quickRejectFromModal(id) {
    // Close quick preview modal
    bootstrap.Modal.getInstance(document.getElementById('quickPreviewModal')).hide();

    // Small delay then open reject modal

}
</script>
<?php $__env->stopPush(); ?>



<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/signature-details.blade.php ENDPATH**/ ?>