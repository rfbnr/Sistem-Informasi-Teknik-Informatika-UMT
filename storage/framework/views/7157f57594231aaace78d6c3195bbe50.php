<?php $__env->startSection('title', 'Struktur Organisasi'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Section Header -->
    <section id="header-section">
        <h1>Struktur Organisasi</h1>
    </section>
    <section>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <?php $__currentLoopData = $strukturs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $struktur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12 col-md-12">
                        <div class="text-center my-4">
                            <img src="<?php echo e(asset('storage/' . $struktur->image)); ?>" class="img-fluid" alt="Uploaded Image">
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/struktur.blade.php ENDPATH**/ ?>