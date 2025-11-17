@php
    // Helper function untuk label pemeriksaan
    function getCheckLabel($checkName) {
        $labels = [
            'document_exists' => 'Dokumen Ditemukan',
            'digital_signature' => 'Kunci Digital Valid',
            'approval_request' => 'Data Pengajuan Valid',
            'document_integrity' => 'Integritas Dokumen',
            'cms_signature' => 'Tanda Tangan Digital',
            'timestamp' => 'Validitas Waktu',
            'certificate' => 'Sertifikat Digital'
        ];
        return $labels[$checkName] ?? ucfirst(str_replace('_', ' ', $checkName));
    }
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMT | Hasil Verifikasi - Tanda Tangan Digital</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <link href="{{ url('assets/logo.JPG') }}" rel="icon">
    <link href="{{ url('assets/logo.JPG') }}" rel="apple-touch-icon">

    <style>
        body {
            background: linear-gradient(135deg, #0056b3 0%, #0056b3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .result-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .result-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .result-header {
            padding: 2rem;
            text-align: center;
            color: white;
            justify-content: center;
        }

        .result-header.valid {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .result-header.invalid {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .result-body {
            padding: 2rem;
        }

        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .check-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            border-left: 4px solid transparent;
        }

        .check-item.success {
            background: rgba(40, 167, 69, 0.1);
            border-left-color: #28a745;
        }

        .check-item.failed {
            background: rgba(220, 53, 69, 0.1);
            border-left-color: #dc3545;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }

        .btn-download {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
            color: white;
        }

        .verification-details {
            background: white;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }

        .detail-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .university-logo {
            max-width: 60px;
            height: auto;
            margin-bottom: 1rem;
        }

        .certificate-preview {
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            padding: 2rem;
            text-align: center;
            margin-top: 1rem;
        }

        /* Document Preview Styling */
        /* .modal-xl {
            max-width: 80%;
        } */

        /* Compact button styling for document preview */
        .btn-sm.btn-outline-primary,
        .btn-sm.btn-outline-success {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            white-space: nowrap;
        }

        /* Loading indicator animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media print {
            body {
                background: white !important;
            }
            .result-container {
                min-height: auto;
                padding: 0;
            }
            .btn, .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="result-card">
                        <!-- Header -->
                        <div class="result-header {{ $verificationResult['is_valid'] ? 'valid' : 'invalid' }} justify-content-center d-flex flex-column align-items-center">
                            <img src="{{ asset('assets/logo.JPG') }}" alt="Logo UMT" class="university-logo">

                            @if($verificationResult['is_valid'])
                                <i class="fas fa-check-circle status-icon"></i>
                                <h2 class="mb-2">Dokumen Terverifikasi</h2>
                                <p class="mb-0">Tanda tangan digital valid dan dokumen autentik</p>
                            @else
                                <i class="fas fa-times-circle status-icon"></i>
                                <h2 class="mb-2">Verifikasi Gagal</h2>
                                <p class="mb-0">{{ $verificationResult['message'] }}</p>
                            @endif

                            <div class="mt-3">
                                <small>Verifikasi ID: {{ $verificationResult['verification_id'] }}</small><br>
                                <small>{{ $verificationResult['verified_at']->format('d F Y, H:i:s') }} WIB</small>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="result-body">
                            @if($verificationResult['is_valid'] && isset($verificationResult['details']))
                                <!-- Document Information -->
                                @if(isset($verificationResult['details']['approval_request']))
                                    @php $document = $verificationResult['details']['approval_request']; @endphp
                                    <div class="info-card ">
                                        <h5 class="mb-3">
                                            <i class="fas fa-file-alt text-primary"></i> Informasi Dokumen
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Nama Dokumen:</strong><br>
                                                {{ $document->document_name }}
                                            </div>
                                            {{-- <div class="col-md-6">
                                                <strong>Nomor Dokumen:</strong><br>
                                                {{ $document->full_document_number }}
                                            </div> --}}
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <strong>Tipe Dokumen:</strong><br>
                                                {{ $document->document_type ?? 'Tidak diketahui' }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Status Dokumen:</strong><br>
                                                @if($document->status === 'approved')
                                                    <span class="badge bg-success">Disetujui</span>
                                                @elseif($document->status === 'rejected')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                @elseif($document->status === 'pending')
                                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                                @elseif($document->status === 'cancelled')
                                                    <span class="badge bg-secondary">Dibatalkan</span>
                                                @elseif($document->status === 'user_signed')
                                                    <span class="badge bg-info text-dark">Ditandatangani Pengguna</span>
                                                @elseif($document->status === 'sign_approved')
                                                    <span class="badge bg-primary">Tanda Tangan Disetujui</span>
                                                @else
                                                    <span class="badge bg-secondary">Tidak diketahui</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <strong>Pengaju:</strong><br>
                                                {{ $document->user->name ?? 'Tidak diketahui' }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Tanggal Pengajuan:</strong><br>
                                                {{ $document->created_at->format('d F Y') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Signature Information -->
                                @if(isset($verificationResult['details']['document_signature']))
                                    @php $signature = $verificationResult['details']['document_signature']; @endphp
                                    <div class="info-card">
                                        <h5 class="mb-3">
                                            <i class="fas fa-signature text-success"></i> Informasi Tanda Tangan
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Ditandatangani Oleh:</strong><br>
                                                {{ $signature->signer->name ?? 'Tidak diketahui' }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Tanggal Tanda Tangan:</strong><br>
                                                {{ $signature->signed_at ? $signature->signed_at->format('d F Y, H:i:s') : 'Tidak diketahui' }}
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <strong>Algoritma:</strong><br>
                                                <span class="badge bg-info">{{ $signature->digitalSignature->algorithm ?? 'Tidak diketahui' }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Panjang Kunci:</strong><br>
                                                <span class="badge bg-success">{{ $signature->digitalSignature->key_length ?? 'N/A' }} bit</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Verification Checks -->
                                @if(isset($verificationResult['details']['checks']))
                                    <div class="info-card">
                                        <h5 class="mb-3">
                                            <i class="fas fa-list-check text-info"></i> Detail Verifikasi
                                        </h5>

                                        @foreach($verificationResult['details']['checks'] as $checkName => $check)
                                            <div class="check-item {{ $check['status'] ? 'success' : 'failed' }}">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas {{ $check['status'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} me-2"></i>
                                                    <div class="flex-grow-1">
                                                        <strong>{{ getCheckLabel($checkName) }}</strong>
                                                        @if(isset($check['message']))
                                                            <div class="small text-muted">{{ $check['message'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                @endif

                                <!-- Verification Summary -->
                                @if(isset($verificationResult['details']['verification_summary']))
                                    @php $summary = $verificationResult['details']['verification_summary']; @endphp
                                    <div class="verification-details">
                                        <div class="detail-item">
                                            <span><strong>Status Keseluruhan</strong></span>
                                            <span class="badge {{ $summary['overall_status'] === 'VALID' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $summary['overall_status'] }}
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span>Pemeriksaan Berhasil</span>
                                            <span>{{ $summary['checks_passed'] }} / {{ $summary['total_checks'] }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span>Tingkat Keberhasilan</span>
                                            <span>{{ $summary['success_rate'] }}%</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Warnings -->
                                @if(isset($verificationResult['details']['warnings']) && !empty($verificationResult['details']['warnings']))
                                    <div class="alert alert-warning mt-3">
                                        <h6><i class="fas fa-exclamation-triangle"></i> Peringatan:</h6>
                                        <ul class="mb-0">
                                            @foreach($verificationResult['details']['warnings'] as $warning)
                                                <li>{{ $warning }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="row mt-4">
                                    <div class="col-md-6 mb-2">
                                        <button class="btn btn-primary btn-block w-100" onclick="viewCertificate()">
                                            <i class="fas fa-certificate"></i> Lihat Sertifikat
                                        </button>
                                    </div>
                                    {{-- <div class="col-md-4 mb-2">
                                        <button class="btn btn-outline-success btn-block w-100" onclick="downloadCertificate()">
                                            <i class="fas fa-download"></i> Download PDF
                                        </button>
                                    </div> --}}
                                    <div class="col-md-6 mb-2">
                                        <button class="btn btn-outline-primary btn-block w-100" onclick="previewDocument()"
                                            title="Preview Signed Document">
                                            <i class="fas fa-file-pdf"></i> Lihat Dokumen
                                        </button>
                                    </div>
                                </div>

                            @else
                                <!-- Error Information -->
                                <div class="alert alert-danger">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Verifikasi Gagal</h5>
                                    <p class="mb-0">{{ $verificationResult['message'] }}</p>

                                    @if(isset($verificationResult['details']['error_details']))
                                        <hr>
                                        <small class="text-muted">
                                            Detail: {{ $verificationResult['details']['error_details'] }}
                                        </small>
                                    @endif
                                </div>

                                <div class="text-center">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h5>Kemungkinan Penyebab:</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-times text-danger"></i> QR Code atau token verifikasi tidak valid</li>
                                        <li><i class="fas fa-times text-danger"></i> Dokumen telah dimodifikasi setelah ditandatangani</li>
                                        <li><i class="fas fa-times text-danger"></i> Sertifikat digital telah kedaluwarsa atau dicabut</li>
                                        <li><i class="fas fa-times text-danger"></i> Link verifikasi telah kedaluwarsa</li>
                                    </ul>
                                </div>
                            @endif

                            <!-- Navigation -->
                            <div class="text-center mt-4 no-print">
                                <a href="{{ route('signature.verify.page') }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-arrow-left"></i> Verifikasi Lain
                                </a>
                                <a href="{{ url('/') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-home"></i> Beranda
                                </a>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center pb-3 no-print">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt text-success"></i>
                                Sistem Verifikasi Tanda Tangan Digital - Prodi Teknik Informatika UMT
                            </small>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="row mt-4 no-print">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                    <h6>Tentang Verifikasi</h6>
                                    <small class="text-muted">
                                        Verifikasi dilakukan menggunakan teknologi kriptografi digital yang menjamin
                                        keaslian dan integritas dokumen.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-phone fa-2x text-success mb-2"></i>
                                    <h6>Bantuan</h6>
                                    <small class="text-muted">
                                        Jika mengalami masalah verifikasi, hubungi admin Prodi Teknik Informatika
                                        atau kunjungi website resmi.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Certificate Viewer Modal -->
    <div class="modal fade" id="certificateModal" tabindex="-1" aria-labelledby="certificateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="certificateModalLabel">
                        <i class="fas fa-certificate me-2"></i>
                        Informasi Sertifikat Digital X.509
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="certificateContent" style="max-height: 80vh; overflow-y: auto;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Memuat informasi sertifikat...</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <small class="text-muted me-auto">
                        <i class="fas fa-shield-alt text-success me-1"></i>
                        Informasi sertifikat untuk verifikasi publik
                    </small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentPreviewModalLabel">
                        <i class="fas fa-file-pdf me-2"></i>
                        <span id="previewTitle">Document Preview</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <div id="pdfLoadingIndicator" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading document...</p>
                    </div>
                    <iframe id="documentPreviewFrame"
                            style="width: 100%; height: 100%; border: none; display: none;"
                            frameborder="0">
                    </iframe>
                    <div id="previewError" class="alert alert-danger m-3" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> <span id="errorMessage">Unable to load document preview.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <a id="downloadDocumentBtn" href="#" class="btn btn-success" target="_blank">
                        <i class="fas fa-download me-1"></i> Download Document
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Auto-focus pada tombol utama
        $(document).ready(function() {
            @if($verificationResult['is_valid'])
                $('button:contains("Lihat Sertifikat")').focus();
            @else
                $('a:contains("Verifikasi Lain")').focus();
            @endif
        });

        @php
            $document = $verificationResult['details']['approval_request'] ?? null;
        @endphp

        function previewDocument() {
            @if($verificationResult['is_valid'])
                const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
                const iframe = document.getElementById('documentPreviewFrame');
                const loading = document.getElementById('pdfLoadingIndicator');
                const errorDiv = document.getElementById('previewError');
                const titleSpan = document.getElementById('previewTitle');
                const downloadBtn = document.getElementById('downloadDocumentBtn');

                // Reset modal state
                iframe.style.display = 'none';
                errorDiv.style.display = 'none';
                loading.style.display = 'block';

                const signedFileName = '{{ $document->signed_document_path ? basename($document->signed_document_path) : "Not Signed Yet" }}';

                // Set title based on type
                titleSpan.textContent = 'Signed Document Preview';

                // Get document path
                const docPath = '{{ $document->signed_document_path ? asset("storage/" . $document->signed_document_path) : "" }}';

                if (!docPath) {
                    loading.style.display = 'none';
                    errorDiv.style.display = 'block';
                    document.getElementById('errorMessage').textContent = 'Document not available for preview.';
                    modal.show();
                    return;
                }

                // Set download button
                downloadBtn.href = docPath;
                downloadBtn.style.display = 'none';

                // Show modal
                modal.show();

                // Load PDF in iframe
                iframe.onload = function() {
                    loading.style.display = 'none';
                    iframe.style.display = 'block';
                };

                iframe.onerror = function() {
                    loading.style.display = 'none';
                    errorDiv.style.display = 'block';
                    document.getElementById('errorMessage').textContent = 'Failed to load document. The file may be corrupted or not accessible.';
                };

                // Set iframe source (add #toolbar=0 to hide PDF toolbar for cleaner view)
                iframe.src = docPath + '#toolbar=0';

                // Fallback timeout in case onload doesn't fire
                setTimeout(function() {
                    if (loading.style.display !== 'none') {
                        loading.style.display = 'none';
                        iframe.style.display = 'block';
                    }
                }, 3000);
            @else
                alert('Dokumen tidak tersedia untuk pratinjau karena verifikasi gagal.');
            @endif
        }

        // Extract token from verification URL
        function extractTokenFromUrl(url)
        {
            const patterns = [
                '\/verify\/([^\/\\?]+)',
                '[\\?&]token=([^&]+)',
                '\/signature\/verify\/([^\/\\?]+)'
            ];

            // looping match pattern
            for (const pattern of patterns) {
                const regex = new RegExp(pattern);
                const matches = url.match(regex);
                if (matches && matches[1]) {
                    return matches[1];
                }
            }

            return null;
        }

        /**
         * ✅ NEW: View X.509 Certificate in Modal (AJAX)
         */
        function viewCertificate() {
            @if($verificationResult['is_valid'])

                // const extractedToken = extractTokenFromUrl('{{ request()->verification_input }}');

                // var token = '{{ request()->route('token') ?? '' }}';

                // if(!token) token = extractedToken;

                const tokenShortCode = '{{ $verificationResult["details"]['short_code_token'] ?? "" }}';

                if (!tokenShortCode) {
                    alert('Token verifikasi tidak ditemukan');
                    return;
                }

                const modal = new bootstrap.Modal(document.getElementById('certificateModal'));
                modal.show();

                // Fetch certificate details via AJAX
                fetch(`{{ route('signature.certificate.view', '') }}/${tokenShortCode}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCertificateInfo(data.certificate);
                    } else {
                        showCertificateError(data.message || 'Gagal memuat sertifikat');
                    }
                })
                .catch(error => {
                    console.error('Certificate fetch error:', error);
                    showCertificateError('Terjadi kesalahan saat memuat sertifikat');
                });
            @else
                alert('Sertifikat hanya tersedia untuk dokumen yang terverifikasi valid');
            @endif
        }

        /**
         * Display certificate information in modal
         */
        function displayCertificateInfo(cert) {
            // Build validity badge
            let validityBadge = '';
            if (cert.is_expired) {
                validityBadge = '<span class="badge bg-danger ms-2"><i class="fas fa-exclamation-triangle"></i> EXPIRED</span>';
            } else if (cert.is_expiring_soon) {
                validityBadge = `<span class="badge bg-warning ms-2"><i class="fas fa-clock"></i> ${cert.days_remaining} hari lagi</span>`;
            } else {
                validityBadge = `<span class="badge bg-success ms-2"><i class="fas fa-check-circle"></i> Valid</span>`;
            }

            // Build Subject DN
            let subjectDN = `CN=${cert.subject.CN}`;
            if (cert.subject.OU) subjectDN += `, OU=${cert.subject.OU}`;
            if (cert.subject.O) subjectDN += `, O=${cert.subject.O}`;
            if (cert.subject.L) subjectDN += `, L=${cert.subject.L}`;
            if (cert.subject.ST) subjectDN += `, ST=${cert.subject.ST}`;
            if (cert.subject.C) subjectDN += `, C=${cert.subject.C}`;

            // Build Issuer DN
            let issuerDN = `CN=${cert.issuer.CN}`;
            if (cert.issuer.OU) issuerDN += `, OU=${cert.issuer.OU}`;
            if (cert.issuer.O) issuerDN += `, O=${cert.issuer.O}`;
            if (cert.issuer.C) issuerDN += `, C=${cert.issuer.C}`;

            const html = `
                <div class="certificate-details">
                    <!-- Public Notice -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Sertifikat Digital X.509</h6>
                                <small>Informasi ini ditampilkan untuk keperluan verifikasi publik. Data sensitif tidak ditampilkan untuk melindungi privasi.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Informasi Dasar</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Version:</strong></div>
                                <div class="col-md-8"><span class="badge bg-secondary">X.509 v${cert.version}</span></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Serial Number:</strong></div>
                                <div class="col-md-8">
                                    <code class="text-primary">${cert.serial_number}</code>
                                    <small class="text-muted d-block">Sebagian nomor seri disembunyikan untuk keamanan</small>
                                </div>
                            </div>
                            ${cert.is_revoked ? `
                            <div class="alert alert-danger mt-2 mb-0">
                                <i class="fas fa-ban me-2"></i>
                                <strong>Sertifikat ini telah DICABUT!</strong>
                            </div>` : ''}
                        </div>
                    </div>

                    <!-- Subject (Owner) -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-user-circle me-2"></i>
                            <strong>Subject (Pemilik Sertifikat)</strong>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light mb-3">
                                <small class="text-muted">Distinguished Name (DN):</small><br>
                                <code class="text-dark">${subjectDN}</code>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Common Name (CN):</strong></div>
                                <div class="col-md-8">${cert.subject.CN}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organizational Unit (OU):</strong></div>
                                <div class="col-md-8">${cert.subject.OU}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organization (O):</strong></div>
                                <div class="col-md-8">${cert.subject.O}</div>
                            </div>
                            ${cert.subject.L ? `<div class="row mb-2">
                                <div class="col-md-4"><strong>Locality (L):</strong></div>
                                <div class="col-md-8">${cert.subject.L}</div>
                            </div>` : ''}
                            ${cert.subject.ST ? `<div class="row mb-2">
                                <div class="col-md-4"><strong>State/Province (ST):</strong></div>
                                <div class="col-md-8">${cert.subject.ST}</div>
                            </div>` : ''}
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Country (C):</strong></div>
                                <div class="col-md-8">${cert.subject.C}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Issuer -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-building me-2"></i>
                            <strong>Issuer (Penerbit Sertifikat)</strong>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light mb-3">
                                <small class="text-muted">Distinguished Name (DN):</small><br>
                                <code class="text-dark">${issuerDN}</code>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Common Name (CN):</strong></div>
                                <div class="col-md-8">${cert.issuer.CN}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organizational Unit (OU):</strong></div>
                                <div class="col-md-8">${cert.issuer.OU || 'N/A'}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Organization (O):</strong></div>
                                <div class="col-md-8">${cert.issuer.O}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Country (C):</strong></div>
                                <div class="col-md-8">${cert.issuer.C}</div>
                            </div>
                            ${cert.is_self_signed ? `
                            <div class="alert alert-info mt-2 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <small><strong>Self-Signed Certificate</strong> - Issuer dan Subject sama (sertifikat ditandatangani sendiri)</small>
                            </div>` : ''}
                        </div>
                    </div>

                    <!-- Validity Period -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Periode Validitas</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Valid From:</strong></div>
                                <div class="col-md-8">
                                    <i class="fas fa-calendar-check text-success me-2"></i>
                                    ${cert.valid_from}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Valid Until:</strong></div>
                                <div class="col-md-8">
                                    <i class="fas fa-calendar-times text-danger me-2"></i>
                                    ${cert.valid_until}
                                    ${validityBadge}
                                </div>
                            </div>
                            ${cert.days_remaining >= 0 ? `
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Sisa Waktu:</strong></div>
                                <div class="col-md-8">
                                    <strong class="${cert.days_remaining <= 7 ? 'text-danger' : (cert.days_remaining <= 30 ? 'text-warning' : 'text-success')}">${cert.days_remaining} hari</strong>
                                </div>
                            </div>` : `
                            <div class="alert alert-danger mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Sertifikat ini telah EXPIRED!</strong>
                            </div>`}
                        </div>
                    </div>

                    <!-- Cryptographic Algorithms -->
                    <div class="card mb-3">
                        <div class="card-header bg-dark text-white">
                            <i class="fas fa-lock me-2"></i>
                            <strong>Algoritma Kriptografi</strong>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Public Key:</strong></div>
                                <div class="col-md-8"><span class="badge bg-primary">${cert.public_key_algorithm}</span></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Signature:</strong></div>
                                <div class="col-md-8"><span class="badge bg-primary">${cert.signature_algorithm}</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fingerprint (Masked) -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-fingerprint me-2"></i>
                            <strong>Certificate Fingerprint (Partial)</strong>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>SHA-256 (Masked):</strong><br>
                                <code class="d-block bg-light p-2 rounded" style="word-break: break-all; font-size: 11px;">${cert.fingerprint_sha256}</code>
                                <small class="text-muted">Sebagian fingerprint disembunyikan untuk keamanan</small>
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="alert alert-success mb-0">
                        <h6 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>Informasi Keamanan</h6>
                        <hr>
                        <ul class="mb-0 small">
                            <li>Sertifikat ini digunakan untuk memverifikasi tanda tangan digital pada dokumen</li>
                            <li>Fingerprint dapat digunakan untuk memverifikasi keaslian sertifikat</li>
                            <li>Beberapa informasi sensitif tidak ditampilkan untuk melindungi privasi</li>
                            <li>Sertifikat ini menggunakan enkripsi RSA yang aman</li>
                        </ul>
                    </div>
                </div>
            `;

            document.getElementById('certificateContent').innerHTML = html;
        }

        /**
         * Show error in certificate modal
         */
        function showCertificateError(message) {
            document.getElementById('certificateContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error:</strong> ${message}
                </div>
            `;
        }

        // Share result functionality
        function shareResult() {
            if (navigator.share) {
                navigator.share({
                    title: 'Hasil Verifikasi Tanda Tangan Digital',
                    text: 'Status: {{ $verificationResult['is_valid'] ? 'Valid' : 'Invalid' }}',
                    url: window.location.href
                });
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link hasil verifikasi telah disalin ke clipboard');
                });
            }
        }
    </script>
</body>
</html>
