<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Document Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Signature Rejection</strong><br>
                        Rejecting this signature will also reject the approval request. The user will need to re-sign the document with correct placement.
                    </div>
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason *</label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="4" required
                                  placeholder="Example: Signature placement is incorrect - too far to the left"></textarea>
                        <small class="text-muted">Please specify the issue (placement, size, quality, etc.)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Common Rejection Reasons:</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature placement is incorrect - positioned too far to the left')">
                                Placement too far left
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature placement is incorrect - positioned too far to the right')">
                                Placement too far right
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature size is too large and overlaps with document content')">
                                Signature too large
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature quality is poor - image appears distorted or pixelated')">
                                Poor signature quality
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature does not match the designated signature area')">
                                Not in designated area
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject Signature
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rejectSignature(id) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');

    // Set form action dynamically
    form.action = `/admin/signature/documents/${id}/reject`;

    // Clear previous input
    document.getElementById('reject_reason').value = '';

    // Show modal
    new bootstrap.Modal(modal).show();

    // Handle form submission
    form.onsubmit = function(e) {
        e.preventDefault();

        const reason = document.getElementById('reject_reason').value;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;

        console.log('Submitting rejection with reason:', reason);
        console.log('Form action URL:', form.action);
        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);
        console.log('Request Body:', JSON.stringify({ reason: reason }));

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Rejecting...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                bootstrap.Modal.getInstance(modal).hide();
                location.reload();
            } else {
                alert('❌ Rejection failed: ' + (data.message || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        })
        .catch(error => {
            console.error('Rejection error:', error);
            alert('❌ Network error: Failed to reject signature. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        });
    };
}

function setRejectReason(reason) {
    document.getElementById('reject_reason').value = reason;
}
</script>
<?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/partials/reject-signed-modal.blade.php ENDPATH**/ ?>