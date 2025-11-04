<?php $__env->startSection('header'); ?>
    <?php echo $__env->make('emails.partials.header', [
        'title' => 'Permintaan Persetujuan Baru',
        'subtitle' => 'Terdapat dokumen yang memerlukan persetujuan Anda'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <p class="greeting">
        Yth. Ketua Program Studi,
    </p>

    
    <p>
        Sebuah permintaan persetujuan dokumen baru telah diajukan oleh <strong><?php echo e($approvalRequest->user->name); ?></strong> dan memerlukan tindakan Anda.
    </p>

    
    <div class="alert alert-info">
        <strong>⏰ Perhatian:</strong> Mohon segera review dan berikan persetujuan untuk mempercepat proses tanda tangan digital.
    </div>

    
    <?php echo $__env->make('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showRequester' => true,
        'showStatus' => true
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php if($approvalRequest->notes): ?>
    <div class="alert alert-warning">
        <h3 class="section-title">
            💬 Catatan dari Pemohon
        </h3>
        <p class="mb-0">
            "<?php echo e($approvalRequest->notes); ?>"
        </p>
    </div>
    <?php endif; ?>

    
    <div class="mt-30">
        <p class="text-center text-strong mb-15">
            Tindakan yang Diperlukan:
        </p>

        
        <?php echo $__env->make('emails.components.button', [
            'url' => route('admin.signature.approval.show', $approvalRequest->id),
            'text' => '📋 Review & Setujui Dokumen',
            'type' => 'primary',
            'block' => true
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        
        <p class="mt-15 mb-0 text-center text-muted text-small">
            Atau kunjungi
            <a href="<?php echo e(route('admin.signature.dashboard')); ?>" class="link-primary">
                Dashboard Kaprodi
            </a>
            untuk melihat semua permintaan
        </p>
    </div>

    
    <div class="divider"></div>

    
    <div class="section-card">
        <h4 class="section-subtitle">
            ℹ️ Informasi Penting
        </h4>
        <ul class="list-no-margin text-muted text-small">
            <li>Dokumen akan tersedia untuk ditandatangani setelah Anda menyetujui</li>
            <li>Mahasiswa akan menerima notifikasi otomatis setelah persetujuan</li>
            <li>Anda dapat melihat preview dokumen sebelum memberikan persetujuan</li>
            <li>Jika ada pertanyaan, silakan hubungi pemohon melalui email: <a href="mailto:<?php echo e($approvalRequest->user->email); ?>" class="link-primary"><?php echo e($approvalRequest->user->email); ?></a></li>
        </ul>
    </div>

    
    <p class="mt-30 mb-10">
        Terima kasih atas perhatian dan kerjasamanya.
    </p>

    <p class="mb-0">
        Hormat kami,<br>
        <strong>Sistem Digital Signature</strong><br>
        <span class="text-muted text-small">UMT Informatika</span>
    </p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('footer'); ?>
    <?php echo $__env->make('emails.partials.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/emails/new_approval_request.blade.php ENDPATH**/ ?>