{{-- resources/views/digital-signature/admin/approval-requests/partials/approve-modal.blade.php --}}

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    Approve Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Hidden Input for Request ID -->
                <input type="hidden" id="approveRequestId" value="">

                <!-- Confirmation Message -->
                <div class="alert alert-success border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Confirm Approval</h6>
                            <p class="mb-0 small">You are about to approve this document request</p>
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
                        <p class="card-text fw-bold" id="approveDocumentName"></p>
                    </div>
                </div>

                <!-- Approval Notes (Optional) -->
                <div class="mb-3">
                    <label for="approval_notes" class="form-label">
                        <i class="fas fa-sticky-note me-1"></i>
                        Approval Notes <span class="text-muted">(Optional)</span>
                    </label>
                    <textarea class="form-control"
                              id="approval_notes"
                              name="notes"
                              rows="3"
                              placeholder="Add any notes or comments about this approval..."></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        These notes will be visible to the student and included in the approval notification.
                    </div>
                </div>

                <!-- Approval Confirmation Checklist -->
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">
                            <i class="fas fa-tasks me-1"></i>
                            Please confirm:
                        </h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="confirmDocumentReviewed">
                            <label class="form-check-label" for="confirmDocumentReviewed">
                                I have reviewed the document content
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="confirmMeetsRequirements">
                            <label class="form-check-label" for="confirmMeetsRequirements">
                                Document meets all requirements
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmAuthorized">
                            <label class="form-check-label" for="confirmAuthorized">
                                I am authorized to approve this request
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Warning Message -->
                <div class="alert alert-warning border-0 mt-3 mb-0" role="alert">
                    <small>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Note:</strong> Once approved, the student will be able to digitally sign this document.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button"
                        class="btn btn-success"
                        id="confirmApproveBtn"
                        onclick="performApprove()">
                    <i class="fas fa-check me-1"></i> Approve Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Validate checklist before allowing approval
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = ['confirmDocumentReviewed', 'confirmMeetsRequirements', 'confirmAuthorized'];
    const approveBtn = document.getElementById('confirmApproveBtn');

    function updateApproveButtonState() {
        if (!approveBtn) return;

        const allChecked = checkboxes.every(id => {
            const checkbox = document.getElementById(id);
            return checkbox && checkbox.checked;
        });

        if (allChecked) {
            approveBtn.disabled = false;
            approveBtn.classList.remove('disabled');
        } else {
            approveBtn.disabled = true;
            approveBtn.classList.add('disabled');
        }
    }

    // Add event listeners to checkboxes
    checkboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox) {
            checkbox.addEventListener('change', updateApproveButtonState);
        }
    });

    // Reset checkboxes when modal is hidden
    const approveModal = document.getElementById('approveModal');
    if (approveModal) {
        approveModal.addEventListener('hidden.bs.modal', function() {
            // Uncheck all checkboxes
            checkboxes.forEach(id => {
                const checkbox = document.getElementById(id);
                if (checkbox) {
                    checkbox.checked = false;
                }
            });

            // Clear notes
            const notesTextarea = document.getElementById('approval_notes');
            if (notesTextarea) {
                notesTextarea.value = '';
            }

            // Update button state
            updateApproveButtonState();
        });

        // Initialize button state when modal is shown
        approveModal.addEventListener('shown.bs.modal', function() {
            updateApproveButtonState();
        });
    }
});
</script>

<style>
/* Disabled button style */
#confirmApproveBtn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Checkbox focus style */
.form-check-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
}

/* Checked checkbox style */
.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

/* Animation for modal */
#approveModal .modal-dialog {
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
</style>
