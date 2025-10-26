{{-- resources/views/digital-signature/admin/templates/show.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Template Details')

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
                    <i class="fas fa-file-signature me-3"></i>
                    {{ $template->name }}
                </h1>
                <p class="mb-0 opacity-75">
                    <span class="badge {{ $template->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($template->status) }}
                    </span>
                    @if($template->is_default)
                        <span class="badge bg-primary ms-2">Default</span>
                    @endif
                </p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.templates.edit', $template->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="{{ route('admin.signature.templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Template Preview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Template Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-4 bg-light text-center" style="min-height: 400px;">
                        <div class="row g-4 justify-content-center">
                            @if($template->signature_image_path)
                                <div class="col-md-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <i class="fas fa-signature me-2"></i> Signature
                                        </div>
                                        <div class="card-body">
                                            <img src="{{ Storage::url($template->signature_image_path) }}"
                                                 class="img-fluid"
                                                 alt="Signature"
                                                 style="max-height: 200px;">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- @if($template->logo_path)
                                <div class="col-md-6">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-image me-2"></i> Logo
                                        </div>
                                        <div class="card-body">
                                            <img src="{{ Storage::url($template->logo_path) }}"
                                                 class="img-fluid"
                                                 alt="Logo"
                                                 style="max-height: 150px;">
                                        </div>
                                    </div>
                                </div>
                            @endif --}}

                            @if(!$template->signature_image_path && !$template->logo_path)
                                <div class="col-12">
                                    <div class="text-muted">
                                        <i class="fas fa-image fa-3x mb-3"></i>
                                        <p>No images uploaded</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Information -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Template Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">Template Name</label>
                            <div class="fs-5">{{ $template->name }}</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small">Status</label>
                            <div>
                                <span class="badge {{ $template->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($template->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small">Default</label>
                            <div>
                                @if($template->is_default)
                                    <span class="badge bg-primary">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </div>
                        </div>

                        @if($template->description)
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold text-muted small">Description</label>
                                <div>{{ $template->description }}</div>
                            </div>
                        @endif

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-muted small">Created</label>
                            <div>{{ $template->created_at->format('d M Y, H:i') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-muted small">Last Updated</label>
                            <div>{{ $template->updated_at->format('d M Y, H:i') }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-muted small">Created By</label>
                            <div>{{ $template->kaprodi->nama ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Usage Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Usage Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-4 text-primary fw-bold">{{ $template->usage_count }}</div>
                        <div class="text-muted">Times Used</div>
                    </div>

                    @if($template->last_used_at)
                        <div class="border-top pt-3">
                            <small class="text-muted d-block">Last Used</small>
                            <div class="fw-bold">{{ $template->last_used_at->format('d M Y, H:i') }}</div>
                            <small class="text-muted">{{ $template->last_used_at->diffForHumans() }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$template->is_default && $template->status === 'active')
                            <button class="btn btn-warning" onclick="setAsDefault({{ $template->id }})">
                                <i class="fas fa-star me-2"></i> Set as Default
                            </button>
                        @endif

                        <button class="btn btn-info" onclick="cloneTemplate({{ $template->id }})">
                            <i class="fas fa-copy me-2"></i> Clone Template
                        </button>

                        @if($template->status === 'active')
                            <form action="{{ route('admin.signature.templates.update', $template->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="inactive">
                                <input type="hidden" name="name" value="{{ $template->name }}">
                                <button type="submit" class="btn btn-secondary w-100">
                                    <i class="fas fa-pause me-2"></i> Deactivate
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.signature.templates.update', $template->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="active">
                                <input type="hidden" name="name" value="{{ $template->name }}">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-play me-2"></i> Activate
                                </button>
                            </form>
                        @endif

                        <button class="btn btn-danger" onclick="deleteTemplate({{ $template->id }})">
                            <i class="fas fa-trash me-2"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include delete modal from index page -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function setAsDefault(templateId) {
    if (confirm('Set this template as default?')) {
        fetch(`/admin/signature/templates/${templateId}/set-default`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Template set as default successfully!');
                location.reload();
            } else {
                alert('Failed to set template as default');
            }
        });
    }
}

function cloneTemplate(templateId) {
    const newName = prompt('Enter name for the cloned template:');
    if (newName) {
        fetch(`/admin/signature/templates/${templateId}/clone`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                new_name: newName,
                kaprodi_id: {{ Auth::id() }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Template cloned successfully!');
                window.location.href = '/admin/signature/templates/' + data.template.id;
            } else {
                alert('Failed to clone template');
            }
        });
    }
}

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/signature/templates/${templateId}`;
        form.submit();
    }
}
</script>
@endpush
