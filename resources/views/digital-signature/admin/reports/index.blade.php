{{-- resources/views/digital-signature/admin/reports/index.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Reports & Analytics')

@section('sidebar')
    @include('digital-signature.admin.partials.sidebar')
@endsection

@section('content')
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-chart-bar me-3"></i>
                    Reports & Analytics
                </h1>
                <p class="mb-0 opacity-75">Comprehensive insights and statistics for digital signature system</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="btn-group">
                    <button class="btn btn-light" onclick="refreshReport()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-download me-1"></i> Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.signature.reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-calendar me-1"></i>
                            Showing data from {{ Carbon\Carbon::parse($startDate)->format('d M Y') }}
                            to {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Overview Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-primary">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number text-primary">{{ $statistics['total_approval_requests'] }}</div>
                    <div class="text-muted">Total Approval Requests</div>
                    <div class="mt-2">
                        <small class="text-success">
                            <i class="fas fa-check me-1"></i>
                            {{ $statistics['approval_sign_approved'] }} Completed
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-success">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number text-success">{{ $statistics['total_document_signatures'] }}</div>
                    <div class="text-muted">Documents Signed</div>
                    <div class="mt-2">
                        <small class="text-info">
                            <i class="fas fa-shield-alt me-1"></i>
                            {{ $statistics['documents_verified'] }} Verified
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-info">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number text-info">{{ $statistics['total_qr_codes'] }}</div>
                    <div class="text-muted">QR Codes Generated</div>
                    <div class="mt-2">
                        <small class="text-warning">
                            <i class="fas fa-eye me-1"></i>
                            {{ number_format($statistics['qr_total_scans']) }} Scans
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon bg-warning">
                    <i class="fas fa-key"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-number text-warning">{{ $statistics['total_digital_signatures'] }}</div>
                    <div class="text-muted">Digital Signatures</div>
                    <div class="mt-2">
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ $statistics['signatures_active'] }} Active
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h2 text-success mb-2">{{ $statistics['completion_rate'] }}%</div>
                    <div class="text-muted">Completion Rate</div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $statistics['completion_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h2 text-danger mb-2">{{ $statistics['approval_rejection_rate'] }}%</div>
                    <div class="text-muted small">Approval Rejection</div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-danger" style="width: {{ $statistics['approval_rejection_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="h2 text-warning mb-2">{{ $statistics['signature_invalid_rate'] }}%</div>
                    <div class="text-muted small">Invalid Signatures</div>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: {{ $statistics['signature_invalid_rate'] }}%"></div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        {{ $statistics['documents_invalid'] }} marked invalid
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h4 text-info mb-2">
                        <i class="fas fa-clock me-2"></i>
                        {{ $statistics['avg_processing_time'] }}
                    </div>
                    <div class="text-muted">Avg. Processing Time</div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h2 text-primary mb-2">{{ $qrAnalytics['avg_scans_per_code'] }}</div>
                    <div class="text-muted">Avg. QR Scans/Code</div>
                </div>
            </div>
        </div>

        {{-- <div class="col-lg-4 col-md-4 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-tachometer-alt fa-3x text-primary mb-3"></i>
                    <h5>Performance Metrics</h5>
                    <p class="text-muted">Detailed performance analysis</p>
                    <a href="{{ route('admin.signature.reports.performance') }}"
                        class="btn btn-primary">
                        <i class="fas fa-arrow-right me-1"></i> View Metrics
                    </a>
                </div>
            </div>
        </div> --}}
    </div>

    <div class="row">
        <!-- Top Users -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Top Users by Submissions
                    </h5>
                </div>
                <div class="card-body">
                    @if($topUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>NIM</th>
                                        <th class="text-center">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topUsers->take(7) as $index => $userStat)
                                    <tr>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge bg-warning">{{ $index + 1 }}</span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td class="small">{{ Str::limit($userStat->user->name ?? 'Unknown', 15) }}</td>
                                        <td>
                                            <small class="text-muted">{{ $userStat->user->NIM ?? 'N/A' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $userStat->submission_count }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <p>No user data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Rejection Reasons -->
        <div class="col-lg-4 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Top Rejection Reasons
                    </h5>
                </div>
                <div class="card-body">
                    @if($topRejectionReasons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Reason</th>
                                        <th width="80" class="text-center">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topRejectionReasons->take(7) as $index => $rejection)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="small" title="{{ $rejection->rejection_reason }}">
                                                {{ Str::limit($rejection->rejection_reason, 30) }}
                                            </div>
                                            <span class="badge bg-secondary" style="font-size: 0.65rem;">
                                                {{ $rejection->category }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">{{ $rejection->count }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p class="small">No rejections yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Type Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Document Type Distribution
                    </h5>
                </div>
                <div class="card-body">
                    @if($documentTypeDistribution->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th class="text-center">Count</th>
                                        <th width="200">Distribution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalDocs = $documentTypeDistribution->sum('total');
                                    @endphp
                                    @foreach($documentTypeDistribution as $type)
                                    <tr>
                                        <td>{{ $type->document_type ?? 'Unspecified' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $type->total }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info"
                                                     style="width: {{ $totalDocs > 0 ? ($type->total / $totalDocs * 100) : 0 }}%">
                                                    {{ $totalDocs > 0 ? round($type->total / $totalDocs * 100, 1) : 0 }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <p>No document types data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Timeline Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Activity Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="timelineChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- QR Code Analytics -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        QR Code Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 text-center border-end">
                            <div class="h3 text-info">{{ $qrAnalytics['total_codes'] }}</div>
                            <small class="text-muted">Total QR Codes</small>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h3 text-success">{{ $qrAnalytics['total_scans'] }}</div>
                            <small class="text-muted">Total Scans</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-6 text-center border-end">
                            <div class="h4 text-primary">{{ $qrAnalytics['active_codes'] }}</div>
                            <small class="text-muted">Active Codes</small>
                        </div>
                        <div class="col-6 text-center">
                            <div class="h4 text-danger">{{ $qrAnalytics['expired_codes'] }}</div>
                            <small class="text-muted">Expired Codes</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="h4 text-warning">{{ $qrAnalytics['never_scanned'] }}</div>
                        <small class="text-muted">Never Scanned</small>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.signature.reports.qr-codes') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye me-1"></i> View Detailed QR Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row">
        <!-- Status Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Approval Status
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Activity (5 Latest)
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="activity-timeline">
                            @foreach($recentActivity as $activity)
                            <div class="activity-item">
                                <div class="activity-icon bg-{{ $activity['color'] }}">
                                    <i class="fas {{ $activity['icon'] }}"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">{{ $activity['title'] }}</div>
                                    <div class="activity-description">{{ $activity['description'] }}</div>
                                    <div class="activity-timestamp">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $activity['timestamp']->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-3x mb-3"></i>
                            <p>No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        {{-- <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Priority Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" height="250"></canvas>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Expiring Signatures Alert -->
    @if($expiringSoon->count() > 0)
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Signatures Expiring Soon (Next 30 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Signature ID</th>
                                    <th>Algorithm</th>
                                    <th>Created By</th>
                                    <th>Valid Until</th>
                                    <th>Days Remaining</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiringSoon as $signature)
                                <tr>
                                    <td><code>{{ Str::limit($signature->signature_id, 20) }}</code></td>
                                    <td><span class="badge bg-info">{{ $signature->algorithm }}</span></td>
                                    <td>System Generated</td>
                                    <td>{{ $signature->valid_until->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ $signature->valid_until->diffInDays(now()) }} days
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.signature.keys.index', $signature->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('admin.signature.reports.export') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select class="form-select" name="format" required>
                            <option value="csv">CSV (Excel Compatible)</option>
                            <option value="pdf">PDF Document</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download me-1"></i> Download Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stats-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
}

.stats-content {
    flex: 1;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.activity-timeline {
    position: relative;
}

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #e9ecef;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.activity-description {
    color: #6c757d;
    font-size: 0.875rem;
}

.activity-timestamp {
    margin-top: 0.25rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Timeline Chart
const timelineCtx = document.getElementById('timelineChart').getContext('2d');
new Chart(timelineCtx, {
    type: 'line',
    data: {
        labels: @json($timelineData['dates']),
        datasets: [
            {
                label: 'Approval Requests',
                data: @json($timelineData['approvals']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            },
            {
                label: 'Documents Signed',
                data: @json($timelineData['documents']),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Approved', 'User Signed', 'Completed', 'Rejected'],
        datasets: [{
            data: [
                {{ $statistics['approval_pending'] }},
                {{ $statistics['approval_approved'] }},
                {{ $statistics['approval_user_signed'] }},
                {{ $statistics['approval_sign_approved'] }},
                {{ $statistics['approval_rejected'] }}
            ],
            backgroundColor: [
                'rgba(255, 206, 86, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(76, 175, 80, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

function refreshReport() {
    location.reload();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    const lastRefresh = localStorage.getItem('reportLastRefresh');
    const now = Date.now();

    if (!lastRefresh || (now - lastRefresh) > 300000) {
        refreshReport();
        localStorage.setItem('reportLastRefresh', now);
    }
}, 300000);
</script>
@endpush
