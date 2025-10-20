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
                            Basic Information
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
                                <label for="canvas_width" class="form-label">Width (px) *</label>
                                <input type="number" class="form-control" id="canvas_width" name="canvas_width"
                                       value="{{ old('canvas_width', $template->canvas_width) }}" min="400" max="2000" required>
                            </div>
                            <div class="col-md-4">
                                <label for="canvas_height" class="form-label">Height (px) *</label>
                                <input type="number" class="form-control" id="canvas_height" name="canvas_height"
                                       value="{{ old('canvas_height', $template->canvas_height) }}" min="300" max="1500" required>
                            </div>
                            <div class="col-md-4">
                                <label for="background_color" class="form-label">Background *</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="background_color"
                                           name="background_color" value="{{ old('background_color', $template->background_color) }}" required>
                                    <input type="text" class="form-control" id="bg_color_hex"
                                           value="{{ old('background_color', $template->background_color) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images -->
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
                                <label for="signature_image" class="form-label">Signature Image</label>
                                @if($template->signature_image_path)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($template->signature_image_path) }}" alt="Current Signature" style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">
                                        <div class="small text-muted mt-1">Current signature image</div>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="signature_image" name="signature_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Leave empty to keep current image. PNG/JPG, max 2MB.</small>
                                <div class="mt-2">
                                    <img id="signature_preview" src="" alt="Preview" style="max-width: 200px; display: none;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="logo_image" class="form-label">Logo Image</label>
                                @if($template->logo_path)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($template->logo_path) }}" alt="Current Logo" style="max-width: 150px; border: 1px solid #ddd; padding: 5px;">
                                        <div class="small text-muted mt-1">Current logo</div>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/jpeg,image/png,image/jpg">
                                <small class="text-muted">Leave empty to keep current image</small>
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
                        @php
                            $textConfig = $template->text_config;
                        @endphp
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kaprodi_name" class="form-label">Kaprodi Name *</label>
                                <input type="text" class="form-control" id="kaprodi_name" name="kaprodi_name"
                                       value="{{ old('kaprodi_name', $textConfig['kaprodi_name']['text'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="kaprodi_nidn" class="form-label">NIDN *</label>
                                <input type="text" class="form-control" id="kaprodi_nidn" name="kaprodi_nidn"
                                       value="{{ old('kaprodi_nidn', str_replace('NIDN : ', '', $textConfig['nidn']['text'] ?? '')) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kaprodi_title" class="form-label">Title/Position *</label>
                                <input type="text" class="form-control" id="kaprodi_title" name="kaprodi_title"
                                       value="{{ old('kaprodi_title', $textConfig['title']['text'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="institution_name" class="form-label">Institution *</label>
                                <input type="text" class="form-control" id="institution_name" name="institution_name"
                                       value="{{ old('institution_name', $textConfig['institution']['text'] ?? '') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Statistics -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
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
                            Live Preview
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div
