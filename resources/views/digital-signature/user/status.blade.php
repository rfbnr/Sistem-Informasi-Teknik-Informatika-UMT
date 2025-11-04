{{-- resources/views/digital-signature/user/status.blade.php --}}
{{-- @extends('digital-signature.layouts.app') --}}
@extends('user.layouts.app')

@section('title', 'My Document Status')

@push('styles')
<style>
.status-container {
    background: white;
    border-radius: 1rem;
    margin-top: 4rem;
    margin-right: 1rem;
    margin-left: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.status-header {
    /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem 1rem 0 0;
}

.document-card {
    border: none;
    border-radius: 1rem;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    overflow: hidden;
}

.document-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.document-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.document-body {
    padding: 1.5rem;
}

.status-timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 2rem;
    width: 2px;
    height: calc(100% - 1rem);
    background: #e9ecef;
}

.timeline-dot {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.timeline-dot.completed { background: #28a745; }
.timeline-dot.current { background: #007bff; animation: pulse 2s infinite; }
.timeline-dot.pending { background: #6c757d; }
.timeline-dot.rejected { background: #dc3545; }

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-approved { background: #d1ecf1; color: #0c5460; }
.status-user_signed { background: #e2e3ff; color: #4c4cdb; }
.status-sign_approved { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }

.priority-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.priority-low { background: #e3f2fd; color: #1976d2; }
.priority-normal { background: #f3e5f5; color: #7b1fa2; }
.priority-high { background: #fff3e0; color: #f57c00; }
.priority-urgent { background: #ffebee; color: #d32f2f; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.qr-code-display {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    margin-top: 1rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.document-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.document-type {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.meta-item {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: #6c757d;
}

.meta-item i {
    margin-right: 0.5rem;
    width: 16px;
}

@media (max-width: 768px) {
    .document-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .action-buttons {
        flex-direction: column;
    }

    .status-header {
        padding: 1rem;
    }
}
</style>
@endpush

@section('content')
<!-- Section Header -->
<section id="header-section">
    <h1>My Document Status</h1>
</section>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="status-container">
        <div class="status-header">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="mb-2">
                        <i class="fas fa-file-alt me-3"></i>
                        My Document Status
                    </h1>
                    <p class="mb-0 opacity-75">Track your document approval and signing progress</p>
                </div>
                <div class="col-lg-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('user.signature.approval.request') }}" class="btn btn-light">
                            <i class="fas fa-plus me-1"></i> New Request
                        </a>
                        <button class="btn btn-warning" onclick="refreshStatus()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="p-3 border-bottom">
            <div class="row text-center">
                @php
                    $stats = [
                        'total' => $approvalRequests->count(),
                        'pending' => $approvalRequests->where('status', 'pending')->count(),
                        'approved' => $approvalRequests->where('status', 'approved')->count(),
                        'completed' => $approvalRequests->whereIn('status', ['sign_approved', 'user_signed'])->count(),
                        'rejected' => $approvalRequests->where('status', 'rejected')->count()
                    ];
                @endphp
                <div class="col-md-2">
                    <div class="h4 text-primary">{{ $stats['total'] }}</div>
                    <small class="text-muted">Total</small>
                </div>
                <div class="col-md-2">
                    <div class="h4 text-warning">{{ $stats['pending'] }}</div>
                    <small class="text-muted">Pending</small>
                </div>
                <div class="col-md-3">
                    <div class="h4 text-info">{{ $stats['approved'] }}</div>
                    <small class="text-muted">Approved</small>
                </div>
                <div class="col-md-3">
                    <div class="h4 text-success">{{ $stats['completed'] }}</div>
                    <small class="text-muted">Completed</small>
                </div>
                <div class="col-md-2">
                    <div class="h4 text-danger">{{ $stats['rejected'] }}</div>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    @if($approvalRequests->count() > 0)
        <div class="row">
            @foreach($approvalRequests as $request)
            <div class="col-lg-6 mb-4">
                <div class="document-card">
                    <div class="document-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">{{ $request->document_name }}</h5>
                                <span class="badge bg-secondary mb-2">
                                    {{ $request->document_type ? $request->document_type : 'N/A' }}
                                </span>
                                <div class="document-meta">
                                    {{-- <div class="meta-item">
                                        <i class="fas fa-hashtag"></i>
                                        {{ $request->full_document_number }}
                                    </div> --}}
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        {{ $request->created_at->format('d M Y') }}
                                    </div>
                                    {{-- @if($request->priority !== 'normal')
                                    <span class="priority-badge priority-{{ $request->priority }}">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                    @endif --}}
                                </div>
                            </div>
                            <span class="status-badge status-{{ str_replace(' ', '_', strtolower($request->status)) }}">
                                {{ $request->status_label }}
                            </span>
                        </div>
                    </div>

                    <div class="document-body">
                        <!-- Timeline -->
                        <div class="status-timeline">
                            <!-- Step 1: Submitted -->
                            <div class="timeline-item">
                                <div class="timeline-dot completed"></div>
                                <div>
                                    <strong>Document Submitted</strong>
                                    <div class="small text-muted">{{ $request->created_at->format('d M Y H:i') }}</div>
                                    @if($request->notes)
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-comment me-1"></i>{{ Str::limit($request->notes, 100) }}
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 2: Review -->
                            <div class="timeline-item">
                                <div class="timeline-dot {{
                                    $request->status === 'pending' ? 'current' :
                                    ($request->status === 'rejected' ? 'rejected' : 'completed')
                                }}"></div>
                                <div>
                                    <strong>Kaprodi Review</strong>
                                    @if($request->status === 'pending')
                                        <div class="small text-muted">Waiting for Kaprodi approval...</div>
                                    @elseif($request->status === 'rejected')
                                        <div class="small text-danger">
                                            <strong>Rejected:</strong> {{ $request->rejection_reason }}
                                        </div>
                                        @if($request->rejected_at)
                                        <div class="small text-muted">{{ $request->rejected_at->format('d M Y H:i') }}</div>
                                        @endif
                                        @if($request->rejector)
                                        <div class="small text-muted">by {{ $request->rejector->name }}</div>
                                        @endif
                                    @else
                                        <div class="small text-success">Approved by Kaprodi</div>
                                        @if($request->approved_at)
                                        <div class="small text-muted">{{ $request->approved_at->format('d M Y H:i') }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            @if($request->status !== 'rejected' && $request->status !== 'pending')
                            <!-- Step 3: Digital Signing -->
                            <div class="timeline-item">
                                <div class="timeline-dot {{
                                    $request->status === 'approved' ? 'current' :
                                    (in_array($request->status, ['user_signed', 'sign_approved']) ? 'completed' : 'pending')
                                }}"></div>
                                <div>
                                    <strong>Digital Signing</strong>
                                    @if($request->status === 'approved')
                                        <div class="small text-muted">Ready for your digital signature</div>
                                    @elseif(in_array($request->status, ['user_signed', 'sign_approved']))
                                        <div class="small text-success">Digitally signed</div>
                                        @if($request->user_signed_at)
                                        <div class="small text-muted">{{ $request->user_signed_at->format('d M Y H:i') }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Step 4: Final Approval -->
                            <div class="timeline-item">
                                <div class="timeline-dot {{
                                    $request->status === 'sign_approved' ? 'completed' :
                                    ($request->status === 'user_signed' ? 'current' : 'pending')
                                }}"></div>
                                <div>
                                    <strong>Final Approval</strong>
                                    @if($request->status === 'sign_approved')
                                        <div class="small text-success">Process completed successfully</div>
                                        @if($request->sign_approved_at)
                                        <div class="small text-muted">{{ $request->sign_approved_at->format('d M Y H:i') }}</div>
                                        @endif
                                    @elseif($request->status === 'user_signed')
                                        <div class="small text-muted">Waiting for final approval</div>
                                    @else
                                        <div class="small text-muted">Pending completion of previous steps</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- REJECTED STATUS ALERT -->
                        @if($request->status === 'rejected' || $request->documentSignature && $request->documentSignature->signature_status === 'rejected')
                        <div class="alert alert-danger">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-circle fa-2x me-3 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading mb-2">Signature Rejected</h6>
                                    <p class="mb-2"><strong>Reason:</strong> {{ $request->rejection_reason }}</p>
                                    @if($request->documentSignature && $request->documentSignature->signature_status === 'rejected')
                                        <p class="mb-2 small">
                                            <strong>Issue Type:</strong>
                                            @if(str_contains($request->rejection_reason, 'placement'))
                                                Signature Placement Problem
                                            @elseif(str_contains($request->rejection_reason, 'size') || str_contains($request->rejection_reason, 'large'))
                                                Signature Size Issue
                                            @elseif(str_contains($request->rejection_reason, 'quality'))
                                                Signature Quality Problem
                                            @else
                                                General Signature Issue
                                            @endif
                                        </p>
                                    @endif
                                    <hr class="my-2">
                                    <p class="mb-0 small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Please submit a new document request with the corrections mentioned above.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- QR Code Display -->
                        @if($request->documentSignature && $request->documentSignature->qr_code_path && in_array($request->status, ['sign_approved']))
                        <div class="qr-code-display">
                            <h6>Verification QR Code</h6>
                            <img src="{{ Storage::url($request->documentSignature->qr_code_path) }}"
                                 alt="QR Code" class="img-fluid" style="max-width: 150px;">
                            <div class="small text-muted mt-2">
                                Scan to verify document authenticity
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="action-buttons mt-3">
                            @if($request->status === 'rejected')
                                <a href="{{ route('user.signature.approval.request') }}"
                                   class="btn btn-primary">
                                    <i class="fas fa-redo me-1"></i> Submit New Request
                                </a>
                                {{-- <a href="{{ route('user.signature.approval.request') }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-question-circle me-1"></i> Need Help?
                                </a> --}}
                            @endif

                            @if($request->status === 'approved')
                                <a href="{{ route('user.signature.sign.document', $request->id) }}"
                                   class="btn btn-primary">
                                    <i class="fas fa-signature me-1"></i> Sign Document
                                </a>
                            @endif

                            @if($request->status === 'sign_approved' && $request->signed_document_path)
                                <a href="{{ route('user.signature.my.signatures.download', $request->id) }}"
                                   class="btn btn-success">
                                    <i class="fas fa-download me-1"></i> Download Signed
                                </a>
                            @endif

                            @if($request->documentSignature)
                                <a href="{{ route('user.signature.my.signatures.show', $request->documentSignature->id) }}"
                                   class="btn btn-outline-info">
                                    <i class="fas fa-info-circle me-1"></i> View Details
                                </a>
                            @endif

                            @if($request->documentSignature && $request->documentSignature->qr_code_path && in_array($request->status, ['sign_approved']))
                                <a href="{{ route('user.signature.my.signatures.qr', $request->documentSignature->id) }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-qrcode me-1"></i> Download QR
                                </a>
                            @endif

                            <!-- Verification Link -->
                            @if($request->documentSignature && $request->documentSignature->verification_url && in_array($request->status, ['sign_approved']))
                                <button class="btn btn-outline-primary"
                                        onclick="copyVerificationLink('{{ $request->documentSignature->verification_url }}')">
                                    <i class="fas fa-link me-1"></i> Copy Verification Link
                                </button>
                            @endif
                        </div>

                        <!-- Additional Info -->
                        @if($request->deadline)
                        <div class="mt-3 p-2 bg-light rounded">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Expected completion: {{ $request->deadline->format('d M Y') }}
                                @if($request->deadline->isPast() && !in_array($request->status, ['sign_approved', 'rejected']))
                                    <span class="text-danger">(Overdue)</span>
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="status-container">
            <div class="empty-state">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Documents Found</h4>
                <p class="text-muted mb-4">You haven't submitted any documents for approval yet.</p>
                <a href="{{ route('user.signature.approval.request') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Submit Your First Document
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Copy Link Modal -->
<div class="modal fade" id="linkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verification Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Share this link to allow others to verify your document:</p>
                <div class="input-group">
                    <input type="text" class="form-control" id="verificationLink" readonly>
                    <button class="btn btn-outline-primary" onclick="copyToClipboard()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshStatus() {
    location.reload();
}

function copyVerificationLink(url) {
    document.getElementById('verificationLink').value = url;
    new bootstrap.Modal(document.getElementById('linkModal')).show();
}

function copyToClipboard() {
    const linkInput = document.getElementById('verificationLink');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999);

    try {
        document.execCommand('copy');

        // Show success feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);

    } catch (err) {
        alert('Failed to copy link. Please copy manually.');
    }
}

// Auto-refresh every 30 seconds if there are pending documents
@if($approvalRequests->whereIn('status', ['pending', 'approved', 'user_signed'])->count() > 0)
setInterval(function() {
    // Check if page is visible
    if (!document.hidden) {
        location.reload();
    }
}, 30000);
@endif

// Add notification sound for status changes (optional)
function playNotificationSound() {
    try {
        const audio = new Audio('/sounds/notification.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignore if audio fails to play
        });
    } catch (e) {
        // Ignore audio errors
    }
}
</script>
@endpush
