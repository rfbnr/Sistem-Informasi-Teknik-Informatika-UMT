{{-- resources/views/digital-signature/user/sign-document.blade.php --}}
{{-- MODERN UI: QR Code Drag & Drop with Split Screen Layout --}}
@extends('user.layouts.app')

@section('title', 'Digital Document Signing - QR Drag & Drop')

@push('styles')
<style>
/* ========== MODERN VARIABLES ========== */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    --card-shadow-hover: 0 15px 50px rgba(0, 0, 0, 0.15);
}

/* ========== GENERAL LAYOUT ========== */
.signing-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 1.5rem;
}

/* ========== MODERN STEPS INDICATOR ========== */
.signing-steps {
    background: var(--primary-gradient);
    color: white;
    border-radius: 1.5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--card-shadow);
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    position: relative;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    z-index: 0;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    font-weight: bold;
    font-size: 18px;
    transition: all 0.3s ease;
}

.step-number.active {
    background: white;
    color: #667eea;
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.4);
}

.step-number.completed {
    background: var(--success-gradient);
    color: white;
}

/* ========== SPLIT SCREEN WORKSPACE (DESKTOP) ========== */
.signing-workspace {
    display: grid;
    grid-template-columns: 400px 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
    min-height: 70vh;
}

/* ========== QR CODE SECTION (LEFT PANEL) ========== */
.qr-panel {
    background: white;
    border-radius: 1.5rem;
    box-shadow: var(--card-shadow);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 1.5rem;
    height: fit-content;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.qr-panel-header {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.qr-icon {
    width: 50px;
    height: 50px;
    background: var(--success-gradient);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.qr-icon i {
    color: white;
    font-size: 22px;
}

.qr-panel-title h5 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #2d3748;
}

.qr-panel-title p {
    margin: 0;
    font-size: 13px;
    color: #718096;
}

.qr-code-wrapper {
    background: linear-gradient(135deg, #f6f8fb 0%, #e9f0f5 100%);
    border-radius: 1rem;
    padding: 1.5rem;
    border: 2px dashed #11998e;
    margin-bottom: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.qr-code-wrapper:hover {
    border-color: #38ef7d;
    box-shadow: 0 8px 25px rgba(17, 153, 142, 0.15);
}

.qr-auto-placement {
    /* margin-top: 1.5rem;
    text-align: center; */
    margin-top: 1.5rem;
    text-align: center;
}

.qr-auto-placement .btn {
    width: 100%;
}

.qr-auto-placement:hover .btn {
    box-shadow: var(--card-shadow-hover);
}

.qr-code-item {
    background: white;
    border: 3px solid #11998e;
    border-radius: 1rem;
    padding: 1rem;
    cursor: grab;
    transition: all 0.3s ease;
    display: inline-block;
}

.qr-code-item:hover {
    box-shadow: 0 10px 30px rgba(17, 153, 142, 0.25);
    transform: translateY(-5px) scale(1.02);
}

.qr-code-item.dragging {
    opacity: 0.7;
    cursor: grabbing;
    transform: scale(0.95);
}

.qr-code-preview-img {
    width: 180px;
    height: 180px;
    object-fit: contain;
    margin-bottom: 0.75rem;
}

.qr-info h6 {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2d3748;
}

.qr-info p {
    font-size: 13px;
    color: #718096;
    margin: 0;
}

/* QR SIZE PRESETS */
.qr-size-presets {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.qr-size-btn {
    padding: 0.75rem 0.5rem;
    border: 2px solid #e2e8f0;
    background: white;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
}

.qr-size-btn:hover {
    border-color: #11998e;
    background: #f0fdf4;
    transform: translateY(-2px);
}

.qr-size-btn.active {
    background: var(--success-gradient);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
}

/* QR CONTROLS */
.qr-controls {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.qr-control-btn {
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.qr-control-btn:hover:not(:disabled) {
    background: #f7fafc;
    border-color: #11998e;
    transform: translateY(-2px);
}

.qr-control-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ========== PDF PREVIEW SECTION (RIGHT PANEL) ========== */
.pdf-panel {
    background: white;
    border-radius: 1.5rem;
    box-shadow: var(--card-shadow);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
}

.pdf-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.pdf-info h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pdf-badge {
    background: var(--success-gradient);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 12px;
    font-weight: 600;
}

.pdf-preview-wrapper {
    position: relative;
    border: 2px solid #e2e8f0;
    border-radius: 1rem;
    background: #f7fafc;
    overflow: auto;
    flex: 1;
    min-height: 500px;
    max-height: calc(100vh - 350px);
    transition: all 0.3s ease;
}

.pdf-preview-wrapper.drop-zone-active {
    border-color: #38ef7d;
    background: rgba(17, 153, 142, 0.05);
    box-shadow: 0 0 0 4px rgba(17, 153, 142, 0.1);
}

#pdfCanvas {
    display: block;
    margin: 0 auto;
    background: white;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
    border: 3px dashed #11998e;
    background: rgba(17, 153, 142, 0.1);
    cursor: move;
    pointer-events: all;
    border-radius: 0.5rem;
    z-index: 10;
    transition: all 0.2s ease;
}

.placed-qr:hover {
    border-color: #38ef7d;
    background: rgba(17, 153, 142, 0.2);
    box-shadow: 0 0 20px rgba(17, 153, 142, 0.3);
}

.placed-qr img {
    width: 100%;
    height: auto;
    display: block;
    pointer-events: none;
}

.qr-handles {
    position: absolute;
    width: 14px;
    height: 14px;
    background: #11998e;
    border: 3px solid white;
    border-radius: 50%;
    cursor: nwse-resize;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.handle-se {
    bottom: -7px;
    right: -7px;
}

.delete-qr-btn {
    position: absolute;
    top: -15px;
    right: -15px;
    width: 32px;
    height: 32px;
    background: var(--danger-gradient);
    color: white;
    border: 3px solid white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(235, 51, 73, 0.4);
    transition: all 0.3s ease;
}

.delete-qr-btn:hover {
    transform: scale(1.15) rotate(90deg);
}

/* PAGE NAVIGATION */
.page-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f6f8fb 0%, #e9f0f5 100%);
    border-radius: 1rem;
}

.page-navigation button {
    padding: 0.75rem 1.5rem;
    border: 2px solid #e2e8f0;
    background: white;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.page-navigation button:hover:not(:disabled) {
    background: var(--success-gradient);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

.page-navigation button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.page-info {
    font-weight: 700;
    color: #2d3748;
    font-size: 15px;
}

/* ========== SIGNING CONTROLS (STICKY BOTTOM) ========== */
.signing-controls {
    position: sticky;
    bottom: 1.5rem;
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
    padding: 1.5rem;
    border: 2px solid #e2e8f0;
    z-index: 100;
    margin: 0 -0.5rem;
}

.signing-controls .form-check-label {
    font-weight: 600;
    color: #2d3748;
}

.signing-controls small {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

/* ========== LOADING OVERLAY ========== */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
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
    padding: 3rem;
    border-radius: 1.5rem;
    text-align: center;
    max-width: 400px;
}

.spinner {
    border: 5px solid #f3f3f3;
    border-top: 5px solid #11998e;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ========== PREVIEW MODAL (ENHANCED) ========== */
.preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.preview-modal.active {
    display: flex;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.preview-modal-content {
    background: white;
    border-radius: 1.5rem;
    max-width: 1200px;
    width: 100%;
    max-height: 95vh;
    overflow: auto;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.preview-header h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.preview-canvas-wrapper {
    border: 2px solid #e2e8f0;
    border-radius: 1rem;
    background: #f7fafc;
    padding: 1.5rem;
    margin: 1.5rem 0;
    text-align: center;
}

#previewCanvas {
    max-width: 100%;
    height: auto;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.preview-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

/* ========== VISUAL GUIDE (FIRST TIME) ========== */
.visual-guide-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    z-index: 9999;
    display: none;
    margin: 0 auto;
}

.visual-guide-overlay.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

.visual-guide {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    padding: 2.5rem;
    max-width: 550px;
    width: 90%;
    z-index: 10000;
    display: none;
}

.visual-guide.active {
    display: block;
    animation: slideUp 0.4s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translate(-50%, -40%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

.guide-header {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
}

.guide-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.guide-icon i {
    color: white;
    font-size: 28px;
}

.guide-header h4 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #2d3748;
}

.guide-step {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: start;
    gap: 1rem;
}

.guide-step-number {
    width: 35px;
    height: 35px;
    background: var(--success-gradient);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
    font-size: 16px;
}

.guide-step-content h6 {
    margin: 0 0 0.5rem 0;
    color: #2d3748;
    font-weight: 600;
}

.guide-step-content p {
    margin: 0;
    color: #718096;
    font-size: 14px;
    line-height: 1.6;
}

.guide-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* ========== RESPONSIVE OPTIMIZATION ========== */

/* Tablet and smaller desktops */
@media (max-width: 992px) {
    .signing-workspace {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    .qr-panel {
        position: relative;
        top: 0;
        max-height: none;
        width: 100%;
    }

    .pdf-preview-wrapper {
        min-height: 400px;
        max-height: 500px;
    }

    .pdf-panel {
        padding: 1.25rem;
    }

    /* .delete-qr-btn {
    position: absolute;
    top: -15px;
    right: -15px;
    width: 32px;
    height: 32px;
    background: var(--danger-gradient);
    color: white;
    border: 3px solid white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(235, 51, 73, 0.4);
    transition: all 0.3s ease;
}

    .delete-qr-btn:hover {
        transform: scale(1.15) rotate(90deg);
    } */
}

/* Mobile view adjustments */
@media (max-width: 768px) {
    .signing-container {
        padding: 1rem;
    }

    .signing-steps {
        padding: 1.25rem;
    }

    /* Stack steps vertically */
    .step-indicator {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .step-indicator::before {
        display: none;
    }

    .step-item {
        flex-direction: row;
        gap: 0.75rem;
        justify-content: flex-start;
    }

    .qr-panel {
        margin-bottom: 1rem;
    }

    .qr-code-preview-img {
        width: 120px;
        height: 120px;
    }

    .qr-code-wrapper {
        padding: 1rem;
    }

    .qr-size-presets {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.4rem;
    }

    .qr-size-btn {
        padding: 0.6rem;
        font-size: 12px;
    }

    /* PDF area fit to screen width */
    .pdf-preview-wrapper {
        min-height: 400px;
        max-height: 70vh;
        border-width: 1.5px;
    }

    #pdfCanvas {
        width: 100% !important;
        /* height: auto !important; */
        display: block;
        margin: 0 auto;
        /* Preserve aspect ratio */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);

    }

    /* Sticky bottom bar adjustments */
    .signing-controls {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        margin: 0;
        border-radius: 1.5rem 1.5rem 0 0;
        padding: 1.25rem 1rem;
        box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.15);
        border: none;
    }

    .signing-controls .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .signing-controls button {
        width: 100%;
        font-size: 14px;
        padding: 0.75rem;
    }

    /* Navigation buttons */
    .page-navigation {
        flex-direction: column;
        gap: 0.75rem;
    }

    .page-navigation button {
        width: 100%;
        padding: 0.75rem;
    }

    .page-info {
        font-size: 14px;
    }

    /* Modal fix */
    .preview-modal {
        padding: 0.75rem;
    }

    .preview-modal-content {
        padding: 1.25rem;
        max-height: 90vh;
    }
}

/* Extra small mobile screens (≤576px) */
@media (max-width: 576px) {
    .signing-container {
        padding: 0.75rem;
    }

    .qr-panel {
        padding: 1rem;
    }

    .qr-code-preview-img {
        width: 100px;
        height: 100px;
    }

    .qr-info h6 {
        font-size: 13px;
    }

    .qr-info p {
        font-size: 12px;
    }

    .pdf-preview-wrapper {
        min-height: 350px;
    }

    .signing-controls small {
        font-size: 12px;
        line-height: 1.4;
    }

    .step-number {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }

    .guide-step-content p {
        font-size: 13px;
    }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .qr-handles {
        width: 20px;
        height: 20px;
        border: 1px solid white;
    }

    .handle-se {
        bottom: -12px;
        right: -12px;
    }

    .delete-qr-btn {
        width: 20px;
        height: 20px;
        top: -12px;
        right: -12px;
        font-size: 12px;
        box-shadow: 0 1px 4px rgba(235, 51, 73, 0.4);
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
    <!-- Modern Steps Indicator -->
    <div class="signing-steps">
        <div class="step-indicator">
            <div class="step-item">
                <div class="step-number completed">1</div>
                <small>Document Uploaded</small>
            </div>
            <div class="step-item">
                <div class="step-number active">2</div>
                <small>Position QR Code</small>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <small>Sign Document</small>
            </div>
        </div>
        <h5 class="mb-1">Step 2: Position QR Code on Document</h5>
        <p class="mb-0 opacity-75">Drag the QR code to your desired position on the document preview</p>
    </div>

    <!-- Split Screen Workspace -->
    <div class="signing-workspace">
        <!-- LEFT PANEL: QR Code Controls -->
        <div class="qr-panel">
            <div class="qr-panel-header">
                <div class="qr-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="qr-panel-title">
                    <h5>Verification QR Code</h5>
                    <p>Drag to document →</p>
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
                            <h6><i class="fas fa-qrcode me-2"></i>Drag & Drop</h6>
                            <p>Place on document</p>
                        </div>
                    </div>
                    {{-- Auto Place QR Code --}}
                    <div class="qr-auto-placement">
                        <button class="btn btn-primary" id="autoPlaceQRBtn" onclick="placeQROnPDF()">
                            <i class="fas fa-check-circle me-2"></i>
                            Place QR Code Automatically
                        </button>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        QR code not found
                    </div>
                @endif
            </div>

            <!-- QR Size Presets -->
            <div class="qr-size-presets">
                <button class="qr-size-btn" onclick="setQRSize('small')">
                    <i class="fas fa-compress-alt d-block mb-1"></i>
                    <small>Small</small>
                </button>
                <button class="qr-size-btn active" onclick="setQRSize('medium')">
                    <i class="fas fa-expand-alt d-block mb-1"></i>
                    <small>Medium</small>
                </button>
                <button class="qr-size-btn" onclick="setQRSize('large')">
                    <i class="fas fa-expand d-block mb-1"></i>
                    <small>Large</small>
                </button>
            </div>

            <!-- QR Controls -->
            <div class="qr-controls">
                <button class="qr-control-btn" id="resetQRBtn" onclick="resetQRPosition()" disabled>
                    <i class="fas fa-redo"></i> Reset
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

        <!-- RIGHT PANEL: PDF Preview -->
        <div class="pdf-panel">
            <div class="pdf-panel-header">
                <div class="pdf-info">
                    <h5>
                        <i class="fas fa-file-pdf text-danger"></i>
                        {{ $approvalRequest->document_name }}
                    </h5>
                    <small class="text-muted">{{ $approvalRequest->full_document_number }}</small>
                </div>
                <span class="pdf-badge">
                    <i class="fas fa-check-circle me-1"></i>Approved
                </span>
            </div>

            <!-- PDF Preview with QR Overlay -->
            <div class="pdf-preview-wrapper" id="pdfPreviewWrapper">
                <canvas id="pdfCanvas"></canvas>
                <div id="qrOverlay"></div>
            </div>

            <!-- Page Navigation -->
            <div class="page-navigation">
                <button id="prevPageBtn" onclick="previousPage()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="page-info">
                    Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                </div>
                <button id="nextPageBtn" onclick="nextPage()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
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
                    I confirm the QR code placement is correct
                </label>
            </div>
            <small class="text-muted">
                <i class="fas fa-shield-alt"></i>
                Document will be cryptographically secured with RSA-2048 encryption
            </small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="goBack()">
                <i class="fas fa-arrow-left me-1"></i> Back
            </button>
            <button class="btn btn-primary" id="previewSignBtn" onclick="showPreviewBeforeSign()" disabled>
                <i class="fas fa-eye me-1"></i> Preview & Sign
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <h5>Processing Digital Signature...</h5>
        <p class="text-muted mb-0">Generating unique encryption key</p>
        <p class="text-muted"><small>Please wait...</small></p>
    </div>
</div>

<!-- Visual Guide (First Time) -->
<div class="visual-guide-overlay" id="visualGuideOverlay" onclick="hideGuide()"></div>
<div class="visual-guide" id="visualGuide">
    <div class="guide-header">
        <div class="guide-icon">
            <i class="fas fa-lightbulb"></i>
        </div>
        <h4>How to Sign Your Document</h4>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">1</div>
        <div class="guide-step-content">
            <h6>Drag the QR Code</h6>
            <p>Click and drag the QR code from the left panel onto your PDF document preview on the right.</p>
        </div>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">2</div>
        <div class="guide-step-content">
            <h6>Adjust Size & Position</h6>
            <p>Use the size buttons (Small/Medium/Large) to resize, or drag the corner handle. Move it to the perfect spot.</p>
        </div>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">3</div>
        <div class="guide-step-content">
            <h6>Preview Before Signing</h6>
            <p>Click "Preview & Sign" to see the final document with QR code embedded. You must confirm the preview before signing.</p>
        </div>
    </div>

    <div class="guide-step">
        <div class="guide-step-number">4</div>
        <div class="guide-step-content">
            <h6>Sign the Document</h6>
            <p>After reviewing the preview, click "Confirm & Sign Document" to finalize. A unique encryption key will be generated.</p>
        </div>
    </div>

    <div class="alert alert-info mt-3 mb-0">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Tip:</strong> The QR code and PDF are side-by-side for easy drag & drop!
    </div>

    <div class="guide-footer">
        <label class="form-check-label">
            <input type="checkbox" class="form-check-input" id="dontShowAgain">
            Don't show this again
        </label>
        <button class="btn btn-primary" onclick="hideGuide()">
            <i class="fas fa-check me-1"></i> Got it!
        </button>
    </div>
</div>

<!-- Preview Modal (ENHANCED - REQUIRED BEFORE SIGNING) -->
<div class="preview-modal" id="previewModal" onclick="hidePreview(event)">
    <div class="preview-modal-content" onclick="event.stopPropagation()">
        <div class="preview-header">
            <h4>
                <i class="fas fa-eye text-primary"></i>
                Final Document Preview
            </h4>
            <button class="btn btn-sm btn-outline-secondary" onclick="hidePreview()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Please review the document carefully before signing. The QR code will be permanently embedded at the selected position.
        </div>

        <div class="preview-canvas-wrapper">
            <canvas id="previewCanvas"></canvas>
        </div>

        <div class="mt-3">
            <strong>Document Details:</strong>
            <ul class="list-unstyled mt-2">
                <li><strong>Document:</strong> {{ $approvalRequest->document_name }}</li>
                <li><strong>Signer:</strong> {{ $approvalRequest->approver->name }}</li>
                <li><strong>Signing Time:</strong> <span id="signingTimestamp"></span></li>
                <li><strong>Encryption:</strong> RSA-2048 with SHA-256 (Unique key per document)</li>
            </ul>
        </div>

        <div class="preview-actions">
            <button class="btn btn-secondary" onclick="hidePreview()">
                <i class="fas fa-times me-1"></i> Cancel
            </button>
            <button class="btn btn-success btn-lg" onclick="confirmAndSign()">
                <i class="fas fa-signature me-1"></i> Confirm & Sign Document
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

// Touch support
let isTouchDevice = false;
let touchStartX = 0;
let touchStartY = 0;
let isDraggingQR = false;

// Undo/Redo
let qrHistory = [];
let historyIndex = -1;
const MAX_HISTORY = 50;

// QR sizes
const QR_SIZES = {
    small: { width: 60, height: 60 },
    medium: { width: 100, height: 100 },
    large: { width: 150, height: 150 }
};

let currentQRSize = 'medium';

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing modern QR signing interface...');

    isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    console.log('Touch device:', isTouchDevice);

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    loadPDF();
    setupQRDragDrop();
    setupEventListeners();
    showGuideIfFirstTime();
});

// ==================== VISUAL GUIDE ====================
function showGuideIfFirstTime() {
    const hasSeenGuide = localStorage.getItem('qr_signing_guide_seen');
    if (!hasSeenGuide) {
        setTimeout(() => showGuide(), 800);
    }
}

function showGuide() {
    document.getElementById('visualGuideOverlay').classList.add('active');
    document.getElementById('visualGuide').classList.add('active');
}

function hideGuide() {
    document.getElementById('visualGuideOverlay').classList.remove('active');
    document.getElementById('visualGuide').classList.remove('active');

    if (document.getElementById('dontShowAgain').checked) {
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
        console.log(`PDF loaded: ${totalPages} pages`);
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
        const page = await pdfDocument.getPage(pageNumber);
        // const viewport = page.getViewport({ scale: 1.2 });

        // responsive scaling for better fit desktop and mobile
        const container = document.getElementById('pdfPreviewWrapper');
        const containerWidth = container.clientWidth;
        const scale = containerWidth / page.getViewport({ scale: 1 }).width;
        const viewport = page.getViewport({ scale: scale });

        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const overlay = document.getElementById('qrOverlay');
        overlay.style.width = viewport.width + 'px';
        overlay.style.height = viewport.height + 'px';

        await page.render({
            canvasContext: ctx,
            viewport: viewport
        }).promise;

        pageRendering = false;

        if (pageNumPending !== null) {
            renderPage(pageNumPending);
            pageNumPending = null;
        }

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

// ==================== QR DRAG & DROP ====================
function setupQRDragDrop() {
    const qrCodeItem = document.getElementById('qrCodeItem');
    const pdfWrapper = document.getElementById('pdfPreviewWrapper');

    if (!qrCodeItem) return;

    if (!isTouchDevice) {
        qrCodeItem.addEventListener('dragstart', handleQRDragStart);
        qrCodeItem.addEventListener('dragend', handleQRDragEnd);
    }

    if (isTouchDevice) {
        qrCodeItem.addEventListener('touchstart', handleQRTouchStart, { passive: false });
        qrCodeItem.addEventListener('touchmove', handleQRTouchMove, { passive: false });
        qrCodeItem.addEventListener('touchend', handleQRTouchEnd, { passive: false });
    }

    pdfWrapper.addEventListener('dragover', handlePDFDragOver);
    pdfWrapper.addEventListener('dragleave', handlePDFDragLeave);
    pdfWrapper.addEventListener('drop', handlePDFDrop);
}

function handleQRDragStart(e) {
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('qrcode', 'true');
}

function handleQRDragEnd(e) {
    e.target.classList.remove('dragging');
}

function handleQRTouchStart(e) {
    isDraggingQR = true;
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
    e.currentTarget.classList.add('dragging');
}

function handleQRTouchMove(e) {
    if (!isDraggingQR) return;
    e.preventDefault();

    const touch = e.touches[0];
    const canvasRect = canvas.getBoundingClientRect();
    const pdfWrapper = document.getElementById('pdfPreviewWrapper');

    if (touch.clientX >= canvasRect.left && touch.clientX <= canvasRect.right &&
        touch.clientY >= canvasRect.top && touch.clientY <= canvasRect.bottom) {
        pdfWrapper.classList.add('drop-zone-active');
    } else {
        pdfWrapper.classList.remove('drop-zone-active');
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

    if (!e.dataTransfer.getData('qrcode')) return;

    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    placeQROnPDF(x, y);
}

// ==================== QR PLACEMENT ====================
function placeQROnPDF(x, y) {
    if (placedQR) {
        placedQR.remove();
    }

    const overlay = document.getElementById('qrOverlay');
    const qrDiv = document.createElement('div');
    qrDiv.className = 'placed-qr';
    qrDiv.id = 'placedQR';
    qrDiv.style.left = x + 'px';
    qrDiv.style.top = y + 'px';
    qrDiv.style.width = '100px';
    qrDiv.style.height = '100px';

    const img = document.createElement('img');
    img.src = qrCodeImageSrc;
    img.alt = 'QR Code';
    qrDiv.appendChild(img);

    const deleteBtn = document.createElement('div');
    deleteBtn.className = 'delete-qr-btn';
    deleteBtn.innerHTML = '×';
    deleteBtn.onclick = removeQR;
    qrDiv.appendChild(deleteBtn);

    const handle = document.createElement('div');
    handle.className = 'qr-handles handle-se';
    qrDiv.appendChild(handle);

    overlay.appendChild(qrDiv);
    placedQR = qrDiv;

    makeQRDraggable(qrDiv);
    makeQRResizable(qrDiv);
    updateButtonStates();
    saveToHistory();
}

function setQRSize(size) {
    if (!placedQR) {
        alert('Please place the QR code first.');
        return;
    }

    currentQRSize = size;
    const sizeConfig = QR_SIZES[size];

    placedQR.style.width = sizeConfig.width + 'px';
    placedQR.style.height = sizeConfig.height + 'px';

    document.querySelectorAll('.qr-size-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.qr-size-btn').classList.add('active');

    saveToHistory();
}

function resetQRPosition() {
    if (!placedQR) return;

    const centerX = (canvas.width / 2) - (placedQR.offsetWidth / 2);
    const centerY = (canvas.height / 2) - (placedQR.offsetHeight / 2);

    placedQR.style.left = centerX + 'px';
    placedQR.style.top = centerY + 'px';

    saveToHistory();
}

// ==================== UNDO/REDO ====================
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
        applyHistoryState(qrHistory[historyIndex]);
        updateUndoRedoButtons();
    }
}

function redo() {
    if (historyIndex < qrHistory.length - 1 && placedQR) {
        historyIndex++;
        applyHistoryState(qrHistory[historyIndex]);
        updateUndoRedoButtons();
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
        if (e.target.classList.contains('qr-handles') || e.target.classList.contains('delete-qr-btn')) return;
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

    element.addEventListener('touchstart', function(e) {
        if (e.target.classList.contains('qr-handles') || e.target.classList.contains('delete-qr-btn')) return;
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
    const handles = element.querySelectorAll('.qr-handles');

    handles.forEach(handle => {
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

            if (handle.classList.contains('handle-se')) {
                const delta = Math.max(Math.abs(dx), Math.abs(dy));
                const newSize = startWidth + (dx > 0 ? delta : -delta);
                const constrainedSize = Math.max(32, Math.min(300, newSize));

                element.style.width = constrainedSize + 'px';
                element.style.height = constrainedSize + 'px';
            }
        }
    });
}

function removeQR() {
    if (placedQR) {
        placedQR.remove();
        placedQR = null;
        qrHistory = [];
        historyIndex = -1;
        updateButtonStates();
        updateUndoRedoButtons();
    }
}

// ==================== PREVIEW BEFORE SIGN (NEW FLOW) ====================
function showPreviewBeforeSign() {
    if (!placedQR) {
        alert('Please place the QR code first.');
        return;
    }

    // Update timestamp
    document.getElementById('signingTimestamp').textContent = new Date().toLocaleString();

    // Generate instant preview
    const previewCanvas = document.getElementById('previewCanvas');
    const previewCtx = previewCanvas.getContext('2d');

    previewCanvas.width = canvas.width;
    previewCanvas.height = canvas.height;

    // Draw PDF
    previewCtx.drawImage(canvas, 0, 0);

    // Draw QR
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

    // Show preview modal
    document.getElementById('previewModal').classList.add('active');
}

function hidePreview(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('previewModal').classList.remove('active');
}

// ==================== SIGNING (FROM PREVIEW) ====================
function confirmAndSign() {
    if (!placedQR) {
        alert('Please place the QR code on the document first.');
        return;
    }

    if (!document.getElementById('confirmSignature').checked) {
        alert('Please confirm your QR code placement by checking the confirmation box.');
        return;
    }

    if (confirm('Are you sure you want to sign this document? This action is permanent and cannot be undone.')) {
        signDocument();
    }
}

async function signDocument() {
    try {
        hidePreview();

        console.log('Starting document signing...');
        document.getElementById('loadingOverlay').classList.add('active');

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

        const formData = new FormData();
        formData.append('qr_positioning_data', JSON.stringify(qrPositioningData));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

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

function setupEventListeners() {
    document.getElementById('confirmSignature').addEventListener('change', updateButtonStates);
}

function updateButtonStates() {
    const hasQR = placedQR !== null;
    const isConfirmed = document.getElementById('confirmSignature').checked;

    document.getElementById('previewSignBtn').disabled = !hasQR || !isConfirmed;
    document.getElementById('resetQRBtn').disabled = !hasQR;
}

function goBack() {
    if (confirm('Are you sure you want to go back? Your QR placement will be lost.')) {
        window.location.href = '{{ route("user.signature.approval.status") }}';
    }
}

// Prevent accidental reload
window.addEventListener('beforeunload', function(e) {
    if (placedQR) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>
@endpush
