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

    <!-- Templates Grid -->
    <div class="row">
        @forelse($templates as $template)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 {{ $template->is_default ? 'border-primary' : '' }}">
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
                    <div class="template-preview mb-3 p-3 border rounded" style="background: {{ $template->background_color }}; height: 200px; position: relative;">
                        @if($template->signature_image_path)
                            <img src="{{ Storage::url($template->signature_image_path) }}"
                                 alt="Signature"
                                 style="max-width: 150px; max-height: 80px; position: absolute; top: 20px; left: 20px;">
                        @endif

                        @if($template->logo_path)
                            <img src="{{ Storage::url($template->logo_path) }}"
                                 alt="Logo"
                                 style="max-width: 80px; max-height: 80px; position: absolute; top: 20px; right: 20px;">
                        @endif

                        <div style="position: absolute; bottom: 20px; left: 20px;">
                            <small style="font-size: 0.75rem;">
                                <strong>{{ $template->text_config['kaprodi_name']['text'] ?? 'Kaprodi Name' }}</strong><br>
                                {{ $template->text_config['nidn']['text'] ?? 'NIDN' }}<br>
                                {{ $template->text_config['institution']['text'] ?? 'Institution' }}
                            </small>
                        </div>
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
                <h5 class="modal-title">Delete Template</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this template?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
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

function deleteTemplate(templateId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/signature/templates/${templateId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
