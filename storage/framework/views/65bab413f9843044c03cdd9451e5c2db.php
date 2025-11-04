<?php $__env->startSection('title', 'Signature Verification Tools'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('digital-signature.admin.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-shield-alt me-3"></i>
                    Signature Verification Tools
                </h1>
                <p class="mb-0 opacity-75">Verify and validate digital signatures</p>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-info"><?php echo e($verificationStats['total_signatures'] ?? 0); ?></div>
                <div class="text-muted">Total Signatures</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success"><?php echo e($verificationStats['verified_signatures'] ?? 0); ?></div>
                <div class="text-muted">Verified</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-primary"><?php echo e($verificationStats['verification_rate'] ?? 0); ?>%</div>
                <div class="text-muted">Success Rate</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-warning"><?php echo e($verificationStats['period_days'] ?? 30); ?></div>
                <div class="text-muted">Days Period</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Manual Verification -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-search me-2"></i>
                        Manual Verification
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.signature.manual.verify')); ?>" method="POST" id="manualVerifyForm">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="document_signature_id" class="form-label">Document Signature ID *</label>
                            <input type="number" class="form-control" id="document_signature_id"
                                   name="document_signature_id" required
                                   placeholder="Enter document signature ID">
                            <small class="text-muted">Enter the ID of the document signature to verify</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i> Verify Signature
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <h6>Quick Verification Methods:</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-info btn-sm" onclick="showQRScanner()">
                            <i class="fas fa-qrcode me-1"></i> Scan QR Code
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="showTokenInput()">
                            <i class="fas fa-key me-1"></i> Verify by Token
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Verification -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        Batch Verification
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Upload a file containing multiple signature IDs for batch verification</p>

                    <form id="batchVerifyForm">
                        <div class="mb-3">
                            <label for="batch_file" class="form-label">Upload File (CSV/TXT)</label>
                            <input type="file" class="form-control" id="batch_file" accept=".csv,.txt">
                            <small class="text-muted">One signature ID per line</small>
                        </div>

                        <div class="mb-3">
                            <label for="batch_ids" class="form-label">Or enter IDs manually:</label>
                            <textarea class="form-control" id="batch_ids" rows="5"
                                      placeholder="Enter signature IDs (one per line)"></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="button" class="btn btn-success" onclick="processBatchVerification()">
                                <i class="fas fa-check-double me-1"></i> Batch Verify
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Verifications -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>
                Recent Verifications
            </h5>
        </div>
        <div class="card-body">
            <?php if($recentVerifications && $recentVerifications->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Signer</th>
                                <th>Verified At</th>
                                <th>Verified By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $recentVerifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $verification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($verification->approvalRequest->document_name); ?></strong><br>
                                    <small class="text-muted"><?php echo e($verification->approvalRequest->full_document_number); ?></small>
                                </td>
                                <td><?php echo e($verification->signer->name ?? 'Unknown'); ?></td>
                                <td>
                                    <?php echo e($verification->verified_at->format('d M Y H:i')); ?>

                                    <br><small class="text-muted"><?php echo e($verification->verified_at->diffForHumans()); ?></small>
                                </td>
                                <td><?php echo e($verification->verifier->name ?? 'System'); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo e(strtolower($verification->signature_status)); ?>">
                                        <?php echo e(ucfirst($verification->signature_status)); ?>

                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('admin.signature.documents.show', $verification->id)); ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Recent Verifications</h5>
                    <p class="text-muted">Verified signatures will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan QR Code for Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-result" class="mt-3" style="display: none;">
                    <div class="alert alert-success">
                        <strong>QR Code Detected:</strong>
                        <p id="qr-data" class="mb-0"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="verifyQRBtn" style="display: none;">
                    Verify Signature
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Token Input Modal -->
<div class="modal fade" id="tokenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify by Token</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="verification_token" class="form-label">Verification Token</label>
                    <input type="text" class="form-control" id="verification_token"
                           placeholder="Enter verification token">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="verifyByToken()">
                    <i class="fas fa-check me-1"></i> Verify
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Results Modal -->
<div class="modal fade" id="batchResultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batch Verification Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="batch-results"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportBatchResults()">
                    <i class="fas fa-download me-1"></i> Export Results
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.4/minified/html5-qrcode.min.js"></script>
<script>
let html5QrCode;

function showQRScanner() {
    const modal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
    modal.show();

    html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            document.getElementById('qr-data').textContent = decodedText;
            document.getElementById('qr-result').style.display = 'block';
            document.getElementById('verifyQRBtn').style.display = 'block';
            html5QrCode.stop();
        }
    );
}

function showTokenInput() {
    new bootstrap.Modal(document.getElementById('tokenModal')).show();
}

function verifyByToken() {
    const token = document.getElementById('verification_token').value;
    if (token) {
        window.location.href = `/signature/verify/${token}`;
    }
}

function processBatchVerification() {
    const ids = document.getElementById('batch_ids').value.split('\n')
        .map(id => id.trim())
        .filter(id => id.length > 0);

    if (ids.length === 0) {
        alert('Please enter at least one signature ID');
        return;
    }

    fetch('<?php echo e(route("admin.signature.documents.batch.verify")); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ signature_ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        showBatchResults(data);
    });
}

function showBatchResults(data) {
    const resultsHtml = `
        <div class="alert alert-info">
            <strong>Summary:</strong> ${data.summary.verified} verified, ${data.summary.failed} failed out of ${data.summary.total}
        </div>
        <div class="table

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/verification-tools.blade.php ENDPATH**/ ?>