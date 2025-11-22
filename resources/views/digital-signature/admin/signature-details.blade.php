{{-- resources/views/digital-signature/admin/signature-details.blade.php --}}
@extends('digital-signature.layouts.app')

@section('title', 'Document Signature Details')

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
                    Document Signature Details
                </h1>
                <p class="mb-0 opacity-75">{{ $documentSignature->approvalRequest->document_name }}</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="{{ route('admin.signature.documents.index') }}" class="btn btn-outline-light me-2">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                {{-- <a href="{{ route('admin.signature.documents.download', $documentSignature->id) }}" class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Download
                </a> --}}
            </div>
        </div>
    </div>

    <!-- Verification Result -->
    @if($verificationResult)
        @if($verificationResult['is_valid'])
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Signature Verified:</strong> {{ $verificationResult['message'] }}
            </div>
        @else
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                <strong>Verification Failed:</strong> {{ $verificationResult['message'] }}
            </div>
        @endif
    @endif

    {{-- Signature Invalide Alert --}}
    @if($documentSignature->signature_status === 'invalid')
        <div class="alert alert-danger">
            <i class="fas fa-ban me-2"></i>
            <strong>Signature Invalidated:</strong> This signature was marked as invalid on {{ $documentSignature->invalidated_at->format('d F Y, H:i') }}.
            @if($documentSignature->invalidated_reason)
                <br>
                <strong>Reason:</strong> {{ $documentSignature->invalidated_reason }}
            @endif
        </div>
    @endif

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Document Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        Document Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submitted By</strong><br>
                            {{ $documentSignature->approvalRequest->user->name }}
                            <br>
                            <small class="text-muted">
                                NIM: {{ $documentSignature->approvalRequest->user->NIM ?? '-' }}
                            </small>
                        </div>
                        <div class="col-md-6">
                            <strong>Email</strong><br>
                            {{ $documentSignature->approvalRequest->user->email }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Document Name:</strong><br>
                            {{ $documentSignature->approvalRequest->document_name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Document Type:</strong><br>
                            @if($documentSignature->approvalRequest->document_type)
                                <span class="badge bg-secondary">
                                    {{ $documentSignature->approvalRequest->document_type }}
                                </span>
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submission Date:</strong><br>
                            {{ $documentSignature->approvalRequest->created_at->format('d F Y, H:i') }}
                            <small class="text-muted d-block">{{ $documentSignature->approvalRequest->created_at->diffForHumans() }}</small>
                        </div>
                        {{-- <div class="col-md-6">
                            <strong>Deadline:</strong><br>
                            @if($documentSignature->approvalRequest->deadline)
                                {{ $documentSignature->approvalRequest->deadline->format('d F Y, H:i') }}
                                @if($documentSignature->approvalRequest->isOverdue())
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                @elseif($documentSignature->approvalRequest->isNearDeadline())
                                    <span class="badge bg-warning ms-2">Near Deadline</span>
                                @endif
                            @else
                                <span class="text-muted">No deadline set</span>
                            @endif
                        </div> --}}
                    </div>

                    <div class="row mb-3">
                        @if($documentSignature->approvalRequest->notes)
                        <div class="col-md-6">
                            <div class="col-12">
                                <strong>Submission Notes:</strong><br>
                                <p class="text-muted mb-0">{{ $documentSignature->approvalRequest->notes }}</p>
                            </div>
                        </div>
                        @endif

                        @if($documentSignature->approvalRequest->approval_notes)
                        <div class="col-md-6">
                            <div class="col-12">
                                <strong>Approval Notes:</strong><br>
                                <div class="mb-0">
                                    {{ $documentSignature->approvalRequest->approval_notes }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($documentSignature->approvalRequest->admin_notes)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Admin Notes:</strong><br>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $documentSignature->approvalRequest->admin_notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Document Files Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Original Document:</strong><br>
                            @if($documentSignature->approvalRequest->document_path)
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small flex-grow-1" style="word-break: break-all;">
                                        {{ basename($documentSignature->approvalRequest->document_path) }}
                                    </code>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="previewDocument('original')"
                                            title="Preview Original Document">
                                        <i class="fas fa-eye"></i> Show
                                    </button>
                                </div>
                            @else
                                <span class="text-muted">Not available</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Signed Document:</strong><br>
                            @if($documentSignature->approvalRequest->signed_document_path || $documentSignature->final_pdf_path)
                                <div class="d-flex align-items-center gap-2">
                                    <code class="small flex-grow-1" style="word-break: break-all;">
                                        {{ basename($documentSignature->approvalRequest->signed_document_path ?? $documentSignature->final_pdf_path) }}
                                    </code>
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                            onclick="previewDocument('signed')"
                                            title="Preview Signed Document">
                                        <i class="fas fa-eye"></i> Show
                                    </button>
                                </div>
                            @else
                                <span class="text-muted">Not yet signed</span>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Document Name:</strong><br>
                            {{ $documentSignature->approvalRequest->document_name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Document Number:</strong><br>
                            {{ $documentSignature->approvalRequest->full_document_number }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Submitted By:</strong><br>
                            {{ $documentSignature->approvalRequest->user->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Submission Date:</strong><br>
                            {{ $documentSignature->approvalRequest->created_at->format('d F Y H:i') }}
                        </div>
                    </div>

                    @if($documentSignature->approvalRequest->notes)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Notes:</strong><br>
                            <p class="text-muted">{{ $documentSignature->approvalRequest->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <strong>PDF Status:</strong><br>
                            @if($documentSignature->final_pdf_path)
                                <div class="alert alert-success mb-0 mt-2">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Signed PDF Available</strong> - Document has been merged with digital signature
                                    <br>
                                    <small class="text-muted mt-1 d-block">
                                        Path: <code>{{ $documentSignature->final_pdf_path }}</code>
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-warning mb-0 mt-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Original Document Only</strong> - Signed PDF not yet generated
                                    <br>
                                    <small class="text-muted mt-1 d-block">
                                        Using original document: <code>{{ $documentSignature->approvalRequest->document_path }}</code>
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- Digital Signature Information -->
            @if($documentSignature->signature_status !== 'pending')
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-signature me-2"></i>
                            Digital Signature Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Unique Encryption Key:</strong> This document is secured with a unique RSA-2048 digital signature key that was automatically generated specifically for this document. Each signed document has its own independent encryption key for maximum security.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signature ID:</strong><br>
                                <code>{{ $documentSignature->digitalSignature->signature_id ?? 'N/A' }}</code>
                                <br>
                                <span class="badge bg-primary mt-1">
                                    <i class="fas fa-key me-1"></i> Auto-Generated Unique Key
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Signature Status:</strong><br>
                                <span class="status-badge status-{{ strtolower($documentSignature->signature_status) }}">
                                    {{ ucfirst($documentSignature->signature_status) }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signed By:</strong><br>
                                @if($documentSignature->signer)
                                    {{ $documentSignature->signer->name }}<br>
                                    <small class="text-muted">
                                        NIDN: {{ $documentSignature->signer->NIDN ?? '-' }}<br>
                                        Email: {{ $documentSignature->signer->email }}
                                    </small>
                                @else
                                    <span class="text-muted">Not signed yet</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Signed At:</strong><br>
                                @if($documentSignature->signed_at)
                                    {{ $documentSignature->signed_at->format('d F Y, H:i:s') }}
                                    <br><small class="text-muted">{{ $documentSignature->signed_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Not signed yet</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Signature Algorithm:</strong><br>
                                <span class="badge bg-info">
                                    {{ $documentSignature->digitalSignature->algorithm ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Key Length:</strong><br>
                                <span class="badge bg-success">
                                    {{ $documentSignature->digitalSignature->key_length ?? 'N/A' }} bits
                                </span>
                            </div>
                            <div class="col-md-4">
                                <strong>Certificate Status:</strong><br>
                                @if($documentSignature->digitalSignature && $documentSignature->digitalSignature->isValid())
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Valid
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Invalid
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($documentSignature->digitalSignature)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Certificate Valid From:</strong><br>
                                {{ $documentSignature->digitalSignature->valid_from->format('d F Y, H:i') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Certificate Valid Until:</strong><br>
                                {{ $documentSignature->digitalSignature->valid_until->format('d F Y, H:i') }}
                                @if($documentSignature->digitalSignature->isExpiringSoon())
                                    <span class="badge bg-warning ms-2">Expiring Soon</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- ✅ NEW: Signature Format & Implementation Method --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Signature Format:</strong><br>
                                @if($documentSignature->signature_format === 'pkcs7_cms_detached')
                                    <span class="badge bg-success">
                                        <i class="fas fa-certificate me-1"></i> PKCS#7 CMS Detached
                                    </span>
                                    <p class="small text-muted mb-0 mt-1">
                                        Industry-standard cryptographic message syntax
                                    </p>
                                @elseif($documentSignature->signature_format === 'legacy_hash_only')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-hashtag me-1"></i> Legacy Hash-Only
                                    </span>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Signing Implementation:</strong><br>
                                @if(isset($documentSignature->signature_metadata['signing_method']) && $documentSignature->signature_metadata['signing_method'] === 'one_pass_tcpdf')
                                    <span class="badge bg-primary">
                                        <i class="fas fa-bolt me-1"></i> ONE-PASS TCPDF
                                    </span>
                                    <p class="small text-muted mb-0 mt-1">
                                        Combined QR embedding and PDF signing
                                    </p>
                                @else
                                    <span class="text-muted">Standard</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Placement Method:</strong><br>
                                @if(isset($documentSignature->signature_metadata['placement_method']))
                                    @if($documentSignature->signature_metadata['placement_method'] === 'drag_drop_qr')
                                        <span class="badge bg-success">
                                            <i class="fas fa-qrcode me-1"></i> QR Code Drag & Drop
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            {{ ucwords(str_replace('_', ' ', $documentSignature->signature_metadata['placement_method'])) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Signed Via:</strong><br>
                                @if(isset($documentSignature->signature_metadata['signed_via']))
                                    <span class="badge bg-info">
                                        <i class="fas fa-globe me-1"></i> {{ ucwords(str_replace('_', ' ', $documentSignature->signature_metadata['signed_via'])) }}
                                    </span>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Document Hash:</strong><br>
                                <code class="small">{{ substr($documentSignature->document_hash, 0, 32) }}...</code>
                            </div>
                            <div class="col-md-6">
                                <strong>Signature Value:</strong><br>
                                <code class="small">{{ substr($documentSignature->signature_value, 0, 32) }}...</code>
                            </div>
                        </div>

                        @if($documentSignature->verified_at)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Verified At:</strong><br>
                                {{ $documentSignature->verified_at->format('d F Y, H:i:s') }}
                                <br><small class="text-muted">{{ $documentSignature->verified_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Verified By:</strong><br>
                                @if($documentSignature->verifier)
                                    {{ $documentSignature->verifier->name }}<br>
                                    <small class="text-muted">{{ $documentSignature->verifier->email }}</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ✅ NEW: X.509 v3 Certificate Extensions Details --}}
                @if($verificationResult && isset($verificationResult['details']['checks']['certificate']['details']['extensions_validation']))
                    @php
                        $extensionsValidation = $verificationResult['details']['checks']['certificate']['details']['extensions_validation'];
                    @endphp
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h5 class="mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                X.509 v3 Certificate Extensions
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Summary Badge --}}
                            <div class="alert alert-{{ $extensionsValidation['all_valid'] ? 'success' : ($extensionsValidation['critical_valid'] ? 'warning' : 'danger') }} mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-{{ $extensionsValidation['all_valid'] ? 'check-circle' : ($extensionsValidation['critical_valid'] ? 'exclamation-triangle' : 'times-circle') }} fa-2x me-3"></i>
                                    <div>
                                        <strong>{{ $extensionsValidation['summary'] }}</strong>
                                        @if(!empty($extensionsValidation['warnings']))
                                            <div class="small mt-1">
                                                @foreach($extensionsValidation['warnings'] as $warning)
                                                    <div><i class="fas fa-info-circle me-1"></i> {{ $warning }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Extensions Details --}}
                            @foreach($extensionsValidation['checks'] as $extName => $extCheck)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            @if($extCheck['valid'])
                                                <i class="fas fa-check-circle fa-lg text-success"></i>
                                            @else
                                                <i class="fas fa-times-circle fa-lg text-danger"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong>{{ $extCheck['name'] }}</strong>
                                                @if($extCheck['critical'])
                                                    <span class="badge bg-danger">CRITICAL</span>
                                                @else
                                                    <span class="badge bg-secondary">Non-Critical</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted mb-2">{{ $extCheck['description'] }}</div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted">Expected:</small><br>
                                                    <code class="small">{{ $extCheck['expected'] }}</code>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted">Current Value:</small><br>
                                                    @if($extCheck['present'])
                                                        <code class="small">{{ $extCheck['value'] }}</code>
                                                    @else
                                                        <span class="text-danger small">Not Present</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="small text-muted mt-3">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Note:</strong> X.509 v3 extensions provide additional constraints and capabilities for the certificate.
                                Critical extensions must be valid for the certificate to be trusted.
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ✅ NEW: CMS PKCS#7 Signature Details --}}
                @if($verificationResult && isset($verificationResult['details']['checks']['cms_signature']))
                    @php
                        $cmsCheck = $verificationResult['details']['checks']['cms_signature'];
                        $cmsDetails = $cmsCheck['details'] ?? [];
                    @endphp
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                            <h5 class="mb-0">
                                <i class="fas fa-lock me-2"></i>
                                CMS PKCS#7 Signature Details
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Verification Status --}}
                            <div class="alert alert-{{ $cmsCheck['status'] ? 'success' : 'danger' }} mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-{{ $cmsCheck['status'] ? 'check-circle' : 'times-circle' }} fa-2x me-3"></i>
                                    <div>
                                        <strong>{{ $cmsCheck['message'] }}</strong>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($cmsDetails))
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Signature Format:</strong><br>
                                        <code class="badge bg-primary">
                                            {{ $cmsDetails['signature_format_used'] ?? 'N/A' }}
                                        </code>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Verification Status:</strong><br>
                                        @if($cmsDetails['is_valid'] ?? false)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i> Valid
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i> Invalid
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if(isset($cmsDetails['algorithm_used']))
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <strong>Algorithm Used:</strong><br>
                                            <code>{{ $cmsDetails['algorithm_used'] }}</code>
                                        </div>
                                    </div>
                                @endif

                                @if(isset($cmsDetails['signature_time']))
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <strong>Signature Time:</strong><br>
                                            {{ $cmsDetails['signature_time'] }}
                                        </div>
                                    </div>
                                @endif

                                {{-- Adobe PDF Signature Indicators (if available) --}}
                                @if(isset($cmsDetails['pdf_indicators']))
                                    <div class="mt-3 pt-3 border-top">
                                        <strong class="d-block mb-2">Adobe PDF Signature Indicators:</strong>
                                        <ul class="mb-0">
                                            <li>
                                                <strong>AcroForm Dictionary:</strong>
                                                @if($cmsDetails['pdf_indicators']['has_acroform'] ?? false)
                                                    <span class="text-success"><i class="fas fa-check"></i> Present</span>
                                                @else
                                                    <span class="text-danger"><i class="fas fa-times"></i> Missing</span>
                                                @endif
                                            </li>
                                            <li>
                                                <strong>Signature Field:</strong>
                                                @if($cmsDetails['pdf_indicators']['has_sig_field'] ?? false)
                                                    <span class="text-success"><i class="fas fa-check"></i> Present</span>
                                                @else
                                                    <span class="text-danger"><i class="fas fa-times"></i> Missing</span>
                                                @endif
                                            </li>
                                            <li>
                                                <strong>ByteRange:</strong>
                                                @if($cmsDetails['pdf_indicators']['has_byterange'] ?? false)
                                                    <span class="text-success"><i class="fas fa-check"></i> Present</span>
                                                @else
                                                    <span class="text-danger"><i class="fas fa-times"></i> Missing</span>
                                                @endif
                                            </li>
                                        </ul>

                                        @if($cmsDetails['pdf_indicators']['adobe_compatible'] ?? false)
                                            <div class="mt-2">
                                                <span class="badge bg-success">
                                                    <i class="fas fa-file-pdf me-1"></i> Adobe Reader Compatible
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            <div class="small text-muted mt-3">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Note:</strong> PKCS#7 CMS (Cryptographic Message Syntax) is an industry-standard format
                                for digitally signing and encrypting data, widely used in PDF document signatures.
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Verification Checks -->
            @if($verificationResult && isset($verificationResult['details']['checks']))
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        Verification Checks
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($verificationResult['details']['checks'] as $checkName => $check)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-start">
                                <div class="me-3 mt-1">
                                    @if($check['status'])
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle fa-2x text-danger"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <strong>{{ ucwords(str_replace('_', ' ', $checkName)) }}</strong>
                                    <div class="small text-muted">{{ $check['message'] }}</div>

                                    {{-- ✅ ENHANCED: Show Document Integrity Details --}}
                                    @if($checkName === 'document_integrity' && isset($check['details']))
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <div class="row small">
                                                <div class="col-md-6 mb-2">
                                                    <strong>Verified File:</strong><br>
                                                    @if($check['details']['is_signed_pdf'] ?? false)
                                                        <span class="badge bg-success">Signed PDF</span>
                                                    @else
                                                        <span class="badge bg-info">Original PDF</span>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>File Size:</strong><br>
                                                    {{ number_format($check['details']['file_size'] ?? 0) }} bytes
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Hash Algorithm:</strong><br>
                                                    <code>SHA-256</code>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Hash Match:</strong><br>
                                                    @if($check['status'])
                                                        <span class="text-success">
                                                            <i class="fas fa-check"></i> Identical
                                                        </span>
                                                    @else
                                                        <span class="text-danger">
                                                            <i class="fas fa-times"></i> Modified
                                                        </span>
                                                    @endif
                                                </div>
                                                @if(isset($check['details']['stored_hash']))
                                                    <div class="col-12 mt-2">
                                                        <strong>Stored Hash:</strong><br>
                                                        <code class="small">{{ substr($check['details']['stored_hash'], 0, 64) }}...</code>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ✅ ENHANCED: Show Digital Signature Key Details --}}
                                    @if($checkName === 'digital_signature' && isset($check['details']))
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <div class="row small">
                                                <div class="col-md-6 mb-2">
                                                    <strong>Signature ID:</strong><br>
                                                    <code class="small">{{ $check['details']['signature_id'] ?? 'N/A' }}</code>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Algorithm:</strong><br>
                                                    <span class="badge bg-info">{{ $check['details']['algorithm'] ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Key Length:</strong><br>
                                                    <span class="badge bg-success">{{ $check['details']['key_length'] ?? 'N/A' }} bits</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Days Until Expiry:</strong><br>
                                                    {{ abs($check['details']['days_until_expiry'] ?? 0) }} days
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- ✅ ENHANCED: Show Approval Request Details --}}
                                    @if($checkName === 'approval_request' && isset($check['details']))
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <div class="row small">
                                                <div class="col-md-6 mb-2">
                                                    <strong>Document Type:</strong><br>
                                                    <span class="badge bg-secondary">{{ $check['details']['document_type'] ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Submitted By:</strong><br>
                                                    {{ $check['details']['submitted_by'] ?? 'N/A' }}
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Status:</strong><br>
                                                    <span class="badge bg-success">{{ ucfirst($check['details']['status'] ?? 'N/A') }}</span>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <strong>Document Number:</strong><br>
                                                    <code class="small">{{ $check['details']['document_number'] ?? 'N/A' }}</code>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Signature Image Preview -->
            @if($documentSignature->canvas_data)
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-paint-brush me-2"></i>
                        Signature Template Preview
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $documentSignature->canvas_data }}" alt="Signature" class="img-fluid" style="max-height: 200px; border: 2px dashed #dee2e6; border-radius: 0.5rem; padding: 1rem;">
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        {{-- @if($documentSignature->signature_status === 'signed')
                            <button class="btn btn-success" onclick="verifySignature()">
                                <i class="fas fa-check me-2"></i> Verify Signature
                            </button>

                            <button class="btn btn-danger" onclick="rejectSignature({{ $documentSignature->id }})">
                                <i class="fas fa-times me-2"></i> Reject Signature
                            </button>
                        @endif --}}

                        <a href="{{ route('admin.signature.documents.download', $documentSignature->id) }}" class="btn btn-info">
                            <i class="fas fa-download me-2"></i> Download Document
                        </a>

                        @if($documentSignature->qr_code_path)
                            <a href="{{ route('admin.signature.documents.qr.download', $documentSignature->id) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-qrcode me-2"></i> Download QR Code
                            </a>
                        {{-- @else
                            <button class="btn btn-outline-secondary" onclick="regenerateQR()">
                                <i class="fas fa-qrcode me-2"></i> Generate QR Code
                            </button> --}}
                        @endif

                        @if(in_array($documentSignature->signature_status, ['signed', 'verified']))
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#invalidateModal">
                                <i class="fas fa-ban me-2"></i> Invalidate Signature
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($documentSignature->qr_code_path)
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        Verification QR Code
                    </h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ Storage::url($documentSignature->qr_code_path) }}" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="small text-muted">Scan to verify document authenticity</p>
                    <div class="small">
                        <strong>Verification URL:</strong><br>
                        <input type="text" class="form-control form-control-sm mt-2" readonly value="{{ $documentSignature->verification_url }}">
                    </div>
                </div>
            </div>
            @endif

            <!-- Audit Trail -->
            @if($documentSignature->auditLogs && $documentSignature->auditLogs->count() > 0)
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Audit Trail
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($documentSignature->auditLogs->take(5) as $log)
                        <div class="mb-2 pb-2 border-bottom">
                            <small>
                                <strong>{{ $log->action }}</strong><br>
                                <span class="text-muted">
                                    {{ $log->performed_at->format('d M Y H:i') }}
                                    by {{ $log->user->name ?? 'System' }}
                                </span>
                            </small>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Invalidate Modal -->
{{-- <div class="modal fade" id="invalidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Invalidate Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This will mark the signature as invalid and cannot be undone.
                </div>
                <div class="mb-3">
                    <label for="invalidateReason" class="form-label">Reason *</label>
                    <textarea class="form-control" id="invalidateReason" name="reason" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmInvalidateBtn" class="btn btn-danger" onclick="performInvalidate()">
                    <i class="fas fa-ban me-1"></i> Invalidate
                </button>
            </div>
        </div>
    </div>
</div> --}}

<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentPreviewModalLabel">
                    <i class="fas fa-file-pdf me-2"></i>
                    <span id="previewTitle">
                        Document Preview
                        {{ $documentSignature->approvalRequest->document_name }}
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <div id="pdfLoadingIndicator" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading document...</p>
                </div>
                <iframe id="documentPreviewFrame"
                        style="width: 100%; height: 100%; border: none; display: none;"
                        frameborder="0">
                </iframe>
                <div id="previewError" class="alert alert-danger m-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> <span id="errorMessage">Unable to load document preview.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <a id="downloadDocumentBtn" href="#" class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Download Document
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reject Signature Modal -->
{{-- <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Document Signature</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Signature Rejection</strong><br>
                        Rejecting this signature will also reject the approval request. The user will need to re-sign the document with correct placement.
                    </div>
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason *</label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="4" required
                                  placeholder="Example: Signature placement is incorrect - too far to the left"></textarea>
                        <small class="text-muted">Please specify the issue (placement, size, quality, etc.)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Common Rejection Reasons:</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature placement is incorrect - positioned too far to the left')">
                                Placement too far left
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature placement is incorrect - positioned too far to the right')">
                                Placement too far right
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature size is too large and overlaps with document content')">
                                Signature too large
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature quality is poor - image appears distorted or pixelated')">
                                Poor signature quality
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="setRejectReason('Signature does not match the designated signature area')">
                                Not in designated area
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Reject Signature
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
@include('digital-signature.admin.partials.invalidate-signed-modal')

@endsection

@push('scripts')
<script>
// Hide PDF loading overlay when iframe is loaded
document.addEventListener('DOMContentLoaded', function() {
    const pdfIframe = document.getElementById('pdfPreview');
    const loadingOverlay = document.getElementById('pdfLoading');

    if (pdfIframe && loadingOverlay) {
        pdfIframe.addEventListener('load', function() {
            // Hide loading overlay after 500ms delay for smooth transition
            setTimeout(function() {
                loadingOverlay.style.display = 'none';
            }, 500);
        });

        // Also hide on error
        pdfIframe.addEventListener('error', function() {
            loadingOverlay.innerHTML = '<div class="text-center"><i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i><p class="text-danger">Failed to load PDF preview</p></div>';
        });
    }
});

const documentPaths = {
    original: @json($documentSignature->approvalRequest->document_path ? Storage::url($documentSignature->approvalRequest->document_path) : null),
    signed: @json(
        $documentSignature->approvalRequest->signed_document_path
            ? Storage::url($documentSignature->approvalRequest->signed_document_path)
            : ($documentSignature->final_pdf_path ? Storage::url($documentSignature->final_pdf_path) : null)
    )
};

function previewDocument(type) {
    const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
    const iframe = document.getElementById('documentPreviewFrame');
    const loading = document.getElementById('pdfLoadingIndicator');
    const errorDiv = document.getElementById('previewError');
    const titleSpan = document.getElementById('previewTitle');
    const downloadBtn = document.getElementById('downloadDocumentBtn');

    // Reset modal state
    iframe.style.display = 'none';
    errorDiv.style.display = 'none';
    loading.style.display = 'block';

    const originalFileName = '{{ basename($documentSignature->approvalRequest->document_path) }}';
    const signedFileName = '{{ $documentSignature->final_pdf_path ? basename($documentSignature->final_pdf_path) : "Not Signed Yet" }}';

    // Set title based on type
    if (type === 'original') {
        titleSpan.textContent = 'Original Document Preview';
    } else {
        titleSpan.textContent = 'Signed Document Preview';
    }

    // Get document path
    const docPath = documentPaths[type];

    if (!docPath) {
        loading.style.display = 'none';
        errorDiv.style.display = 'block';
        document.getElementById('errorMessage').textContent = 'Document not available for preview.';
        modal.show();
        return;
    }

    // Set download button
    if( type === 'signed') {
        downloadBtn.target = '';
        downloadBtn.href = "{{ route('admin.signature.documents.download', $documentSignature->id) }}"
    } else {
        downloadBtn.target = '_blank';
        downloadBtn.href = docPath;
    }

    // Show modal
    modal.show();

    // Load PDF in iframe
    iframe.onload = function() {
        loading.style.display = 'none';
        iframe.style.display = 'block';
    };

    iframe.onerror = function() {
        loading.style.display = 'none';
        errorDiv.style.display = 'block';
        document.getElementById('errorMessage').textContent = 'Failed to load document. The file may be corrupted or not accessible.';
    };

    // Set iframe source (add #toolbar=0 to hide PDF toolbar for cleaner view)
    if( type === 'signed') {
        iframe.src = docPath + '#toolbar=0';
    } else {
        iframe.src = docPath;
    }

    // Fallback timeout in case onload doesn't fire
    setTimeout(function() {
        if (loading.style.display !== 'none') {
            loading.style.display = 'none';
            iframe.style.display = 'block';
        }
    }, 3000);
}

// function setRejectReason(reason) {
//     document.getElementById('reject_reason').value = reason;
// }

// Quick Reject from Modal
function quickRejectFromModal(id) {
    // Close quick preview modal
    bootstrap.Modal.getInstance(document.getElementById('quickPreviewModal')).hide();

    // Small delay then open reject modal

}
</script>
@endpush

{{--

// function regenerateQR() {
//     if (confirm('Generate QR code for this document?')) {
//         fetch('{{ route("admin.signature.documents.qr.regenerate", $documentSignature->id) }}', {
//             method: 'POST',
//             headers: {
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//             }
//         })
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 alert('QR code generated successfully!');
//                 location.reload();
//             } else {
//                 alert('QR generation failed');
//             }
//         });
//     }
// }

--}}
