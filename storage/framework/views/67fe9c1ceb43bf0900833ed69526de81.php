
<div class="sidebar-header text-center py-3">
    <h5 class="text-white mb-0">
        <i class="fas fa-user-shield me-2"></i>
        Kaprodi Panel
    </h5>
    <small class="text-white-50">DiSign | Informatika UMT</small>
</div>

<ul class="nav flex-column">
    <!-- Dashboard -->
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.signature.dashboard') ? 'active' : ''); ?>"
           href="<?php echo e(route('admin.signature.dashboard')); ?>">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard
        </a>
    </li>

    <!-- Digital Signature Keys Management -->
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.signature.keys.*') ? 'active' : ''); ?>"
           href="<?php echo e(route('admin.signature.keys.index')); ?>">
            <i class="fas fa-key me-2"></i>
            Digital Signature Keys
            <?php
                $expiringKeys = \App\Models\DigitalSignature::expiringSoon(7)->count();
            ?>
            <?php if($expiringKeys > 0): ?>
                <span class="badge bg-danger rounded-pill ms-auto"><?php echo e($expiringKeys); ?></span>
            <?php endif; ?>
        </a>
    </li>

    <!-- Document Management -->
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.signature.documents.*') ? 'active' : ''); ?>"
           href="<?php echo e(route('admin.signature.documents.index')); ?>">
            <i class="fas fa-file-signature me-2"></i>
            Document Signatures
            <?php if(isset($stats['pending_signatures']) && $stats['pending_signatures'] > 0): ?>
                <span class="badge bg-info rounded-pill ms-auto"><?php echo e($stats['pending_signatures']); ?></span>
            <?php endif; ?>
        </a>
    </li>

    <!-- Approval Requests -->
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.signature.approval.*') ? 'active' : ''); ?>"
           href="<?php echo e(route('admin.signature.approval.index')); ?>">
            <i class="fas fa-clipboard-check me-2"></i>
            Approval Requests
            <?php
                $pendingCount = \App\Models\ApprovalRequest::pendingApproval()->count();
            ?>
            <?php if($pendingCount > 0): ?>
                <span class="badge bg-warning rounded-pill ms-auto"><?php echo e($pendingCount); ?></span>
            <?php endif; ?>
        </a>
    </li>

    <!-- Divider -->
    <hr class="my-3 text-white-50">

    <!-- Reports & Analytics -->
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.signature.reports.*') ? 'active' : ''); ?>"
           href="<?php echo e(route('admin.signature.reports.index')); ?>">
            <i class="fas fa-chart-bar me-2"></i>
            Reports & Analytics
        </a>
    </li>

    <!-- Activity Logs -->
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.signature.logs.*') ? 'active' : ''); ?>"
           href="<?php echo e(route('admin.signature.logs.audit')); ?>">
            <i class="fas fa-history me-2"></i>
            Activity Logs
            <?php
                $failureCount = \App\Http\Controllers\DigitalSignature\LogsController::getRecentFailuresCount();
            ?>
            <?php if($failureCount > 0): ?>
                <span class="badge bg-danger rounded-pill ms-auto"><?php echo e($failureCount); ?></span>
            <?php endif; ?>
        </a>
    </li>

    <!-- Export Data -->
    

    <!-- System Settings -->
    

    <!-- Divider -->
    <hr class="my-3 text-white-50">

    <!-- Public Verification -->
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('signature.verify.page')); ?>" target="_blank">
            <i class="fas fa-external-link-alt me-2"></i>
            Public Verification
        </a>
    </li>

    <!-- Help & Documentation -->
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.signature.help') ? 'active' : ''); ?>"
           href="<?php echo e(route('admin.signature.help')); ?>">
            <i class="fas fa-question-circle me-2"></i>
            Help & Support
        </a>
    </li>

    
    
</ul>


<div class="text-center my-4">
    <img src="<?php echo e(asset('assets/logo.JPG')); ?>" alt="Logo" class="d-inline-block align-text-center img-fluid rounded mx-auto d-block" style="max-width: 150px;">
</div>

<!-- Quick Stats at Bottom -->

<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/partials/sidebar.blade.php ENDPATH**/ ?>