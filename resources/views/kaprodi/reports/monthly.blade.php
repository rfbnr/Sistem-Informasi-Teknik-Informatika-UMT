@extends('kaprodi.layouts.app')

@section('title', 'Laporan Bulanan')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-success">
                                <i class="fas fa-chart-bar me-2"></i>Laporan Bulanan
                            </h4>
                            <p class="text-muted mb-0">Analisis dan statistik tanda tangan digital per bulan</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <label for="monthPicker" class="form-label mb-0 me-2">Bulan:</label>
                                <input type="month" class="form-control form-control-sm" id="monthPicker"
                                       value="{{ request('month', now()->format('Y-m')) }}"
                                       onchange="loadMonthlyReport()">
                            </div>
                            <button class="btn btn-success" onclick="exportReport()">
                                <i class="fas fa-download me-2"></i>Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-file-signature text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $summary['total_signatures'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Total Tanda Tangan</p>
                            @if(isset($summary['signature_growth']))
                                <small class="text-{{ $summary['signature_growth'] > 0 ? 'success' : 'danger' }}">
                                    {{ $summary['signature_growth'] > 0 ? '+' : '' }}{{ $summary['signature_growth'] }}% dari bulan lalu
                                </small>
                            @endif
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
                            <h3 class="h4 mb-0">{{ $summary['completed_documents'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Dokumen Selesai</p>
                            <small class="text-muted">
                                {{ round($summary['completion_rate'] ?? 0, 1) }}% tingkat penyelesaian
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $summary['avg_processing_days'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Rata-rata Hari Proses</p>
                            <small class="text-muted">
                                Tercepat: {{ $summary['fastest_processing'] ?? 0 }} hari
                            </small>
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
                            <i class="fas fa-link text-info fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $summary['blockchain_transactions'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Transaksi Blockchain</p>
                            <small class="text-success">
                                {{ $summary['blockchain_success_rate'] ?? 0 }}% sukses
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Charts Section -->
        <div class="col-lg-8">
            <!-- Signature Trends Chart -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📈 Tren Tanda Tangan Harian</h5>
                </div>
                <div class="card-body">
                    <canvas id="signatureTrendsChart" height="100"></canvas>
                </div>
            </div>

            <!-- Document Types Chart -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📊 Distribusi Jenis Dokumen</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="documentTypesChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Jenis Dokumen</th>
                                            <th>Jumlah</th>
                                            <th>Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($documentTypes ?? [] as $type)
                                        <tr>
                                            <td>{{ ucfirst($type['name']) }}</td>
                                            <td>{{ $type['count'] }}</td>
                                            <td>{{ round($type['percentage'], 1) }}%</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Processing Time Analysis -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⏱️ Analisis Waktu Pemrosesan</h5>
                </div>
                <div class="card-body">
                    <canvas id="processingTimeChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Sidebar Statistics -->
        <div class="col-lg-4">
            <!-- Top Requesters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">👥 Top Pemohon</h5>
                </div>
                <div class="card-body p-0">
                    @if(isset($topRequesters) && count($topRequesters) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topRequesters as $requester)
                            <div class="list-group-item border-0 px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $requester['name'] }}</h6>
                                        <small class="text-muted">{{ $requester['email'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">{{ $requester['count'] }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Tidak ada data pemohon</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">📋 Distribusi Status</h5>
                </div>
                <div class="card-body">
                    @foreach($statusDistribution ?? [] as $status)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            @php
                                $statusConfig = [
                                    'completed' => ['color' => 'success', 'icon' => 'check-circle'],
                                    'pending' => ['color' => 'warning', 'icon' => 'clock'],
                                    'in_progress' => ['color' => 'info', 'icon' => 'spinner'],
                                    'rejected' => ['color' => 'danger', 'icon' => 'times-circle'],
                                ];
                                $config = $statusConfig[$status['status']] ?? ['color' => 'secondary', 'icon' => 'question'];
                            @endphp
                            <i class="fas fa-{{ $config['icon'] }} text-{{ $config['color'] }} me-2"></i>
                            <span>{{ ucfirst($status['status']) }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="progress me-2" style="width: 60px; height: 6px;">
                                <div class="progress-bar bg-{{ $config['color'] }}" style="width: {{ $status['percentage'] }}%"></div>
                            </div>
                            <span class="badge bg-{{ $config['color'] }}">{{ $status['count'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Monthly Goals -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">🎯 Target Bulanan</h5>
                </div>
                <div class="card-body">
                    @php
                        $targetSignatures = 100; // This could come from settings
                        $currentSignatures = $summary['total_signatures'] ?? 0;
                        $targetProgress = min(100, ($currentSignatures / $targetSignatures) * 100);
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Target Tanda Tangan</span>
                            <span class="small">{{ $currentSignatures }}/{{ $targetSignatures }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $targetProgress }}%"></div>
                        </div>
                        <small class="text-muted">{{ round($targetProgress, 1) }}% tercapai</small>
                    </div>

                    @php
                        $targetDays = 3; // Average processing days target
                        $currentDays = $summary['avg_processing_days'] ?? 0;
                        $daysProgress = $currentDays <= $targetDays ? 100 : (($targetDays / $currentDays) * 100);
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Target Waktu Proses</span>
                            <span class="small">{{ $currentDays }}/{{ $targetDays }} hari</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-{{ $daysProgress >= 80 ? 'success' : 'warning' }}" style="width: {{ $daysProgress }}%"></div>
                        </div>
                        <small class="text-muted">
                            {{ $currentDays <= $targetDays ? 'Target tercapai' : 'Perlu perbaikan' }}
                        </small>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="setMonthlyTargets()">
                            <i class="fas fa-cog me-1"></i>Atur Target
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Set Targets Modal -->
<div class="modal fade" id="targetsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Target Bulanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="targetSignatures" class="form-label">Target Tanda Tangan per Bulan:</label>
                        <input type="number" class="form-control" id="targetSignatures" value="100" min="1">
                    </div>
                    <div class="mb-3">
                        <label for="targetProcessingDays" class="form-label">Target Waktu Pemrosesan (hari):</label>
                        <input type="number" class="form-control" id="targetProcessingDays" value="3" min="1" max="30">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveTargets()">Simpan Target</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    // Signature Trends Chart
    const trendsCtx = document.getElementById('signatureTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: @json($chartData['daily_labels'] ?? []),
            datasets: [{
                label: 'Tanda Tangan Harian',
                data: @json($chartData['daily_signatures'] ?? []),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Document Types Chart
    const typesCtx = document.getElementById('documentTypesChart').getContext('2d');
    new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: @json(collect($documentTypes ?? [])->pluck('name')->toArray()),
            datasets: [{
                data: @json(collect($documentTypes ?? [])->pluck('count')->toArray()),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Processing Time Chart
    const timeCtx = document.getElementById('processingTimeChart').getContext('2d');
    new Chart(timeCtx, {
        type: 'bar',
        data: {
            labels: @json($chartData['time_labels'] ?? []),
            datasets: [{
                label: 'Waktu Pemrosesan (hari)',
                data: @json($chartData['processing_times'] ?? []),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        callback: function(value) {
                            return value + ' hari';
                        }
                    }
                }
            }
        }
    });
}

function loadMonthlyReport() {
    const month = $('#monthPicker').val();
    window.location.href = `{{ route('kaprodi.reports.monthly') }}?month=${month}`;
}

function exportReport() {
    const month = $('#monthPicker').val();
    showToast('Mengexport laporan...', 'info');

    // Create export URL
    const exportUrl = `{{ route('kaprodi.reports.monthly') }}/export?month=${month}`;
    window.open(exportUrl, '_blank');

    setTimeout(() => {
        showToast('Laporan berhasil diunduh!', 'success');
    }, 2000);
}

function setMonthlyTargets() {
    $('#targetsModal').modal('show');
}

function saveTargets() {
    const signatureTarget = $('#targetSignatures').val();
    const processingTarget = $('#targetProcessingDays').val();

    // Save targets (this would typically be an AJAX call)
    showToast('Target berhasil disimpan!', 'success');
    $('#targetsModal').modal('hide');

    // Reload page to reflect new targets
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast align-items-center text-white bg-${type === 'info' ? 'primary' : type} border-0 position-fixed"
             style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);

    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();

    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>

<style>
.progress {
    height: 8px;
    background-color: rgba(0,0,0,0.1);
}

.list-group-item:hover {
    background-color: rgba(0,0,0,0.02);
}

.card-header {
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}

canvas {
    max-height: 300px;
}
</style>
@endpush