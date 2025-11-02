{{-- resources/views/digital-signature/admin/partials/sidebar.blade.php --}}
<div class="sidebar-header text-center py-3">
    <h5 class="text-white mb-0">
        <i class="fas fa-user-shield me-2"></i>
        Kaprodi Panel
    </h5>
    <small class="text-white-50">DiSign | Informatika UMT</small>
</div>

<ul class="nav flex-column">
    <!-- Dashboard -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.signature.dashboard') }}">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard
        </a>
    </li>

    <!-- Digital Signature Keys Management -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.keys.*') ? 'active' : '' }}"
           href="{{ route('admin.signature.keys.index') }}">
            <i class="fas fa-key me-2"></i>
            Digital Signature Keys
            @php
                $expiringKeys = \App\Models\DigitalSignature::expiringSoon(7)->count();
            @endphp
            @if($expiringKeys > 0)
                <span class="badge bg-danger rounded-pill ms-auto">{{ $expiringKeys }}</span>
            @endif
        </a>
    </li>

    <!-- Document Management -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.documents.*') ? 'active' : '' }}"
           href="{{ route('admin.signature.documents.index') }}">
            <i class="fas fa-file-signature me-2"></i>
            Document Signatures
            @if(isset($stats['pending_signatures']) && $stats['pending_signatures'] > 0)
                <span class="badge bg-info rounded-pill ms-auto">{{ $stats['pending_signatures'] }}</span>
            @endif
        </a>
    </li>

    <!-- Approval Requests -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.approval.*') ? 'active' : '' }}"
           href="{{ route('admin.signature.approval.index') }}">
            <i class="fas fa-clipboard-check me-2"></i>
            Approval Requests
            @php
                $pendingCount = \App\Models\ApprovalRequest::pendingApproval()->count();
            @endphp
            @if($pendingCount > 0)
                <span class="badge bg-warning rounded-pill ms-auto">{{ $pendingCount }}</span>
            @endif
        </a>
    </li>

    <!-- Divider -->
    <hr class="my-3 text-white-50">

    <!-- Reports & Analytics -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.reports.*') ? 'active' : '' }}"
           href="{{ route('admin.signature.reports.index') }}">
            <i class="fas fa-chart-bar me-2"></i>
            Reports & Analytics
        </a>
    </li>

    <!-- Activity Logs -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.logs.*') ? 'active' : '' }}"
           href="{{ route('admin.signature.logs.audit') }}">
            <i class="fas fa-history me-2"></i>
            Activity Logs
            @php
                $failureCount = \App\Http\Controllers\DigitalSignature\LogsController::getRecentFailuresCount();
            @endphp
            @if($failureCount > 0)
                <span class="badge bg-danger rounded-pill ms-auto">{{ $failureCount }}</span>
            @endif
        </a>
    </li>

    <!-- Export Data -->
    {{-- <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.signature.export') }}?format=csv">
            <i class="fas fa-download me-2"></i>
            Export Data
        </a>
    </li> --}}

    <!-- System Settings -->
    {{-- <li class="nav-item">
        <a class="nav-link" href="#" onclick="showSettings()">
            <i class="fas fa-cog me-2"></i>
            Settings
        </a>
    </li> --}}

    <!-- Divider -->
    <hr class="my-3 text-white-50">

    <!-- Public Verification -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('signature.verify.page') }}" target="_blank">
            <i class="fas fa-external-link-alt me-2"></i>
            Public Verification
        </a>
    </li>

    <!-- Help & Documentation -->
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.help') ? 'active' : '' }}"
           href="{{ route('admin.signature.help') }}">
            <i class="fas fa-question-circle me-2"></i>
            Help & Support
        </a>
    </li>

    {{-- User Help & Support --}}
    {{-- <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.signature.user.help') ? 'active' : '' }}"
           href="{{ route('admin.signature.help.user') }}">
            <i class="fas fa-question-circle me-2"></i>
            User Help & Support
        </a>
    </li> --}}
</ul>

{{-- Logo Section --}}
<div class="text-center my-4">
    <img src="{{ asset('assets/logo.JPG') }}" alt="Logo" class="d-inline-block align-text-center img-fluid rounded mx-auto d-block" style="max-width: 150px;">
</div>

<!-- Quick Stats at Bottom -->
{{-- <div class="mt-auto pt-3">
    <div class="bg-white bg-opacity-20 rounded p-3 mx-2">
        <h6 class="text-black mb-2">Quick Stats</h6>
        <div class="row text-center">
            <div class="col-6">
                <div class="text-black h6">{{ \App\Models\DigitalSignature::active()->count() }}</div>
                <small class="text-black-50">Active Keys</small>
            </div>
            <div class="col-6">
                <div class="text-black h6">{{ \App\Models\DocumentSignature::where('signature_status', 'verified')->count() }}</div>
                <small class="text-black-50">Verified</small>
            </div>
        </div>
    </div>
</div> --}}
