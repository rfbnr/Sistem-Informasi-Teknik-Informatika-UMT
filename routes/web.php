<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\DigitalSignature\VerificationController;
use App\Http\Controllers\DigitalSignature\ApprovalRequestController;
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

    // API endpoints for verification
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('verify/{token}', [VerificationController::class, 'getVerificationDetails'])
            ->name('verify');
        Route::post('bulk-verify', [VerificationController::class, 'bulkVerify'])
            ->name('bulk.verify');
        Route::get('statistics', [VerificationController::class, 'getPublicStatistics'])
            ->name('statistics');
    });

    // Download verification certificate
    Route::get('certificate/{token}', [VerificationController::class, 'downloadCertificate'])
        ->name('certificate');
});

// ===================================================================
// KAPRODI/ADMIN ROUTES (Kaprodi Authentication Required)
// ===================================================================
Route::middleware(['auth:kaprodi'])->prefix('admin/signature')->name('admin.signature.')->group(function () {

    // ==================== DASHBOARD ====================
    Route::get('dashboard', [DigitalSignatureController::class, 'adminDashboard'])
        ->name('dashboard');

    // ==================== DIGITAL SIGNATURE KEY MANAGEMENT ====================
    Route::prefix('keys')->name('keys.')->group(function () {
        Route::get('/', [DigitalSignatureController::class, 'keyManagement'])
            ->name('index');
        Route::get('create', [DigitalSignatureController::class, 'createKeyForm'])
            ->name('create');
        Route::post('store', [DigitalSignatureController::class, 'createSignatureKey'])
            ->name('store');
        Route::get('{id}', [DigitalSignatureController::class, 'viewSignatureKey'])
            ->name('view');
        Route::post('{id}/revoke', [DigitalSignatureController::class, 'revokeSignatureKey'])
            ->name('revoke');
    });

    // ==================== DOCUMENT SIGNATURES MANAGEMENT ====================
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentSignatureController::class, 'index'])
            ->name('index');
        Route::get('{id}', [DocumentSignatureController::class, 'show'])
            ->name('show');
        Route::post('{id}/verify', [DocumentSignatureController::class, 'verify'])
            ->name('verify');
        Route::post('{id}/invalidate', [DocumentSignatureController::class, 'invalidate'])
            ->name('invalidate');
        Route::post('batch-verify', [DocumentSignatureController::class, 'batchVerify'])
            ->name('batch.verify');
        Route::get('{id}/view', [DocumentSignatureController::class, 'viewSignedDocument'])
            ->name('view');
        Route::get('{id}/download', [DocumentSignatureController::class, 'downloadSignedDocument'])
            ->name('download');
        Route::get('{id}/qr-code', [DocumentSignatureController::class, 'downloadQRCode'])
            ->name('qr.download');
        Route::post('{id}/regenerate-qr', [DocumentSignatureController::class, 'regenerateQRCode'])
            ->name('qr.regenerate');
        Route::get('export', [DocumentSignatureController::class, 'export'])
            ->name('export');
    });

    // ==================== SIGNATURE TEMPLATE MANAGEMENT ====================
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [SignatureTemplateController::class, 'index'])
            ->name('index');
        Route::get('create', [SignatureTemplateController::class, 'create'])
            ->name('create');
        Route::post('store', [SignatureTemplateController::class, 'store'])
            ->name('store');
        Route::get('{id}', [SignatureTemplateController::class, 'show'])
            ->name('show');
        Route::get('{id}/edit', [SignatureTemplateController::class, 'edit'])
            ->name('edit');
        Route::put('{id}', [SignatureTemplateController::class, 'update'])
            ->name('update');
        Route::delete('{id}', [SignatureTemplateController::class, 'destroy'])
            ->name('destroy');
        Route::post('{id}/set-default', [SignatureTemplateController::class, 'setDefault'])
            ->name('set.default');
        Route::post('{id}/upload-signature', [SignatureTemplateController::class, 'uploadSignatureImage'])
            ->name('upload.signature');
        Route::post('{id}/clone', [SignatureTemplateController::class, 'clone'])
            ->name('clone');
        Route::get('active/list', [SignatureTemplateController::class, 'getActiveTemplates'])
            ->name('active');
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
        Route::post('{id}/approve-signature', [ApprovalRequestController::class, 'approveSignature'])
            ->name('approve.signature');
        Route::post('bulk-approve', [ApprovalRequestController::class, 'bulkApprove'])
            ->name('bulk.approve');
        Route::get('export', [ApprovalRequestController::class, 'exportApprovalRequests'])
            ->name('export');
        Route::get('{id}/download', [ApprovalRequestController::class, 'downloadDocument'])
            ->name('download');
    });

    // ==================== VERIFICATION TOOLS ====================
    Route::get('verification-tools', [DigitalSignatureController::class, 'verificationTools'])
        ->name('verification.tools');
    Route::post('manual-verify', [DigitalSignatureController::class, 'manualVerification'])
        ->name('manual.verify');

    // ==================== STATISTICS & EXPORT ====================
    Route::get('statistics', [DocumentSignatureController::class, 'getStatistics'])
        ->name('statistics');
    Route::get('export', [DigitalSignatureController::class, 'exportStatistics'])
        ->name('export');
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

    // ==================== DOCUMENT SIGNING ====================
    Route::prefix('sign')->name('sign.')->group(function () {
        Route::get('{approvalRequestId}', [DigitalSignatureController::class, 'signDocument'])
            ->name('document');
        Route::get('{approvalRequestId}/templates', [DigitalSignatureController::class, 'getTemplatesForSigning'])
            ->name('templates');
        Route::get('{approvalRequestId}/canvas', [DigitalSignatureController::class, 'signatureCanvas'])
            ->name('canvas');
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
// DEVELOPMENT & TESTING ROUTES (Local Environment Only)
// ===================================================================
if (app()->environment('local', 'staging')) {
    Route::prefix('dev/signature')->name('dev.signature.')->group(function () {
        Route::get('test-canvas', function () {
            return view('digital-signature.dev.test-canvas');
        })->name('test.canvas');

        Route::get('test-verification/{token}', [VerificationController::class, 'testVerification'])
            ->name('test.verification');

        Route::post('generate-test-data', [DigitalSignatureController::class, 'generateTestData'])
            ->name('generate.test.data');

        // Test routes for UI components
        Route::get('test/layout', function () {
            return view('digital-signature.layouts.app');
        })->name('test.layout');

        Route::get('test/admin-dashboard', function () {
            $stats = [
                'total_signatures' => 10,
                'active_signatures' => 8,
                'expired_signatures' => 2,
                'total_documents_signed' => 25,
                'pending_signatures' => 5,
                'verified_signatures' => 20
            ];
            $recentSignatures = collect();
            $expiringSignatures = collect();
            $verificationStats = ['verification_rate' => 85, 'total_signatures' => 10, 'verified_signatures' => 8, 'period_days' => 30];

            return view('digital-signature.admin.dashboard', compact('stats', 'recentSignatures', 'expiringSignatures', 'verificationStats'));
        })->name('test.admin.dashboard');

        Route::get('test/user-form', function () {
            $hasApprovalRequests = false;
            $recentRequests = collect();
            return view('digital-signature.user.approval-request', compact('hasApprovalRequests', 'recentRequests'));
        })->name('test.user.form');

        Route::get('test/user-status', function () {
            $approvalRequests = collect();
            return view('digital-signature.user.status', compact('approvalRequests'));
        })->name('test.user.status');
    });
}

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
