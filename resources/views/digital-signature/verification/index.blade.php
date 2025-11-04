<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>UMT | Verifikasi Tanda Tangan Digital - Prodi Teknik Informatika</title>

    <link href="{{ url('assets/logo.JPG') }}" rel="icon">
    <link href="{{ url('assets/logo.JPG') }}" rel="apple-touch-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/signature-interface.css') }}" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0056b3 0%, #0056b3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .verification-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .verification-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .verification-header {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            color: white;
            border-radius: 1rem 1rem 0 0;
            padding: 2rem;
            text-align: center;
        }

        .verification-body {
            padding: 2rem;
        }

        .input-group-text {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
        }

        .btn-verify {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .verification-methods {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .method-button {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .method-button:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .method-button.active {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .qr-scanner {
            text-align: center;
            padding: 2rem;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .university-logo {
            max-width: 80px;
            height: auto;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="verification-card">
                        <!-- Header -->
                        <div class="verification-header">
                            <img src="{{ asset('assets/logo.JPG') }}" alt="Logo UMT" class="university-logo">
                            <h2 class="mb-2">Verifikasi Tanda Tangan Digital</h2>
                            <p class="mb-0">Program Studi Teknik Informatika</p>
                            <small>Fakultas Teknik - Universitas Muhammadiyah Tangerang</small>
                        </div>

                        <!-- Body -->
                        <div class="verification-body">
                            <!-- Verification Methods -->
                            <div class="verification-methods">
                                <h6 class="mb-3 font-weight-bold">Pilih Metode Verifikasi:</h6>

                                <div class="method-button" data-method="qr" onclick="selectMethod('qr')">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-qrcode fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Scan QR Code</h6>
                                            <small class="text-muted">Scan QR code dari dokumen yang telah ditandatangani</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="method-button" data-method="url" onclick="selectMethod('url')">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-link fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">URL Verifikasi</h6>
                                            <small class="text-muted">Masukkan URL verifikasi yang diberikan</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- <div class="method-button" data-method="token" onclick="selectMethod('token')">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-key fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Token Verifikasi</h6>
                                            <small class="text-muted">Masukkan token verifikasi langsung</small>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>

                            <!-- Verification Form -->
                            <form id="verificationForm" action="{{ route('signature.verify.public') }}" method="POST">
                                @csrf
                                <input type="hidden" name="verification_type" id="verificationType" value="qr">

                                <!-- QR Scanner -->
                                <div id="qrSection" class="verification-section">
                                    <div class="qr-scanner">
                                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                                        <h6>Scan QR Code</h6>
                                        <p class="text-muted mb-3">Arahkan kamera ke QR code pada dokumen</p>
                                        <button type="button" class="btn btn-outline-primary" id="startQRScanner">
                                            <i class="fas fa-camera"></i> Mulai Scan
                                        </button>
                                        <div id="qrReader" style="display: none; margin-top: 1rem;"></div>
                                        <!-- Hidden input for QR data -->
                                        <input type="hidden" id="qrInput" name="verification_input" data-method="qr">
                                    </div>
                                </div>

                                <!-- URL Input -->
                                <div id="urlSection" class="verification-section" style="display: none;">
                                    <div class="form-group mb-3">
                                        <label for="verificationUrl" class="form-label font-weight-bold">URL Verifikasi:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-link"></i>
                                            </span>
                                            <input type="url" class="form-control" id="verificationUrl" name="verification_input"
                                                   placeholder="https://example.com/signature/verify/..." disabled data-method="url">
                                        </div>
                                        <small class="text-muted">Paste URL verifikasi yang Anda terima</small>
                                    </div>
                                </div>

                                <!-- Token Input -->
                                {{-- <div id="tokenSection" class="verification-section" style="display: none;">
                                    <div class="form-group mb-3">
                                        <label for="verificationToken" class="form-label font-weight-bold">Token Verifikasi:</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="text" class="form-control" id="verificationToken" name="verification_input"
                                                   placeholder="Masukkan token verifikasi" disabled data-method="token">
                                        </div>
                                        <small class="text-muted">Token berupa kombinasi huruf dan angka</small>
                                    </div>
                                </div> --}}

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-verify" id="verifyButton" disabled>
                                        <i class="fas fa-shield-alt"></i> Verifikasi Dokumen
                                    </button>
                                </div>
                            </form>

                            <!-- Loading State -->
                            <div id="loadingState" style="display: none;" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Memverifikasi dokumen...</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center pb-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt text-success"></i>
                                Sistem verifikasi aman menggunakan teknologi kriptografi digital
                            </small>
                        </div>
                    </div>

                    <!-- Info Cards -->
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-lock fa-2x text-primary mb-2"></i>
                                    <h6>Aman & Terpercaya</h6>
                                    <small class="text-muted">Menggunakan enkripsi tingkat militer</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x text-success mb-2"></i>
                                    <h6>Verifikasi Instant</h6>
                                    <small class="text-muted">Hasil verifikasi dalam hitungan detik</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 bg-light border-0">
                                <div class="card-body text-center">
                                    <i class="fas fa-certificate fa-2x text-warning mb-2"></i>
                                    <h6>Sertifikat Digital</h6>
                                    <small class="text-muted">Download sertifikat verifikasi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (load first) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- QR Scanner Library (load after jQuery) -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        let html5QrCode;
        let currentMethod = 'qr';

        function selectMethod(method) {
            currentMethod = method;

            // Update active state
            $('.method-button').removeClass('active');
            $(`.method-button[data-method="${method}"]`).addClass('active');

            // Hide all sections
            $('.verification-section').hide();

            // Show selected section
            $(`#${method}Section`).show();

            // Update form
            $('#verificationType').val(method);

            // CRITICAL FIX: Disable all inputs first, then enable only the active one
            $('input[name="verification_input"]').prop('disabled', true).val('');

            // Enable only the input for the selected method
            $(`input[name="verification_input"][data-method="${method}"]`).prop('disabled', false);

            // Disable verify button until input is filled
            $('#verifyButton').prop('disabled', true);

            // Stop QR scanner if switching away
            if (method !== 'qr' && html5QrCode) {
                try {
                    html5QrCode.stop().then(() => {
                        console.log('QR scanner stopped successfully');
                        $('#qrReader').hide();
                        $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');
                        html5QrCode = null; // Clear the instance
                    }).catch(err => {
                        console.log('QR scanner already stopped or error:', err);
                        $('#qrReader').hide();
                        $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');
                        html5QrCode = null;
                    });
                } catch (err) {
                    console.log('Error stopping QR scanner:', err);
                    $('#qrReader').hide();
                    $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');
                    html5QrCode = null;
                }
            }
        }

        // QR Code Scanner
        $('#startQRScanner').click(function() {
            // Check if Html5Qrcode is available
            if (typeof Html5Qrcode === 'undefined') {
                alert('Library QR Scanner belum siap. Mohon refresh halaman dan coba lagi.');
                console.error('Html5Qrcode is not defined. Library not loaded properly.');
                return;
            }

            $('#qrReader').show();
            $(this).prop('disabled', true).text('Scanning...');

            try {
                html5QrCode = new Html5Qrcode("qrReader");

                html5QrCode.start(
                    { facingMode: "environment" }, // Use back camera
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    },
                    (decodedText, decodedResult) => {
                        // QR Code successfully scanned
                        console.log(`QR Code detected: ${decodedText}`);

                        // Set the scanned data to QR input (not extract, send full data)
                        $('#qrInput').val(decodedText);
                        $('#verifyButton').prop('disabled', false);

                        // Stop scanning
                        html5QrCode.stop().then(() => {
                            $('#qrReader').hide();
                            $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');

                            // Auto-submit or show confirmation
                            if (confirm('QR Code berhasil discan. Lanjutkan verifikasi?')) {
                                $('#verificationForm').submit();
                            }
                        }).catch(err => {
                            console.log('Error stopping scanner:', err);
                            $('#qrReader').hide();
                            $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');
                        });
                    },
                    (errorMessage) => {
                        // QR Code scan error (this is normal during scanning)
                        // Don't log every frame error to avoid console spam
                    }
                ).catch(err => {
                    console.error('Camera access error:', err);

                    // let errorMsg = 'Tidak dapat mengakses kamera. ';
                    let errorMsg = err;

                    if (err.name === 'NotAllowedError') {
                        errorMsg += 'Mohon berikan izin akses kamera pada browser Anda.';
                    } else if (err.name === 'NotFoundError') {
                        errorMsg += 'Kamera tidak ditemukan pada perangkat Anda.';
                    } else if (err.name === 'NotReadableError') {
                        errorMsg += 'Kamera sedang digunakan oleh aplikasi lain.';
                    } else if (err.name === 'OverconstrainedError') {
                        errorMsg += 'Kamera tidak mendukung mode yang diminta.';
                    } else if (err.name === 'SecurityError') {
                        errorMsg += 'Akses kamera diblokir karena alasan keamanan. Pastikan menggunakan HTTPS.';
                    }
                    //  else {
                    //     errorMsg += err.message || 'Error tidak diketahui.';
                    // }

                    alert(errorMsg);
                    $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');
                    $('#qrReader').hide();
                });
            } catch (err) {
                console.error('Error initializing QR scanner:', err);
                alert('Gagal menginisialisasi QR scanner. Mohon refresh halaman dan coba lagi.');
                $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');
                $('#qrReader').hide();
            }
        });

        // Input validation - only for visible/enabled inputs
        $('input[name="verification_input"]').on('input', function() {
            // Only validate if this input is enabled (active method)
            if (!$(this).prop('disabled')) {
                const value = $(this).val().trim();
                $('#verifyButton').prop('disabled', value.length === 0);
            }
        });

        // Form submission
        $('#verificationForm').on('submit', function(e) {
            // Validate that we have input in the active method
            const activeInput = $('input[name="verification_input"]:not(:disabled)');
            const value = activeInput.val().trim();

            if (value.length === 0) {
                e.preventDefault();
                alert('Mohon masukkan data verifikasi terlebih dahulu.');
                return false;
            }

            // Show loading state
            $('#loadingState').show();
            $('#verifyButton').prop('disabled', true);
            $('.verification-section').hide();
        });

        // Check if Html5Qrcode library is loaded
        function checkLibraryLoaded() {
            return new Promise((resolve) => {
                if (typeof Html5Qrcode !== 'undefined') {
                    resolve(true);
                } else {
                    let attempts = 0;
                    const maxAttempts = 50; // 5 seconds max wait
                    const checkInterval = setInterval(() => {
                        attempts++;
                        if (typeof Html5Qrcode !== 'undefined') {
                            clearInterval(checkInterval);
                            resolve(true);
                        } else if (attempts >= maxAttempts) {
                            clearInterval(checkInterval);
                            resolve(false);
                        }
                    }, 100);
                }
            });
        }

        // Initialize default method
        $(document).ready(function() {
            selectMethod('qr');

            // Check if QR library is loaded
            checkLibraryLoaded().then((loaded) => {
                if (!loaded) {
                    console.error('Html5Qrcode library failed to load');
                    $('#startQRScanner').prop('disabled', true)
                        .html('<i class="fas fa-exclamation-triangle"></i> Library Gagal Dimuat')
                        .addClass('btn-danger');

                    // Show error message
                    $('<div class="alert alert-warning mt-2" role="alert">')
                        .html('<i class="fas fa-exclamation-triangle"></i> <strong>Peringatan:</strong> Library QR Scanner gagal dimuat. Silakan gunakan metode URL Verifikasi sebagai alternatif atau refresh halaman.')
                        .insertAfter('#qrReader');
                } else {
                    console.log('Html5Qrcode library loaded successfully');
                }
            });
        });

        // Handle browser back button
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                $('#loadingState').hide();
                $('#verifyButton').prop('disabled', false);
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (html5QrCode) {
                try {
                    html5QrCode.stop().catch(err => console.log('Cleanup error:', err));
                } catch (err) {
                    console.log('Cleanup error:', err);
                }
            }
        });
    </script>

    @if(session('error'))
    <script>
        $(document).ready(function() {
            $('#loadingState').hide();
            $('#verifyButton').prop('disabled', false);
            alert('{{ session('error') }}');
        });
    </script>
    @endif
</body>
</html>
