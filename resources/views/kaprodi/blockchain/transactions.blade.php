@extends('kaprodi.layouts.app')

@section('title', 'Blockchain Transactions')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-info">
                                <i class="fas fa-link me-2"></i>Blockchain Transactions
                            </h4>
                            <p class="text-muted mb-0">Monitor dan verifikasi transaksi blockchain untuk integritas dokumen</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="blockchain-status-indicator bg-success rounded-circle" style="width: 12px; height: 12px;"></div>
                                <span class="small text-muted">Network Online</span>
                            </div>
                            <button class="btn btn-info" onclick="refreshTransactions()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Status Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $stats['confirmed'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Confirmed</p>
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
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-cube text-primary fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $stats['total_blocks'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Total Blocks</p>
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
                            <i class="fas fa-gas-pump text-info fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="h4 mb-0">{{ $stats['avg_gas_price'] ?? 0 }}</h3>
                            <p class="text-muted mb-0 small">Avg Gas (Gwei)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" id="statusFilter">
                                        <option value="">Semua Status</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm" id="typeFilter">
                                        <option value="">Semua Tipe</option>
                                        <option value="document_hash">Document Hash</option>
                                        <option value="signature">Signature</option>
                                        <option value="verification">Verification</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" class="form-control form-control-sm" id="dateFilter"
                                           value="{{ request('date', now()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm" id="searchFilter"
                                           placeholder="Cari hash...">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="exportTransactions()">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                                <button class="btn btn-outline-info" onclick="verifyAllIntegrity()">
                                    <i class="fas fa-shield-alt me-1"></i>Verify All
                                </button>
                                <button class="btn btn-outline-secondary" onclick="showNetworkInfo()">
                                    <i class="fas fa-info-circle me-1"></i>Network Info
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">🔗 Transaksi Blockchain</h5>
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
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">Transaction</th>
                                        <th class="border-0 py-3">Document</th>
                                        <th class="border-0 py-3">Type</th>
                                        <th class="border-0 py-3">Status</th>
                                        <th class="border-0 py-3">Gas Used</th>
                                        <th class="border-0 py-3">Timestamp</th>
                                        <th class="border-0 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                    <tr class="transaction-row"
                                        data-status="{{ $transaction->status }}"
                                        data-type="{{ $transaction->transaction_type }}"
                                        data-date="{{ $transaction->created_at->format('Y-m-d') }}">
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info bg-opacity-10 rounded p-2 me-3">
                                                    <i class="fas fa-link text-info"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <code class="small">{{ Str::limit($transaction->transaction_hash, 16) }}...</code>
                                                        <button class="btn btn-sm btn-outline-secondary ms-1"
                                                                onclick="copyToClipboard('{{ $transaction->transaction_hash }}')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </h6>
                                                    <small class="text-muted">Block: {{ $transaction->block_number ?? 'Pending' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            @if($transaction->document)
                                                <div>
                                                    <div class="fw-medium">{{ Str::limit($transaction->document->title, 30) }}</div>
                                                    <small class="text-muted">{{ $transaction->document->file_name }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $typeConfig = [
                                                    'document_hash' => ['badge' => 'bg-primary', 'text' => 'Document Hash'],
                                                    'signature' => ['badge' => 'bg-success', 'text' => 'Signature'],
                                                    'verification' => ['badge' => 'bg-info', 'text' => 'Verification'],
                                                ];
                                                $config = $typeConfig[$transaction->transaction_type] ?? ['badge' => 'bg-secondary', 'text' => ucfirst($transaction->transaction_type)];
                                            @endphp
                                            <span class="badge {{ $config['badge'] }}">{{ $config['text'] }}</span>
                                        </td>
                                        <td class="py-3">
                                            @php
                                                $statusConfig = [
                                                    'confirmed' => ['badge' => 'bg-success', 'text' => 'Confirmed', 'icon' => 'check-circle'],
                                                    'pending' => ['badge' => 'bg-warning', 'text' => 'Pending', 'icon' => 'clock'],
                                                    'failed' => ['badge' => 'bg-danger', 'text' => 'Failed', 'icon' => 'times-circle'],
                                                ];
                                                $config = $statusConfig[$transaction->status] ?? ['badge' => 'bg-secondary', 'text' => ucfirst($transaction->status), 'icon' => 'question'];
                                            @endphp
                                            <span class="badge {{ $config['badge'] }}">
                                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                            </span>
                                            @if($transaction->status === 'confirmed' && $transaction->confirmations)
                                                <div class="small text-muted">{{ $transaction->confirmations }} confirmations</div>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($transaction->gas_used)
                                                <div>
                                                    <div class="fw-medium">{{ number_format($transaction->gas_used) }}</div>
                                                    @if($transaction->gas_price)
                                                        <small class="text-muted">{{ $transaction->gas_price }} Gwei</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            <div>
                                                <div class="fw-medium">{{ $transaction->created_at->format('d M Y') }}</div>
                                                <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                                            </div>
                                            <div class="small text-muted">{{ $transaction->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="py-3">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary"
                                                        onclick="viewTransactionDetails('{{ $transaction->id }}')"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($transaction->transaction_hash)
                                                    <a href="https://polygonscan.com/tx/{{ $transaction->transaction_hash }}"
                                                       target="_blank" class="btn btn-outline-info" title="View on Explorer">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                                @if($transaction->status === 'confirmed')
                                                    <button class="btn btn-outline-success"
                                                            onclick="verifyTransaction('{{ $transaction->id }}')"
                                                            title="Verify">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($transactions->hasPages())
                            <div class="card-footer bg-white border-top">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-link fa-3x text-muted mb-3"></i>
                            <h6>Belum Ada Transaksi</h6>
                            <p class="text-muted">Belum ada transaksi blockchain yang tercatat</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi Blockchain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="transactionDetails">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">Loading transaction details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadTransactionReport()">
                    <i class="fas fa-download me-2"></i>Download Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Network Info Modal -->
<div class="modal fade" id="networkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Network Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Network:</strong>
                    </div>
                    <div class="col-sm-6">
                        Polygon Mainnet
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Chain ID:</strong>
                    </div>
                    <div class="col-sm-6">
                        137
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Current Block:</strong>
                    </div>
                    <div class="col-sm-6">
                        <span id="currentBlock">Loading...</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Gas Price:</strong>
                    </div>
                    <div class="col-sm-6">
                        <span id="currentGasPrice">Loading...</span> Gwei
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <strong>Contract Address:</strong>
                    </div>
                    <div class="col-sm-6">
                        <code class="small">{{ config('blockchain.contract_address', 'Not configured') }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize filters
    initializeFilters();

    // Auto-refresh if enabled
    setInterval(function() {
        if ($('#autoRefresh').is(':checked')) {
            refreshTransactionData();
        }
    }, 30000); // Every 30 seconds

    // Update network status
    updateNetworkStatus();
    setInterval(updateNetworkStatus, 60000); // Every minute
});

function initializeFilters() {
    $('#statusFilter, #typeFilter, #dateFilter').change(function() {
        filterTransactions();
    });

    $('#searchFilter').on('keyup', function() {
        filterTransactions();
    });
}

function filterTransactions() {
    const statusFilter = $('#statusFilter').val();
    const typeFilter = $('#typeFilter').val();
    const dateFilter = $('#dateFilter').val();
    const searchFilter = $('#searchFilter').val().toLowerCase();

    $('.transaction-row').each(function() {
        const $row = $(this);
        const status = $row.data('status');
        const type = $row.data('type');
        const date = $row.data('date');
        const text = $row.text().toLowerCase();

        let show = true;

        if (statusFilter && status !== statusFilter) show = false;
        if (typeFilter && type !== typeFilter) show = false;
        if (dateFilter && date !== dateFilter) show = false;
        if (searchFilter && text.indexOf(searchFilter) === -1) show = false;

        $row.toggle(show);
    });

    updateFilterStats();
}

function updateFilterStats() {
    const visibleRows = $('.transaction-row:visible').length;
    const totalRows = $('.transaction-row').length;

    if (visibleRows !== totalRows) {
        showToast(`Menampilkan ${visibleRows} dari ${totalRows} transaksi`, 'info');
    }
}

function refreshTransactions() {
    showToast('Memperbarui data transaksi...', 'info');

    // Add loading state
    $('.card-header h5').html('<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui Transaksi...');

    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

function refreshTransactionData() {
    $.get('{{ route('kaprodi.api.blockchain-status') }}')
        .done(function(data) {
            // Update stats cards
            if (data.stats) {
                Object.keys(data.stats).forEach(key => {
                    $(`.stat-${key}`).text(data.stats[key]);
                });
            }

            // Update network indicator
            if (data.network_status === 'online') {
                $('.blockchain-status-indicator').removeClass('bg-danger').addClass('bg-success');
            } else {
                $('.blockchain-status-indicator').removeClass('bg-success').addClass('bg-danger');
            }
        });
}

function viewTransactionDetails(transactionId) {
    $('#transactionModal').modal('show');

    $.get(`/kaprodi/api/transactions/${transactionId}`)
        .done(function(data) {
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium">Transaction Hash:</td>
                                <td><code class="small">${data.transaction_hash}</code></td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Block Number:</td>
                                <td>${data.block_number || 'Pending'}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Status:</td>
                                <td><span class="badge bg-success">${data.status}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Gas Used:</td>
                                <td>${data.gas_used ? number_format(data.gas_used) : 'N/A'}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Gas Price:</td>
                                <td>${data.gas_price || 'N/A'} Gwei</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-medium">Type:</td>
                                <td><span class="badge bg-primary">${data.transaction_type}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Timestamp:</td>
                                <td>${data.created_at}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Confirmations:</td>
                                <td>${data.confirmations || 0}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Document:</td>
                                <td>${data.document ? data.document.title : 'N/A'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;

            if (data.metadata) {
                html += `
                    <hr>
                    <h6>Metadata:</h6>
                    <pre class="bg-light p-3 rounded"><code>${JSON.stringify(data.metadata, null, 2)}</code></pre>
                `;
            }

            $('#transactionDetails').html(html);
        })
        .fail(function() {
            $('#transactionDetails').html('<div class="alert alert-danger">Gagal memuat detail transaksi</div>');
        });
}

function verifyTransaction(transactionId) {
    showToast('Memverifikasi transaksi...', 'info');

    $.post(`/kaprodi/api/transactions/${transactionId}/verify`)
        .done(function(data) {
            if (data.verified) {
                showToast('✅ Transaksi berhasil diverifikasi', 'success');
            } else {
                showToast('❌ Transaksi gagal verifikasi', 'danger');
            }
        })
        .fail(function() {
            showToast('Gagal memverifikasi transaksi', 'danger');
        });
}

function exportTransactions() {
    const filters = {
        status: $('#statusFilter').val(),
        type: $('#typeFilter').val(),
        date: $('#dateFilter').val(),
        search: $('#searchFilter').val()
    };

    const params = new URLSearchParams(filters);
    window.open(`/kaprodi/blockchain/transactions/export?${params.toString()}`, '_blank');

    showToast('Mengexport data transaksi...', 'info');
}

function verifyAllIntegrity() {
    if (!confirm('Verifikasi semua transaksi? Proses ini mungkin memerlukan waktu lama.')) {
        return;
    }

    showToast('Memverifikasi integritas semua transaksi...', 'info');

    $.post('/kaprodi/api/verify-all-transactions')
        .done(function(data) {
            showToast(`Verifikasi selesai: ${data.verified}/${data.total} transaksi valid`, 'success');
        })
        .fail(function() {
            showToast('Gagal memverifikasi transaksi', 'danger');
        });
}

function showNetworkInfo() {
    $('#networkModal').modal('show');
    updateNetworkDetails();
}

function updateNetworkDetails() {
    $.get('/kaprodi/api/network-info')
        .done(function(data) {
            $('#currentBlock').text(data.current_block || 'Unknown');
            $('#currentGasPrice').text(data.gas_price || 'Unknown');
        });
}

function updateNetworkStatus() {
    $.get('/kaprodi/api/network-status')
        .done(function(data) {
            if (data.online) {
                $('.blockchain-status-indicator').removeClass('bg-danger').addClass('bg-success');
            } else {
                $('.blockchain-status-indicator').removeClass('bg-success').addClass('bg-danger');
            }
        });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Hash berhasil disalin!', 'success');
    });
}

function downloadTransactionReport() {
    // Implementation for downloading transaction report
    showToast('Mengunduh laporan transaksi...', 'info');
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

function number_format(number) {
    return new Intl.NumberFormat().format(number);
}
</script>

<style>
.blockchain-status-indicator {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.transaction-row:hover {
    background-color: rgba(0,0,0,0.02);
}

.table code {
    background-color: rgba(0,0,0,0.05);
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.85em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.form-select-sm, .form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

pre {
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endpush