{{-- resources/views/digital-signature/admin/templates/create.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Create Signature Template')

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
                    <i class="fas fa-plus-circle me-3"></i>
                    Create Signature Template
                </h1>
                <p class="mb-0 opacity-75">Create a new signature template for document signing</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Templates
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.signature.templates.store') }}" method="POST" enctype="multipart/form-data" id="createTemplateForm">
        @csrf
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
                        <div class="mb-3">
                            <label for="name" class="form-label">Template Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g., Official Signature 2025" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Brief description of this template...">{{ old('description') }}</textarea>
                            <small class="text-muted">Optional: Describe when to use this template</small>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                Set as default template
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Signature & Logo Upload -->
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
                                <label for="signature_image" class="form-label">Signature Image *</label>
                                <input type="file" class="form-control @error('signature_image') is-invalid @enderror"
                                       id="signature_image" name="signature_image" accept="image/jpeg,image/png,image/jpg" required>
                                <small class="text-muted">PNG/JPG, max 2MB. Transparent background recommended.</small>
                                @error('signature_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mt-2">
                                    <img id="signature_preview" src="" alt="Preview" class="img-thumbnail bg-light" style="max-width: 200px; display: none;">
                                </div>
                            </div>
                            {{-- <div class="col-md-6">
                                <label for="logo_image" class="form-label">Logo/Institution Image</label>
                                <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Optional. PNG/JPG, max 2MB.</small>
                                <div class="mt-2">
                                    <img id="logo_preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px; display: none;">
                                </div>
                            </div> --}}
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
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <div>
                                    <i class="fas fa-image fa-3x mb-3"></i>
                                    <p class="mb-0">Upload images to see preview</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i> Create Template
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i> Reset Form
                            </button>
                            <a href="{{ route('admin.signature.templates.index') }}" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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

        if (signatureImg || logoImg) {
            let previewHtml = '<div class="row g-3">';

            if (signatureImg) {
                previewHtml += `
                    <div class="col-12 text-center">
                        <small class="text-muted d-block mb-2">Signature</small>
                        <img src="${signatureImg}" class="img-fluid rounded shadow-sm bg-white" style="max-height: 150px;">
                    </div>
                `;
            }

            if (logoImg) {
                previewHtml += `
                    <div class="col-12 text-center mt-3">
                        <small class="text-muted d-block mb-2">Logo</small>
                        <img src="${logoImg}" class="img-fluid rounded shadow-sm" style="max-height: 100px;">
                    </div>
                `;
            }

            previewHtml += '</div>';
            $('#template-preview').html(previewHtml);
        }
    }
});

function resetForm() {
    if (confirm('Reset all fields? This cannot be undone.')) {
        document.getElementById('createTemplateForm').reset();
        $('#signature_preview, #logo_preview').hide();
        $('#template-preview').html(`
            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                <div>
                    <i class="fas fa-image fa-3x mb-3"></i>
                    <p class="mb-0">Upload images to see preview</p>
                </div>
            </div>
        `);
    }
}

// Form validation
$('#createTemplateForm').on('submit', function(e) {
    const signatureFile = $('#signature_image')[0].files[0];
    if (!signatureFile) {
        e.preventDefault();
        alert('Please upload a signature image');
        return false;
    }
});
</script>
@endpush
