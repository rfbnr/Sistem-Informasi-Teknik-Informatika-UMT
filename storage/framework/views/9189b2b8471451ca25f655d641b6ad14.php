<?php $__env->startSection('title', 'Bantuan & Dukungan'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('digital-signature.admin.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Help Section Styling */
.help-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    margin-bottom: 30px;
}

.help-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.help-card-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border-radius: 12px 12px 0 0 !important;
    padding: 20px;
    border: none;
}

.help-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 15px;
}

.accordion-button {
    font-weight: 600;
    color: #5a5c69;
    background-color: #f8f9fc;
}

.accordion-button:not(.collapsed) {
    background-color: #e7e9f5;
    color: #4e73df;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(78, 115, 223, 0.25);
}

.accordion-body {
    padding: 20px;
    line-height: 1.8;
}

.faq-item {
    background: #f8f9fc;
    border-left: 4px solid #4e73df;
    padding: 15px 20px;
    margin-bottom: 15px;
    border-radius: 8px;
}

.faq-question {
    font-weight: 600;
    color: #2e3b52;
    margin-bottom: 10px;
    font-size: 15px;
}

.faq-answer {
    color: #5a5c69;
    line-height: 1.7;
}

.search-box {
    position: sticky;
    top: 20px;
    z-index: 100;
}

.quick-link-card {
    border: 2px solid #e3e6f0;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.quick-link-card:hover {
    border-color: #4e73df;
    background-color: #f8f9fc;
    transform: translateY(-3px);
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
                    <i class="fas fa-question-circle me-3"></i>
                    Bantuan & Dukungan
                </h1>
                <p class="mb-0 opacity-75">Dokumentasi lengkap dan panduan untuk Sistem Tanda Tangan Digital</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?php echo e(route('admin.signature.dashboard')); ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar - Quick Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card search-box">
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
                            <?php echo e($section['title']); ?>

                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <a href="#faqs" class="list-group-item list-group-item-action">
                            <i class="fas fa-question me-2 text-info"></i>
                            Pertanyaan yang Sering Diajukan
                        </a>
                    </div>

                    <!-- Contact Support -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2">
                            <i class="fas fa-headset me-2"></i>
                            Butuh Bantuan Lebih Lanjut?
                        </h6>
                        <p class="small text-muted mb-2">Hubungi Dukungan IT</p>
                        <div class="small">
                            <i class="fas fa-envelope me-1"></i> support@umt.ac.id<br>
                            <i class="fas fa-phone me-1"></i> (021) 5555-1234
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
                        <h5 class="mb-1">Selamat Datang di Pusat Bantuan!</h5>
                        <p class="mb-0">Temukan panduan lengkap, tutorial, dan jawaban atas pertanyaan umum tentang Sistem Tanda Tangan Digital.</p>
                    </div>
                </div>
            </div>

            <!-- Help Sections -->
            <?php $__currentLoopData = $helpSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="help-card" id="<?php echo e($section['id']); ?>">
                <div class="help-card-header">
                    <div class="d-flex align-items-center">
                        <div class="help-icon  bg-opacity-20">
                            <i class="fas fa-<?php echo e($section['icon']); ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1"><?php echo e($section['title']); ?></h4>
                            <p class="mb-0 opacity-90"><?php echo e($section['description']); ?></p>
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
                                        data-bs-target="#collapse<?php echo e($loop->parent->index); ?>_<?php echo e($loop->index); ?>">
                                    <i class="fas fa-chevron-right me-2"></i>
                                    <?php echo e($item['title']); ?>

                                </button>
                            </h2>
                            <div id="collapse<?php echo e($loop->parent->index); ?>_<?php echo e($loop->index); ?>"
                                 class="accordion-collapse collapse <?php echo e($loop->first ? 'show' : ''); ?>"
                                 data-bs-parent="#accordion<?php echo e($loop->parent->index); ?>">
                                <div class="accordion-body">
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
            <div class="help-card pb-2" id="faqs">
                <div class="help-card-header">
                    <div class="d-flex align-items-center">
                        <div class="help-icon bg-opacity-20">
                            <i class="fas fa-question"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">Pertanyaan yang Sering Diajukan</h4>
                            <p class="mb-0 opacity-90">Jawaban cepat untuk pertanyaan umum</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $faqItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-3 m-3 faq-item">
                        <div class="faq-question">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            <?php echo e($faq['question']); ?>

                        </div>
                        <div class="faq-answer">
                            <?php echo e($faq['answer']); ?>

                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Additional Resources -->
            <div class="card border shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="fas fa-book me-2"></i>
                        Sumber Daya Tambahan
                    </h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="quick-link-card" onclick="window.open('<?php echo e(route('signature.verify.page')); ?>', '_blank')">
                                <i class="fas fa-qrcode fa-2x text-primary mb-2"></i>
                                <h6>Verifikasi Publik</h6>
                                <small class="text-muted">Tes verifikasi kode QR</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="quick-link-card" onclick="window.location='<?php echo e(route('admin.signature.reports.index')); ?>'">
                                <i class="fas fa-chart-bar fa-2x text-success mb-2"></i>
                                <h6>Lihat Laporan</h6>
                                <small class="text-muted">Analitik & statistik</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="quick-link-card" onclick="window.location='<?php echo e(route('admin.signature.logs.audit')); ?>'">
                                <i class="fas fa-history fa-2x text-info mb-2"></i>
                                <h6>Log Aktivitas</h6>
                                <small class="text-muted">Jejak audit sistem</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Search functionality
document.getElementById('helpSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const accordionItems = document.querySelectorAll('.accordion-item');
    const faqItems = document.querySelectorAll('.faq-item');

    // Search in accordion items
    accordionItems.forEach(item => {
        const title = item.querySelector('.accordion-button').textContent.toLowerCase();
        const content = item.querySelector('.accordion-body').textContent.toLowerCase();

        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            item.style.display = '';
            // Auto-expand matching items
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

    // Search in FAQ items
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question').textContent.toLowerCase();
        const answer = item.querySelector('.faq-answer').textContent.toLowerCase();

        if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
});

// Smooth scroll to sections
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Highlight the section briefly
            target.style.transition = 'background-color 0.5s';
            target.style.backgroundColor = '#f8f9fc';
            setTimeout(() => {
                target.style.backgroundColor = '';
            }, 1000);
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/help-support.blade.php ENDPATH**/ ?>