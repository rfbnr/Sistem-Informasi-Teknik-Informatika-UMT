<?php

namespace App\Http\Controllers\DigitalSignature;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Services\QRCodeService;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use App\Models\SignatureAuditLog;
// REMOVED: SignatureTemplate - no longer using signature templates
// use App\Models\SignatureTemplate;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PDFSignatureService;
use App\Services\VerificationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\Validator;

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
                'need_verification' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_SIGNED)->count(),
                'rejected_signatures' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_REJECTED)->count(),
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

            return view('digital-signature.admin.dashboard', compact(
                'stats',
                // 'recentSignatures',
                // 'expiringSignatures',
                'pendingApprovals',
                'needVerification',
                'verificationStats'
            ));

        } catch (\Exception $e) {
            Log::error('Admin dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load dashboard data');
        }
    }

    /**
     * DEPRECATED: Key management interface
     * Keys are now auto-generated per document, no manual management needed
     */
    // public function keyManagement()
    // {
    //     try {
    //         $signatures = DigitalSignature::with('documentSignature')
    //             ->orderBy('created_at', 'desc')
    //             ->paginate(15);

    //         return view('digital-signature.admin.key-management.index', compact('signatures'));

    //     } catch (\Exception $e) {
    //         Log::error('Key management error: ' . $e->getMessage());
    //         return back()->with('error', 'Failed to load key management data');
    //     }
    // }

    /**
     * DEPRECATED: Create new digital signature key
     * Keys are now auto-generated during document signing
     */
    // public function createSignatureKey(Request $request)
    // {
    //     return back()->with('error', 'Manual key creation is deprecated. Keys are auto-generated per document.');
    // }

    /**
     * DEPRECATED: View signature key details
     * Keys are now auto-generated per document
     */
    // public function viewSignatureKey($id)
    // {
    //     try {
    //         $signature = DigitalSignature::with('documentSignature')->findOrFail($id);

    //         return view('digital-signature.admin.key-management.show', compact('signature'));

    //     } catch (\Exception $e) {
    //         Log::error('View signature key error: ' . $e->getMessage());
    //         return back()->with('error', 'Signature key not found');
    //     }
    // }

    /**
     * DEPRECATED: Revoke signature key
     * Keys are now auto-generated per document and expire with document signature
     */
    // public function revokeSignatureKey(Request $request, $id)
    // {
    //     return back()->with('error', 'Manual key revocation is deprecated. Keys are auto-generated per document.');
    // }

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

            Log::info('Starting document signing process', [
                'approval_request_id' => $approvalRequestId,
                'document_signature_id' => $documentSignature->id,
                'qr_positioning' => $qrPositioningData
            ]);

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
            $originalPdfPath = Storage::disk('public')->path($approvalRequest->document_path);

            $pdfWithQRPath = $this->pdfSignatureService->embedQRCodeIntoPDF(
                $originalPdfPath,
                $qrCodeAbsolutePath,
                $qrPositioningData,
                $documentSignature
            );

            $pdfWithQRAbsolutePath = Storage::disk('public')->path($pdfWithQRPath);

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
                // $pdfWithQRAbsolutePath
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
                    'auto_generated_key' => true
                ])
            ]);

            // ========================================================================
            // STEP 6: Update Approval Request Status
            // ========================================================================
            $approvalRequest->markUserSigned($pdfWithQRPath);
            // $approvalRequest->approveSignature($pdfWithQRPath);

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
            ]);

            SignatureAuditLog::create([
                'document_signature_id' => $signedDocumentSignature->id,
                'approval_request_id' => $approvalRequestId,
                'user_id' => Auth::id(),
                'action' => SignatureAuditLog::ACTION_DOCUMENT_SIGNED,
                'status_from' => ApprovalRequest::STATUS_APPROVED,
                'status_to' => DocumentSignature::STATUS_SIGNED,
                'description' => "Document '{$approvalRequest->document_name}' signed with auto-generated unique key",
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            // ========================================================================
            // STEP 8: Send Notification to Kaprodi
            // ========================================================================
            try {
                $kaprodiEmails = \App\Models\Kaprodi::pluck('email')->toArray();
                if (!empty($kaprodiEmails)) {
                    \Illuminate\Support\Facades\Mail::to($kaprodiEmails)->send(
                        new \App\Mail\DocumentSignedByUserNotification($approvalRequest, $signedDocumentSignature)
                    );
                }
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
    public function verificationTools()
    {
        try {
            $recentVerifications = DocumentSignature::with(['approvalRequest', 'signer'])
                ->where('signature_status', DocumentSignature::STATUS_VERIFIED)
                ->latest('verified_at')
                ->limit(20)
                ->get();

            $verificationStats = $this->verificationService->getVerificationStatistics();

            return view('digital-signature.admin.verification-tools', compact(
                'recentVerifications',
                'verificationStats'
            ));

        } catch (\Exception $e) {
            Log::error('Verification tools error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load verification tools');
        }
    }

    /**
     * Manual verification oleh admin
     */
    public function manualVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_signature_id' => 'required|exists:document_signatures,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $verificationResult = $this->verificationService->verifyById($request->document_signature_id);

            if ($verificationResult['is_valid']) {
                // Update status jika valid
                $documentSignature = DocumentSignature::findOrFail($request->document_signature_id);
                $documentSignature->update([
                    'signature_status' => DocumentSignature::STATUS_VERIFIED,
                    'verified_at' => now(),
                    'verified_by' => Auth::id()
                ]);

                return back()->with('success', 'Document signature verified successfully');
            } else {
                return back()->with('error', 'Verification failed: ' . $verificationResult['message']);
            }

        } catch (\Exception $e) {
            Log::error('Manual verification failed: ' . $e->getMessage());
            return back()->with('error', 'Verification process failed');
        }
    }

    /**
     * Export signature statistics
     */
    public function exportStatistics(Request $request)
    {
        try {
            $period = $request->get('period', 30);
            $format = $request->get('format', 'json');

            $stats = $this->verificationService->getVerificationStatistics($period);

            // Add detailed signature data
            $signatures = DigitalSignature::with(['creator', 'documentSignatures'])
                ->where('created_at', '>=', now()->subDays($period))
                ->get();

            $exportData = [
                'period' => $period,
                'generated_at' => now(),
                'statistics' => $stats,
                'signatures' => $signatures->map(function ($signature) {
                    return [
                        'id' => $signature->id,
                        'signature_id' => $signature->signature_id,
                        'algorithm' => $signature->algorithm,
                        'status' => $signature->status,
                        'created_at' => $signature->created_at,
                        'valid_until' => $signature->valid_until,
                        'documents_signed' => $signature->documentSignatures->count(),
                        'creator' => $signature->creator->name ?? 'Unknown'
                    ];
                })
            ];

            if ($format === 'csv') {
                return $this->exportToCSV($exportData);
            }

            return response()->json($exportData);

        } catch (\Exception $e) {
            Log::error('Export statistics failed: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed'], 500);
        }
    }

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
    private function exportToCSV($data)
    {
        $filename = 'signature_statistics_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Signature ID',
                'Algorithm',
                'Status',
                'Created At',
                'Valid Until',
                'Documents Signed',
                'Creator'
            ]);

            // CSV data
            foreach ($data['signatures'] as $signature) {
                fputcsv($handle, [
                    $signature['signature_id'],
                    $signature['algorithm'],
                    $signature['status'],
                    $signature['created_at'],
                    $signature['valid_until'],
                    $signature['documents_signed'],
                    $signature['creator']
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

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
}
