{{-- @extends('user.layouts.app')

@section('title', 'Upload Dokumen')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-cloud-upload-alt text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="h4 mb-1">Upload Dokumen Baru</h2>
                            <p class="text-muted mb-0">Upload dokumen PDF untuk persetujuan dan tanda tangan digital</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📄 Informasi Dokumen</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <!-- Document Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">Judul Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}"
                                   placeholder="Masukkan judul dokumen..." required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Document Category -->
                        <div class="mb-4">
                            <label for="category" class="form-label fw-bold">Kategori Dokumen <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">Pilih kategori dokumen</option>
                                <option value="academic_transcript" {{ old('category') == 'academic_transcript' ? 'selected' : '' }}>
                                    📊 Transkrip Akademik
                                </option>
                                <option value="certificate" {{ old('category') == 'certificate' ? 'selected' : '' }}>
                                    🏆 Sertifikat
                                </option>
                                <option value="thesis" {{ old('category') == 'thesis' ? 'selected' : '' }}>
                                    📖 Skripsi/Thesis
                                </option>
                                <option value="research_proposal" {{ old('category') == 'research_proposal' ? 'selected' : '' }}>
                                    🔬 Proposal Penelitian
                                </option>
                                <option value="internship_report" {{ old('category') == 'internship_report' ? 'selected' : '' }}>
                                    💼 Laporan Magang
                                </option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>
                                    📋 Lainnya
                                </option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Document Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Jelaskan tujuan dan isi dokumen...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Opsional - Berikan deskripsi singkat tentang dokumen</small>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="document" class="form-label fw-bold">File Dokumen <span class="text-danger">*</span></label>
                            <div class="border rounded p-4 bg-light" id="dropZone">
                                <div class="text-center" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6>Drag & Drop file PDF atau klik untuk browse</h6>
                                    <p class="text-muted mb-3">Maksimal ukuran file: 25MB</p>
                                    <input type="file" class="form-control @error('document') is-invalid @enderror"
                                           id="document" name="document" accept=".pdf" required style="display: none;">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('document').click()">
                                        <i class="fas fa-folder-open me-2"></i>Pilih File PDF
                                    </button>
                                </div>
                                <div id="filePreview" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" id="fileName"></h6>
                                            <small class="text-muted" id="fileSize"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('document')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="fas fa-shield-alt me-3 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading">🔒 Keamanan Dokumen</h6>
                                    <p class="mb-0">
                                        Dokumen Anda akan dienkripsi dan hash-nya disimpan di blockchain untuk memastikan
                                        integritas dan keaslian. Sistem akan otomatis melakukan verifikasi keamanan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload me-2"></i>
                                <span id="submitText">Upload Dokumen</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // File input change event
    $('#document').change(function() {
        const file = this.files[0];
        if (file) {
            showFilePreview(file);
        }
    });

    // Drag and drop functionality
    $('#dropZone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-primary bg-primary bg-opacity-10');
    });

    $('#dropZone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-primary bg-opacity-10');
    });

    $('#dropZone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-primary bg-opacity-10');

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type === 'application/pdf') {
                $('#document')[0].files = files;
                showFilePreview(file);
            } else {
                alert('Hanya file PDF yang diperbolehkan!');
            }
        }
    });

    // Form submission
    $('#uploadForm').submit(function() {
        $('#submitBtn').prop('disabled', true);
        $('#submitText').html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...');
    });
});

function showFilePreview(file) {
    const maxSize = 25 * 1024 * 1024; // 25MB in bytes

    if (file.size > maxSize) {
        alert('Ukuran file terlalu besar! Maksimal 25MB.');
        $('#document').val('');
        return;
    }

    $('#fileName').text(file.name);
    $('#fileSize').text(formatFileSize(file.size));
    $('#uploadArea').hide();
    $('#filePreview').show();
}

function removeFile() {
    $('#document').val('');
    $('#uploadArea').show();
    $('#filePreview').hide();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush --}}

@extends('user.layouts.app')

@section('title', 'Upload Dokumen')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-cloud-upload-alt text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="h4 mb-1">Upload Dokumen Baru</h2>
                            <p class="text-muted mb-0">Upload dokumen PDF untuk persetujuan dan tanda tangan digital</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📄 Informasi Dokumen</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <!-- Document Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">Judul Dokumen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}"
                                   placeholder="Masukkan judul dokumen..." required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Document Category -->
                        <div class="mb-4">
                            <label for="category" class="form-label fw-bold">Kategori Dokumen <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">Pilih kategori dokumen</option>
                                <option value="academic_transcript" {{ old('category') == 'academic_transcript' ? 'selected' : '' }}>
                                    📊 Transkrip Akademik
                                </option>
                                <option value="certificate" {{ old('category') == 'certificate' ? 'selected' : '' }}>
                                    🏆 Sertifikat
                                </option>
                                <option value="thesis" {{ old('category') == 'thesis' ? 'selected' : '' }}>
                                    📖 Skripsi/Thesis
                                </option>
                                <option value="research_proposal" {{ old('category') == 'research_proposal' ? 'selected' : '' }}>
                                    🔬 Proposal Penelitian
                                </option>
                                <option value="internship_report" {{ old('category') == 'internship_report' ? 'selected' : '' }}>
                                    💼 Laporan Magang
                                </option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>
                                    📋 Lainnya
                                </option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Document Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Jelaskan tujuan dan isi dokumen...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Opsional - Berikan deskripsi singkat tentang dokumen</small>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="documentFile" class="form-label fw-bold">File Dokumen <span class="text-danger">*</span></label>
                            <div class="border rounded p-4 bg-light" id="dropZone">
                                <div class="text-center" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6>Drag & Drop file PDF atau klik untuk browse</h6>
                                    <p class="text-muted mb-3">Maksimal ukuran file: 25MB</p>
                                    <input type="file" class="form-control @error('document') is-invalid @enderror"
                                           id="documentFile" name="document" accept=".pdf" required style="display: none;">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('documentFile').click()">
                                        <i class="fas fa-folder-open me-2"></i>Pilih File PDF
                                    </button>
                                </div>
                                <div id="filePreview" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" id="fileName"></h6>
                                            <small class="text-muted" id="fileSize"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('document')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="fas fa-shield-alt me-3 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading">🔒 Keamanan Dokumen</h6>
                                    <p class="mb-0">
                                        Dokumen Anda akan dienkripsi dan hash-nya disimpan di blockchain untuk memastikan
                                        integritas dan keaslian. Sistem akan otomatis melakukan verifikasi keamanan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload me-2"></i>
                                <span id="submitText">Upload Dokumen</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // File input change event
    $('#documentFile').change(function() {
        const file = this.files[0];
        if (file) {
            showFilePreview(file);
        }
    });

    // Drag and drop functionality
    $('#dropZone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('border-primary bg-primary bg-opacity-10');
    });

    $('#dropZone').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-primary bg-opacity-10');
    });

    $('#dropZone').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('border-primary bg-primary bg-opacity-10');

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type === 'application/pdf') {
                $('#documentFile')[0].files = files;
                showFilePreview(file);
            } else {
                alert('Hanya file PDF yang diperbolehkan!');
            }
        }
    });

    // Form submission
    $('#uploadForm').submit(function() {
        $('#submitBtn').prop('disabled', true);
        $('#submitText').html('<i class="fas fa-spinner fa-spin me-2"></i>Mengupload...');
    });
});

function showFilePreview(file) {
    const maxSize = 25 * 1024 * 1024; // 25MB in bytes

    if (file.size > maxSize) {
        alert('Ukuran file terlalu besar! Maksimal 25MB.');
        $('#documentFile').val('');
        return;
    }

    $('#fileName').text(file.name);
    $('#fileSize').text(formatFileSize(file.size));
    $('#uploadArea').hide();
    $('#filePreview').show();
}

function removeFile() {
    $('#documentFile').val('');
    $('#uploadArea').show();
    $('#filePreview').hide();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush
