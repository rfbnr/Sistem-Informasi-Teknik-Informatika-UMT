<?php $__env->startSection('title', 'My Digital Signatures'); ?>

<?php $__env->startSection('content'); ?>
<!-- Section Header -->
<section id="header-section">
    <h1>My Digital Signatures</h1>
</section>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-signature me-3"></i>
                    My Digital Signatures
                </h1>
                <p class="mb-0 opacity-75">View and manage your signed documents</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="btn-group">
                    <a href="<?php echo e(route('user.signature.approval.request')); ?>" class="btn btn-warning">
                        <i class="fas fa-plus me-1"></i> New Request
                    </a>
                    <a href="<?php echo e(route('user.signature.approval.status')); ?>" class="btn btn-outline-warning">
                        <i class="fas fa-list me-1"></i> All Status
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-primary"><?php echo e($statistics['total'] ?? 0); ?></div>
                <div class="text-muted">Total</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success"><?php echo e($statistics['verified'] ?? 0); ?></div>
                <div class="text-muted">Verified</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-info"><?php echo e($statistics['pending'] ?? 0); ?></div>
                <div class="text-muted">Pending</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-warning"><?php echo e($statistics['this_month'] ?? 0); ?></div>
                <div class="text-muted">This Month</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('user.signature.my.signatures.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search documents..."
                           value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="signed" <?php echo e(request('status') == 'signed' ? 'selected' : ''); ?>>Signed</option>
                        <option value="verified" <?php echo e(request('status') == 'verified' ? 'selected' : ''); ?>>Verified</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="month">
                        <option value="">All Time</option>
                        <option value="current" <?php echo e(request('month') == 'current' ? 'selected' : ''); ?>>This Month</option>
                        <option value="last" <?php echo e(request('month') == 'last' ? 'selected' : ''); ?>>Last Month</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Signatures Grid -->
    <?php if($signatures->count() > 0): ?>
        <div class="row">
            <?php $__currentLoopData = $signatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $signature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header <?php echo e($signature->signature_status === 'verified' ? 'bg-success' :
                        ($signature->signature_status === 'signed' ? 'bg-info' :
                        ($signature->signature_status === 'rejected' ? 'bg-danger' : 'bg-warning'))); ?> text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-file-signature me-2"></i>
                                <?php echo e($signature->approvalRequest->document_name); ?>

                            </h6>
                            <span class="badge bg-light text-dark">
                                <?php echo e(ucfirst($signature->signature_status)); ?>

                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Document Number & Signature ID -->
                        <div class="mb-3">
                            <div class="row">
                                
                                <div class="col-6">
                                    <strong class="small">Signature ID:</strong><br>
                                    <code class="small"><?php echo e($signature->digitalSignature->signature_id ?? 'N/A'); ?></code>
                                </div>
                            </div>
                        </div>

                        <!-- Document Type & Priority -->
                        <?php if($signature->approvalRequest->document_type || $signature->approvalRequest->priority): ?>
                        <div class="mb-3">
                            <?php if($signature->approvalRequest->document_type): ?>
                                <span class="badge bg-secondary me-1">
                                    <i class="fas fa-file-alt me-1"></i><?php echo e($signature->approvalRequest->document_type); ?>

                                </span>
                            <?php endif; ?>
                            
                        </div>
                        <?php endif; ?>

                        <!-- Signer & Algorithm -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong class="small">Signed By:</strong><br>
                                <small class="text-muted">
                                    
                                    
                                    <?php if($signature->signer): ?>
                                        <?php echo e($signature->signer->name); ?><br>
                                        <span class="text-muted" style="font-size: 0.75rem;">NIDN: <?php echo e($signature->signer->NIDN ?? '-'); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Not signed yet</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="col-6">
                                <strong class="small">Algorithm:</strong><br>
                                <span class="badge bg-info">
                                    <?php echo e($signature->digitalSignature->algorithm ?? 'N/A'); ?>

                                </span>
                                <br>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <?php echo e($signature->digitalSignature->key_length ?? 'N/A'); ?> bits
                                </small>
                            </div>
                        </div>

                        <!-- Signed At & Certificate Validity -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong class="small">Signed At:</strong><br>
                                <small class="text-muted">
                                    <?php if($signature->signed_at): ?>
                                        <?php echo e($signature->signed_at->format('d M Y H:i')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">Not signed yet</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <div class="col-6">
                                <strong class="small">Certificate:</strong><br>
                                <?php if($signature->digitalSignature): ?>
                                    <?php if($signature->digitalSignature->isValid()): ?>
                                        <span class="badge bg-success small">
                                            <i class="fas fa-check-circle"></i> Valid
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger small">
                                            <i class="fas fa-times-circle"></i> Invalid
                                        </span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        Until <?php echo e($signature->digitalSignature->valid_until->format('d M Y')); ?>

                                    </small>
                                <?php else: ?>
                                    <span class="badge bg-secondary small">N/A</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Rejection Status -->
                        <?php if($signature->signature_status === 'rejected'): ?>
                        <div class="mb-3">
                            <div class="alert alert-danger mb-0 py-2">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong class="small">Signature Rejected</strong>
                                <small class="d-block mt-1" style="font-size: 0.75rem;">
                                    <strong>Reason:</strong> <?php echo e($signature->rejection_reason); ?>

                                </small>
                                <?php if($signature->rejected_at): ?>
                                <small class="d-block text-muted" style="font-size: 0.75rem;">
                                    on <?php echo e($signature->rejected_at->format('d M Y H:i')); ?>

                                    <?php if($signature->rejector): ?>
                                        by <?php echo e($signature->rejector->name); ?>

                                    <?php endif; ?>
                                </small>
                                <?php endif; ?>
                                <hr class="my-2">
                                <small class="d-block" style="font-size: 0.7rem;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Please submit a new request with the necessary corrections.
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Verification Status -->
                        <?php if($signature->verified_at): ?>
                        <div class="mb-3">
                            <div class="alert alert-success mb-0 py-2">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong class="small">Verified</strong>
                                <small class="d-block text-muted" style="font-size: 0.75rem;">
                                    on <?php echo e($signature->verified_at->format('d M Y H:i')); ?>

                                    <?php if($signature->verifier): ?>
                                        by <?php echo e($signature->verifier->name); ?>

                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Approval Request Status -->
                        <?php if($signature->approvalRequest->status !== 'sign_approved'): ?>
                        <div class="mb-3">
                            <div class="alert alert-info mb-0 py-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong class="small">Document Status:</strong>
                                <span class="badge <?php echo e($signature->approvalRequest->status_badge_class); ?> ms-1 text-black">
                                    <?php echo e($signature->approvalRequest->status_label); ?>

                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- QR Code Preview -->
                        <?php if($signature->qr_code_path && in_array($signature->signature_status, ['verified'])): ?>
                        <div class="text-center mb-3 p-3 bg-light rounded">
                            <img src="<?php echo e(Storage::url($signature->qr_code_path)); ?>" alt="QR Code" style="max-width: 120px;">
                            <div class="small text-muted mt-2">Scan to verify</div>
                        </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            
                            <?php if($signature->signature_status === 'rejected'): ?>
                                <a href="<?php echo e(route('user.signature.approval.request')); ?>"
                                   class="btn btn-sm btn-danger flex-fill">
                                    <i class="fas fa-redo"></i> Submit New Request
                                </a>
                                <a href="<?php echo e(route('user.signature.my.signatures.show', $signature->id)); ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(route('user.signature.my.signatures.show', $signature->id)); ?>"
                                   class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                                
                                <?php if($signature->signature_status === 'pending'): ?>
                                    <a href="<?php echo e(route('user.signature.sign.document', $signature->approvalRequest->id)); ?>"
                                       class="btn btn-sm btn-primary flex-fill">
                                        <i class="fas fa-signature"></i> Sign Now
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if(($signature->approvalRequest->signed_document_path || $signature->final_pdf_path) && $signature->signature_status === 'verified'): ?>
                                <a href="<?php echo e(route('user.signature.my.signatures.download', $signature->id)); ?>"
                                   class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php endif; ?>
                            <?php if($signature->qr_code_path && $signature->signature_status === 'verified'): ?>
                                <a href="<?php echo e(route('user.signature.my.signatures.qr', $signature->id)); ?>"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer text-muted small">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-clock me-1"></i>
                                Created <?php echo e($signature->created_at->diffForHumans()); ?>

                            </span>
                            
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Pagination -->
        
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($signatures->withQueryString()->links()); ?>

        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-signature fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Signatures Yet</h4>
                <p class="text-muted">You haven't signed any documents yet.</p>
                <a href="<?php echo e(route('user.signature.approval.request')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Submit Your First Document
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Auto-refresh if there are pending signatures
<?php if($statistics['pending'] > 0): ?>
    setInterval(function() {
        if (!document.hidden) {
            location.reload();
        }
    }, 60000); // Refresh every minute
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/user/my-signature/index.blade.php ENDPATH**/ ?>