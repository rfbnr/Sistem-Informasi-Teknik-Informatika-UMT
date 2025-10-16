@extends('kaprodi.layouts.app')

@section('title', 'Dashboard Kaprodi')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h3 mb-1">🎓 Dashboard Kaprodi</h2>
                            <p class="mb-0 opacity-75">Panel manajemen tanda tangan digital dan persetujuan dokumen</p>
                        </div>
                        <div class="text-end">
                            <div class="h4 mb-0">{{ now()->format('H:i') }}</div>
                            <small class="opacity-75">{{ now()->format('d M Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $pendingCount }}</h3>
                            <p class="text-muted mb-0">Menunggu Tanda Tangan</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('kaprodi.signatures.pending') }}" class="btn btn-sm btn-warning">
                            Lihat Detail
                        </a>
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
                            <h3 class="h4 mb-0">{{ $completedCount }}</h3>
                            <p class="text-muted mb-0">Sudah Ditandatangani</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('kaprodi.signatures.completed') }}" class="btn btn-sm btn-success">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-shield-alt text-info fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $blockchainCount }}</h3>
                            <p class="text-muted mb-0">Transaksi Blockchain</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('kaprodi.blockchain.transactions') }}" class="btn btn-sm btn-info">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-calendar text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $thisMonthCount }}</h3>
                            <p class="text-muted mb-0">Bulan Ini</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">+{{ $growthPercentage }}% dari bulan lalu</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Signature Requests -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📋 Permintaan Tanda Tangan Terbaru</h5>
                        <a href="{{ route('kaprodi.signatures.index') }}" class="btn btn-sm btn-outline-primary">
                            Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentRequests->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentRequests as $request)
                            <div class="list-group-item border-0 px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="fas fa-file-alt text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $request->title }}</h6>
                                        <div class="d-flex align-items-center text-muted small">
                                            <span class="me-3">
                                                <i class="fas fa-user me-1"></i>
                                                {{ $request->requester->name }}
                                            </span>
                                            <span class="me-3">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $request->created_at->diffForHumans() }}
                                            </span>
                                            @if($request->deadline)
                                            <span class="me-3">
                                                <i class="fas fa-calendar me-1"></i>
                                                Deadline: {{ $request->deadline->format('d M') }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @php
                                            $statusConfig = [
                                                'pending' => ['badge' => 'bg-warning', 'text' => 'Menunggu'],
                                                'in_progress' => ['badge' => 'bg-info', 'text' => 'Dalam Proses'],
                                                'completed' => ['badge' => 'bg-success', 'text' => 'Selesai'],
                                                'expired' => ['badge' => 'bg-danger', 'text' => 'Expired']
                                            ];
                                            $config = $statusConfig[$request->status] ?? ['badge' => 'bg-secondary', 'text' => ucfirst($request->status)];
                                        @endphp
                                        <span class="badge {{ $config['badge'] }} mb-2">{{ $config['text'] }}</span>
                                        <div>
                                            @if($request->status === 'pending')
                                                <a href="{{ route('kaprodi.signatures.sign', $request) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-signature me-1"></i>Tanda Tangani
                                                </a>
                                            @else
                                                <a href="{{ route('kaprodi.signatures.show', $request) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-eye me-1"></i>Lihat
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
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

        <!-- Quick Actions & Stats -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">⚡ Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('kaprodi.signatures.pending') }}" class="btn btn-warning">
                            <i class="fas fa-clock me-2"></i>
                            Review Dokumen Pending ({{ $pendingCount }})
                        </a>
                        <a href="{{ route('kaprodi.signatures.urgent') }}" class="btn btn-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Dokumen Urgent ({{ $urgentCount }})
                        </a>
                        <a href="{{ route('kaprodi.blockchain.verify') }}" class="btn btn-info">
                            <i class="fas fa-shield-alt me-2"></i>
                            Verifikasi Blockchain
                        </a>
                        <a href="{{ route('kaprodi.reports.monthly') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-2"></i>
                            Laporan Bulanan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">🕒 Aktivitas Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($recentActivities as $activity)
                        <div class="list-group-item border-0 px-3 py-2">
                            <div class="d-flex align-items-start">
                                <div class="bg-{{ $activity['color'] }} bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                                    <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }} small"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 small">{{ $activity['message'] }}</p>
                                    <small class="text-muted">{{ $activity['time'] }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📊 Statistik Tanda Tangan Bulanan</h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active" data-period="month">
                                Bulan Ini
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-period="quarter">
                                3 Bulan
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-period="year">
                                Tahun Ini
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="signatureChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Blockchain Status -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">🔗 Status Blockchain</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h6>Network Status</h6>
                                <p class="text-muted mb-0">Online</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info">{{ $blockchainStats['pending_tx'] }}</div>
                                <h6>Pending Transactions</h6>
                                <p class="text-muted mb-0">Dalam antrian</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success">{{ $blockchainStats['confirmed_tx'] }}</div>
                                <h6>Confirmed</h6>
                                <p class="text-muted mb-0">Terkonfirmasi</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary">{{ $blockchainStats['gas_price'] }} Gwei</div>
                                <h6>Gas Price</h6>
                                <p class="text-muted mb-0">Biaya transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// $(document).ready(function() {
//     // Initialize signature chart
//     initializeChart();

//     // Period selector
//     $('.btn-group button').click(function() {
//         $('.btn-group button').removeClass('active');
//         $(this).addClass('active');

//         const period = $(this).data('period');
//         updateChart(period);
//     });

//     // Auto refresh stats
//     setInterval(function() {
//         refreshStats();
//     }, 30000); // Refresh every 30 seconds
// });

// function initializeChart() {
//     const ctx = document.getElementById('signatureChart').getContext('2d');

//     window.signatureChart = new Chart(ctx, {
//         type: 'line',
//         data: {
//             labels: @json($chartData['labels']),
//             datasets: [{
//                 label: 'Dokumen Ditandatangani',
//                 data: @json($chartData['signed']),
//                 borderColor: 'rgb(75, 192, 192)',
//                 backgroundColor: 'rgba(75, 192, 192, 0.1)',
//                 tension: 0.4,
//                 fill: true
//             }, {
//                 label: 'Dokumen Pending',
//                 data: @json($chartData['pending']),
//                 borderColor: 'rgb(255, 193, 7)',
//                 backgroundColor: 'rgba(255, 193, 7, 0.1)',
//                 tension: 0.4,
//                 fill: true
//             }]
//         },
//         options: {
//             responsive: true,
//             maintainAspectRatio: false,
//             plugins: {
//                 legend: {
//                     position: 'top',
//                 },
//                 title: {
//                     display: false
//                 }
//             },
//             scales: {
//                 y: {
//                     beginAtZero: true,
//                     ticks: {
//                         precision: 0
//                     }
//                 }
//             }
//         }
//     });
// }

// function updateChart(period) {
//     // Fetch new data based on period
//     $.get(`/kaprodi/api/chart-data/${period}`)
//         .done(function(data) {
//             window.signatureChart.data.labels = data.labels;
//             window.signatureChart.data.datasets[0].data = data.signed;
//             window.signatureChart.data.datasets[1].data = data.pending;
//             window.signatureChart.update();
//         });
// }

// function refreshStats() {
//     $.get('/kaprodi/api/stats')
//         .done(function(data) {
//             // Update stats cards
//             Object.keys(data).forEach(function(key) {
//                 $(`.stat-${key}`).text(data[key]);
//             });
//         });
// }

// // Auto-update blockchain status
// setInterval(function() {
//     $.get('/kaprodi/api/blockchain-status')
//         .done(function(data) {
//             // Update blockchain status indicators
//             $('.blockchain-status').html(data.html);
//         });
// }, 60000); // Every minute
</script>
@endpush
