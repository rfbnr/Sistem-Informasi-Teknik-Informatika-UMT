

<?php $__env->startSection('title', 'Layanan'); ?>

<?php $__env->startSection('content'); ?>

<style>
    .badge-success {
        background-color: #28a745;
        color: #fff;
        padding: 0.5em 1em;
        border-radius: 0.25em;
    }

    .badge-danger {
        background-color: #dc3545;
        color: #fff;
        padding: 0.5em 1em;
        border-radius: 0.25em;
    }

    .img-circle {
        width: 100px;
        height: 100px;
        object-fit: cover; /* Ensures the image covers the entire circle */
        border-radius: 50%; /* Makes the image circular */
    }
</style>

<!-- Section Header -->
<section id="header-section">
    <h1>Layanan Prodi</h1>
</section>

<!-- Section Aproval Kaprodi -->
<section id="aproval-kaprodi" class="mt-5 mb-5">
    <div class="container">
        <div class="text-center mb-4">
            <h3 class="fs-3 fw-bolder">Layanan Prodi</h3>
        </div>
        <div class="table-responsive">
            <table id="Layanan" class="table table-bordered table-striped table-sm text-center">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $layanans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $layanan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><img src="<?php echo e(Storage::url($layanan->image)); ?>" alt="foto" class="img-circle"></td>
                            <td><?php echo e($layanan->name); ?></td>
                            <td><?php echo e($layanan->jabatan); ?></td>
                            <td>
                                <?php if($layanan->status == 'Ada'): ?>
                                    <span class="badge badge-success"> <?php echo e($layanan->status); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger"> <?php echo e($layanan->status); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $layanan->keterangan; ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center">Coming Soon</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/layanan.blade.php ENDPATH**/ ?>