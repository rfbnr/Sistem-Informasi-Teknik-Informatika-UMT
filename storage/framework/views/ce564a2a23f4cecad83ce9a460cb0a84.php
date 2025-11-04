<?php $__env->startSection('header'); ?>
    <?php echo $__env->make('emails.partials.header', [
        'title' => 'Tanda Tangan Perlu Diperbaiki ⚠️',
        'subtitle' => 'Kaprodi meminta Anda untuk menandatangani ulang dokumen'
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <p class="greeting">
        Halo <?php echo e($approvalRequest->user->name); ?>,
    </p>

    
    <div class="alert alert-warning">
        <strong style="font-size: 16px;">⚠️ Tanda Tangan Ditolak</strong>
        <p class="mt-10 mb-0">
            Kaprodi telah meninjau tanda tangan Anda pada dokumen <strong><?php echo e($approvalRequest->document_name); ?></strong> dan meminta Anda untuk <strong>menandatangani ulang atau mengajukan ulang</strong> dokumen tersebut.
        </p>
    </div>

    
    <p>
        Jangan khawatir, ini adalah hal yang wajar dalam proses quality control. Kaprodi ingin memastikan bahwa tanda tangan Anda terlihat sempurna pada dokumen final.
    </p>

    
    <?php echo $__env->make('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <div style="background-color: #ffebee; border-left: 4px solid #f44336; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 style="margin: 0 0 12px 0; color: #c62828; font-size: 16px; font-weight: 600;">
            📝 Alasan Penolakan
        </h3>
        <p style="margin: 0; color: #555555; font-size: 14px; line-height: 1.6; background-color: white; padding: 15px; border-radius: 4px;">
            <?php echo e($rejectionReason); ?>

        </p>
    </div>

    
    <div class="info-card-blue">
        <h3 class="section-title">
            💡 Tips untuk Penandatanganan Ulang
        </h3>
        <p class="mb-12">
            Berikut adalah hal-hal yang perlu diperhatikan saat menandatangani ulang:
        </p>
        <ul class="list-styled">
            <li><strong>Posisi yang tepat:</strong> Letakkan tanda tangan di area yang ditentukan, tidak terlalu ke pinggir</li>
            <li><strong>Ukuran proporsional:</strong> Pastikan ukuran tanda tangan tidak terlalu besar atau terlalu kecil</li>
            <li><strong>Tidak menutupi teks:</strong> Pastikan tanda tangan tidak menutupi informasi penting di dokumen</li>
            <li><strong>Kualitas visual:</strong> Gunakan template tanda tangan dengan kualitas gambar yang baik</li>
            <li><strong>Preview sebelum submit:</strong> Selalu review penempatan sebelum mengirimkan</li>
        </ul>
    </div>

    
    <div class="section-card">
        <h4 class="section-title">
            📋 Langkah-Langkah Menandatangani Ulang
        </h4>
        <ol class="list-styled">
            <li><strong>Klik tombol "Tandatangani Ulang"</strong> di bawah ini</li>
            <li><strong>Buka halaman penandatanganan</strong> dokumen</li>
            <li><strong>Perhatikan feedback</strong> dari Kaprodi (alasan penolakan di atas)</li>
            <li><strong>Pilih atau edit template</strong> tanda tangan Anda jika diperlukan</li>
            <li><strong>Letakkan tanda tangan</strong> dengan lebih hati-hati, ikuti tips di atas</li>
            <li><strong>Review penempatan</strong> dengan teliti sebelum submit</li>
            <li><strong>Submit ulang</strong> untuk review Kaprodi</li>
        </ol>
    </div>

    
    <div class="timeline-container">
        <h4 class="timeline-title">
            📊 Progress Dokumen Anda
        </h4>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DIAJUKAN</div>
                </td>

                
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DISETUJUI</div>
                </td>

                
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-retry"></div>
                </td>

                
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-retry">
                        ↻
                    </div>
                    <div class="timeline-label timeline-label-retry">TANDA TANGAN ULANG</div>
                </td>

                
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-pending"></div>
                </td>

                
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-pending">
                        ⏳
                    </div>
                    <div class="timeline-label timeline-label-pending">VERIFIKASI</div>
                </td>
            </tr>
        </table>
    </div>

    
    <div class="mt-30">
        <p class="text-center text-strong mb-15">
            Tandatangani Ulang Dokumen Anda
        </p>

        <?php echo $__env->make('emails.components.button', [
            'url' => route('user.signature.sign.document', $approvalRequest->id),
            'text' => '✍️ Tandatangani Ulang Dokumen',
            'type' => 'primary',
            'block' => true
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <p class="mt-15 mb-0 text-center">
            <a href="<?php echo e(route('user.signature.approval.status')); ?>" class="link-primary">
                atau lihat status dokumen Anda
            </a>
        </p>
    </div>

    
    <div class="divider"></div>

    
    <div class="info-card-green">
        <h4 class="section-subtitle">
            💪 Jangan Berkecil Hati!
        </h4>
        <p class="mb-0">
            Proses quality control ini bertujuan untuk memastikan dokumen Anda terlihat sempurna dan profesional. Dengan mengikuti tips di atas, Anda akan berhasil di percobaan berikutnya!
        </p>
    </div>

    
    <div style="background-color: #fff3e0; border-left: 3px solid #ff9800; padding: 16px 20px; border-radius: 6px; margin: 20px 0;">
        <h4 class="section-subtitle">
            💡 Butuh Bantuan?
        </h4>
        <p class="mb-8 text-small">
            Jika Anda mengalami kesulitan atau memiliki pertanyaan tentang proses penandatanganan, jangan ragu untuk menghubungi:
        </p>
        <ul class="list-no-margin text-muted text-small">
            <li>Email: support@informatika.umt.ac.id</li>
            <li>WhatsApp: +62 812-3456-7890</li>
            <li>Jam kerja: Senin - Jumat, 08:00 - 16:00 WIB</li>
        </ul>
    </div>

    
    <p class="mt-30 mb-10">
        Semangat untuk menandatangani ulang! Kami yakin Anda bisa melakukannya dengan baik.
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

<?php echo $__env->make('emails.layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/emails/document_signature_rejected_by_kaprodi.blade.php ENDPATH**/ ?>