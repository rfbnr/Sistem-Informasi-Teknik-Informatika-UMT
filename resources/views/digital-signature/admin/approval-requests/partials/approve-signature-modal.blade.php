{{-- resources/views/digital-signature/admin/approval-requests/partials/approve-signature-modal.blade.php --}}

<!-- Approve Signature Modal -->
{{-- <div class="modal fade" id="approveSignatureModal" tabindex="-1" aria-labelledby="approveSignatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="approveSignatureModalLabel">
                    <i class="fas fa-stamp me-2"></i>
                    Approve Digital Signature
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Hidden Input for Request ID -->
                <input type="hidden" id="approveSignatureRequestId" value="">

                <!-- Information Alert -->
                <div class="alert alert-info border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Signature Verification & Approval</h6>
                            <p class="mb-0 small">Review the signed document and verify the digital signature before final approval</p>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">
                            <i class="fas fa-file-contract me-1"></i>
                            Document Name:
                        </h6>
                        <p class="card-text fw-bold" id="approveSignatureDocumentName"></p>
                    </div>
                </div>

                <!-- Verification Status -->
                <div class="card border-primary mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-shield-alt me-1"></i>
                            Signature Verification Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Signature Valid</h6>
                                        <small class="text-muted">Digital signature is cryptographically valid</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-fingerprint fa-2x text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Document Integrity</h6>
                                        <small class="text-muted">Document has not been modified</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-key fa-2x text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Algorithm</h6>
                                        <small class="text-muted">RSA-SHA256 (2048 bit)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-clock fa-2x text-secondary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Signed At</h6>
                                        <small class="text-muted" id="signedAtTime">Just now</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Details -->
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                The digital signature has been verified and meets all security requirements.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Approval Notes -->
                <div class="mb-3">
                    <label for="approve_signature_notes" class="form-label">
                        <i class="fas fa-comment-dots me-1"></i>
                        Approval Notes <span class="text-muted">(Optional)</span>
                    </label>
                    <textarea class="form-control"
                              id="approve_signature_notes"
                              name="approval_notes"
                              rows="3"
                              placeholder="Add any final notes or comments about this signature approval..."></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        These notes will be recorded in the audit log and included in the completion notification.
                    </div>
                </div>

                <!-- Final Verification Checklist -->
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-3 text-muted">
                            <i class="fas fa-tasks me-1"></i>
                            Final Verification Checklist:
                        </h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="verifySignatureValid">
                            <label class="form-check-label" for="verifySignatureValid">
                                I confirm the digital signature is valid and authentic
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="verifyDocumentIntact">
                            <label class="form-check-label" for="verifyDocumentIntact">
                                I confirm the document content is correct and intact
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="verifyAuthorizedFinal">
                            <label class="form-check-label" for="verifyAuthorizedFinal">
                                I am authorized to provide final approval for this signature
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="alert alert-success border-0 mb-0" role="alert">
                    <small>
                        <i class="fas fa-check-circle me-1"></i>
                        <strong>Final Step:</strong> Once approved, the document will be marked as complete and the student will receive the final signed document with QR code verification.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button"
                        class="btn btn-primary"
                        id="confirmApproveSignatureBtn"
                        onclick="performApproveSignature()">
                    <i class="fas fa-stamp me-1"></i> Approve Signature
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Validate signature approval checklist
document.addEventListener('DOMContentLoaded', function() {
    const signatureCheckboxes = ['verifySignatureValid', 'verifyDocumentIntact', 'verifyAuthorizedFinal'];
    const approveSignatureBtn = document.getElementById('confirmApproveSignatureBtn');

    function updateApproveSignatureButtonState() {
        if (!approveSignatureBtn) return;

        const allChecked = signatureCheckboxes.every(id => {
            const checkbox = document.getElementById(id);
            return checkbox && checkbox.checked;
        });

        if (allChecked) {
            approveSignatureBtn.disabled = false;
            approveSignatureBtn.classList.remove('disabled');
        } else {
            approveSignatureBtn.disabled = true;
            approveSignatureBtn.classList.add('disabled');
        }
    }

    // Add event listeners to checkboxes
    signatureCheckboxes.forEach(id => {
        const checkbox = document.getElementById(id);
        if (checkbox) {
            checkbox.addEventListener('change', updateApproveSignatureButtonState);
        }
    });

    // Reset modal when hidden
    const approveSignatureModal = document.getElementById('approveSignatureModal');
    if (approveSignatureModal) {
        approveSignatureModal.addEventListener('hidden.bs.modal', function() {
            // Uncheck all checkboxes
            signatureCheckboxes.forEach(id => {
                const checkbox = document.getElementById(id);
                if (checkbox) {
                    checkbox.checked = false;
                }
            });

            // Clear notes
            const notesTextarea = document.getElementById('approve_signature_notes');
            if (notesTextarea) {
                notesTextarea.value = '';
            }

            // Update button state
            updateApproveSignatureButtonState();
        });

        // Initialize button state when modal is shown
        approveSignatureModal.addEventListener('shown.bs.modal', function() {
            updateApproveSignatureButtonState();
        });
    }
});
</script>

<style>
/* Disabled button style */
#confirmApproveSignatureBtn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Verification card icons */
.card-body .fa-check-circle {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Checkbox styles */
.form-check-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

/* Verification status icons animation */
.fa-fingerprint,
.fa-key,
.fa-clock {
    transition: transform 0.3s ease;
}

.d-flex:hover .fa-fingerprint,
.d-flex:hover .fa-key,
.d-flex:hover .fa-clock {
    transform: scale(1.1);
}

/* Modal animation */
#approveSignatureModal .modal-dialog {
    animation: fadeInScale 0.3s ease-out;
}

@keyframes fadeInScale {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* Card hover effect */
.card.border-primary {
    transition: box-shadow 0.3s ease;
}

.card.border-primary:hover {
    box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.15);
}
</style> --}}
