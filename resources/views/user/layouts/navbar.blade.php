<nav class="navbar navbar-expand-lg" style="background-color: #05184E">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ url('assets/logo.JPG') }}" alt="Logo"
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
                    <a class="nav-link text-white" aria-current="page" href="{{ url('/') }}">BERANDA</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="profileDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        PROFIL
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item " href="{{ url('visi-misi') }}">VISI DAN MISI</a></li>
                        <li><a class="dropdown-item" href="{{ url('dosen') }}">DOSEN</a></li>
                        <li><a class="dropdown-item" href="{{ url('akreditasi') }}">AKREDITASI</a></li>
                        <li><a class="dropdown-item" href="{{ url('struktur') }}">SO</a></li>
                        <li><a class="dropdown-item" href="{{ url('alumni') }}">ALUMNI</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown">
                        LAYANAN DIGITAL
                    </a>
                    <ul class="dropdown-menu">
                        @auth('web')
                            <li><a class="dropdown-item" href="{{ route('user.signature.approval.request') }}">
                                <i class="fas fa-file-upload me-2"></i> Submit Document
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('user.signature.approval.status') }}">
                                <i class="fas fa-list-alt me-2"></i> My Documents
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('user.signature.my.signatures.index') }}">
                                <i class="fas fa-signature me-2"></i> My Signatures
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                        @endauth

                        <li><a class="dropdown-item" href="{{ route('signature.verify.page') }}">
                            <i class="fas fa-shield-alt me-2"></i> Verify Document
                        </a></li>

                        {{-- Help & Support --}}
                        <li><a class="dropdown-item" href="{{ route('user.signature.help') }}">
                            <i class="fas fa-question-circle me-2"></i> Help & Support
                        </a></li>

                        @guest
                            <li><a class="dropdown-item" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Submit Documents
                            </a></li>
                        @endguest
                    </ul>
                </li>

                {{-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        LAYANAN
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ url('aproval') }}">APROVAL KAPRODI</a></li>
                        <li><a class="dropdown-item" href="{{ route('signature.verify.page') }}">VERIFIKASI DIGITAL</a></li>
                        <li><a class="dropdown-item" href="{{ url('layanans') }}">KAPRODI</a></li>
                    </ul>
                </li> --}}
                {{-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="layananDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        LAYANAN
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="layananDropdown">
                        <li><a class="dropdown-item" href="{{ url('aproval') }}">APROVAL KAPRODI</a></li>
                        <li><a class="dropdown-item" href="{{ url('layanans') }}">KAPRODI</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('documents.index') }}">📄 E-SIGNATURE</a></li>
                        <li><a class="dropdown-item" href="{{ route('signatures.index') }}">🖋️ TANDA TANGAN</a></li>
                        <li><a class="dropdown-item" href="{{ route('validation.index') }}">✅ VALIDASI</a></li>
                    </ul>
                </li> --}}

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="layananDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AGENDA
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="layananDropdown">
                        <li><a class="dropdown-item" href="{{ url('events') }}">JADWAL</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="risetDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        RISET DAN PENGABDIAN
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="risetDropdown">
                        <li><a class="dropdown-item" href="{{ url('penelitian') }}">DAFTAR PENELITIAN</a></li>
                        <li><a class="dropdown-item" href="{{ url('pengabdian') }}">DAFTAR PENGABDIAN</a></li>
                        <li><a class="dropdown-item" href="{{ url('sinta') }}">PROFIL SINTA</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="infoDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        INFO PENTING
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="infoDropdown">
                        <li><a class="dropdown-item" href="{{ url('sorotan') }}">TERKINI</a></li>
                        <li><a class="dropdown-item" href="{{ url('lomba') }}">LOMBA</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="akademikDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        AKADEMIK
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="akademikDropdown">
                        <li><a class="dropdown-item" href="{{ url('kurikulum-rps')}}">KURIKULUM & RPS</a></li>
                        <li><a class="dropdown-item" href="#">CAPSTONE</a></li>
                        <li><a class="dropdown-item" href="{{ url('luaran-obe')}}">LUARAN OBE</a></li>
                        <li><a class="dropdown-item" href="{{ url('dosen-pembimbing-akademik') }}">DOSEN PEMBIMBING AKADEMIK</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" aria-current="page" href="{{ url('download') }}">DOWNLOAD</a>
                </li>
            </ul>

            <div class="navbar-nav ms-auto">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-md login btn-outline-primary">Login</a>
                @endguest
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            @auth
                                {{ Auth::user()->name }}
                            @endauth
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ '/logout' }}">Logout</a>
                            </li>
                            {{-- <li>
                                <hr class="dropdown-divider">
                            </li> --}}
                        </ul>
                    </li>
                @endauth
            </div>

        </div>
    </div>
</nav>
