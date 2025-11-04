

<?php $__env->startSection('title', 'Dosen'); ?>

<?php $__env->startSection('content'); ?>

    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            border-radius: 50%; /* Membuat gambar berbentuk bulat */
            object-fit: cover; /* Menjaga proporsi gambar dan mengisi area */
            height: 200px; /* Tinggi gambar */
            width: 200px; /* Lebar gambar */
            margin: 0 auto; /* Memposisikan gambar di tengah */
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

        .btn {
            font-size: 1.2rem;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-dark {
            background-color: #343a40;
            border-color: #343a40;
        }
    </style>

    <!-- Section Header -->
    <section id="header-section">
        <h1>Daftar Dosen</h1>
    </section>

    <!-- Section Dosen -->
    <section id="dosen" class="mt-5">
        <div class="container">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Dosen</h3>
            </div>
            <div class="row justify-content-center">
                <?php $__currentLoopData = $dosens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $dosen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 text-center">
                            <img src="<?php echo e(asset('storage/' . $dosen->image)); ?>" class="card-img-top"
                                alt="<?php echo e($dosen->name); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e($dosen->name); ?></h5>
                                <p class="card-text"><?php echo e($dosen->jabatan); ?></p>
                                <div class="d-flex justify-content-center">
                                    <a href="<?php echo e($dosen->linkedin); ?>" target="_blank" class="btn btn-primary">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="<?php echo e($dosen->instagram); ?>" target="_blank" class="btn btn-danger">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="mailto:<?php echo e($dosen->email); ?>" target="_blank" class="btn btn-secondary">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    <a href="<?php echo e($dosen->youtube); ?>" target="_blank" class="btn btn-danger">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                    <a href="<?php echo e($dosen->tiktok); ?>" target="_blank" class="btn btn-dark">
                                        <i class="fab fa-tiktok"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                                            </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/dosen.blade.php ENDPATH**/ ?>