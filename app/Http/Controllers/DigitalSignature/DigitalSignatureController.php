<?php

namespace App\Http\Controllers\DigitalSignature;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Services\QRCodeService;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use App\Models\SignatureAuditLog;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\PDFSignatureService;
use App\Services\VerificationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\Validator;
use App\Mail\ApprovalRequestSignedNotification;
use Illuminate\Support\Facades\DB;

class DigitalSignatureController extends Controller
{
    protected $digitalSignatureService;
    protected $qrCodeService;
    protected $verificationService;
    protected $pdfSignatureService;

    public function __construct(
        DigitalSignatureService $digitalSignatureService,
        QRCodeService $qrCodeService,
        VerificationService $verificationService,
        PDFSignatureService $pdfSignatureService
    ) {
        $this->digitalSignatureService = $digitalSignatureService;
        $this->qrCodeService = $qrCodeService;
        $this->verificationService = $verificationService;
        $this->pdfSignatureService = $pdfSignatureService;
    }

    /**
     * Admin dashboard untuk digital signature management
     */
    //! DIPAKAI DI ROUTE web.php
    public function adminDashboard()
    {
        try {
            $stats = [
                'total_signatures' => DigitalSignature::count(),
                'active_signatures' => DigitalSignature::active()->count(),
                'expired_signatures' => DigitalSignature::where('valid_until', '<', now())->count(),
                'total_documents_signed' => DocumentSignature::count(),
                'pending_signatures' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_PENDING)->count(),
                'verified_signatures' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_VERIFIED)->count(),
                // NEW: Approval Request stats
                'pending_approvals' => ApprovalRequest::where('status', ApprovalRequest::STATUS_PENDING)->count(),
                // 'need_verification' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_SIGNED)->count(),
                // 'rejected_signatures' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_REJECTED)->count(),
            ];


            // $recentSignatures = DigitalSignature::with('creator')
            //     ->latest()
            //     ->limit(10)
            //     ->get();

            // $expiringSignatures = DigitalSignature::expiringSoon(30)
            //     ->with('creator')
            //     ->get();

            // NEW: Pending Approval Requests for quick approve
            $pendingApprovals = ApprovalRequest::with(['user:id,name,NIM'])
                ->where('status', ApprovalRequest::STATUS_PENDING)
                ->latest()
                ->limit(5)
                ->get();

            // NEW: Signed Documents needing verification for quick verify
            $needVerification = DocumentSignature::with([
                    'approvalRequest:id,document_name,nomor,user_id',
                    'approvalRequest.user:id,name,NIM',
                    'signer:id,name'
                ])
                ->where('signature_status', DocumentSignature::STATUS_SIGNED)
                ->latest('signed_at')
                ->limit(5)
                ->get();

            $verificationStats = $this->verificationService->getVerificationStatistics();

            // NEW: Key stats for dashboard widget
            $keyStats = [
                'active_keys' => DigitalSignature::active()->count(),
                'expiring_soon' => DigitalSignature::expiringSoon(30)->count(),
                'urgent_expiry' => DigitalSignature::expiringSoon(7)->count(),
                // 'revoked_keys' => DigitalSignature::where('status', 'revoked')->count(),
            ];

            // Get expiring keys for widget
            $expiringKeys = DigitalSignature::with(['documentSignature.approvalRequest'])
                ->expiringSoon(30)
                ->orderBy('valid_until', 'asc')
                ->limit(5)
                ->get();

            return view('digital-signature.admin.dashboard', compact(
                'stats',
                // 'recentSignatures',
                // 'expiringSignatures',
                'pendingApprovals',
                'needVerification',
                'verificationStats',
                'keyStats',
                'expiringKeys'
            ));

        } catch (\Exception $e) {
            Log::error('Admin dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load dashboard data');
        }
    }

    /**
     * REFACTORED: Document signing interface untuk user
     * Shows QR drag & drop interface (no signature template)
     */
    public function signDocument($approvalRequestId)
    {
        try {
            $approvalRequest = ApprovalRequest::with(['user', 'documentSignature'])->findOrFail($approvalRequestId);

            // Check authorization
            if ($approvalRequest->user_id !== Auth::id()) {
                abort(403, 'Unauthorized to sign this document');
            }

            // Check if document is ready to be signed
            if (!$approvalRequest->canBeSignedByUser()) {
                return redirect()->route('digital-signature.user.signature.approval.status')
                    ->with('error', 'Document is not ready for signing');
            }

            // DocumentSignature should already exist (created during approval)
            $documentSignature = $approvalRequest->documentSignature;

            if (!$documentSignature) {
                Log::error('DocumentSignature not found for approved request', [
                    'approval_request_id' => $approvalRequestId
                ]);
                return back()->with('error', 'Document signature record not found. Please contact administrator.');
            }

            // Check if temporary QR code exists
            if (!$documentSignature->temporary_qr_code_path) {
                Log::warning('Temporary QR code not found, regenerating', [
                    'document_signature_id' => $documentSignature->id
                ]);

                try {
                    $documentSignature->generateTemporaryQRCode();
                } catch (\Exception $qrException) {
                    Log::error('Failed to generate temporary QR code', [
                        'error' => $qrException->getMessage()
                    ]);
                }
            }

            return view('digital-signature.user.sign-document', compact(
                'approvalRequest',
                'documentSignature'
            ));

        } catch (\Exception $e) {
            Log::error('Sign document interface error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load signing interface');
        }
    }

    /**
     * REFACTORED: Process document signing with QR drag & drop
     * Flow: Save QR positioning → Embed QR → Auto-generate key → Sign document
     */
    //! DIPAKAI DI ROUTE web.php
    public function processDocumentSigning(Request $request, $approvalRequestId)
    {
        $startTime = microtime(true); // Track signing duration

        // Validate QR positioning data
        $validator = Validator::make($request->all(), [
            'qr_positioning_data' => 'required|string', // JSON string with QR position from drag & drop
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid QR positioning data',
                'errors' => $validator->errors()
            ], 400);
        }

        // Variables to track for cleanup on error
        $qrData = null;
        $pdfWithQRPath = null;
        $originalPdfPath = null;

        try {
            $approvalRequest = ApprovalRequest::with('documentSignature')->findOrFail($approvalRequestId);

            // Check authorization
            if ($approvalRequest->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            // Check if ready to sign
            if (!$approvalRequest->canBeSignedByUser()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Document is not ready for signing'
                ], 400);
            }

            // Get DocumentSignature (should already exist from approval)
            $documentSignature = $approvalRequest->documentSignature;

            if (!$documentSignature) {
                return response()->json([
                    'success' => false,
                    'error' => 'Document signature record not found'
                ], 400);
            }

            // Parse QR positioning data from user drag & drop
            $qrPositioningData = json_decode($request->qr_positioning_data, true);

            if (!$qrPositioningData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid QR positioning data format'
                ], 400);
            }

            // ✅ SECURITY FIX: MANDATORY document integrity check before signing
            $originalPdfPath = Storage::disk('public')->path($approvalRequest->document_path);
            $currentDocumentHash = hash_file('sha256', $originalPdfPath);
            $storedDocumentHash = $approvalRequest->workflow_metadata['document_hash'] ?? null;

            // ✅ CRITICAL: Stored hash MUST exist (no null bypass)
            if (!$storedDocumentHash) {
                Log::error('Document hash not found in workflow metadata', [
                    'approval_request_id' => $approvalRequestId,
                    'workflow_metadata' => $approvalRequest->workflow_metadata
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Document integrity data missing. This document was approved before hash verification was implemented. Please request a new approval.'
                ], 400);
            }

            // ✅ Verify hash integrity
            if ($currentDocumentHash !== $storedDocumentHash) {
                Log::error('Document integrity check failed - hash mismatch', [
                    'approval_request_id' => $approvalRequestId,
                    'current_hash' => $currentDocumentHash,
                    'stored_hash' => $storedDocumentHash,
                    'hash_generated_at' => $approvalRequest->workflow_metadata['hash_generated_at'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Document integrity check failed. The document has been modified after approval. Please request a new approval.'
                ], 400);
            }

            Log::info('Starting document signing process', [
                'approval_request_id' => $approvalRequestId,
                'document_signature_id' => $documentSignature->id,
                'qr_positioning' => $qrPositioningData,
                'document_integrity' => 'verified'
            ]);

            // ========================================================================
            // CRITICAL FIX: Wrap STEPS 1-7 in DB Transaction for data consistency
            // ========================================================================
            $result = DB::transaction(function () use (
                $documentSignature,
                $qrPositioningData,
                $approvalRequest,
                $approvalRequestId,
                $request,
                $startTime,
                &$qrData,
                &$pdfWithQRPath,
                $originalPdfPath
            ) {
                // ========================================================================
                // STEP 1: Save QR Positioning Data
                // ========================================================================
                $documentSignature->saveQRPositioning($qrPositioningData);

                // ========================================================================
                // STEP 2: Generate Final Verification QR Code
                // ========================================================================
                $qrData = $this->qrCodeService->generateVerificationQR($documentSignature->id);
                $qrCodeAbsolutePath = Storage::disk('public')->path($qrData['qr_code_path']);

                Log::info('Final QR code generated for signing', [
                    'qr_code_path' => $qrData['qr_code_path'],
                    'verification_url' => $qrData['verification_url']
                ]);

                // ========================================================================
                // STEP 3: Embed QR Code into PDF at User-Defined Position
                // ========================================================================
                $pdfWithQRPath = $this->pdfSignatureService->embedQRCodeIntoPDF(
                    $originalPdfPath,
                    $qrCodeAbsolutePath,
                    $qrPositioningData,
                    $documentSignature
                );

                Log::info('QR code embedded into PDF', [
                    'original_pdf' => $approvalRequest->document_path,
                    'pdf_with_qr' => $pdfWithQRPath
                ]);

                // ========================================================================
                // STEP 4: Sign Document with Auto-Generated Unique Key
                // This will create DigitalSignature, generate CMS signature, and update DocumentSignature
                // ========================================================================
                $signedDocumentSignature = $this->digitalSignatureService->signDocumentWithUniqueKey(
                    $documentSignature,
                    $pdfWithQRPath
                );

                Log::info('Document signed with auto-generated unique key', [
                    'document_signature_id' => $signedDocumentSignature->id,
                    'digital_signature_id' => $signedDocumentSignature->digital_signature_id,
                    'final_pdf_path' => $signedDocumentSignature->final_pdf_path
                ]);

                // ========================================================================
                // STEP 5: Update DocumentSignature with Additional Metadata
                // ========================================================================
                $signedDocumentSignature->update([
                    'qr_code_path' => $qrData['qr_code_path'],
                    'verification_url' => $qrData['verification_url'],
                    'signature_metadata' => array_merge($signedDocumentSignature->signature_metadata ?? [], [
                        'placement_method' => 'drag_drop_qr',
                        'signed_via' => 'web_interface',
                        'browser' => $request->userAgent(),
                        'qr_positioning' => $qrPositioningData,
                        'auto_generated_key' => true,
                        'document_hash_verified' => true
                    ])
                ]);

                // ========================================================================
                // STEP 6: Update Approval Request Status (Auto-Verify)
                // ========================================================================
                $approvalRequest->markUserSigned($pdfWithQRPath);

                // Calculate signing duration
                $durationMs = (int) ((microtime(true) - $startTime) * 1000);

                Log::info('Document signing completed successfully', [
                    'approval_request_id' => $approvalRequestId,
                    'document_signature_id' => $signedDocumentSignature->id,
                    'digital_signature_id' => $signedDocumentSignature->digitalSignature->signature_id ?? null,
                    'duration_ms' => $durationMs,
                    'user_id' => Auth::id()
                ]);

                // ========================================================================
                // STEP 7: Create Audit Log
                // ========================================================================
                $metadata = SignatureAuditLog::createMetadata([
                    'digital_signature_id' => $signedDocumentSignature->digitalSignature->signature_id ?? null,
                    'qr_positioning' => $qrPositioningData,
                    'duration_ms' => $durationMs,
                    'document_name' => $approvalRequest->document_name,
                    'placement_method' => 'drag_drop_qr',
                    'signed_via' => 'web_interface',
                    'auto_generated_key' => true,
                    'qr_generated' => true,
                    'document_hash_verified' => true
                ]);

                SignatureAuditLog::create([
                    'document_signature_id' => $signedDocumentSignature->id,
                    'approval_request_id' => $approvalRequestId,
                    'user_id' => Auth::id(),
                    'action' => SignatureAuditLog::ACTION_DOCUMENT_SIGNED,
                    'status_from' => ApprovalRequest::STATUS_APPROVED,
                    'status_to' => ApprovalRequest::STATUS_SIGN_APPROVED,
                    'description' => "Document '{$approvalRequest->document_name}' signed and auto-approved with unique digital signature key",
                    'metadata' => $metadata,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'performed_at' => now()
                ]);

                return [
                    'signedDocumentSignature' => $signedDocumentSignature,
                    'approvalRequest' => $approvalRequest,
                    'durationMs' => $durationMs
                ];
            });

            // Extract results from transaction
            $signedDocumentSignature = $result['signedDocumentSignature'];
            $approvalRequest = $result['approvalRequest'];
            $durationMs = $result['durationMs'];

            // ========================================================================
            // STEP 8: Send Notification to Kaprodi (Outside transaction)
            // ========================================================================
            try {
                // Send success notification to student with signed PDF and QR code
                if ($approvalRequest->user) {
                    Mail::to($approvalRequest->user->email)->send(
                        new ApprovalRequestSignedNotification(
                            $approvalRequest,
                            $signedDocumentSignature
                        )
                    );
                }

                // $kaprodiEmails = \App\Models\Kaprodi::pluck('email')->toArray();
                // if (!empty($kaprodiEmails)) {
                //     \Illuminate\Support\Facades\Mail::to($kaprodiEmails)->send(
                //         new \App\Mail\DocumentSignedByUserNotification($approvalRequest, $signedDocumentSignature)
                //     );
                // }
            } catch (\Exception $mailException) {
                Log::warning('Failed to send notification email', [
                    'error' => $mailException->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Document signed successfully with unique digital signature key',
                'data' => [
                    'document_signature_id' => $signedDocumentSignature->id,
                    'digital_signature_id' => $signedDocumentSignature->digital_signature_id,
                    'approval_request_id' => $approvalRequest->id,
                    'status' => $approvalRequest->status,
                    'qr_code_url' => $qrData['qr_code_url'] ?? null,
                    'verification_url' => $qrData['verification_url'] ?? null,
                    'signed_pdf_path' => $signedDocumentSignature->final_pdf_path
                ]
            ]);

        } catch (\Exception $e) {
            // ========================================================================
            // HIGH PRIORITY FIX: File cleanup on error
            // ========================================================================
            Log::error('Document signing process failed - Starting cleanup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'approval_request_id' => $approvalRequestId
            ]);

            // Cleanup generated files on error
            if ($qrData && isset($qrData['qr_code_path']) && Storage::disk('public')->exists($qrData['qr_code_path'])) {
                try {
                    Storage::disk('public')->delete($qrData['qr_code_path']);
                    Log::info('Cleaned up QR code after error', ['path' => $qrData['qr_code_path']]);
                } catch (\Exception $cleanupException) {
                    Log::warning('Failed to cleanup QR code', ['error' => $cleanupException->getMessage()]);
                }
            }

            if ($pdfWithQRPath && Storage::disk('public')->exists($pdfWithQRPath)) {
                try {
                    Storage::disk('public')->delete($pdfWithQRPath);
                    Log::info('Cleaned up PDF with QR after error', ['path' => $pdfWithQRPath]);
                } catch (\Exception $cleanupException) {
                    Log::warning('Failed to cleanup PDF with QR', ['error' => $cleanupException->getMessage()]);
                }
            }
            Log::error('Document signing process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'approval_request_id' => $approvalRequestId
            ]);

            // Calculate duration even on failure
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            // Create error audit log
            $metadata = SignatureAuditLog::createMetadata([
                'error_code' => 'SIGN_FAILED',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'duration_ms' => $durationMs,
                'approval_request_id' => $approvalRequestId,
                'exception_type' => get_class($e),
            ]);

            SignatureAuditLog::create([
                'document_signature_id' => $documentSignature->id ?? null,
                'approval_request_id' => $approvalRequestId,
                'user_id' => Auth::id(),
                'action' => SignatureAuditLog::ACTION_SIGNING_FAILED,
                'status_from' => ApprovalRequest::STATUS_APPROVED,
                'status_to' => null,
                'description' => "Document signing failed: {$e->getMessage()}",
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Signing process failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Signature canvas interface
     */
    // public function signatureCanvas($approvalRequestId)
    // {
    //     try {
    //         $approvalRequest = ApprovalRequest::with('user')->findOrFail($approvalRequestId);

    //         // Check authorization
    //         if ($approvalRequest->user_id !== Auth::id()) {
    //             abort(403, 'Unauthorized');
    //         }

    //         $digitalSignature = DigitalSignature::active()->valid()->first();
    //         $documentSignature = DocumentSignature::where('approval_request_id', $approvalRequestId)->first();

    //         // Get signature template (default atau custom)
    //         $template = \App\Models\SignatureTemplate::default()->first();
    //         $canvasData = $template ? $template->generateCanvasData($documentSignature) : null;

    //         return view('digital-signature.components.signature-canvas', compact(
    //             'approvalRequest',
    //             'digitalSignature',
    //             'documentSignature',
    //             'canvasData'
    //         ));

    //     } catch (\Exception $e) {
    //         Log::error('Signature canvas error: ' . $e->getMessage());
    //         return back()->with('error', 'Failed to load signature canvas');
    //     }
    // }

    /**
     * Verification tools untuk admin
     */
    // public function verificationTools()
    // {
    //     try {
    //         $recentVerifications = DocumentSignature::with(['approvalRequest', 'signer'])
    //             ->where('signature_status', DocumentSignature::STATUS_VERIFIED)
    //             ->latest('verified_at')
    //             ->limit(20)
    //             ->get();

    //         $verificationStats = $this->verificationService->getVerificationStatistics();

    //         return view('digital-signature.admin.verification-tools', compact(
    //             'recentVerifications',
    //             'verificationStats'
    //         ));

    //     } catch (\Exception $e) {
    //         Log::error('Verification tools error: ' . $e->getMessage());
    //         return back()->with('error', 'Failed to load verification tools');
    //     }
    // }

    /**
     * Manual verification oleh admin
     */
    // public function manualVerification(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'document_signature_id' => 'required|exists:document_signatures,id'
    //     ]);

    //     if ($validator->fails()) {
    //         return back()->withErrors($validator);
    //     }

    //     try {
    //         $verificationResult = $this->verificationService->verifyById($request->document_signature_id);

    //         if ($verificationResult['is_valid']) {
    //             // Update status jika valid
    //             $documentSignature = DocumentSignature::findOrFail($request->document_signature_id);
    //             $documentSignature->update([
    //                 'signature_status' => DocumentSignature::STATUS_VERIFIED,
    //                 'verified_at' => now(),
    //                 'verified_by' => Auth::id()
    //             ]);

    //             return back()->with('success', 'Document signature verified successfully');
    //         } else {
    //             return back()->with('error', 'Verification failed: ' . $verificationResult['message']);
    //         }

    //     } catch (\Exception $e) {
    //         Log::error('Manual verification failed: ' . $e->getMessage());
    //         return back()->with('error', 'Verification process failed');
    //     }
    // }

    /**
     * Export signature statistics
     */
    // public function exportStatistics(Request $request)
    // {
    //     try {
    //         $period = $request->get('period', 30);
    //         $format = $request->get('format', 'json');

    //         $stats = $this->verificationService->getVerificationStatistics($period);

    //         // Add detailed signature data
    //         $signatures = DigitalSignature::with(['creator', 'documentSignatures'])
    //             ->where('created_at', '>=', now()->subDays($period))
    //             ->get();

    //         $exportData = [
    //             'period' => $period,
    //             'generated_at' => now(),
    //             'statistics' => $stats,
    //             'signatures' => $signatures->map(function ($signature) {
    //                 return [
    //                     'id' => $signature->id,
    //                     'signature_id' => $signature->signature_id,
    //                     'algorithm' => $signature->algorithm,
    //                     'status' => $signature->status,
    //                     'created_at' => $signature->created_at,
    //                     'valid_until' => $signature->valid_until,
    //                     'documents_signed' => $signature->documentSignatures->count(),
    //                     'creator' => $signature->creator->name ?? 'Unknown'
    //                 ];
    //             })
    //         ];

    //         if ($format === 'csv') {
    //             return $this->exportToCSV($exportData);
    //         }

    //         return response()->json($exportData);

    //     } catch (\Exception $e) {
    //         Log::error('Export statistics failed: ' . $e->getMessage());
    //         return response()->json(['error' => 'Export failed'], 500);
    //     }
    // }

    /**
     * Generate test signature untuk development
     */
    // public function generateTestSignature()
    // {
    //     if (!app()->environment('local')) {
    //         abort(403, 'Test signatures only available in development environment');
    //     }

    //     try {
    //         $signature = $this->digitalSignatureService->createDigitalSignature(
    //             'Test Signature - Development',
    //             Auth::id(),
    //             1
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'signature_id' => $signature->signature_id,
    //             'message' => 'Test signature created successfully'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Test signature creation failed'], 500);
    //     }
    // }

    /**
     * Export data to CSV format
     */
    // private function exportToCSV($data)
    // {
    //     $filename = 'signature_statistics_' . date('Y-m-d_H-i-s') . '.csv';

    //     $headers = [
    //         'Content-Type' => 'text/csv',
    //         'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    //     ];

    //     $callback = function() use ($data) {
    //         $handle = fopen('php://output', 'w');

    //         // CSV headers
    //         fputcsv($handle, [
    //             'Signature ID',
    //             'Algorithm',
    //             'Status',
    //             'Created At',
    //             'Valid Until',
    //             'Documents Signed',
    //             'Creator'
    //         ]);

    //         // CSV data
    //         foreach ($data['signatures'] as $signature) {
    //             fputcsv($handle, [
    //                 $signature['signature_id'],
    //                 $signature['algorithm'],
    //                 $signature['status'],
    //                 $signature['created_at'],
    //                 $signature['valid_until'],
    //                 $signature['documents_signed'],
    //                 $signature['creator']
    //             ]);
    //         }

    //         fclose($handle);
    //     };

    //     return response()->stream($callback, 200, $headers);
    // }

    /**
     * DEPRECATED: Get available signature templates untuk user signing
     * No longer using signature templates, using QR code only
     */
    // public function getTemplatesForSigning($approvalRequestId)
    // {
    //     return response()->json([
    //         'success' => false,
    //         'error' => 'Signature templates are deprecated. Using QR code drag & drop instead.'
    //     ], 410); // 410 Gone - resource no longer available
    // }

    // ==================== DIGITAL SIGNATURE KEYS MANAGEMENT ====================

    /**
     * Display list of all digital signature keys
     * Route: admin.signature.keys.index
     */
    public function keysIndex(Request $request)
    {
        try {
            $query = DigitalSignature::with(['documentSignature.approvalRequest']);

            // Filter by status
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }

            // Filter by expiry
            if ($request->has('expiry')) {
                switch ($request->expiry) {
                    case 'expiring_soon':
                        $query->expiringSoon(30);
                        break;
                    case 'expired':
                        $query->where('valid_until', '<', now());
                        break;
                    case 'valid':
                        $query->valid();
                        break;
                }
            }

            // Search by signature_id
            if ($request->has('search') && $request->search) {
                $query->where('signature_id', 'like', '%' . $request->search . '%');
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $keys = $query->paginate(10);

            // Statistics
            $stats = [
                'total' => DigitalSignature::count(),
                'active' => DigitalSignature::active()->count(),
                'expiring_soon' => DigitalSignature::expiringSoon(30)->count(),
                'urgent_expiry' => DigitalSignature::expiringSoon(7)->count(),
                'revoked' => DigitalSignature::where('status', 'revoked')->count(),
                'expired' => DigitalSignature::where('valid_until', '<', now())->count(),
            ];

            return view('digital-signature.admin.keys.index', compact('keys', 'stats'));

        } catch (\Exception $e) {
            Log::error('Keys index error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat data digital signature keys');
        }
    }

    /**
     * Display detailed information about a specific key
     * Route: admin.signature.keys.show
     */
    public function keyShow($id)
    {
        try {
            $key = DigitalSignature::with([
                'documentSignature.approvalRequest.user',
                'documentSignature.signer',
                'documentSignature.verificationLogs',
                'documentSignature.auditLogs'
            ])->findOrFail($id);

            // Get usage statistics
            $usageStats = [
                'total_verifications' => $key->documentSignature->verificationLogs()->count(),
                'successful_verifications' => $key->documentSignature->verificationLogs()
                    ->where('is_valid', true)->count(),
                'verification_rate' => 0,
                'last_verification' => $key->documentSignature->verificationLogs()
                    ->latest('verified_at')->first()?->verified_at,
            ];

            if ($usageStats['total_verifications'] > 0) {
                $usageStats['verification_rate'] = round(
                    ($usageStats['successful_verifications'] / $usageStats['total_verifications']) * 100,
                    2
                );
            }

            // Days until expiry
            $daysUntilExpiry = (int) abs(now()->diffInDays($key->valid_until, false));

            return view('digital-signature.admin.keys.show', compact('key', 'usageStats', 'daysUntilExpiry'));

        } catch (\Exception $e) {
            Log::error('Key show error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat detail digital signature key');
        }
    }

    /**
     * Revoke a digital signature key
     * Route: admin.signature.keys.revoke
     */
    public function revokeKey(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'required|string|min:10|max:500',
            ], [
                'reason.required' => 'Alasan revoke wajib diisi',
                'reason.min' => 'Alasan revoke minimal 10 karakter',
                'reason.max' => 'Alasan revoke maksimal 500 karakter',
            ]);

            $key = DigitalSignature::findOrFail($id);

            // Check if already revoked
            // if ($key->status === DigitalSignature::STATUS_REVOKED) {
            //     return back()->with('warning', 'Signature key sudah di-revoke sebelumnya');
            // }

            // Revoke the key
            $key->revoke($request->reason);

            // Also invalidate the related document signature
            if ($key->documentSignature) {
                $key->documentSignature->invalidate('Key revoked: ' . $request->reason);
            }

            return redirect()
                ->route('admin.signature.keys.show', $id)
                ->with('success', 'Digital signature key berhasil di-revoke');

        } catch (\Exception $e) {
            Log::error('Key revoke error: ' . $e->getMessage());
            return back()->with('error', 'Gagal melakukan revoke key: ' . $e->getMessage());
        }
    }

    /**
     * Export public key
     * Route: admin.signature.keys.export.public
     */
    public function exportPublicKey($id)
    {
        try {
            $key = DigitalSignature::findOrFail($id);

            $filename = 'public_key_' . $key->signature_id . '.pem';

            return response($key->public_key, 200)
                ->header('Content-Type', 'application/x-pem-file')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Export public key error: ' . $e->getMessage());
            return back()->with('error', 'Gagal export public key');
        }
    }

    /**
     * View audit log for a specific key
     * Route: admin.signature.keys.audit
     */
    public function keyAuditLog($id)
    {
        try {
            $key = DigitalSignature::findOrFail($id);

            $auditLogs = SignatureAuditLog::where('document_signature_id', $key->document_signature_id)
                ->orWhere('metadata->signature_id', $key->signature_id)
                ->orderBy('performed_at', 'desc')
                ->paginate(20);

            return view('digital-signature.admin.keys.audit-log', compact('key', 'auditLogs'));

        } catch (\Exception $e) {
            Log::error('Key audit log error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat audit log');
        }
    }

    /**
     * View certificate details (can be modal or page)
     * Route: admin.signature.keys.certificate
     */
    public function viewCertificate($id)
    {
        try {
            $key = DigitalSignature::findOrFail($id);

            if (!$key->certificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificate tidak tersedia untuk key ini'
                ], 404);
            }

            // Parse certificate information
            $certInfo = $this->parseCertificateInfo($key->certificate);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'certificate' => $certInfo,
                    'signature_id' => $key->signature_id
                ]);
            }

            return view('digital-signature.admin.keys.certificate', compact('key', 'certInfo'));

        } catch (\Exception $e) {
            Log::error('View certificate error: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memuat certificate'
                ], 500);
            }

            return back()->with('error', 'Gagal memuat certificate details');
        }
    }

    /**
     * Helper: Parse REAL certificate information using OpenSSL
     *
     * @param string $certificate Certificate in PEM format
     * @return array Parsed certificate information
     */
    // private function parseCertificateInfo($certificate)
    // {
    //     try {
    //         // Check if certificate is in fallback format (base64 encoded JSON)
    //         if (strpos($certificate, '-----BEGIN CERTIFICATE-----') !== false) {
    //             // Extract the base64 part
    //             $certificateLines = explode("\n", $certificate);
    //             $base64Content = '';
    //             foreach ($certificateLines as $line) {
    //                 if (strpos($line, '-----') === false) {
    //                     $base64Content .= trim($line);
    //                 }
    //             }

    //             // Try to decode as JSON (fallback format)
    //             $decoded = base64_decode($base64Content);
    //             $jsonData = @json_decode($decoded, true);

    //             Log::info('Attempting to parse certificate, checking for fallback format');

    //             Log::debug('Decoded certificate content', ['decoded' => $decoded]);
    //             Log::debug('JSON parse result', ['jsonData' => $jsonData]);

    //             if ($jsonData && isset($jsonData['subject'])) {
    //                 // This is fallback format, parse differently
    //                 Log::info('Detected fallback certificate format');

    //                 return [
    //                     'is_fallback' => true,
    //                     'subject' => [
    //                         'CN' => $jsonData['subject'] ?? 'N/A',
    //                     ],
    //                     'issuer' => [
    //                         'CN' => $jsonData['issuer'] ?? 'N/A',
    //                     ],
    //                     'version' => 3,
    //                     'serial_number' => $jsonData['serial_number'] ?? 'N/A',
    //                     'signature_algorithm' => 'SHA256withRSA',
    //                     'public_key_algorithm' => 'RSA (2048 bit)',
    //                     'valid_from' => $jsonData['valid_from'] ?? 'N/A',
    //                     'valid_until' => $jsonData['valid_until'] ?? 'N/A',
    //                     'fingerprints' => [
    //                         'sha256' => hash('sha256', $certificate),
    //                         'sha1' => hash('sha1', $certificate),
    //                     ]
    //                 ];
    //             }
    //         }

    //         // ✅ PARSE REAL X.509 Certificate using OpenSSL
    //         $certData = openssl_x509_parse($certificate);

    //         if (!$certData) {
    //             throw new \Exception('Failed to parse certificate: ' . openssl_error_string());
    //         }

    //         Log::info('Successfully parsed X.509 certificate', [
    //             'subject_CN' => $certData['subject']['CN'] ?? 'N/A',
    //             'issuer_CN' => $certData['issuer']['CN'] ?? 'N/A',
    //             'serial' => $certData['serialNumber'] ?? 'N/A'
    //         ]);

    //         // ✅ Get REAL X.509 fingerprints using OpenSSL
    //         $sha256Fingerprint = openssl_x509_fingerprint($certificate, 'sha256');
    //         $sha1Fingerprint = openssl_x509_fingerprint($certificate, 'sha1');

    //         // Format fingerprints with colons (standard X.509 format)
    //         $sha256Formatted = strtoupper(chunk_split($sha256Fingerprint, 2, ':'));
    //         $sha256Formatted = rtrim($sha256Formatted, ':');
    //         $sha1Formatted = strtoupper(chunk_split($sha1Fingerprint, 2, ':'));
    //         $sha1Formatted = rtrim($sha1Formatted, ':');

    //         // ✅ Extract REAL data from parsed certificate
    //         $info = [
    //             'is_fallback' => false,
    //             'subject' => [
    //                 'CN' => $certData['subject']['CN'] ?? 'N/A',
    //                 'OU' => $certData['subject']['OU'] ?? $certData['subject']['organizationalUnitName'] ?? 'N/A',
    //                 'O' => $certData['subject']['O'] ?? $certData['subject']['organizationName'] ?? 'N/A',
    //                 'C' => $certData['subject']['C'] ?? $certData['subject']['countryName'] ?? 'N/A',
    //                 'ST' => $certData['subject']['ST'] ?? $certData['subject']['stateOrProvinceName'] ?? null,
    //                 'L' => $certData['subject']['L'] ?? $certData['subject']['localityName'] ?? null,
    //                 'emailAddress' => $certData['subject']['emailAddress'] ?? null,
    //             ],
    //             'issuer' => [
    //                 'CN' => $certData['issuer']['CN'] ?? 'N/A',
    //                 'OU' => $certData['issuer']['OU'] ?? $certData['issuer']['organizationalUnitName'] ?? 'N/A',
    //                 'O' => $certData['issuer']['O'] ?? $certData['issuer']['organizationName'] ?? 'N/A',
    //                 'C' => $certData['issuer']['C'] ?? $certData['issuer']['countryName'] ?? 'N/A',
    //             ],
    //             'version' => ($certData['version'] ?? 2) + 1, // OpenSSL returns 0-based version (0=v1, 1=v2, 2=v3)
    //             'serial_number' => isset($certData['serialNumberHex'])
    //                 ? strtoupper($certData['serialNumberHex'])
    //                 : (isset($certData['serialNumber'])
    //                     ? strtoupper(sprintf('%X', $certData['serialNumber']))
    //                     : 'N/A'),
    //             'signature_algorithm' => $certData['signatureTypeLN'] ?? $certData['signatureTypeSN'] ?? 'sha256WithRSAEncryption',
    //             'public_key_algorithm' => 'RSA (' . ($certData['bits'] ?? 2048) . ' bit)',
    //             'valid_from' => isset($certData['validFrom_time_t'])
    //                 ? date('Y-m-d H:i:s', $certData['validFrom_time_t'])
    //                 : 'N/A',
    //             'valid_until' => isset($certData['validTo_time_t'])
    //                 ? date('Y-m-d H:i:s', $certData['validTo_time_t'])
    //                 : 'N/A',
    //             'valid_from_timestamp' => $certData['validFrom_time_t'] ?? null,
    //             'valid_until_timestamp' => $certData['validTo_time_t'] ?? null,
    //             'fingerprints' => [
    //                 'sha256' => $sha256Formatted,
    //                 'sha256_raw' => $sha256Fingerprint,
    //                 'sha1' => $sha1Formatted,
    //                 'sha1_raw' => $sha1Fingerprint,
    //             ],
    //             'purposes' => $certData['purposes'] ?? [],
    //             'extensions' => $certData['extensions'] ?? [],
    //         ];

    //         // Remove null values from subject and issuer
    //         $info['subject'] = array_filter($info['subject'], function($value) {
    //             return $value !== null;
    //         });
    //         $info['issuer'] = array_filter($info['issuer'], function($value) {
    //             return $value !== null;
    //         });

    //         Log::debug('Certificate info parsed', $info);

    //         return $info;

    //     } catch (\Exception $e) {
    //         Log::error('Failed to parse certificate: ' . $e->getMessage(), [
    //             'certificate_preview' => substr($certificate, 0, 100) . '...',
    //             'error_trace' => $e->getTraceAsString()
    //         ]);

    //         // ❌ Fallback to basic info (only if parsing fails completely)
    //         return [
    //             'error' => true,
    //             'error_message' => 'Failed to parse certificate: ' . $e->getMessage(),
    //             'subject' => [
    //                 'CN' => 'Certificate Parsing Error',
    //                 'O' => 'Universitas Muhammadiyah Tangerang',
    //                 'C' => 'ID'
    //             ],
    //             'issuer' => [
    //                 'CN' => 'Certificate Parsing Error',
    //                 'O' => 'Universitas Muhammadiyah Tangerang',
    //                 'C' => 'ID'
    //             ],
    //             'version' => 'N/A',
    //             'serial_number' => 'N/A',
    //             'signature_algorithm' => 'N/A',
    //             'public_key_algorithm' => 'N/A',
    //             'valid_from' => 'N/A',
    //             'valid_until' => 'N/A',
    //             'fingerprints' => [
    //                 'sha256' => 'N/A',
    //                 'sha1' => 'N/A',
    //             ],
    //         ];
    //     }
    // }
    /**
     * ✅ FIXED: Parse certificate with proper validation
     */
    private function parseCertificateInfo($certificate)
    {
        try {
            // ✅ VALIDATE: Check if certificate is NULL or empty
            if (empty($certificate)) {
                throw new \Exception('Certificate is empty or null');
            }

            // ✅ VALIDATE: Check PEM format markers
            if (!str_contains($certificate, '-----BEGIN CERTIFICATE-----') ||
                !str_contains($certificate, '-----END CERTIFICATE-----')) {
                throw new \Exception('Certificate is not in valid PEM format');
            }

            Log::info('Parsing X.509 certificate');

            // ✅ Try to parse as real X.509 certificate FIRST
            $certData = openssl_x509_parse($certificate);

            if (!$certData) {
                $opensslError = openssl_error_string();
                throw new \Exception("Failed to parse X.509 certificate: {$opensslError}");
            }

            // ✅ If parsing succeeded, this is a REAL X.509 certificate
            Log::info('Successfully parsed REAL X.509 certificate', [
                'subject_CN' => $certData['subject']['CN'] ?? 'N/A',
                'issuer_CN' => $certData['issuer']['CN'] ?? 'N/A',
                'serial' => $certData['serialNumber'] ?? 'N/A',
                'version' => ($certData['version'] ?? 2) + 1
            ]);

            // ✅ Get certificate fingerprints
            $sha256Fingerprint = openssl_x509_fingerprint($certificate, 'sha256');
            $sha1Fingerprint = openssl_x509_fingerprint($certificate, 'sha1');

            // Format fingerprints (XX:XX:XX format)
            $sha256Formatted = strtoupper(implode(':', str_split($sha256Fingerprint, 2)));
            $sha1Formatted = strtoupper(implode(':', str_split($sha1Fingerprint, 2)));

            // ✅ Build certificate info from REAL parsed data
            return [
                'is_real_certificate' => true,
                'is_fallback' => false,
                'subject' => [
                    'CN' => $certData['subject']['CN'] ?? 'N/A',
                    'OU' => $certData['subject']['OU'] ?? $certData['subject']['organizationalUnitName'] ?? 'N/A',
                    'O' => $certData['subject']['O'] ?? $certData['subject']['organizationName'] ?? 'N/A',
                    'C' => $certData['subject']['C'] ?? $certData['subject']['countryName'] ?? 'N/A',
                    'ST' => $certData['subject']['ST'] ?? $certData['subject']['stateOrProvinceName'] ?? null,
                    'L' => $certData['subject']['L'] ?? $certData['subject']['localityName'] ?? null,
                    'emailAddress' => $certData['subject']['emailAddress'] ?? null,
                ],
                'issuer' => [
                    'CN' => $certData['issuer']['CN'] ?? 'N/A',
                    'OU' => $certData['issuer']['OU'] ?? $certData['issuer']['organizationalUnitName'] ?? 'N/A',
                    'O' => $certData['issuer']['O'] ?? $certData['issuer']['organizationName'] ?? 'N/A',
                    'C' => $certData['issuer']['C'] ?? $certData['issuer']['countryName'] ?? 'N/A',
                ],
                'version' => ($certData['version'] ?? 2) + 1,
                'serial_number' => isset($certData['serialNumberHex'])
                    ? strtoupper($certData['serialNumberHex'])
                    : (isset($certData['serialNumber']) ? strtoupper(dechex($certData['serialNumber'])) : 'N/A'),
                'signature_algorithm' => $certData['signatureTypeLN'] ?? $certData['signatureTypeSN'] ?? 'sha256WithRSAEncryption',
                'public_key_algorithm' => 'RSA (' . ($certData['bits'] ?? 2048) . ' bit)',
                'valid_from' => isset($certData['validFrom_time_t'])
                    ? date('Y-m-d H:i:s', $certData['validFrom_time_t'])
                    : 'N/A',
                'valid_until' => isset($certData['validTo_time_t'])
                    ? date('Y-m-d H:i:s', $certData['validTo_time_t'])
                    : 'N/A',
                'valid_from_timestamp' => $certData['validFrom_time_t'] ?? null,
                'valid_until_timestamp' => $certData['validTo_time_t'] ?? null,
                'fingerprints' => [
                    'sha256' => $sha256Formatted,
                    'sha256_raw' => strtoupper($sha256Fingerprint),
                    'sha1' => $sha1Formatted,
                    'sha1_raw' => strtoupper($sha1Fingerprint),
                ],
                'purposes' => $certData['purposes'] ?? [],
                'extensions' => $certData['extensions'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('Certificate parsing failed', [
                'error' => $e->getMessage(),
                'certificate_preview' => substr($certificate ?? '', 0, 200)
            ]);

            // ✅ Check if this is the old fallback JSON format
            try {
                // Extract content between PEM markers
                $lines = explode("\n", $certificate);
                $base64Content = '';
                foreach ($lines as $line) {
                    if (!str_contains($line, '-----')) {
                        $base64Content .= trim($line);
                    }
                }

                $decoded = base64_decode($base64Content);
                $jsonData = @json_decode($decoded, true);

                if ($jsonData && isset($jsonData['subject'])) {
                    Log::warning('Detected OLD fallback JSON certificate format - NEEDS REGENERATION', [
                        'subject' => $jsonData['subject'] ?? 'N/A'
                    ]);

                    return [
                        'is_real_certificate' => false,
                        'is_fallback' => true,
                        'warning' => 'This is an old fallback certificate format. Please regenerate the digital signature key.',
                        'subject' => [
                            'CN' => $jsonData['subject'] ?? 'N/A',
                        ],
                        'issuer' => [
                            'CN' => $jsonData['issuer'] ?? 'N/A',
                        ],
                        'version' => 'N/A (Fallback Format)',
                        'serial_number' => $jsonData['serial_number'] ?? 'N/A',
                        'signature_algorithm' => 'N/A (Fallback Format)',
                        'public_key_algorithm' => 'N/A (Fallback Format)',
                        'valid_from' => $jsonData['valid_from'] ?? 'N/A',
                        'valid_until' => $jsonData['valid_until'] ?? 'N/A',
                        'fingerprints' => [
                            'sha256' => 'N/A (Fallback Format)',
                            'sha1' => 'N/A (Fallback Format)',
                        ],
                    ];
                }
            } catch (\Exception $fallbackCheckEx) {
                // Not fallback format either
            }

            // ✅ Return error info
            return [
                'is_real_certificate' => false,
                'is_fallback' => false,
                'error' => true,
                'error_message' => 'Certificate parsing failed: ' . $e->getMessage(),
                'subject' => ['CN' => 'Certificate Error'],
                'issuer' => ['CN' => 'Certificate Error'],
                'version' => 'N/A',
                'serial_number' => 'N/A',
                'signature_algorithm' => 'N/A',
                'public_key_algorithm' => 'N/A',
                'valid_from' => 'N/A',
                'valid_until' => 'N/A',
                'fingerprints' => [
                    'sha256' => 'N/A',
                    'sha1' => 'N/A',
                ],
            ];
        }
    }
}
