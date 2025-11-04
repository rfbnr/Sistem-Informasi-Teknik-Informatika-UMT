<?php $__env->startSection('title', 'Submit Document for Digital Signature'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.upload-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.priority-selector {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.priority-option {
    padding: 0.75rem 1.5rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    min-width: 120px;
}

.priority-option.low {
    background: #e3f2fd;
    color: #1976d2;
    border-color: #e3f2fd;
}
.priority-option.normal {
    background: #f3e5f5;
    color: #7b1fa2;
    border-color: #f3e5f5;
}
.priority-option.high {
    background: #fff3e0;
    color: #f57c00;
    border-color: #fff3e0;
}
.priority-option.urgent {
    background: #ffebee;
    color: #d32f2f;
    border-color: #ffebee;
}

.priority-option.active {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.priority-option.low.active { border-color: #1976d2; }
.priority-option.normal.active { border-color: #7b1fa2; }
.priority-option.high.active { border-color: #f57c00; }
.priority-option.urgent.active { border-color: #d32f2f; }

.file-drop-zone {
    border: 3px dashed #dee2e6;
    border-radius: 1rem;
    padding: 3rem 2rem;
    text-align: center;
    transition: all 0.3s ease;
    background: #fff;
    cursor: pointer;
}

.file-drop-zone:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.file-drop-zone.dragover {
    border-color: #28a745;
    background: #f8fff8;
    transform: scale(1.02);
}

.file-selected {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.process-steps {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}

.step-item {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.step-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    margin-right: 1rem;
    flex-shrink: 0;
}

.step-1 { background: #007bff; }
.step-2 { background: #17a2b8; }
.step-3 { background: #ffc107; }
.step-4 { background: #28a745; }

.recent-requests {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.request-item {
    display: flex;
    align-items: center;
    justify-content: between;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
}

.request-item:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.status-pending { background: #ffc107; }
.status-approved { background: #17a2b8; }
.status-user_signed { background: #6f42c1; }
.status-sign_approved { background: #28a745; }
.status-rejected { background: #dc3545; }

.guidelines-box {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.guideline-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}

.guideline-item i {
    margin-right: 0.75rem;
    width: 20px;
}

@media (max-width: 768px) {
    .priority-selector {
        flex-direction: column;
    }

    .priority-option {
        min-width: auto;
        width: 100%;
    }

    .upload-container {
        padding: 1rem;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<!-- Section Header -->
<section id="header-section">
    <h1>Digital Document Approval Request</h1>
</section>

<div class="container-fluid mx-auto mt-4">
    <!-- Header Section -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-file-signature me-3"></i>
                    Digital Document Approval Request
                </h1>
                <p class="mb-0 opacity-75">Submit your document for Kaprodi approval and digital signature</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo e(route('user.signature.approval.status')); ?>" class="btn btn-light">
                        <i class="fas fa-list me-1"></i> My Requests
                    </a>
                    <a href="<?php echo e(route('signature.verify.page')); ?>" class="btn btn-warning">
                        <i class="fas fa-shield-alt me-1"></i> Verify Document
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <!-- Guidelines -->
            <div class="guidelines-box">
                <h5 class="mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Important Guidelines
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="guideline-item">
                            <i class="fas fa-file-pdf"></i>
                            <span>Only PDF files are accepted (max 25MB)</span>
                        </div>
                        <div class="guideline-item">
                            <i class="fas fa-clock"></i>
                            <span>Processing time: 1-3 business days</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="guideline-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Documents are encrypted and secure</span>
                        </div>
                        <div class="guideline-item">
                            <i class="fas fa-bell"></i>
                            <span>Email notifications for status updates</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="upload-container">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width: 50px; height: 50px;">
                        <i class="fas fa-upload"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">Submit New Document</h3>
                        <p class="text-muted mb-0">Fill in the details and upload your document</p>
                    </div>
                </div>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                
                <form action="<?php echo e(route('user.signature.approval.upload')); ?>" method="POST" enctype="multipart/form-data" id="uploadForm" >
                    <?php echo csrf_field(); ?>

                    <!-- User Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user text-primary me-1"></i> Student Name
                            </label>
                            <input type="text" class="form-control" value="<?php echo e(auth()->user()->name); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-id-card text-primary me-1"></i> Student ID (NIM)
                            </label>
                            <input type="text" class="form-control" value="<?php echo e(auth()->user()->NIM ?? 'Not Set'); ?>" readonly>
                        </div>
                    </div>

                    <!-- Document Type -->
                    <div class="mb-4">
                        <label for="document_type" class="form-label fw-bold">
                            <i class="fas fa-file-alt text-primary me-1"></i> Document Type *
                        </label>
                        <select class="form-select" name="document_type" id="document_type" required>
                            <option value="" disabled selected>-- Select Document Type --</option>
                            <option value="Surat Dispensasi" <?php echo e(old('document_type') == 'Surat Dispensasi' ? 'selected' : ''); ?>>
                                Surat Dispensasi
                            </option>
                            <option value="Surat Peminjaman Ruang" <?php echo e(old('document_type') == 'Surat Peminjaman Ruang' ? 'selected' : ''); ?>>
                                Surat Peminjaman Ruang
                            </option>
                            <option value="Surat Peminjaman Alat" <?php echo e(old('document_type') == 'Surat Peminjaman Alat' ? 'selected' : ''); ?>>
                                Surat Peminjaman Alat
                            </option>
                            <option value="Kartu Ujian" <?php echo e(old('document_type') == 'Kartu Ujian' ? 'selected' : ''); ?>>
                                Kartu Ujian
                            </option>
                            <option value="Surat Keterangan Aktif" <?php echo e(old('document_type') == 'Surat Keterangan Aktif' ? 'selected' : ''); ?>>
                                Surat Keterangan Aktif
                            </option>
                            <option value="Surat Rekomendasi" <?php echo e(old('document_type') == 'Surat Rekomendasi' ? 'selected' : ''); ?>>
                                Surat Rekomendasi
                            </option>
                            <option value="Lainnya" <?php echo e(old('document_type') == 'Lainnya' ? 'selected' : ''); ?>>
                                Lainnya
                            </option>
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Choose the type of document you're submitting
                        </div>
                    </div>

                    <!-- Priority Level -->
                    

                    <!-- Expected Completion Date -->
                    

                    <!-- Description/Notes -->
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-bold">
                            <i class="fas fa-comment-alt text-primary me-1"></i> Description/Notes *
                        </label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" required
                                  placeholder="Please provide details about your request, purpose, or any special instructions..."><?php echo e(old('notes')); ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i>
                            Clear details help with faster approval
                        </div>
                    </div>

                    <!-- File Upload - DIPERBAIKI -->
                    

                    <!-- File Upload Section - DIPERBAIKI -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-cloud-upload-alt text-primary me-1"></i> Upload Document *
                        </label>
                        <div class="file-drop-zone" id="dropzone">
                            <div id="dropzone-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5>Drop your PDF file here</h5>
                                <p class="text-muted mb-3">or click to browse files</p>
                                <button type="button" class="btn btn-outline-primary" onclick="triggerFileInput()">
                                    <i class="fas fa-folder-open me-1"></i> Choose File
                                </button>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Maximum file size: 25MB • PDF format only
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <input
                            type="file"
                            name="document"
                            id="document"
                            accept=".pdf"
                            required
                            style="display: none;">
                        <div id="fileInfo" class="mt-3" style="display: none;"></div>
                        <div id="fileError" class="mt-2" style="display: none;"></div>
                        <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger mt-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary me-md-2" onclick="resetForm()">
                            <i class="fas fa-undo me-1"></i> Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-paper-plane me-1"></i> Submit Request
                        </button>
                    </div>

                    <?php if($hasApprovalRequests ?? false): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            <strong>Track Your Submissions:</strong>
                            Check your <a href="<?php echo e(route('user.signature.approval.status')); ?>" class="text-decoration-none">
                                document status page</a> to monitor progress.
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Process Steps -->
            <div class="process-steps">
                <h5 class="mb-3">
                    <i class="fas fa-route text-primary me-2"></i>
                    Process Steps
                </h5>

                <div class="step-item">
                    <div class="step-number step-1">1</div>
                    <div>
                        <strong>Submit Document</strong>
                        <div class="small text-muted">Upload PDF with details and description</div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number step-2">2</div>
                    <div>
                        <strong>Kaprodi Review</strong>
                        <div class="small text-muted">Document reviewed and approved by Kaprodi</div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number step-3">3</div>
                    <div>
                        <strong>Digital Signing</strong>
                        <div class="small text-muted">You sign the approved document digitally</div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number step-4">4</div>
                    <div>
                        <strong>Complete & Download</strong>
                        <div class="small text-muted">Download signed document with QR verification</div>
                    </div>
                </div>
            </div>

            <!-- Recent Requests -->
            <?php if(isset($recentRequests) && $recentRequests->count() > 0): ?>
            <div class="recent-requests">
                <h5 class="mb-3">
                    <i class="fas fa-history text-primary me-2"></i>
                    Recent Requests
                </h5>
                <?php $__currentLoopData = $recentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="request-item">
                    <div class="status-dot status-<?php echo e(str_replace(' ', '_', strtolower($request->status))); ?>"></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold"><?php echo e($request->document_name); ?></div>
                        <small class="text-muted"><?php echo e($request->created_at->format('d M Y')); ?></small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted"><?php echo e($request->status_label); ?></small>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="text-center mt-3">
                    <a href="<?php echo e(route('user.signature.approval.status')); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i> View All
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    console.log('Document ready, initializing form...');

    // Priority selection
    // $('.priority-option').click(function() {
    //     $('.priority-option').removeClass('active');
    //     $(this).addClass('active');
    //     const radioId = $(this).attr('for');
    //     $('#' + radioId).prop('checked', true);
    //     console.log('Priority selected:', $('#' + radioId).val());
    // });

    // // Set initial active priority
    // $('input[name="priority"]:checked').each(function() {
    //     $('label[for="' + this.id + '"]').addClass('active');
    // });

    // File upload handling - DIPERBAIKI
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('document');
    const fileInfo = document.getElementById('fileInfo');
    const fileError = document.getElementById('fileError');

    console.log('File input element:', fileInput);
    console.log('Dropzone element:', dropzone);

    // Event listeners
    if (dropzone && fileInput) {
        dropzone.addEventListener('dragover', handleDragOver);
        dropzone.addEventListener('dragleave', handleDragLeave);
        dropzone.addEventListener('drop', handleDrop);
        dropzone.addEventListener('click', function(e) {
            // Prevent event if clicking on button
            if (!e.target.closest('button')) {
                triggerFileInput();
            }
        });
        fileInput.addEventListener('change', handleFileSelect);
    }

    function handleDragOver(e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
    }

    function handleDrop(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            // CRITICAL FIX: Set files properly
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            fileInput.files = dataTransfer.files;

            handleFileSelect();
        }
    }

    function handleFileSelect() {
        console.log('File selected, processing...');
        const file = fileInput.files[0];

        if (file) {
            console.log('File details:', {
                name: file.name,
                type: file.type,
                size: file.size
            });

            // Clear previous errors
            hideFileError();

            // Validate file type
            if (file.type !== 'application/pdf') {
                showFileError('Please select a PDF file only.');
                fileInput.value = '';
                return;
            }

            // Validate file size (25MB = 26214400 bytes)
            if (file.size > 26214400) {
                showFileError('File size must be less than 25MB.');
                fileInput.value = '';
                return;
            }

            showFileInfo(file);
        }
    }

    function showFileInfo(file) {
        const fileSize = formatFileSize(file.size);
        fileInfo.innerHTML = `
            <div class="alert alert-success">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                    <div class="flex-grow-1">
                        <strong>${file.name}</strong><br>
                        <small>${fileSize} • PDF Document</small>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFile()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        fileInfo.style.display = 'block';
        dropzone.classList.add('file-selected');

        // FIXED: Update content without replacing the entire dropzone
        const dropzoneContent = dropzone.querySelector('#dropzone-content') || dropzone;
        dropzoneContent.innerHTML = `
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5 class="text-success">File Selected</h5>
            <p class="text-muted">${file.name}</p>
            <button type="button" class="btn btn-outline-primary" onclick="triggerFileInput()">
                <i class="fas fa-exchange-alt me-1"></i> Change File
            </button>
        `;
    }

    function showFileError(message) {
        fileError.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
        fileError.style.display = 'block';
    }

    function hideFileError() {
        fileError.style.display = 'none';
    }

    // Form submission dengan validasi lengkap - DIPERBAIKI
    $('#uploadForm').on('submit', function(e) {
        console.log('Form submission triggered');

        // CRITICAL FIX: Validate the actual file input
        const fileInputElement = document.getElementById('document');
        console.log('File input at submission:', fileInputElement);
        console.log('Files at submission:', fileInputElement.files);
        console.log('File count:', fileInputElement.files.length);

        if (fileInputElement.files.length > 0) {
            console.log('File present:', fileInputElement.files[0].name);
        }

        let isValid = true;
        let errors = [];

        // Validation checks
        if (!$('#document_type').val()) {
            errors.push('Please select a document type.');
            isValid = false;
        }

        // CRITICAL FIX: Check file input properly
        if (!fileInputElement.files || fileInputElement.files.length === 0) {
            errors.push('Please upload a PDF document.');
            isValid = false;
        } else {
            console.log('File validation passed:', fileInputElement.files[0].name);
        }

        if (!$('#notes').val().trim()) {
            errors.push('Please provide a description.');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            console.log('Form validation failed:', errors);
            showAlert(errors.join('<br>'), 'danger');
            return false;
        } else {
            console.log('Form validation passed, submitting...');
            // Show loading state
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...');
        }
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        $('.alert').not('.alert-success').remove();

        // Add new alert at the top of the form
        $('.upload-container').prepend(alertHtml);

        // Auto-hide after 5 seconds
        setTimeout(() => $('.alert-danger, .alert-warning').fadeOut(), 5000);
    }
});

// Global functions - DIPERBAIKI
function triggerFileInput() {
    console.log('Triggering file input...');
    const fileInput = document.getElementById('document');
    if (fileInput) {
        fileInput.click();
    }
}

function removeFile() {
    console.log('Removing file...');
    const fileInput = document.getElementById('document');
    const dropzone = document.getElementById('dropzone');
    const fileInfo = document.getElementById('fileInfo');
    const fileError = document.getElementById('fileError');

    if (fileInput) {
        fileInput.value = '';
        console.log('File input cleared');
    }

    if (fileInfo) {
        fileInfo.style.display = 'none';
    }

    if (fileError) {
        fileError.style.display = 'none';
    }

    if (dropzone) {
        dropzone.classList.remove('file-selected');

        // Reset dropzone content - FIXED
        const dropzoneContent = dropzone.querySelector('#dropzone-content') || dropzone;
        dropzoneContent.innerHTML = `
            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
            <h5>Drop your PDF file here</h5>
            <p class="text-muted mb-3">or click to browse files</p>
            <button type="button" class="btn btn-outline-primary" onclick="triggerFileInput()">
                <i class="fas fa-folder-open me-1"></i> Choose File
            </button>
            <div class="mt-2">
                <small class="text-muted">
                    Maximum file size: 25MB • PDF format only
                </small>
            </div>
        `;
    }
}

function resetForm() {
    console.log('Resetting form...');
    document.getElementById('uploadForm').reset();
    removeFile();
    // $('.priority-option').removeClass('active');
    // $('#priority-normal').prop('checked', true);
    // $('label[for="priority-normal"]').addClass('active');

    // Reset submit button
    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Submit Request');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/user/approval-request.blade.php ENDPATH**/ ?>