<?php

namespace App\Http\Controllers\DigitalSignature;

use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Services\QRCodeService;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Services\DigitalSignatureService;
use Illuminate\Support\Facades\Validator;

class DocumentSignatureController extends Controller
{
    protected $digitalSignatureService;
    protected $qrCodeService;
    protected $verificationService;

    public function __construct(
        DigitalSignatureService $digitalSignatureService,
        QRCodeService $qrCodeService,
        VerificationService $verificationService
    ) {
        $this->digitalSignatureService = $digitalSignatureService;
        $this->qrCodeService = $qrCodeService;
        $this->verificationService = $verificationService;
    }

    /**
     * Display list of document signatures for admin
     */
    public function index(Request $request)
    {
        try {
            $query = DocumentSignature::with(['approvalRequest.user', 'digitalSignature', 'signer']);

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('signature_status', $request->status);
            }

            // Filter by date range
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->where('signed_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->where('signed_at', '<=', $request->date_to);
            }

            // Search by document name
            if ($request->has('search') && !empty($request->search)) {
                $query->whereHas('approvalRequest', function ($q) use ($request) {
                    $q->where('document_name', 'like', '%' . $request->search . '%');
                });
            }

            $documentSignatures = $query->latest('signed_at')->paginate(15);

            $statusCounts = [
                'pending' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_PENDING)->count(),
                'signed' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_SIGNED)->count(),
                'verified' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_VERIFIED)->count(),
                'rejected' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_REJECTED)->count(),
                'invalid' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_INVALID)->count(),
            ];

            return view('digital-signature.admin.document-signatures', compact(
                'documentSignatures',
                'statusCounts'
            ));

        } catch (\Exception $e) {
            Log::error('Document signatures index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load document signatures');
        }
    }

    /**
     * Show specific document signature details
     */
    public function show($id)
    {
        try {
            $documentSignature = DocumentSignature::with([
                'approvalRequest.user',
                'digitalSignature',
                'signer',
                'verifier',
                'auditLogs'
            ])->findOrFail($id);

            // Get signature info
            $signatureInfo = $documentSignature->getSignatureInfo();

            // dd($signatureInfo);

            // Perform verification
            $verificationResult = $this->verificationService->verifyById($id);

            // dd($verificationResult);

            return view('digital-signature.admin.signature-details', compact(
                'documentSignature',
                'signatureInfo',
                'verificationResult'
            ));

        } catch (\Exception $e) {
            // dd($e);
            Log::error('Document signature show error: ' . $e->getMessage());
            return back()->with('error', 'Document signature not found');
        }
    }

    /**
     * Verify document signature
     */
    public function verify(Request $request, $id)
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($id);
            $approvalRequest = $documentSignature->approvalRequest;

            $verificationResult = $this->verificationService->verifyById($id);

            if ($verificationResult['is_valid']) {
                $approvalRequest->approveSignature(
                    Auth::id(),
                    $documentSignature->final_pdf_path
                );
                $documentSignature->update([
                    'signature_status' => DocumentSignature::STATUS_VERIFIED,
                    'verified_at' => now(),
                    'verified_by' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Document signature verified successfully',
                    'verification_result' => $verificationResult
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification failed: ' . $verificationResult['message'],
                    'verification_result' => $verificationResult
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Document signature verification error: ' . $e->getMessage());
            return response()->json(['error' => 'Verification failed'], 500);
        }
    }

    /**
     * Quick preview with verification for modal
     */
    public function quickPreview($id)
    {
        try {
            $documentSignature = DocumentSignature::with([
                'approvalRequest.user',
                'digitalSignature',
                'signer',
                'verifier'
            ])->findOrFail($id);

            // Get signature info
            $signatureInfo = $documentSignature->getSignatureInfo();

            // Perform verification (7 comprehensive checks)
            $verificationResult = $this->verificationService->verifyById($id);

            // Prepare response data
            return response()->json([
                'success' => true,
                'data' => [
                    'document' => [
                        'id' => $documentSignature->id,
                        'name' => $documentSignature->approvalRequest->document_name,
                        'number' => $documentSignature->approvalRequest->full_document_number,
                        'type' => $documentSignature->approvalRequest->document_type,
                        'submitted_by' => $documentSignature->approvalRequest->user->name,
                        'submitted_at' => $documentSignature->approvalRequest->created_at->format('d F Y H:i'),
                        'notes' => $documentSignature->approvalRequest->notes,
                    ],
                    'signature' => [
                        'status' => $documentSignature->signature_status,
                        'signed_by' => $documentSignature->signer->name ?? 'Unknown',
                        'signed_at' => $documentSignature->signed_at ? $documentSignature->signed_at->format('d F Y H:i:s') : null,
                        'signed_at_human' => $documentSignature->signed_at ? $documentSignature->signed_at->diffForHumans() : null,
                        'algorithm' => $documentSignature->digitalSignature->algorithm ?? 'N/A',
                        'key_length' => $documentSignature->digitalSignature->key_length ?? 'N/A',
                        'document_hash' => substr($documentSignature->document_hash, 0, 16) . '...',
                        'has_signed_pdf' => $documentSignature->final_pdf_path ? true : false,
                        'pdf_path' => $documentSignature->final_pdf_path,
                    ],
                    'verification' => [
                        'is_valid' => $verificationResult['is_valid'],
                        'message' => $verificationResult['message'],
                        'verified_at' => $verificationResult['verified_at'],
                        'checks' => $verificationResult['details']['checks'] ?? [],
                        'warnings' => $verificationResult['details']['warnings'] ?? [],
                        'summary' => $verificationResult['details']['verification_summary'] ?? [],
                    ],
                    'urls' => [
                        'view' => route('admin.signature.documents.view', $documentSignature->id),
                        'download' => route('admin.signature.documents.download', $documentSignature->id),
                        'detail' => route('admin.signature.documents.show', $documentSignature->id),
                        'verify' => route('admin.signature.documents.verify', $documentSignature->id),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Quick preview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load document preview'
            ], 500);
        }
    }

    /**
     * Reject document signature (for placement/quality issues)
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $documentSignature = DocumentSignature::findOrFail($id);

            // Only allow rejection for SIGNED signatures
            if ($documentSignature->signature_status !== DocumentSignature::STATUS_SIGNED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only reject signatures with "signed" status'
                ], 400);
            }

            $documentSignature->rejectSignature($request->reason, Auth::id());

            Log::info('Document signature rejected', [
                'document_signature_id' => $id,
                'reason' => $request->reason,
                'rejected_by' => Auth::id(),
                'approval_request_id' => $documentSignature->approval_request_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document signature rejected successfully. User will need to re-sign the document.'
            ]);

        } catch (\Exception $e) {
            Log::error('Document signature rejection error: ' . $e->getMessage());
            return response()->json(['error' => 'Rejection failed'], 500);
        }
    }

    /**
     * Invalidate document signature
     */
    public function invalidate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $documentSignature = DocumentSignature::findOrFail($id);
            $documentSignature->invalidate($request->reason);

            Log::info('Document signature invalidated', [
                'document_signature_id' => $id,
                'reason' => $request->reason,
                'invalidated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document signature invalidated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Document signature invalidation error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalidation failed'], 500);
        }
    }

    /**
     * Download signed document
     */
    public function downloadSignedDocument($id)
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($id);
            $approvalRequest = $documentSignature->approvalRequest;

            // Check authorization - Check if user is Kaprodi or document owner
            $isKaprodi = Auth::guard('kaprodi')->check();
            $isOwner = Auth::check() && $approvalRequest->user_id === Auth::id();

            if (!$isKaprodi && !$isOwner) {
                Log::warning('Unauthorized download attempt', [
                    'document_signature_id' => $id,
                    'attempted_by' => Auth::id() ?? 'guest',
                    'is_kaprodi' => $isKaprodi,
                    'is_owner' => $isOwner
                ]);
                abort(403, 'Unauthorized to download this document');
            }

            // Allow download even if not verified yet (user might need to download for verification)
            // Just log a warning if status is invalid
            if ($documentSignature->signature_status === DocumentSignature::STATUS_INVALID) {
                Log::warning('Downloading document with invalid signature', [
                    'document_signature_id' => $id,
                    'status' => $documentSignature->signature_status,
                    'downloaded_by' => Auth::id()
                ]);
            }

            // Determine which file to use
            if ($documentSignature->final_pdf_path) {
                // Use signed PDF (stored in local disk)
                $filePath = $documentSignature->final_pdf_path;
                $fullPath = Storage::disk('public')->path($filePath);
                $fileType = 'signed';
            } else {
                // Fallback to original document (stored in public disk)
                $filePath = $approvalRequest->document_path;
                $fullPath = Storage::disk('public')->path($filePath);
                $fileType = 'original';
            }

            if (!file_exists($fullPath)) {
                Log::error('Document file not found', [
                    'document_signature_id' => $id,
                    'file_path' => $filePath,
                    'full_path' => $fullPath,
                    'file_type' => $fileType
                ]);
                return back()->with('error', 'Document file not found');
            }

            // Generate safe filename
            $signedAtDate = $documentSignature->signed_at
                ? $documentSignature->signed_at->format('Y-m-d')
                : now()->format('Y-m-d');

            $filename = $approvalRequest->document_name .
                       ($fileType === 'signed' ? '_signed_' : '_') .
                       $signedAtDate .
                       '.' . pathinfo($fullPath, PATHINFO_EXTENSION);

            Log::info('Document downloaded', [
                'document_signature_id' => $id,
                'downloaded_by' => Auth::id(),
                'filename' => $filename,
                'file_type' => $fileType
            ]);

            return Response::download($fullPath, $filename);

        } catch (\Exception $e) {
            Log::error('Download document error: ' . $e->getMessage());
            return back()->with('error', 'Failed to download document');
        }
    }

    /**
     * View/Preview signed PDF in browser
     */
    public function viewSignedDocument($id)
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($id);
            $approvalRequest = $documentSignature->approvalRequest;

            // Check authorization - Check if user is Kaprodi or document owner
            $isKaprodi = Auth::guard('kaprodi')->check();
            $isOwner = Auth::check() && $approvalRequest->user_id === Auth::id();

            if (!$isKaprodi && !$isOwner) {
                Log::warning('Unauthorized view attempt', [
                    'document_signature_id' => $id,
                    'attempted_by' => Auth::id() ?? 'guest'
                ]);
                abort(403, 'Unauthorized to view this document');
            }

            // Determine which file to use
            if ($documentSignature->final_pdf_path) {
                // Use signed PDF (stored in local disk)
                $filePath = $documentSignature->final_pdf_path;
                // $fullPath = Storage::url($filePath);
                $fullPath = Storage::disk('public')->path($filePath);
                $fileType = 'signed';
            } else {
                // Fallback to original document (stored in public disk)
                $filePath = $approvalRequest->document_path;
                // $fullPath = Storage::url($filePath);
                $fullPath = Storage::disk('public')->path($filePath);
                $fileType = 'original';
            }

            if (!file_exists($fullPath)) {
                Log::error('Document file not found for viewing', [
                    'document_signature_id' => $id,
                    'file_path' => $filePath,
                    'full_path' => $fullPath,
                    'file_type' => $fileType
                ]);
                return back()->with('error', 'Document file not found');
            }

            Log::info('Document viewed', [
                'document_signature_id' => $id,
                'viewed_by' => Auth::id(),
                'file_type' => $fileType,
                'full_path' => $fullPath
            ]);

            // Return PDF file inline (untuk preview di browser)
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('View document error: ' . $e->getMessage());
            return back()->with('error', 'Failed to view document');
        }
    }

    /**
     * Download QR code
     */
    public function downloadQRCode($id)
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($id);
            $approvalRequest = $documentSignature->approvalRequest;

            // AUTHORIZATION: Check if user is Kaprodi or document owner
            $isKaprodi = Auth::guard('kaprodi')->check();
            $isOwner = Auth::check() && $approvalRequest->user_id === Auth::id();

            if (!$isKaprodi && !$isOwner) {
                Log::warning('Unauthorized QR code download attempt', [
                    'document_signature_id' => $id,
                    'attempted_by' => Auth::id() ?? 'guest',
                    'is_kaprodi' => $isKaprodi,
                    'is_owner' => $isOwner
                ]);
                abort(403, 'Unauthorized to download this QR code');
            }

            if (!$documentSignature->qr_code_path) {
                // Generate QR code if not exists
                $qrData = $this->qrCodeService->generateVerificationQR($id);
                $documentSignature->update(['qr_code_path' => $qrData['qr_code_path']]);
            }

            // Use Storage facade untuk konsistensi
            $qrPath = Storage::disk('public')->path($documentSignature->qr_code_path);

            if (!file_exists($qrPath)) {
                Log::error('QR code file not found', [
                    'document_signature_id' => $id,
                    'qr_code_path' => $documentSignature->qr_code_path,
                    'full_path' => $qrPath
                ]);
                return back()->with('error', 'QR code file not found');
            }

            $filename = 'qr_code_' . $documentSignature->id . '_' .
                       now()->format('Y-m-d') . '.png';

            Log::info('QR code downloaded', [
                'document_signature_id' => $id,
                'downloaded_by' => Auth::id(),
                'filename' => $filename
            ]);

            return Response::download($qrPath, $filename);

        } catch (\Exception $e) {
            Log::error('Download QR code error: ' . $e->getMessage());
            return back()->with('error', 'Failed to download QR code');
        }
    }

    /**
     * ========================================================================
     * USER MY SIGNATURES MANAGEMENT METHODS
     * ========================================================================
     */

    /**
     * Display list of user's document signatures (My Signatures page)
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function userSignatures(Request $request)
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            // Query: Get all DocumentSignatures where approval_request belongs to user
            $query = DocumentSignature::with([
                    'approvalRequest',
                    'digitalSignature',
                    'signer',
                    'verifier',
                    'rejector'
                ])
                ->whereHas('approvalRequest', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });

            // FILTER 1: Search by document name or number
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('approvalRequest', function($q) use ($search) {
                    $q->where('document_name', 'like', "%{$search}%")
                      ->orWhere('document_number', 'like', "%{$search}%");
                });
            }

            // FILTER 2: Filter by signature status
            if ($request->filled('status')) {
                $query->where('signature_status', $request->status);
            }

            // FILTER 3: Filter by month
            if ($request->filled('month')) {
                if ($request->month === 'current') {
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                } elseif ($request->month === 'last') {
                    $query->whereMonth('created_at', now()->subMonth()->month)
                          ->whereYear('created_at', now()->subMonth()->year);
                }
            }

            // Order by latest first
            $query->orderBy('created_at', 'desc');

            // Paginate results (12 per page for 2-column grid)
            $signatures = $query->paginate(12);

            // STATISTICS CALCULATION
            $baseQuery = DocumentSignature::whereHas('approvalRequest', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });

            $statistics = [
                'total' => (clone $baseQuery)->count(),

                'verified' => (clone $baseQuery)->where('signature_status', 'verified')->count(),

                'pending' => (clone $baseQuery)->whereIn('signature_status', ['pending', 'signed'])->count(),

                'rejected' => (clone $baseQuery)->where('signature_status', 'rejected')->count(),

                'this_month' => (clone $baseQuery)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ];

            // Log access
            Log::info('User accessed My Signatures page', [
                'user_id' => $user->id,
                'filters' => $request->only(['search', 'status', 'month']),
                'results_count' => $signatures->total()
            ]);

            return view('digital-signature.user.my-signature.index', compact('signatures', 'statistics'));

        } catch (\Exception $e) {
            Log::error('Failed to load My Signatures page', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to load signatures. Please try again.');
        }
    }

    /**
     * Display details of specific user's document signature
     *
     * @param int $id - DocumentSignature ID
     * @return \Illuminate\View\View
     */
    public function userSignatureDetails($id)
    {
        try {
            // Get authenticated user
            $user = Auth::user();

            // Find DocumentSignature with relations
            $documentSignature = DocumentSignature::with([
                    'approvalRequest.user',  // Submitter info
                    'approvalRequest.approver',  // Who approved
                    'approvalRequest.signApprover',  // Who approved signature
                    'digitalSignature',       // Crypto info
                    'signer',                 // Who signed (if signed_by exists)
                    'verifier',               // Who verified (if verified_by exists)
                    'rejector'                // Who rejected (if rejected_by exists)
                ])
                ->findOrFail($id);

            // AUTHORIZATION: User can only view their own signatures
            if ($documentSignature->approvalRequest->user_id !== $user->id) {
                Log::warning('Unauthorized access attempt to signature details', [
                    'user_id' => $user->id,
                    'document_signature_id' => $id,
                    'owner_id' => $documentSignature->approvalRequest->user_id
                ]);

                abort(403, 'Unauthorized to view this signature');
            }

            // Log access
            Log::info('User viewed signature details', [
                'user_id' => $user->id,
                'document_signature_id' => $id,
                'signature_status' => $documentSignature->signature_status
            ]);

            return view('digital-signature.user.my-signature.details', compact('documentSignature'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Signature not found', [
                'user_id' => Auth::id(),
                'document_signature_id' => $id
            ]);

            return back()->with('error', 'Signature not found.');

        } catch (\Exception $e) {
            Log::error('Failed to load signature details', [
                'user_id' => Auth::id(),
                'document_signature_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to load signature details. Please try again.');
        }
    }

    /**
     * Regenerate QR code
     */
    public function regenerateQRCode($id)
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($id);

            // Regenerate verification token for security
            $documentSignature->regenerateVerificationToken();

            // Generate new QR code
            $qrData = $this->qrCodeService->generateVerificationQR($id, [
                'size' => 300,
                'add_logo' => true,
                'add_label' => true
            ]);

            $documentSignature->update(['qr_code_path' => $qrData['qr_code_path']]);

            Log::info('QR code regenerated', [
                'document_signature_id' => $id,
                'regenerated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR code regenerated successfully',
                'qr_code_url' => $qrData['qr_code_url'],
                'verification_url' => $qrData['verification_url']
            ]);

        } catch (\Exception $e) {
            Log::error('QR code regeneration error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to regenerate QR code'], 500);
        }
    }

    /**
     * Export document signatures
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $status = $request->get('status');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = DocumentSignature::with(['approvalRequest.user', 'digitalSignature', 'signer']);

            if ($status) {
                $query->where('signature_status', $status);
            }
            if ($dateFrom) {
                $query->where('signed_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('signed_at', '<=', $dateTo);
            }

            $documentSignatures = $query->get();

            if ($format === 'csv') {
                return $this->exportToCSV($documentSignatures);
            } elseif ($format === 'json') {
                return $this->exportToJSON($documentSignatures);
            }

            return back()->with('error', 'Invalid export format');

        } catch (\Exception $e) {
            Log::error('Export document signatures error: ' . $e->getMessage());
            return back()->with('error', 'Export failed');
        }
    }

    /**
     * Batch verify multiple document signatures
     */
    public function batchVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'signature_ids' => 'required|array',
            'signature_ids.*' => 'exists:document_signatures,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($request->signature_ids as $id) {
                try {
                    $verificationResult = $this->verificationService->verifyById($id);

                    if ($verificationResult['is_valid']) {
                        $documentSignature = DocumentSignature::findOrFail($id);
                        $documentSignature->update([
                            'signature_status' => DocumentSignature::STATUS_VERIFIED,
                            'verified_at' => now(),
                            'verified_by' => Auth::id()
                        ]);
                        $successCount++;
                        $results[$id] = ['status' => 'success', 'message' => 'Verified successfully'];
                    } else {
                        $failureCount++;
                        $results[$id] = ['status' => 'failed', 'message' => $verificationResult['message']];
                    }
                } catch (\Exception $e) {
                    $failureCount++;
                    $results[$id] = ['status' => 'error', 'message' => $e->getMessage()];
                }
            }

            Log::info('Batch verification completed', [
                'total_processed' => count($request->signature_ids),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'verified_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Batch verification completed. {$successCount} verified, {$failureCount} failed.",
                'results' => $results,
                'summary' => [
                    'total' => count($request->signature_ids),
                    'success' => $successCount,
                    'failure' => $failureCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Batch verification error: ' . $e->getMessage());
            return response()->json(['error' => 'Batch verification failed'], 500);
        }
    }

    /**
     * Get signature statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_signatures' => DocumentSignature::count(),
                'by_status' => [
                    'pending' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_PENDING)->count(),
                    'signed' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_SIGNED)->count(),
                    'verified' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_VERIFIED)->count(),
                    'invalid' => DocumentSignature::where('signature_status', DocumentSignature::STATUS_INVALID)->count(),
                ],
                'recent_activity' => [
                    'today' => DocumentSignature::whereDate('signed_at', today())->count(),
                    'this_week' => DocumentSignature::where('signed_at', '>=', now()->startOfWeek())->count(),
                    'this_month' => DocumentSignature::where('signed_at', '>=', now()->startOfMonth())->count(),
                ],
                'verification_rate' => $this->calculateVerificationRate()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Get statistics error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get statistics'], 500);
        }
    }

    /**
     * Export to CSV format
     */
    private function exportToCSV($documentSignatures)
    {
        $filename = 'document_signatures_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($documentSignatures) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Document Name',
                'Document Number',
                'Signed By',
                'Signed At',
                'Status',
                'Algorithm',
                'Verification URL'
            ]);

            foreach ($documentSignatures as $signature) {
                fputcsv($handle, [
                    $signature->id,
                    $signature->approvalRequest->document_name,
                    $signature->approvalRequest->full_document_number,
                    $signature->signer->name ?? 'Unknown',
                    $signature->signed_at ? $signature->signed_at->format('Y-m-d H:i:s') : '',
                    $signature->signature_status,
                    $signature->digitalSignature->algorithm ?? '',
                    $signature->verification_url ?? ''
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to JSON format
     */
    private function exportToJSON($documentSignatures)
    {
        $data = $documentSignatures->map(function ($signature) {
            return [
                'id' => $signature->id,
                'document_name' => $signature->approvalRequest->document_name,
                'document_number' => $signature->approvalRequest->full_document_number,
                'signed_by' => $signature->signer->name ?? 'Unknown',
                'signed_at' => $signature->signed_at,
                'status' => $signature->signature_status,
                'algorithm' => $signature->digitalSignature->algorithm ?? '',
                'verification_url' => $signature->verification_url
            ];
        });

        $filename = 'document_signatures_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Calculate verification rate
     */
    private function calculateVerificationRate()
    {
        $totalSigned = DocumentSignature::whereIn('signature_status', [
            DocumentSignature::STATUS_SIGNED,
            DocumentSignature::STATUS_VERIFIED
        ])->count();

        $verified = DocumentSignature::where('signature_status', DocumentSignature::STATUS_VERIFIED)->count();

        return $totalSigned > 0 ? round(($verified / $totalSigned) * 100, 2) : 0;
    }
}
