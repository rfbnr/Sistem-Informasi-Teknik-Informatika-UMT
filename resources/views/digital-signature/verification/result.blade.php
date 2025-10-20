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
    <title>Hasil Verifikasi - Tanda Tangan Digital</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                        <div class="result-header {{ $verificationResult['is_valid'] ? 'valid' : 'invalid' }}">
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
                                    <div class="info-card">
                                        <h5 class="mb-3">
                                            <i class="fas fa-file-alt text-primary"></i> Informasi Dokumen
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Nama Dokumen:</strong><br>
                                                {{ $document->document_name }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Nomor Dokumen:</strong><br>
                                                {{-- {{ $document->full_document_number }} --}}
                                            </div>
                                        </div>
                                        <div class="row mt-2">
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
                                        <button class="btn btn-download btn-block w-100" onclick="downloadCertificate()">
                                            <i class="fas fa-certificate"></i> Download Sertifikat
                                        </button>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <button class="btn btn-outline-primary btn-block w-100" onclick="window.print()">
                                            <i class="fas fa-print"></i> Cetak Hasil
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        function downloadCertificate() {
            @if($verificationResult['is_valid'])
                // Construct certificate download URL
                const token = '{{ request()->route('token') ?? '' }}';
                if (token) {
                    window.open(`{{ route('signature.certificate', '') }}/${token}`, '_blank');
                } else {
                    alert('Token verifikasi tidak ditemukan untuk download sertifikat');
                }
            @else
                alert('Sertifikat hanya tersedia untuk dokumen yang terverifikasi valid');
            @endif
        }

        // Auto-focus pada tombol utama
        $(document).ready(function() {
            @if($verificationResult['is_valid'])
                $('button:contains("Download Sertifikat")').focus();
            @else
                $('a:contains("Verifikasi Lain")').focus();
            @endif
        });

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
