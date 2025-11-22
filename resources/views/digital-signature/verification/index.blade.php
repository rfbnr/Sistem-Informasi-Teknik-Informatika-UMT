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
    {{-- <link href="{{ asset('assets/css/signature-interface.css') }}" rel="stylesheet"> --}}

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

        .upload-area {
            background: #f8f9fa;
            border: 2px dashed #dee2e6 !important;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: #667eea !important;
            background: #f8f9ff;
        }

        .upload-area.drag-over {
            border-color: #28a745 !important;
            background: #f0fff4;
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

                                <div class="method-button" data-method="upload" onclick="selectMethod('upload')">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Upload PDF</h6>
                                            <small class="text-muted">Upload file PDF yang sudah ditandatangani untuk diverifikasi</small>
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
                            <form id="verificationForm" action="{{ route('signature.verify.public') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="verification_type" id="verificationType" value="qr">

                                <!-- QR Scanner -->
                                <div id="qrSection" class="verification-section mb-3">
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

                                        {{-- ✅ NEW: URL Preview --}}
                                        <div id="urlPreview" class="mt-2" style="display: none;">
                                            <div class="alert alert-info py-2 mb-0">
                                                <small>
                                                    <strong><i class="fas fa-check-circle"></i> Detected:</strong> <span id="previewType"></span><br>
                                                    <strong><i class="fas fa-key"></i> Token:</strong> <code id="previewToken" class="bg-white px-1"></code>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PDF Upload Section -->
                                <div id="uploadSection" class="verification-section" style="display: none;">
                                    <div class="form-group mb-3">
                                        <label for="pdfFile" class="form-label font-weight-bold">Upload File PDF:</label>
                                        <div class="upload-area border rounded p-4 text-center" id="uploadArea">
                                            <input type="file" class="d-none" id="pdfFile" name="pdf_file" accept="application/pdf" disabled data-method="upload">
                                            <div id="uploadPlaceholder">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                                <h6>Drag & Drop PDF atau Klik untuk Upload</h6>
                                                <p class="text-muted mb-2">File PDF yang sudah ditandatangani digital</p>
                                                <button type="button" class="btn btn-outline-primary btn-sm" id="selectFileBtn">
                                                    <i class="fas fa-folder-open"></i> Pilih File
                                                </button>
                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i> Maks. 10MB | Format: PDF
                                                    </small>
                                                </div>
                                            </div>
                                            <div id="uploadPreview" style="display: none;">
                                                <div class="alert alert-info mb-0">
                                                    <div class="align-items-center justify-content-between">
                                                        <div>
                                                            <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                                            <span id="fileName" class="font-weight-bold d-inline-block text-truncate" style="max-width: 150px;"></span>
                                                            <br>
                                                            <small class="text-muted" id="fileSize"></small>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger mt-3" onclick="clearUpload()">
                                                            <i class="fas fa-times"></i> Hapus
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-shield-alt text-success"></i>
                                            File Anda akan dianalisa untuk memverifikasi keaslian tanda tangan digital
                                        </small>
                                    </div>
                                </div>

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
                                    <small class="text-muted">Lihat hasil sertifikat verifikasi</small>
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
            if (method === 'upload') {
                $('#pdfFile').prop('disabled', false);
            } else {
                $(`input[name="verification_input"][data-method="${method}"]`).prop('disabled', false);
                $('#pdfFile').prop('disabled', true);
            }

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
        $('#startQRScanner').click(async function() {
            // Check if Html5Qrcode is available
            if (typeof Html5Qrcode === 'undefined') {
                alert('Library QR Scanner belum siap. Mohon refresh halaman dan coba lagi.');
                console.error('Html5Qrcode is not defined. Library not loaded properly.');
                return;
            }

            // ✅ NEW: Request camera permission first
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });

                // Permission granted, release the stream immediately
                stream.getTracks().forEach(track => track.stop());

                // Now start QR scanner
                $('#qrReader').show();
                $(this).prop('disabled', true).text('Scanning...');

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
                    console.error('QR Scanner start error:', err);
                    $('#qrReader').hide();
                    $('#startQRScanner').prop('disabled', false).html('<i class="fas fa-camera"></i> Mulai Scan');

                    let errorMsg = 'Tidak dapat memulai QR scanner. ';
                    errorMsg += err.message || err;
                    alert(errorMsg);
                });

            } catch (err) {
                // ✅ Camera permission denied or error
                console.error('Camera permission error:', err);

                let errorMsg = 'Tidak dapat mengakses kamera. ';

                if (err.name === 'NotAllowedError') {
                    errorMsg += 'Mohon berikan izin akses kamera pada browser Anda.\n\n';
                    errorMsg += 'Cara mengaktifkan:\n';
                    errorMsg += '• Chrome/Edge: Klik ikon kunci di address bar → Site settings → Camera → Allow\n';
                    errorMsg += '• Firefox: Klik ikon kamera di address bar → Allow\n';
                    errorMsg += '• Safari: Settings → Privacy → Camera → Allow for this website';
                } else if (err.name === 'NotFoundError') {
                    errorMsg += 'Kamera tidak ditemukan pada perangkat Anda.';
                } else if (err.name === 'NotReadableError') {
                    errorMsg += 'Kamera sedang digunakan oleh aplikasi lain. Mohon tutup aplikasi lain dan coba lagi.';
                } else if (err.name === 'OverconstrainedError') {
                    errorMsg += 'Kamera tidak mendukung mode yang diminta.';
                } else if (err.name === 'SecurityError') {
                    errorMsg += 'Akses kamera diblokir karena alasan keamanan. Pastikan menggunakan HTTPS.';
                } else {
                    errorMsg += err.message || 'Error tidak diketahui.';
                }

                alert(errorMsg);
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

        // ✅ NEW: URL Preview - Show token extraction (supports short codes)
        $('#verificationUrl').on('input', function() {
            const input = $(this).val().trim();

            if (!input) {
                $('#urlPreview').hide();
                return;
            }

            // Check if URL
            if (input.startsWith('http://') || input.startsWith('https://')) {
                // Pattern 1: Short code format (XXXX-XXXX-XXXX)
                const shortCodePattern = /\/verify\/([A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4})/i;
                const shortCodeMatch = input.match(shortCodePattern);

                if (shortCodeMatch) {
                    const shortCode = shortCodeMatch[1];
                    $('#previewType').html('<span class="badge bg-success">Short Code Detected</span>');
                    $('#previewToken').text(shortCode);
                    $('#urlPreview').show();
                    return;
                }

                // Pattern 2: Full encrypted token
                const patterns = [
                    /\/verify\/([a-zA-Z0-9_\-=+\/]{20,500})/,
                    /[\?&]token=([a-zA-Z0-9_\-=+\/]{20,500})/,
                    /\/signature\/verify\/([a-zA-Z0-9_\-=+\/]{20,500})/
                ];

                for (let pattern of patterns) {
                    const match = input.match(pattern);
                    if (match) {
                        const token = match[1];
                        const displayToken = token.length > 40 ? token.substring(0, 40) + '...' : token;

                        $('#previewType').html('<span class="badge bg-success">URL Verifikasi Valid</span>');
                        $('#previewToken').text(displayToken);
                        $('#urlPreview').show();
                        return;
                    }
                }

                // URL detected but no token found
                $('#previewType').html('<span class="badge bg-warning">URL terdeteksi, tetapi token tidak ditemukan</span>');
                $('#previewToken').text('Tidak ada token');
                $('#urlPreview').show();
            } else {
                // Check if direct short code (XXXX-XXXX-XXXX)
                if (/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i.test(input)) {
                    $('#previewType').html('<span class="badge bg-info">Short Code</span>');
                    $('#previewToken').text(input);
                    $('#urlPreview').show();
                } else {
                    // Direct token
                    const displayToken = input.length > 40 ? input.substring(0, 40) + '...' : input;
                    $('#previewType').html('<span class="badge bg-info">Direct Token</span>');
                    $('#previewToken').text(displayToken);
                    $('#urlPreview').show();
                }
            }
        });

        // PDF File upload handlers
        function clearUpload() {
            $('#pdfFile').val('');
            $('#uploadPlaceholder').show();
            $('#uploadPreview').hide();
            $('#verifyButton').prop('disabled', true);
        }

        // File input change handler
        $('#pdfFile').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handleFileSelect(file);
            }
        });

        // Handle file selection
        function handleFileSelect(file) {
            // Validate file type
            if (file.type !== 'application/pdf') {
                alert('Format file tidak valid! Hanya file PDF yang diperbolehkan.');
                clearUpload();
                return;
            }

            // Validate file size (10MB = 10485760 bytes)
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar! Maksimal 10MB.');
                clearUpload();
                return;
            }

            // Show file preview
            $('#fileName').text(file.name);
            $('#fileSize').text(formatFileSize(file.size));
            $('#uploadPlaceholder').hide();
            $('#uploadPreview').show();
            $('#verifyButton').prop('disabled', false);
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // FIXED: Separate button handler to prevent infinite loop
        $('#selectFileBtn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#pdfFile').trigger('click');
        });

        // FIXED: Upload area click - only trigger if clicking on area itself, not on children
        $('#uploadArea').on('click', function(e) {
            // Only trigger if clicking directly on uploadArea or uploadPlaceholder
            // but NOT on button or its children
            if (e.target === this || $(e.target).closest('#uploadPlaceholder').length && !$(e.target).closest('#selectFileBtn').length) {
                e.preventDefault();
                e.stopPropagation();
                $('#pdfFile').trigger('click');
            }
        });

        $('#uploadArea').on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });

        $('#uploadArea').on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });

        $('#uploadArea').on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');

            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];

                // Set file to input element
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('pdfFile').files = dataTransfer.files;

                handleFileSelect(file);
            }
        });

        // Form submission
        $('#verificationForm').on('submit', function(e) {
            // Check if upload method
            if (currentMethod === 'upload') {
                const fileInput = document.getElementById('pdfFile');
                if (!fileInput.files || fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('Mohon upload file PDF terlebih dahulu.');
                    return false;
                }

                // Change form action for upload
                $(this).attr('action', '{{ route("signature.verify.upload") }}');
            } else {
                // Validate that we have input in the active method
                const activeInput = $('input[name="verification_input"]:not(:disabled)');
                const value = activeInput.val().trim();

                if (value.length === 0) {
                    e.preventDefault();
                    alert('Mohon masukkan data verifikasi terlebih dahulu.');
                    return false;
                }

                // Ensure form action is correct
                $(this).attr('action', '{{ route("signature.verify.public") }}');
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
