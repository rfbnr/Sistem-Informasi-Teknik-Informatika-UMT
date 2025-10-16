@extends('user.layouts.app')

@section('title', 'Tanda Tangan Digital')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-signature text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="h4 mb-1">Tanda Tangan Digital</h2>
                            <p class="text-muted mb-0">{{ $signatureRequest->title }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Document Preview -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📄 {{ $signatureRequest->document->title }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Kategori:</small>
                            <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $signatureRequest->document->category)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Ukuran File:</small>
                            <p class="mb-0">{{ number_format($signatureRequest->document->file_size / 1024, 0) }} KB</p>
                        </div>
                    </div>

                    @if($signatureRequest->description)
                    <div class="mb-3">
                        <small class="text-muted">Deskripsi:</small>
                        <p class="mb-0">{{ $signatureRequest->description }}</p>
                    </div>
                    @endif

                    <!-- PDF Preview -->
                    <div class="border rounded bg-light p-3 text-center">
                        <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                        <h6>Preview Dokumen PDF</h6>
                        <p class="text-muted mb-3">Klik untuk membuka dokumen dalam tab baru</p>
                        <a href="{{ route('documents.download', $signatureRequest->document) }}"
                           target="_blank"
                           class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>Buka Dokumen
                        </a>
                    </div>

                    <!-- Integrity Check -->
                    <div class="mt-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <small class="text-muted">Dokumen terverifikasi di blockchain</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-pen-fancy me-2"></i>Panel Tanda Tangan
                    </h5>
                </div>
                <div class="card-body">
                    <form id="signatureForm">
                        @csrf

                        <!-- Signature Method -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Metode Tanda Tangan</label>
                            <div class="d-grid gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="signature_method"
                                           value="digital_signature" id="digital" checked>
                                    <label class="form-check-label" for="digital">
                                        <i class="fas fa-signature me-2"></i>Tanda Tangan Digital
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="signature_method"
                                           value="pin_signature" id="pin">
                                    <label class="form-check-label" for="pin">
                                        <i class="fas fa-key me-2"></i>Tanda Tangan dengan PIN
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Digital Signature Canvas -->
                        <div id="digitalSignaturePanel">
                            <label class="form-label fw-bold">Buat Tanda Tangan</label>
                            <div class="border rounded p-2 mb-3" style="background: #fafafa;">
                                <canvas id="signatureCanvas"
                                        width="300"
                                        height="150"
                                        style="border: 1px dashed #ddd; cursor: crosshair; width: 100%; height: 150px;">
                                </canvas>
                            </div>
                            <div class="d-flex gap-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSignature">
                                    <i class="fas fa-eraser me-1"></i>Hapus
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="saveSignature">
                                    <i class="fas fa-save me-1"></i>Simpan
                                </button>
                            </div>
                        </div>

                        <!-- PIN Input -->
                        <div id="pinSignaturePanel" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Masukkan PIN</label>
                                <input type="password" class="form-control" name="pin"
                                       placeholder="Masukkan 6 digit PIN" maxlength="6">
                                <small class="text-muted">PIN untuk verifikasi identitas Anda</small>
                            </div>
                        </div>

                        <!-- Location (Optional) -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="includeLocation">
                                <label class="form-check-label" for="includeLocation">
                                    <i class="fas fa-map-marker-alt me-2"></i>Sertakan lokasi
                                </label>
                            </div>
                            <small class="text-muted">Lokasi akan disimpan untuk audit trail</small>
                        </div>

                        <!-- Agreement -->
                        <div class="mb-4">
                            <div class="border rounded p-3 bg-light">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreement" required>
                                    <label class="form-check-label" for="agreement">
                                        Saya menyetujui untuk menandatangani dokumen ini secara digital
                                    </label>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Dengan menandatangani, Anda menyetujui bahwa tanda tangan digital ini
                                    memiliki kekuatan hukum yang sama dengan tanda tangan basah.
                                </small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="signButton">
                                <i class="fas fa-signature me-2"></i>
                                <span id="signButtonText">Tanda Tangani Dokumen</span>
                            </button>
                            <button type="button" class="btn btn-outline-danger"
                                    onclick="showRejectModal()">
                                <i class="fas fa-times me-2"></i>Tolak Permintaan
                            </button>
                        </div>
                    </form>

                    <!-- Progress Info -->
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted fw-bold">Informasi Proses:</small>
                        <ul class="list-unstyled mt-2 small">
                            <li><i class="fas fa-check text-success me-2"></i>Dokumen terverifikasi</li>
                            <li><i class="fas fa-check text-success me-2"></i>Identitas terautentikasi</li>
                            <li><i class="fas fa-clock text-warning me-2"></i>Menunggu tanda tangan</li>
                            <li><i class="fas fa-circle text-muted me-2"></i>Penyimpanan blockchain</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Permintaan Tanda Tangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('signatures.reject', $signatureRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" name="rejection_reason" rows="4"
                                  placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Penolakan ini akan tercatat permanent dan tidak dapat dibatalkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h4>Dokumen Berhasil Ditandatangani!</h4>
                <p class="text-muted mb-4">Tanda tangan Anda telah disimpan dengan aman di blockchain.</p>

                <div class="border rounded p-3 bg-light mb-4">
                    <small class="text-muted fw-bold">Kode Verifikasi:</small>
                    <div class="h5 text-primary mt-1" id="verificationCode"></div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('signatures.index') }}" class="btn btn-primary">
                        Kembali ke Daftar
                    </a>
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Cetak Bukti
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let canvas, ctx, isDrawing = false;
let signatureData = null;

$(document).ready(function() {
    // Initialize signature canvas
    initializeCanvas();

    // Toggle signature method
    $('input[name="signature_method"]').change(function() {
        if ($(this).val() === 'digital_signature') {
            $('#digitalSignaturePanel').show();
            $('#pinSignaturePanel').hide();
        } else {
            $('#digitalSignaturePanel').hide();
            $('#pinSignaturePanel').show();
        }
    });

    // Handle form submission
    $('#signatureForm').submit(function(e) {
        e.preventDefault();
        processSignature();
    });

    // Get location if checkbox is checked
    $('#includeLocation').change(function() {
        if ($(this).is(':checked')) {
            getLocation();
        }
    });
});

function initializeCanvas() {
    canvas = document.getElementById('signatureCanvas');
    ctx = canvas.getContext('2d');

    // Set canvas size
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * 2;
    canvas.height = rect.height * 2;
    ctx.scale(2, 2);

    // Set drawing styles
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    // Mouse events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    // Touch events for mobile
    canvas.addEventListener('touchstart', handleTouch);
    canvas.addEventListener('touchmove', handleTouch);
    canvas.addEventListener('touchend', stopDrawing);

    // Clear button
    $('#clearSignature').click(function() {
        clearCanvas();
    });

    // Save button
    $('#saveSignature').click(function() {
        saveSignature();
    });
}

function startDrawing(e) {
    isDrawing = true;
    const pos = getMousePos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
}

function draw(e) {
    if (!isDrawing) return;
    const pos = getMousePos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
}

function stopDrawing() {
    isDrawing = false;
}

function getMousePos(e) {
    const rect = canvas.getBoundingClientRect();
    return {
        x: (e.clientX - rect.left) * (canvas.width / rect.width) / 2,
        y: (e.clientY - rect.top) * (canvas.height / rect.height) / 2
    };
}

function handleTouch(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' :
                                     e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
}

function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    signatureData = null;
}

function saveSignature() {
    signatureData = canvas.toDataURL();
    Swal.fire({
        icon: 'success',
        title: 'Tanda Tangan Disimpan',
        text: 'Tanda tangan Anda berhasil disimpan.',
        timer: 1500,
        showConfirmButton: false
    });
}

function processSignature() {
    const signMethod = $('input[name="signature_method"]:checked').val();

    // Validate signature data
    if (signMethod === 'digital_signature' && !signatureData) {
        Swal.fire({
            icon: 'error',
            title: 'Tanda Tangan Diperlukan',
            text: 'Silakan buat tanda tangan terlebih dahulu.'
        });
        return;
    }

    if (signMethod === 'pin_signature' && !$('input[name="pin"]').val()) {
        Swal.fire({
            icon: 'error',
            title: 'PIN Diperlukan',
            text: 'Silakan masukkan PIN Anda.'
        });
        return;
    }

    // Show loading
    $('#signButton').prop('disabled', true);
    $('#signButtonText').html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');

    // Prepare form data
    const formData = {
        signature_method: signMethod,
        signature_data: signatureData || '',
        pin: $('input[name="pin"]').val(),
        location: $('#includeLocation').is(':checked') ? getCurrentLocation() : null,
        _token: $('input[name="_token"]').val()
    };

    // Submit to server
    $.ajax({
        url: '{{ route("signatures.process", $signatureRequest) }}',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#verificationCode').text(response.verification_code);
                $('#successModal').modal('show');

                // Update progress
                updateProgress();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message || 'Terjadi kesalahan saat memproses tanda tangan.'
                });
            }
        },
        error: function(xhr) {
            let message = 'Terjadi kesalahan sistem.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        },
        complete: function() {
            $('#signButton').prop('disabled', false);
            $('#signButtonText').html('<i class="fas fa-signature me-2"></i>Tanda Tangani Dokumen');
        }
    });
}

function showRejectModal() {
    $('#rejectModal').modal('show');
}

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            console.log('Location obtained:', position.coords);
        }, function(error) {
            console.log('Location error:', error);
            $('#includeLocation').prop('checked', false);
        });
    }
}

function getCurrentLocation() {
    // Return stored location data if available
    return {
        latitude: null,
        longitude: null,
        accuracy: null,
        timestamp: new Date().toISOString()
    };
}

function updateProgress() {
    // Update the progress indicators
    $('i.fa-clock').removeClass('fa-clock text-warning').addClass('fa-check text-success');
    $('i.fa-circle').removeClass('fa-circle text-muted').addClass('fa-spinner fa-spin text-primary');

    setTimeout(function() {
        $('i.fa-spinner').removeClass('fa-spinner fa-spin text-primary').addClass('fa-check text-success');
    }, 3000);
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush