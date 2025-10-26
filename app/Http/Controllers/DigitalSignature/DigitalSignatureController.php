<?php

namespace App\Http\Controllers\DigitalSignature;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Services\QRCodeService;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use App\Models\SignatureTemplate;
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

            $recentSignatures = DigitalSignature::with('creator')
                ->latest()
                ->limit(10)
                ->get();

            $expiringSignatures = DigitalSignature::expiringSoon(30)
                ->with('creator')
                ->get();

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
                'recentSignatures',
                'expiringSignatures',
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
     * Key management interface
     */
    public function keyManagement()
    {
        try {
            $signatures = DigitalSignature::with('creator')
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('digital-signature.admin.key-management.index', compact('signatures'));

        } catch (\Exception $e) {
            Log::error('Key management error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load key management data');
        }
    }

    /**
     * Create new digital signature key
     */
    public function createSignatureKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purpose' => 'required|string|max:255',
            'validity_years' => 'required|integer|min:1|max:10',
            'key_length' => 'required|integer|in:2048,3072,4096'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $signature = $this->digitalSignatureService->createDigitalSignature(
                $request->purpose,
                Auth::id(),
                $request->validity_years
            );

            Log::info('Digital signature key created', [
                'signature_id' => $signature->signature_id,
                'created_by' => Auth::id()
            ]);

            return redirect()->route('admin.signature.keys.index')
                ->with('success', 'Digital signature key created successfully');

        } catch (QueryException $e) {
            Log::error('Database error during signature key creation: ' . $e->getMessage());
            return back()->with('error', 'Database error: ' . $e->getMessage());
        } catch (\RuntimeException $e) {
            Log::error('Runtime error during signature key creation: ' . $e->getMessage());
            return back()->with('error', 'Runtime error: ' . $e->getMessage());
        } catch (\LogicException $e) {
            Log::error('Logic error during signature key creation: ' . $e->getMessage());
            return back()->with('error', 'Logic error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Signature key creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create digital signature key: ' . $e->getMessage());
        }
    }

    /**
     * View signature key details
     */
    public function viewSignatureKey($id)
    {
        try {
            $signature = DigitalSignature::with('creator', 'documentSignatures')->findOrFail($id);
            $stats = $this->digitalSignatureService->getSignatureStatistics($id);

            return view('digital-signature.admin.key-management.show', compact('signature', 'stats'));

        } catch (\Exception $e) {
            Log::error('View signature key error: ' . $e->getMessage());
            return back()->with('error', 'Signature key not found');
        }
    }

    /**
     * Revoke signature key
     */
    public function revokeSignatureKey(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $this->digitalSignatureService->revokeSignature($id, $request->reason);

            return back()->with('success', 'Digital signature key revoked successfully');

        } catch (\Exception $e) {
            Log::error('Signature key revocation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to revoke signature key');
        }
    }

    /**
     * Document signing interface untuk user
     */
    public function signDocument($approvalRequestId)
    {
        try {
            $approvalRequest = ApprovalRequest::with('user')->findOrFail($approvalRequestId);

            // Check authorization
            if ($approvalRequest->user_id !== Auth::id()) {
                abort(403, 'Unauthorized to sign this document');
            }

            // Check if document is ready to be signed
            if (!$approvalRequest->canBeSignedByUser()) {
                return redirect()->route('digital-signature.user.signature.approval.status')
                    ->with('error', 'Document is not ready for signing');
            }

            // Get active digital signature
            $digitalSignature = DigitalSignature::active()->valid()->first();

            // dd($digitalSignature);

            if (!$digitalSignature) {
                return back()->with('error', 'No valid digital signature available for signing');
            }

            // Check if DocumentSignature already exists
            $documentSignature = DocumentSignature::where('approval_request_id', $approvalRequestId)->first();

            if (!$documentSignature) {
                // Create new DocumentSignature record
                $documentSignature = DocumentSignature::create([
                    'approval_request_id' => $approvalRequestId,
                    'digital_signature_id' => $digitalSignature->id,
                    'document_hash' => DocumentSignature::generateDocumentHash(
                        storage_path('app/' . $approvalRequest->document_path)
                    ),
                    'signed_by' => Auth::id(),
                    'signature_status' => DocumentSignature::STATUS_PENDING
                ]);
            }

            return view('digital-signature.user.sign-document', compact(
                'approvalRequest',
                'digitalSignature',
                'documentSignature'
            ));

        } catch (\Exception $e) {
            Log::error('Sign document interface error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load signing interface');
        }
    }

    /**
     * Process document signing
     */
    /**
     * Process document signing with template
     * TODO #3: Updated to support drag & drop template
     */
    public function processDocumentSigning(Request $request, $approvalRequestId)
    {
        // TODO #3: Support both old (canvas_data) and new (template_id) format
        $validator = Validator::make($request->all(), [
            'template_id' => 'sometimes|required|exists:signature_templates,id',
            'positioning_data' => 'required|string', // JSON string
            'canvas_data' => 'sometimes|string' // For backward compatibility
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid signing data',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);

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

            // Get active digital signature
            $digitalSignature = DigitalSignature::active()->valid()->first();

            if (!$digitalSignature) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid digital signature available'
                ], 400);
            }

            // Parse positioning data
            $positioningData = json_decode($request->positioning_data, true);

            if (!$positioningData) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid positioning data format'
                ], 400);
            }

            // Get or create DocumentSignature
            $documentSignature = $approvalRequest->documentSignature;

            if (!$documentSignature) {
                return response()->json([
                    'success' => false,
                    'error' => 'Document signature record not found'
                ], 400);
            }

            // ========================================================================
            // STEP 1: Merge PDF FIRST (if template is used)
            // This creates the final PDF that will be signed
            // ========================================================================
            $signedPdfPath = null;
            $documentPathForSigning = $approvalRequest->document_path; // Default: original PDF

            if ($request->has('template_id')) {
                try {
                    Log::info('Starting PDF merging before signing', [
                        'approval_request_id' => $approvalRequestId,
                        'template_id' => $request->template_id
                    ]);

                    // Get original PDF path
                    $originalPdfPath = Storage::disk('public')->path($approvalRequest->document_path);

                    // Generate temporary verification token for QR code
                    // $tempVerificationToken = $documentSignature->verification_token ?? \Illuminate\Support\Str::random(64);
                    // $verificationUrl = route('signature.verify', ['token' => $tempVerificationToken]);

                    // // Generate QR code image for embedding
                    // $qrCodeImagePath = $this->pdfSignatureService->generateQRCodeImage(
                    //     $verificationUrl,
                    //     $documentSignature->id
                    // );

                    // Generate QR code for verification
                    $qrData = $this->qrCodeService->generateVerificationQR($documentSignature->id);

                    // ✅ FIX: Convert relative storage path
                    // to absolute filesystem path
                    $qrCodeAbsolutePath = null;
                    if (isset($qrData['qr_code_path'])) {
                        $qrCodeAbsolutePath = Storage::disk('public')->path($qrData['qr_code_path']);

                        Log::info('QR code path converted for PDF embedding', [
                            'relative_path' => $qrData['qr_code_path'],
                            'absolute_path' => $qrCodeAbsolutePath,
                            'file_exists' => file_exists($qrCodeAbsolutePath)
                        ]);
                    }

                    // Merge signature template into PDF
                    $signedPdfPath = $this->pdfSignatureService->mergeSignatureIntoPDF(
                        $originalPdfPath,
                        $request->template_id,
                        $positioningData,
                        $documentSignature,
                        $qrCodeAbsolutePath
                        // $qrData['qr_code_path'] ?? null
                        // $qrCodeImagePath
                    );

                    // ✅ CRITICAL: Use signed PDF for CMS signature creation
                    $documentPathForSigning = $signedPdfPath;

                    Log::info('PDF merged successfully before signing', [
                        'signed_pdf_path' => $signedPdfPath,
                        'will_sign_this_file' => $documentPathForSigning
                    ]);

                    // Increment template usage
                    $template = SignatureTemplate::find($request->template_id);
                    if ($template) {
                        $template->incrementUsage();
                    }

                } catch (\Exception $e) {
                    Log::error('PDF merging failed - cannot proceed with signing', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'approval_request_id' => $approvalRequestId
                    ]);

                    // ❌ FAIL ENTIRE PROCESS - cannot sign without final PDF
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to prepare document for signing: ' . $e->getMessage()
                    ], 500);
                }
            }

            // ========================================================================
            // STEP 2: Create CMS Signature from FINAL PDF (signed PDF or original)
            // This ensures hash and signature match the file user will download
            // ========================================================================
            Log::info('Creating CMS signature', [
                'document_path_for_signing' => $documentPathForSigning,
                'is_signed_pdf' => $signedPdfPath !== null
            ]);

            $this->digitalSignatureService->signApprovalRequest(
                $approvalRequestId,
                $digitalSignature->id,
                $documentPathForSigning  // ✅ Pass the correct path (signed PDF or original)
            );

            // Reload to get updated signature data
            $documentSignature->refresh();

            // ========================================================================
            // STEP 3: Update DocumentSignature with all metadata
            // ========================================================================
            $documentSignature->update([
                'qr_code_path' => $qrData['qr_code_path'] ?? null,
                'verification_url' => $qrData['verification_url'] ?? $documentSignature->verification_url,
                // 'verification_token' => $qrData['verification_token'] ?? $documentSignature->verification_token,
                'final_pdf_path' => $signedPdfPath, // Set immediately
                'positioning_data' => $positioningData,
                'signature_metadata' => array_merge($documentSignature->signature_metadata ?? [], [
                    'template_id' => $request->template_id ?? null,
                    'placement_method' => $request->template_id ? 'drag_drop_template' : 'canvas_draw',
                    'signed_via' => 'web_interface',
                    'browser' => $request->userAgent(),
                    'signing_method' => 'sign_after_merge', // Track new method
                    'signed_file' => $signedPdfPath ? 'merged_pdf' : 'original_pdf'
                ])
            ]);

            // Reload to get updated signature data
            // $documentSignature->refresh();

            // // ========================================================================
            // // STEP 2: Create CMS Signature from FINAL PDF (signed PDF or original)
            // // This ensures hash and signature match the file user will download
            // // ========================================================================
            // Log::info('Creating CMS signature', [
            //     'document_path_for_signing' => $documentPathForSigning,
            //     'is_signed_pdf' => $signedPdfPath !== null
            // ]);

            // $this->digitalSignatureService->signApprovalRequest(
            //     $approvalRequestId,
            //     $digitalSignature->id,
            //     $documentPathForSigning  // ✅ Pass the correct path (signed PDF or original)
            // );

            // // Reload to get updated signature data
            // $documentSignature->refresh();

            // Save canvas data if provided (for backward compatibility with old canvas method)
            if ($request->has('canvas_data')) {
                $documentSignature->saveCanvasData(
                    $request->canvas_data,
                    $positioningData
                );
            }

            // Generate QR code data for response
            // $qrData = $this->qrCodeService->generateVerificationQR($documentSignature->id);

            // Update approval request status
            $approvalRequest->markUserSigned();

            Log::info('Document signed successfully', [
                'approval_request_id' => $approvalRequestId,
                'document_signature_id' => $documentSignature->id,
                'template_id' => $request->template_id ?? 'none',
                'method' => $request->template_id ? 'template' : 'canvas',
                'signed_pdf_created' => $signedPdfPath !== null,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document signed successfully',
                'data' => [
                    'document_signature_id' => $documentSignature->id,
                    'approval_request_id' => $approvalRequest->id,
                    'status' => $approvalRequest->status,
                    'qr_code_url' => $qrData['qr_code_url'] ?? null,
                    'verification_url' => $qrData['verification_url'] ?? $documentSignature->verification_url,
                    'signed_pdf_available' => $signedPdfPath !== null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Document signing process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'approval_request_id' => $approvalRequestId
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
    public function signatureCanvas($approvalRequestId)
    {
        try {
            $approvalRequest = ApprovalRequest::with('user')->findOrFail($approvalRequestId);

            // Check authorization
            if ($approvalRequest->user_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }

            $digitalSignature = DigitalSignature::active()->valid()->first();
            $documentSignature = DocumentSignature::where('approval_request_id', $approvalRequestId)->first();

            // Get signature template (default atau custom)
            $template = \App\Models\SignatureTemplate::default()->first();
            $canvasData = $template ? $template->generateCanvasData($documentSignature) : null;

            return view('digital-signature.components.signature-canvas', compact(
                'approvalRequest',
                'digitalSignature',
                'documentSignature',
                'canvasData'
            ));

        } catch (\Exception $e) {
            Log::error('Signature canvas error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load signature canvas');
        }
    }

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
    public function generateTestSignature()
    {
        if (!app()->environment('local')) {
            abort(403, 'Test signatures only available in development environment');
        }

        try {
            $signature = $this->digitalSignatureService->createDigitalSignature(
                'Test Signature - Development',
                Auth::id(),
                1
            );

            return response()->json([
                'success' => true,
                'signature_id' => $signature->signature_id,
                'message' => 'Test signature created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Test signature creation failed'], 500);
        }
    }

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
     * Get available signature templates untuk user signing
     * TODO #2: Load templates for drag & drop
     */
    public function getTemplatesForSigning($approvalRequestId)
    {
        try {
            $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);

            // Check authorization
            if ($approvalRequest->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            // Get active templates
            $templates = SignatureTemplate::active()
                ->with('kaprodi')
                ->get()
                ->map(function($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'signature_image_url' => Storage::url($template->signature_image_path),
                        'thumbnail_url' => Storage::url($template->signature_image_path),
                        'is_default' => $template->is_default,
                        'canvas_width' => $template->canvas_width,
                        'canvas_height' => $template->canvas_height,
                        'text_config' => $template->text_config,
                        'layout_config' => $template->layout_config,
                        'kaprodi_name' => $template->kaprodi->name ?? 'N/A',
                        'usage_count' => $template->usage_count,
                    ];
                });

            return response()->json([
                'success' => true,
                'templates' => $templates,
                'total' => $templates->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load templates for signing', [
                'error' => $e->getMessage(),
                'approval_request_id' => $approvalRequestId
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load templates'
            ], 500);
        }
    }
}
