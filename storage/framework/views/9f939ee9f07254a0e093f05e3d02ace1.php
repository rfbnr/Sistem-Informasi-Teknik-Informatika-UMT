<?php $__env->startSection('title', 'Bantuan & Dukungan'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.status-container {
    background: white;
    border-radius: 1rem;
    margin-top: 4rem;
    margin-right: 1rem;
    margin-left: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.status-header {
    /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<!-- Section Header -->
<section id="header-section">
    <h1>Bantuan & Dukungan</h1>
</section>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm status-header" >
                <div class="card-body text-white p-4">
                    <h1 class="mb-2">
                        <i class="fas fa-question-circle me-3"></i>
                        Bantuan & Dukungan
                    </h1>
                    <p class="mb-0 opacity-90">Pelajari cara menggunakan Sistem Tanda Tangan Digital dengan efektif</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar - Quick Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-search me-2"></i>
                        Navigasi Cepat
                    </h6>

                    <!-- Search Input -->
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="helpSearch" placeholder="Cari topik...">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <!-- Quick Links -->
                    <div class="list-group">
                        <?php $__currentLoopData = $helpSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="#<?php echo e($section['id']); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-<?php echo e($section['icon']); ?> me-2 text-<?php echo e($section['color']); ?>"></i>
                            <small><?php echo e($section['title']); ?></small>
                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <a href="#faqs" class="list-group-item list-group-item-action">
                            <i class="fas fa-question me-2 text-info"></i>
                            <small>FAQs</small>
                        </a>
                    </div>

                    <!-- Contact Support -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2">
                            <i class="fas fa-headset me-2"></i>
                            Butuh Bantuan?
                        </h6>
                        <p class="small text-muted mb-2">Hubungi Dukungan</p>
                        <div class="small">
                            <i class="fas fa-envelope me-1"></i> support@umt.ac.id
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Welcome Message -->
            <div class="alert alert-info border-0 mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">Selamat Datang di Pusat Bantuan Mahasiswa!</h5>
                        <p class="mb-0">Panduan ini akan membantu Anda mengirimkan dokumen, menandatanganinya secara digital, dan mengelola tanda tangan Anda.</p>
                    </div>
                </div>
            </div>

            <!-- Help Sections -->
            <?php $__currentLoopData = $helpSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card border-0 shadow-sm mb-4" id="<?php echo e($section['id']); ?>">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #<?php echo e($section['color'] === 'primary' ? '4e73df' : ($section['color'] === 'success' ? '1cc88a' : ($section['color'] === 'info' ? '36b9cc' : ($section['color'] === 'warning' ? 'f6c23e' : ($section['color'] === 'danger' ? 'e74a3b' : '858796'))))); ?> 0%, #<?php echo e($section['color'] === 'primary' ? '224abe' : ($section['color'] === 'success' ? '17a673' : ($section['color'] === 'info' ? '2c9faf' : ($section['color'] === 'warning' ? 'c69500' : ($section['color'] === 'danger' ? 'be2617' : '60616f'))))); ?> 100%);">
                    <div class="d-flex align-items-center p-2">
                        <div class="me-3" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-<?php echo e($section['icon']); ?> fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?php echo e($section['title']); ?></h5>
                            <small class="opacity-90"><?php echo e($section['description']); ?></small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="accordion<?php echo e($loop->index); ?>">
                        <?php $__currentLoopData = $section['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo e($loop->first ? '' : 'collapsed'); ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?php echo e($loop->parent->index); ?>_<?php echo e($loop->index); ?>"
                                        style="font-weight: 600;">
                                    <i class="fas fa-chevron-right me-2"></i>
                                    <?php echo e($item['title']); ?>

                                </button>
                            </h2>
                            <div id="collapse<?php echo e($loop->parent->index); ?>_<?php echo e($loop->index); ?>"
                                 class="accordion-collapse collapse <?php echo e($loop->first ? 'show' : ''); ?>"
                                 data-bs-parent="#accordion<?php echo e($loop->parent->index); ?>">
                                <div class="accordion-body" style="line-height: 1.8;">
                                    <?php echo $item['content']; ?>

                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <!-- FAQs Section -->
            <div class="card border-0 shadow-sm" id="faqs">
                <div class="card-header bg-info text-white">
                    <div class="d-flex align-items-center p-2">
                        <div class="me-3" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-question fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Pertanyaan yang Sering Diajukan</h5>
                            <small class="opacity-90">Jawaban cepat untuk pertanyaan umum</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $faqItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-3 mb-3 bg-light border-start border-4 border-primary" style="border-radius: 8px;">
                        <div class="fw-bold text-dark mb-2">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            <?php echo e($faq['question']); ?>

                        </div>
                        <div class="text-muted">
                            <?php echo e($faq['answer']); ?>

                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="fas fa-bolt me-2"></i>
                        Aksi Cepat
                    </h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border-2" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='#4e73df';" onmouseout="this.style.transform=''; this.style.borderColor='';" onclick="window.location='<?php echo e(route('user.signature.approval.request')); ?>'">
                                <div class="card-body text-center">
                                    <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                                    <h6>Unggah Dokumen</h6>
                                    <small class="text-muted">Kirim dokumen baru</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-2" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='#1cc88a';" onmouseout="this.style.transform=''; this.style.borderColor='';" onclick="window.location='<?php echo e(route('user.signature.approval.status')); ?>'">
                                <div class="card-body text-center">
                                    <i class="fas fa-folder-open fa-3x text-success mb-3"></i>
                                    <h6>Dokumen Saya</h6>
                                    <small class="text-muted">Lihat status pengajuan</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-2" style="cursor: pointer; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='#36b9cc';" onmouseout="this.style.transform=''; this.style.borderColor='';" onclick="window.location='<?php echo e(route('user.signature.my.signatures.index')); ?>'">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-signature fa-3x text-info mb-3"></i>
                                    <h6>Tanda Tangan Saya</h6>
                                    <small class="text-muted">Dokumen yang ditandatangani</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button:not(.collapsed) {
    background-color: #e7e9f5;
    color: #4e73df;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(78, 115, 223, 0.25);
}
</style>

<script>
// Search functionality
document.getElementById('helpSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const accordionItems = document.querySelectorAll('.accordion-item');
    const faqItems = document.querySelectorAll('.bg-light.border-start');

    accordionItems.forEach(item => {
        const title = item.querySelector('.accordion-button').textContent.toLowerCase();
        const content = item.querySelector('.accordion-body').textContent.toLowerCase();

        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            item.style.display = '';
            if (searchTerm.length > 2) {
                const collapse = item.querySelector('.accordion-collapse');
                if (collapse && !collapse.classList.contains('show')) {
                    const button = item.querySelector('.accordion-button');
                    button.click();
                }
            }
        } else {
            item.style.display = 'none';
        }
    });

    faqItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            target.style.transition = 'background-color 0.5s';
            target.style.backgroundColor = '#f8f9fc';
            setTimeout(() => { target.style.backgroundColor = ''; }, 1000);
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/user/help-support.blade.php ENDPATH**/ ?>