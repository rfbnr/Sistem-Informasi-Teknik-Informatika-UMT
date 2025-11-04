<?php $__env->startSection('title', 'Approval Requests Management'); ?>

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
                    <i class="fas fa-clipboard-check me-3"></i>
                    Approval Requests Management
                </h1>
                <p class="mb-0 opacity-75">Review and manage document approval requests</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="btn-group">
                    
                    <a href="<?php echo e(route('admin.signature.approval.export')); ?>" class="btn btn-success">
                        <i class="fas fa-download me-1"></i> Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-warning"><?php echo e($statistics['pending'] ?? 0); ?></div>
                <div class="text-muted"><i class="fas fa-clock me-1"></i> Pending Approval</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-info"><?php echo e($statistics['approved'] ?? 0); ?></div>
                <div class="text-muted"><i class="fas fa-check me-1"></i> Approved</div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success"><?php echo e($statistics['completed'] ?? 0); ?></div>
                <div class="text-muted"><i class="fas fa-signature me-1"></i> Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-danger"><?php echo e($statistics['rejected'] ?? 0); ?></div>
                <div class="text-muted"><i class="fas fa-times me-1"></i> Rejected</div>
            </div>
        </div>
    </div>

    <!-- Overdue Alert -->
    <?php if(isset($statistics['overdue']) && $statistics['overdue'] > 0): ?>
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attention!</strong> You have <?php echo e($statistics['overdue']); ?> overdue approval request(s) that need immediate attention.
        </div>
    <?php endif; ?>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.signature.approval.index')); ?>" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search"
                               placeholder="Search by document name, nomor, or student name..."
                               value="<?php echo e(request('search')); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                        
                        <option value="sign_approved" <?php echo e(request('status') == 'sign_approved' ? 'selected' : ''); ?>>Sign Approved</option>
                        <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo e(route('admin.signature.approval.index')); ?>" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Approval Requests Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Approval Requests (<?php echo e($approvalRequests->total()); ?>)
            </h5>
        </div>
        <div class="card-body">
            <?php if($approvalRequests->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                
                                <th>No</th>
                                <th>Document</th>
                                <th>Type</th>
                                <th>Submitted By</th>
                                <th>Date</th>
                                
                                <th>Status</th>
                                <th width="180">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $approvalRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr id="request-row-<?php echo e($request->id); ?>">
                                
                                <td>
                                    <?php echo e($loop->iteration + ($approvalRequests->currentPage() - 1) * $approvalRequests->perPage()); ?>

                                </td>
                                <td>
                                    <div>
                                        <a href="<?php echo e(route('admin.signature.approval.show', $request->id)); ?>"
                                           class="text-decoration-none">
                                            <strong><?php echo e($request->document_name); ?></strong>
                                        </a>
                                        <?php if($request->notes): ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-sticky-note me-1"></i>
                                                <?php echo e(Str::limit($request->notes, 50)); ?>

                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php echo e(ucfirst($request->document_type ?? 'N/A')); ?>

                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php echo e($request->user->name ?? 'Unknown'); ?>

                                        <?php if($request->user && $request->user->nim): ?>
                                            <br><small class="text-muted"><?php echo e($request->user->nim); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php echo e($request->created_at->format('d M Y')); ?>

                                        <br><small class="text-muted"><?php echo e($request->created_at->diffForHumans()); ?></small>
                                    </div>
                                    <?php if($request->deadline): ?>
                                        <div class="mt-1">
                                            <small class="badge bg-info">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Due: <?php echo e(\Carbon\Carbon::parse($request->deadline)->format('d M Y')); ?>

                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <span class="status-badge status-<?php echo e(str_replace('_', '-', strtolower($request->status))); ?>">
                                        <?php echo e(str_replace('_', ' ', ucfirst($request->status))); ?>

                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- View Document -->
                                        <button class="btn btn-outline-info"
                                                onclick="viewDocument(<?php echo e($request->id); ?>, '<?php echo e(asset('storage/' . $request->document_path)); ?>')"
                                                title="View Document">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <!-- Download Original -->
                                        

                                        
                                        <a href="<?php echo e(route('admin.signature.approval.show', $request->id)); ?>"
                                           class="btn btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-info-circle"></i>
                                        </a>

                                        <?php if($request->status === 'pending'): ?>
                                            <!-- Approve Button -->
                                            <button class="btn btn-outline-success"
                                                    onclick="showApproveModal(<?php echo e($request->id); ?>, '<?php echo e($request->document_name); ?>', '<?php echo e($request->document_type); ?>')"
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>

                                            <!-- Reject Button -->
                                            <button class="btn btn-outline-danger"
                                                    onclick="showRejectModal(<?php echo e($request->id); ?>, '<?php echo e($request->document_name); ?>')"
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if($request->status === 'user_signed' && $request->documentSignature): ?>
                                            <!-- Approve Signature Button -->
                                            <button class="btn btn-outline-primary"
                                                    onclick="showApproveSignatureModal(<?php echo e($request->id); ?>, '<?php echo e($request->document_name); ?>')"
                                                    title="Approve Signature">
                                                <i class="fas fa-stamp"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    <?php echo e($approvalRequests->withQueryString()->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Approval Requests Found</h5>
                    <p class="text-muted">
                        <?php if(request()->has('search') || request()->has('status') || request()->has('priority')): ?>
                            Try adjusting your filters or search query.
                        <?php else: ?>
                            New approval requests from students will appear here.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include Modals -->
<?php echo $__env->make('digital-signature.admin.approval-requests.partials.view-document-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('digital-signature.admin.approval-requests.partials.approve-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('digital-signature.admin.approval-requests.partials.reject-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-double me-2"></i>
                    Bulk Approve Requests
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Select approval requests from the list below and click "Approve Selected" to approve multiple requests at once.
                </div>
                <div id="selectedRequests" class="mb-3">
                    <strong>0</strong> request(s) selected
                </div>
                <div class="mb-3">
                    <label for="bulk_notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="bulk_notes" rows="3"
                              placeholder="Add notes for all selected approvals..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="performBulkApprove()">
                    <i class="fas fa-check-double me-1"></i> Approve Selected
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Toggle Select All Checkboxes
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.request-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedCount();
}

// Update Selected Count
function updateSelectedCount() {
    const selected = document.querySelectorAll('.request-checkbox:checked');
    const container = document.getElementById('selectedRequests');
    if (container) {
        container.innerHTML = `<strong>${selected.length}</strong> request(s) selected`;
    }
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

// Show Approve Modal
function showApproveModal(id, documentName, documentType) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    document.getElementById('approveRequestId').value = id;
    document.getElementById('approveDocumentName').textContent = documentName;
    document.getElementById('approveDocumentType').textContent = documentType;
    modal.show();
}

// Show Reject Modal
function showRejectModal(id, documentName) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    document.getElementById('rejectRequestId').value = id;
    document.getElementById('rejectDocumentName').textContent = documentName;
    document.getElementById('rejection_reason').value = '';
    modal.show();
}

// Show Approve Signature Modal
function showApproveSignatureModal(id, documentName) {
    const modal = new bootstrap.Modal(document.getElementById('approveSignatureModal'));
    document.getElementById('approveSignatureRequestId').value = id;
    document.getElementById('approveSignatureDocumentName').textContent = documentName;
    modal.show();
}

// Perform Approve Signature
// function performApproveSignature() {
//     const requestId = document.getElementById('approveSignatureRequestId').value;
//     const notes = document.getElementById('approve_signature_notes').value;

//     fetch(`/admin/signature/approval-requests/${requestId}/approve-signature`, {
//         method: 'POST',
//         headers: {
//             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
//             'Content-Type': 'application/json'
//         },
//         body: JSON.stringify({ approval_notes: notes })
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success || !data.error) {
//             showAlert('success', 'Signature approved successfully!');
//             setTimeout(() => location.reload(), 1500);
//         } else {
//             showAlert('danger', data.message || 'Failed to approve signature');
//         }
//         bootstrap.Modal.getInstance(document.getElementById('approveSignatureModal')).hide();
//     })
//     .catch(error => {
//         showAlert('danger', 'An error occurred while approving the signature');
//         console.error('Error:', error);
//     });
// }

// Perform Bulk Approve
function performBulkApprove() {
    const selected = Array.from(document.querySelectorAll('.request-checkbox:checked'))
        .map(cb => cb.value);

    if (selected.length === 0) {
        showAlert('warning', 'Please select at least one request to approve');
        return;
    }

    const notes = document.getElementById('bulk_notes').value;

    fetch('/admin/signature/approval-requests/bulk-approve', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            approval_request_ids: selected,
            bulk_notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', data.message || 'Bulk approval failed');
        }
        bootstrap.Modal.getInstance(document.getElementById('bulkApproveModal')).hide();
    })
    .catch(error => {
        showAlert('danger', 'An error occurred during bulk approval');
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/approval-requests/index.blade.php ENDPATH**/ ?>