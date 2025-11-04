<nav class="navbar navbar-expand-lg" style="background-color: #05184E">
    <div class="container">
        <a class="navbar-brand" href="<?php echo e(url('/')); ?>">
            <img src="<?php echo e(url('assets/logo.JPG')); ?>" alt="Logo"
                class="d-inline-block align-text-center img-fluid rounded mx-auto d-block">
        </a>
        <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 text-center">
                <li class="nav-item">
                    <a class="nav-link text-white" aria-current="page" href="<?php echo e(url('/')); ?>">BERANDA</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="profileDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        PROFIL
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item " href="<?php echo e(url('visi-misi')); ?>">VISI DAN MISI</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('dosen')); ?>">DOSEN</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('akreditasi')); ?>">AKREDITASI</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('struktur')); ?>">SO</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('alumni')); ?>">ALUMNI</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown">
                        LAYANAN DIGITAL
                    </a>
                    <ul class="dropdown-menu">
                        <?php if(auth()->guard('web')->check()): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('user.signature.approval.request')); ?>">
                                <i class="fas fa-file-upload me-2"></i> Submit Document
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('user.signature.approval.status')); ?>">
                                <i class="fas fa-list-alt me-2"></i> My Documents
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('user.signature.my.signatures.index')); ?>">
                                <i class="fas fa-signature me-2"></i> My Signatures
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>

                        <li><a class="dropdown-item" href="<?php echo e(route('signature.verify.page')); ?>">
                            <i class="fas fa-shield-alt me-2"></i> Verify Document
                        </a></li>

                        
                        <li><a class="dropdown-item" href="<?php echo e(route('user.signature.help')); ?>">
                            <i class="fas fa-question-circle me-2"></i> Help & Support
                        </a></li>

                        <?php if(auth()->guard()->guest()): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('login')); ?>">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Submit Documents
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                
                

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="layananDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AGENDA
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="layananDropdown">
                        <li><a class="dropdown-item" href="<?php echo e(url('events')); ?>">JADWAL</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="risetDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        RISET DAN PENGABDIAN
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="risetDropdown">
                        <li><a class="dropdown-item" href="<?php echo e(url('penelitian')); ?>">DAFTAR PENELITIAN</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('pengabdian')); ?>">DAFTAR PENGABDIAN</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('sinta')); ?>">PROFIL SINTA</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="infoDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        INFO PENTING
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="infoDropdown">
                        <li><a class="dropdown-item" href="<?php echo e(url('sorotan')); ?>">TERKINI</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('lomba')); ?>">LOMBA</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="akademikDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AKADEMIK
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="akademikDropdown">
                        <li><a class="dropdown-item" href="<?php echo e(url('kurikulum-rps')); ?>">KURIKULUM & RPS</a></li>
                        <li><a class="dropdown-item" href="#">CAPSTONE</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('luaran-obe')); ?>">LUARAN OBE</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(url('dosen-pembimbing-akademik')); ?>">DOSEN PEMBIMBING AKADEMIK</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" aria-current="page" href="<?php echo e(url('download')); ?>">DOWNLOAD</a>
                </li>
            </ul>

            <div class="navbar-nav ms-auto">
                <?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-md login btn-outline-primary">Login</a>
                <?php endif; ?>
                <?php if(auth()->guard()->check()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if(auth()->guard()->check()): ?>
                                <?php echo e(Auth::user()->name); ?>

                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?php echo e('/logout'); ?>">Logout</a>
                            </li>
                            
                        </ul>
                    </li>
                <?php endif; ?>
            </div>

        </div>
    </div>
</nav>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/layouts/navbar.blade.php ENDPATH**/ ?>