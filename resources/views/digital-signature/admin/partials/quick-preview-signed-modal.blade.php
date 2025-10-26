<!-- Quick Preview Modal -->
<div class="modal fade" id="quickPreviewModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Preview & Verification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickPreviewContent">
                <!-- Loading State -->
                <div class="text-center py-5" id="quickPreviewLoading">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                    <p class="text-muted">Loading document and running verification checks...</p>
                </div>

                <!-- Content will be loaded here -->
                <div id="quickPreviewData" style="display: none;">
                    <!-- Verification Status Alert -->
                    <div id="verificationAlert"></div>

                    <div class="row">
                        <!-- Left Column: Document Info & PDF Preview -->
                        <div class="col-lg-8">
                            <!-- Document Information Card -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Document Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="documentInfo">
                                        <!-- Will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <!-- PDF Preview -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-file-pdf me-2"></i>Document Preview</h6>
                                </div>
                                <div class="card-body p-0">
                                    <iframe id="pdfPreviewFrame" style="width: 100%; height: 600px; border: none;"></iframe>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Verification Checks & Actions -->
                        <div class="col-lg-4">
                            <!-- Verification Summary Card -->
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Verification Summary</h6>
                                </div>
                                <div class="card-body" id="verificationSummary">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                            </div>

                            <!-- Verification Checks List -->
                            <div class="card mb-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-list-check me-2"></i>Security Checks (7)</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush" id="verificationChecks">
                                        <!-- Will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <!-- Signature Info Card -->
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-signature me-2"></i>Signature Details</h6>
                                </div>
                                <div class="card-body small" id="signatureInfo">
                                    <!-- Will be populated via JavaScript -->
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2" id="quickActions">
                                        <!-- Will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewDetailBtn" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i> View Full Details
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Preview Document Function
function quickPreviewDocument(id) {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('quickPreviewModal'));
    modal.show();

    // Show loading, hide content
    document.getElementById('quickPreviewLoading').style.display = 'block';
    document.getElementById('quickPreviewData').style.display = 'none';

    // Fetch document data with verification
    fetch(`/admin/signature/documents/${id}/quick-preview`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(response => {
        if (!response.success) {
            throw new Error(response.message || 'Failed to load preview');
        }

        const data = response.data;

        console.log('Quick Preview Data:', data);

        // Hide loading, show content
        document.getElementById('quickPreviewLoading').style.display = 'none';
        document.getElementById('quickPreviewData').style.display = 'block';

        // Populate Verification Alert
        const alertDiv = document.getElementById('verificationAlert');
        if (data.verification.is_valid) {
            alertDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>✓ Signature Valid:</strong> ${data.verification.message}
                </div>
            `;
        } else {
            alertDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>✗ Verification Failed:</strong> ${data.verification.message}
                </div>
            `;
        }

        // Populate Document Info
        const docInfo = document.getElementById('documentInfo');
        docInfo.innerHTML = `
            <div class="col-md-6 mb-2">
                <strong>Document Name:</strong><br>
                <span class="text-muted">${data.document.name}</span>
            </div>
            <div class="col-md-6 mb-2">
                <strong>Document Number:</strong><br>
                <span class="text-muted">${data.document.number}</span>
            </div>
            <div class="col-md-6 mb-2">
                <strong>Submitted By:</strong><br>
                <span class="text-muted">${data.document.submitted_by}</span>
            </div>
            <div class="col-md-6 mb-2">
                <strong>Submitted At:</strong><br>
                <span class="text-muted">${data.document.submitted_at}</span>
            </div>
            ${data.document.notes ? `
                <div class="col-12 mb-2">
                    <strong>Notes:</strong><br>
                    <span class="text-muted">${data.document.notes}</span>
                </div>
            ` : ''}
        `;

        // Load PDF Preview
        document.getElementById('pdfPreviewFrame').src = data.urls.view;

        // Populate Verification Summary
        const summary = data.verification.summary;
        const summaryDiv = document.getElementById('verificationSummary');
        const successRate = summary.success_rate || 0;
        const statusClass = data.verification.is_valid ? 'success' : 'danger';

        summaryDiv.innerHTML = `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">
                        <span class="badge bg-${statusClass}">
                            ${summary.overall_status || 'UNKNOWN'}
                        </span>
                    </h5>
                    <span class="text-muted">${successRate}% passed</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-${statusClass}" style="width: ${successRate}%" role="progressbar"></div>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-6">
                    <div class="h4 text-success mb-0">${summary.checks_passed || 0}</div>
                    <small class="text-muted">Passed</small>
                </div>
                <div class="col-6">
                    <div class="h4 text-danger mb-0">${summary.checks_failed || 0}</div>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        `;

        // Populate Verification Checks
        const checksDiv = document.getElementById('verificationChecks');
        let checksHtml = '';

        for (const [checkName, check] of Object.entries(data.verification.checks)) {
            const iconClass = check.status ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
            const checkTitle = checkName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            checksHtml += `
                <div class="list-group-item">
                    <div class="d-flex align-items-start">
                        <div class="me-3 mt-1">
                            <i class="fas ${iconClass}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">${checkTitle}</div>
                            <small class="text-muted">${check.message}</small>
                        </div>
                    </div>
                </div>
            `;
        }
        checksDiv.innerHTML = checksHtml;

        // Populate Signature Info
        const sigInfo = document.getElementById('signatureInfo');
        sigInfo.innerHTML = `
            <div class="mb-2">
                <strong>Status:</strong>
                <span class="badge bg-${data.signature.status === 'verified' ? 'success' : (data.signature.status === 'signed' ? 'info' : 'warning')} ms-1">
                    ${data.signature.status.toUpperCase()}
                </span>
            </div>
            <div class="mb-2">
                <strong>Signed By:</strong><br>
                ${data.signature.signed_by}
            </div>
            <div class="mb-2">
                <strong>Signed At:</strong><br>
                ${data.signature.signed_at || 'N/A'}
                ${data.signature.signed_at_human ? `<br><small class="text-muted">${data.signature.signed_at_human}</small>` : ''}
            </div>
            <div class="mb-2">
                <strong>Algorithm:</strong>
                <span class="badge bg-info ms-1">${data.signature.algorithm}</span>
            </div>
            <div class="mb-2">
                <strong>Key Length:</strong>
                <span class="badge bg-success ms-1">${data.signature.key_length} bits</span>
            </div>
            <div class="mb-2">
                <strong>Hash:</strong><br>
                <code class="small">${data.signature.document_hash}</code>
            </div>
            <div class="mb-0">
                <strong>PDF Status:</strong>
                ${data.signature.has_signed_pdf ?
                    '<span class="badge bg-success ms-1"><i class="fas fa-check-circle me-1"></i>Signed PDF</span>' :
                    '<span class="badge bg-secondary ms-1"><i class="fas fa-file me-1"></i>Original</span>'
                }
            </div>
        `;

        // Populate Quick Actions
        const actionsDiv = document.getElementById('quickActions');
        let actionsHtml = '';

        // Verify & Reject buttons (only for 'signed' status)
        if (data.signature.status === 'signed') {
            // Verify button (only if valid)
            if (data.verification.is_valid) {
                actionsHtml += `
                    <button class="btn btn-success" onclick="quickVerifyFromModal(${data.document.id}, '${data.urls.verify}')">
                        <i class="fas fa-check-circle me-2"></i> Verify Signature Now
                    </button>
                `;
            }

            // Reject button (always show for signed status)
            actionsHtml += `
                <button class="btn btn-danger" onclick="quickRejectFromModal(${data.document.id})">
                    <i class="fas fa-times me-2"></i> Reject Signature
                </button>
            `;
        }

        // Download button
        actionsHtml += `
            <a href="${data.urls.download}" class="btn btn-info">
                <i class="fas fa-download me-2"></i> Download Document
            </a>
        `;

        // View detail button (already in footer, but can add here too)
        actionsHtml += `
            <a href="${data.urls.detail}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i> Open Full Detail Page
            </a>
        `;

        actionsDiv.innerHTML = actionsHtml;

        // Update footer detail link
        document.getElementById('viewDetailBtn').href = data.urls.detail;

    })
    .catch(error => {
        console.error('Quick preview error:', error);
        document.getElementById('quickPreviewLoading').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> ${error.message}
            </div>
        `;
    });
}

// Quick Verify from Modal
function quickVerifyFromModal(id, verifyUrl) {
    if (!confirm('Verify this signature now?')) {
        return;
    }

    // Get button
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verifying...';

    fetch(verifyUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ Signature verified successfully!');
            // Close modal and reload page
            bootstrap.Modal.getInstance(document.getElementById('quickPreviewModal')).hide();
            location.reload();
        } else {
            alert('❌ Verification failed: ' + (data.message || 'Unknown error'));
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    })
    .catch(error => {
        console.error('Verification error:', error);
        alert('❌ Network error: Failed to verify signature. Please try again.');
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
}

// Quick Reject from Modal
function quickRejectFromModal(id) {
    // Close quick preview modal
    bootstrap.Modal.getInstance(document.getElementById('quickPreviewModal')).hide();

    // Small delay then open reject modal
    setTimeout(() => {
        rejectSignature(id);
    }, 300);
}
</script>