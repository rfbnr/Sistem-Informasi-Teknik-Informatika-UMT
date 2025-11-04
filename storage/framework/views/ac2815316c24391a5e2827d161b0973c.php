<?php $__env->startSection('title', 'Digital Signature Keys Management'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('digital-signature.admin.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.key-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.key-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.key-card.status-active {
    border-left-color: #1cc88a;
}

.key-card.status-expiring {
    border-left-color: #f6c23e;
}

.key-card.status-expired {
    border-left-color: #e74a3b;
}

.key-card.status-revoked {
    border-left-color: #858796;
}

.filter-badge {
    cursor: pointer;
    transition: all 0.2s;
}

.filter-badge:hover {
    transform: scale(1.05);
}

.filter-badge.active {
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.3);
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
                    <i class="fas fa-key me-3"></i>
                    Digital Signature Keys Management
                </h1>
                <p class="mb-0 opacity-75">Kelola dan monitor semua digital signature keys</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?php echo e(route('admin.signature.dashboard')); ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-primary"><?php echo e($stats['total']); ?></div>
                <div class="text-muted small">Total Keys</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card clickable" onclick="filterByStatus('active')">
                <div class="stats-number text-success"><?php echo e($stats['active']); ?></div>
                <div class="text-muted small">Active</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card clickable" onclick="filterByExpiry('expiring_soon')">
                <div class="stats-number text-warning"><?php echo e($stats['expiring_soon']); ?></div>
                <div class="text-muted small">Expiring Soon</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card clickable" onclick="filterByExpiry('expired')">
                <div class="stats-number text-danger"><?php echo e($stats['expired']); ?></div>
                <div class="text-muted small">Expired</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-secondary"><?php echo e($stats['revoked']); ?></div>
                <div class="text-muted small">Revoked</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="stats-card">
                <div class="stats-number text-info"><?php echo e($stats['urgent_expiry']); ?></div>
                <div class="text-muted small">Urgent (< 7d)</div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.signature.keys.index')); ?>" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo e(request('status') == 'all' ? 'selected' : ''); ?>>Semua Status</option>
                            <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                            <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Expired</option>
                            <option value="revoked" <?php echo e(request('status') == 'revoked' ? 'selected' : ''); ?>>Revoked</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Expiry</label>
                        <select name="expiry" class="form-select" onchange="this.form.submit()">
                            <option value="" <?php echo e(!request('expiry') ? 'selected' : ''); ?>>Semua</option>
                            <option value="valid" <?php echo e(request('expiry') == 'valid' ? 'selected' : ''); ?>>Valid</option>
                            <option value="expiring_soon" <?php echo e(request('expiry') == 'expiring_soon' ? 'selected' : ''); ?>>Expiring Soon (30d)</option>
                            <option value="expired" <?php echo e(request('expiry') == 'expired' ? 'selected' : ''); ?>>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Search Signature ID</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari signature ID..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Keys List -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Digital Signature Keys (<?php echo e($keys->total()); ?>)
                </h5>
                <small>Showing <?php echo e($keys->firstItem() ?? 0); ?> - <?php echo e($keys->lastItem() ?? 0); ?> of <?php echo e($keys->total()); ?></small>
            </div>
        </div>
        <div class="card-body p-4">
            <?php if($keys->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Signature ID</th>
                                <th>Algorithm</th>
                                <th>Key Length</th>
                                <th>Status</th>
                                <th>Valid From</th>
                                <th>Valid Until</th>
                                <th>Days Left</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $keys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $daysLeft = (int) now()->diffInDays($key->valid_until, false);

                                $statusClass = 'status-active';

                                if ($key->status == 'revoked') {
                                    $statusClass = 'status-revoked';
                                } elseif ($daysLeft < 0) {
                                    $statusClass = 'status-expired';
                                } elseif ($daysLeft <= 30) {
                                    $statusClass = 'status-expiring';
                                }
                            ?>
                            <tr class="key-card <?php echo e($statusClass); ?>">
                                <td>
                                    <strong class="text-primary"><?php echo e($key->signature_id); ?></strong>
                                    <?php if($key->documentSignature): ?>
                                        <br><small class="text-muted">
                                            <i class="fas fa-file-alt"></i>
                                            <?php echo e(Str::limit($key->documentSignature->approvalRequest->document_name ?? 'N/A', 30)); ?>

                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($key->algorithm); ?></td>
                                <td><?php echo e($key->key_length); ?> bit</td>
                                <td>
                                    <?php if($key->status == 'active'): ?>
                                        <?php if($daysLeft < 0): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-triangle"></i> Expired
                                            </span>
                                        <?php elseif($daysLeft <= 7): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-clock"></i> Urgent Expiry
                                            </span>
                                        <?php elseif($daysLeft <= 30): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-circle"></i> Expiring Soon
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Active
                                            </span>
                                        <?php endif; ?>
                                    <?php elseif($key->status == 'revoked'): ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-ban"></i> Revoked
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><?php echo e(ucfirst($key->status)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo e($key->valid_from->format('d M Y')); ?></small>
                                </td>
                                <td>
                                    <small><?php echo e($key->valid_until->format('d M Y')); ?></small>
                                </td>
                                <td>
                                    <?php if($daysLeft < 0): ?>
                                        <span class="text-danger"><strong><?php echo e(abs($daysLeft)); ?> days ago</strong></span>
                                    <?php elseif($daysLeft <= 7): ?>
                                        <span class="text-danger"><strong><?php echo e($daysLeft); ?> days!</strong></span>
                                    <?php elseif($daysLeft <= 30): ?>
                                        <span class="text-warning"><strong><?php echo e($daysLeft); ?> days</strong></span>
                                    <?php else: ?>
                                        <span class="text-success"><?php echo e($daysLeft); ?> days</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo e(route('admin.signature.keys.show', $key->id)); ?>"
                                       class="btn btn-sm btn-primary"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3">
                    <?php echo e($keys->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-key fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Data</h5>
                    <p class="text-muted">Tidak ada digital signature keys yang ditemukan dengan filter ini</p>
                    <a href="<?php echo e(route('admin.signature.keys.index')); ?>" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Reset Filter
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function filterByStatus(status) {
    const form = document.getElementById('filterForm');
    const statusSelect = form.querySelector('select[name="status"]');
    statusSelect.value = status;
    form.submit();
}

function filterByExpiry(expiry) {
    const form = document.getElementById('filterForm');
    const expirySelect = form.querySelector('select[name="expiry"]');
    expirySelect.value = expiry;
    form.submit();
}

// Auto-submit on Enter key in search
document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('filterForm').submit();
    }
});

.stats-card.clickable {
    cursor: pointer;
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/keys/index.blade.php ENDPATH**/ ?>