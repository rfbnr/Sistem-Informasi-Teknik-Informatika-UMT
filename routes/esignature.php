<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\KaprodiController;

/*
|--------------------------------------------------------------------------
| E-Signature Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the e-signature system with blockchain integration
|
*/

// Student/User Routes - Document Management
Route::middleware(['auth:web'])->prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::get('/create', [DocumentController::class, 'create'])->name('create');
    Route::post('/', [DocumentController::class, 'store'])->name('store');
    Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
    Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
    Route::get('/{document}/verify-integrity', [DocumentController::class, 'verifyIntegrity'])->name('verify-integrity');

    // Signature Request Routes
    Route::post('/{document}/signature-request', [DocumentController::class, 'createSignatureRequest'])->name('signature-request.create');
});

// Student/User Routes - Signature Management
Route::middleware(['auth:web'])->prefix('signatures')->name('signatures.')->group(function () {
    Route::get('/', [SignatureController::class, 'index'])->name('index');
    Route::get('/{signatureRequest}', [SignatureController::class, 'show'])->name('show');
    Route::get('/{signatureRequest}/sign', [SignatureController::class, 'sign'])->name('sign');
    Route::post('/{signatureRequest}/process', [SignatureController::class, 'processSignature'])->name('process');
    Route::post('/{signatureRequest}/reject', [SignatureController::class, 'reject'])->name('reject');
    Route::get('/{signatureRequest}/download-signed', [SignatureController::class, 'downloadSigned'])->name('download-signed');

    // Verification
    Route::post('/verify', [SignatureController::class, 'verify'])->name('verify');
});

// Kaprodi Routes - Signature Management
Route::middleware(['auth:kaprodi'])->prefix('kaprodi')->name('kaprodi.')->group(function () {
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

// Public Routes - Verification
Route::prefix('verify')->name('verify.')->group(function () {
    Route::get('/document/{hash}', [DocumentController::class, 'publicVerifyDocument'])->name('document');
    Route::get('/signature/{code}', [SignatureController::class, 'publicVerifySignature'])->name('signature');
});

// Public Routes - Signature Validation
Route::prefix('validation')->name('validation.')->group(function () {
    Route::get('/', [App\Http\Controllers\SignatureValidationController::class, 'index'])->name('index');
    Route::post('/hash', [App\Http\Controllers\SignatureValidationController::class, 'validateByHash'])->name('hash');
    Route::post('/qr', [App\Http\Controllers\SignatureValidationController::class, 'validateByQR'])->name('qr');
    Route::post('/file', [App\Http\Controllers\SignatureValidationController::class, 'validateByFile'])->name('file');
    Route::get('/report', [App\Http\Controllers\SignatureValidationController::class, 'generateReport'])->name('report');
});