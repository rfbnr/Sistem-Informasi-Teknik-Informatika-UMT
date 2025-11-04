<?php $__env->startSection('title', 'Verification Logs - Activity Logs'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('digital-signature.admin.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Timeline Styles */
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e9ecef 0%, #dee2e6 50%, #e9ecef 100%);
}

.timeline-item {
    position: relative;
    padding-left: 70px;
    margin-bottom: 25px;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.timeline-icon {
    position: absolute;
    left: 16px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    z-index: 1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-card {
    background: white;
    border-radius: 8px;
    padding: 15px 20px;
    border-left: 3px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.timeline-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.timeline-card.valid {
    border-left-color: #28a745;
}

.timeline-card.invalid {
    border-left-color: #dc3545;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.timeline-title {
    font-weight: 600;
    font-size: 14px;
    margin: 0;
    color: #2c3e50;
}

.timeline-time {
    font-size: 12px;
    color: #6c757d;
    white-space: nowrap;
}

.timeline-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 10px;
    font-size: 12px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
}

.meta-item i {
    width: 14px;
    text-align: center;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.stats-card.success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stats-card.danger {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
}

.stats-card.warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stats-card .stats-number {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 5px;
}

.stats-card .stats-label {
    font-size: 14px;
    opacity: 0.9;
}

.filter-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}

.btn-filter {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    color: white;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 12px 24px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: #667eea;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}

.badge-device {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.badge-device.desktop {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-device.mobile {
    background: #f3e5f5;
    color: #7b1fa2;
}

.badge-device.tablet {
    background: #e8f5e9;
    color: #388e3c;
}

.badge-method {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.badge-method.token {
    background: #e8eaf6;
    color: #3f51b5;
}

.badge-method.url {
    background: #e0f2f1;
    color: #00897b;
}

.badge-method.qr {
    background: #fff3e0;
    color: #f57c00;
}

.badge-method.id {
    background: #fce4ec;
    color: #c2185b;
}

.badge-anonymous {
    background: #ffebee;
    color: #c62828;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-state h5 {
    color: #6c757d;
    margin-bottom: 10px;
}

.empty-state p {
    color: #adb5bd;
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
                    <i class="fas fa-history me-3"></i>
                    Activity Logs
                </h1>
                <p class="mb-0 opacity-75">Track signature verification attempts and results</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-light" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <button class="btn btn-warning" onclick="exportLogs()">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-number"><?php echo e($stats['total_today']); ?></div>
                <div class="stats-label">
                    <i class="fas fa-shield-alt me-1"></i> Total Verifications Today
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card success">
                <div class="stats-number"><?php echo e($stats['successful_today']); ?></div>
                <div class="stats-label">
                    <i class="fas fa-check-circle me-1"></i> Valid Signatures
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card danger">
                <div class="stats-number"><?php echo e($stats['failed_today']); ?></div>
                <div class="stats-label">
                    <i class="fas fa-times-circle me-1"></i> Invalid Signatures
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card warning">
                <div class="stats-number"><?php echo e($stats['success_rate']); ?>%</div>
                <div class="stats-label">
                    <i class="fas fa-percentage me-1"></i> Validation Rate
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="<?php echo e(route('admin.signature.logs.audit')); ?>">
                <i class="fas fa-clipboard-list me-2"></i>
                Audit Logs
                <span class="badge bg-light text-dark ms-2"><?php echo e($auditCount); ?></span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" href="<?php echo e(route('admin.signature.logs.verification')); ?>">
                <i class="fas fa-shield-alt me-2"></i>
                Verification Logs
                <span class="badge bg-light text-dark ms-2"><?php echo e($verificationCount); ?></span>
            </a>
        </li>
    </ul>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="<?php echo e(route('admin.signature.logs.verification')); ?>" id="filterForm">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-calendar me-1"></i> Date Range
                    </label>
                    <select name="range" class="form-select form-select-sm" onchange="toggleCustomDate()">
                        <option value="today" <?php echo e($range == 'today' ? 'selected' : ''); ?>>Today</option>
                        <option value="7days" <?php echo e($range == '7days' ? 'selected' : ''); ?>>Last 7 Days</option>
                        <option value="30days" <?php echo e($range == '30days' ? 'selected' : ''); ?>>Last 30 Days</option>
                        <option value="custom" <?php echo e($range == 'custom' ? 'selected' : ''); ?>>Custom Range</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6" id="customDateFields" style="display: <?php echo e($range == 'custom' ? 'block' : 'none'); ?>;">
                    <label class="form-label small text-muted mb-1">Custom Date Range</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>">
                        <span class="input-group-text">to</span>
                        <input type="date" name="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>">
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-fingerprint me-1"></i> Method
                    </label>
                    <select name="method" class="form-select form-select-sm">
                        <option value="">All Methods</option>
                        <?php $__currentLoopData = $methodTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php echo e($method == $value ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-check-circle me-1"></i> Status
                    </label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="valid" <?php echo e($status == 'valid' ? 'selected' : ''); ?>>Valid</option>
                        <option value="invalid" <?php echo e($status == 'invalid' ? 'selected' : ''); ?>>Invalid</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted mb-1">
                        <i class="fas fa-user me-1"></i> User Type
                    </label>
                    <select name="user_type" class="form-select form-select-sm">
                        <option value="">All Users</option>
                        <option value="authenticated" <?php echo e($userType == 'authenticated' ? 'selected' : ''); ?>>Authenticated</option>
                        <option value="anonymous" <?php echo e($userType == 'anonymous' ? 'selected' : ''); ?>>Anonymous</option>
                    </select>
                </div>

                <div class="col-lg-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-filter btn-sm">
                            <i class="fas fa-filter me-1"></i> Apply Filters
                        </button>
                        <a href="<?php echo e(route('admin.signature.logs.verification')); ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Timeline -->
    <?php if($logs->count() > 0): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <ul class="timeline">
                    <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="timeline-item">
                            <!-- Timeline Icon -->
                            <div class="timeline-icon" style="background: <?php echo e($log->is_valid ? '#28a745' : '#dc3545'); ?>; color: white;">
                                <i class="fas fa-<?php echo e($log->is_valid ? 'shield-alt' : 'exclamation-triangle'); ?>"></i>
                            </div>

                            <!-- Timeline Card -->
                            <div class="timeline-card <?php echo e($log->is_valid ? 'valid' : 'invalid'); ?>">
                                <div class="timeline-header">
                                    <div>
                                        <h6 class="timeline-title">
                                            Signature Verification <?php echo e($log->is_valid ? 'Successful' : 'Failed'); ?>

                                        </h6>
                                        <p class="mb-0 small">
                                            <span class="badge bg-<?php echo e($log->is_valid ? 'success' : 'danger'); ?>">
                                                <?php echo e($log->result_label); ?>

                                            </span>
                                        </p>
                                    </div>
                                    <div class="timeline-time">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo e($log->verified_at->format('H:i:s')); ?>

                                        <br>
                                        <small class="text-muted"><?php echo e($log->verified_at->format('d M Y')); ?></small>
                                    </div>
                                </div>

                                <!-- Metadata -->
                                <div class="timeline-meta">
                                    <?php if($log->user): ?>
                                        <span class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <strong><?php echo e($log->user->name); ?></strong>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-anonymous">
                                            <i class="fas fa-user-secret"></i> Anonymous
                                        </span>
                                    <?php endif; ?>

                                    <span class="meta-item">
                                        <span class="badge-method <?php echo e(strtolower($log->method_label)); ?>">
                                            <i class="fas fa-<?php echo e($log->method_label == 'Token' ? 'key' :
                                                ($log->method_label == 'URL' ? 'link' :
                                                ($log->method_label == 'QR Code' ? 'qrcode' : 'fingerprint'))); ?>"></i>
                                            <?php echo e($log->method_label); ?>

                                        </span>
                                    </span>

                                    <?php if($log->device_type): ?>
                                        <span class="meta-item">
                                            <span class="badge-device <?php echo e($log->device_type); ?>">
                                                <i class="fas fa-<?php echo e($log->device_type == 'mobile' ? 'mobile-alt' : ($log->device_type == 'tablet' ? 'tablet-alt' : 'desktop')); ?>"></i>
                                                <?php echo e(ucfirst($log->device_type)); ?>

                                            </span>
                                        </span>
                                    <?php endif; ?>

                                    <?php if($log->browser_name): ?>
                                        <span class="meta-item">
                                            <i class="fas fa-globe"></i>
                                            <?php echo e($log->browser_name); ?>

                                        </span>
                                    <?php endif; ?>

                                    <?php if($log->verification_duration_human): ?>
                                        <span class="meta-item">
                                            <i class="fas fa-stopwatch"></i>
                                            <?php echo e($log->verification_duration_human); ?>

                                        </span>
                                    <?php endif; ?>

                                    <span class="meta-item">
                                        <i class="fas fa-network-wired"></i>
                                        <?php echo e($log->ip_address); ?>

                                    </span>

                                    <?php if($log->documentSignature): ?>
                                        <span class="meta-item">
                                            <i class="fas fa-file-signature"></i>
                                            Doc #<?php echo e($log->documentSignature->id); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- View Details Button -->
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewLogDetails(<?php echo e($log->id); ?>, 'verification')">
                                        <i class="fas fa-info-circle me-1"></i> View Details
                                    </button>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($logs->links()); ?>

        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-shield-alt"></i>
                    <h5>No verification logs found</h5>
                    <p>There are no verification logs matching your current filters.</p>
                    <a href="<?php echo e(route('admin.signature.logs.verification')); ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-redo me-1"></i> Clear Filters
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Include Log Details Modal -->
<?php echo $__env->make('digital-signature.admin.logs.partials.log-details-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleCustomDate() {
    const range = document.querySelector('select[name="range"]').value;
    const customFields = document.getElementById('customDateFields');
    customFields.style.display = range === 'custom' ? 'block' : 'none';
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'verification');
    params.set('format', 'csv');
    window.location.href = `<?php echo e(route('admin.signature.logs.export')); ?>?${params.toString()}`;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/logs/verification.blade.php ENDPATH**/ ?>