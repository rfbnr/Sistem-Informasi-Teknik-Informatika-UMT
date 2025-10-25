{{-- resources/views/digital-signature/user/sign-document-new.blade.php --}}
{{-- NEW VERSION: Drag & Drop Template TTD Kaprodi --}}
{{-- @extends('digital-signature.layouts.app') --}}
@extends('user.layouts.app')

@section('title', 'Digital Document Signing - Drag & Drop')

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
    /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
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

#signatureOverlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.placed-signature {
    position: absolute;
    border: 2px dashed #007bff;
    background: rgba(0, 123, 255, 0.1);
    cursor: move;
    pointer-events: all;
    border-radius: 0.25rem;
    padding: 0.5rem;
    z-index: 10;
}

.placed-signature:hover {
    border-color: #0056b3;
    background: rgba(0, 123, 255, 0.2);
}

.placed-signature.selected {
    border-color: #28a745;
    border-width: 3px;
    box-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
}

.placed-signature img {
    width: 100%;
    height: auto;
    display: block;
    pointer-events: none;
}

.signature-handles {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #007bff;
    border: 2px solid white;
    border-radius: 50%;
    cursor: nwse-resize;
}

.handle-nw { top: -5px; left: -5px; cursor: nwse-resize; }
.handle-ne { top: -5px; right: -5px; cursor: nesw-resize; }
.handle-sw { bottom: -5px; left: -5px; cursor: nesw-resize; }
.handle-se { bottom: -5px; right: -5px; cursor: nwse-resize; }

.delete-signature-btn {
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

.delete-signature-btn:hover {
    background: #c82333;
}

/* ========== TEMPLATE SELECTOR ========== */
.template-selector-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.template-item {
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 0.75rem;
    padding: 1rem;
    cursor: grab;
    transition: all 0.3s ease;
    position: relative;
}

.template-item:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    transform: translateY(-2px);
}

.template-item.dragging {
    opacity: 0.5;
    cursor: grabbing;
}

.template-item.default-badge::after {
    content: "DEFAULT";
    position: absolute;
    top: 10px;
    right: 10px;
    background: #28a745;
    color: white;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: bold;
}

.template-preview-img {
    width: 100%;
    height: 150px;
    object-fit: contain;
    background: #f8f9fa;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
}

.template-info h6 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.template-meta {
    font-size: 12px;
    color: #666;
}

.template-meta i {
    margin-right: 5px;
}

/* ========== CONTROL PANEL ========== */
.control-panel-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.control-group {
    margin-bottom: 1.5rem;
}

.control-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.range-control {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.range-control input[type="range"] {
    flex: 1;
}

.range-value {
    min-width: 60px;
    text-align: center;
    font-weight: 600;
    color: #007bff;
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

    .template-grid {
        grid-template-columns: 1fr;
    }

    .pdf-preview-wrapper {
        max-height: 500px;
    }

    .step-indicator {
        flex-direction: column;
        gap: 1rem;
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
                <small>Select Template</small>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <small>Position Signature</small>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <small>Complete Signing</small>
            </div>
        </div>
        <h5 class="mb-0">Step 2: Select & Position Your Signature Template</h5>
        <p class="mb-0 opacity-75">Drag and drop the signature template onto the document</p>
    </div>

    <!-- Template Selector -->
    {{-- <div class="template-selector-section">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                 style="width: 50px; height: 50px;">
                <i class="fas fa-stamp"></i>
            </div>
            <div>
                <h4 class="mb-1">Select Signature Template</h4>
                <p class="text-muted mb-0">Drag the template to the document above</p>
            </div>
        </div>

        <div id="templateGrid" class="template-grid">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading templates...</p>
            </div>
        </div>
    </div> --}}

    <!-- Document Information -->
    <div class="pdf-preview-section">


        <div class="mb-3">
            <h4 class="mb-3">
                <i class="fas fa-file-alt text-primary me-2"></i>
                {{ $approvalRequest->document_name }}
            </h4>
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Document Number:</strong><br>
                            <span class="text-muted">{{ $approvalRequest->full_document_number }}</span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Document Type:</strong><br>
                            <span class="text-muted">{{ $approvalRequest->document_type ? $approvalRequest->document_type : 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6">
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

            {{--  --}}
            <div class="template-selector-section mt-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 30px; height: 30px;">
                        <i class="fas fa-stamp"></i>
                    </div>
                    <div>
                        <h4 class="mb-1">Select Signature Template</h4>
                        <p class="text-muted mb-0">Drag the template to the document above</p>
                    </div>
                </div>

                <div id="templateGrid" class="template-grid">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading templates...</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- PDF Preview with Overlay -->
        <div class="pdf-preview-wrapper" id="pdfPreviewWrapper">
            <canvas id="pdfCanvas"></canvas>
            <div id="signatureOverlay"></div>
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



    <!-- Control Panel -->
    <div class="control-panel-section" id="controlPanel" style="display: none;">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                 style="width: 50px; height: 50px;">
                <i class="fas fa-sliders-h"></i>
            </div>
            <div>
                <h4 class="mb-1">Adjust Signature Position & Size</h4>
                <p class="text-muted mb-0">Fine-tune the signature placement</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="control-group">
                    <label>Width</label>
                    <div class="range-control">
                        <input type="range" id="widthSlider" min="50" max="500" value="200" class="form-range">
                        <span class="range-value"><span id="widthValue">200</span>px</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="control-group">
                    <label>Height</label>
                    <div class="range-control">
                        <input type="range" id="heightSlider" min="50" max="300" value="100" class="form-range">
                        <span class="range-value"><span id="heightValue">100</span>px</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="control-group">
                    <label>Horizontal Position</label>
                    <div class="range-control">
                        <input type="range" id="positionXSlider" min="0" max="100" value="50" class="form-range">
                        <span class="range-value"><span id="positionXValue">50</span>%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="control-group">
                    <label>Vertical Position</label>
                    <div class="range-control">
                        <input type="range" id="positionYSlider" min="0" max="100" value="50" class="form-range">
                        <span class="range-value"><span id="positionYValue">50</span>%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Tip:</strong> You can also drag the signature directly on the document and resize using the corner handles.
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
                    I confirm that this signature placement is correct and I authorize this document
                </label>
            </div>
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>
                This signature will be legally binding and cryptographically secured
            </small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" onclick="goBack()">
                <i class="fas fa-arrow-left me-1"></i> Back
            </button>
            <button class="btn btn-warning" id="previewBtn" onclick="previewSignature()" disabled>
                <i class="fas fa-eye me-1"></i> Preview
            </button>
            <button class="btn btn-success" id="signBtn" onclick="signDocument()" disabled>
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
        <p class="text-muted mb-0">Please wait while we securely sign your document</p>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Signature Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h6>Your signature will appear like this on the document:</h6>
                </div>
                <div id="finalPreview" class="border rounded p-3 bg-light" style="min-height: 300px; position: relative;">
                    <canvas id="previewCanvas"></canvas>
                </div>
                <div class="mt-3">
                    <strong>Document Details:</strong>
                    <ul class="list-unstyled mt-2">
                        <li><strong>Document:</strong> {{ $approvalRequest->document_name }}</li>
                        <li><strong>Number:</strong> {{ $approvalRequest->full_document_number }}</li>
                        <li><strong>Signer:</strong> {{ auth()->user()->name }}</li>
                        <li><strong>Timestamp:</strong> <span id="signingTimestamp"></span></li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Edit Signature</button>
                <button type="button" class="btn btn-success" onclick="confirmSigning()">
                    <i class="fas fa-signature me-1"></i> Confirm & Sign
                </button>
            </div>
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

let availableTemplates = [];
let placedSignature = null;
let selectedTemplate = null;

const approvalRequestId = {{ $approvalRequest->id }};
const documentPath = "{{ Storage::url($approvalRequest->document_path) }}";

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing document signing...');

    // Initialize PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    // Load PDF document
    loadPDF();

    // Load templates
    loadTemplates();

    // Setup event listeners
    setupEventListeners();
});

// ==================== PDF RENDERING ====================
async function loadPDF() {
    try {
        console.log('Loading PDF:', documentPath);

        const loadingTask = pdfjsLib.getDocument(documentPath);
        pdfDocument = await loadingTask.promise;
        totalPages = pdfDocument.numPages;

        document.getElementById('totalPages').textContent = totalPages;

        console.log(`PDF loaded successfully. Total pages: ${totalPages}`);

        // Render first page
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
        const overlay = document.getElementById('signatureOverlay');
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

        // Update page navigation
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

// ==================== TEMPLATE LOADING ====================
async function loadTemplates() {
    try {
        console.log('Loading templates...');

        const response = await fetch(`/user/signature/sign/${approvalRequestId}/templates`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        console.log('Templates fetch response status:', response.status);

        const data = await response.json();

        console.log('Templates response:', data);

        if (data.success) {
            availableTemplates = data.templates;
            console.log(`Loaded ${data.total} templates`);
            renderTemplates();
        } else {
            throw new Error(data.error || 'Failed to load templates');
        }

    } catch (error) {
        console.error('Error loading templates:', error);
        document.getElementById('templateGrid').innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <p class="text-danger">Failed to load templates</p>
                <button class="btn btn-primary btn-sm" onclick="loadTemplates()">Retry</button>
            </div>
        `;
    }
}

function renderTemplates() {
    const grid = document.getElementById('templateGrid');

    if (availableTemplates.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No templates available</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = '';

    availableTemplates.forEach(template => {
        const templateElement = document.createElement('div');
        templateElement.className = 'template-item' + (template.is_default ? ' default-badge' : '');
        templateElement.dataset.templateId = template.id;
        templateElement.draggable = true;

        templateElement.innerHTML = `
            <img src="${template.signature_image_url}"
                 alt="${template.name}"
                 class="template-preview-img">
            <div class="template-info">
                <h6>${template.name}</h6>
                <div class="template-meta">
                    <div><i class="fas fa-user"></i> ${template.kaprodi_name}</div>
                    <div><i class="fas fa-chart-line"></i> Used ${template.usage_count} times</div>
                </div>
            </div>
        `;

        // Add drag event listeners
        templateElement.addEventListener('dragstart', handleDragStart);
        templateElement.addEventListener('dragend', handleDragEnd);
        templateElement.addEventListener('click', () => selectTemplate(template));

        grid.appendChild(templateElement);
    });

    console.log('Templates rendered successfully');
}

// ==================== DRAG & DROP ====================
function handleDragStart(e) {
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('templateId', e.target.dataset.templateId);
    console.log('Drag started for template:', e.target.dataset.templateId);
}

function handleDragEnd(e) {
    e.target.classList.remove('dragging');
    console.log('Drag ended');
}

function selectTemplate(template) {
    selectedTemplate = template;
    console.log('Template selected:', template.name);
}

// ==================== PDF DROP ZONE ====================
const pdfWrapper = document.getElementById('pdfPreviewWrapper');

pdfWrapper.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    pdfWrapper.style.borderColor = '#007bff';
    pdfWrapper.style.background = 'rgba(0, 123, 255, 0.05)';
});

pdfWrapper.addEventListener('dragleave', function(e) {
    pdfWrapper.style.borderColor = '#dee2e6';
    pdfWrapper.style.background = '#f8f9fa';
});

pdfWrapper.addEventListener('drop', function(e) {
    e.preventDefault();
    pdfWrapper.style.borderColor = '#dee2e6';
    pdfWrapper.style.background = '#f8f9fa';

    const templateId = e.dataTransfer.getData('templateId');

    if (!templateId) {
        console.warn('No template ID in drop data');
        return;
    }

    const template = availableTemplates.find(t => t.id == templateId);

    if (!template) {
        console.error('Template not found:', templateId);
        return;
    }

    // Get drop position relative to PDF canvas
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    console.log('Template dropped at:', { x, y });

    placeSignatureOnPDF(template, x, y);
});

// ==================== SIGNATURE PLACEMENT ====================
function placeSignatureOnPDF(template, x, y) {
    // Remove existing signature if any
    if (placedSignature) {
        placedSignature.remove();
    }

    const overlay = document.getElementById('signatureOverlay');

    // Create signature element
    const signatureDiv = document.createElement('div');
    signatureDiv.className = 'placed-signature selected';
    signatureDiv.id = 'placedSignature';
    signatureDiv.dataset.templateId = template.id;
    signatureDiv.style.left = x + 'px';
    signatureDiv.style.top = y + 'px';
    signatureDiv.style.width = '200px';
    signatureDiv.style.height = '100px';

    // Add signature image
    const img = document.createElement('img');
    img.src = template.signature_image_url;
    img.alt = template.name;
    signatureDiv.appendChild(img);

    // Add delete button
    const deleteBtn = document.createElement('div');
    deleteBtn.className = 'delete-signature-btn';
    deleteBtn.innerHTML = '×';
    deleteBtn.onclick = removeSignature;
    signatureDiv.appendChild(deleteBtn);

    // Add resize handles
    ['nw', 'ne', 'sw', 'se'].forEach(pos => {
        const handle = document.createElement('div');
        handle.className = `signature-handles handle-${pos}`;
        signatureDiv.appendChild(handle);
    });

    overlay.appendChild(signatureDiv);
    placedSignature = signatureDiv;

    // Make draggable and resizable
    makeSignatureDraggable(signatureDiv);
    makeSignatureResizable(signatureDiv);

    // Show control panel
    document.getElementById('controlPanel').style.display = 'block';

    // Enable buttons
    updateButtonStates();

    console.log('Signature placed successfully');
}

function makeSignatureDraggable(element) {
    let isDragging = false;
    let currentX;
    let currentY;
    let initialX;
    let initialY;

    element.addEventListener('mousedown', function(e) {
        if (e.target.classList.contains('signature-handles') || e.target.classList.contains('delete-signature-btn')) {
            return;
        }

        isDragging = true;
        initialX = e.clientX - element.offsetLeft;
        initialY = e.clientY - element.offsetTop;
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;

        e.preventDefault();
        currentX = e.clientX - initialX;
        currentY = e.clientY - initialY;

        element.style.left = currentX + 'px';
        element.style.top = currentY + 'px';
    });

    document.addEventListener('mouseup', function() {
        isDragging = false;
    });
}

function makeSignatureResizable(element) {
    const handles = element.querySelectorAll('.signature-handles');

    handles.forEach(handle => {
        let isResizing = false;
        let startX, startY, startWidth, startHeight, startLeft, startTop;

        handle.addEventListener('mousedown', function(e) {
            e.stopPropagation();
            isResizing = true;
            startX = e.clientX;
            startY = e.clientY;
            startWidth = parseInt(window.getComputedStyle(element).width, 10);
            startHeight = parseInt(window.getComputedStyle(element).height, 10);
            startLeft = element.offsetLeft;
            startTop = element.offsetTop;
        });

        document.addEventListener('mousemove', function(e) {
            if (!isResizing) return;

            const dx = e.clientX - startX;
            const dy = e.clientY - startY;

            if (handle.classList.contains('handle-se')) {
                element.style.width = (startWidth + dx) + 'px';
                element.style.height = (startHeight + dy) + 'px';
            } else if (handle.classList.contains('handle-sw')) {
                element.style.width = (startWidth - dx) + 'px';
                element.style.height = (startHeight + dy) + 'px';
                element.style.left = (startLeft + dx) + 'px';
            } else if (handle.classList.contains('handle-ne')) {
                element.style.width = (startWidth + dx) + 'px';
                element.style.height = (startHeight - dy) + 'px';
                element.style.top = (startTop + dy) + 'px';
            } else if (handle.classList.contains('handle-nw')) {
                element.style.width = (startWidth - dx) + 'px';
                element.style.height = (startHeight - dy) + 'px';
                element.style.left = (startLeft + dx) + 'px';
                element.style.top = (startTop + dy) + 'px';
            }

            updateControlPanelValues();
        });

        document.addEventListener('mouseup', function() {
            isResizing = false;
        });
    });
}

function removeSignature() {
    if (placedSignature) {
        placedSignature.remove();
        placedSignature = null;
        document.getElementById('controlPanel').style.display = 'none';
        updateButtonStates();
        console.log('Signature removed');
    }
}

// ==================== CONTROL PANEL ====================
function setupEventListeners() {
    // Width slider
    document.getElementById('widthSlider').addEventListener('input', function(e) {
        if (placedSignature) {
            placedSignature.style.width = e.target.value + 'px';
            document.getElementById('widthValue').textContent = e.target.value;
        }
    });

    // Height slider
    document.getElementById('heightSlider').addEventListener('input', function(e) {
        if (placedSignature) {
            placedSignature.style.height = e.target.value + 'px';
            document.getElementById('heightValue').textContent = e.target.value;
        }
    });

    // Position X slider
    document.getElementById('positionXSlider').addEventListener('input', function(e) {
        if (placedSignature) {
            const canvasWidth = canvas.width;
            const sigWidth = placedSignature.offsetWidth;
            const maxX = canvasWidth - sigWidth;
            const x = (e.target.value / 100) * maxX;
            placedSignature.style.left = x + 'px';
            document.getElementById('positionXValue').textContent = e.target.value;
        }
    });

    // Position Y slider
    document.getElementById('positionYSlider').addEventListener('input', function(e) {
        if (placedSignature) {
            const canvasHeight = canvas.height;
            const sigHeight = placedSignature.offsetHeight;
            const maxY = canvasHeight - sigHeight;
            const y = (e.target.value / 100) * maxY;
            placedSignature.style.top = y + 'px';
            document.getElementById('positionYValue').textContent = e.target.value;
        }
    });

    // Confirm checkbox
    document.getElementById('confirmSignature').addEventListener('change', updateButtonStates);
}

function updateControlPanelValues() {
    if (!placedSignature) return;

    document.getElementById('widthSlider').value = placedSignature.offsetWidth;
    document.getElementById('widthValue').textContent = placedSignature.offsetWidth;

    document.getElementById('heightSlider').value = placedSignature.offsetHeight;
    document.getElementById('heightValue').textContent = placedSignature.offsetHeight;
}

function updateButtonStates() {
    const hasSignature = placedSignature !== null;
    const isConfirmed = document.getElementById('confirmSignature').checked;

    document.getElementById('previewBtn').disabled = !hasSignature;
    document.getElementById('signBtn').disabled = !hasSignature || !isConfirmed;
}

// ==================== PREVIEW & SIGNING ====================
function previewSignature() {
    if (!placedSignature) {
        alert('Please place a signature template first.');
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

    // Draw signature overlay
    const img = new Image();
    img.src = placedSignature.querySelector('img').src;
    img.onload = function() {
        const rect = placedSignature.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();

        const x = rect.left - canvasRect.left;
        const y = rect.top - canvasRect.top;
        const w = rect.width;
        const h = rect.height;

        previewCtx.drawImage(img, x, y, w, h);
    };

    // Show modal
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

function confirmSigning() {
    // Close preview modal
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();

    // Show loading
    document.getElementById('loadingOverlay').classList.add('active');

    // Sign document
    signDocument();
}

async function signDocument() {
    if (!placedSignature) {
        alert('Please place a signature template first.');
        return;
    }

    if (!document.getElementById('confirmSignature').checked) {
        alert('Please confirm your signature placement.');
        return;
    }

    try {
        console.log('Starting document signing...');

        document.getElementById('loadingOverlay').classList.add('active');

        // Get positioning data
        const rect = placedSignature.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();

        const positioningData = {
            template_id: parseInt(placedSignature.dataset.templateId),
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

        console.log('Positioning data:', positioningData);

        // Prepare form data
        const formData = new FormData();
        formData.append('template_id', positioningData.template_id);
        formData.append('positioning_data', JSON.stringify(positioningData));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        // Submit
        const response = await fetch(`/user/signature/sign/${approvalRequestId}/process`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        document.getElementById('loadingOverlay').classList.remove('active');

        if (data.success) {
            alert('Document signed successfully!');
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
    if (confirm('Are you sure you want to go back? Your signature placement will be lost.')) {
        window.location.href = '{{ route("user.signature.approval.status") }}';
    }
}

// Prevent accidental page reload
window.addEventListener('beforeunload', function(e) {
    if (placedSignature) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>
@endpush
