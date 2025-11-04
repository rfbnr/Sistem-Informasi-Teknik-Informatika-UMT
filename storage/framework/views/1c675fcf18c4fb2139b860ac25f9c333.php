<?php $__env->startSection('title', 'Signature Details'); ?>

<?php $__env->startSection('content'); ?>
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
                <p class="mb-0 opacity-75"><?php echo e($documentSignature->approvalRequest->document_name); ?></p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?php echo e(route('user.signature.my.signatures.index')); ?>" class="btn btn-outline-warning">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    <?php if($documentSignature->signature_status === 'verified'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Verified!</strong> Your document signature has been verified and is ready for download.
        </div>
    <?php elseif($documentSignature->signature_status === 'signed'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-clock me-2"></i>
            <strong>Pending Verification:</strong> Your signature is awaiting final verification by Kaprodi.
        </div>
    
    <?php elseif($documentSignature->signature_status === 'invalid'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Invalid Signature:</strong> This signature has been marked as invalid.
        </div>
    <?php endif; ?>

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
                                    <?php echo e($documentSignature->approvalRequest->created_at->format('d M Y, H:i')); ?>

                                </div>
                                <div class="small text-muted">
                                    By: <?php echo e($documentSignature->approvalRequest->user->name); ?>

                                </div>
                            </div>
                        </div>

                        <!-- Approved -->
                        <?php if($documentSignature->approvalRequest->approved_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Approved by Kaprodi</strong>
                                <div class="small text-muted">
                                    <?php echo e($documentSignature->approvalRequest->approved_at->format('d M Y, H:i')); ?>

                                </div>
                                <?php if($documentSignature->approvalRequest->approver): ?>
                                <div class="small text-muted">
                                    By: <?php echo e($documentSignature->approvalRequest->approver->name); ?>

                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Signed -->
                        <?php if($documentSignature->signed_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Digitally Signed</strong>
                                <div class="small text-muted">
                                    <?php echo e($documentSignature->signed_at->format('d M Y, H:i')); ?>

                                </div>
                                <?php if($documentSignature->signer): ?>
                                <div class="small text-muted">
                                    By: <?php echo e($documentSignature->signer->name); ?> (NIDN: <?php echo e($documentSignature->signer->NIDN ?? '-'); ?>)
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Signature Approved -->
                        <?php if($documentSignature->approvalRequest->sign_approved_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Signature Approved & Finalized</strong>
                                <div class="small text-muted">
                                    <?php echo e($documentSignature->approvalRequest->sign_approved_at->format('d M Y, H:i')); ?>

                                </div>
                                <?php if($documentSignature->approvalRequest->signApprover): ?>
                                <div class="small text-muted">
                                    By: <?php echo e($documentSignature->approvalRequest->signApprover->name); ?>

                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Verified -->
                        <?php if($documentSignature->verified_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div>
                                <strong>Signature Verified</strong>
                                <div class="small text-success">
                                    <?php echo e($documentSignature->verified_at->format('d M Y, H:i')); ?>

                                </div>
                                <?php if($documentSignature->verifier): ?>
                                <div class="small text-muted">
                                    By: <?php echo e($documentSignature->verifier->name); ?>

                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Rejected -->
                        <?php if($documentSignature->rejected_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot rejected"></div>
                            <div>
                                <strong class="text-danger">Signature Rejected</strong>
                                <div class="small text-danger">
                                    <?php echo e($documentSignature->rejected_at->format('d M Y, H:i')); ?>

                                </div>
                                <?php if($documentSignature->rejector): ?>
                                <div class="small text-muted">
                                    By: <?php echo e($documentSignature->rejector->name); ?>

                                </div>
                                <?php endif; ?>
                                <div class="small mt-2 p-2 bg-danger bg-opacity-10 rounded">
                                    <strong>Reason:</strong> <?php echo e($documentSignature->rejection_reason); ?>

                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Technical Details -->
            

            <!-- Canvas Signature Preview -->
            
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header <?php echo e($documentSignature->signature_status === 'rejected' ? 'bg-danger' : 'bg-primary'); ?> text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if($documentSignature->signature_status === 'rejected'): ?>
                            
                            <a href="<?php echo e(route('user.signature.approval.request')); ?>" class="btn btn-danger">
                                <i class="fas fa-redo me-2"></i> Submit New Request
                            </a>
                            <a href="<?php echo e(route('user.signature.my.signatures.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i> Back to List
                            </a>
                            <button class="btn btn-outline-info" onclick="showRejectionHelp()">
                                <i class="fas fa-question-circle me-2"></i> Need Help?
                            </button>
                        <?php else: ?>
                            
                            <?php if($documentSignature->approvalRequest->signed_document_path || $documentSignature->final_pdf_path && in_array($documentSignature->signature_status, ['verified'])): ?>
                                <a href="<?php echo e(route('user.signature.my.signatures.download', $documentSignature->id)); ?>"
                                   class="btn btn-success">
                                    <i class="fas fa-download me-2"></i> Download Signed Document
                                </a>
                            <?php endif; ?>

                            <?php if($documentSignature->qr_code_path && in_array($documentSignature->signature_status, ['verified'])): ?>
                                <a href="<?php echo e(route('user.signature.my.signatures.qr', $documentSignature->id)); ?>"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-qrcode me-2"></i> Download QR Code
                                </a>
                            <?php endif; ?>

                            <?php if($documentSignature->verification_url && in_array($documentSignature->signature_status, ['verified'])): ?>
                                <button class="btn btn-outline-info" onclick="copyVerificationLink()">
                                    <i class="fas fa-link me-2"></i> Copy Verification Link
                                </button>
                            <?php endif; ?>

                            <a href="<?php echo e(route('signature.verify.page')); ?>" class="btn btn-outline-warning" target="_blank">
                                <i class="fas fa-shield-alt me-2"></i> Verify Document
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rejection Details Card -->
            <?php if($documentSignature->signature_status === 'rejected'): ?>
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
                            <?php echo e($documentSignature->rejection_reason); ?>

                        </div>
                    </div>
                    <?php if($documentSignature->rejected_at): ?>
                    <div class="mb-3">
                        <strong>Rejected On:</strong><br>
                        <small><?php echo e($documentSignature->rejected_at->format('d F Y, H:i')); ?></small><br>
                        <small class="text-muted"><?php echo e($documentSignature->rejected_at->diffForHumans()); ?></small>
                    </div>
                    <?php endif; ?>
                    <?php if($documentSignature->rejector): ?>
                    <div class="mb-3">
                        <strong>Rejected By:</strong><br>
                        <small><?php echo e($documentSignature->rejector->name); ?></small><br>
                        <small class="text-muted"><?php echo e($documentSignature->rejector->email); ?></small>
                    </div>
                    <?php endif; ?>
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
            <?php endif; ?>

            <!-- QR Code Display (Hidden for Rejected) -->
            <?php if($documentSignature->qr_code_path && in_array($documentSignature->signature_status, ['verified'])): ?>
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Verification QR Code
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo e(Storage::url($documentSignature->qr_code_path)); ?>"
                         alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="small text-muted mb-0">
                        Scan this QR code to verify document authenticity
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Verification URL (Hidden for Rejected) -->
            <?php if($documentSignature->verification_url && in_array($documentSignature->signature_status, ['verified'])): ?>
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
                               value="<?php echo e($documentSignature->verification_url); ?>" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyVerificationLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Share this link to allow others to verify your document
                    </small>
                </div>
            </div>
            <?php endif; ?>

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
                            <span class="badge status-badge status-approval-<?php echo e(strtolower($documentSignature->approvalRequest->status)); ?>">
                                <?php echo e(str_replace('_', ' ', ucwords($documentSignature->approvalRequest->status, '_'))); ?>

                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Signature Status:</strong><br>
                            <span class="badge status-badge status-<?php echo e(strtolower($documentSignature->signature_status)); ?>">
                                <?php echo e(ucfirst($documentSignature->signature_status)); ?>

                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Certificate Status:</strong><br>
                            <?php if($documentSignature->digitalSignature && $documentSignature->digitalSignature->isValid()): ?>
                                <span class="badge bg-success">Valid</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Invalid/Expired</span>
                            <?php endif; ?>
                        </li>
                        
                        <li class="mb-2">
                            <strong>Progress:</strong><br>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: <?php echo e($documentSignature->approvalRequest->workflow_progress); ?>%"
                                     aria-valuenow="<?php echo e($documentSignature->approvalRequest->workflow_progress); ?>"
                                     aria-valuemin="0" aria-valuemax="100">
                                    <?php echo e($documentSignature->approvalRequest->workflow_progress); ?>%
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Document paths for preview
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

    if(type === 'signed' && !<?php echo e(in_array($documentSignature->signature_status, ['verified']) ? 'true' : 'false'); ?>) {
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/user/my-signature/details.blade.php ENDPATH**/ ?>