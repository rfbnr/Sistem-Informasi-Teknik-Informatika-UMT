<?php $__env->startSection('title', 'Digital Document Approval Request'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.form-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.priority-badge {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.priority-badge.low { background: #e3f2fd; color: #1976d2; }
.priority-badge.normal { background: #f3e5f5; color: #7b1fa2; }
.priority-badge.high { background: #fff3e0; color: #f57c00; }
.priority-badge.urgent { background: #ffebee; color: #d32f2f; }

.priority-badge.active {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 1rem;
    padding: 3rem 2rem;
    text-align: center;
    transition: all 0.3s ease;
    background: #fff;
}

.file-upload-area:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.file-upload-area.dragover {
    border-color: #28a745;
    background: #f8fff8;
}

.recent-requests {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.request-card {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.request-card:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 0.5rem;
}

.status-pending { background: #ffc107; }
.status-approved { background: #17a2b8; }
.status-user_signed { background: #6f42c1; }
.status-sign_approved { background: #28a745; }
.status-rejected { background: #dc3545; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<!-- Header Section -->
<section id="header-section" class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white mb-3">Digital Document Approval</h1>
                <p class="text-white-50 lead mb-0">Submit documents for digital signature approval by Kaprodi</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="bg-white bg-opacity-20 rounded-3 p-3">
                    <i class="fas fa-file-signature fa-3x text-white mb-2"></i>
                    <div class="text-white">
                        <small>Secure Digital Process</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="form-section">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-upload fa-2x text-primary me-3"></i>
                        <div>
                            <h3 class="mb-1">Submit New Document</h3>
                            <p class="text-muted mb-0">Upload your document for digital signature approval</p>
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

                    <form action="<?php echo e(route('approval-request.upload')); ?>" method="POST" enctype="multipart/form-data" id="approvalForm">
                        <?php echo csrf_field(); ?>

                        <!-- User Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="user_name" class="form-label fw-bold">
                                    <i class="fas fa-user text-primary me-1"></i> Applicant Name
                                </label>
                                <input type="text" class="form-control" id="user_name"
                                       value="<?php echo e(auth()->user()->name); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="user_nim" class="form-label fw-bold">
                                    <i class="fas fa-id-card text-primary me-1"></i> Student ID (NIM)
                                </label>
                                <input type="text" class="form-control" id="user_nim"
                                       value="<?php echo e(auth()->user()->NIM ?? 'Not Set'); ?>" readonly>
                            </div>
                        </div>

                        <!-- Document Type -->
                        <div class="mb-4">
                            <label for="document_name" class="form-label fw-bold">
                                <i class="fas fa-file-alt text-primary me-1"></i> Document Type
                            </label>
                            <select class="form-select" name="document_name" id="document_name" required>
                                <option value="" disabled selected>-- Select Document Type --</option>
                                <option value="Surat Dispensasi" <?php echo e(old('document_name') == 'Surat Dispensasi' ? 'selected' : ''); ?>>
                                    Surat Dispensasi
                                </option>
                                <option value="Surat Peminjaman Ruang" <?php echo e(old('document_name') == 'Surat Peminjaman Ruang' ? 'selected' : ''); ?>>
                                    Surat Peminjaman Ruang
                                </option>
                                <option value="Surat Peminjaman Alat" <?php echo e(old('document_name') == 'Surat Peminjaman Alat' ? 'selected' : ''); ?>>
                                    Surat Peminjaman Alat
                                </option>
                                <option value="Kartu Ujian" <?php echo e(old('document_name') == 'Kartu Ujian' ? 'selected' : ''); ?>>
                                    Kartu Ujian
                                </option>
                                <option value="Surat Keterangan Aktif" <?php echo e(old('document_name') == 'Surat Keterangan Aktif' ? 'selected' : ''); ?>>
                                    Surat Keterangan Aktif
                                </option>
                                <option value="Surat Rekomendasi" <?php echo e(old('document_name') == 'Surat Rekomendasi' ? 'selected' : ''); ?>>
                                    Surat Rekomendasi
                                </option>
                                <option value="Lainnya" <?php echo e(old('document_name') == 'Lainnya' ? 'selected' : ''); ?>>
                                    Lainnya
                                </option>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Select the type of document you want to submit for approval
                            </div>
                        </div>

                        <!-- Priority Level -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-flag text-primary me-1"></i> Priority Level
                            </label>
                            <div class="d-flex gap-2 flex-wrap">
                                <input type="radio" class="btn-check" name="priority" id="priority-low" value="low" <?php echo e(old('priority', 'normal') == 'low' ? 'checked' : ''); ?>>
                                <label class="priority-badge low" for="priority-low">
                                    <i class="fas fa-circle me-1"></i> Low Priority
                                </label>

                                <input type="radio" class="btn-check" name="priority" id="priority-normal" value="normal" <?php echo e(old('priority', 'normal') == 'normal' ? 'checked' : ''); ?>>
                                <label class="priority-badge normal" for="priority-normal">
                                    <i class="fas fa-circle me-1"></i> Normal Priority
                                </label>

                                <input type="radio" class="btn-check" name="priority" id="priority-high" value="high" <?php echo e(old('priority') == 'high' ? 'checked' : ''); ?>>
                                <label class="priority-badge high" for="priority-high">
                                    <i class="fas fa-circle me-1"></i> High Priority
                                </label>

                                <input type="radio" class="btn-check" name="priority" id="priority-urgent" value="urgent" <?php echo e(old('priority') == 'urgent' ? 'checked' : ''); ?>>
                                <label class="priority-badge urgent" for="priority-urgent">
                                    <i class="fas fa-circle me-1"></i> Urgent
                                </label>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Higher priority documents will be processed faster
                            </div>
                        </div>

                        <!-- Deadline -->
                        <div class="mb-4">
                            <label for="deadline" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt text-primary me-1"></i> Expected Completion Date (Optional)
                            </label>
                            <input type="date" class="form-control" id="deadline" name="deadline"
                                   value="<?php echo e(old('deadline')); ?>" min="<?php echo e(date('Y-m-d', strtotime('+1 day'))); ?>">
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                When do you need this document to be completed?
                            </div>
                        </div>

                        <!-- Notes/Description -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-bold">
                                <i class="fas fa-comment-alt text-primary me-1"></i> Description/Notes
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                      placeholder="Please provide details about your request, purpose, or any special instructions..."><?php echo e(old('notes')); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Provide clear details to help with faster approval
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="document" class="form-label fw-bold">
                                <i class="fas fa-cloud-upload-alt text-primary me-1"></i> Upload Document
                            </label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5>Drag & Drop your PDF file here</h5>
                                <p class="text-muted mb-3">or click to browse files</p>
                                <input class="form-control d-none" type="file" id="document" name="document"
                                       accept=".pdf" required>
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('document').click()">
                                    <i class="fas fa-folder-open me-1"></i> Choose File
                                </button>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Maximum file size: 25MB. Only PDF files are allowed.
                                    </small>
                                </div>
                            </div>
                            <div id="filePreview" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <span id="fileName"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="removeFile()">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i> Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane me-1"></i> Submit Request
                            </button>
                        </div>

                        <?php if($hasApprovalRequests): ?>
                            <div class="mt-4 p-3 bg-light rounded">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                <strong>Track Your Submissions:</strong>
                                Check your <a href="<?php echo e(route('approval-request.status')); ?>" class="text-decoration-none">document status page</a>
                                to monitor approval progress and access signed documents.
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Process Steps -->
                <div class="recent-requests mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-route text-primary me-2"></i> Process Steps
                    </h5>
                    <div class="step-list">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <small>1</small>
                            </div>
                            <div>
                                <strong>Submit Document</strong>
                                <div class="small text-muted">Upload your PDF document with details</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <small>2</small>
                            </div>
                            <div>
                                <strong>Kaprodi Review</strong>
                                <div class="small text-muted">Document reviewed and approved by Kaprodi</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <small>3</small>
                            </div>
                            <div>
                                <strong>Digital Signing</strong>
                                <div class="small text-muted">You digitally sign the approved document</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">
                                <small>4</small>
                            </div>
                            <div>
                                <strong>Complete</strong>
                                <div class="small text-muted">Download verified signed document</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Requests -->
                <?php if(isset($recentRequests) && $recentRequests->count() > 0): ?>
                <div class="recent-requests">
                    <h5 class="mb-3">
                        <i class="fas fa-history text-primary me-2"></i> Recent Requests
                    </h5>
                    <?php $__currentLoopData = $recentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="request-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold"><?php echo e($request->document_name); ?></div>
                                <small class="text-muted"><?php echo e($request->created_at->format('d M Y')); ?></small>
                            </div>
                            <div class="text-end">
                                <span class="status-indicator status-<?php echo e(str_replace(' ', '_', strtolower($request->status))); ?>"></span>
                                <small class="text-muted"><?php echo e($request->status_label); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('approval-request.status')); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i> View All Requests
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Help & Guidelines -->
                <div class="recent-requests">
                    <h5 class="mb-3">
                        <i class="fas fa-question-circle text-primary me-2"></i> Guidelines
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Upload only PDF documents</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Maximum file size is 25MB</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Provide clear description</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Check status regularly</small>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <a href="<?php echo e(route('signature.verify.page')); ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-shield-alt me-1"></i> Verify Documents
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // File upload handling
    const fileInput = document.getElementById('document');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');

    // Drag and drop functionality
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    });

    // File input change
    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        const file = fileInput.files[0];
        if (file) {
            if (file.type !== 'application/pdf') {
                alert('Please select a PDF file only.');
                fileInput.value = '';
                return;
            }
            if (file.size > 25 * 1024 * 1024) { // 25MB
                alert('File size must be less than 25MB.');
                fileInput.value = '';
                return;
            }

            fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            filePreview.style.display = 'block';
            fileUploadArea.style.display = 'none';
        }
    }

    window.removeFile = function() {
        fileInput.value = '';
        filePreview.style.display = 'none';
        fileUploadArea.style.display = 'block';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form validation
    $('#approvalForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        if (!$('#document_name').val()) {
            alert('Please select a document type.');
            isValid = false;
        }

        if (!fileInput.files[0]) {
            alert('Please upload a document.');
            isValid = false;
        }

        if (!$('#notes').val().trim()) {
            if (!confirm('No description provided. Continue anyway?')) {
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
        } else {
            // Show loading state
            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Submitting...');
        }
    });

    // Priority selection visual feedback
    $('input[name="priority"]').on('change', function() {
        $('.priority-badge').removeClass('active');
        $('label[for="' + this.id + '"]').addClass('active');
    });

    // Set initial active priority
    $('input[name="priority"]:checked').trigger('change');
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('user.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/user/aproval.blade.php ENDPATH**/ ?>