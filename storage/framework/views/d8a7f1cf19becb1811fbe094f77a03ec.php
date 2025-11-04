


<div class="info-card-purple">
    <h3 class="section-title">
        📄 Detail Dokumen
    </h3>

    <table class="info-table" width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden;">
        
        <tr class="info-row">
            <td class="info-label">
                Nama Dokumen
            </td>
            <td class="info-value">
                <?php echo e($approvalRequest->document_name); ?>

            </td>
        </tr>

        
        <?php if($approvalRequest->nomor): ?>
        <tr class="info-row">
            <td class="info-label">
                Nomor Dokumen
            </td>
            <td class="info-value">
                <?php echo e($approvalRequest->full_document_number ?? $approvalRequest->nomor); ?>

            </td>
        </tr>
        <?php endif; ?>

        
        <?php if($approvalRequest->document_type): ?>
        <tr class="info-row">
            <td class="info-label">
                Jenis Dokumen
            </td>
            <td class="info-value">
                <?php echo e(ucfirst(str_replace('_', ' ', $approvalRequest->document_type))); ?>

            </td>
        </tr>
        <?php endif; ?>

        
        <?php if(isset($showRequester) && $showRequester && $approvalRequest->user): ?>
        <tr class="info-row">
            <td class="info-label">
                Pemohon
            </td>
            <td class="info-value">
                <?php echo e($approvalRequest->user->name); ?><br>
                <span class="text-muted text-small"><?php echo e($approvalRequest->user->email); ?></span>
            </td>
        </tr>
        <?php endif; ?>

        
        <tr class="info-row">
            <td class="info-label">
                Tanggal Pengajuan
            </td>
            <td class="info-value">
                <?php echo e($approvalRequest->created_at->format('d F Y, H:i')); ?> WIB
            </td>
        </tr>

        
        <?php if(isset($approvalRequest->priority)): ?>
        <tr class="info-row">
            <td class="info-label">
                Prioritas
            </td>
            <td class="info-value">
                <?php
                    $priorityBadge = [
                        'high' => ['class' => 'badge-danger', 'text' => 'Tinggi'],
                        'normal' => ['class' => 'badge-warning', 'text' => 'Normal'],
                        'low' => ['class' => 'badge-info', 'text' => 'Rendah']
                    ][$approvalRequest->priority] ?? ['class' => 'badge-info', 'text' => ucfirst($approvalRequest->priority)];
                ?>
                <span class="badge <?php echo e($priorityBadge['class']); ?>">
                    <?php echo e($priorityBadge['text']); ?>

                </span>
            </td>
        </tr>
        <?php endif; ?>

        
        <?php if(isset($showStatus) && $showStatus): ?>
        <tr>
            <td class="info-label">
                Status
            </td>
            <td class="info-value">
                <?php
                    $statusBadge = [
                        'pending' => ['class' => 'badge-warning', 'text' => 'Menunggu Persetujuan'],
                        'approved' => ['class' => 'badge-success', 'text' => 'Disetujui'],
                        'rejected' => ['class' => 'badge-danger', 'text' => 'Ditolak'],
                        'user_signed' => ['class' => 'badge-info', 'text' => 'Sudah Ditandatangani'],
                        'sign_approved' => ['class' => 'badge-success', 'text' => 'Terverifikasi']
                    ][$approvalRequest->status] ?? ['class' => 'badge-info', 'text' => $approvalRequest->status];
                ?>
                <span class="badge <?php echo e($statusBadge['class']); ?>">
                    <?php echo e($statusBadge['text']); ?>

                </span>
            </td>
        </tr>
        <?php endif; ?>

        
        <?php if($approvalRequest->notes): ?>
        <tr>
            <td class="info-label">
                Catatan
            </td>
            <td class="info-value">
                <?php echo e($approvalRequest->notes); ?>

            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/emails/components/document-card.blade.php ENDPATH**/ ?>