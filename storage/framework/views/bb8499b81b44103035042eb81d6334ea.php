<?php $__env->startSection('title', 'Akreditasi'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Section Header -->
    <section id="header-section">
        <h1>Akreditasi</h1>
    </section>

    <section>
        <div class="container">
            <div class="row justify-content-center">
                <?php $__currentLoopData = $akreditasis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $akreditasi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12 col-md-8">
                        <div class="text-center my-4">
                            <img src="<?php echo e(asset('storage/' . $akreditasi->image)); ?>" class="img-fluid" alt="Uploaded Image">
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/akreditas.blade.php ENDPATH**/ ?>