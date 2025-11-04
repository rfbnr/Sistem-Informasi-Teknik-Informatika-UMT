<?php $__env->startSection('title', 'QR Code Analytics Report'); ?>

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
                    <i class="fas fa-qrcode me-3"></i>
                    QR Code Analytics Report
                </h1>
                <p class="mb-0 opacity-75">Detailed analytics for verification QR codes</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?php echo e(route('admin.signature.reports.index')); ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h2 text-primary"><?php echo e($analytics['total_codes']); ?></div>
                    <div class="text-muted">Total QR Codes</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h2 text-success"><?php echo e(number_format($analytics['total_scans'])); ?></div>
                    <div class="text-muted">Total Scans</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h2 text-info"><?php echo e($analytics['avg_scans_per_code']); ?></div>
                    <div class="text-muted">Avg. Scans/Code</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h2 text-warning"><?php echo e($analytics['never_scanned']); ?></div>
                    <div class="text-muted">Never Scanned</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Scanned QR Codes -->
    <?php if($analytics['most_scanned']->count() > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Top 5 Most Scanned QR Codes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Short Code</th>
                                    <th>Document</th>
                                    <th>Created</th>
                                    <th class="text-center">Scans</th>
                                    <th>Last Accessed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $analytics['most_scanned']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $qr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <?php if($index < 3): ?>
                                            <span class="badge bg-warning"><?php echo e($index + 1); ?></span>
                                        <?php else: ?>
                                            <?php echo e($index + 1); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td><code><?php echo e($qr->short_code); ?></code></td>
                                    <td>
                                        <?php if($qr->documentSignature && $qr->documentSignature->approvalRequest): ?>
                                            <?php echo e(Str::limit($qr->documentSignature->approvalRequest->document_name, 40)); ?>

                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo e($qr->created_at->format('d M Y')); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6"><?php echo e($qr->access_count); ?></span>
                                    </td>
                                    <td>
                                        <?php if($qr->last_accessed_at): ?>
                                            <small class="text-muted">
                                                <?php echo e($qr->last_accessed_at->diffForHumans()); ?>

                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($qr->expires_at > now()): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Expired</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- All QR Codes Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                All Verification Codes (<?php echo e($qrCodes->total()); ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if($qrCodes->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="qrCodesTable">
                        <thead>
                            <tr>
                                <th>Short Code</th>
                                <th>Document</th>
                                <th>User</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th class="text-center">Scans</th>
                                <th>Last Accessed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $qrCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <code class="bg-light p-1 rounded"><?php echo e($qr->short_code); ?></code>
                                </td>
                                <td>
                                    <?php if($qr->documentSignature && $qr->documentSignature->approvalRequest): ?>
                                        <div><?php echo e(Str::limit($qr->documentSignature->approvalRequest->document_name, 30)); ?></div>
                                        <small class="text-muted">
                                            <?php echo e($qr->documentSignature->approvalRequest->document_type ?? 'General'); ?>

                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($qr->documentSignature && $qr->documentSignature->approvalRequest && $qr->documentSignature->approvalRequest->user): ?>
                                        <div><?php echo e($qr->documentSignature->approvalRequest->user->name); ?></div>
                                        <small class="text-muted"><?php echo e($qr->documentSignature->approvalRequest->user->NIM); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo e($qr->created_at->format('d M Y H:i')); ?></small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo e($qr->expires_at->format('d M Y')); ?>

                                        <?php if($qr->expires_at < now()): ?>
                                            <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if($qr->access_count > 0): ?>
                                        <span class="badge bg-primary"><?php echo e($qr->access_count); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($qr->last_accessed_at): ?>
                                        <small class="text-muted">
                                            <?php echo e($qr->last_accessed_at->diffForHumans()); ?>

                                            <br>
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                <?php echo e($qr->last_accessed_ip); ?>

                                            </span>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($qr->expires_at > now()): ?>
                                        <span class="badge bg-success">Active</span>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo e($qr->expires_at->diffInDays(now())); ?> days left
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Expired</span>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo e(now()->diffInDays($qr->expires_at)); ?> days ago
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info"
                                                onclick="viewQRDetails('<?php echo e($qr->short_code); ?>', <?php echo e($qr->id); ?>)"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="<?php echo e(route('signature.verify.page', $qr->short_code)); ?>"
                                           class="btn btn-outline-primary"
                                           target="_blank"
                                           title="Test Verification">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing <?php echo e($qrCodes->firstItem()); ?> to <?php echo e($qrCodes->lastItem()); ?>

                        of <?php echo e($qrCodes->total()); ?> entries
                    </div>
                    <div>
                        <?php echo e($qrCodes->links()); ?>

                    </div>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-qrcode fa-4x mb-3"></i>
                    <h5>No QR Codes Found</h5>
                    <p>No verification codes were generated in the selected period</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- QR Details Modal -->
<div class="modal fade" id="qrDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>
                    QR Code Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="qrDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
.badge.fs-6 {
    font-size: 1rem !important;
    padding: 0.5rem 0.75rem;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#qrCodesTable').DataTable({
        pageLength: 25,
        order: [[5, 'desc']], // Order by scans
        columnDefs: [
            { orderable: false, targets: [8] } // Disable ordering for Actions column
        ],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
});

function viewQRDetails(shortCode, qrId) {
    const modal = new bootstrap.Modal(document.getElementById('qrDetailsModal'));
    const contentDiv = document.getElementById('qrDetailsContent');

    // Show modal with loading state
    modal.show();

    // Mock details (in production, fetch from API)
    contentDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted mb-3">QR Code Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">Short Code:</td>
                        <td><code>${shortCode}</code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Full URL:</td>
                        <td><small>${window.location.origin}/signature/verify/${shortCode}</small></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">QR ID:</td>
                        <td>${qrId}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6 text-center">
                <h6 class="text-muted mb-3">QR Code Preview</h6>
                <div class="p-3 bg-light rounded">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${window.location.origin}/signature/verify/${shortCode}" alt="QR Code">
                    <p class="mt-2 mb-0 small text-muted">QR Code: ${shortCode}</p>
                </div>
            </div>
        </div>
        <hr>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> This is a verification code that maps to an encrypted payload stored securely in the database.
        </div>
    `;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/reports/qr-codes.blade.php ENDPATH**/ ?>