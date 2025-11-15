/**
 * SignatureCanvas - Digital Signature Canvas Interface
 * Handles document signing with drag-and-drop positioning
 */
class SignatureCanvas {
    constructor(options) {
        this.options = {
            canvasId: "signatureCanvas",
            approvalRequestId: null,
            digitalSignatureId: null,
            canvasData: null,
            ...options,
        };

        this.canvas = document.getElementById(this.options.canvasId);
        this.ctx = this.canvas.getContext("2d");
        this.isDrawing = false;
        this.hasSignatureDrawn = false;
        this.brushSize = 3;
        this.brushColor = "#000000";
        this.eventListeners = {};

        this.init();
    }

    init() {
        this.setupCanvas();
        this.setupEventListeners();
        this.setupDragDrop();
        this.loadCanvasData();
    }

    setupCanvas() {
        // Set canvas size
        const container = this.canvas.parentElement;
        const rect = container.getBoundingClientRect();

        this.canvas.width = 800;
        this.canvas.height = 600;

        // Set initial styles
        this.ctx.lineCap = "round";
        this.ctx.lineJoin = "round";
        this.ctx.strokeStyle = this.brushColor;
        this.ctx.lineWidth = this.brushSize;

        // Draw grid if enabled
        this.drawGrid();
    }

    setupEventListeners() {
        // Mouse events
        this.canvas.addEventListener("mousedown", this.startDrawing.bind(this));
        this.canvas.addEventListener("mousemove", this.draw.bind(this));
        this.canvas.addEventListener("mouseup", this.stopDrawing.bind(this));
        this.canvas.addEventListener("mouseout", this.stopDrawing.bind(this));

        // Touch events for mobile
        this.canvas.addEventListener("touchstart", this.handleTouch.bind(this));
        this.canvas.addEventListener("touchmove", this.handleTouch.bind(this));
        this.canvas.addEventListener("touchend", this.stopDrawing.bind(this));

        // Prevent scrolling when touching the canvas
        this.canvas.addEventListener("touchstart", (e) => e.preventDefault());
        this.canvas.addEventListener("touchend", (e) => e.preventDefault());
        this.canvas.addEventListener("touchmove", (e) => e.preventDefault());
    }

    setupDragDrop() {
        const draggableElements =
            document.querySelectorAll(".draggable-element");

        draggableElements.forEach((element) => {
            element.addEventListener("mousedown", this.startDrag.bind(this));
            element.addEventListener("dragstart", (e) => e.preventDefault());
        });

        document.addEventListener("mousemove", this.drag.bind(this));
        document.addEventListener("mouseup", this.stopDrag.bind(this));
    }

    loadCanvasData() {
        if (this.options.canvasData) {
            this.applyCanvasData(this.options.canvasData);
        }
    }

    // Drawing functions
    startDrawing(e) {
        // Only draw in signature area
        if (!this.isInSignatureArea(e)) return;

        this.isDrawing = true;
        this.ctx.beginPath();

        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        this.ctx.moveTo(x, y);
        this.hasSignatureDrawn = true;
        this.emit("signatureStarted");
    }

    draw(e) {
        if (!this.isDrawing) return;

        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        this.ctx.lineTo(x, y);
        this.ctx.stroke();
        this.ctx.beginPath();
        this.ctx.moveTo(x, y);

        this.emit("signatureDrawn");
    }

    stopDrawing() {
        if (this.isDrawing) {
            this.isDrawing = false;
            this.ctx.beginPath();
            this.emit("signatureCompleted");
        }
    }

    handleTouch(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent(
            e.type === "touchstart"
                ? "mousedown"
                : e.type === "touchmove"
                ? "mousemove"
                : "mouseup",
            {
                clientX: touch.clientX,
                clientY: touch.clientY,
            }
        );
        this.canvas.dispatchEvent(mouseEvent);
    }

    isInSignatureArea(e) {
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Convert canvas coordinates to signature area
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;
        const canvasX = x * scaleX;
        const canvasY = y * scaleY;

        // Default signature area bounds (can be customized)
        const signatureArea = {
            x: 220,
            y: 50,
            width: 200,
            height: 100,
        };

        return (
            canvasX >= signatureArea.x &&
            canvasX <= signatureArea.x + signatureArea.width &&
            canvasY >= signatureArea.y &&
            canvasY <= signatureArea.y + signatureArea.height
        );
    }

    // Drag and drop functions
    startDrag(e) {
        this.dragElement = e.currentTarget;
        this.dragElement.classList.add("dragging");

        const rect = this.dragElement.getBoundingClientRect();
        this.dragOffset = {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top,
        };

        e.preventDefault();
    }

    drag(e) {
        if (!this.dragElement) return;

        const containerRect = document
            .getElementById("canvasContainer")
            .getBoundingClientRect();

        let newX = e.clientX - containerRect.left - this.dragOffset.x;
        let newY = e.clientY - containerRect.top - this.dragOffset.y;

        // Constrain to container bounds
        const elementRect = this.dragElement.getBoundingClientRect();
        newX = Math.max(
            0,
            Math.min(newX, containerRect.width - elementRect.width)
        );
        newY = Math.max(
            0,
            Math.min(newY, containerRect.height - elementRect.height)
        );

        this.dragElement.style.left = newX + "px";
        this.dragElement.style.top = newY + "px";

        this.emit("elementMoved", {
            element: this.dragElement.id,
            x: newX,
            y: newY,
        });
    }

    stopDrag() {
        if (this.dragElement) {
            this.dragElement.classList.remove("dragging");
            this.dragElement = null;
        }
    }

    // Utility functions
    setBrushSize(size) {
        this.brushSize = parseInt(size);
        this.ctx.lineWidth = this.brushSize;
    }

    setBrushColor(color) {
        this.brushColor = color;
        this.ctx.strokeStyle = this.brushColor;
    }

    drawGrid() {
        if (!document.getElementById("showGridLines").checked) return;

        const gridSize = 20;
        this.ctx.save();
        this.ctx.strokeStyle = "#e0e0e0";
        this.ctx.lineWidth = 1;
        this.ctx.globalAlpha = 0.5;

        // Vertical lines
        for (let x = 0; x <= this.canvas.width; x += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(x, 0);
            this.ctx.lineTo(x, this.canvas.height);
            this.ctx.stroke();
        }

        // Horizontal lines
        for (let y = 0; y <= this.canvas.height; y += gridSize) {
            this.ctx.beginPath();
            this.ctx.moveTo(0, y);
            this.ctx.lineTo(this.canvas.width, y);
            this.ctx.stroke();
        }

        this.ctx.restore();
    }

    reset() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.drawGrid();
        this.hasSignatureDrawn = false;
        this.emit("canvasReset");
    }

    hasSignature() {
        return this.hasSignatureDrawn;
    }

    getCanvasData() {
        return {
            imageData: this.canvas.toDataURL("image/png"),
            positioning: this.getElementPositions(),
            timestamp: new Date().toISOString(),
        };
    }

    getElementPositions() {
        const positions = {};
        const elements = document.querySelectorAll(".draggable-element");

        elements.forEach((element) => {
            positions[element.id] = {
                x: parseInt(element.style.left) || 0,
                y: parseInt(element.style.top) || 0,
            };
        });

        return positions;
    }

    applyCanvasData(data) {
        if (data.positioning) {
            Object.entries(data.positioning).forEach(([id, position]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.style.left = position.x + "px";
                    element.style.top = position.y + "px";
                }
            });
        }
    }

    // Signature processing
    async processSignature() {
        if (!this.hasSignature()) {
            throw new Error("No signature drawn");
        }

        const canvasData = this.getCanvasData();

        try {
            const response = await fetch(
                `/mahasiswa/approval-requests/${this.options.approvalRequestId}/process-signing`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        canvas_data: canvasData.imageData,
                        positioning_data: canvasData.positioning,
                    }),
                }
            );

            const result = await response.json();

            if (result.success) {
                this.emit("signatureProcessed", result);
                this.showSuccessModal(result);
            } else {
                throw new Error(result.message || "Signing failed");
            }
        } catch (error) {
            console.error("Signature processing error:", error);
            this.emit("signatureError", error);
            this.showErrorModal(error.message);
        }
    }

    // Modal functions
    showSuccessModal(result) {
        $("#signingModal").modal("hide");

        const modalHTML = `
            <div class="modal fade" id="successModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-check-circle"></i> Document Signed Successfully
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-file-signature fa-4x text-success mb-3"></i>
                                <h5>Your document has been digitally signed!</h5>
                                <p class="text-muted">The signature has been cryptographically secured and a verification QR code has been generated.</p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="${result.verification_url}" target="_blank" class="btn btn-info btn-block">
                                        <i class="fas fa-qrcode"></i> View QR Code
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="/mahasiswa/status" class="btn btn-primary btn-block">
                                        <i class="fas fa-list"></i> View Status
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $("body").append(modalHTML);
        $("#successModal").modal("show");

        // Remove modal after closing
        $("#successModal").on("hidden.bs.modal", function () {
            $(this).remove();
        });
    }

    showErrorModal(message) {
        $("#signingModal").modal("hide");

        const modalHTML = `
            <div class="modal fade" id="errorModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle"></i> Signing Failed
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                                <h5>Document signing failed</h5>
                                <p class="text-muted">${message}</p>
                            </div>
                            <p class="small text-muted">
                                Please try again or contact support if the problem persists.
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="location.reload()">Try Again</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $("body").append(modalHTML);
        $("#errorModal").modal("show");

        $("#errorModal").on("hidden.bs.modal", function () {
            $(this).remove();
        });
    }

    // Template functions
    applyTemplate(templateName) {
        const templates = {
            default: {
                qrCode: { x: 50, y: 50 },
                signature: { x: 220, y: 50 },
                textInfo: { x: 220, y: 160 },
                logo: { x: 550, y: 50 },
            },
            compact: {
                qrCode: { x: 30, y: 30 },
                signature: { x: 200, y: 30 },
                textInfo: { x: 200, y: 140 },
                logo: { x: 450, y: 30 },
            },
            detailed: {
                qrCode: { x: 60, y: 60 },
                signature: { x: 250, y: 60 },
                textInfo: { x: 250, y: 180 },
                logo: { x: 580, y: 60 },
            },
        };

        const template = templates[templateName] || templates.default;

        Object.entries(template).forEach(([elementType, position]) => {
            const elementId = elementType + "Element";
            const element = document.getElementById(elementId);
            if (element) {
                element.style.left = position.x + "px";
                element.style.top = position.y + "px";
            }
        });

        this.emit("templateApplied", templateName);
    }

    // Event system
    on(event, callback) {
        if (!this.eventListeners[event]) {
            this.eventListeners[event] = [];
        }
        this.eventListeners[event].push(callback);
    }

    emit(event, data) {
        if (this.eventListeners[event]) {
            this.eventListeners[event].forEach((callback) => callback(data));
        }
    }

    // Export functions
    exportCanvas(format = "png") {
        const dataURL = this.canvas.toDataURL(`image/${format}`);
        const link = document.createElement("a");
        link.download = `signature_canvas_${Date.now()}.${format}`;
        link.href = dataURL;
        link.click();
    }

    // Validation
    validateSignature() {
        const imageData = this.ctx.getImageData(
            0,
            0,
            this.canvas.width,
            this.canvas.height
        );
        const pixels = imageData.data;
        let hasContent = false;

        // Check if there are any non-white pixels (simple validation)
        for (let i = 0; i < pixels.length; i += 4) {
            const r = pixels[i];
            const g = pixels[i + 1];
            const b = pixels[i + 2];
            const a = pixels[i + 3];

            if (a > 0 && (r < 255 || g < 255 || b < 255)) {
                hasContent = true;
                break;
            }
        }

        return hasContent;
    }

    // Canvas utilities
    getCanvasBlob() {
        return new Promise((resolve) => {
            this.canvas.toBlob(resolve, "image/png");
        });
    }

    async uploadCanvas() {
        const blob = await this.getCanvasBlob();
        const formData = new FormData();
        formData.append("canvas", blob, "signature.png");
        formData.append(
            "positioning",
            JSON.stringify(this.getElementPositions())
        );

        // Upload implementation would go here
        return formData;
    }

    // Touch optimization for mobile
    optimizeForMobile() {
        if (window.innerWidth <= 768) {
            // Adjust canvas size for mobile
            this.canvas.style.maxWidth = "100%";
            this.canvas.style.height = "auto";

            // Increase brush size for touch
            this.setBrushSize(Math.max(this.brushSize, 5));

            // Add mobile-specific CSS classes
            this.canvas.classList.add("mobile-optimized");
        }
    }

    // Undo/Redo functionality
    saveState() {
        if (!this.states) this.states = [];
        if (!this.currentStateIndex) this.currentStateIndex = -1;

        // Remove any states after current index
        this.states = this.states.slice(0, this.currentStateIndex + 1);

        // Add new state
        this.states.push(this.canvas.toDataURL());
        this.currentStateIndex++;

        // Limit history size
        if (this.states.length > 20) {
            this.states.shift();
            this.currentStateIndex--;
        }
    }

    undo() {
        if (this.currentStateIndex > 0) {
            this.currentStateIndex--;
            this.restoreState(this.states[this.currentStateIndex]);
        }
    }

    redo() {
        if (this.currentStateIndex < this.states.length - 1) {
            this.currentStateIndex++;
            this.restoreState(this.states[this.currentStateIndex]);
        }
    }

    restoreState(dataURL) {
        const img = new Image();
        img.onload = () => {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.ctx.drawImage(img, 0, 0);
            this.drawGrid();
        };
        img.src = dataURL;
    }

    // Cleanup
    destroy() {
        // Remove event listeners
        this.canvas.removeEventListener("mousedown", this.startDrawing);
        this.canvas.removeEventListener("mousemove", this.draw);
        this.canvas.removeEventListener("mouseup", this.stopDrawing);
        this.canvas.removeEventListener("mouseout", this.stopDrawing);

        document.removeEventListener("mousemove", this.drag);
        document.removeEventListener("mouseup", this.stopDrag);

        // Clear event listeners
        this.eventListeners = {};
    }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    // Template selection handler
    document
        .getElementById("templateSelect")
        ?.addEventListener("change", function () {
            if (window.signatureCanvas) {
                window.signatureCanvas.applyTemplate(this.value);
            }
        });

    // Grid toggle handler
    document
        .getElementById("showGridLines")
        ?.addEventListener("change", function () {
            if (window.signatureCanvas) {
                window.signatureCanvas.drawGrid();
            }
        });

    // Color picker handler
    document
        .getElementById("brushColor")
        ?.addEventListener("change", function () {
            if (window.signatureCanvas) {
                window.signatureCanvas.setBrushColor(this.value);
            }
        });

    // Preview button handler
    document
        .getElementById("previewBtn")
        ?.addEventListener("click", function () {
            if (window.signatureCanvas) {
                const preview = window.open(
                    "",
                    "_blank",
                    "width=800,height=600"
                );
                preview.document.write(`
                <html>
                    <head><title>Signature Preview</title></head>
                    <body style="margin:0;background:#f0f0f0;display:flex;justify-content:center;align-items:center;min-height:100vh;">
                        <img src="${window.signatureCanvas.canvas.toDataURL()}" style="max-width:100%;max-height:100%;border:1px solid #ccc;">
                    </body>
                </html>
            `);
            }
        });
});

// Export for global access
window.SignatureCanvas = SignatureCanvas;
