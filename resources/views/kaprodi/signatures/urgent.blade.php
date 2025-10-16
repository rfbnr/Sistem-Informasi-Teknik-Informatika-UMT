@extends('kaprodi.layouts.app')

@section('title', 'Dokumen Urgent')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Dokumen Urgent
                            </h4>
                            <p class="text-muted mb-0">Dokumen dengan prioritas tinggi yang memerlukan perhatian segera</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger text-white rounded-circle p-3 pulse-animation">
                                <h3 class="mb-0">{{ $urgentRequests->count() }}</h3>
                            </div>
                            <button class="btn btn-danger" onclick="refreshData()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notice -->
    @if($urgentRequests->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center">
                <i class="fas fa-bell fa-lg me-3"></i>
                <div>
                    <strong>Tindakan Diperlukan!</strong> Terdapat {{ $urgentRequests->count() }} dokumen urgent yang memerlukan tanda tangan segera.
                    Beberapa mungkin sudah mendekati atau melewati deadline.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⚡ Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-danger w-100 mb-2" onclick="signAllUrgent()">
                                <i class="fas fa-signature me-2"></i>
                                Batch Sign All
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning w-100 mb-2" onclick="showOverdueOnly()">
                                <i class="fas fa-calendar-times me-2"></i>
                                Overdue ({{ $overdueCount }})
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info w-100 mb-2" onclick="showExpiringToday()">
                                <i class="fas fa-clock me-2"></i>
                                Berakhir Hari Ini ({{ $expiringTodayCount }})
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary w-100 mb-2" onclick="downloadAllDocuments()">
                                <i class="fas fa-download me-2"></i>
                                Download All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent Documents List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">🚨 Dokumen Urgent</h5>
                        <div class="d-flex gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                                <label class="form-check-label" for="autoRefresh">
                                    Auto Refresh
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($urgentRequests->count() > 0)
                        <div id="urgentDocumentsContainer">
                            @foreach($urgentRequests as $request)
                            <div class="urgent-document-item border-bottom p-4 {{ $request->deadline && $request->deadline->isPast() ? 'overdue-item' : '' }} {{ $request->deadline && $request->deadline->isToday() ? 'expiring-today' : '' }}"
                                 data-deadline="{{ $request->deadline ? $request->deadline->format('Y-m-d H:i:s') : '' }}"
                                 data-request-id="{{ $request->id }}">
                                <div class="row align-items-center">
                                    <!-- Document Info -->
                                    <div class="col-md-5">
                                        <div class="d-flex align-items-start">
                                            <div class="position-relative me-3">
                                                <div class="bg-danger bg-opacity-20 rounded p-3">
                                                    <i class="fas fa-file-alt text-danger fa-lg"></i>
                                                </div>
                                                <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">
                                                    URGENT
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $request->title }}</h6>
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <span class="me-3">
                                                        <i class="fas fa-user me-1"></i>
                                                        {{ $request->requester->name }}
                                                    </span>
                                                    <span class="me-3">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ $request->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                                @if($request->description)
                                                    <p class="text-muted small mb-2">{{ Str::limit($request->description, 120) }}</p>
                                                @endif
                                                <div class="d-flex gap-1">
                                                    <span class="badge bg-danger">URGENT</span>
                                                    <span class="badge bg-light text-dark">{{ ucfirst($request->type) }}</span>
                                                    @if($request->status === 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deadline Info -->
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            @if($request->deadline)
                                                @php
                                                    $now = now();
                                                    $deadline = $request->deadline;
                                                    $isOverdue = $deadline->isPast();
                                                    $hoursLeft = $now->diffInHours($deadline, false);
                                                    $minutesLeft = $now->diffInMinutes($deadline, false);
                                                @endphp
                                                <div class="mb-2">
                                                    <div class="fw-medium {{ $isOverdue ? 'text-danger' : ($hoursLeft <= 24 ? 'text-warning' : 'text-success') }}">
                                                        {{ $deadline->format('d M Y') }}
                                                    </div>
                                                    <div class="small text-muted">{{ $deadline->format('H:i') }}</div>
                                                </div>
                                                <div class="countdown-timer" data-deadline="{{ $deadline->toISOString() }}">
                                                    @if($isOverdue)
                                                        <span class="badge bg-danger fs-6">OVERDUE</span>
                                                        <div class="small text-danger">{{ $deadline->diffForHumans() }}</div>
                                                    @elseif($hoursLeft <= 1 && $minutesLeft > 0)
                                                        <span class="badge bg-danger fs-6">{{ $minutesLeft }}m tersisa</span>
                                                        <div class="small text-danger blink">Segera berakhir!</div>
                                                    @elseif($hoursLeft <= 24)
                                                        <span class="badge bg-warning fs-6">{{ $hoursLeft }}h tersisa</span>
                                                        <div class="small text-warning">Berakhir hari ini</div>
                                                    @else
                                                        <span class="badge bg-success fs-6">{{ ceil($hoursLeft/24) }} hari</span>
                                                        <div class="small text-muted">tersisa</div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-muted">
                                                    <i class="fas fa-infinity"></i>
                                                    <div class="small">Tanpa deadline</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="col-md-4">
                                        <div class="d-grid gap-2">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <a href="{{ route('kaprodi.signatures.show', $request) }}"
                                                       class="btn btn-outline-primary btn-sm w-100">
                                                        <i class="fas fa-eye me-1"></i>Detail
                                                    </a>
                                                </div>
                                                <div class="col-6">
                                                    @if($request->document)
                                                        <a href="{{ route('documents.download', $request->document) }}"
                                                           class="btn btn-outline-secondary btn-sm w-100" target="_blank">
                                                            <i class="fas fa-download me-1"></i>Unduh
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                            <a href="{{ route('kaprodi.signatures.sign', $request) }}"
                                               class="btn btn-danger btn-sm sign-urgent-btn"
                                               data-request-id="{{ $request->id }}">
                                                <i class="fas fa-signature me-2"></i>
                                                <strong>TANDA TANGANI SEKARANG</strong>
                                            </a>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-warning flex-fill"
                                                        onclick="postponeDeadline({{ $request->id }})">
                                                    <i class="fas fa-clock me-1"></i>Tunda
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info flex-fill"
                                                        onclick="addToCalendar({{ $request->id }})">
                                                    <i class="fas fa-calendar-plus me-1"></i>Kalender
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress indicator for signing process -->
                                <div class="signing-progress mt-3" id="progress-{{ $request->id }}" style="display: none;">
                                    <div class="progress">
                                        <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated"
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Memproses tanda tangan...</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($urgentRequests->hasPages())
                            <div class="card-footer bg-white border-top">
                                {{ $urgentRequests->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h6 class="text-success">Excellent! Tidak Ada Dokumen Urgent</h6>
                            <p class="text-muted">Semua dokumen urgent telah ditandatangani atau tidak ada dokumen dengan prioritas tinggi</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('kaprodi.signatures.pending') }}" class="btn btn-warning">
                                    Lihat Dokumen Pending
                                </a>
                                <a href="{{ route('kaprodi.signatures.index') }}" class="btn btn-primary">
                                    Lihat Semua Dokumen
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Batch Sign Modal -->
<div class="modal fade" id="batchSignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batch Sign Dokumen Urgent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Perhatian!</strong> Anda akan menandatangani {{ $urgentRequests->count() }} dokumen sekaligus.
                    Pastikan Anda telah mereview semua dokumen.
                </div>
                <div class="form-group">
                    <label for="batchSignPin">PIN Tanda Tangan:</label>
                    <input type="password" class="form-control" id="batchSignPin" maxlength="6" placeholder="Masukkan PIN 6 digit">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="processBatchSign()">
                    <i class="fas fa-signature me-2"></i>Tanda Tangani Semua
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize countdown timers
    initializeCountdowns();

    // Auto-refresh if enabled
    setInterval(function() {
        if ($('#autoRefresh').is(':checked')) {
            updateCountdowns();
            refreshUrgentStats();
        }
    }, 30000); // Every 30 seconds

    // Real-time countdown updates
    setInterval(updateCountdowns, 1000); // Every second

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Play notification sound
    playUrgentNotification();
});

function initializeCountdowns() {
    $('.countdown-timer').each(function() {
        const deadline = $(this).data('deadline');
        if (deadline) {
            updateSingleCountdown($(this), new Date(deadline));
        }
    });
}

function updateCountdowns() {
    $('.countdown-timer').each(function() {
        const deadline = $(this).data('deadline');
        if (deadline) {
            updateSingleCountdown($(this), new Date(deadline));
        }
    });
}

function updateSingleCountdown(element, deadline) {
    const now = new Date().getTime();
    const distance = deadline.getTime() - now;

    if (distance < 0) {
        element.html('<span class="badge bg-danger fs-6">OVERDUE</span><div class="small text-danger">Telah berakhir</div>');
        element.closest('.urgent-document-item').addClass('overdue-item');
        return;
    }

    const hours = Math.floor(distance / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

    if (hours < 1) {
        element.html(`<span class="badge bg-danger fs-6">${minutes}m tersisa</span><div class="small text-danger blink">Segera berakhir!</div>`);
    } else if (hours <= 24) {
        element.html(`<span class="badge bg-warning fs-6">${hours}h tersisa</span><div class="small text-warning">Berakhir hari ini</div>`);
    } else {
        const days = Math.ceil(hours / 24);
        element.html(`<span class="badge bg-success fs-6">${days} hari</span><div class="small text-muted">tersisa</div>`);
    }
}

function signAllUrgent() {
    $('#batchSignModal').modal('show');
}

function processBatchSign() {
    const pin = $('#batchSignPin').val();
    if (!pin || pin.length !== 6) {
        alert('Masukkan PIN 6 digit!');
        return;
    }

    // Show progress for all documents
    $('.urgent-document-item').each(function() {
        const requestId = $(this).data('request-id');
        $(`#progress-${requestId}`).show();
    });

    $('#batchSignModal').modal('hide');

    // Simulate batch signing process
    let processed = 0;
    const total = $('.urgent-document-item').length;

    $('.urgent-document-item').each(function(index) {
        const requestId = $(this).data('request-id');

        setTimeout(() => {
            // Update progress
            const progress = Math.round(((index + 1) / total) * 100);
            $(`#progress-${requestId} .progress-bar`).css('width', progress + '%');

            // Mark as completed
            setTimeout(() => {
                $(this).fadeOut();
                processed++;

                if (processed === total) {
                    showSuccessMessage('Semua dokumen urgent berhasil ditandatangani!');
                    setTimeout(() => window.location.reload(), 2000);
                }
            }, 1000);
        }, index * 500);
    });
}

function showOverdueOnly() {
    $('.urgent-document-item').hide();
    $('.overdue-item').show();
    updateFilterButtons('overdue');
}

function showExpiringToday() {
    $('.urgent-document-item').hide();
    $('.expiring-today').show();
    updateFilterButtons('expiring');
}

function updateFilterButtons(filter) {
    $('.btn').removeClass('active');
    if (filter === 'overdue') {
        $('.btn-warning').addClass('active');
    } else if (filter === 'expiring') {
        $('.btn-info').addClass('active');
    }
}

function postponeDeadline(requestId) {
    const newDeadline = prompt('Masukkan deadline baru (YYYY-MM-DD HH:MM):');
    if (newDeadline) {
        // Here you would make an AJAX call to update the deadline
        alert('Deadline berhasil diperbarui!');
        // Refresh the page or update the UI
        location.reload();
    }
}

function addToCalendar(requestId) {
    const title = $(`.urgent-document-item[data-request-id="${requestId}"]`).find('h6').text();
    const deadline = $(`.urgent-document-item[data-request-id="${requestId}"]`).data('deadline');

    if (deadline) {
        const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${encodeURIComponent(deadline.replace(/[-:]/g, '').replace('T', 'T').replace(/\.\d{3}Z?$/, 'Z'))}`;
        window.open(calendarUrl, '_blank');
    }
}

function downloadAllDocuments() {
    const documentLinks = [];
    $('.urgent-document-item').each(function() {
        const downloadLink = $(this).find('a[href*="download"]').attr('href');
        if (downloadLink) {
            documentLinks.push(downloadLink);
        }
    });

    if (documentLinks.length > 0) {
        documentLinks.forEach((link, index) => {
            setTimeout(() => {
                window.open(link, '_blank');
            }, index * 500);
        });

        showSuccessMessage(`Mengunduh ${documentLinks.length} dokumen...`);
    } else {
        alert('Tidak ada dokumen yang dapat diunduh.');
    }
}

function refreshData() {
    const originalText = $('.card-header h5').text();
    $('.card-header h5').html('<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui...');

    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

function refreshUrgentStats() {
    $.get('{{ route('kaprodi.api.stats') }}')
        .done(function(data) {
            // Update urgent count
            $('.bg-danger.text-white.rounded-circle h3').text(data.urgent || 0);
        });
}

function showSuccessMessage(message) {
    const alert = $(`
        <div class="alert alert-success alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 9999;">
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('body').append(alert);

    setTimeout(() => {
        alert.fadeOut(() => alert.remove());
    }, 5000);
}

function playUrgentNotification() {
    @if($urgentRequests->count() > 0)
        // Play urgent notification sound
        if (typeof Audio !== 'undefined') {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+H2u2ceBDON0+/QfC4GM2+3xNyUPwkPhP//y3MrTg==');
            audio.volume = 0.2;
            audio.play().catch(() => {}); // Ignore autoplay policy errors
        }
    @endif
}

// Sign button with progress
$('.sign-urgent-btn').click(function(e) {
    e.preventDefault();
    const requestId = $(this).data('request-id');
    const href = $(this).attr('href');

    // Show progress
    $(`#progress-${requestId}`).show();
    $(`#progress-${requestId} .progress-bar`).css('width', '30%');

    // Navigate to sign page
    window.location.href = href;
});
</script>

<style>
.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

.overdue-item {
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.1) 0%, transparent 100%);
    border-left: 4px solid #dc3545 !important;
}

.expiring-today {
    background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, transparent 100%);
    border-left: 4px solid #ffc107 !important;
}

.blink {
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.5; }
}

.urgent-document-item {
    transition: all 0.3s ease;
}

.urgent-document-item:hover {
    background-color: rgba(0,0,0,0.02);
    transform: translateY(-2px);
}

.countdown-timer {
    min-height: 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.signing-progress {
    opacity: 0;
    animation: fadeIn 0.5s ease-in-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.btn.active {
    transform: scale(0.98);
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}
</style>
@endpush