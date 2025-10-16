@extends('kaprodi.layouts.app')

@section('title', 'Dokumen Pending')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-warning">
                                <i class="fas fa-clock me-2"></i>Dokumen Pending
                            </h4>
                            <p class="text-muted mb-0">Dokumen yang menunggu tanda tangan Anda</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning text-white rounded-circle p-3">
                                <h3 class="mb-0">{{ $pendingRequests->count() }}</h3>
                            </div>
                            <button class="btn btn-warning" onclick="refreshData()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Documents Alert -->
    @if($urgentCount > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                <div>
                    <strong>Perhatian!</strong> Terdapat {{ $urgentCount }} dokumen urgent yang memerlukan tanda tangan segera.
                    <a href="{{ route('kaprodi.signatures.urgent') }}" class="alert-link ms-2">Lihat dokumen urgent →</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Priority Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⚡ Aksi Prioritas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <button class="btn btn-danger w-100 mb-2" onclick="showUrgentOnly()">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Tampilkan Hanya Urgent ({{ $urgentCount }})
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-warning w-100 mb-2" onclick="showExpiringToday()">
                                <i class="fas fa-calendar-times me-2"></i>
                                Berakhir Hari Ini ({{ $expiringTodayCount }})
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info w-100 mb-2" onclick="showAll()">
                                <i class="fas fa-list me-2"></i>
                                Tampilkan Semua
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Documents List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📋 Dokumen Menunggu Tanda Tangan</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="sortBy" style="width: auto;">
                                <option value="created_at">Terbaru</option>
                                <option value="deadline">Deadline</option>
                                <option value="priority">Prioritas</option>
                                <option value="requester">Pemohon</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($pendingRequests->count() > 0)
                        <div id="documentsContainer">
                            @foreach($pendingRequests as $request)
                            <div class="document-item border-bottom p-4 {{ $request->is_urgent ? 'urgent-item' : '' }} {{ $request->deadline && $request->deadline->isToday() ? 'expiring-today' : '' }}"
                                 data-priority="{{ $request->is_urgent ? 'urgent' : 'normal' }}"
                                 data-deadline="{{ $request->deadline ? $request->deadline->format('Y-m-d') : '' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-primary bg-opacity-10 rounded p-3 me-3">
                                                <i class="fas fa-file-alt text-primary fa-lg"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $request->title }}</h6>
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <span class="me-3">
                                                        <i class="fas fa-user me-1"></i>
                                                        {{ $request->requester->name }}
                                                    </span>
                                                    <span class="me-3">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        {{ $request->requester->email }}
                                                    </span>
                                                </div>
                                                @if($request->description)
                                                    <p class="text-muted small mb-1">{{ Str::limit($request->description, 100) }}</p>
                                                @endif
                                                <div class="d-flex gap-2 mt-2">
                                                    @if($request->is_urgent)
                                                        <span class="badge bg-danger">URGENT</span>
                                                    @endif
                                                    <span class="badge bg-light text-dark">{{ ucfirst($request->type) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            @if($request->deadline)
                                                @php
                                                    $daysLeft = now()->diffInDays($request->deadline, false);
                                                    $hoursLeft = now()->diffInHours($request->deadline, false);
                                                @endphp
                                                <div class="mb-2">
                                                    <i class="fas fa-calendar-alt text-muted"></i>
                                                    <div class="fw-medium">{{ $request->deadline->format('d M Y') }}</div>
                                                    <div class="small">{{ $request->deadline->format('H:i') }}</div>
                                                </div>
                                                <div class="small">
                                                    @if($daysLeft < 0)
                                                        <span class="badge bg-danger">OVERDUE</span>
                                                    @elseif($daysLeft == 0)
                                                        <span class="badge bg-warning">{{ $hoursLeft }} jam lagi</span>
                                                    @elseif($daysLeft <= 2)
                                                        <span class="badge bg-warning">{{ $daysLeft }} hari lagi</span>
                                                    @else
                                                        <span class="badge bg-success">{{ $daysLeft }} hari lagi</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-muted">
                                                    <i class="fas fa-infinity"></i>
                                                    <div class="small">Tidak ada deadline</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('kaprodi.signatures.show', $request) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-2"></i>Lihat Detail
                                            </a>
                                            <a href="{{ route('kaprodi.signatures.sign', $request) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-signature me-2"></i>Tanda Tangani Sekarang
                                            </a>
                                            @if($request->document)
                                                <a href="{{ route('documents.download', $request->document) }}"
                                                   class="btn btn-outline-secondary btn-sm" target="_blank">
                                                    <i class="fas fa-download me-2"></i>Unduh Dokumen
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($pendingRequests->hasPages())
                            <div class="card-footer bg-white border-top">
                                {{ $pendingRequests->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h6 class="text-success">Selamat! Tidak Ada Dokumen Pending</h6>
                            <p class="text-muted">Semua dokumen telah ditandatangani atau tidak ada permintaan baru</p>
                            <a href="{{ route('kaprodi.signatures.index') }}" class="btn btn-primary">
                                Lihat Semua Dokumen
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Sort functionality
    $('#sortBy').change(function() {
        sortDocuments($(this).val());
    });

    // Auto refresh every 60 seconds for pending documents
    setInterval(function() {
        refreshData();
    }, 60000);

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});

function showUrgentOnly() {
    $('.document-item').hide();
    $('.urgent-item').show();
    updateActionButtons('urgent');
}

function showExpiringToday() {
    $('.document-item').hide();
    $('.expiring-today').show();
    updateActionButtons('expiring');
}

function showAll() {
    $('.document-item').show();
    updateActionButtons('all');
}

function updateActionButtons(filter) {
    $('.btn').removeClass('active');

    if (filter === 'urgent') {
        $('.btn-danger').addClass('active');
    } else if (filter === 'expiring') {
        $('.btn-warning').addClass('active');
    } else {
        $('.btn-info').addClass('active');
    }
}

function sortDocuments(sortBy) {
    const container = $('#documentsContainer');
    const items = container.children('.document-item').get();

    items.sort(function(a, b) {
        let aVal, bVal;

        switch(sortBy) {
            case 'deadline':
                aVal = $(a).data('deadline') || '9999-12-31';
                bVal = $(b).data('deadline') || '9999-12-31';
                return aVal.localeCompare(bVal);

            case 'priority':
                aVal = $(a).data('priority') === 'urgent' ? 0 : 1;
                bVal = $(b).data('priority') === 'urgent' ? 0 : 1;
                return aVal - bVal;

            case 'requester':
                aVal = $(a).find('.fas.fa-user').parent().text().trim();
                bVal = $(b).find('.fas.fa-user').parent().text().trim();
                return aVal.localeCompare(bVal);

            default: // created_at
                return 0; // Keep original order
        }
    });

    $.each(items, function(index, item) {
        container.append(item);
    });
}

function refreshData() {
    // Add subtle loading indicator
    const originalText = $('.card-header h5').text();
    $('.card-header h5').html('<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui...');

    setTimeout(function() {
        window.location.reload();
    }, 2000);
}

// Notification sound for urgent documents
@if($urgentCount > 0)
    // Play subtle notification sound
    if (typeof Audio !== 'undefined') {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+H2u2ceBDON0+/QfC4GM2+3xNyUPwkPhP//y3Mrc');
        audio.volume = 0.1;
        audio.play().catch(() => {}); // Ignore autoplay policy errors
    }
@endif
</script>

<style>
.urgent-item {
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.05) 0%, transparent 100%);
    border-left: 4px solid #dc3545 !important;
}

.expiring-today {
    background: linear-gradient(90deg, rgba(255, 193, 7, 0.05) 0%, transparent 100%);
    border-left: 4px solid #ffc107 !important;
}

.document-item:hover {
    background-color: rgba(0,0,0,0.02);
}

.badge {
    font-size: 0.75rem;
}

.btn.active {
    transform: scale(0.98);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.urgent-item .badge.bg-danger {
    animation: pulse 2s infinite;
}
</style>
@endpush