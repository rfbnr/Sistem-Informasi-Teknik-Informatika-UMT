@extends('user.layouts.app')

@section('title', 'Validasi Tanda Tangan Digital')

@section('content')
<div class="container py-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-gradient-primary text-white">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shield-check fa-4x mb-4 opacity-75"></i>
                    <h1 class="display-5 fw-bold mb-3">Validasi Tanda Tangan Digital</h1>
                    <p class="lead mb-4">Verifikasi keaslian dan integritas dokumen yang telah ditandatangani secara digital</p>
                    <div class="row text-center mt-4">
                        <div class="col-md-4">
                            <i class="fas fa-file-shield fa-2x mb-2"></i>
                            <div class="small">Integritas File</div>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-signature fa-2x mb-2"></i>
                            <div class="small">Validitas Tanda Tangan</div>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-link fa-2x mb-2"></i>
                            <div class="small">Verifikasi Blockchain</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Validation Methods -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Pilih Metode Validasi</h2>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Validate by Hash -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm validation-card" data-method="hash">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-4 mb-4 d-inline-flex">
                        <i class="fas fa-hashtag text-primary fa-2x"></i>
                    </div>
                    <h4 class="mb-3">Hash Dokumen</h4>
                    <p class="text-muted mb-4">Validasi menggunakan hash SHA-256 dokumen untuk verifikasi integritas dan keaslian tanda tangan</p>
                    <button class="btn btn-primary w-100" onclick="showValidationModal('hash')">
                        <i class="fas fa-search me-2"></i>Validasi dengan Hash
                    </button>
                </div>
            </div>
        </div>

        <!-- Validate by QR Code -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm validation-card" data-method="qr">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 rounded-circle p-4 mb-4 d-inline-flex">
                        <i class="fas fa-qrcode text-success fa-2x"></i>
                    </div>
                    <h4 class="mb-3">QR Code</h4>
                    <p class="text-muted mb-4">Scan atau upload QR code yang terdapat pada dokumen yang telah ditandatangani untuk validasi cepat</p>
                    <button class="btn btn-success w-100" onclick="showValidationModal('qr')">
                        <i class="fas fa-camera me-2"></i>Validasi dengan QR
                    </button>
                </div>
            </div>
        </div>

        <!-- Validate by File -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm validation-card" data-method="file">
                <div class="card-body text-center p-4">
                    <div class="bg-info bg-opacity-10 rounded-circle p-4 mb-4 d-inline-flex">
                        <i class="fas fa-file-upload text-info fa-2x"></i>
                    </div>
                    <h4 class="mb-3">Upload File</h4>
                    <p class="text-muted mb-4">Upload file dokumen untuk validasi otomatis hash dan verifikasi tanda tangan digital</p>
                    <button class="btn btn-info w-100" onclick="showValidationModal('file')">
                        <i class="fas fa-upload me-2"></i>Validasi dengan File
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Tentang Validasi Tanda Tangan Digital
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Proses Validasi Meliputi:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Verifikasi integritas file dokumen
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Validasi keaslian tanda tangan digital
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Pengecekan blockchain transaction
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Verifikasi timestamp dan metadata
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3">Keamanan Terjamin:</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    Enkripsi SHA-256 untuk hash dokumen
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    Blockchain immutable record
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    Multi-layer validation process
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    Tamper-proof verification
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Validations -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-history text-muted me-2"></i>
                        Validasi Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <div id="recentValidations">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-search fa-2x mb-3"></i>
                            <p>Belum ada validasi yang dilakukan</p>
                            <small>Validasi pertama Anda akan muncul di sini</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hash Validation Modal -->
<div class="modal fade" id="hashModal" tabindex="-1" aria-labelledby="hashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hashModalLabel">
                    <i class="fas fa-hashtag text-primary me-2"></i>
                    Validasi dengan Hash Dokumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="hashForm">
                    <div class="mb-3">
                        <label for="documentHash" class="form-label">Hash Dokumen (SHA-256)</label>
                        <textarea class="form-control" id="documentHash" rows="3"
                                  placeholder="Masukkan hash SHA-256 dokumen (64 karakter)..."></textarea>
                        <div class="form-text">Hash dokumen berupa string 64 karakter hexadecimal</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Validasi Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Validation Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">
                    <i class="fas fa-qrcode text-success me-2"></i>
                    Validasi dengan QR Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Scan QR Code</h6>
                        <div id="qrScanner" class="border rounded p-4 text-center">
                            <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Klik untuk mengaktifkan kamera</p>
                            <button class="btn btn-success" onclick="startQRScanner()">
                                <i class="fas fa-camera me-2"></i>Aktifkan Kamera
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Upload QR Code</h6>
                        <form id="qrUploadForm">
                            <div class="mb-3">
                                <input type="file" class="form-control" id="qrFile" accept="image/*">
                                <div class="form-text">Upload gambar QR code dari dokumen</div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload me-2"></i>Upload & Validasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Upload Validation Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileModalLabel">
                    <i class="fas fa-file-upload text-info me-2"></i>
                    Validasi dengan Upload File
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fileUploadForm">
                    <div class="mb-3">
                        <label for="documentFile" class="form-label">File Dokumen</label>
                        <input type="file" class="form-control" id="documentFile" accept=".pdf,.doc,.docx">
                        <div class="form-text">Upload file PDF, DOC, atau DOCX (Maksimal 10MB)</div>
                    </div>
                    <div class="file-info mt-3" id="fileInfo" style="display: none;">
                        <div class="alert alert-info">
                            <h6>Informasi File:</h6>
                            <div id="fileDetails"></div>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-upload me-2"></i>Upload & Validasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Validation Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalLabel">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Hasil Validasi Tanda Tangan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="validationResult">
                    <!-- Results will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="downloadReport">
                    <i class="fas fa-download me-2"></i>Download Laporan
                </button>
                <button type="button" class="btn btn-success" id="shareResult">
                    <i class="fas fa-share me-2"></i>Bagikan Hasil
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6>Memvalidasi...</h6>
                <p class="text-muted mb-0">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>
<script>
let currentValidationHash = null;

$(document).ready(function() {
    // Initialize event listeners
    initializeEventListeners();

    // Load recent validations
    loadRecentValidations();
});

function initializeEventListeners() {
    // Hash validation form
    $('#hashForm').on('submit', function(e) {
        e.preventDefault();
        validateByHash();
    });

    // File upload form
    $('#fileUploadForm').on('submit', function(e) {
        e.preventDefault();
        validateByFile();
    });

    // QR upload form
    $('#qrUploadForm').on('submit', function(e) {
        e.preventDefault();
        validateByQR();
    });

    // File input change
    $('#documentFile').on('change', function() {
        showFileInfo(this.files[0]);
    });

    // Download report
    $('#downloadReport').on('click', function() {
        if (currentValidationHash) {
            downloadValidationReport(currentValidationHash);
        }
    });

    // Share result
    $('#shareResult').on('click', function() {
        shareValidationResult();
    });
}

function showValidationModal(method) {
    if (method === 'hash') {
        $('#hashModal').modal('show');
    } else if (method === 'qr') {
        $('#qrModal').modal('show');
    } else if (method === 'file') {
        $('#fileModal').modal('show');
    }
}

function validateByHash() {
    const hash = $('#documentHash').val().trim();

    if (!hash) {
        showAlert('Masukkan hash dokumen', 'warning');
        return;
    }

    if (hash.length !== 64) {
        showAlert('Hash harus 64 karakter', 'danger');
        return;
    }

    showLoadingModal();
    currentValidationHash = hash;

    $.ajax({
        url: '/validation/hash',
        method: 'POST',
        data: {
            document_hash: hash,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoadingModal();
            $('#hashModal').modal('hide');
            showValidationResult(response);
            addToRecentValidations(response);
        },
        error: function(xhr) {
            hideLoadingModal();
            const error = xhr.responseJSON || { message: 'Terjadi kesalahan' };
            showAlert(error.message, 'danger');
        }
    });
}

function validateByFile() {
    const fileInput = document.getElementById('documentFile');
    const file = fileInput.files[0];

    if (!file) {
        showAlert('Pilih file untuk divalidasi', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    showLoadingModal();

    $.ajax({
        url: '/validation/file',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoadingModal();
            $('#fileModal').modal('hide');
            currentValidationHash = response.document.file_hash;
            showValidationResult(response);
            addToRecentValidations(response);
        },
        error: function(xhr) {
            hideLoadingModal();
            const error = xhr.responseJSON || { message: 'Terjadi kesalahan' };
            showAlert(error.message, 'danger');
        }
    });
}

function validateByQR() {
    const fileInput = document.getElementById('qrFile');
    const file = fileInput.files[0];

    if (!file) {
        showAlert('Pilih gambar QR code', 'warning');
        return;
    }

    // For demo purposes, we'll simulate QR validation
    showLoadingModal();

    setTimeout(() => {
        hideLoadingModal();
        $('#qrModal').modal('hide');
        showAlert('Fitur QR validation akan segera tersedia', 'info');
    }, 2000);
}

function startQRScanner() {
    showAlert('QR Scanner akan segera tersedia', 'info');
}

function showValidationResult(data) {
    let html = '';

    // Overall status
    const statusConfig = {
        'valid': { color: 'success', icon: 'check-circle', text: 'VALID' },
        'invalid': { color: 'danger', icon: 'times-circle', text: 'TIDAK VALID' },
        'file_tampered': { color: 'danger', icon: 'exclamation-triangle', text: 'FILE DIMODIFIKASI' },
        'blockchain_invalid': { color: 'warning', icon: 'link', text: 'BLOCKCHAIN BERMASALAH' },
        'signature_invalid': { color: 'danger', icon: 'signature', text: 'TANDA TANGAN TIDAK VALID' }
    };

    const status = statusConfig[data.validation.overall_status] || statusConfig['invalid'];

    html += `
        <div class="alert alert-${status.color} border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-${status.icon} fa-2x me-3"></i>
                <div>
                    <h4 class="alert-heading mb-1">Status: ${status.text}</h4>
                    <p class="mb-0">Hasil validasi untuk dokumen: ${data.document.title}</p>
                </div>
            </div>
        </div>
    `;

    // Document information
    html += `
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Informasi Dokumen</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-medium">Judul:</td>
                                <td>${data.document.title}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Nama File:</td>
                                <td>${data.document.file_name}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Ukuran:</td>
                                <td>${data.document.file_size || 'N/A'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-medium">Hash File:</td>
                                <td><code class="small">${data.document.file_hash.substring(0, 16)}...</code></td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Tanggal Upload:</td>
                                <td>${data.document.created_at}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Validation details
    html += `
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-check me-2"></i>Detail Validasi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <i class="fas fa-file-shield fa-2x ${data.validation.file_integrity.valid ? 'text-success' : 'text-danger'} mb-2"></i>
                            <h6>Integritas File</h6>
                            <span class="badge bg-${data.validation.file_integrity.valid ? 'success' : 'danger'}">
                                ${data.validation.file_integrity.valid ? 'Valid' : 'Invalid'}
                            </span>
                            <p class="small text-muted mt-2">${data.validation.file_integrity.message}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <i class="fas fa-link fa-2x ${data.validation.blockchain_integrity.valid ? 'text-success' : 'text-danger'} mb-2"></i>
                            <h6>Blockchain</h6>
                            <span class="badge bg-${data.validation.blockchain_integrity.valid ? 'success' : 'danger'}">
                                ${data.validation.blockchain_integrity.valid ? 'Valid' : 'Invalid'}
                            </span>
                            <p class="small text-muted mt-2">${data.validation.blockchain_integrity.message}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <i class="fas fa-signature fa-2x ${data.validation.signatures.valid ? 'text-success' : 'text-danger'} mb-2"></i>
                            <h6>Tanda Tangan</h6>
                            <span class="badge bg-${data.validation.signatures.valid ? 'success' : 'danger'}">
                                ${data.validation.signatures.valid ? 'Valid' : 'Invalid'}
                            </span>
                            <p class="small text-muted mt-2">${data.validation.signatures.message}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Signature details
    if (data.signature_requests && data.signature_requests.length > 0) {
        html += `
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Detail Tanda Tangan</h5>
                </div>
                <div class="card-body">
        `;

        data.signature_requests.forEach(request => {
            html += `
                <div class="mb-4">
                    <h6 class="fw-bold">${request.title}</h6>
                    <p class="text-muted">Pemohon: ${request.requester} | Status: <span class="badge bg-primary">${request.status}</span></p>

                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Penandatangan</th>
                                    <th>Jabatan</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            request.signatures.forEach(signature => {
                html += `
                    <tr>
                        <td>${signature.signer_name}</td>
                        <td>${signature.signer_role}</td>
                        <td>
                            <span class="badge bg-${signature.status === 'signed' ? 'success' : 'warning'}">
                                ${signature.status}
                            </span>
                        </td>
                        <td>${signature.signed_at || 'Belum ditandatangani'}</td>
                        <td>${signature.location || '-'}</td>
                    </tr>
                `;
            });

            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
    }

    $('#validationResult').html(html);
    $('#resultModal').modal('show');
}

function showFileInfo(file) {
    if (file) {
        const size = (file.size / (1024 * 1024)).toFixed(2);
        const html = `
            <strong>Nama:</strong> ${file.name}<br>
            <strong>Ukuran:</strong> ${size} MB<br>
            <strong>Tipe:</strong> ${file.type}
        `;
        $('#fileDetails').html(html);
        $('#fileInfo').show();
    } else {
        $('#fileInfo').hide();
    }
}

function addToRecentValidations(data) {
    const recent = JSON.parse(localStorage.getItem('recentValidations') || '[]');

    const validation = {
        hash: data.document.file_hash,
        title: data.document.title,
        status: data.validation.overall_status,
        timestamp: new Date().toISOString()
    };

    recent.unshift(validation);
    if (recent.length > 5) recent.pop();

    localStorage.setItem('recentValidations', JSON.stringify(recent));
    loadRecentValidations();
}

function loadRecentValidations() {
    const recent = JSON.parse(localStorage.getItem('recentValidations') || '[]');

    if (recent.length === 0) {
        return;
    }

    let html = '';
    recent.forEach(validation => {
        const statusConfig = {
            'valid': { color: 'success', icon: 'check-circle' },
            'invalid': { color: 'danger', icon: 'times-circle' }
        };

        const status = statusConfig[validation.status] || statusConfig['invalid'];
        const date = new Date(validation.timestamp).toLocaleString('id-ID');

        html += `
            <div class="d-flex align-items-center p-3 border-bottom">
                <i class="fas fa-${status.icon} text-${status.color} me-3"></i>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${validation.title}</h6>
                    <small class="text-muted">${date}</small>
                </div>
                <span class="badge bg-${status.color}">${validation.status}</span>
            </div>
        `;
    });

    $('#recentValidations').html(html);
}

function downloadValidationReport(hash) {
    window.open(`/validation/report?document_hash=${hash}`, '_blank');
}

function shareValidationResult() {
    if (navigator.share) {
        navigator.share({
            title: 'Hasil Validasi Tanda Tangan Digital',
            text: 'Lihat hasil validasi tanda tangan digital',
            url: window.location.href
        });
    } else {
        // Fallback
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            showAlert('Link berhasil disalin!', 'success');
        });
    }
}

function showLoadingModal() {
    $('#loadingModal').modal('show');
}

function hideLoadingModal() {
    $('#loadingModal').modal('hide');
}

function showAlert(message, type = 'info') {
    const alert = $(`
        <div class="alert alert-${type} alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('body').append(alert);

    setTimeout(() => {
        alert.alert('close');
    }, 5000);
}

// Validation card hover effects
$(document).on('mouseenter', '.validation-card', function() {
    $(this).addClass('shadow-lg').css('transform', 'translateY(-5px)');
});

$(document).on('mouseleave', '.validation-card', function() {
    $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
});
</script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #05184E 0%, #0a2470 100%);
}

.validation-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.validation-card:hover {
    transform: translateY(-5px);
}

#qrScanner {
    min-height: 200px;
    background: #f8f9fa;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.alert-heading {
    font-size: 1.25rem;
}

code {
    background-color: rgba(0,0,0,0.05);
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.9em;
}
</style>
@endpush