{{-- resources/views/digital-signature/user/sign-document.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Digital Document Signing')

@push('styles')
<style>
.signing-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.document-preview {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.signature-canvas-container {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.canvas-wrapper {
    position: relative;
    border: 2px dashed #dee2e6;
    border-radius: 1rem;
    background: #f8f9fa;
    overflow: hidden;
}

.signature-canvas {
    display: block;
    cursor: crosshair;
    border-radius: 1rem;
}

.canvas-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.signature-tools {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.color-picker {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.brush-size {
    width: 120px;
}

.preview-signature {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.signing-steps {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.step-line {
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    z-index: -1;
}

.document-info {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.signature-placement {
    border: 2px dashed #007bff;
    background: rgba(0, 123, 255, 0.1);
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    margin: 1rem 0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.signature-placement:hover {
    background: rgba(0, 123, 255, 0.2);
    border-color: #0056b3;
}

.signature-placement.has-signature {
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.signing-controls {
    position: sticky;
    bottom: 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    border: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .signing-container {
        padding: 1rem;
    }

    .canvas-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .signature-tools {
        justify-content: center;
    }

    .step-indicator {
        flex-direction: column;
        gap: 1rem;
    }

    .step-line {
        display: none;
    }
}
</style>
@endpush

@section('content')
<div class="signing-container">
    <!-- Signing Steps Indicator -->
    <div class="signing-steps">
        <div class="step-indicator">
            <div class="step-item">
                <div class="step-number completed">1</div>
                <div class="step-line"></div>
                <small>Document Review</small>
            </div>
            <div class="step-item">
                <div class="step-number active">2</div>
                <div class="step-line"></div>
                <small>Create Signature</small>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-line"></div>
                <small>Place Signature</small>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <small>Complete Signing</small>
            </div>
        </div>
        <h5 class="mb-0">Step 2: Create Your Digital Signature</h5>
        <p class="mb-0 opacity-75">Draw your signature in the canvas below</p>
    </div>

    <!-- Document Information -->
    <div class="document-preview">
        <div class="document-info">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-3">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        {{ $approvalRequest->document_name }}
                    </h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Document Number:</strong><br>
                            <span class="text-muted">{{ $approvalRequest->full_document_number }}</span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Submitted:</strong><br>
                            <span class="text-muted">{{ $approvalRequest->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                    @if($approvalRequest->notes)
                    <div class="mt-3">
                        <strong>Description:</strong><br>
                        <span class="text-muted">{{ $approvalRequest->notes }}</span>
                    </div>
                    @endif
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

        <!-- Signature Placement Area -->
        <div class="signature-placement" id="signaturePlacement">
            <i class="fas fa-signature fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Your signature will be placed here</h5>
            <p class="text-muted mb-0">Complete the signature canvas below first</p>
        </div>
    </div>

    <!-- Signature Canvas -->
    <div class="signature-canvas-container">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                 style="width: 50px; height: 50px;">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div>
                <h4 class="mb-1">Create Your Signature</h4>
                <p class="text-muted mb-0">Draw your signature using mouse, touch, or stylus</p>
            </div>
        </div>

        <div class="canvas-wrapper">
            <canvas id="signatureCanvas" class="signature-canvas" width="800" height="300"></canvas>
        </div>

        <div class="canvas-controls">
            <div class="signature-tools">
                <label class="form-label me-2">Color:</label>
                <input type="color" id="colorPicker" class="color-picker" value="#000000">

                <label class="form-label me-2 ms-3">Brush Size:</label>
                <input type="range" id="brushSize" class="brush-size" min="1" max="10" value="3">
                <span id="brushSizeValue" class="ms-2">3px</span>
            </div>

            <div class="canvas-actions">
                <button class="btn btn-outline-secondary" onclick="clearCanvas()">
                    <i class="fas fa-eraser me-1"></i> Clear
                </button>
                <button class="btn btn-outline-primary" onclick="undoLastStroke()">
                    <i class="fas fa-undo me-1"></i> Undo
                </button>
            </div>
        </div>

        <!-- Signature Preview -->
        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Preview:</label>
                <div class="preview-signature" id="signaturePreview">
                    <span class="text-muted">Your signature will appear here</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Template Options:</label>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-info btn-sm" onclick="loadTemplate('formal')">
                        Formal Style
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="loadTemplate('casual')">
                        Casual Style
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="loadTemplate('elegant')">
                        Elegant Style
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Positioning Controls -->
    <div class="signature-canvas-container" id="positioningControls" style="display: none;">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                 style="width: 50px; height: 50px;">
                <i class="fas fa-crosshairs"></i>
            </div>
            <div>
                <h4 class="mb-1">Position Your Signature</h4>
                <p class="text-muted mb-0">Adjust the position and size of your signature</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Horizontal Position:</label>
                <input type="range" id="positionX" class="form-range" min="0" max="100" value="50">
                <div class="d-flex justify-content-between">
                    <small>Left</small>
                    <small>Center</small>
                    <small>Right</small>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Vertical Position:</label>
                <input type="range" id="positionY" class="form-range" min="0" max="100" value="80">
                <div class="d-flex justify-content-between">
                    <small>Top</small>
                    <small>Middle</small>
                    <small>Bottom</small>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Signature Size:</label>
                <input type="range" id="signatureSize" class="form-range" min="50" max="200" value="100">
                <div class="d-flex justify-content-between">
                    <small>50%</small>
                    <small>100%</small>
                    <small>200%</small>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rotation:</label>
                <input type="range" id="signatureRotation" class="form-range" min="-15" max="15" value="0">
                <div class="d-flex justify-content-between">
                    <small>-15°</small>
                    <small>0°</small>
                    <small>15°</small>
                </div>
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
                    I confirm that this is my authentic digital signature
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

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Signature Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h6>Your signature will appear like this on the document:</h6>
                </div>
                <div id="finalPreview" class="border rounded p-3" style="min-height: 200px;">
                    <!-- Preview content will be inserted here -->
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

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5>Processing Digital Signature...</h5>
                <p class="text-muted mb-0">Please wait while we securely sign your document</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
class SignatureCanvas {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.isDrawing = false;
        this.strokes = [];
        this.currentStroke = [];

        this.setupCanvas();
        this.bindEvents();
    }

    setupCanvas() {
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 3;

        // Set canvas background
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }

    bindEvents() {
        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
        this.canvas.addEventListener('mousemove', (e) => this.draw(e));
        this.canvas.addEventListener('mouseup', () => this.stopDrawing());
        this.canvas.addEventListener('mouseout', () => this.stopDrawing());

        // Touch events
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            this.startDrawing(e.touches[0]);
        });
        this.canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            this.draw(e.touches[0]);
        });
        this.canvas.addEventListener('touchend', (e) => {
            e.preventDefault();
            this.stopDrawing();
        });
    }

    getPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    startDrawing(e) {
        this.isDrawing = true;
        const pos = this.getPosition(e);
        this.currentStroke = [pos];
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);
    }

    draw(e) {
        if (!this.isDrawing) return;

        const pos = this.getPosition(e);
        this.currentStroke.push(pos);
        this.ctx.lineTo(pos.x, pos.y);
        this.ctx.stroke();
    }

    stopDrawing() {
        if (this.isDrawing) {
            this.isDrawing = false;
            this.strokes.push({
                points: [...this.currentStroke],
                color: this.ctx.strokeStyle,
                width: this.ctx.lineWidth
            });
            this.currentStroke = [];
            this.updatePreview();
            this.checkCanvasState();
        }
    }

    clear() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        this.strokes = [];
        this.updatePreview();
        this.checkCanvasState();
    }

    undo() {
        if (this.strokes.length > 0) {
            this.strokes.pop();
            this.redraw();
            this.updatePreview();
            this.checkCanvasState();
        }
    }

    redraw() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

        this.strokes.forEach(stroke => {
            if (stroke.points.length > 0) {
                this.ctx.strokeStyle = stroke.color;
                this.ctx.lineWidth = stroke.width;
                this.ctx.beginPath();
                this.ctx.moveTo(stroke.points[0].x, stroke.points[0].y);

                stroke.points.forEach(point => {
                    this.ctx.lineTo(point.x, point.y);
                });

                this.ctx.stroke();
            }
        });
    }

    updatePreview() {
        const preview = document.getElementById('signaturePreview');
        const dataURL = this.canvas.toDataURL();

        if (this.strokes.length > 0) {
            preview.innerHTML = `<img src="${dataURL}" alt="Signature Preview" style="max-width: 100%; max-height: 80px;">`;
            this.updateSignaturePlacement(dataURL);
        } else {
            preview.innerHTML = '<span class="text-muted">Your signature will appear here</span>';
            this.clearSignaturePlacement();
        }
    }

    updateSignaturePlacement(dataURL) {
        const placement = document.getElementById('signaturePlacement');
        placement.innerHTML = `
            <div class="d-flex align-items-center justify-content-center">
                <img src="${dataURL}" alt="Signature" style="max-height: 100px; border: 2px dashed #28a745; border-radius: 0.5rem; padding: 0.5rem; background: white;">
            </div>
            <h6 class="text-success mt-2">Signature Ready</h6>
            <p class="text-muted mb-0">Click preview to see final placement</p>
        `;
        placement.classList.add('has-signature');
    }

    clearSignaturePlacement() {
        const placement = document.getElementById('signaturePlacement');
        placement.innerHTML = `
            <i class="fas fa-signature fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Your signature will be placed here</h5>
            <p class="text-muted mb-0">Complete the signature canvas below first</p>
        `;
        placement.classList.remove('has-signature');
    }

    checkCanvasState() {
        const hasSignature = this.strokes.length > 0;
        const confirmCheckbox = document.getElementById('confirmSignature');
        const previewBtn = document.getElementById('previewBtn');
        const signBtn = document.getElementById('signBtn');

        previewBtn.disabled = !hasSignature;
        signBtn.disabled = !hasSignature || !confirmCheckbox.checked;
    }

    getCanvasData() {
        return {
            dataURL: this.canvas.toDataURL(),
            strokes: this.strokes,
            canvasWidth: this.canvas.width,
            canvasHeight: this.canvas.height
        };
    }
}

// Initialize signature canvas
let signatureCanvas;

document.addEventListener('DOMContentLoaded', function() {
    signatureCanvas = new SignatureCanvas('signatureCanvas');

    // Color picker
    document.getElementById('colorPicker').addEventListener('change', function() {
        signatureCanvas.ctx.strokeStyle = this.value;
    });

    // Brush size
    const brushSize = document.getElementById('brushSize');
    const brushSizeValue = document.getElementById('brushSizeValue');

    brushSize.addEventListener('input', function() {
        signatureCanvas.ctx.lineWidth = this.value;
        brushSizeValue.textContent = this.value + 'px';
    });

    // Confirm checkbox
    document.getElementById('confirmSignature').addEventListener('change', function() {
        signatureCanvas.checkCanvasState();
    });
});

function clearCanvas() {
    signatureCanvas.clear();
}

function undoLastStroke() {
    signatureCanvas.undo();
}

function loadTemplate(type) {
    // This would load predefined signature templates
    alert(`Loading ${type} template - Feature coming soon!`);
}

function previewSignature() {
    const canvasData = signatureCanvas.getCanvasData();

    if (canvasData.strokes.length === 0) {
        alert('Please create a signature first.');
        return;
    }

    // Update timestamp
    document.getElementById('signingTimestamp').textContent = new Date().toLocaleString();

    // Show preview in modal
    const finalPreview = document.getElementById('finalPreview');
    finalPreview.innerHTML = `
        <div class="document-preview-container">
            <div class="bg-light p-3 rounded">
                <h6>{{ $approvalRequest->document_name }}</h6>
                <p class="mb-3">{{ $approvalRequest->full_document_number }}</p>
                <div class="signature-placement-preview">
                    <img src="${canvasData.dataURL}" alt="Your Signature" style="max-height: 80px;">
                </div>
                <small class="text-muted">Digital signature will be cryptographically secured</small>
            </div>
        </div>
    `;

    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

function confirmSigning() {
    // Close preview modal
    bootstrap.Modal.getInstance(document.getElementById('previewModal')).hide();

    // Show loading modal
    new bootstrap.Modal(document.getElementById('loadingModal')).show();

    // Proceed with signing
    signDocument();
}

function signDocument() {
    const canvasData = signatureCanvas.getCanvasData();

    if (canvasData.strokes.length === 0) {
        alert('Please create a signature first.');
        return;
    }

    if (!document.getElementById('confirmSignature').checked) {
        alert('Please confirm that this is your authentic signature.');
        return;
    }

    // Prepare positioning data
    const positioningData = {
        x: document.getElementById('positionX')?.value || 50,
        y: document.getElementById('positionY')?.value || 80,
        size: document.getElementById('signatureSize')?.value || 100,
        rotation: document.getElementById('signatureRotation')?.value || 0
    };

    // Prepare form data
    const formData = new FormData();
    formData.append('canvas_data', canvasData.dataURL);
    formData.append('positioning_data', JSON.stringify(positioningData));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    // Submit signature
    fetch('{{ route("user.signature.sign.process", $approvalRequest->id) }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading modal
        bootstrap.Modal.getInstance(document.getElementById('loadingModal')).hide();

        if (data.success) {
            // Show success message
            alert('Document signed successfully!');

            // Redirect to status page
            window.location.href = '{{ route("user.signature.approval.status") }}';
        } else {
            alert('Signing failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Hide loading modal
        bootstrap.Modal.getInstance(document.getElementById('loadingModal')).hide();

        console.error('Signing error:', error);
        alert('Signing failed. Please try again.');
    });
}

function goBack() {
    if (confirm('Are you sure you want to go back? Your signature will be lost.')) {
        window.location.href = '{{ route("user.signature.approval.status") }}';
    }
}

// Prevent accidental page reload
window.addEventListener('beforeunload', function(e) {
    if (signatureCanvas && signatureCanvas.strokes.length > 0) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>
@endpush
