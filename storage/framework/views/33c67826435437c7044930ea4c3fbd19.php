<?php $__env->startSection('title', 'Alumni'); ?>

<?php $__env->startSection('content'); ?>

    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            border-radius: 15px 15px 0 0;
            object-fit: cover;
            height: 200px;
            width: 100%;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-text {
            font-size: 1rem;
            color: #6c757d;
        }

        .btn-link {
            color: #007bff;
            font-size: 1.2rem;
        }

        .btn-link:hover {
            color: #0056b3;
            text-decoration: none;
        }
    </style>

    <!-- Section Header -->
    <section id="header-section">
        <h1>Alumni</h1>
    </section>

    <!-- Section Alumni -->
    <section id="alumni" class="mt-5">
        <div class="container">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Alumni</h3>
            </div>
            <div class="row justify-content-center">
                <?php $__currentLoopData = $alumnis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alumni): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo e(asset('storage/' . $alumni->image)); ?>" class="card-img-top"
                                alt="<?php echo e($alumni->name); ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo e($alumni->name); ?></h5>
                                <p class="card-text"><?php echo e($alumni->jabatan); ?></p>
                                <div class="d-flex justify-content-center">
                                    <?php if($alumni->linkedin): ?>
                                        <a href="<?php echo e($alumni->linkedin); ?>" class="btn btn-link"><i
                                                class="bi bi-linkedin"></i></a>
                                    <?php endif; ?>
                                    <?php if($alumni->instagram): ?>
                                        <a href="<?php echo e($alumni->instagram); ?>" class="btn btn-link"><i
                                                class="bi bi-instagram"></i></a>
                                    <?php endif; ?>
                                    <?php if($alumni->email): ?>
                                        <a href="mailto:<?php echo e($alumni->email); ?>" class="btn btn-link"><i
                                                class="bi bi-envelope"></i></a>
                                    <?php endif; ?>
                                    <?php if($alumni->youtube): ?>
                                        <a href="<?php echo e($alumni->youtube); ?>" class="btn btn-link"><i
                                                class="bi bi-youtube"></i></a>
                                    <?php endif; ?>
                                    <?php if($alumni->tiktok): ?>
                                        <a href="<?php echo e($alumni->tiktok); ?>" class="btn btn-link"><i
                                                class="bi bi-tiktok"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/alumni.blade.php ENDPATH**/ ?>