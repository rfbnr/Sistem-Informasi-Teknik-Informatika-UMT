{{-- resources/views/digital-signature/admin/key-management.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Digital Signature Key Management')

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
                    <i class="fas fa-key me-3"></i>
                    Digital Signature Key Management
                </h1>
                <p class="mb-0 opacity-75">Manage cryptographic keys for digital signatures</p>
            </div>
            <div class="col-lg-4 text-end">
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#createKeyModal">
                    <i class="fas fa-plus me-1"></i> Create New Key
                </button>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.signature.keys.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search by signature ID or purpose"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="algorithm">
                        <option value="">All Algorithms</option>
                        <option value="RSA-SHA256">RSA-SHA256</option>
                        <option value="RSA-SHA512">RSA-SHA512</option>
                        <option value="ECDSA">ECDSA</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $signatures->where('status', 'active')->count() }}</div>
                <div class="text-muted">Active Keys</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-warning">{{ $signatures->where('valid_until', '<', now()->addDays(30))->count() }}</div>
                <div class="text-muted">Expiring Soon</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-danger">{{ $signatures->where('status', 'expired')->count() }}</div>
                <div class="text-muted">Expired</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-info">{{ $signatures->total() }}</div>
                <div class="text-muted">Total Keys</div>
            </div>
        </div>
    </div>

    <!-- Keys Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Digital Signature Keys
            </h5>
        </div>
        <div class="card-body">
            @if($signatures->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Signature ID</th>
                                <th>Algorithm</th>
                                <th>Key Length</th>
                                <th>Purpose</th>
                                <th>Created</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th>Usage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($signatures as $signature)
                            <tr>
                                <td>
                                    <code>{{ Str::limit($signature->signature_id, 20) }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $signature->algorithm }}</span>
                                </td>
                                <td>{{ $signature->key_length }} bits</td>
                                <td>{{ Str::limit($signature->purpose, 30) }}</td>
                                <td>{{ $signature->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="{{ $signature->valid_until->isPast() ? 'text-danger' : ($signature->valid_until->diffInDays() < 30 ? 'text-warning' : '') }}">
                                        {{ $signature->valid_until->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($signature->status) }}">
                                        {{ ucfirst($signature->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $signature->documentSignatures->count() }} docs
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.signature.keys.view', $signature->id) }}"
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($signature->status === 'active')
                                            <button class="btn btn-outline-danger"
                                                    onclick="revokeKey({{ $signature->id }})"
                                                    title="Revoke Key">
                                                <i class="fas fa-ban"></i>
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
                <div class="mt-3">
                    {{ $signatures->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-key fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Digital Signature Keys</h5>
                    <p class="text-muted">Create your first signature key to get started</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKeyModal">
                        <i class="fas fa-plus me-1"></i> Create Key
                    </button>
                </div>
            @endif
        </div>
    </div>
{{-- </div> --}}

<!-- Create Key Modal -->
<div class="modal fade" id="createKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Digital Signature Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.signature.keys.store') }}" method="POST" id="createKeyForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose *</label>
                        <input type="text" class="form-control" id="purpose" name="purpose"
                               placeholder="e.g., Document signing for 2025" required>
                    </div>
                    <div class="mb-3">
                        <label for="key_length" class="form-label">Key Length *</label>
                        <select class="form-select" id="key_length" name="key_length" required>
                            <option value="2048">2048 bits (Standard)</option>
                            <option value="3072">3072 bits (Enhanced)</option>
                            <option value="4096" selected>4096 bits (Maximum Security)</option>
                        </select>
                        <small class="text-muted">Higher key length = stronger security</small>
                    </div>
                    <div class="mb-3">
                        <label for="validity_years" class="form-label">Validity Period *</label>
                        <select class="form-select" id="validity_years" name="validity_years" required>
                            <option value="1">1 Year</option>
                            <option value="2" selected>2 Years</option>
                            <option value="3">3 Years</option>
                            <option value="5">5 Years</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Key generation may take a few seconds depending on key length.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createKeyBtn">
                        <i class="fas fa-key me-1"></i> Generate Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
