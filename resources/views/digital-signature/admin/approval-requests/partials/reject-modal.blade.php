{{-- resources/views/digital-signature/admin/approval-requests/partials/reject-modal.blade.php --}}

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2"></i>
                    Reject Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Hidden Input for Request ID -->
                <input type="hidden" id="rejectRequestId" value="">

                <!-- Warning Message -->
                <div class="alert alert-danger border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Confirm Rejection</h6>
                            <p class="mb-0 small">You are about to reject this document request</p>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-file-alt me-1"></i>
                            Document Name:
                        </h6>
                        <p class="card-text fw-bold" id="rejectDocumentName"></p>
                    </div>
                </div>

                <!-- Rejection Reason (Required) -->
                <div class="mb-3">
                    <label for="rejection_reason" class="form-label">
                        <i class="fas fa-comment-dots me-1"></i>
                        Rejection Reason <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control"
                              id="rejection_reason"
                              name="rejection_reason"
                              rows="4"
                              maxlength="500"
                              placeholder="Please provide a clear and constructive reason for rejecting this request..."
                              required></textarea>
                    <div class="form-text">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-info-circle me-1"></i>
                                This reason will be sent to the student via email
                            </span>
                            <span id="charCount" class="text-muted">
                                <span id="currentChars">0</span> / 500 characters
                            </span>
                        </div>
                    </div>
                    <div class="invalid-feedback" id="rejectionReasonError">
                        Please provide a rejection reason (minimum 10 characters).
                    </div>
                </div>

                <!-- Quick Rejection Reasons (Templates) -->
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-list-ul me-1"></i>
                            Quick Rejection Reasons:
                        </h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectionReason('Document format is incorrect. Please submit in PDF format.')">
                                <i class="fas fa-file-pdf me-1"></i>
                                Incorrect document format
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectionReason('Required information is missing or incomplete in the document.')">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Incomplete information
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectionReason('Document does not meet the institutional requirements. Please review the guidelines and resubmit.')">
                                <i class="fas fa-clipboard-check me-1"></i>
                                Does not meet requirements
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectionReason('Document quality is poor or illegible. Please submit a clearer version.')">
                                <i class="fas fa-image me-1"></i>
                                Poor document quality
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="alert alert-warning border-0 mb-0" role="alert">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Important:</strong> The student will receive an email notification with your rejection reason and can resubmit the corrected document.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i> Cancel
                </button>
                <button type="button"
                        class="btn btn-danger"
                        id="confirmRejectBtn"
                        onclick="performReject()">
                    <i class="fas fa-times me-1"></i> Reject Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Set rejection reason from template
function setRejectionReason(reason) {
    const textarea = document.getElementById('rejection_reason');
    if (textarea) {
        textarea.value = reason;
        updateCharCount();
        validateRejectionReason();
    }
}

// Update character count
function updateCharCount() {
    const textarea = document.getElementById('rejection_reason');
    const currentCharsSpan = document.getElementById('currentChars');

    if (textarea && currentCharsSpan) {
        const length = textarea.value.length;
        currentCharsSpan.textContent = length;

        // Change color based on character count
        const charCount = document.getElementById('charCount');
        if (length > 450) {
            charCount.classList.add('text-danger');
            charCount.classList.remove('text-muted');
        } else if (length > 400) {
            charCount.classList.add('text-warning');
            charCount.classList.remove('text-muted', 'text-danger');
        } else {
            charCount.classList.remove('text-warning', 'text-danger');
            charCount.classList.add('text-muted');
        }
    }
}

// Validate rejection reason
function validateRejectionReason() {
    const textarea = document.getElementById('rejection_reason');
    const rejectBtn = document.getElementById('confirmRejectBtn');
    const errorDiv = document.getElementById('rejectionReasonError');

    if (!textarea || !rejectBtn) return;

    const reason = textarea.value.trim();
    const isValid = reason.length >= 10 && reason.length <= 500;

    if (isValid) {
        textarea.classList.remove('is-invalid');
        textarea.classList.add('is-valid');
        rejectBtn.disabled = false;
        rejectBtn.classList.remove('disabled');
        if (errorDiv) errorDiv.style.display = 'none';
    } else {
        textarea.classList.remove('is-valid');
        if (reason.length > 0) {
            textarea.classList.add('is-invalid');
            if (errorDiv) errorDiv.style.display = 'block';
        }
        rejectBtn.disabled = true;
        rejectBtn.classList.add('disabled');
    }

    return isValid;
}

// Initialize rejection modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('rejection_reason');

    if (textarea) {
        // Update character count on input
        textarea.addEventListener('input', function() {
            updateCharCount();
            validateRejectionReason();
        });

        // Validate on blur
        textarea.addEventListener('blur', validateRejectionReason);
    }

    // Reset modal when hidden
    const rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('hidden.bs.modal', function() {
            // Clear textarea
            if (textarea) {
                textarea.value = '';
                textarea.classList.remove('is-valid', 'is-invalid');
            }

            // Reset character count
            const currentCharsSpan = document.getElementById('currentChars');
            if (currentCharsSpan) {
                currentCharsSpan.textContent = '0';
            }

            const charCount = document.getElementById('charCount');
            if (charCount) {
                charCount.classList.remove('text-warning', 'text-danger');
                charCount.classList.add('text-muted');
            }

            // Reset button state
            const rejectBtn = document.getElementById('confirmRejectBtn');
            if (rejectBtn) {
                rejectBtn.disabled = true;
                rejectBtn.classList.add('disabled');
            }

            // Hide error message
            const errorDiv = document.getElementById('rejectionReasonError');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        });

        // Initialize when modal is shown
        rejectModal.addEventListener('shown.bs.modal', function() {
            // Focus on textarea
            if (textarea) {
                textarea.focus();
            }
        });
    }
});
</script>

<style>
/* Validation styles */
.is-valid {
    border-color: #28a745 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.is-invalid {
    border-color: #dc3545 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

/* Disabled button style */
#confirmRejectBtn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Quick reason buttons hover effect */
.btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
    transform: translateX(5px);
    transition: all 0.2s ease;
}

/* Animation for modal */
#rejectModal .modal-dialog {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Character count styles */
#charCount.text-warning {
    font-weight: bold;
}

#charCount.text-danger {
    font-weight: bold;
    animation: pulse 1s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.6;
    }
}
</style>
