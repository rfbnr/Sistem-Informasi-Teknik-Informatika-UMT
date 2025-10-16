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
Route::prefix('kaprodi')
    ->middleware(['auth:kaprodi'])
    ->group(function () {
        Route::get('/approval-requests', [ApprovalRequestController::class, 'index'])->name('approval-requests.index');
        Route::get('/approval-requests/{id}/approve', [ApprovalRequestController::class, 'approve'])->name('approval-request.approve');
        Route::get('/approval-requests/{id}/reject', [ApprovalRequestController::class, 'reject'])->name('approval-request.reject');
        Route::post('/approval-requests/{id}/upload-signed-document', [ApprovalRequestController::class, 'uploadSignedDocument'])->name('approval-request.uploadSignedDocument');
        Route::get('/approval-requests/download/{id}', [ApprovalRequestController::class, 'downloadDocument'])->name('approval-request.downloadDocument');
    });

// mahasiswa
Route::prefix('mahasiswa')
    ->middleware(['auth:web'])
    ->group(function () {
        // Route::get('/approval-requests/upload', [ApprovalRequestController::class, 'create'])->name('approval-request.create');
        Route::post('/approval-requests/upload', [ApprovalRequestController::class, 'upload'])->name('approval-request.upload');
        Route::get('/status', [ApprovalRequestController::class, 'status'])->name('approval-request.status');
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




Route::get('/approval-requests/download-signed/{id}', [ApprovalRequestController::class, 'downloadSignedDocument'])->name('approval-request.downloadSignedDocument');



