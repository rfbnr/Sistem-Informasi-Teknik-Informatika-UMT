<?php $__env->startSection('title', 'Digital Document Signing'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Digital Document Signing</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('approval-request.status')); ?>">My Documents</a></li>
                        <li class="breadcrumb-item active">Sign Document</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Document Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Document Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Document Name:</td>
                                    <td><?php echo e($approvalRequest->document_name); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Document Number:</td>
                                    <td><?php echo e($approvalRequest->full_document_number); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Submitted By:</td>
                                    <td><?php echo e($approvalRequest->user->name); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Submission Date:</td>
                                    <td><?php echo e($approvalRequest->created_at->format('d F Y')); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Current Status:</td>
                                    <td>
                                        <span class="badge badge-info"><?php echo e($approvalRequest->status_label); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Digital Signature:</td>
                                    <td>
                                        <?php if($digitalSignature): ?>
                                            <span class="badge badge-success"><?php echo e($digitalSignature->algorithm); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Not Available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Security Level:</td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php echo e($digitalSignature ? $digitalSignature->key_length . ' bits' : 'N/A'); ?>

                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signature Canvas -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Signature Canvas</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="previewBtn">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetCanvasBtn">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Canvas Container -->
                    <div id="canvasContainer" class="position-relative border rounded" style="background-color: #f8f9fa;">
                        <canvas id="signatureCanvas"
                                width="800"
                                height="600"
                                class="w-100"
                                style="max-width: 100%; height: auto; cursor: crosshair;">
                            Your browser does not support HTML5 Canvas.
                        </canvas>

                        <!-- Canvas Overlay Elements -->
                        <div id="canvasOverlay" class="position-absolute" style="top: 0; left: 0; pointer-events: none;">
                            <!-- QR Code -->
                            <div id="qrCodeElement" class="position-absolute draggable-element"
                                 style="left: 50px; top: 50px; pointer-events: all;">
                                <div class="qr-placeholder border border-dashed border-secondary d-flex align-items-center justify-content-center"
                                     style="width: 150px; height: 150px; background-color: rgba(255,255,255,0.9);">
                                    <div class="text-center">
                                        <i class="fas fa-qrcode fa-2x text-muted mb-2"></i>
                                        <div class="small text-muted">QR Code</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Signature Area -->
                            <div id="signatureElement" class="position-absolute draggable-element"
                                 style="left: 220px; top: 50px; pointer-events: all;">
                                <div class="signature-placeholder border border-dashed border-primary d-flex align-items-center justify-content-center"
                                     style="width: 200px; height: 100px; background-color: rgba(255,255,255,0.9);">
                                    <div class="text-center">
                                        <i class="fas fa-signature fa-2x text-primary mb-2"></i>
                                        <div class="small text-primary">Signature Area</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Text Information -->
                            <div id="textInfoElement" class="position-absolute draggable-element"
                                 style="left: 220px; top: 160px; pointer-events: all;">
                                <div class="text-info-placeholder border border-dashed border-info p-2"
                                     style="width: 300px; background-color: rgba(255,255,255,0.9);">
                                    <div class="small">
                                        <div class="font-weight-bold">Yani Sugiyani, MM., M.Kom</div>
                                        <div>NIDN: 0419038004</div>
                                        <div>Prodi Teknik Informatika</div>
                                        <div class="text-muted">Fakultas Teknik - UMT</div>
                                        <div class="mt-1">Tangerang, <?php echo e(now()->format('d F Y')); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Logo Area -->
                            <div id="logoElement" class="position-absolute draggable-element"
                                 style="left: 550px; top: 50px; pointer-events: all;">
                                <div class="logo-placeholder border border-dashed border-success d-flex align-items-center justify-content-center"
                                     style="width: 120px; height: 120px; background-color: rgba(255,255,255,0.9);">
                                    <div class="text-center">
                                        <i class="fas fa-university fa-2x text-success mb-2"></i>
                                        <div class="small text-success">Logo</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Canvas Tools -->
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brushSize" class="small font-weight-bold">Brush Size:</label>
                                    <input type="range" class="form-control-range" id="brushSize"
                                           min="1" max="10" value="3">
                                    <small class="text-muted">Current: <span id="brushSizeValue">3</span>px</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="brushColor" class="small font-weight-bold">Pen Color:</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" class="form-control form-control-sm mr-2"
                                               id="brushColor" value="#000000" style="width: 50px;">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-dark color-preset" data-color="#000000">Black</button>
                                            <button type="button" class="btn btn-outline-primary color-preset" data-color="#007bff">Blue</button>
                                            <button type="button" class="btn btn-outline-success color-preset" data-color="#28a745">Green</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signing Actions -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Draw your signature in the signature area, then click "Sign Document" to complete the process.
                                </small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-success" id="signDocumentBtn" disabled>
                                    <i class="fas fa-file-signature"></i> Sign Document
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signing Information & Controls -->
        <div class="col-lg-4">
            <!-- Progress -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Signing Progress</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold">Document Preparation</span>
                            <span class="small">100%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold">Signature Creation</span>
                            <span class="small" id="signatureProgress">0%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" id="signatureProgressBar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold">Digital Verification</span>
                            <span class="small" id="verificationProgress">Pending</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" id="verificationProgressBar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Security Information</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h6 font-weight-bold text-primary">
                                    <?php echo e($digitalSignature ? $digitalSignature->algorithm : 'N/A'); ?>

                                </div>
                                <div class="small text-muted">Algorithm</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h6 font-weight-bold text-success">
                                <?php echo e($digitalSignature ? $digitalSignature->key_length : 'N/A'); ?> bits
                            </div>
                            <div class="small text-muted">Key Length</div>
                        </div>
                    </div>
                    <hr>
                    <div class="small text-muted">
                        <i class="fas fa-shield-alt text-success"></i>
                        Your signature will be cryptographically secured and timestamped.
                        The integrity of this document can be verified using the QR code.
                    </div>
                </div>
            </div>

            <!-- Template Controls -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Layout Template</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="templateSelect" class="small font-weight-bold">Select Template:</label>
                        <select class="form-control form-control-sm" id="templateSelect">
                            <option value="default">Default Layout</option>
                            <option value="compact">Compact Layout</option>
                            <option value="detailed">Detailed Layout</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="showGridLines" checked>
                            <label class="custom-control-label" for="showGridLines">Show Grid Lines</label>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="enableSnapping" checked>
                            <label class="custom-control-label" for="enableSnapping">Enable Snapping</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signing Modal -->
<div class="modal fade" id="signingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-signature"></i> Signing Document
                </h5>
            </div>
            <div class="modal-body text-center">
                <div id="signingSpinner" class="mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Signing...</span>
                    </div>
                </div>
                <div id="signingStatus">
                    <p class="mb-0">Creating digital signature...</p>
                    <small class="text-muted">This may take a few moments</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/signature-interface.css')); ?>">
<style>
.draggable-element {
    cursor: move;
    border: 2px dashed transparent;
    transition: border-color 0.2s;
}
.draggable-element:hover {
    border-color: #007bff !important;
}
.draggable-element.dragging {
    border-color: #28a745 !important;
    opacity: 0.8;
}
#signatureCanvas {
    border: 2px solid #dee2e6;
    border-radius: 0.25rem;
}
.qr-placeholder, .signature-placeholder, .text-info-placeholder, .logo-placeholder {
    border-radius: 0.25rem;
    font-size: 0.875rem;
}
.color-preset {
    width: 40px;
}
.progress {
    border-radius: 10px;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('assets/js/signature-canvas.js')); ?>"></script>
<script>
$(document).ready(function() {
    // Initialize signature canvas
    const signatureCanvas = new SignatureCanvas({
        canvasId: 'signatureCanvas',
        approvalRequestId: <?php echo e($approvalRequest->id); ?>,
        digitalSignatureId: <?php echo e($digitalSignature ? $digitalSignature->id : 'null'); ?>,
        canvasData: <?php echo json_encode($canvasData ?? null, 15, 512) ?>
    });

    // Update progress when signature is drawn
    signatureCanvas.on('signatureDrawn', function() {
        updateSignatureProgress(50);
        $('#signDocumentBtn').prop('disabled', false);
    });

    // Handle signing
    $('#signDocumentBtn').click(function() {
        if (signatureCanvas.hasSignature()) {
            $('#signingModal').modal('show');
            signatureCanvas.processSignature();
        } else {
            alert('Please draw your signature first.');
        }
    });

    // Brush size control
    $('#brushSize').on('input', function() {
        const size = $(this).val();
        $('#brushSizeValue').text(size);
        signatureCanvas.setBrushSize(size);
    });

    // Color presets
    $('.color-preset').click(function() {
        const color = $(this).data('color');
        $('#brushColor').val(color);
        signatureCanvas.setBrushColor(color);
        $('.color-preset').removeClass('active');
        $(this).addClass('active');
    });

    // Reset canvas
    $('#resetCanvasBtn').click(function() {
        if (confirm('Are you sure you want to reset the canvas?')) {
            signatureCanvas.reset();
            updateSignatureProgress(0);
            $('#signDocumentBtn').prop('disabled', true);
        }
    });

    function updateSignatureProgress(percentage) {
        $('#signatureProgress').text(percentage + '%');
        $('#signatureProgressBar').css('width', percentage + '%');

        if (percentage >= 50) {
            $('#signatureProgressBar').removeClass('bg-warning').addClass('bg-success');
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/components/signature-canvas.blade.php ENDPATH**/ ?>