<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Error - Digital Signature</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="{{ url('assets/logo.JPG') }}" rel="icon">
    <link href="{{ url('assets/logo.JPG') }}" rel="apple-touch-icon">

    <style>
        body {
            background: linear-gradient(135deg, #0056b3 0%, #0056b3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            max-width: 700px;
            width: 100%;
            padding: 2rem;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .error-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .error-icon {
            font-size: 4.5rem;
            margin-bottom: 1rem;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-body {
            padding: 2rem;
        }

        .error-details {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.25rem;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }

        .error-code-box {
            background: #f8f9fa;
            border: 2px dashed #dc3545;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            margin: 1.5rem 0;
        }

        .error-code {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
            font-family: 'Courier New', monospace;
        }

        .suggestions-box {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 1.25rem;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }

        .suggestions-box ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .suggestions-box li {
            margin-bottom: 0.75rem;
            line-height: 1.6;
        }

        .suggestions-box li:last-child {
            margin-bottom: 0;
        }

        .action-section {
            background: #f8f9fa;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .btn-group-custom {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-group-custom .btn {
            flex: 1;
            min-width: 180px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #0056b3 0%, #004494 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 86, 179, 0.4);
            color: white;
        }

        .btn-secondary-custom {
            background: white;
            border: 2px solid #0056b3;
            color: #0056b3;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            background: #0056b3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 86, 179, 0.3);
        }

        .help-section {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin: 1.5rem 0;
        }

        .help-section a {
            color: white;
            text-decoration: underline;
            font-weight: 600;
        }

        .help-section a:hover {
            color: #e3f2fd;
        }

        .university-logo {
            max-width: 70px;
            height: auto;
            margin-bottom: 0.75rem;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 1.5rem 0;
        }

        .technical-info {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .technical-info small {
            color: #6c757d;
            font-family: 'Courier New', monospace;
        }

        .footer-info {
            text-align: center;
            margin-top: 1.5rem;
        }

        .footer-info small {
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <!-- Error Header -->
            <div class="error-header">
                <img src="{{ asset('assets/logo.JPG') }}" alt="Logo UMT" class="university-logo">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="mb-2">Verification Error</h2>
                <p class="mb-0">Gagal Memverifikasi Tanda Tangan Digital</p>
            </div>

            <!-- Error Body -->
            <div class="error-body">
                <!-- Error Code -->
                @if(isset($errorCode))
                <div class="error-code-box">
                    <small class="text-muted d-block mb-1">Error Code</small>
                    <div class="error-code">{{ $errorCode }}</div>
                </div>
                @endif

                <!-- Error Details -->
                <div class="error-details">
                    <h6 class="mb-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Detail Error:</strong>
                    </h6>
                    <p class="mb-0">
                        {{ $message ?? 'Terjadi kesalahan saat memverifikasi tanda tangan digital. Link verifikasi mungkin tidak valid atau telah kadaluarsa.' }}
                    </p>
                </div>

                <div class="divider"></div>

                <!-- Possible Causes -->
                <div class="suggestions-box">
                    <h6 class="mb-3">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Kemungkinan Penyebab:</strong>
                    </h6>
                    <ul>
                        <li>
                            <strong>Link Verifikasi Kadaluarsa:</strong> Link verifikasi memiliki masa berlaku tertentu
                        </li>
                        <li>
                            <strong>QR Code atau Token Tidak Valid:</strong> Data yang di-scan mungkin rusak atau tidak sesuai format
                        </li>
                        <li>
                            <strong>Dokumen Telah Dimodifikasi:</strong> Dokumen mungkin telah diubah setelah ditandatangani
                        </li>
                        <li>
                            <strong>Tanda Tangan Tidak Valid:</strong> Tanda tangan digital tidak sesuai dengan sertifikat yang digunakan
                        </li>
                        <li>
                            <strong>Masalah Koneksi:</strong> Koneksi internet tidak stabil saat melakukan verifikasi
                        </li>
                    </ul>
                </div>

                <div class="divider"></div>

                <!-- Action Section -->
                <div class="action-section">
                    <h6 class="mb-3">
                        <i class="fas fa-hand-point-right me-2 text-primary"></i>
                        <strong>Langkah Selanjutnya:</strong>
                    </h6>

                    <div class="btn-group-custom">
                        <a href="{{ route('signature.verify.page') }}" class="btn btn-primary-custom">
                            <i class="fas fa-redo me-2"></i>
                            Coba Verifikasi Lagi
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-secondary-custom">
                            <i class="fas fa-home me-2"></i>
                            Kembali ke Beranda
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Pastikan Anda menggunakan link atau QR code yang terbaru
                        </small>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="help-section">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <i class="fas fa-question-circle fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-2">
                                <strong>Butuh Bantuan?</strong>
                            </h6>
                            <p class="mb-2 small">
                                Jika Anda yakin dokumen ini valid dan seharusnya dapat diverifikasi,
                                silakan hubungi administrasi Prodi Teknik Informatika UMT untuk bantuan lebih lanjut.
                            </p>
                            <p class="mb-0 small">
                                <i class="fas fa-envelope me-2"></i>
                                Email: <a href="mailto:ft@umt.ac.id">ft@umt.ac.id</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Technical Information (Optional) -->
                @if(isset($technicalDetails))
                <div class="technical-info">
                    <details>
                        <summary class="cursor-pointer">
                            <small>
                                <i class="fas fa-code me-1"></i>
                                <strong>Technical Details</strong>
                                (For debugging purposes)
                            </small>
                        </summary>
                        <div class="mt-2">
                            <small class="d-block">
                                <strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s') }}
                            </small>
                            <small class="d-block">
                                <strong>Request ID:</strong> {{ request()->ip() }}_{{ now()->timestamp }}
                            </small>
                            @if(isset($technicalDetails))
                            <small class="d-block">
                                <strong>Details:</strong> {{ $technicalDetails }}
                            </small>
                            @endif
                        </div>
                    </details>
                </div>
                @endif
            </div>
        </div>

        <!-- Footer Info -->
        <div class="footer-info">
            <small>
                <i class="fas fa-shield-alt me-1"></i>
                Digital Signature Verification System - Prodi Teknik Informatika UMT
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
