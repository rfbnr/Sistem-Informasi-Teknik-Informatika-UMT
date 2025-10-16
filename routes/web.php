<?php

use App\Models\Layanan;
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
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\AkreditasiController;
use App\Http\Controllers\ApprovalRequestController;
use App\Http\Controllers\StrukturOrganisasiController;






// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'do_login']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::get('/user-register', [AuthController::class, "user_register"])->name('user.register');
Route::post('/user-register', [AuthController::class, "do_user_register"])->name('do.user.register');
Route::get('/terverfikasi/disetujui/{id}', [HomeController::class, 'verif'])->name('verifikasi');

// kaprodi
// Route::prefix('kaprodi')
//     ->middleware(['auth:kaprodi'])
//     ->group(function () {
//         Route::get('/approval-requests', [ApprovalRequestController::class, 'index'])->name('approval-requests.index');
//         Route::get('/approval-requests/{id}/approve', [ApprovalRequestController::class, 'approve'])->name('approval-request.approve');
//         Route::get('/approval-requests/{id}/reject', [ApprovalRequestController::class, 'reject'])->name('approval-request.reject');
//         Route::post('/approval-requests/{id}/upload-signed-document', [ApprovalRequestController::class, 'uploadSignedDocument'])->name('approval-request.uploadSignedDocument');
//         Route::get('/approval-requests/download/{id}', [ApprovalRequestController::class, 'downloadDocument'])->name('approval-request.downloadDocument');
//     });
Route::prefix('kaprodi')->middleware(['auth:kaprodi'])->name('kaprodi.')->group(function () {
    Route::get('/dashboard', [KaprodiController::class, 'dashboard'])->name('dashboard');

    // Signature Requests Management
    Route::prefix('signatures')->name('signatures.')->group(function () {
        Route::get('/', [KaprodiController::class, 'signatureIndex'])->name('index');
        Route::get('/pending', [KaprodiController::class, 'pendingSignatures'])->name('pending');
        Route::get('/completed', [KaprodiController::class, 'completedSignatures'])->name('completed');
        Route::get('/urgent', [KaprodiController::class, 'urgentSignatures'])->name('urgent');
        Route::get('/{signatureRequest}', [KaprodiController::class, 'signatureShow'])->name('show');
        Route::get('/{signatureRequest}/sign', [SignatureController::class, 'sign'])->name('sign');
        Route::post('/{signatureRequest}/process', [SignatureController::class, 'processSignature'])->name('process');
        Route::post('/{signatureRequest}/reject', [SignatureController::class, 'reject'])->name('reject');
    });

    // Blockchain Management
    Route::prefix('blockchain')->name('blockchain.')->group(function () {
        Route::get('/transactions', [KaprodiController::class, 'blockchainTransactions'])->name('transactions');
        Route::get('/verify', [KaprodiController::class, 'blockchainVerify'])->name('verify');
        Route::get('/status', [KaprodiController::class, 'blockchainStatus'])->name('status');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/monthly', [KaprodiController::class, 'monthlyReport'])->name('monthly');
        Route::get('/quarterly', [KaprodiController::class, 'quarterlyReport'])->name('quarterly');
        Route::get('/annual', [KaprodiController::class, 'annualReport'])->name('annual');
    });

    // API endpoints for dashboard
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats', [KaprodiController::class, 'getStats'])->name('stats');
        Route::get('/chart-data/{period}', [KaprodiController::class, 'getChartData'])->name('chart-data');
        Route::get('/blockchain-status', [KaprodiController::class, 'getBlockchainStatus'])->name('blockchain-status');
    });
});

// mahasiswa
Route::prefix('mahasiswa')
    ->middleware(['auth:web'])
    ->group(function () {
        // Route::get('/approval-requests/upload', [ApprovalRequestController::class, 'create'])->name('approval-request.create');
        Route::post('/approval-requests/upload', [ApprovalRequestController::class, 'upload'])->name('approval-request.upload');
        Route::get('/status', [ApprovalRequestController::class, 'status'])->name('approval-request.status');

        Route::resource('documents',
        DocumentController::class);
            Route::resource('signatures',
        SignatureController::class);
            Route::post('signatures/{signature}/process',
        [SignatureController::class, 'processSignature']);
    });


Route::get('/', [HomeController::class, 'index']);
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
Route::get('/aproval', [ApprovalRequestController::class, 'showUploadForm']);


Route::get('events', [EventController::class, 'index'])->name('events.index');
Route::get('events/create', [EventController::class, 'create'])->name('events.create');
Route::post('events', [EventController::class, 'store'])->name('events.store');
Route::get('layanans', [LayananController::class, 'index'])->name('layanan.index');

// Public Routes - Signature Validation
Route::prefix('validation')->name('validation.')->group(function () {
    Route::get('/', [App\Http\Controllers\SignatureValidationController::class, 'index'])->name('index');
    Route::post('/hash', [App\Http\Controllers\SignatureValidationController::class, 'validateByHash'])->name('hash');
    Route::post('/qr', [App\Http\Controllers\SignatureValidationController::class, 'validateByQR'])->name('qr');
    Route::post('/file', [App\Http\Controllers\SignatureValidationController::class, 'validateByFile'])->name('file');
    Route::get('/report', [App\Http\Controllers\SignatureValidationController::class, 'generateReport'])->name('report');
});




Route::get('/approval-requests/download-signed/{id}', [ApprovalRequestController::class, 'downloadSignedDocument'])->name('approval-request.downloadSignedDocument');



