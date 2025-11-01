{{-- resources/views/digital-signature/user/sign-document.blade.php --}}
{{-- REFACTORED: QR Code Drag & Drop (No Signature Template) --}}
@extends('user.layouts.app')

@section('title', 'Digital Document Signing - QR Drag & Drop')

@push('styles')
<style>
/* ========== GENERAL LAYOUT ========== */
.signing-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

/* ========== SIGNING STEPS INDICATOR ========== */
.signing-steps {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.step-number.active {
    background: rgba(255, 255, 255, 1);
    color: #667eea;
}

.step-number.completed {
    background: #28a745;
    color: white;
}

/* ========== QR CODE SECTION ========== */
.qr-code-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.qr-code-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 2px dashed #007bff;
}

.qr-code-item {
    background: white;
    border: 2px solid #007bff;
    border-radius: 0.75rem;
    padding: 1.5rem;
    cursor: grab;
    transition: all 0.3s ease;
    text-align: center;
    max-width: 300px;
}

.qr-code-item:hover {
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    transform: translateY(-2px);
}

.qr-code-item.dragging {
    opacity: 0.5;
    cursor: grabbing;
}

.qr-code-preview-img {
    width: 200px;
    height: 200px;
    object-fit: contain;
    margin-bottom: 1rem;
}

.qr-info h6 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.qr-info p {
    font-size: 14px;
    color: #666;
    margin-bottom: 0;
}

/* ========== PDF PREVIEW CONTAINER ========== */
.pdf-preview-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.pdf-preview-wrapper {
    position: relative;
    border: 2px solid #dee2e6;
    border-radius: 0.5rem;
    background: #f8f9fa;
    overflow: auto;
    max-height: 800px;
}

#pdfCanvas {
    display: block;
    margin: 0 auto;
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

#qrOverlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.placed-qr {
    position: absolute;
    border: 2px dashed #28a745;
    background: rgba(40, 167, 69, 0.1);
    cursor: move;
    pointer-events: all;
    border-radius: 0.25rem;
    /* padding: 0.5rem; */
    z-index: 10;
}

.placed-qr:hover {
    border-color: #218838;
    background: rgba(40, 167, 69, 0.2);
}

.placed-qr.selected {
    border-color: #007bff;
    border-width: 3px;
    box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
}

.placed-qr img {
    width: 100%;
    height: auto;
    display: block;
    pointer-events: none;
}

.qr-handles {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
    cursor: nwse-resize;
}

.handle-nw { top: -5px; left: -5px; cursor: nwse-resize; }
.handle-ne { top: -5px; right: -5px; cursor: nesw-resize; }
.handle-sw { bottom: -5px; left: -5px; cursor: nesw-resize; }
.handle-se { bottom: -5px; right: -5px; cursor: nwse-resize; }

.delete-qr-btn {
    position: absolute;
    top: -10px;
    right: -10px;
    width: 24px;
    height: 24px;
    background: #dc3545;
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
}

.delete-qr-btn:hover {
    background: #c82333;
}

/* ========== PAGE NAVIGATION ========== */
.page-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.page-navigation button {
    padding: 0.5rem 1rem;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.page-navigation button:hover:not(:disabled) {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.page-navigation button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    font-weight: 600;
    color: #333;
}

/* ========== SIGNING CONTROLS (STICKY BOTTOM) ========== */
.signing-controls {
    position: sticky;
    bottom: 2rem;
    margin-bottom: 2rem;
    margin-right: 2rem;
    margin-left: 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    border: 1px solid #dee2e6;
    z-index: 100;
}

/* ========== LOADING OVERLAY ========== */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-overlay.active {
    display: flex;
}

.loading-content {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    text-align: center;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .signing-container {
        padding: 1rem;
    }

    .signing-steps {
        padding: 1rem;
    }

    .step-indicator {
        flex-direction: column;
        gap: 0.75rem;
    }

    .step-item {
        flex-direction: row;
        justify-content: flex-start;
        gap: 0.75rem;
    }

    .step-number {
        width: 35px;
        height: 35px;
        margin-bottom: 0;
    }

    .pdf-preview-wrapper {
        max-height: 400px;
    }

    .signing-controls {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        margin: 0;
        border-radius: 1rem 1rem 0 0;
        padding: 1rem;
    }

    .signing-controls .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .signing-controls button {
        width: 100%;
    }
}

/* ========== VISUAL GUIDE / TOOLTIP ========== */
.visual-guide {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    padding: 2rem;
    max-width: 500px;
    z-index: 10000;
    display: none;
}

.visual-guide.active {
    display: block;
}

.visual-guide-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: none;
}

.visual-guide-overlay.active {
    display: block;
}

.guide-step {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: start;
    gap: 1rem;
}

.guide-step-number {
    width: 30px;
    height: 30px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.guide-step-content h6 {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.guide-step-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

/* ========== QR SIZE PRESETS ========== */
.qr-size-presets {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.qr-size-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    font-weight: 500;
}

.qr-size-btn:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.qr-size-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

/* ========== QR CONTROL BUTTONS ========== */
.qr-controls {
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.qr-control-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qr-control-btn:hover:not(:disabled) {
    background: #f8f9fa;
    border-color: #007bff;
}

.qr-control-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qr-control-btn i {
    font-size: 12px;
}

/* ========== KEYBOARD SHORTCUTS INDICATOR ========== */
.keyboard-shortcuts {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

.keyboard-shortcuts h6 {
    margin: 0 0 0.75rem 0;
    font-size: 14px;
    color: #333;
}

.shortcut-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem;
}

.shortcut-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 13px;
}

.shortcut-key {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.15rem 0.4rem;
    font-family: monospace;
    font-size: 12px;
    font-weight: bold;
}

/* ========== PREVIEW MODAL ========== */
.preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.preview-modal.active {
    display: flex;
}

.preview-modal-content {
    background: white;
    border-radius: 1rem;
    max-width: 1000px;
    max-height: 90vh;
    overflow: auto;
    padding: 2rem;
}

.preview-canvas-wrapper {
    border: 2px solid #dee2e6;
    border-radius: 0.5rem;
    background: #f8f9fa;
    padding: 1rem;
    margin: 1rem 0;
    text-align: center;
}

#previewCanvas {
    max-width: 100%;
    height: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .qr-handles {
        width: 24px;
        height: 24px;
        background: #28a745;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .handle-nw { top: -12px; left: -12px; }
    .handle-ne { top: -12px; right: -12px; }
    .handle-sw { bottom: -12px; left: -12px; }
    .handle-se { bottom: -12px; right: -12px; }

    .delete-qr-btn {
        width: 32px;
        height: 32px;
        top: -16px;
        right: -16px;
        font-size: 18px;
    }

    .qr-code-item,
    .placed-qr {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
    }
}
</style>
@endpush

@section('content')
<!-- Section Header -->
<section id="header-section">
    <h1>Signing Digital Document</h1>
</section>

<div class="signing-container">
    <!-- Signing Steps Indicator -->
    <div class="signing-steps">
        <div class="step-indicator">
            <div class="step-item">
                <div class="step-number completed">1</div>
                <small>Document Review</small>
            </div>
            <div class="step-item">
                <div class="step-number active">2</div>
                <small>Position QR Code</small>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <small>Complete Signing</small>
            </div>
        </div>
        <h5 class="mb-0">Step 2: Position QR Code on Document</h5>
        <p class="mb-0 opacity-75">Drag and drop the QR code to your desired position on the document</p>
    </div>

    <!-- QR Code Section -->
    <div class="qr-code-section">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                 style="width: 50px; height: 50px;">
                <i class="fas fa-qrcode"></i>
            </div>
            <div>
                <h4 class="mb-1">Verification QR Code</h4>
                <p class="text-muted mb-0">Drag this QR code to the document below to position it</p>
            </div>
        </div>

        <div class="qr-code-wrapper" id="qrCodeWrapper">
            @if($documentSignature && $documentSignature->temporary_qr_code_path)
                <div class="qr-code-item" id="qrCodeItem" draggable="true">
                    <img src="{{ Storage::url($documentSignature->temporary_qr_code_path) }}"
                         alt="Verification QR Code"
                         class="qr-code-preview-img"
                         id="qrCodeImage">
                    <div class="qr-info">
                        <h6><i class="fas fa-qrcode me-2"></i>Document Verification QR</h6>
                        <p>Drag this QR code to your preferred position on the PDF document</p>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Temporary QR code not found. Please contact administrator.
                </div>
            @endif
        </div>

        <!-- QR Size Presets & Controls -->
        <div class="qr-size-presets">
            <button class="qr-size-btn" onclick="setQRSize('small')">
                <i class="fas fa-compress-alt me-1"></i> Small (80x80)
            </button>
            <button class="qr-size-btn active" onclick="setQRSize('medium')">
                <i class="fas fa-expand-alt me-1"></i> Medium (100x100)
            </button>
            <button class="qr-size-btn" onclick="setQRSize('large')">
                <i class="fas fa-expand me-1"></i> Large (150x150)
            </button>
        </div>

        <!-- QR Control Buttons -->
        <div class="qr-controls">
            <button class="qr-control-btn" id="resetQRBtn" onclick="resetQRPosition()" disabled>
                <i class="fas fa-redo"></i> Reset Position
            </button>
            <button class="qr-control-btn" id="undoBtn" onclick="undo()" disabled>
                <i class="fas fa-undo"></i> Undo
            </button>
            <button class="qr-control-btn" id="redoBtn" onclick="redo()" disabled>
                <i class="fas fa-redo"></i> Redo
            </button>
            <button class="qr-control-btn" onclick="showGuide()">
                <i class="fas fa-question-circle"></i> Help
            </button>
        </div>

        <!-- Keyboard Shortcuts -->
        {{-- <div class="keyboard-shortcuts">
            <h6><i class="fas fa-keyboard me-2"></i>Keyboard Shortcuts</h6>
            <div class="shortcut-list">
                <div class="shortcut-item">
                    <span class="shortcut-key">↑ ↓ ← →</span>
                    <span>Move QR (1px)</span>
                </div>
                <div class="shortcut-item">
                    <span class="shortcut-key">Shift + ↑↓←→</span>
                    <span>Move QR (10px)</span>
                </div>
                <div class="shortcut-item">
                    <span class="shortcut-key">Ctrl + Z</span>
                    <span>Undo</span>
                </div>
                <div class="shortcut-item">
                    <span class="shortcut-key">Ctrl + Y</span>
                    <span>Redo</span>
                </div>
                <div class="shortcut-item">
                    <span class="shortcut-key">Delete</span>
                    <span>Remove QR</span>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Document Information & PDF Preview -->
    <div class="pdf-preview-section">
        <div class="mb-3">
            <h4 class="mb-3">
                <i class="fas fa-file-alt text-primary me-2"></i>
                {{ $approvalRequest->document_name }}
            </h4>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <strong>Document Number:</strong><br>
                            <span class="text-muted">{{ $approvalRequest->full_document_number }}</span>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>Document Type:</strong><br>
                            <span class="text-muted">{{ $approvalRequest->document_type ?? 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <strong>Submitted:</strong><br>
                            <span class="text-muted">{{ $approvalRequest->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="bg-success text-white rounded p-3">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <div><strong>Approved</strong></div>
                        <small>Ready for signing</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDF Preview with QR Overlay -->
        <div class="pdf-preview-wrapper" id="pdfPreviewWrapper">
            <canvas id="pdfCanvas"></canvas>
            <div id="qrOverlay"></div>
        </div>

        <!-- Page Navigation -->
        <div class="page-navigation">
            <button id="prevPageBtn" onclick="previousPage()">
                <i class="fas fa-chevron-left"></i> <span class="btn-text">Previous</span>
            </button>
            <div class="page-info">
                Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
            </div>
            <button id="nextPageBtn" onclick="nextPage()">
                <span class="btn-text">Next</span> <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Signing Controls (Sticky Bottom) -->
<div class="signing-controls">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="confirmSignature">
                <label class="form-check-label" for="confirmSignature">
                    I confirm that the QR code position is correct and I authorize this document
                </label>
            </div>
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>
                This signature will be legally binding and cryptographically secured with a unique key
            </small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="goBack()">
                <i class="fas fa-arrow-left me-1"></i> Back
            </button>
            <button class="btn btn-warning" id="previewBtn" onclick="showPreview()" disabled>
                <i class="fas fa-eye me-1"></i> Preview
            </button>
            <button class="btn btn-success" id="signBtn" onclick="confirmAndSign()" disabled>
                <i class="fas fa-signature me-1"></i> Sign Document
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <h5>Processing Digital Signature...</h5>
        <p class="text-muted mb-0">Generating unique encryption key and signing your document</p>
        <p class="text-muted"><small>Please wait, this may take a moment...</small></p>
    </div>
</div>

<!-- Visual Guide / Tutorial -->
<div class="visual-guide-overlay" id="visualGuideOverlay" onclick="hideGuide()"></div>
<div class="visual-guide" id="visualGuide">
    <h4 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>How to Sign Your Document</h4>

    <div class="guide-step">
        <div class="guide-step-number">1</div>
        <div class="guide-step-content">
            <h6>Drag the QR Code</h6>
            <p>Click and drag the QR code from the box above onto your PDF document at your desired position.</p>
        </div>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">2</div>
        <div class="guide-step-content">
            <h6>Adjust Size & Position</h6>
            <p>Use the size presets (Small/Medium/Large) or drag the corner handles to resize. You can also use arrow keys for precise positioning.</p>
        </div>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">3</div>
        <div class="guide-step-content">
            <h6>Preview Your Signature</h6>
            <p>Click the "Preview" button to see how your document will look after signing.</p>
        </div>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">4</div>
        <div class="guide-step-content">
            <h6>Sign the Document</h6>
            <p>Check the confirmation box and click "Sign Document". A unique encryption key will be generated automatically.</p>
        </div>
    </div>

    <div class="alert alert-info mt-3 mb-0">
        <i class="fas fa-lightbulb me-2"></i>
        <strong>Pro Tip:</strong> Use Ctrl+Z to undo and Ctrl+Y to redo your changes!
    </div>

    <div class="mt-3 text-end">
        <button class="btn btn-primary" onclick="hideGuide()">
            <i class="fas fa-check me-1"></i> Got it!
        </button>
        <label class="form-check-label ms-3">
            <input type="checkbox" class="form-check-input" id="dontShowAgain">
            Don't show this again
        </label>
    </div>
</div>

<!-- Preview Modal -->
<div class="preview-modal" id="previewModal" onclick="hidePreview(event)">
    <div class="preview-modal-content" onclick="event.stopPropagation()">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-eye me-2"></i>Document Preview</h4>
            <button class="btn btn-sm btn-outline-secondary" onclick="hidePreview()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            This is how your document will appear after signing. The QR code will be embedded at the position you selected.
        </div>

        <div class="preview-canvas-wrapper">
            <canvas id="previewCanvas"></canvas>
        </div>

        <div class="mt-3">
            <strong>Document Details:</strong>
            <ul class="list-unstyled mt-2">
                <li><strong>Document:</strong> {{ $approvalRequest->document_name }}</li>
                {{-- <li><strong>Number:</strong> {{ $approvalRequest->full_document_number }}</li> --}}
                {{-- <li><strong>Signer:</strong> {{ auth()->user()->name }}</li> --}}
                <li><strong>Signer:</strong> {{ $approvalRequest->approver->name }}</li>
                <li><strong>Signing Time:</strong> <span id="signingTimestamp"></span></li>
                <li><strong>Encryption:</strong> RSA-2048 with SHA-256 (Unique key per document)</li>
            </ul>
        </div>

        <div class="text-end mt-3">
            <button class="btn btn-secondary" onclick="hidePreview()">Close</button>
            <button class="btn btn-success" onclick="hidePreview(); confirmAndSign();">
                <i class="fas fa-signature me-1"></i> Proceed to Sign
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- PDF.js Library --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
// ==================== GLOBAL VARIABLES ====================
let pdfDocument = null;
let currentPage = 1;
let totalPages = 0;
let pageRendering = false;
let pageNumPending = null;
let canvas = document.getElementById('pdfCanvas');
let ctx = canvas.getContext('2d');

let placedQR = null;
let qrCodeImageSrc = "{{ $documentSignature && $documentSignature->temporary_qr_code_path ? Storage::url($documentSignature->temporary_qr_code_path) : '' }}";

const approvalRequestId = {{ $approvalRequest->id }};
const documentPath = "{{ Storage::url($approvalRequest->document_path) }}";

// Touch support variables
let isTouchDevice = false;
let touchStartX = 0;
let touchStartY = 0;
let isDraggingQR = false;

// Undo/Redo history
let qrHistory = [];
let historyIndex = -1;
const MAX_HISTORY = 50;

// QR size presets
const QR_SIZES = {
    small: { width: 80, height: 80 },
    medium: { width: 100, height: 100 },
    large: { width: 150, height: 150 }
};

let currentQRSize = 'medium';

// ==================== UTILITY FUNCTIONS ====================
function detectTouchDevice() {
    isTouchDevice = ('ontouchstart' in window) ||
                    (navigator.maxTouchPoints > 0) ||
                    (navigator.msMaxTouchPoints > 0);
    console.log('Touch device detected:', isTouchDevice);
    return isTouchDevice;
}

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing QR code signing interface...');

    detectTouchDevice();

    // Initialize PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    // Load PDF document
    loadPDF();

    // Setup drag & drop for QR code
    setupQRDragDrop();

    // Setup event listeners
    setupEventListeners();

    // Show guide for first-time users
    showGuideIfFirstTime();

    // Setup keyboard shortcuts
    // setupKeyboardShortcuts();
});

// ==================== VISUAL GUIDE ====================
function showGuideIfFirstTime() {
    const hasSeenGuide = localStorage.getItem('qr_signing_guide_seen');
    if (!hasSeenGuide) {
        setTimeout(() => showGuide(), 500);
    }
}

function showGuide() {
    document.getElementById('visualGuideOverlay').classList.add('active');
    document.getElementById('visualGuide').classList.add('active');
}

function hideGuide() {
    document.getElementById('visualGuideOverlay').classList.remove('active');
    document.getElementById('visualGuide').classList.remove('active');

    const dontShowAgain = document.getElementById('dontShowAgain').checked;
    if (dontShowAgain) {
        localStorage.setItem('qr_signing_guide_seen', 'true');
    }
}

// ==================== PDF RENDERING ====================
async function loadPDF() {
    try {
        console.log('Loading PDF:', documentPath);

        const loadingTask = pdfjsLib.getDocument(documentPath);
        pdfDocument = await loadingTask.promise;
        totalPages = pdfDocument.numPages;

        document.getElementById('totalPages').textContent = totalPages;

        console.log(`PDF loaded successfully. Total pages: ${totalPages}`);

        renderPage(currentPage);

    } catch (error) {
        console.error('Error loading PDF:', error);
        alert('Failed to load PDF document. Please try again.');
    }
}

async function renderPage(pageNumber) {
    if (pageRendering) {
        pageNumPending = pageNumber;
        return;
    }

    pageRendering = true;

    try {
        console.log(`Rendering page ${pageNumber}...`);

        const page = await pdfDocument.getPage(pageNumber);
        const viewport = page.getViewport({ scale: 1.1 });

        canvas.height = viewport.height;
        canvas.width = viewport.width;

        // Adjust overlay size
        const overlay = document.getElementById('qrOverlay');
        overlay.style.width = viewport.width + 'px';
        overlay.style.height = viewport.height + 'px';

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        await page.render(renderContext).promise;

        pageRendering = false;

        if (pageNumPending !== null) {
            renderPage(pageNumPending);
            pageNumPending = null;
        }

        console.log(`Page ${pageNumber} rendered successfully`);

        updatePageNavigation();

    } catch (error) {
        console.error('Error rendering page:', error);
        pageRendering = false;
    }
}

function updatePageNavigation() {
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('prevPageBtn').disabled = currentPage <= 1;
    document.getElementById('nextPageBtn').disabled = currentPage >= totalPages;
}

function previousPage() {
    if (currentPage <= 1) return;
    currentPage--;
    renderPage(currentPage);
}

function nextPage() {
    if (currentPage >= totalPages) return;
    currentPage++;
    renderPage(currentPage);
}

// ==================== QR CODE DRAG & DROP ====================
function setupQRDragDrop() {
    const qrCodeItem = document.getElementById('qrCodeItem');
    const pdfWrapper = document.getElementById('pdfPreviewWrapper');

    if (!qrCodeItem) {
        console.error('QR code item not found');
        return;
    }

    // Desktop drag events
    if (!isTouchDevice) {
        qrCodeItem.addEventListener('dragstart', handleQRDragStart);
        qrCodeItem.addEventListener('dragend', handleQRDragEnd);
    }

    // Touch events for mobile
    if (isTouchDevice) {
        qrCodeItem.addEventListener('touchstart', handleQRTouchStart, { passive: false });
        qrCodeItem.addEventListener('touchmove', handleQRTouchMove, { passive: false });
        qrCodeItem.addEventListener('touchend', handleQRTouchEnd, { passive: false });
    }

    // PDF drop zone events
    pdfWrapper.addEventListener('dragover', handlePDFDragOver);
    pdfWrapper.addEventListener('dragleave', handlePDFDragLeave);
    pdfWrapper.addEventListener('drop', handlePDFDrop);
}

function handleQRDragStart(e) {
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('qrcode', 'true');
    console.log('QR drag started');
}

function handleQRDragEnd(e) {
    e.target.classList.remove('dragging');
    console.log('QR drag ended');
}

function handleQRTouchStart(e) {
    isDraggingQR = true;
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
    e.currentTarget.classList.add('dragging');
    console.log('QR touch start');
}

function handleQRTouchMove(e) {
    if (!isDraggingQR) return;

    e.preventDefault();

    const touch = e.touches[0];
    const moveX = Math.abs(touch.clientX - touchStartX);
    const moveY = Math.abs(touch.clientY - touchStartY);

    if (moveX > 10 || moveY > 10) {
        const canvasRect = canvas.getBoundingClientRect();
        const pdfWrapper = document.getElementById('pdfPreviewWrapper');

        if (touch.clientX >= canvasRect.left && touch.clientX <= canvasRect.right &&
            touch.clientY >= canvasRect.top && touch.clientY <= canvasRect.bottom) {
            pdfWrapper.style.borderColor = '#28a745';
            pdfWrapper.style.background = 'rgba(40, 167, 69, 0.05)';
        } else {
            pdfWrapper.style.borderColor = '#dee2e6';
            pdfWrapper.style.background = '#f8f9fa';
        }
    }
}

function handleQRTouchEnd(e) {
    if (!isDraggingQR) return;

    e.preventDefault();

    const touch = e.changedTouches[0];
    const canvasRect = canvas.getBoundingClientRect();

    if (touch.clientX >= canvasRect.left && touch.clientX <= canvasRect.right &&
        touch.clientY >= canvasRect.top && touch.clientY <= canvasRect.bottom) {

        const x = touch.clientX - canvasRect.left;
        const y = touch.clientY - canvasRect.top;

        console.log('QR touch drop at:', { x, y });
        placeQROnPDF(x, y);
    }

    e.currentTarget.classList.remove('dragging');
    isDraggingQR = false;
    document.getElementById('pdfPreviewWrapper').style.borderColor = '#dee2e6';
    document.getElementById('pdfPreviewWrapper').style.background = '#f8f9fa';
}

function handlePDFDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.currentTarget.style.borderColor = '#28a745';
    e.currentTarget.style.background = 'rgba(40, 167, 69, 0.05)';
}

function handlePDFDragLeave(e) {
    e.currentTarget.style.borderColor = '#dee2e6';
    e.currentTarget.style.background = '#f8f9fa';
}

function handlePDFDrop(e) {
    e.preventDefault();
    e.currentTarget.style.borderColor = '#dee2e6';
    e.currentTarget.style.background = '#f8f9fa';

    const hasQR = e.dataTransfer.getData('qrcode');

    if (!hasQR) {
        console.warn('No QR code in drop data');
        return;
    }

    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    console.log('QR dropped at:', { x, y });

    placeQROnPDF(x, y);
}

// ==================== QR PLACEMENT ====================
function placeQROnPDF(x, y) {
    // Remove existing QR if any
    if (placedQR) {
        placedQR.remove();
    }

    const overlay = document.getElementById('qrOverlay');

    // Create QR element
    const qrDiv = document.createElement('div');
    qrDiv.className = 'placed-qr selected';
    qrDiv.id = 'placedQR';
    qrDiv.style.left = x + 'px';
    qrDiv.style.top = y + 'px';
    qrDiv.style.width = '100px';
    qrDiv.style.height = '100px';

    // Add QR image
    const img = document.createElement('img');
    img.src = qrCodeImageSrc;
    img.alt = 'Verification QR Code';
    qrDiv.appendChild(img);

    // Add delete button
    const deleteBtn = document.createElement('div');
    deleteBtn.className = 'delete-qr-btn';
    deleteBtn.innerHTML = '×';
    deleteBtn.onclick = removeQR;
    qrDiv.appendChild(deleteBtn);

    // Add resize handles
    // ['nw', 'ne', 'sw', 'se'].forEach(pos => {
    //     const handle = document.createElement('div');
    //     handle.className = `qr-handles handle-${pos}`;
    //     qrDiv.appendChild(handle);
    // });

    // Add resize handles with heigh visibility for better UX
    ['se'].forEach(pos => {
        const handle = document.createElement('div');
        handle.className = `qr-handles handle-${pos}`;
        qrDiv.appendChild(handle);
    });


    overlay.appendChild(qrDiv);
    placedQR = qrDiv;

    // Make draggable and resizable
    makeQRDraggable(qrDiv);
    makeQRResizable(qrDiv);

    // Enable sign button
    updateButtonStates();

    // Save to history for undo/redo
    saveToHistory();

    console.log('QR placed successfully');
}

// ==================== QR SIZE PRESETS ====================
function setQRSize(size) {
    if (!placedQR) {
        alert('Please place the QR code first.');
        return;
    }

    currentQRSize = size;
    const sizeConfig = QR_SIZES[size];

    placedQR.style.width = sizeConfig.width + 'px';
    placedQR.style.height = sizeConfig.height + 'px';

    // Update active button
    document.querySelectorAll('.qr-size-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.qr-size-btn').classList.add('active');

    // Save to history
    saveToHistory();

    console.log('QR size changed to:', size);
}

// ==================== RESET QR POSITION ====================
function resetQRPosition() {
    if (!placedQR) return;

    // Reset to center of canvas
    const canvasRect = canvas.getBoundingClientRect();
    const centerX = (canvasRect.width / 2) - (placedQR.offsetWidth / 2);
    const centerY = (canvasRect.height / 2) - (placedQR.offsetHeight / 2);

    placedQR.style.left = centerX + 'px';
    placedQR.style.top = centerY + 'px';

    // Save to history
    saveToHistory();

    console.log('QR position reset to center');
}

// ==================== UNDO / REDO FUNCTIONALITY ====================
function saveToHistory() {
    if (!placedQR) return;

    const state = {
        left: placedQR.style.left,
        top: placedQR.style.top,
        width: placedQR.style.width,
        height: placedQR.style.height
    };

    // Remove all states after current index
    qrHistory = qrHistory.slice(0, historyIndex + 1);

    // Add new state
    qrHistory.push(state);

    // Limit history size
    if (qrHistory.length > MAX_HISTORY) {
        qrHistory.shift();
    } else {
        historyIndex++;
    }

    updateUndoRedoButtons();
}

function undo() {
    if (historyIndex > 0 && placedQR) {
        historyIndex--;
        const state = qrHistory[historyIndex];
        applyHistoryState(state);
        updateUndoRedoButtons();
        console.log('Undo applied');
    }
}

function redo() {
    if (historyIndex < qrHistory.length - 1 && placedQR) {
        historyIndex++;
        const state = qrHistory[historyIndex];
        applyHistoryState(state);
        updateUndoRedoButtons();
        console.log('Redo applied');
    }
}

function applyHistoryState(state) {
    placedQR.style.left = state.left;
    placedQR.style.top = state.top;
    placedQR.style.width = state.width;
    placedQR.style.height = state.height;
}

function updateUndoRedoButtons() {
    document.getElementById('undoBtn').disabled = historyIndex <= 0 || !placedQR;
    document.getElementById('redoBtn').disabled = historyIndex >= qrHistory.length - 1 || !placedQR;
}

// ==================== KEYBOARD SHORTCUTS ====================
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        if (!placedQR) return;

        // Ctrl+Z - Undo
        if (e.ctrlKey && e.key === 'z') {
            e.preventDefault();
            undo();
            return;
        }

        // Ctrl+Y - Redo
        if (e.ctrlKey && e.key === 'y') {
            e.preventDefault();
            redo();
            return;
        }

        // Delete - Remove QR
        if (e.key === 'Delete') {
            e.preventDefault();
            removeQR();
            return;
        }

        // Arrow keys - Move QR
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
            e.preventDefault();

            const step = e.shiftKey ? 10 : 1; // Shift = 10px, normal = 1px
            const currentLeft = parseInt(placedQR.style.left) || 0;
            const currentTop = parseInt(placedQR.style.top) || 0;

            switch(e.key) {
                case 'ArrowUp':
                    placedQR.style.top = (currentTop - step) + 'px';
                    break;
                case 'ArrowDown':
                    placedQR.style.top = (currentTop + step) + 'px';
                    break;
                case 'ArrowLeft':
                    placedQR.style.left = (currentLeft - step) + 'px';
                    break;
                case 'ArrowRight':
                    placedQR.style.left = (currentLeft + step) + 'px';
                    break;
            }

            // Debounced save to history
            clearTimeout(window.keyboardMoveTimeout);
            window.keyboardMoveTimeout = setTimeout(() => saveToHistory(), 500);
        }
    });
}

function makeQRDraggable(element) {
    let isDragging = false;
    let initialX, initialY;

    element.addEventListener('mousedown', function(e) {
        if (e.target.classList.contains('qr-handles') || e.target.classList.contains('delete-qr-btn')) {
            return;
        }
        isDragging = true;
        initialX = e.clientX - element.offsetLeft;
        initialY = e.clientY - element.offsetTop;
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
        element.style.left = (e.clientX - initialX) + 'px';
        element.style.top = (e.clientY - initialY) + 'px';
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            saveToHistory(); // Save after drag complete
        }
    });

    // Touch support
    element.addEventListener('touchstart', function(e) {
        if (e.target.classList.contains('qr-handles') || e.target.classList.contains('delete-qr-btn')) {
            return;
        }
        e.preventDefault();
        isDragging = true;
        const touch = e.touches[0];
        initialX = touch.clientX - element.offsetLeft;
        initialY = touch.clientY - element.offsetTop;
    }, { passive: false });

    document.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
        const touch = e.touches[0];
        element.style.left = (touch.clientX - initialX) + 'px';
        element.style.top = (touch.clientY - initialY) + 'px';
    }, { passive: false });

    document.addEventListener('touchend', function() {
        if (isDragging) {
            isDragging = false;
            saveToHistory(); // Save after drag complete
        }
    });
}

function makeQRResizable(element) {
    const handles = element.querySelectorAll('.qr-handles');

    handles.forEach(handle => {
        let isResizing = false;
        let startX, startY, startWidth, startHeight, startLeft, startTop;
        let aspectRatio; // ✅ Store aspect ratio

        handle.addEventListener('mousedown', function(e) {
            e.stopPropagation();
            isResizing = true;
            startX = e.clientX;
            startY = e.clientY;
            startWidth = parseInt(window.getComputedStyle(element).width, 10);
            startHeight = parseInt(window.getComputedStyle(element).height, 10);
            startLeft = element.offsetLeft;
            startTop = element.offsetTop;

            // ✅ Calculate and lock aspect ratio (should be 1:1 for QR codes)
            aspectRatio = startWidth / startHeight;
        });

        document.addEventListener('mousemove', function(e) {
            if (!isResizing) return;
            applyResize(e.clientX, e.clientY);
        });

        document.addEventListener('mouseup', function() {
            if (isResizing) {
                isResizing = false;
                saveToHistory();
            }
        });

        // Touch support
        handle.addEventListener('touchstart', function(e) {
            e.stopPropagation();
            e.preventDefault();
            isResizing = true;
            const touch = e.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
            startWidth = parseInt(window.getComputedStyle(element).width, 10);
            startHeight = parseInt(window.getComputedStyle(element).height, 10);
            startLeft = element.offsetLeft;
            startTop = element.offsetTop;

            // ✅ Lock aspect ratio for touch
            aspectRatio = startWidth / startHeight;
        }, { passive: false });

        document.addEventListener('touchmove', function(e) {
            if (!isResizing) return;
            e.preventDefault();
            const touch = e.touches[0];
            applyResize(touch.clientX, touch.clientY);
        }, { passive: false });

        document.addEventListener('touchend', function() {
            if (isResizing) {
                isResizing = false;
                saveToHistory();
            }
        });

        function applyResize(clientX, clientY) {
            const dx = clientX - startX;
            const dy = clientY - startY;

            if (handle.classList.contains('handle-se')) {
                // ✅ Use the larger delta to maintain square aspect
                const delta = Math.max(Math.abs(dx), Math.abs(dy));
                const newSize = startWidth + (dx > 0 ? delta : -delta);

                // ✅ Set minimum and maximum size
                const minSize = 50;
                const maxSize = 300;
                const constrainedSize = Math.max(minSize, Math.min(maxSize, newSize));

                // ✅ Apply same size to width and height (square QR)
                element.style.width = constrainedSize + 'px';
                element.style.height = constrainedSize + 'px';
            }
        }
    });
}

// function makeQRResizableXX(element) {
//     const handles = element.querySelectorAll('.qr-handles');

//     handles.forEach(handle => {
//         let isResizing = false;
//         let startX, startY, startWidth, startHeight, startLeft, startTop;

//         handle.addEventListener('mousedown', function(e) {
//             e.stopPropagation();
//             isResizing = true;
//             startX = e.clientX;
//             startY = e.clientY;
//             startWidth = parseInt(window.getComputedStyle(element).width, 10);
//             startHeight = parseInt(window.getComputedStyle(element).height, 10);
//             startLeft = element.offsetLeft;
//             startTop = element.offsetTop;
//         });

//         document.addEventListener('mousemove', function(e) {
//             if (!isResizing) return;
//             applyResize(e.clientX, e.clientY);
//         });

//         document.addEventListener('mouseup', function() {
//             if (isResizing) {
//                 isResizing = false;
//                 saveToHistory(); // Save after resize complete
//             }
//         });

//         // Touch support
//         handle.addEventListener('touchstart', function(e) {
//             e.stopPropagation();
//             e.preventDefault();
//             isResizing = true;
//             const touch = e.touches[0];
//             startX = touch.clientX;
//             startY = touch.clientY;
//             startWidth = parseInt(window.getComputedStyle(element).width, 10);
//             startHeight = parseInt(window.getComputedStyle(element).height, 10);
//             startLeft = element.offsetLeft;
//             startTop = element.offsetTop;
//         }, { passive: false });

//         document.addEventListener('touchmove', function(e) {
//             if (!isResizing) return;
//             e.preventDefault();
//             const touch = e.touches[0];
//             applyResize(touch.clientX, touch.clientY);
//         }, { passive: false });

//         document.addEventListener('touchend', function() {
//             if (isResizing) {
//                 isResizing = false;
//                 saveToHistory(); // Save after resize complete
//             }
//         });

//         function applyResize(clientX, clientY) {
//             const dx = clientX - startX;
//             const dy = clientY - startY;

//             if (handle.classList.contains('handle-se')) {
//                 element.style.width = (startWidth + dx) + 'px';
//                 element.style.height = (startHeight + dy) + 'px';
//             }else if (handle.classList.contains('handle-sw')) {
//                 element.style.width = (startWidth - dx) + 'px';
//                 element.style.height = (startHeight + dy) + 'px';
//                 element.style.left = (startLeft + dx) + 'px';
//             } else if (handle.classList.contains('handle-ne')) {
//                 element.style.width = (startWidth + dx) + 'px';
//                 element.style.height = (startHeight - dy) + 'px';
//                 element.style.top = (startTop + dy) + 'px';
//             } else if (handle.classList.contains('handle-nw')) {
//                 element.style.width = (startWidth - dx) + 'px';
//                 element.style.height = (startHeight - dy) + 'px';
//                 element.style.left = (startLeft + dx) + 'px';
//                 element.style.top = (startTop + dy) + 'px';
//             }
//         }
//     });
// }

function removeQR() {
    if (placedQR) {
        placedQR.remove();
        placedQR = null;

        // Clear history
        qrHistory = [];
        historyIndex = -1;

        updateButtonStates();
        updateUndoRedoButtons();
        console.log('QR removed');
    }
}

// ==================== PREVIEW FUNCTIONALITY ====================
function showPreview() {
    if (!placedQR) {
        alert('Please place the QR code first.');
        return;
    }

    // Update timestamp
    document.getElementById('signingTimestamp').textContent = new Date().toLocaleString();

    // Copy PDF canvas to preview canvas
    const previewCanvas = document.getElementById('previewCanvas');
    const previewCtx = previewCanvas.getContext('2d');

    previewCanvas.width = canvas.width;
    previewCanvas.height = canvas.height;

    // Draw PDF
    previewCtx.drawImage(canvas, 0, 0);

    // Draw QR overlay
    const img = new Image();
    img.src = placedQR.querySelector('img').src;
    img.onload = function() {
        const rect = placedQR.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();

        const x = rect.left - canvasRect.left;
        const y = rect.top - canvasRect.top;
        const w = rect.width;
        const h = rect.height;

        previewCtx.drawImage(img, x, y, w, h);
    };

    // Show modal
    document.getElementById('previewModal').classList.add('active');
}

function hidePreview(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('previewModal').classList.remove('active');
}

// ==================== CONFIRMATION & SIGNING ====================
function confirmAndSign() {
    if (!placedQR) {
        alert('Please place the QR code on the document first.');
        return;
    }

    if (!document.getElementById('confirmSignature').checked) {
        alert('Please confirm your QR code placement by checking the confirmation box.');
        return;
    }

    // Show confirmation dialog
    if (confirm('Are you sure you want to sign this document? A unique encryption key will be generated and the document will be permanently signed.')) {
        signDocument();
    }
}

// ==================== EVENT LISTENERS ====================
function setupEventListeners() {
    document.getElementById('confirmSignature').addEventListener('change', updateButtonStates);
}

function updateButtonStates() {
    const hasQR = placedQR !== null;
    const isConfirmed = document.getElementById('confirmSignature').checked;

    document.getElementById('previewBtn').disabled = !hasQR;
    document.getElementById('signBtn').disabled = !hasQR || !isConfirmed;
    document.getElementById('resetQRBtn').disabled = !hasQR;
}

// ==================== SIGNING ====================
async function signDocument() {
    if (!placedQR) {
        alert('Please place the QR code on the document first.');
        return;
    }

    if (!document.getElementById('confirmSignature').checked) {
        alert('Please confirm your QR code placement.');
        return;
    }

    try {
        console.log('Starting document signing...');

        document.getElementById('loadingOverlay').classList.add('active');

        // Get QR positioning data
        const rect = placedQR.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();

        const qrPositioningData = {
            page: currentPage,
            position: {
                x: rect.left - canvasRect.left,
                y: rect.top - canvasRect.top
            },
            size: {
                width: rect.width,
                height: rect.height
            },
            canvas_dimensions: {
                width: canvas.width,
                height: canvas.height
            }
        };

        console.log('QR positioning data:', qrPositioningData);

        // Prepare form data
        const formData = new FormData();
        formData.append('qr_positioning_data', JSON.stringify(qrPositioningData));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        // Submit
        const response = await fetch(`/user/signature/sign/${approvalRequestId}/process`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        document.getElementById('loadingOverlay').classList.remove('active');

        if (data.success) {
            alert('Document signed successfully with unique encryption key!');
            window.location.href = '{{ route("user.signature.approval.status") }}';
        } else {
            throw new Error(data.error || 'Signing failed');
        }

    } catch (error) {
        console.error('Signing error:', error);
        document.getElementById('loadingOverlay').classList.remove('active');
        alert('Signing failed: ' + error.message);
    }
}

function goBack() {
    if (confirm('Are you sure you want to go back? Your QR placement will be lost.')) {
        window.location.href = '{{ route("user.signature.approval.status") }}';
    }
}

// Prevent accidental page reload
window.addEventListener('beforeunload', function(e) {
    if (placedQR) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>
@endpush
