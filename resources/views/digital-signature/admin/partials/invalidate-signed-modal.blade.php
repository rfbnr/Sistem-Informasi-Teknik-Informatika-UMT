<!-- Invalidate Modal -->
<div class="modal fade" id="invalidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Invalidate Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            {{-- <form action="{{ route('admin.signature.documents.invalidate', $documentSignature->id) }}" method="POST">
                @csrf --}}
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will mark the signature as invalid and cannot be undone.
                    </div>
                    <div class="mb-3">
                        <label for="invalidateReason" class="form-label">Reason *</label>
                        <textarea class="form-control" id="invalidateReason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    {{-- <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Invalidate
                    </button> --}}
                    <button type="button" id="confirmInvalidateBtn" class="btn btn-danger" onclick="performInvalidate()">
                        <i class="fas fa-ban me-1"></i> Invalidate
                    </button>
                </div>
            {{-- </form> --}}
        </div>
    </div>
</div>

<script>
    // Perform Invalidate
    function performInvalidate() {
        const requestId = {{ $documentSignature->id ?? 0 }};
        const reason = document.getElementById('invalidateReason').value;

        // add loading state to button
        const invalidateBtn = document.getElementById('confirmInvalidateBtn');
        const originalBtnInvalidateHtml = invalidateBtn.innerHTML;

        invalidateBtn.disabled = true;
        invalidateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Invalidating...';

        fetch(`/admin/signature/documents/${requestId}/invalidate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success || !data.error) {
                showAlert('success', 'Signature invalidated successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message || 'Failed to invalidate signature');
                invalidateBtn.disabled = false;
                invalidateBtn.innerHTML = originalBtnInvalidateHtml;
            }
            bootstrap.Modal.getInstance(document.getElementById('invalidateModal')).hide();
        })
        .catch(error => {
            showAlert('danger', 'An error occurred while invalidating the signature');
            console.error('Error:', error);
            invalidateBtn.disabled = false;
            invalidateBtn.innerHTML = originalBtnInvalidateHtml;
        });
    }

    // Show Alert Helper
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        const container = document.querySelector('.main-content');
        const firstChild = container.firstElementChild;
        firstChild.insertAdjacentHTML('afterend', alertHtml);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            });
        }, 5000);
    }
</script>
