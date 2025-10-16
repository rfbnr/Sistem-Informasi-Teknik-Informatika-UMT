@extends('kaprodi.layouts.app')

@section('title', 'Manajemen Tanda Tangan')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">🖋️ Manajemen Tanda Tangan</h4>
                            <p class="text-muted mb-0">Kelola semua permintaan tanda tangan digital</p>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?status=all">Semua</a></li>
                                    <li><a class="dropdown-item" href="?status=pending">Pending</a></li>
                                    <li><a class="dropdown-item" href="?status=in_progress">Dalam Proses</a></li>
                                    <li><a class="dropdown-item" href="?status=completed">Selesai</a></li>
                                    <li><a class="dropdown-item" href="?status=expired">Expired</a></li>
                                </ul>
                            </div>
                            <button class="btn btn-primary" onclick="refreshData()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-spinner text-info fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $stats['in_progress'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Dalam Proses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $stats['completed'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $stats['urgent'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Urgent</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signature Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📋 Permintaan Tanda Tangan</h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" placeholder="Cari dokumen..." id="searchInput">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($signatureRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Dokumen</th>
                                        <th class="border-0 py-3">Pemohon</th>
                                        <th class="border-0 py-3">Jenis</th>
                                        <th class="border-0 py-3">Status</th>
                                        <th class="border-0 py-3">Deadline</th>
                                        <th class="border-0 py-3">Progress</th>
                                        <th class="border-0 py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($signatureRequests as $request)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                                                    <i class="fas fa-file-alt text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ Str::limit($request->title, 40) }}</h6>
                                                    <small class="text-muted">{{ $request->document->file_name ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div>
                                                <div class="fw-medium">{{ $request->requester->name }}</div>
                                                <small class="text-muted">{{ $request->requester->email }}</small>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-light text-dark">{{ ucfirst($request->type) }}</span>
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $statusConfig = [
                                                    'pending' => ['badge' => 'bg-warning', 'text' => 'Menunggu'],
                                                    'in_progress' => ['badge' => 'bg-info', 'text' => 'Dalam Proses'],
                                                    'completed' => ['badge' => 'bg-success', 'text' => 'Selesai'],
                                                    'expired' => ['badge' => 'bg-danger', 'text' => 'Expired'],
                                                    'rejected' => ['badge' => 'bg-secondary', 'text' => 'Ditolak']
                                                ];
                                                $config = $statusConfig[$request->status] ?? ['badge' => 'bg-secondary', 'text' => ucfirst($request->status)];
                                            @endphp
                                            <span class="badge {{ $config['badge'] }}">{{ $config['text'] }}</span>
                                            @if($request->is_urgent)
                                                <span class="badge bg-danger ms-1">URGENT</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($request->deadline)
                                                <div class="small">
                                                    {{ $request->deadline->format('d M Y') }}
                                                    @php
                                                        $daysLeft = now()->diffInDays($request->deadline, false);
                                                    @endphp
                                                    @if($daysLeft < 0)
                                                        <span class="text-danger">(Overdue)</span>
                                                    @elseif($daysLeft <= 2)
                                                        <span class="text-warning">({{ $daysLeft }} hari)</span>
                                                    @else
                                                        <span class="text-muted">({{ $daysLeft }} hari)</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $progress = $request->getProgress();
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 60px; height: 6px;">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ round($progress) }}%</small>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('kaprodi.signatures.show', $request) }}"
                                                   class="btn btn-outline-primary" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status === 'pending' || $request->status === 'in_progress')
                                                    <a href="{{ route('kaprodi.signatures.sign', $request) }}"
                                                       class="btn btn-primary" title="Tanda Tangani">
                                                        <i class="fas fa-signature"></i>
                                                    </a>
                                                @endif
                                                @if($request->status === 'completed')
                                                    <a href="{{ route('signatures.download-signed', $request) }}"
                                                       class="btn btn-success" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($signatureRequests->hasPages())
                            <div class="card-footer bg-white border-top">
                                {{ $signatureRequests->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h6>Tidak Ada Permintaan</h6>
                            <p class="text-muted">Belum ada permintaan tanda tangan yang masuk</p>
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
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Auto refresh data every 30 seconds
    setInterval(function() {
        refreshData();
    }, 30000);
});

function refreshData() {
    // Add loading indicator
    $('.card-body').addClass('position-relative');
    $('.card-body').append('<div class="loading-overlay"><i class="fas fa-spinner fa-spin"></i></div>');

    // Reload page to get fresh data
    setTimeout(function() {
        window.location.reload();
    }, 1000);
}

// Auto-update status indicators
setInterval(function() {
    $.get('{{ route('kaprodi.api.stats') }}')
        .done(function(data) {
            // Update stats cards
            Object.keys(data).forEach(function(key) {
                $(`.stat-${key}`).text(data[key]);
            });
        });
}, 60000); // Every minute
</script>

<style>
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    font-size: 1.5rem;
    color: var(--primary-color);
}

.progress {
    background-color: rgba(0,0,0,0.1);
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endpush