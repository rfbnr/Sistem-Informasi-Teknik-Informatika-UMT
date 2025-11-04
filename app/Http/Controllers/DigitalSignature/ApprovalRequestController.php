<?php

namespace App\Http\Controllers\DigitalSignature;

use Exception;
use App\Models\Kaprodi;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Services\QRCodeService;
use Illuminate\Validation\Rule;
use App\Models\DocumentSignature;
use App\Models\SignatureAuditLog;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\NewApprovalRequestNotification;
use Illuminate\Validation\ValidationException;
use App\Mail\ApprovalRequestSignedNotification;
use App\Mail\ApprovalRequestApprovedNotification;
use App\Mail\ApprovalRequestRejectedNotification;

class ApprovalRequestController extends Controller
{
    protected $qrCodeService;
    protected $verificationService;

    public function __construct(QRCodeService $qrCodeService, VerificationService $verificationService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->verificationService = $verificationService;
    }

    // Handle document upload for approval request
    //! DIPAKAI DI ROUTE user.signature.approval.upload
    public function upload(Request $request)
    {
        // Debug: Log incoming request
        Log::info('Upload request received', [
            'has_file' => $request->hasFile('document'),
            'file_input_name' => 'document',
            'all_files' => $request->allFiles(),
            'all_inputs' => $request->except(['document']), // Exclude file from log for brevity
            'user_id' => Auth::id()
        ]);

        // Check authentication
        if (!Auth::check()) {
            Log::warning('Upload attempt without authentication');
            return redirect()->back()->with('error', 'Anda harus login terlebih dahulu untuk mengunggah dokumen.');
        }

        // Validation dengan pesan error yang lebih spesifik
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf|max:25600', // max file size 25MB
            'notes' => 'required|string|max:1000', // Ubah menjadi required sesuai form
        ], [
            // Custom error messages
            'document_name.required' => 'Please select a document type.',
            'document.required' => 'Please upload a PDF document.',
            'document.file' => 'The uploaded file is not valid.',
            'document.mimes' => 'Only PDF files are allowed.',
            'document.max' => 'File size cannot exceed 25MB.',
            'notes.required' => 'Please provide a description for your request.',
            'notes.max' => 'Description cannot exceed 1000 characters.',
        ]);

        // Debug: Log validation results
        if ($validator->fails()) {
            Log::warning('Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the validation errors below.');
        }

        // Additional file validation
        if (!$request->hasFile('document')) {
            Log::error('No file found in request', [
                'files' => $request->allFiles(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'No document file was uploaded. Please select a PDF file.')
                ->withInput();
        }

        $uploadedFile = $request->file('document');


        // Validate file further
        if (!$uploadedFile->isValid()) {
            Log::error('Invalid file uploaded', [
                'error' => $uploadedFile->getErrorMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'The uploaded file is corrupted or invalid.')
                ->withInput();
        }

        try {
            // Store the uploaded file
            // $documentPath = $request->file('document')->store('documents', 'public');
            // $documentPath = $uploadedFile->store('documents', 'public');
            $documentPath = $uploadedFile->storeAs(
                'documents',
                now()->format('YmdHis') . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $uploadedFile->getClientOriginalName()),
                'public'
            );

            $documentName = $uploadedFile->getClientOriginalName();

            Log::info('File stored successfully', [
                'path' => $documentPath,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize(),
                'user_id' => Auth::id()
            ]);

            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'user_id' => Auth::id(),
                'document_name' => $documentName,
                'document_type' => $request->input('document_type'),
                'document_path' => $documentPath,
                'status' => ApprovalRequest::STATUS_PENDING,
                'notes' => $request->input('notes'),
            ]);

            // ENHANCEMENT: Generate document hash for future integrity verification
            $documentContent = Storage::disk('public')->get($documentPath);
            $documentHash = hash('sha256', $documentContent);

            // Store hash in metadata for integrity check during signing
            $approvalRequest->update([
                'workflow_metadata' => [
                    'document_hash' => $documentHash,  // Used for integrity check in processDocumentSigning()
                    'file_size' => $uploadedFile->getSize(),
                    'original_filename' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'upload_ip' => $request->ip(),
                    'upload_user_agent' => $request->userAgent(),
                    'upload_timestamp' => now()->timestamp
                ]
            ]);

            Log::info('Document hash generated for integrity check', [
                'approval_request_id' => $approvalRequest->id,
                'document_hash' => $documentHash
            ]);

            // Send notification to Kaprodi
            try {
                $kaprodiEmails = Kaprodi::pluck('email')->toArray();
                if (!empty($kaprodiEmails)) {
                    Mail::to($kaprodiEmails)->send(new NewApprovalRequestNotification($approvalRequest));
                    Log::info('Notification sent to Kaprodi', [
                        'emails' => $kaprodiEmails,
                        'approval_request_id' => $approvalRequest->id
                    ]);
                }
            } catch (\Exception $mailException) {
                Log::warning('Failed to send notification email', [
                    'error' => $mailException->getMessage(),
                    'approval_request_id' => $approvalRequest->id
                ]);
                // Don't fail the entire process if email fails
            }

            Log::info('Document uploaded for approval successfully', [
                'approval_request_id' => $approvalRequest->id,
                'user_id' => Auth::id(),
                'document_name' => $approvalRequest->document_name,
                'file_size' => $uploadedFile->getSize()
            ]);

            // Redirect dengan route name yang benar
            return redirect()->route('user.signature.approval.status')
                ->with('success', 'Document uploaded successfully! You will receive email notifications about the approval process.');

        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'file_info' => [
                    'name' => $uploadedFile->getClientOriginalName(),
                    'size' => $uploadedFile->getSize(),
                    'mime' => $uploadedFile->getMimeType()
                ]
            ]);

            // Clean up file if it was stored
            if (isset($documentPath) && Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
                Log::info('Cleaned up stored file after error', ['path' => $documentPath]);
            }

            return redirect()->back()
                ->with('error', 'Upload failed. Please try again. If the problem persists, contact support.' . $e->getMessage())
                ->withInput();
        }
    }

    // Display all approval requests for Kaprodi
    //! DIPAKAI DI ROUTE admin.signature.approval.index
    public function index(Request $request)
    {
        try {
            $query = ApprovalRequest::with(['user', 'documentSignature.digitalSignature']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // if ($request->filled('priority')) {
            //     $query->where('priority', $request->priority);
            // }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('document_name', 'like', '%' . $request->search . '%')
                    //   ->orWhere('nomor', 'like', '%' . $request->search . '%')
                      ->orWhereHas('user', function ($subQ) use ($request) {
                          $subQ->where('name', 'like', '%' . $request->search . '%');
                      });
                });
            }

            $approvalRequests = $query->latest()->paginate(10);

            // Get statistics
            $statistics = [
                'pending' => ApprovalRequest::pendingApproval()->count(),
                'approved' => ApprovalRequest::byStatus(ApprovalRequest::STATUS_APPROVED)->count(),
                'user_signed' => ApprovalRequest::byStatus(ApprovalRequest::STATUS_USER_SIGNED)->count(),
                'completed' => ApprovalRequest::completed()->count(),
                'rejected' => ApprovalRequest::byStatus(ApprovalRequest::STATUS_REJECTED)->count(),
                // 'overdue' => ApprovalRequest::overdue()->count()
            ];

            return view('digital-signature.admin.approval-requests.index', compact('approvalRequests', 'statistics'));

        } catch (\Exception $e) {
            Log::error('Failed to load approval requests: ' . $e->getMessage());
            return back()->with('error', 'Failed to load data');
        }
    }

    // Show detailed approval request
    //! DIPAKAI DI ROUTE admin.signature.approval.show
    public function show($id)
    {
        try {
            $approvalRequest = ApprovalRequest::with([
                'user',
                'documentSignature.digitalSignature',
                'documentSignature.signer',
                // 'documentSignature.rejector',
                'approver',
                'rejector'
            ])->findOrFail($id);

            // Get workflow history/timeline
            $timeline = $this->buildWorkflowTimeline($approvalRequest);

            // Get verification result if document is signed
            $verificationResult = null;
            if ($approvalRequest->documentSignature && $approvalRequest->documentSignature->signature_status !== DocumentSignature::STATUS_PENDING) {
                try {
                    $verificationResult = $this->verificationService->verifyById(
                        $approvalRequest->documentSignature->id
                    );
                } catch (\Exception $e) {
                    Log::warning('Verification failed for approval request detail', [
                        'approval_request_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return view('digital-signature.admin.approval-requests.show', compact(
                'approvalRequest',
                'timeline',
                'verificationResult'
            ));

        } catch (QueryException $qe) {
            Log::error('Database error loading approval request details: ' . $qe->getMessage());
            return back()->with('error', 'Database error occurred while loading details: ' . $qe->getMessage());
        } catch (ValidationException $ve) {
            Log::error('Validation error loading approval request details: ' . $ve->getMessage());
            return back()->with('error', 'Validation error occurred while loading details: ' . $ve->getMessage());
        } catch (\Exception $e) {
            Log::error('Failed to load approval request details: ' . $e->getMessage());
            return back()->with('error', 'Failed to load approval request details : ' . $e->getMessage());
        }
    }

    // Build workflow timeline for approval request
    //! DIPAKAI DI METHOD show
    private function buildWorkflowTimeline($approvalRequest)
    {
        $timeline = [];

        // Submitted
        $timeline[] = [
            'title' => 'Document Submitted',
            'description' => 'Document was submitted for approval',
            'user' => $approvalRequest->user->name ?? 'Unknown',
            'timestamp' => $approvalRequest->created_at,
            'icon' => 'fa-upload',
            'color' => 'primary',
            'status' => 'completed'
        ];

        // Approved by Kaprodi
        if ($approvalRequest->approved_at) {
            $timeline[] = [
                'title' => 'Approved by Kaprodi',
                'description' => $approvalRequest->approval_notes ?? 'Document approved for signing',
                'user' => $approvalRequest->approver->name ?? 'Kaprodi',
                'timestamp' => $approvalRequest->approved_at,
                'icon' => 'fa-check-circle',
                'color' => 'success',
                'status' => 'completed'
            ];
        }

        // User Signed
        if ($approvalRequest->user_signed_at) {
            $timeline[] = [
                'title' => 'Document Signed by Student',
                'description' => 'Student has digitally signed the document',
                'user' => $approvalRequest->user->name ?? 'Student',
                'timestamp' => $approvalRequest->user_signed_at,
                'icon' => 'fa-signature',
                'color' => 'info',
                'status' => 'completed'
            ];
        }

        // Signature Approved
        if ($approvalRequest->sign_approved_at) {
            $timeline[] = [
                'title' => 'Signature Approved',
                'description' => $approvalRequest->sign_approval_notes ?? 'Digital signature has been verified and approved',
                'user' => $approvalRequest->signApprover->name ?? 'Kaprodi',
                'timestamp' => $approvalRequest->sign_approved_at,
                'icon' => 'fa-stamp',
                'color' => 'success',
                'status' => 'completed'
            ];
        }

        // Document Signature Rejected (if signature was rejected)
        // if ($approvalRequest->documentSignature &&
        //     $approvalRequest->documentSignature->signature_status === DocumentSignature::STATUS_REJECTED &&
        //     $approvalRequest->documentSignature->rejected_at) {
        //     $timeline[] = [
        //         'title' => 'Document Signature Rejected',
        //         'description' => $approvalRequest->documentSignature->rejection_reason ?? 'Signature was rejected due to quality or placement issues',
        //         'user' => $approvalRequest->documentSignature->rejector->name ?? 'Kaprodi',
        //         'timestamp' => $approvalRequest->documentSignature->rejected_at,
        //         'icon' => 'fa-ban',
        //         'color' => 'danger',
        //         'status' => 'completed'
        //     ];
        // }

        // Approval Request Rejected
        if ($approvalRequest->status === ApprovalRequest::STATUS_REJECTED && $approvalRequest->rejected_at) {
            $timeline[] = [
                'title' => 'Request Rejected',
                'description' => $approvalRequest->rejection_reason ?? 'Request was rejected',
                'user' => $approvalRequest->rejector->name ?? 'Kaprodi',
                'timestamp' => $approvalRequest->rejected_at,
                'icon' => 'fa-times-circle',
                'color' => 'danger',
                'status' => 'completed'
            ];
        }

        // Pending actions
        if ($approvalRequest->status === ApprovalRequest::STATUS_PENDING) {
            $timeline[] = [
                'title' => 'Awaiting Approval',
                'description' => 'Waiting for Kaprodi approval',
                'user' => null,
                'timestamp' => null,
                'icon' => 'fa-clock',
                'color' => 'warning',
                'status' => 'pending'
            ];
        } elseif ($approvalRequest->status === ApprovalRequest::STATUS_APPROVED) {
            $timeline[] = [
                'title' => 'Awaiting Student Signature',
                'description' => 'Waiting for student to sign the document',
                'user' => null,
                'timestamp' => null,
                'icon' => 'fa-clock',
                'color' => 'warning',
                'status' => 'pending'
            ];
        } elseif ($approvalRequest->status === ApprovalRequest::STATUS_USER_SIGNED) {
            $timeline[] = [
                'title' => 'Awaiting Signature Approval',
                'description' => 'Waiting for final signature approval from Kaprodi',
                'user' => null,
                'timestamp' => null,
                'icon' => 'fa-clock',
                'color' => 'warning',
                'status' => 'pending'
            ];
        }

        return $timeline;
    }

    // Approve a request
    //! DIPAKAI DI ROUTE admin.signature.approval.approve
    public function approve(Request $request, $id)
    {
        try {
            // check validation if not kaprodi
            $userId = Auth::id();
            $kaprodi = Kaprodi::where('id', $userId)->first();
            if (!$kaprodi) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized: Only Kaprodi can approve requests'
                ], 403);
            }

            // FIX #2: Fix input parameter name mismatch (was 'approval_notes', should be 'notes')
            $notes = $request->input('notes', null);

            $approvalRequest = ApprovalRequest::findOrFail($id);

            // FIX #3: Better validation with specific error response
            if ($approvalRequest->status !== ApprovalRequest::STATUS_PENDING) {
                Log::warning('Attempt to approve non-pending request', [
                    'approval_request_id' => $id,
                    'current_status' => $approvalRequest->status,
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'This request cannot be approved in its current status',
                    'current_status' => $approvalRequest->status
                ], 400);
            }

            // Approve the request (this creates DocumentSignature internally)
            $approvalRequest->approveApprovalRequest(Auth::id(), $notes ?? 'Approved by Kaprodi');
            // $approvalRequest->approveApprovalRequest($kaprodi->id, $notes ?? 'Approved by Kaprodi');

            // FIX #6: Reload approvalRequest to get the freshly created documentSignature relation
            $approvalRequest->refresh();
            $approvalRequest->load('documentSignature');

            // Send notification to student
            try {
                Mail::to($approvalRequest->user->email)->send(
                    new ApprovalRequestApprovedNotification($approvalRequest)
                );

                Log::info('Approval notification email sent to student', [
                    'approval_request_id' => $id,
                    'student_email' => $approvalRequest->user->email
                ]);
            } catch (\Exception $mailException) {
                // Log email failure but don't fail the entire approval
                Log::error('Email notification failed', [
                    'approval_request_id' => $id,
                    'error' => $mailException->getMessage()
                ]);
            }

            Log::info('Approval request approved successfully', [
                'approval_request_id' => $id,
                'approved_by' => Auth::id(),
                'document_signature_created' => $approvalRequest->documentSignature ? true : false
            ]);

            // FIX #1: Return JSON response instead of back()
            return response()->json([
                'success' => true,
                'message' => 'Request approved successfully!',
                'data' => [
                    'approval_request_id' => $approvalRequest->id,
                    'status' => $approvalRequest->status,
                    'document_signature_id' => $approvalRequest->documentSignature?->id
                ]
            ]);

        } catch (\Exception $e) {
            // FIX #3: Enhanced error logging with stack trace
            Log::error('Approval failed', [
                'approval_request_id' => $id,
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            // FIX #1: Return JSON error response instead of back()
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve request: ' . $e->getMessage()
            ], 500);
        }
    }

    // Reject a request
    //! DIPAKAI DI ROUTE admin.signature.approval.reject
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $approvalRequest = ApprovalRequest::findOrFail($id);

            $approvalRequest->reject($request->rejection_reason, Auth::id());

            // Send notification to student
            Mail::to($approvalRequest->user->email)->send(
                new ApprovalRequestRejectedNotification($approvalRequest)
            );

            Log::info('Approval request rejected', [
                'approval_request_id' => $id,
                'rejected_by' => Auth::id(),
                'reason' => $request->rejection_reason
            ]);

            // return back()->with('success', 'Request rejected successfully!');
            return response()->json([
                'success' => true,
                'message' => 'Request rejected successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Rejection failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject request'
            ], 500);
        }
    }

    // Approve signature after user signing
    public function approveSignature(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $approvalRequest = ApprovalRequest::findOrFail($id);

            if (!$approvalRequest->canBeSignApproved()) {
                return back()->with('error', 'Signature cannot be approved at this time');
            }

            $approvalRequest->approveSignature(Auth::id(), $request->approval_notes);

            // Verify signature integrity
            if ($approvalRequest->documentSignature) {
                $verificationResult = $this->verificationService->verifyById(
                    $approvalRequest->documentSignature->id
                );

                if (!$verificationResult['is_valid']) {
                    Log::warning('Signature approved but verification failed', [
                        'approval_request_id' => $id,
                        'verification_result' => $verificationResult
                    ]);
                }
            }

            Log::info('Signature approved', [
                'approval_request_id' => $id,
                'approved_by' => Auth::id()
            ]);

            return back()->with('success', 'Signature approved successfully!');

        } catch (\Exception $e) {
            Log::error('Signature approval failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve signature');
        }
    }

    // Display status of approval requests for the student
    //! DIPAKAI DI ROUTE user.signature.approval.status
    public function status()
    {
        try {
            $approvalRequests = ApprovalRequest::where('user_id', Auth::id())
                ->with(['documentSignature.digitalSignature'])
                ->latest()
                ->get();

            return view('digital-signature.user.status', compact('approvalRequests'));

        } catch (\Exception $e) {
            Log::error('Failed to load status: ' . $e->getMessage());
            return back()->with('error', 'Failed to load status');
        }
    }

    // public function uploadSignedDocument(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'signed_document_path' => 'required|file|mimes:pdf|max:25600',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 400);
    //     }

    //     try {
    //         $approvalRequest = ApprovalRequest::findOrFail($id);

    //         if ($request->hasFile('signed_document_path')) {
    //             $file = $request->file('signed_document_path');
    //             $path = $file->store('signed_documents', 'public');

    //             Log::info('Signed document uploaded: ' . $path);

    //             // Update approval request
    //             $approvalRequest->update([
    //                 'signed_document_path' => $path,
    //                 'status' => ApprovalRequest::STATUS_SIGN_APPROVED
    //             ]);

    //             // Generate QR code if DocumentSignature exists
    //             if ($approvalRequest->documentSignature) {
    //                 $qrData = $this->qrCodeService->generateVerificationQR(
    //                     $approvalRequest->documentSignature->id
    //                 );

    //                 // Send notification with QR code
    //                 Mail::to($approvalRequest->user->email)->send(
    //                     new ApprovalRequestSignedNotification($approvalRequest, $qrData['qr_code_url'])
    //                 );
    //             }

    //             Log::info('Signed document processed successfully', [
    //                 'approval_request_id' => $id,
    //                 'file_path' => $path
    //             ]);

    //             return response()->json(['message' => 'Document uploaded successfully.']);
    //         }

    //         return response()->json(['error' => 'File upload failed.'], 400);

    //     } catch (\Exception $e) {
    //         Log::error('Signed document upload failed: ' . $e->getMessage());
    //         return response()->json(['error' => 'Upload failed.'], 500);
    //     }
    // }

    // Download signed document (or original if not signed)
    // public function downloadSignedDocument($id)
    // {
    //     try {
    //         $approvalRequest = ApprovalRequest::findOrFail($id);

    //         // Check authorization
    //         if (!Kaprodi::isKaprodi() && $approvalRequest->user_id !== Auth::id()) {
    //             abort(403, 'Unauthorized action.');
    //         }

    //         $filePath = $approvalRequest->signed_document_path ?? $approvalRequest->document_path;
    //         $fullPath = storage_path('app/public/' . $filePath);

    //         if (!file_exists($fullPath)) {
    //             abort(404, 'File not found.');
    //         }

    //         $suffix = $approvalRequest->signed_document_path ? '_signed' : '';
    //         $filename = $approvalRequest->document_name . $suffix . '.' . pathinfo($fullPath, PATHINFO_EXTENSION);

    //         Log::info('Document downloaded', [
    //             'approval_request_id' => $id,
    //             'downloaded_by' => Auth::id(),
    //             'file_type' => $suffix ? 'signed' : 'original'
    //         ]);

    //         // return response()->download($fullPath, $filename);
    //         return response($approvalRequest->signed_document_path ?? $approvalRequest->document_path)
    //             ->header('Content-Type', mime_content_type($fullPath))
    //             ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

    //     } catch (\Exception $e) {
    //         Log::error('Document download failed: ' . $e->getMessage());
    //         return back()->with('error', 'Download failed');
    //     }
    // }

    // Download original document (Kaprodi only)
    //! DIPAKAI DI ROUTE admin.signature.approval.download
    public function downloadDocument($id)
    {
        try {
            $approvalRequest = ApprovalRequest::findOrFail($id);

            // Check authorization (only Kaprodi can download original documents)
            if (!Kaprodi::isKaprodi()) {
                abort(403, 'Unauthorized action.');
            }

            $filePath = storage_path('app/public/' . $approvalRequest->document_path);

            if (!file_exists($filePath)) {
                abort(404, 'File not found.');
            }

            $filename = $approvalRequest->document_name . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

            // return response()->download($filePath, $filename, [
            //     'Content-Type' => mime_content_type($filePath),
            //     'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            // ]);

            return response($approvalRequest->document_path)
                ->header('Content-Type', mime_content_type($filePath))
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Original document download failed: ' . $e->getMessage());
            return back()->with('error', 'Download failed');
        }
    }

    // Public verification endpoint for encrypted document IDs
    // public function verifyDocument($encryptedId)
    // {
    //     try {
    //         $decryptedData = Crypt::decryptString($encryptedId);
    //         $approvalRequestId = explode('|', $decryptedData)[0];

    //         $approvalRequest = ApprovalRequest::with([
    //             'user',
    //             'documentSignature.digitalSignature'
    //         ])->findOrFail($approvalRequestId);

    //         // Perform verification
    //         $verificationResult = null;
    //         if ($approvalRequest->documentSignature) {
    //             $verificationResult = $this->verificationService->verifyById(
    //                 $approvalRequest->documentSignature->id
    //             );
    //         }

    //         return view('approval_requests.verification', compact(
    //             'approvalRequest',
    //             'verificationResult'
    //         ));

    //     } catch (\Exception $e) {
    //         Log::warning('Document verification failed', [
    //             'encrypted_id' => $encryptedId,
    //             'error' => $e->getMessage()
    //         ]);

    //         return view('approval_requests.verification-error', [
    //             'message' => 'Invalid or expired verification link'
    //         ]);
    //     }
    // }

    // Legacy verification route for backward compatibility
    // public function verifyLegacy($id)
    // {
    //     return $this->verifyDocument($id);
    // }

    // Show upload form for students
    //! DIPAKAI DI ROUTE user.signature.approval.form
    public function showUploadForm()
    {
        try {
            $hasApprovalRequests = ApprovalRequest::where('user_id', Auth::id())->exists();

            // Get user's recent requests for context
            $recentRequests = ApprovalRequest::where('user_id', Auth::id())
                ->latest()
                ->limit(5)
                ->get();

            Log::info('Upload form accessed', [
                'user_id' => Auth::id(),
                'has_previous_requests' => $hasApprovalRequests,
                'recent_requests_count' => $recentRequests->count()
            ]);

            return view('digital-signature.user.approval-request', compact('hasApprovalRequests', 'recentRequests'));

        } catch (\Exception $e) {
            Log::error('Failed to load upload form', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Failed to load form. Please refresh the page.');
        }
    }

    // Additional utility methods for enhanced functionality

    // public function bulkApprove(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'approval_request_ids' => 'required|array',
    //         'approval_request_ids.*' => 'exists:approval_requests,id',
    //         'bulk_notes' => 'nullable|string|max:500'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 400);
    //     }

    //     try {
    //         $approved = 0;
    //         $failed = 0;

    //         foreach ($request->approval_request_ids as $id) {
    //             try {
    //                 $approvalRequest = ApprovalRequest::findOrFail($id);
    //                 if ($approvalRequest->status === ApprovalRequest::STATUS_PENDING) {
    //                     $approvalRequest->approve(Auth::id(), $request->bulk_notes);
    //                     $approved++;
    //                 }
    //             } catch (\Exception $e) {
    //                 $failed++;
    //                 Log::error("Bulk approve failed for ID {$id}: " . $e->getMessage());
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => "Bulk approval completed. {$approved} approved, {$failed} failed.",
    //             'approved' => $approved,
    //             'failed' => $failed
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Bulk approval failed: ' . $e->getMessage());
    //         return response()->json(['error' => 'Bulk approval failed'], 500);
    //     }
    // }

    // Export approval requests data
    //! DIPAKAI DI ROUTE admin.signature.approval.export
    public function exportApprovalRequests(Request $request)
    {
        try {
            $format = $request->get('format', 'csv');
            $status = $request->get('status');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = ApprovalRequest::with(['user', 'documentSignature']);

            if ($status) {
                $query->where('status', $status);
            }
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            }

            $approvalRequests = $query->get();

            if ($format === 'csv') {
                return $this->exportToCSV($approvalRequests);
            }

            return response()->json($approvalRequests);

        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return back()->with('error', 'Export failed');
        }
    }

    // Export to CSV helper
    //! DIPAKAI DI METHOD exportApprovalRequests
    private function exportToCSV($approvalRequests)
    {
        $filename = 'approval_requests_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function() use ($approvalRequests) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'Nomor',
                'Document Name',
                'Submitted By',
                'Submission Date',
                'Status',
                'Priority',
                'Approved At',
                'Signed At',
                'Department'
            ]);

            // CSV data
            foreach ($approvalRequests as $request) {
                fputcsv($handle, [
                    $request->nomor,
                    $request->document_name,
                    $request->user->name ?? 'Unknown',
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->status_label,
                    $request->priority_label,
                    $request->approved_at ? $request->approved_at->format('Y-m-d H:i:s') : '',
                    $request->user_signed_at ? $request->user_signed_at->format('Y-m-d H:i:s') : '',
                    $request->department
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
