<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LombaController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\KaprodiController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\SorotanController;
use App\Http\Controllers\AkreditasiController;
use App\Http\Controllers\StrukturOrganisasiController;

// ===================================================================
// DIGITAL SIGNATURE CONTROLLERS
// ===================================================================
use App\Http\Controllers\DigitalSignature\LogsController;
use App\Http\Controllers\DigitalSignature\HelpSupportController;
use App\Http\Controllers\DigitalSignature\VerificationController;
use App\Http\Controllers\DigitalSignature\ApprovalRequestController;
use App\Http\Controllers\DigitalSignature\ReportAnalyticsController;
use App\Http\Controllers\DigitalSignature\DigitalSignatureController;
use App\Http\Controllers\DigitalSignature\DocumentSignatureController;
use App\Http\Controllers\DigitalSignature\SignatureTemplateController;

// ===================================================================
// PUBLIC ROUTES (No Authentication Required)
// ===================================================================

// Main Website Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/agenda-detail/{id}', [HomeController::class, 'agenda_detail']);
Route::get('/pengabdian', [HomeController::class, 'pengabdian']);
Route::get('/download', [HomeController::class, 'download']);
Route::get('/dosen-pembimbing-akademik', [HomeController::class, 'dpa']);
Route::get('/kurikulum-rps', [HomeController::class, 'kurikulum']);
Route::get('/luaran-obe', [HomeController::class, 'luaran']);
Route::get('/lomba', [LombaController::class, 'index']);
Route::get('/penelitian', [JurnalController::class, 'index']);
Route::get('/dosen', [DosenController::class, 'index']);
Route::get('/akreditasi', [AkreditasiController::class, 'index']);
Route::get('/struktur', [StrukturOrganisasiController::class, 'index']);
Route::get('/sorotan', [SorotanController::class, 'index']);
Route::get('/alumni', [AlumniController::class, 'index']);
Route::view('/visi-misi', 'user.visi-misi');
Route::view('/sinta', 'user.sinta');

// Public Events & Services
Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/create', [EventController::class, 'create'])->name('events.create');
Route::post('events', [EventController::class, 'store'])->name('events.store');
Route::get('layanans', [LayananController::class, 'index'])->name('layanan.index');

// ===================================================================
// AUTHENTICATION ROUTES
// ===================================================================
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'do_login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/user-register', [AuthController::class, "user_register"])->name('user.register');
Route::post('/user-register', [AuthController::class, "do_user_register"])->name('do.user.register');

// ===================================================================
// PUBLIC DIGITAL SIGNATURE VERIFICATION ROUTES
// ===================================================================
Route::prefix('signature')->name('signature.')->group(function () {
    // Public verification page
    Route::get('verify', [VerificationController::class, 'verificationPage'])
        ->name('verify.page');

    // Verify by QR token/URL
    Route::get('verify/{token}', [VerificationController::class, 'verifyByToken'])
        ->name('verify');

    // Public verification form submission
    Route::post('verify', [VerificationController::class, 'verifyPublic'])
        ->name('verify.public');

    // Verify uploaded PDF document
    Route::post('verify-upload', [VerificationController::class, 'verifyUploadedPDF'])
        ->name('verify.upload');

    // API endpoints for verification
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('verify/{token}', [VerificationController::class, 'getVerificationDetails'])
            ->name('verify');
        Route::post('bulk-verify', [VerificationController::class, 'bulkVerify'])
            ->name('bulk.verify');
        Route::get('statistics', [VerificationController::class, 'getPublicStatistics'])
            ->name('statistics');
    });

    // Download verification certificate (PDF Report)
    Route::get('certificate/{token}', [VerificationController::class, 'downloadCertificate'])
        ->name('certificate');

    // View X.509 certificate details (AJAX - Public Safe Info Only)
    Route::get('certificate/view/{token}', [VerificationController::class, 'viewPublicCertificate'])
        ->name('certificate.view');
});

// ===================================================================
// KAPRODI/ADMIN ROUTES (Kaprodi Authentication Required)
// ===================================================================
Route::middleware(['auth:kaprodi'])->prefix('admin/signature')->name('admin.signature.')->group(function () {

    // ==================== DASHBOARD ====================
    Route::get('dashboard', [DigitalSignatureController::class, 'adminDashboard'])
        ->name('dashboard');

    // ==================== DIGITAL SIGNATURE KEYS MANAGEMENT ====================
    Route::prefix('keys')->name('keys.')->group(function () {
        Route::get('/', [DigitalSignatureController::class, 'keysIndex'])
            ->name('index');
        Route::get('{id}', [DigitalSignatureController::class, 'keyShow'])
            ->name('show');
        Route::post('{id}/revoke', [DigitalSignatureController::class, 'revokeKey'])
            ->name('revoke');
        Route::get('{id}/export-public-key', [DigitalSignatureController::class, 'exportPublicKey'])
            ->name('export.public');
        Route::get('{id}/audit-log', [DigitalSignatureController::class, 'keyAuditLog'])
            ->name('audit');
        Route::get('{id}/certificate', [DigitalSignatureController::class, 'viewCertificate'])
            ->name('certificate');
    });

    // ==================== DOCUMENT SIGNATURES MANAGEMENT ====================
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentSignatureController::class, 'index'])
            ->name('index');
        Route::get('{id}', [DocumentSignatureController::class, 'show'])
            ->name('show');
        Route::post('{id}/invalidate', [DocumentSignatureController::class, 'invalidate'])
            ->name('invalidate');
        Route::get('{id}/view', [DocumentSignatureController::class, 'viewSignedDocument'])
            ->name('view');
        Route::get('{id}/download', [DocumentSignatureController::class, 'downloadSignedDocument'])
            ->name('download');
        Route::get('{id}/qr-code', [DocumentSignatureController::class, 'downloadQRCode'])
            ->name('qr.download');
        Route::get('export', [DocumentSignatureController::class, 'export'])
            ->name('export');
    });

    // ==================== APPROVAL REQUEST MANAGEMENT ====================
    Route::prefix('approval-requests')->name('approval.')->group(function () {
        Route::get('/', [ApprovalRequestController::class, 'index'])
            ->name('index');
        Route::get('{id}', [ApprovalRequestController::class, 'show'])
            ->name('show');
        Route::post('{id}/approve', [ApprovalRequestController::class, 'approve'])
            ->name('approve');
        Route::post('{id}/reject', [ApprovalRequestController::class, 'reject'])
            ->name('reject');
        Route::get('export', [ApprovalRequestController::class, 'exportApprovalRequests'])
            ->name('export');
        Route::get('{id}/download', [ApprovalRequestController::class, 'downloadDocument'])
            ->name('download');
    });

    // ==================== REPORTS & ANALYTICS ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportAnalyticsController::class, 'index'])
            ->name('index');
        Route::get('export', [ReportAnalyticsController::class, 'export'])
            ->name('export');
        Route::get('qr-codes', [ReportAnalyticsController::class, 'qrCodeReport'])
            ->name('qr-codes');
        Route::get('performance', [ReportAnalyticsController::class, 'performanceMetrics'])
            ->name('performance');
    });

    // ==================== ACTIVITY LOGS ====================
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogsController::class, 'index'])
            ->name('index');
        Route::get('audit', [LogsController::class, 'auditLogs'])
            ->name('audit');
        Route::get('verification', [LogsController::class, 'verificationLogs'])
            ->name('verification');
        Route::get('{id}/details', [LogsController::class, 'logDetails'])
            ->name('details');
        Route::get('export', [LogsController::class, 'export'])
            ->name('export');
    });

    // ==================== HELP & SUPPORT ====================
    Route::get('help', [HelpSupportController::class, 'adminHelp'])
        ->name('help');
});

// ===================================================================
// STUDENT/USER ROUTES (Web Authentication Required)
// ===================================================================
Route::middleware(['auth:web'])->prefix('user/signature')->name('user.signature.')->group(function () {

    // ==================== APPROVAL REQUEST SUBMISSION ====================
    Route::prefix('approval')->name('approval.')->group(function () {
        Route::get('request', [ApprovalRequestController::class, 'showUploadForm'])
            ->name('request');
        Route::post('upload', [ApprovalRequestController::class, 'upload'])
            ->name('upload');
        Route::get('status', [ApprovalRequestController::class, 'status'])
            ->name('status');
    });

    // ==================== DOCUMENT SIGNING (QR DRAG & DROP) ====================
    Route::prefix('sign')->name('sign.')->group(function () {
        Route::get('{approvalRequestId}', [DigitalSignatureController::class, 'signDocument'])
            ->name('document');
        Route::post('{approvalRequestId}/process', [DigitalSignatureController::class, 'processDocumentSigning'])
            ->name('process');
    });

    // ==================== MY SIGNATURES MANAGEMENT ====================
    Route::prefix('my-signatures')->name('my.signatures.')->group(function () {
        Route::get('/', [DocumentSignatureController::class, 'userSignatures'])
            ->name('index');
        Route::get('{id}', [DocumentSignatureController::class, 'userSignatureDetails'])
            ->name('show');
        Route::get('{id}/download', [DocumentSignatureController::class, 'downloadSignedDocument'])
            ->name('download');
        Route::get('{id}/qr-code', [DocumentSignatureController::class, 'downloadQRCode'])
            ->name('qr');
    });

    // ==================== HELP & SUPPORT ====================
    Route::get('help', [HelpSupportController::class, 'userHelp'])
        ->name('help');
});

// ===================================================================
// SHARED AUTHENTICATED ROUTES (Both Kaprodi & Student)
// ===================================================================
Route::middleware(['auth:web,kaprodi'])->group(function () {
    // Download signed documents (both kaprodi and students can download)
    Route::get('signature/download-signed/{id}', [DocumentSignatureController::class, 'downloadSignedDocument'])
        ->name('signature.download.signed');
});

// ===================================================================
// API ROUTES FOR EXTERNAL INTEGRATIONS
// ===================================================================
Route::prefix('api/signature')->middleware(['throttle:api'])->name('api.signature.')->group(function () {
    Route::post('verify', [VerificationController::class, 'verifyPublic']);
    Route::get('status/{token}', [VerificationController::class, 'getVerificationDetails']);
    Route::get('public-stats', [VerificationController::class, 'getPublicStatistics']);
});

// API routes for real-time updates
Route::prefix('api')->middleware(['auth:web,kaprodi'])->name('api.')->group(function () {
    // Get pending documents count for students
    Route::get('user/pending-documents-count', function () {
        if (auth('web')->check()) {
            $count = \App\Models\ApprovalRequest::where('user_id', auth('web')->id())
                ->whereIn('status', ['pending', 'approved'])
                ->count();
            return response()->json(['count' => $count]);
        }
        return response()->json(['count' => 0]);
    });

    // Get pending requests count for kaprodi
    Route::get('kaprodi/pending-requests-count', function () {
        if (auth('kaprodi')->check()) {
            $count = \App\Models\ApprovalRequest::pendingApproval()->count();
            return response()->json(['count' => $count]);
        }
        return response()->json(['count' => 0]);
    });
});

// ===================================================================
// ROUTE MODEL BINDINGS
// ===================================================================
Route::bind('digitalSignature', function ($value) {
    return \App\Models\DigitalSignature::where('signature_id', $value)->firstOrFail();
});

Route::bind('documentSignature', function ($value) {
    return \App\Models\DocumentSignature::findOrFail($value);
});

Route::bind('approvalRequest', function ($value) {
    return \App\Models\ApprovalRequest::findOrFail($value);
});

// ===================================================================
// FALLBACK & ERROR ROUTES
// ===================================================================
Route::get('signature/error', function () {
    return view('digital-signature.verification.error', [
        'message' => 'Invalid or expired verification link'
    ]);
})->name('signature.error');

Route::get('/debug-file/{id}', function($id) {
    $doc = \App\Models\DocumentSignature::findOrFail($id);
    $path = $doc->final_pdf_path ?? $doc->approvalRequest->document_path;

    $debug = [
        'relative_path' => $path,
        'storage_exists' => Storage::disk('public')->exists($path),
        'absolute_path' => Storage::disk('public')->path($path),
        'file_exists' => file_exists(Storage::disk('public')->path($path)),
        'is_readable' => is_readable(Storage::disk('public')->path($path)),
        'file_size' => Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : 'N/A',
        'storage_disk' => config('filesystems.disks.public.root'),
    ];

    return response()->json($debug);
})->middleware('auth');
