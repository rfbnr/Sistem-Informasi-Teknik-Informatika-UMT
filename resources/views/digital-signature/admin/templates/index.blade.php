{{-- resources/views/digital-signature/admin/templates/index.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Signature Templates')

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
                    <i class="fas fa-palette me-3"></i>
                    Signature Templates
                </h1>
                <p class="mb-0 opacity-75">Manage signature layout templates</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.templates.create') }}" class="btn btn-warning">
                    <i class="fas fa-plus me-1"></i> Create Template
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-primary">{{ $statistics['total_templates'] }}</div>
                <div class="text-muted">Total Templates</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success">{{ $statistics['active_templates'] }}</div>
                <div class="text-muted">Active</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-warning">{{ $statistics['inactive_templates'] }}</div>
                <div class="text-muted">Inactive</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-info">{{ $statistics['default_templates'] }}</div>
                <div class="text-muted">Default</div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.signature.templates.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">
                            <i class="fas fa-search me-1"></i> Search Templates
                        </label>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by name or description...">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">
                            <i class="fas fa-toggle-on me-1"></i> Status
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="is_default" class="form-label">
                            <i class="fas fa-star me-1"></i> Default
                        </label>
                        <select class="form-select" id="is_default" name="is_default">
                            <option value="">All Templates</option>
                            <option value="1" {{ request('is_default') === '1' ? 'selected' : '' }}>Default Only</option>
                            <option value="0" {{ request('is_default') === '0' ? 'selected' : '' }}>Non-Default</option>
                        </select>
                    </div>
                    {{-- <div class="col-md-2">
                        <label for="sort_by" class="form-label">
                            <i class="fas fa-sort me-1"></i> Sort By
                        </label>
                        <select class="form-select" id="sort_by" name="sort_by">
                            <option value="created_at_desc" {{ request('sort_by', 'created_at_desc') === 'created_at_desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="created_at_asc" {{ request('sort_by') === 'created_at_asc' ? 'selected' : '' }}>Oldest First</option>
                            <option value="name_asc" {{ request('sort_by') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ request('sort_by') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="usage_desc" {{ request('sort_by') === 'usage_desc' ? 'selected' : '' }}>Most Used</option>
                            <option value="usage_asc" {{ request('sort_by') === 'usage_asc' ? 'selected' : '' }}>Least Used</option>
                        </select>
                    </div> --}}
                    <div class="col-md-2">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply
                            </button>
                            <a href="{{ route('admin.signature.templates.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Info -->
    @if(request()->hasAny(['search', 'status', 'is_default', 'sort_by']))
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Showing {{ $templates->total() }} result(s)</strong>
        @if(request('search'))
            for search: <strong>"{{ request('search') }}"</strong>
        @endif
        @if(request('status'))
            | Status: <strong>{{ ucfirst(request('status')) }}</strong>
        @endif
        @if(request('is_default') !== null)
            | {{ request('is_default') === '1' ? 'Default Only' : 'Non-Default Only' }}
        @endif
    </div>
    @endif

    <!-- Templates Grid -->
    <div class="row">
        @forelse($templates as $template)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 {{ $template->is_default ? 'border-primary' : '' }}"
                 data-template-id="{{ $template->id }}"
                 data-template-name="{{ $template->name }}"
                 data-template-usage="{{ $template->usage_count }}">
                @if($template->is_default)
                    <div class="ribbon ribbon-top-right">
                        <span class="bg-primary">Default</span>
                    </div>
                @endif

                <div class="card-header {{ $template->is_default ? 'bg-primary text-white' : 'bg-light' }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $template->name }}</h5>
                        <span class="badge {{ $template->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($template->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Template Preview -->
                    <div class="template-preview mb-3 p-3 bg-light border rounded text-center" style="height: 200px; display: flex; align-items: center; justify-content: center; gap: 20px;">
                        @if($template->signature_image_path)
                            <div class="text-center">
                                <img src="{{ Storage::url($template->signature_image_path) }}"
                                     alt="Signature"
                                     class="img-fluid rounded shadow-sm bg-white"
                                     style="max-width: 180px; max-height: 140px;">
                                <div class="mt-1"><small class="text-muted">Signature</small></div>
                            </div>
                        @endif

                        {{-- @if($template->logo_path)
                            <div class="text-center">
                                <img src="{{ Storage::url($template->logo_path) }}"
                                     alt="Logo"
                                     class="img-fluid rounded shadow-sm"
                                     style="max-width: 80px; max-height: 80px;">
                                <div class="mt-1"><small class="text-muted">Logo</small></div>
                            </div>
                        @endif --}}

                        @if(!$template->signature_image_path && !$template->logo_path)
                            <div class="text-muted">
                                <i class="fas fa-image fa-2x mb-2"></i>
                                <p class="mb-0 small">No images</p>
                            </div>
                        @endif
                    </div>

                    @if($template->description)
                        <p class="text-muted small mb-3">{{ Str::limit($template->description, 100) }}</p>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted">
                            <i class="fas fa-ruler-combined me-1"></i>
                            {{ $template->canvas_width }}x{{ $template->canvas_height }}
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-file-signature me-1"></i>
                            Used {{ $template->usage_count }} times
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.signature.templates.show', $template->id) }}"
                           class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.signature.templates.edit', $template->id) }}"
                           class="btn btn-sm btn-outline-warning flex-fill">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <div class="btn-group flex-fill">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                @if(!$template->is_default && $template->status === 'active')
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="setAsDefault({{ $template->id }})">
                                            <i class="fas fa-star text-warning me-2"></i>Set as Default
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="#" onclick="cloneTemplate({{ $template->id }})">
                                        <i class="fas fa-copy text-info me-2"></i>Clone
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteTemplate({{ $template->id }})">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-muted small">
                    <i class="fas fa-clock me-1"></i>
                    Created {{ $template->created_at->diffForHumans() }}
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-palette fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Templates Found</h4>
                    <p class="text-muted">Create your first signature template to get started</p>
                    <a href="{{ route('admin.signature.templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Create First Template
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
        <div class="mt-4">
            {{ $templates->links() }}
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Delete Template
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST" onsubmit="return confirmDelete(event)">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>

                    <p class="mb-3">You are about to delete:</p>
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="mb-1" id="deleteTemplateName"></h6>
                            <small class="text-muted">
                                <i class="fas fa-chart-line me-1"></i>
                                Used <span id="deleteTemplateUsage"></span> times
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirmTemplateName" class="form-label">
                            <strong>Type the template name to confirm:</strong>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="confirmTemplateName"
                               placeholder="Enter template name"
                               autocomplete="off"
                               required>
                        <small class="text-muted">This helps prevent accidental deletion</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn" disabled>
                        <i class="fas fa-trash me-1"></i> Yes, Delete Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ribbon {
    position: absolute;
    overflow: hidden;
    width: 100px;
    height: 100px;
    z-index: 10;
}

.ribbon-top-right {
    top: -3px;
    right: -3px;
}

.ribbon span {
    position: absolute;
    display: block;
    width: 150px;
    padding: 8px 0;
    box-shadow: 0 2px 5px rgba(0,0,0,.2);
    color: #fff;
    font-size: 0.75rem;
    font-weight: bold;
    text-shadow: 0 1px 1px rgba(0,0,0,.2);
    text-transform: uppercase;
    text-align: center;
    right: -35px;
    top: 20px;
    transform: rotate(45deg);
}

.template-preview {
    overflow: hidden;
}
</style>
@endpush

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
                location.reload();
            } else {
                alert('Failed to clone template');
            }
        });
    }
}

let currentTemplateName = '';
let currentTemplateData = {};

function deleteTemplate(templateId) {
    // Find template data from the page
    const templateCard = document.querySelector(`[data-template-id="${templateId}"]`);
    if (templateCard) {
        currentTemplateData = {
            id: templateId,
            name: templateCard.dataset.templateName || '',
            usage: templateCard.dataset.templateUsage || '0'
        };
    } else {
        // Fallback: extract from card element
        currentTemplateData = {
            id: templateId,
            name: 'Unknown Template',
            usage: '0'
        };
    }

    currentTemplateName = currentTemplateData.name;

    // Update modal content
    document.getElementById('deleteTemplateName').textContent = currentTemplateData.name;
    document.getElementById('deleteTemplateUsage').textContent = currentTemplateData.usage;

    // Reset form
    document.getElementById('confirmTemplateName').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;

    // Set form action
    const form = document.getElementById('deleteForm');
    form.action = `/admin/signature/templates/${templateId}`;

    // Show modal
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Enable delete button only when template name matches
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmTemplateName');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            if (this.value.trim() === currentTemplateName.trim()) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('btn-danger');
                confirmBtn.classList.add('btn-success');
            } else {
                confirmBtn.disabled = true;
                confirmBtn.classList.remove('btn-success');
                confirmBtn.classList.add('btn-danger');
            }
        });
    }
});

function confirmDelete(event) {
    const confirmInput = document.getElementById('confirmTemplateName');
    if (confirmInput.value.trim() !== currentTemplateName.trim()) {
        event.preventDefault();
        alert('Template name does not match. Please type the exact template name.');
        return false;
    }
    return true;
}
</script>
@endpush
