{{-- @extends('digital-signature.layouts.app')

@section('title', 'Create Signature Key')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="mb-0">Create New Digital Signature Key</h2>
                    <p class="text-muted mb-0">Generate a new RSA key pair for digital signing</p>
                </div>
            </div>

            <!-- Create Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.signature.keys.store') }}" method="POST" id="createKeyForm">
                        @csrf

                        <!-- Purpose -->
                        <div class="mb-4">
                            <label for="purpose" class="form-label fw-bold">
                                Purpose <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('purpose') is-invalid @enderror"
                                   id="purpose"
                                   name="purpose"
                                   value="{{ old('purpose') }}"
                                   placeholder="e.g., Document Approval Signatures 2025"
                                   required>
                            <small class="text-muted">Describe the intended use of this signature key</small>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Validity Period -->
                        <div class="mb-4">
                            <label for="validity_years" class="form-label fw-bold">
                                Validity Period (Years) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('validity_years') is-invalid @enderror"
                                    id="validity_years"
                                    name="validity_years"
                                    required>
                                <option value="">Select validity period</option>
                                <option value="1" {{ old('validity_years') == 1 ? 'selected' : '' }}>1 Year</option>
                                <option value="2" {{ old('validity_years') == 2 ? 'selected' : '' }}>2 Years</option>
                                <option value="3" {{ old('validity_years') == 3 ? 'selected' : '' }}>3 Years</option>
                                <option value="5" {{ old('validity_years') == 5 ? 'selected' : '' }}>5 Years</option>
                                <option value="10" {{ old('validity_years') == 10 ? 'selected' : '' }}>10 Years</option>
                            </select>
                            <small class="text-muted">How long should this key remain valid?</small>
                            @error('validity_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Key Length -->
                        <div class="mb-4">
                            <label for="key_length" class="form-label fw-bold">
                                Key Length (bits) <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('key_length') is-invalid @enderror"
                                    id="key_length"
                                    name="key_length"
                                    required>
                                <option value="">Select key length</option>
                                <option value="2048" {{ old('key_length') == 2048 ? 'selected' : '' }}>
                                    2048 bits (Standard - Faster)
                                </option>
                                <option value="3072" {{ old('key_length') == 3072 ? 'selected' : '' }}>
                                    3072 bits (Enhanced Security)
                                </option>
                                <option value="4096" {{ old('key_length') == 4096 ? 'selected' : '' }}>
                                    4096 bits (Maximum Security - Slower)
                                </option>
                            </select>
                            <small class="text-muted">
                                Higher key lengths provide better security but slower performance
                            </small>
                            @error('key_length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Security Notice:</strong> The private key will be securely stored and encrypted.
                            Keep the generated key information confidential.
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.signature.keys.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-key me-2"></i>Generate Signature Key
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>About Digital Signature Keys
                    </h5>
                    <ul class="mb-0">
                        <li class="mb-2">
                            <strong>RSA Algorithm:</strong> Uses industry-standard RSA encryption for secure digital signatures
                        </li>
                        <li class="mb-2">
                            <strong>Key Pair:</strong> Generates both public and private keys - private key is encrypted and securely stored
                        </li>
                        <li class="mb-2">
                            <strong>Validity Period:</strong> Keys expire after the specified period for enhanced security
                        </li>
                        <li class="mb-2">
                            <strong>Key Length:</strong> Longer keys (4096 bits) provide maximum security but require more processing time
                        </li>
                        <li>
                            <strong>Usage:</strong> Once created, this key will be used to sign all approval documents until it expires
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#createKeyForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Generating Key...');

        // Show warning
        Swal.fire({
            title: 'Generate Signature Key?',
            html: `
                <p>This will create a new RSA key pair with the following specifications:</p>
                <ul class="text-start">
                    <li><strong>Purpose:</strong> ${$('#purpose').val()}</li>
                    <li><strong>Validity:</strong> ${$('#validity_years').val()} year(s)</li>
                    <li><strong>Key Length:</strong> ${$('#key_length').val()} bits</li>
                </ul>
                <div class="alert alert-warning mt-3">
                    <strong>Note:</strong> Key generation may take a few moments, especially for 4096-bit keys.
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Generate Key',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize()
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Digital signature key generated successfully',
                    icon: 'success',
                    confirmButtonText: 'View Key Details'
                }).then(() => {
                    window.location.href = '{{ route("admin.signature.keys.index") }}';
                });
            } else {
                // Re-enable button if cancelled
                submitBtn.prop('disabled', false).html(originalText);
            }
        }).catch((error) => {
            submitBtn.prop('disabled', false).html(originalText);

            let errorMessage = 'Failed to generate signature key';
            if(error.responseJSON && error.responseJSON.message) {
                errorMessage = error.responseJSON.message;
            }

            Swal.fire('Error!', errorMessage, 'error');
        });
    });
});
</script>
@endpush --}}
