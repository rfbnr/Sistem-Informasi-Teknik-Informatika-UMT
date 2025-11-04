<?php $__env->startSection('title', 'Sorotan'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Section Header -->
    <section id="header-section">
        <h1>Daftar Sorotan Terkini</h1>
    </section>
    <section>
        <div class="container mt-5 mb-5">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Sorotan Informasi</h3>
            </div>
            <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                <?php $__currentLoopData = $sorotans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sorotan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col d-flex justify-content-center">
                        <div class="card h-100">
                            <div class="home-card-img-top">
                                <img src="<?php echo e(asset('storage/' . $sorotan->image)); ?>" alt="Placeholder image"
                                    class="card-img-top">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e($sorotan->title); ?></h5>
                                <p class="card-text"><?php echo e($sorotan->description); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/sorotan.blade.php ENDPATH**/ ?>