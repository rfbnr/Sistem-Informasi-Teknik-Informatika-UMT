<?php $__env->startSection('title', 'Pengabdian'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Section Header -->
    <section id="header-section">
        <h1>Daftar Pengabdian</h1>
    </section>
    
    <section id="jurnal">
        <div class="container-fluid mt-5">
            <!--<div class="text-center mb-4">-->
            <!--    <h3 class="fs-3 fw-bolder">Daftar Pengabdian</h3>-->
            <!--</div>-->
            <div class="row justify-content-center">
                <!--<div class="col-md-10">-->
                <!--    <p>Dimana mahasiswa dapat mem-publish atau menerbitkan artikel ilmiah ? Laman ini menampilkan informasi-->
                <!--        mengenai Pengabdian bidang Informatika dari seluruh Indonesia. Pengabdian dikelompokkan berdasarkan waktu-->
                <!--        terbit dan tingkat akreditasi Sinta, dengan urutan Sinta 1 lebih baik apabila dibandingkan Sinta 6.-->
                <!--        Beberapa diantaranya terakreditas Scopus. Khusus mahasiswa S1 Prodi Informatika, Pengabdian-->
                <!--        terakreditasi Sinta 3 merupakan syarat minimal yang harus dipenuhi apabila hendak menempuh jalur-->
                <!--        kelulusan non skripsi atau non reguler kategori Scientist. Melalui skema Scientist, segala biaya-->
                <!--        yang timbul ditanggung oleh Mahasiswa</p>-->

                <!--</div>-->
                <div class="col-md-10">
                    <iframe src="https://docs.google.com/spreadsheets/d/14Z3KS43pWeiEPRBiqZnjWG9ns-YR7cpJfM6DvVFLxoU/preview?gid=417893748&single=true"
                        frameborder="0"
                        width="100%"
                        height="800"
                        allowfullscreen
                        style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                    </iframe>
                </div>



            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/pengabdian.blade.php ENDPATH**/ ?>