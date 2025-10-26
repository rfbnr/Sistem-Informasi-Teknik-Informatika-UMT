{{-- resources/views/digital-signature/admin/templates/edit.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Edit Signature Template')

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
                    <i class="fas fa-edit me-3"></i>
                    Edit Signature Template
                </h1>
                <p class="mb-0 opacity-75">{{ $template->name }}</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.signature.templates.update', $template->id) }}" method="POST" enctype="multipart/form-data" id="editTemplateForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Template Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Template Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $template->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" {{ $template->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $template->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $template->description) }}</textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                                   {{ $template->is_default ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                Set as default template
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-image me-2"></i>
                            Images
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="signature_image" class="form-label">Signature Image</label>
                                @if($template->signature_image_path)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($template->signature_image_path) }}"
                                             alt="Current Signature"
                                             class="img-thumbnail bg-light"
                                             style="max-width: 200px;">
                                        <div class="small text-muted mt-1">Current signature image</div>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="signature_image" name="signature_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Leave empty to keep current image. PNG/JPG, max 2MB.</small>
                                <div class="mt-2">
                                    <img id="signature_preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; display: none;">
                                </div>
                            </div>
                            {{-- <div class="col-md-6">
                                <label for="logo_image" class="form-label">Logo Image</label>
                                @if($template->logo_path)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($template->logo_path) }}"
                                             alt="Current Logo"
                                             class="img-thumbnail"
                                             style="max-width: 150px;">
                                        <div class="small text-muted mt-1">Current logo</div>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Leave empty to keep current image</small>
                                <div class="mt-2">
                                    <img id="logo_preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px; display: none;">
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>

                <!-- Usage Statistics -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Usage Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="h4 text-primary">{{ $template->usage_count }}</div>
                                <small class="text-muted">Times Used</small>
                            </div>
                            <div class="col-md-4">
                                <div class="h4 text-success">{{ $template->created_at->format('d M Y') }}</div>
                                <small class="text-muted">Created</small>
                            </div>
                            <div class="col-md-4">
                                <div class="h4 text-info">{{ $template->updated_at->diffForHumans() }}</div>
                                <small class="text-muted">Last Updated</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="col-lg-4">
                <!-- Live Preview -->
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-eye me-2"></i>
                            Preview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="template-preview" class="border rounded p-3 bg-light text-center" style="min-height: 300px;">
                            <div class="row g-3">
                                @if($template->signature_image_path)
                                    <div class="col-12 text-center">
                                        <small class="text-muted d-block mb-2">Signature</small>
                                        <img src="{{ Storage::url($template->signature_image_path) }}"
                                             class="img-fluid rounded shadow-sm bg-white"
                                             style="max-height: 150px;">
                                    </div>
                                @endif

                                {{-- @if($template->logo_path)
                                    <div class="col-12 text-center mt-3">
                                        <small class="text-muted d-block mb-2">Logo</small>
                                        <img src="{{ Storage::url($template->logo_path) }}"
                                             class="img-fluid rounded shadow-sm"
                                             style="max-height: 100px;">
                                    </div>
                                @endif --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Signature image preview
    $('#signature_image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#signature_preview').attr('src', e.target.result).show();
                updatePreview();
            };
            reader.readAsDataURL(file);
        }
    });

    // Logo image preview
    $('#logo_image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logo_preview').attr('src', e.target.result).show();
                updatePreview();
            };
            reader.readAsDataURL(file);
        }
    });

    function updatePreview() {
        const signatureImg = $('#signature_preview').attr('src');
        const logoImg = $('#logo_preview').attr('src');
        const currentSig = '{{ $template->signature_image_path ? Storage::url($template->signature_image_path) : "" }}';
        const currentLogo = '{{ $template->logo_path ? Storage::url($template->logo_path) : "" }}';

        const finalSig = signatureImg || currentSig;
        const finalLogo = logoImg || currentLogo;

        if (finalSig || finalLogo) {
            let previewHtml = '<div class="row g-3">';

            if (finalSig) {
                previewHtml += `
                    <div class="col-12 text-center">
                        <small class="text-muted d-block mb-2">Signature</small>
                        <img src="${finalSig}" class="img-fluid rounded shadow-sm" style="max-height: 150px;">
                    </div>
                `;
            }

            if (finalLogo) {
                previewHtml += `
                    <div class="col-12 text-center mt-3">
                        <small class="text-muted d-block mb-2">Logo</small>
                        <img src="${finalLogo}" class="img-fluid rounded shadow-sm" style="max-height: 100px;">
                    </div>
                `;
            }

            previewHtml += '</div>';
            $('#template-preview').html(previewHtml);
        }
    }
});
</script>
@endpush
