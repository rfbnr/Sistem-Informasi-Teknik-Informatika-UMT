<?php $__env->startSection('header'); ?>
    <?php echo $__env->make('emails.partials.header', [
        'title' => 'Permintaan Perlu Perbaikan',
        'subtitle' => 'Informasi mengenai permintaan persetujuan dokumen Anda'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <p class="greeting">
        Halo <?php echo e($approvalRequest->user->name); ?>,
    </p>

    
    <div class="alert alert-warning">
        <strong>⚠️ Perhatian:</strong> Permintaan persetujuan dokumen Anda <strong>memerlukan perbaikan</strong>.
    </div>

    
    <p>
        Setelah melakukan review, Ketua Program Studi memutuskan bahwa dokumen <strong><?php echo e($approvalRequest->document_name); ?></strong> memerlukan beberapa perbaikan sebelum dapat disetujui.
    </p>

    
    <?php echo $__env->make('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php if($approvalRequest->rejection_reason): ?>
    <div style="background-color: #ffebee; border-left: 4px solid #f44336; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="section-title" style="color: #c62828;">
            📝 Alasan Penolakan
        </h3>
        <p class="mb-0" style="color: #c62828; background-color: #ffffff; padding: 15px; border-radius: 4px;">
            "<?php echo e($approvalRequest->rejection_reason); ?>"
        </p>
    </div>
    <?php endif; ?>

    
    <div class="info-card-blue">
        <h3 class="section-title">
            🔧 Langkah Perbaikan
        </h3>
        <ol class="list-styled">
            <li class="mb-8">Baca dengan teliti <strong>alasan penolakan</strong> di atas</li>
            <li class="mb-8">Lakukan <strong>perbaikan</strong> pada dokumen sesuai catatan yang diberikan</li>
            <li class="mb-8">Pastikan semua <strong>persyaratan</strong> dokumen sudah terpenuhi</li>
            <li class="mb-8"><strong>Upload ulang</strong> dokumen yang sudah diperbaiki</li>
            <li class="mb-0">Tunggu proses review berikutnya</li>
        </ol>
    </div>

    
    <div class="mt-30">
        <p class="text-center text-strong mb-15">
            Siap Mengajukan Ulang?
        </p>

        <?php echo $__env->make('emails.components.button', [
            'url' => route('user.signature.approval.request'),
            'text' => '📤 Ajukan Dokumen Baru',
            'type' => 'primary',
            'block' => true
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <p class="mt-15 mb-0 text-center text-muted text-small">
            atau lihat
            <a href="<?php echo e(route('user.signature.approval.status')); ?>" class="link-primary">
                Status Semua Dokumen
            </a>
        </p>
    </div>

    
    <div class="divider"></div>

    
    <div class="section-card">
        <h4 class="section-subtitle">
            💡 Tips Agar Dokumen Disetujui
        </h4>
        <ul class="list-no-margin text-muted text-small">
            <li>Pastikan format dokumen sesuai (PDF, ukuran max 25MB)</li>
            <li>Periksa kelengkapan informasi dalam dokumen</li>
            <li>Gunakan nama file yang jelas dan deskriptif</li>
            <li>Isi catatan tambahan jika ada informasi penting</li>
            <li>Hubungi kaprodi jika ada pertanyaan: <a href="mailto:<?php echo e($approvalRequest->kaprodi->email ?? 'informatika@umt.ac.id'); ?>" class="link-primary"><?php echo e($approvalRequest->kaprodi->email ?? 'informatika@umt.ac.id'); ?></a></li>
        </ul>
    </div>

    
    <?php if($approvalRequest->rejected_at && $approvalRequest->rejector): ?>
    <div style="background-color: #ffffff; border: 1px solid #e9ecef; padding: 15px 20px; border-radius: 6px; margin: 20px 0;">
        <table width="100%" cellpadding="5" cellspacing="0" class="text-small text-muted">
            <tr>
                <td width="40%" class="text-strong">Ditolak Oleh:</td>
                <td width="60%"><?php echo e($approvalRequest->rejector->name); ?></td>
            </tr>
            <tr>
                <td class="text-strong">Tanggal Penolakan:</td>
                <td><?php echo e($approvalRequest->rejected_at->format('d F Y, H:i')); ?> WIB</td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    
    <div class="alert alert-success">
        <strong>💪 Jangan Berkecil Hati!</strong> Penolakan ini adalah bagian dari proses quality control untuk memastikan dokumen Anda sempurna. Lakukan perbaikan dan ajukan kembali!
    </div>

    
    <p class="mt-30 mb-10">
        Jika ada pertanyaan atau butuh klarifikasi lebih lanjut, jangan ragu untuk menghubungi kami.
    </p>

    <p class="mb-0">
        Salam,<br>
        <strong>Tim Digital Signature</strong><br>
        <span class="text-muted text-small">UMT Informatika</span>
    </p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('footer'); ?>
    <?php echo $__env->make('emails.partials.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/emails/approval_request_rejected.blade.php ENDPATH**/ ?>