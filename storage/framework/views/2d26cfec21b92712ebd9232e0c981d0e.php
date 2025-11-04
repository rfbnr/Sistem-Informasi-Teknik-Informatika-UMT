

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="logDetailsModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    Log Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="logDetailsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading log details...</p>
                </div>

                <!-- Content Container -->
                <div id="logDetailsContent" style="display: none;">
                    <!-- Basic Information -->
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-file-alt me-2 text-primary"></i>
                                Basic Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Log ID</label>
                                    <div class="fw-bold" id="detail-log-id">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Action/Event</label>
                                    <div class="fw-bold" id="detail-action">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Timestamp</label>
                                    <div id="detail-timestamp">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Status</label>
                                    <div id="detail-status">-</div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="small text-muted mb-1">Description</label>
                                    <div id="detail-description">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user me-2 text-success"></i>
                                User Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">User</label>
                                    <div id="detail-user">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">User ID</label>
                                    <div id="detail-user-id">-</div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="small text-muted mb-1">IP Address</label>
                                    <div id="detail-ip">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Device Information -->
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-mobile-alt me-2 text-info"></i>
                                Device Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted mb-1">Device Type</label>
                                    <div id="detail-device">-</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted mb-1">Browser</label>
                                    <div id="detail-browser">-</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small text-muted mb-1">Platform</label>
                                    <div id="detail-platform">-</div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="small text-muted mb-1">User Agent</label>
                                    <div class="small font-monospace text-muted" id="detail-user-agent">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="card mb-3 border-0 shadow-sm" id="performance-section" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2 text-warning"></i>
                                Performance Metrics
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Duration</label>
                                    <div id="detail-duration">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Session ID</label>
                                    <div class="font-monospace small" id="detail-session">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Records -->
                    <div class="card mb-3 border-0 shadow-sm" id="related-section" style="display: none;">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-link me-2 text-danger"></i>
                                Related Records
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Document Signature ID</label>
                                    <div id="detail-doc-sig-id">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small text-muted mb-1">Approval Request ID</label>
                                    <div id="detail-approval-id">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-database me-2 text-secondary"></i>
                                Additional Metadata
                            </h6>
                        </div>
                        <div class="card-body">
                            <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code id="detail-metadata" class="language-json">-</code></pre>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div id="logDetailsError" style="display: none;" class="text-center py-5">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 48px;"></i>
                    <h5 class="mt-3">Failed to Load Details</h5>
                    <p class="text-muted">An error occurred while loading the log details.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="copyLogDataBtn" onclick="copyLogData()">
                    <i class="fas fa-copy me-1"></i> Copy JSON
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentLogData = null;

function viewLogDetails(logId, type) {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    modal.show();

    // Show loading state
    document.getElementById('logDetailsLoading').style.display = 'block';
    document.getElementById('logDetailsContent').style.display = 'none';
    document.getElementById('logDetailsError').style.display = 'none';

    // Fetch log details
    fetch(`/admin/signature/logs/${logId}/details?type=${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentLogData = data;
                populateLogDetails(data.log, data.metadata, type);
                document.getElementById('logDetailsLoading').style.display = 'none';
                document.getElementById('logDetailsContent').style.display = 'block';
            } else {
                throw new Error('Failed to load log details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('logDetailsLoading').style.display = 'none';
            document.getElementById('logDetailsError').style.display = 'block';
        });
}

function populateLogDetails(log, metadata, type) {
    // Basic Information
    document.getElementById('detail-log-id').textContent = log.id;

    if (type === 'audit') {
        document.getElementById('detail-action').innerHTML = `
            <span class="badge bg-primary">${log.action}</span>
        `;
        document.getElementById('detail-status').innerHTML = log.is_success
            ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Success</span>'
            : '<span class="badge bg-danger"><i class="fas fa-times me-1"></i> Failed</span>';
        document.getElementById('detail-timestamp').textContent = formatDateTime(log.performed_at);
    } else {
        document.getElementById('detail-action').innerHTML = `
            <span class="badge bg-info">Verification Attempt</span>
        `;
        document.getElementById('detail-status').innerHTML = log.is_valid
            ? '<span class="badge bg-success"><i class="fas fa-shield-alt me-1"></i> Valid</span>'
            : '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Invalid</span>';
        document.getElementById('detail-timestamp').textContent = formatDateTime(log.verified_at);
    }

    document.getElementById('detail-description').textContent = log.description || '-';

    // User Information
    document.getElementById('detail-user').textContent = log.user ? log.user.name : 'Anonymous';
    document.getElementById('detail-user-id').textContent = log.user_id || 'N/A';
    document.getElementById('detail-ip').innerHTML = `
        <span class="badge bg-secondary font-monospace">${log.ip_address || 'Unknown'}</span>
    `;

    // Device Information
    const deviceType = metadata?.device_type || 'Unknown';
    const deviceIcon = deviceType === 'mobile' ? 'mobile-alt' : (deviceType === 'tablet' ? 'tablet-alt' : 'desktop');
    document.getElementById('detail-device').innerHTML = `
        <span class="badge bg-info">
            <i class="fas fa-${deviceIcon} me-1"></i> ${deviceType.charAt(0).toUpperCase() + deviceType.slice(1)}
        </span>
    `;
    document.getElementById('detail-browser').textContent = metadata?.browser || 'Unknown';
    document.getElementById('detail-platform').textContent = metadata?.platform || 'Unknown';
    document.getElementById('detail-user-agent').textContent = log.user_agent || 'Not available';

    // Performance Metrics
    const hasDuration = (type === 'audit' && log.duration_ms) || (type === 'verification' && metadata?.verification_duration_ms);
    if (hasDuration || metadata?.session_id) {
        document.getElementById('performance-section').style.display = 'block';
        const duration = type === 'audit' ? log.duration_ms : metadata?.verification_duration_ms;
        document.getElementById('detail-duration').textContent = duration ? `${duration}ms` : '-';
        document.getElementById('detail-session').textContent = metadata?.session_id || '-';
    }

    // Related Records
    if (log.document_signature_id || log.approval_request_id) {
        document.getElementById('related-section').style.display = 'block';
        document.getElementById('detail-doc-sig-id').textContent = log.document_signature_id || '-';
        document.getElementById('detail-approval-id').textContent = log.approval_request_id || '-';
    }

    // Metadata
    document.getElementById('detail-metadata').textContent = JSON.stringify(metadata, null, 2);
}

function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function copyLogData() {
    if (!currentLogData) return;

    const jsonData = JSON.stringify(currentLogData, null, 2);
    navigator.clipboard.writeText(jsonData).then(() => {
        // Change button text temporarily
        const btn = document.getElementById('copyLogDataBtn');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');

        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy data to clipboard');
    });
}
</script>

<style>
#logDetailsModal .modal-content {
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

#logDetailsModal .card {
    transition: transform 0.2s ease;
}

#logDetailsModal .card:hover {
    transform: translateY(-2px);
}

#detail-metadata {
    font-size: 12px;
    line-height: 1.5;
    color: #2c3e50;
}
</style>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/logs/partials/log-details-modal.blade.php ENDPATH**/ ?>