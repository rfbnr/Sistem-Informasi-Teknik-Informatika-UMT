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
                <p class="mb-0 opacity-75">Design a new signature layout template</p>
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
                            Basic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Template Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="e.g., Official Document Signature 2025" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
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

                <!-- Canvas Configuration -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-ruler-combined me-2"></i>
                            Canvas Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="canvas_width" class="form-label">Canvas Width (px) *</label>
                                <input type="number" class="form-control" id="canvas_width" name="canvas_width"
                                       value="{{ old('canvas_width', 800) }}" min="400" max="2000" required>
                            </div>
                            <div class="col-md-4">
                                <label for="canvas_height" class="form-label">Canvas Height (px) *</label>
                                <input type="number" class="form-control" id="canvas_height" name="canvas_height"
                                       value="{{ old('canvas_height', 600) }}" min="300" max="1500" required>
                            </div>
                            <div class="col-md-4">
                                <label for="background_color" class="form-label">Background Color *</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="background_color"
                                           name="background_color" value="{{ old('background_color', '#ffffff') }}" required>
                                    <input type="text" class="form-control" id="bg_color_hex"
                                           value="{{ old('background_color', '#ffffff') }}" placeholder="#ffffff">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Recommended:</strong> Width: 800px, Height: 600px for optimal viewing
                        </div>
                    </div>
                </div>

                <!-- Signature & Logo Upload -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
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
                                    <img id="signature_preview" src="" alt="Preview" style="max-width: 200px; display: none;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="logo_image" class="form-label">Logo/Institution Image</label>
                                <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Optional. PNG/JPG, max 2MB.</small>
                                <div class="mt-2">
                                    <img id="logo_preview" src="" alt="Preview" style="max-width: 150px; display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Text Configuration -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-font me-2"></i>
                            Text Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kaprodi_name" class="form-label">Kaprodi Name *</label>
                                <input type="text" class="form-control @error('kaprodi_name') is-invalid @enderror"
                                       id="kaprodi_name" name="kaprodi_name" value="{{ old('kaprodi_name') }}"
                                       placeholder="e.g., Dr. John Doe, M.Kom" required>
                                @error('kaprodi_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="kaprodi_nidn" class="form-label">NIDN *</label>
                                <input type="text" class="form-control @error('kaprodi_nidn') is-invalid @enderror"
                                       id="kaprodi_nidn" name="kaprodi_nidn" value="{{ old('kaprodi_nidn') }}"
                                       placeholder="e.g., 0419038004" required>
                                @error('kaprodi_nidn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kaprodi_title" class="form-label">Title/Position *</label>
                                <input type="text" class="form-control @error('kaprodi_title') is-invalid @enderror"
                                       id="kaprodi_title" name="kaprodi_title" value="{{ old('kaprodi_title') }}"
                                       placeholder="e.g., Ketua Program Studi Teknik Informatika" required>
                                @error('kaprodi_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="institution_name" class="form-label">Institution *</label>
                                <input type="text" class="form-control @error('institution_name') is-invalid @enderror"
                                       id="institution_name" name="institution_name" value="{{ old('institution_name') }}"
                                       placeholder="e.g., Universitas Muhammadiyah Tangerang" required>
                                @error('institution_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                            Live Preview
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="template-preview" style="width: 100%; height: 400px; overflow: hidden; position: relative; background: #ffffff;">
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <div class="text-center">
                                    <i class="fas fa-paint-brush fa-3x mb-3"></i>
                                    <p>Preview will appear here</p>
                                    <small>Upload images to see preview</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            This is a simplified preview. Actual layout may vary.
                        </small>
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
    // Color picker sync
    $('#background_color').on('input', function() {
        $('#bg_color_hex').val($(this).val());
        updatePreview();
    });

    $('#bg_color_hex').on('input', function() {
        const color = $(this).val();
        if (/^#[0-9A-F]{6}$/i.test(color)) {
            $('#background_color').val(color);
            updatePreview();
        }
    });

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

    // Update preview on text change
    $('#kaprodi_name, #kaprodi_nidn, #kaprodi_title, #institution_name').on('input', updatePreview);
    $('#canvas_width, #canvas_height').on('input', updatePreview);

    function updatePreview() {
        const bgColor = $('#background_color').val();
        const width = $('#canvas_width').val() || 800;
        const height = $('#canvas_height').val() || 600;
        const signatureSrc = $('#signature_preview').attr('src');
        const logoSrc = $('#logo_preview').attr('src');
        const kaprodiName = $('#kaprodi_name').val();
        const nidn = $('#kaprodi_nidn').val();
        const title = $('#kaprodi_title').val();
        const institution = $('#institution_name').val();

        // Calculate scale to fit preview
        const previewWidth = $('#template-preview').width();
        const scale = previewWidth / width;
        const previewHeight = height * scale;

        let previewHtml = '';

        if (signatureSrc || logoSrc || kaprodiName) {
            previewHtml = `
                <div style="width: 100%; height: ${previewHeight}px; background: ${bgColor}; position: relative; padding: 20px;">
                    ${signatureSrc ? `<img src="${signatureSrc}" style="position: absolute; left: 30px; top: 30px; max-width: ${150 * scale}px; max-height: ${80 * scale}px;">` : ''}
                    ${logoSrc ? `<img src="${logoSrc}" style="position: absolute; right: 30px; top: 30px; max-width: ${100 * scale}px; max-height: ${100 * scale}px;">` : ''}
                    <div style="position: absolute; left: 30px; bottom: 30px; font-size: ${12 * scale}px;">
                        ${kaprodiName ? `<div style="font-weight: bold; margin-bottom: 5px;">${kaprodiName}</div>` : ''}
                        ${nidn ? `<div style="margin-bottom: 3px;">NIDN: ${nidn}</div>` : ''}
                        ${title ? `<div style="margin-bottom: 3px;">${title}</div>` : ''}
                        ${institution ? `<div style="color: #666;">${institution}</div>` : ''}
                    </div>
                </div>
            `;
        } else {
            previewHtml = `
                <div class="d-flex align-items-center justify-content-center h-100 text-muted" style="background: ${bgColor};">
                    <div class="text-center">
                        <i class="fas fa-paint-brush fa-3x mb-3"></i>
                        <p>Upload images and fill details to see preview</p>
                    </div>
                </div>
            `;
        }

        $('#template-preview').html(previewHtml);
    }
});

function resetForm() {
    if (confirm('Reset all fields? This cannot be undone.')) {
        document.getElementById('createTemplateForm').reset();
        $('#signature_preview, #logo_preview').hide();
        $('#template-preview').html(`
            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                <div class="text-center">
                    <i class="fas fa-paint-brush fa-3x mb-3"></i>
                    <p>Preview will appear here</p>
                    <small>Upload images to see preview</small>
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

    // Show loading
    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Creating...');
});
</script>
@endpush
