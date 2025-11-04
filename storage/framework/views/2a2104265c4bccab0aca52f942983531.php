<?php $__env->startSection('title', 'Lomba'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Section Header -->
    <section id="header-section">
        <h1>Daftar Lomba</h1>
    </section>
    
    <section id="lomba">
        <div class="container mt-5">
            <div class="text-center mb-4">
                <h3 class="fs-3 fw-bolder">Lomba</h3>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <p>Dunia teknologi dan bisnis di Indonesia terus berkembang pesat, sejalan dengan meningkatnya jumlah
                        acara IT yang diselenggarakan setiap tahunnya. Berdasarkan data, terdapat berbagai jenis acara IT
                        yang dapat diikuti, mulai dari seminar dan workshop hingga kompetisi dan hackathon. Bidang IT &
                        Business menjadi fokus utama, diikuti oleh IT murni, Business, Multimedia, dan Startup. Universitas
                        dan institusi pendidikan menjadi penyelenggara terbanyak, menunjukkan peran penting mereka dalam
                        mendorong kemajuan teknologi di Indonesia. Acara IT tersebar sepanjang tahun, dengan puncaknya pada
                        bulan September dan Oktober. Beberapa acara yang patut disorot di antaranya Technofair 9.0, PKM
                        (Pekan Kreativitas Mahasiswa), GEMASTIK, dan INVFEST 6.0. Acara-acara ini menawarkan kesempatan bagi
                        para peserta untuk belajar, membangun jaringan, dan mengembangkan potensi diri di bidang IT. Adapun
                        rekomendasi acara lomba rutin yang bisa diikuti mahasiswa informatika sebagai berikut.</p>

                    <!-- Menampilkan data lomba dalam bentuk list -->
                    <ul>
                        <?php $__currentLoopData = $lombas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lomba): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($lomba->name); ?> - <?php echo e($lomba->month); ?> - <?php echo e($lomba->bidang); ?> - <?php echo e($lomba->tempat); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </section>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/lomba.blade.php ENDPATH**/ ?>