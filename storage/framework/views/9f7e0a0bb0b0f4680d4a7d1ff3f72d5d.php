<?php $__env->startSection('title', 'Digital Signature Dashboard'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('digital-signature.admin.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.stats-card.clickable {
    cursor: pointer;
    transition: all 0.3s ease;
}

.stats-card.clickable:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-primary"><?php echo e($stats['total_signatures'] ?? 0); ?></div>
                <div class="text-muted small">Total Signatures</div>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo e($stats['active_signatures'] ?? 0); ?> Active
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="stats-card clickable" onclick="window.location='<?php echo e(route('admin.signature.approval.index')); ?>'">
                <div class="stats-number text-warning"><?php echo e($stats['pending_approvals'] ?? 0); ?></div>
                <div class="text-muted small">Pending Approvals</div>
                <div class="mt-2">
                    <small class="text-primary">
                        <i class="fas fa-hand-pointer"></i>
                        Need Action
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-success"><?php echo e($stats['verified_signatures'] ?? 0); ?></div>
                <div class="text-muted small">Verified</div>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="fas fa-shield-alt"></i>
                        <?php echo e($verificationStats['verification_rate'] ?? 0); ?>%
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-danger"><?php echo e($stats['rejected_signatures'] ?? 0); ?></div>
                <div class="text-muted small">Rejected</div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-times-circle"></i>
                        Signatures
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-secondary"><?php echo e($stats['expired_signatures'] ?? 0); ?></div>
                <div class="text-muted small">Expired Keys</div>
                <div class="mt-2">
                    <small class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Renew
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Approvals - Quick Approve -->
        <div class="col-lg-8 mb-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check me-2"></i>
                            Pending Approvals
                        </h5>
                        <span class="badge bg-dark"><?php echo e($stats['pending_approvals'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($pendingApprovals && $pendingApprovals->count() > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php $__currentLoopData = $pendingApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <i class="fas fa-file-alt me-1 text-primary"></i>
                                            <?php echo e(Str::limit($approval->document_name, 40)); ?>

                                        </h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-file-signature me-1"></i>
                                            Type: <?php echo e(ucfirst(str_replace('_', ' ', $approval->document_type))); ?>

                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo e($approval->user->name ?? 'Unknown'); ?> (<?php echo e($approval->user->NIM ?? 'N/A'); ?>)
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo e($approval->created_at->diffForHumans()); ?>

                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <button class="btn btn-sm btn-success mb-1 w-100"
                                                onclick="showApproveModal(<?php echo e($approval->id); ?>, '<?php echo e($approval->document_name); ?>', '<?php echo e($approval->document_type); ?>')">
                                            <i class="fas fa-check me-1"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-info mb-1 w-100"
                                        onclick="viewDocument(<?php echo e($approval->id); ?>, '<?php echo e(asset('storage/' . $approval->document_path)); ?>')">
                                            <i class="fas fa-file-alt me-1"></i> View
                                        </button>
                                        <a href="<?php echo e(route('admin.signature.approval.show', $approval->id)); ?>"
                                           class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-eye me-1"></i> Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="<?php echo e(route('admin.signature.approval.index')); ?>" class="btn btn-warning">
                                <i class="fas fa-list me-1"></i> View All Pending (<?php echo e($stats['pending_approvals']); ?>)
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">All Caught Up!</h5>
                            <p class="text-muted">No pending approval requests at the moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Digital Signature Keys Widget -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            Signature Keys
                        </h5>
                        <a href="<?php echo e(route('admin.signature.keys.index')); ?>" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Key Stats -->
                    <div class="row text-center mb-3">
                        <div class="col-6 mb-2">
                            <div class="h5 text-success mb-0"><?php echo e($keyStats['active_keys']); ?></div>
                            <small class="text-muted">Active Keys</small>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="h5 text-secondary mb-0"><?php echo e($keyStats['revoked_keys']); ?></div>
                            <small class="text-muted">Revoked</small>
                        </div>
                    </div>

                    <!-- Alert Section -->
                    <?php if($keyStats['urgent_expiry'] > 0): ?>
                    <div class="alert alert-danger mb-3 py-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong><?php echo e($keyStats['urgent_expiry']); ?></strong> keys expire in < 7 days!
                    </div>
                    <?php endif; ?>

                    <?php if($keyStats['expiring_soon'] > 0 && $keyStats['urgent_expiry'] == 0): ?>
                    <div class="alert alert-warning mb-3 py-2">
                        <i class="fas fa-clock me-1"></i>
                        <strong><?php echo e($keyStats['expiring_soon']); ?></strong> keys expiring soon (30d)
                    </div>
                    <?php endif; ?>

                    <!-- Expiring Keys List -->
                    <?php if($expiringKeys->count() > 0): ?>
                    <div class="mb-2">
                        <strong class="small">Expiring Soon:</strong>
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                        <?php $__currentLoopData = $expiringKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $daysLeft = (int) now()->diffInDays($key->valid_until, false);
                        ?>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="small">
                                        <code class="text-primary"><?php echo e(Str::limit($key->signature_id, 12)); ?></code>
                                    </div>
                                    <?php if($key->documentSignature && $key->documentSignature->approvalRequest): ?>
                                    <div class="small text-muted">
                                        <?php echo e(Str::limit($key->documentSignature->approvalRequest->document_name, 25)); ?>

                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ms-2">
                                    <?php if($daysLeft <= 7): ?>
                                        <span class="badge bg-danger small"><?php echo e($daysLeft); ?> d</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning small"><?php echo e($daysLeft); ?> d</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="small text-muted mb-0">All keys are healthy!</p>
                    </div>
                    <?php endif; ?>

                    <!-- Action Button -->
                    <div class="mt-3 d-grid">
                        <a href="<?php echo e(route('admin.signature.keys.index')); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-key me-1"></i> Manage All Keys
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <!-- Need Verification - Quick Verify -->
        
    </div>

    <!-- Second Row: Recent Signatures & Quick Actions -->
    

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
                            <div class="h4 text-info"><?php echo e($verificationStats['total_signatures'] ?? 0); ?></div>
                            <div class="text-muted">Total Signatures</div>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center mb-3">
                            <div class="h4 text-success"><?php echo e($verificationStats['verified_signatures'] ?? 0); ?></div>
                            <div class="text-muted">Verified</div>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center mb-3">
                            <div class="h4 text-primary"><?php echo e($verificationStats['verification_rate'] ?? 0); ?>%</div>
                            <div class="text-muted">Success Rate</div>
                        </div>
                        <div class="col-lg-3 col-md-6 text-center mb-3">
                            <div class="h4 text-warning"><?php echo e($verificationStats['period_days'] ?? 30); ?></div>
                            <div class="text-muted">Days Period</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Approve Modal -->


<!-- Quick Verify Modal -->


<!-- Include Modals -->
<?php echo $__env->make('digital-signature.admin.partials.quick-preview-signed-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('digital-signature.admin.partials.reject-signed-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('digital-signature.admin.approval-requests.partials.view-document-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('digital-signature.admin.approval-requests.partials.approve-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>




<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let currentApprovalId = null;
let currentVerifyId = null;

function refreshDashboard() {
    location.reload();
}

// Show Approve Modal
function showApproveModal(id, documentName, documentType) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    document.getElementById('approveRequestId').value = id;
    document.getElementById('approveDocumentName').textContent = documentName;
    document.getElementById('approveDocumentType').textContent = documentType;
    modal.show();
}

// View Document in Modal
function viewDocument(id, documentUrl) {
    const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
    const iframe = document.getElementById('documentIframe');
    const downloadBtn = document.getElementById('downloadDocumentBtn');

    iframe.src = documentUrl;
    downloadBtn.href = documentUrl;

    modal.show();
}

// QUICK VERIFY
function quickVerify(docSigId, documentName) {
    currentVerifyId = docSigId;
    document.getElementById('verifyDocumentName').textContent = documentName;
    document.getElementById('verifyProgressDiv').classList.add('d-none');
    document.getElementById('verifyResultDiv').classList.add('d-none');
    document.getElementById('verifyResultDiv').innerHTML = '';
    document.getElementById('verifyBtn').disabled = false;

    const modal = new bootstrap.Modal(document.getElementById('quickVerifyModal'));
    modal.show();
}

function executeVerify() {
    const progressDiv = document.getElementById('verifyProgressDiv');
    const resultDiv = document.getElementById('verifyResultDiv');
    const verifyBtn = document.getElementById('verifyBtn');

    progressDiv.classList.remove('d-none');
    resultDiv.classList.add('d-none');
    verifyBtn.disabled = true;

    fetch(`/admin/signature/documents/${currentVerifyId}/verify`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        progressDiv.classList.add('d-none');
        resultDiv.classList.remove('d-none');

        if (data.success) {
            resultDiv.innerHTML = `
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>Verification Successful!</h6>
                    <p class="mb-0 small">The document signature has been verified and approved.</p>
                </div>
            `;
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('quickVerifyModal')).hide();
                refreshDashboard();
            }, 2000);
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-times-circle me-2"></i>Verification Failed</h6>
                    <p class="mb-0 small">${data.message || data.error || 'Unknown error occurred'}</p>
                </div>
            `;
            verifyBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Verify error:', error);
        progressDiv.classList.add('d-none');
        resultDiv.classList.remove('d-none');
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Network Error</h6>
                <p class="mb-0 small">Failed to verify signature. Please try again.</p>
            </div>
        `;
        verifyBtn.disabled = false;
    });
}

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

// Auto-refresh dashboard every 3 minutes
setInterval(function() {
    const lastRefresh = localStorage.getItem('dashboardLastRefresh');
    const now = Date.now();

    if (!lastRefresh || (now - lastRefresh) > 180000) { // 3 minutes
        refreshDashboard();
        localStorage.setItem('dashboardLastRefresh', now);
    }
}, 180000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/dashboard.blade.php ENDPATH**/ ?>