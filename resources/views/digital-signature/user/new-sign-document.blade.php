{{-- resources/views/digital-signature/user/sign-document.blade.php --}}
{{-- REFACTORED: QR Code Drag & Drop with Side-by-Side Layout --}}
@extends('user.layouts.app')

@section('title', 'Digital Document Signing - QR Drag & Drop')

@push('styles')
<style>
/* ========== GENERAL LAYOUT ========== */
.signing-container {
    max-width: 1600px;
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

/* ========== NEW: SIDE-BY-SIDE LAYOUT FOR DESKTOP ========== */
.signing-workspace {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

/* ========== QR CODE SECTION (LEFT SIDEBAR) ========== */
.qr-code-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.qr-section-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.qr-icon-wrapper {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.qr-icon-wrapper i {
    color: white;
    font-size: 20px;
}

.qr-section-title h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.qr-section-title p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.qr-code-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.75rem;
    border: 2px dashed #28a745;
    margin-bottom: 1rem;
}

.qr-code-item {
    background: white;
    border: 2px solid #28a745;
    border-radius: 0.75rem;
    padding: 1rem;
    cursor: grab;
    transition: all 0.3s ease;
    text-align: center;
    max-width: 100%;
}

.qr-code-item:hover {
    box-shadow: 0 6px 16px rgba(40, 167, 69, 0.3);
    transform: translateY(-2px);
}

.qr-code-item.dragging {
    opacity: 0.6;
    cursor: grabbing;
    transform: scale(0.95);
}

.qr-code-preview-img {
    width: 180px;
    height: 180px;
    object-fit: contain;
    margin-bottom: 0.75rem;
}

.qr-info {
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.qr-info h6 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #28a745;
}

.qr-info p {
    font-size: 12px;
    color: #666;
    margin-bottom: 0;
    line-height: 1.4;
}

/* ========== QR SIZE PRESETS ========== */
.qr-size-presets {
    margin-bottom: 1rem;
}

.qr-size-presets h6 {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.75rem;
}

.size-buttons {
    display: flex;
    gap: 0.5rem;
}

.qr-size-btn {
    flex: 1;
    padding: 0.5rem;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
}

.qr-size-btn:hover {
    border-color: #28a745;
    background: #f8f9fa;
}

.qr-size-btn.active {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.qr-size-btn i {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 16px;
}

/* ========== QR CONTROL BUTTONS ========== */
.qr-controls {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.qr-control-btn {
    width: 100%;
    padding: 0.6rem 1rem;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.qr-control-btn:hover:not(:disabled) {
    background: #f8f9fa;
    border-color: #28a745;
    color: #28a745;
}

.qr-control-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.qr-control-btn i {
    font-size: 14px;
    width: 16px;
}

/* ========== PDF PREVIEW SECTION (RIGHT CONTENT) ========== */
.pdf-preview-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.document-header {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f0f0f0;
}

.document-title {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.document-title i {
    font-size: 24px;
    color: #007bff;
    margin-right: 0.75rem;
}

.document-title h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #333;
}

.document-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item strong {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.info-item span {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.status-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 0.75rem;
    padding: 1rem;
    text-align: center;
}

.status-badge i {
    font-size: 32px;
    margin-bottom: 0.5rem;
}

.status-badge strong {
    display: block;
    font-size: 16px;
    margin-bottom: 0.25rem;
}

.status-badge small {
    font-size: 13px;
    opacity: 0.9;
}

.pdf-preview-wrapper {
    position: relative;
    border: 2px solid #dee2e6;
    border-radius: 0.75rem;
    background: #f8f9fa;
    overflow: auto;
    max-height: 700px;
    transition: all 0.3s ease;
}

.pdf-preview-wrapper.drop-zone-active {
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.05);
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
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
    z-index: 10;
    transition: all 0.2s ease;
}

.placed-qr:hover {
    border-color: #218838;
    background: rgba(40, 167, 69, 0.15);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
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
    width: 12px;
    height: 12px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
    cursor: nwse-resize;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.handle-se {
    bottom: -6px;
    right: -6px;
    cursor: nwse-resize;
}

.delete-qr-btn {
    position: absolute;
    top: -12px;
    right: -12px;
    width: 26px;
    height: 26px;
    background: #dc3545;
    color: white;
    border: 2px solid white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.delete-qr-btn:hover {
    background: #c82333;
    transform: scale(1.1);
}

/* ========== PAGE NAVIGATION ========== */
.page-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.75rem;
}

.page-navigation button {
    padding: 0.5rem 1rem;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
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
    font-size: 14px;
}

/* ========== SIGNING CONTROLS (STICKY BOTTOM) ========== */
.signing-controls {
    position: sticky;
    bottom: 1rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
    padding: 1.5rem 2rem;
    border: 1px solid #dee2e6;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.confirmation-section {
    flex: 1;
}

.form-check {
    margin-bottom: 0.5rem;
}

.form-check-label {
    font-weight: 500;
    color: #333;
    cursor: pointer;
}

.security-note {
    font-size: 12px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-buttons {
    display: flex;
    gap: 0.75rem;
}

.action-buttons .btn {
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.btn-preview {
    background: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-preview:hover:not(:disabled) {
    background: #e0a800;
    border-color: #e0a800;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.btn-sign {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
}

.btn-sign:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

/* ========== LOADING OVERLAY ========== */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
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
    padding: 2.5rem;
    border-radius: 1rem;
    text-align: center;
    max-width: 400px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #28a745;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-content h5 {
    margin-bottom: 0.5rem;
    color: #333;
}

.loading-content p {
    color: #666;
    margin-bottom: 0;
}

/* ========== PREVIEW MODAL ========== */
.preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
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
    width: 100%;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.preview-header h4 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.preview-canvas-wrapper {
    border: 2px solid #dee2e6;
    border-radius: 0.75rem;
    background: #f8f9fa;
    padding: 1rem;
    margin: 1.5rem 0;
    text-align: center;
}

#previewCanvas {
    max-width: 100%;
    height: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    background: white;
}

.preview-details {
    background: #f8f9fa;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin: 1.5rem 0;
}

.preview-details strong {
    display: block;
    margin-bottom: 1rem;
    font-size: 16px;
    color: #333;
}

.preview-details ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.preview-details li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
}

.preview-details li:last-child {
    border-bottom: none;
}

.preview-details li strong {
    display: inline;
    margin: 0;
    font-size: 14px;
    color: #666;
}

.preview-details li span {
    color: #333;
    font-weight: 500;
}

.preview-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

/* ========== VISUAL GUIDE ========== */
.visual-guide-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: none;
}

.visual-guide-overlay.active {
    display: block;
}

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

.guide-step {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: start;
    gap: 1rem;
}

.guide-step-number {
    width: 30px;
    height: 30px;
    background: #28a745;
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
    font-size: 15px;
}

.guide-step-content p {
    margin: 0;
    color: #666;
    font-size: 13px;
    line-height: 1.5;
}

/* ========== RESPONSIVE DESIGN ========== */
@media (max-width: 1200px) {
    .signing-workspace {
        grid-template-columns: 320px 1fr;
        gap: 1.5rem;
    }

    .qr-code-preview-img {
        width: 150px;
        height: 150px;
    }
}

@media (max-width: 992px) {
    .signing-workspace {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .qr-code-section {
        position: relative;
        top: 0;
    }

    .qr-code-wrapper {
        padding: 1rem;
    }

    .qr-code-preview-img {
        width: 120px;
        height: 120px;
    }

    .pdf-preview-wrapper {
        max-height: 500px;
    }
}

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

    .qr-code-section {
        padding: 1rem;
    }

    .size-buttons {
        flex-direction: column;
    }

    .qr-size-btn {
        padding: 0.75rem;
    }

    .pdf-preview-section {
        padding: 1rem;
    }

    .pdf-preview-wrapper {
        max-height: 400px;
    }

    .signing-controls {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 1rem 1rem 0 0;
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
    }

    .action-buttons {
        width: 100%;
        flex-direction: column;
    }

    .action-buttons .btn {
        width: 100%;
    }

    .document-info {
        grid-template-columns: 1fr;
    }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .qr-handles {
        width: 20px;
        height: 20px;
        border: 3px solid white;
    }

    .handle-se {
        bottom: -10px;
        right: -10px;
    }

    .delete-qr-btn {
        width: 34px;
        height: 34px;
        top: -17px;
        right: -17px;
        font-size: 20px;
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
    <h1>Digital Document Signing</h1>
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

    <!-- NEW: Side-by-Side Workspace -->
    <div class="signing-workspace">
        <!-- LEFT: QR Code Section -->
        <div class="qr-code-section">
            <div class="qr-section-header">
                <div class="qr-icon-wrapper">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="qr-section-title">
                    <h5>QR Code</h5>
                    <p>Drag to position</p>
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
                            <h6><i class="fas fa-hand-pointer me-1"></i>Drag Me!</h6>
                            <p>Click and drag to the PDF preview</p>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Temporary QR code not found.
                    </div>
                @endif
            </div>

            <!-- QR Size Presets -->
            <div class="qr-size-presets">
                <h6><i class="fas fa-expand-arrows-alt me-1"></i>QR Size</h6>
                <div class="size-buttons">
                    <button class="qr-size-btn" onclick="setQRSize('small')">
                        <i class="fas fa-compress-alt"></i>
                        <span>Small</span>
                    </button>
                    <button class="qr-size-btn active" onclick="setQRSize('medium')">
                        <i class="fas fa-expand-alt"></i>
                        <span>Medium</span>
                    </button>
                    <button class="qr-size-btn" onclick="setQRSize('large')">
                        <i class="fas fa-expand"></i>
                        <span>Large</span>
                    </button>
                </div>
            </div>

            <!-- QR Controls -->
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
        </div>

        <!-- RIGHT: PDF Preview Section -->
        <div class="pdf-preview-section">
            <!-- Document Header -->
            <div class="document-header">
                <div class="document-title">
                    <i class="fas fa-file-pdf"></i>
                    <h4>{{ $approvalRequest->document_name }}</h4>
                </div>

                <div class="document-info">
                    <div class="info-item">
                        <strong>Document Type</strong>
                        <span>{{ $approvalRequest->document_type ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Submitted</strong>
                        <span>{{ $approvalRequest->created_at->format('d M Y H:i') }}</span>
                    </div>
                    <div class="status-badge">
                        <i class="fas fa-check-circle"></i>
                        <strong>Approved</strong>
                        <small>Ready for signing</small>
                    </div>
                </div>
            </div>

            <!-- PDF Preview -->
            <div class="pdf-preview-wrapper" id="pdfPreviewWrapper">
                <canvas id="pdfCanvas"></canvas>
                <div id="qrOverlay"></div>
            </div>

            <!-- Page Navigation -->
            <div class="page-navigation">
                <button id="prevPageBtn" onclick="previousPage()">
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <div class="page-info">
                    Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                </div>
                <button id="nextPageBtn" onclick="nextPage()">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Signing Controls (Sticky Bottom) -->
<div class="signing-controls">
    <div class="confirmation-section">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="confirmSignature">
            <label class="form-check-label" for="confirmSignature">
                I confirm that the QR code position is correct and I authorize this document
            </label>
        </div>
        <div class="security-note">
            <i class="fas fa-shield-alt"></i>
            <span>This signature will be legally binding and cryptographically secured</span>
        </div>
    </div>
    <div class="action-buttons">
        <button class="btn btn-outline-secondary" onclick="goBack()">
            <i class="fas fa-arrow-left me-1"></i> Back
        </button>
        <button class="btn btn-warning btn-preview" id="previewBtn" onclick="showPreview()" disabled>
            <i class="fas fa-eye me-1"></i> Preview
        </button>
        <button class="btn btn-success btn-sign" id="signBtn" onclick="confirmAndSign()" disabled>
            <i class="fas fa-signature me-1"></i> Sign Document
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <h5>Processing Digital Signature...</h5>
        <p class="text-muted">Generating unique encryption key and signing your document</p>
        <p class="text-muted"><small>Please wait, this may take a moment...</small></p>
    </div>
</div>

<!-- Visual Guide / Tutorial -->
<div class="visual-guide-overlay" id="visualGuideOverlay" onclick="hideGuide()"></div>
<div class="visual-guide" id="visualGuide">
    <h4 class="mb-3"><i class="fas fa-info-circle me-2 text-success"></i>How to Sign Your Document</h4>

    <div class="guide-step">
        <div class="guide-step-number">1</div>
        <div class="guide-step-content">
            <h6>Drag the QR Code</h6>
            <p>Click and drag the QR code from the left panel onto your PDF document at your desired position.</p>
        </div>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">2</div>
        <div class="guide-step-content">
            <h6>Adjust Size & Position</h6>
            <p>Use the size buttons (Small/Medium/Large) or drag the corner handle to resize. Move QR by dragging.</p>
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

    <div class="alert alert-info mt-3 mb-3">
        <i class="fas fa-lightbulb me-2"></i>
        <strong>Pro Tip:</strong> The QR code will be embedded directly into your PDF and can be scanned to verify authenticity!
    </div>

    <div class="mt-3 text-end">
        <button class="btn btn-success" onclick="hideGuide()">
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
        <div class="preview-header">
            <h4><i class="fas fa-eye me-2"></i>Document Preview</h4>
            <button class="btn btn-sm btn-outline-secondary" onclick="hidePreview()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            This is how your document will appear after signing. The QR code will be embedded at the position you selected.
        </div>

        <div class="preview-canvas-wrapper" id="previewCanvasWrapper">
            <div class="spinner" id="previewSpinner" style="display: none;"></div>
            <canvas id="previewCanvas"></canvas>
        </div>

        <div class="preview-details">
            <strong><i class="fas fa-info-circle me-2"></i>Document Details</strong>
            <ul>
                <li>
                    <strong>Document:</strong>
                    <span>{{ $approvalRequest->document_name }}</span>
                </li>
                <li>
                    <strong>Signer:</strong>
                    <span>{{ $approvalRequest->approver->name ?? 'N/A' }}</span>
                </li>
                <li>
                    <strong>Signing Time:</strong>
                    <span id="signingTimestamp"></span>
                </li>
                <li>
                    <strong>Encryption:</strong>
                    <span>RSA-2048 with SHA-256</span>
                </li>
                <li>
                    <strong>Key Type:</strong>
                    <span>Unique key per document</span>
                </li>
            </ul>
        </div>

        <div class="preview-actions">
            <button class="btn btn-secondary" onclick="hidePreview()">
                <i class="fas fa-times me-1"></i> Close
            </button>
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
        const viewport = page.getViewport({ scale: 1.2 });

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
            pdfWrapper.classList.add('drop-zone-active');
        } else {
            pdfWrapper.classList.remove('drop-zone-active');
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
    document.getElementById('pdfPreviewWrapper').classList.remove('drop-zone-active');
}

function handlePDFDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    e.currentTarget.classList.add('drop-zone-active');
}

function handlePDFDragLeave(e) {
    e.currentTarget.classList.remove('drop-zone-active');
}

function handlePDFDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drop-zone-active');

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

    // Add resize handle (only SE corner for simplicity)
    const handle = document.createElement('div');
    handle.className = 'qr-handles handle-se';
    qrDiv.appendChild(handle);

    overlay.appendChild(qrDiv);
    placedQR = qrDiv;

    // Make draggable and resizable
    makeQRDraggable(qrDiv);
    makeQRResizable(qrDiv);

    // Enable buttons
    updateButtonStates();

    // Save to history
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
    const centerX = (canvas.width / 2) - (placedQR.offsetWidth / 2);
    const centerY = (canvas.height / 2) - (placedQR.offsetHeight / 2);

    placedQR.style.left = centerX + 'px';
    placedQR.style.top = centerY + 'px';

    saveToHistory();

    console.log('QR position reset to center');
}

// ==================== UNDO / REDO ====================
function saveToHistory() {
    if (!placedQR) return;

    const state = {
        left: placedQR.style.left,
        top: placedQR.style.top,
        width: placedQR.style.width,
        height: placedQR.style.height
    };

    qrHistory = qrHistory.slice(0, historyIndex + 1);
    qrHistory.push(state);

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
            saveToHistory();
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
            saveToHistory();
        }
    });
}

function makeQRResizable(element) {
    const handle = element.querySelector('.qr-handles');
    if (!handle) return;

    let isResizing = false;
    let startX, startY, startWidth, startHeight;

    handle.addEventListener('mousedown', function(e) {
        e.stopPropagation();
        isResizing = true;
        startX = e.clientX;
        startY = e.clientY;
        startWidth = parseInt(window.getComputedStyle(element).width, 10);
        startHeight = parseInt(window.getComputedStyle(element).height, 10);
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

        // Use larger delta to maintain square aspect
        const delta = Math.max(Math.abs(dx), Math.abs(dy));
        const newSize = startWidth + (dx > 0 ? delta : -delta);

        // Constrain size
        const minSize = 50;
        const maxSize = 300;
        const constrainedSize = Math.max(minSize, Math.min(maxSize, newSize));

        // Apply square size
        element.style.width = constrainedSize + 'px';
        element.style.height = constrainedSize + 'px';
    }
}

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

// ==================== ENHANCED: PREVIEW WITH API CALL ====================
async function showPreview() {
    if (!placedQR) {
        alert('Please place the QR code first.');
        return;
    }

    // Show modal immediately with spinner
    document.getElementById('previewModal').classList.add('active');
    document.getElementById('previewSpinner').style.display = 'block';
    document.getElementById('previewCanvas').style.display = 'none';

    // Update timestamp
    document.getElementById('signingTimestamp').textContent = new Date().toLocaleString();

    try {
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

        console.log('Generating preview with positioning:', qrPositioningData);

        // Call preview API
        const formData = new FormData();
        formData.append('qr_positioning_data', JSON.stringify(qrPositioningData));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        const response = await fetch(`/user/signature/sign/${approvalRequestId}/preview`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Load preview PDF
            console.log('Preview generated, loading PDF:', data.data.preview_url);

            // Load preview PDF and render on canvas
            const previewPdfPath = data.data.preview_url;
            const loadingTask = pdfjsLib.getDocument(previewPdfPath);
            const previewPdf = await loadingTask.promise;
            const previewPage = await previewPdf.getPage(currentPage);

            const previewCanvas = document.getElementById('previewCanvas');
            const previewCtx = previewCanvas.getContext('2d');
            const viewport = previewPage.getViewport({ scale: 1.2 });

            previewCanvas.width = viewport.width;
            previewCanvas.height = viewport.height;

            await previewPage.render({
                canvasContext: previewCtx,
                viewport: viewport
            }).promise;

            // Hide spinner, show canvas
            document.getElementById('previewSpinner').style.display = 'none';
            document.getElementById('previewCanvas').style.display = 'block';

            console.log('Preview rendered successfully');
        } else {
            throw new Error(data.error || 'Failed to generate preview');
        }

    } catch (error) {
        console.error('Preview generation failed:', error);
        document.getElementById('previewSpinner').style.display = 'none';
        alert('Failed to generate preview: ' + error.message);
        hidePreview();
    }
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
