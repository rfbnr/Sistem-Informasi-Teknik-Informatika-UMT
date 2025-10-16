{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') | Kaprodi Informatika UMT</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #05184E;
            --secondary-color: #f8f9fa;
            --accent-color: #17a2b8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a2470 100%);
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar .logo {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar .logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-left-color: #fff;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .user-menu .dropdown-toggle::after {
            display: none;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #0a2470;
            border-color: #0a2470;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a2470 100%);
        }

        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 260px;
            z-index: 1001;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .sidebar-toggle.collapsed {
            left: 80px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                left: 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo">
            <img src="{{ url('assets/logo.png') }}" alt="Logo" class="mb-2">
            <div class="text-white fw-bold sidebar-text">KAPRODI</div>
            <div class="text-white-50 small sidebar-text">Informatika UMT</div>
        </div>

        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.dashboard') ? 'active' : '' }}"
                   href="{{ route('kaprodi.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.signatures.*') ? 'active' : '' }}"
                   href="{{ route('kaprodi.signatures.index') }}">
                    <i class="fas fa-signature"></i>
                    <span class="sidebar-text">Tanda Tangan</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.signatures.pending') ? 'active' : '' }}"
                   href="{{ route('kaprodi.signatures.pending') }}">
                    <i class="fas fa-clock"></i>
                    <span class="sidebar-text">Pending</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.signatures.urgent') ? 'active' : '' }}"
                   href="{{ route('kaprodi.signatures.urgent') }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="sidebar-text">Urgent</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.blockchain.*') ? 'active' : '' }}"
                   href="{{ route('kaprodi.blockchain.transactions') }}">
                    <i class="fas fa-link"></i>
                    <span class="sidebar-text">Blockchain</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.reports.*') ? 'active' : '' }}"
                   href="{{ route('kaprodi.reports.monthly') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="sidebar-text">Laporan</span>
                </a>
            </li>

            <li class="nav-item mt-4">
                <hr class="text-white-50">
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}">
                    <i class="fas fa-home"></i>
                    <span class="sidebar-text">Portal Utama</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-text">Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark">@yield('title', 'Kaprodi Dashboard')</h5>
                        <small class="text-muted">{{ now()->format('l, d F Y') }}</small>
                    </div>

                    <div class="user-menu">
                        <div class="dropdown">
                            <a class="btn btn-link text-decoration-none p-0" href="#" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2">
                                        {{ substr(auth()->user()->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div class="text-start">
                                        <div class="fw-semibold text-dark">{{ auth()->user()->name ?? 'Kaprodi' }}</div>
                                        <small class="text-muted">Kepala Program Studi</small>
                                    </div>
                                    <i class="fas fa-chevron-down ms-2 text-muted"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ url('/') }}"><i class="fas fa-home me-2"></i>Portal Utama</a></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="container-fluid pt-3">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="container-fluid pt-3">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        console.log('Kaprodi Dashboard Loaded');
        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarToggle').click(function() {
                $('#sidebar').toggleClass('collapsed');
                $('#mainContent').toggleClass('expanded');
                $(this).toggleClass('collapsed');
            });

            // Mobile sidebar toggle
            $('.mobile-menu-toggle').click(function() {
                $('#sidebar').toggleClass('show');
            });

            // Close sidebar on mobile when clicking outside
            $(document).click(function(e) {
                if ($(window).width() <= 768) {
                    if (!$(e.target).closest('#sidebar, #sidebarToggle').length) {
                        $('#sidebar').removeClass('show');
                    }
                }
            });

            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>

</html>

 --}}



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') | Kaprodi Informatika UMT</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #05184E;
            --secondary-color: #f8f9fa;
            --accent-color: #17a2b8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a2470 100%);
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar .logo {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar .logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-left-color: #fff;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .user-menu .dropdown-toggle::after {
            display: none;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #0a2470;
            border-color: #0a2470;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a2470 100%);
        }

        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 260px;
            z-index: 1001;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .sidebar-toggle.collapsed {
            left: 80px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                left: 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo">
            <img src="{{ url('assets/logo.png') }}" alt="Logo" class="mb-2">
            <div class="text-white fw-bold sidebar-text">KAPRODI</div>
            <div class="text-white-50 small sidebar-text">Informatika UMT</div>
        </div>

        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.dashboard') ? 'active' : '' }}"
                   href="{{ route('kaprodi.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.signatures.*') ? 'active' : '' }}"
                   href="{{ route('kaprodi.signatures.index') }}">
                    <i class="fas fa-signature"></i>
                    <span class="sidebar-text">Tanda Tangan</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.signatures.pending') ? 'active' : '' }}"
                   href="{{ route('kaprodi.signatures.pending') }}">
                    <i class="fas fa-clock"></i>
                    <span class="sidebar-text">Pending</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.signatures.urgent') ? 'active' : '' }}"
                   href="{{ route('kaprodi.signatures.urgent') }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="sidebar-text">Urgent</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.blockchain.*') ? 'active' : '' }}"
                   href="{{ route('kaprodi.blockchain.transactions') }}">
                    <i class="fas fa-link"></i>
                    <span class="sidebar-text">Blockchain</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kaprodi.reports.*') ? 'active' : '' }}"
                   href="{{ route('kaprodi.reports.monthly') }}">
                    <i class="fas fa-chart-bar"></i>
                    <span class="sidebar-text">Laporan</span>
                </a>
            </li>

            <li class="nav-item mt-4">
                <hr class="text-white-50">
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ url('/') }}">
                    <i class="fas fa-home"></i>
                    <span class="sidebar-text">Portal Utama</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-text">Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark">@yield('title', 'Kaprodi Dashboard')</h5>
                        <small class="text-muted">{{ now()->format('l, d F Y') }}</small>
                    </div>

                    <div class="user-menu">
                        <div class="dropdown">
                            <a class="btn btn-link text-decoration-none p-0" href="#" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2">
                                        {{ substr(auth()->user()->name ?? 'K', 0, 1) }}
                                    </div>
                                    <div class="text-start">
                                        <div class="fw-semibold text-dark">{{ auth()->user()->name ?? 'Kaprodi' }}</div>
                                        <small class="text-muted">Kepala Program Studi</small>
                                    </div>
                                    <i class="fas fa-chevron-down ms-2 text-muted"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ url('/') }}"><i class="fas fa-home me-2"></i>Portal Utama</a></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="container-fluid pt-3">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="container-fluid pt-3">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        console.log('Kaprodi Dashboard Loaded');
        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarToggle').click(function() {
                $('#sidebar').toggleClass('collapsed');
                $('#mainContent').toggleClass('expanded');
                $(this).toggleClass('collapsed');
            });

            // Mobile sidebar toggle
            $('.mobile-menu-toggle').click(function() {
                $('#sidebar').toggleClass('show');
            });

            // Close sidebar on mobile when clicking outside
            $(document).click(function(e) {
                if ($(window).width() <= 768) {
                    if (!$(e.target).closest('#sidebar, #sidebarToggle').length) {
                        $('#sidebar').removeClass('show');
                    }
                }
            });

            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>

</html>



