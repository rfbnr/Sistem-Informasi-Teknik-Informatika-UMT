

<!-- View Document Modal -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewDocumentModalLabel">
                    <i class="fas fa-file-pdf me-2"></i>
                    Document Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Document Viewer -->
                <div class="document-viewer" style="position: relative; height: 600px;">
                    <iframe id="documentIframe"
                            src=""
                            style="width:100%; height:100%; border:none;"
                            frameborder="0">
                    </iframe>

                    <!-- Fullscreen Toggle Button -->
                    <button type="button"
                            class="btn btn-primary btn-sm position-absolute top-0 end-0 m-3"
                            onclick="toggleFullscreen()"
                            title="Toggle Fullscreen">
                        <i class="fas fa-expand" id="fullscreenIcon"></i>
                    </button>
                </div>

                <!-- Document Loading Indicator -->
                <div id="documentLoadingIndicator" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading document...</p>
                </div>

                <!-- Document Error Message -->
                <div id="documentErrorMessage" class="text-center py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Unable to Load Document</h5>
                    <p class="text-muted">The document preview could not be loaded. Please try downloading the document instead.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a id="downloadDocumentBtn" href="#" class="btn btn-success" download>
                    <i class="fas fa-download me-1"></i> Download Document
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Fullscreen Styles */
    .modal-fullscreen-custom {
        width: 100vw;
        max-width: 100vw;
        height: 100vh;
        margin: 0;
    }

    .modal-fullscreen-custom .modal-content {
        height: 100vh;
        border: none;
        border-radius: 0;
    }

    .modal-fullscreen-custom .document-viewer {
        height: calc(100vh - 120px) !important;
    }

    /* Loading Animation */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    #documentLoadingIndicator {
        animation: pulse 1.5s ease-in-out infinite;
    }
</style>

<script>
// Toggle Fullscreen for Document Viewer
function toggleFullscreen() {
    const modal = document.getElementById('viewDocumentModal');
    const modalDialog = modal.querySelector('.modal-dialog');
    const icon = document.getElementById('fullscreenIcon');

    if (modalDialog.classList.contains('modal-fullscreen-custom')) {
        // Exit fullscreen
        modalDialog.classList.remove('modal-fullscreen-custom');
        modalDialog.classList.add('modal-xl');
        icon.classList.remove('fa-compress');
        icon.classList.add('fa-expand');
    } else {
        // Enter fullscreen
        modalDialog.classList.remove('modal-xl');
        modalDialog.classList.add('modal-fullscreen-custom');
        icon.classList.remove('fa-expand');
        icon.classList.add('fa-compress');
    }
}

// Handle iframe load events
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.getElementById('documentIframe');
    const loadingIndicator = document.getElementById('documentLoadingIndicator');
    const errorMessage = document.getElementById('documentErrorMessage');

    if (iframe) {
        // Show loading indicator when iframe starts loading
        iframe.addEventListener('loadstart', function() {
            loadingIndicator.style.display = 'block';
            errorMessage.style.display = 'none';
            iframe.style.display = 'none';
        });

        // Hide loading indicator when iframe finishes loading
        iframe.addEventListener('load', function() {
            loadingIndicator.style.display = 'none';
            iframe.style.display = 'block';
        });

        // Show error message if iframe fails to load
        iframe.addEventListener('error', function() {
            loadingIndicator.style.display = 'none';
            errorMessage.style.display = 'block';
            iframe.style.display = 'none';
        });
    }

    // Reset modal state when closed
    const viewDocumentModal = document.getElementById('viewDocumentModal');
    if (viewDocumentModal) {
        viewDocumentModal.addEventListener('hidden.bs.modal', function() {
            // Reset fullscreen state
            const modalDialog = viewDocumentModal.querySelector('.modal-dialog');
            const icon = document.getElementById('fullscreenIcon');

            if (modalDialog.classList.contains('modal-fullscreen-custom')) {
                modalDialog.classList.remove('modal-fullscreen-custom');
                modalDialog.classList.add('modal-xl');
                icon.classList.remove('fa-compress');
                icon.classList.add('fa-expand');
            }

            // Clear iframe src to stop loading
            iframe.src = '';

            // Reset visibility
            loadingIndicator.style.display = 'none';
            errorMessage.style.display = 'none';
            iframe.style.display = 'block';
        });
    }
});
</script>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/approval-requests/partials/view-document-modal.blade.php ENDPATH**/ ?>