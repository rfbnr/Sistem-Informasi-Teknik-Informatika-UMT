<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use Illuminate\Http\Request;
use App\Models\SignatureRequest;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SignatureController extends Controller
{
    use AuthorizesRequests;

    protected $blockchainService;

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * Display pending signature requests for current user
     */
    public function index()
    {
        $user = Auth::user();

        $pendingSignatures = SignatureRequest::whereHas('signees', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', 'pending');
            })
            ->with(['document', 'requester', 'signatures'])
            ->latest()
            ->paginate(10);

        $completedSignatures = SignatureRequest::whereHas('signees', function($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', 'signed');
            })
            ->with(['document', 'requester', 'signatures'])
            ->latest()
            ->paginate(10);

        // $completedSignatures = $user->signatures()
        //     ->where('status', 'signed')
        //     ->with(['signatureRequest.document', 'signatureRequest.requester'])
        //     ->latest()
        //     ->paginate(10);

        return view('signatures.index', compact('pendingSignatures', 'completedSignatures'));
    }

    /**
     * Show signature request details
     */
    public function show(SignatureRequest $signatureRequest)
    {
        $this->authorize('view', $signatureRequest);

        $signatureRequest->load([
            'document',
            'requester',
            'signatures.signer',
            'signees' => function($query) {
                $query->orderBy('order');
            }
        ]);

        // Mark as viewed
        $signatureRequest->signees()
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->update([
                'status' => 'viewed',
                'viewed_at' => now()
            ]);

        return view('signatures.show', compact('signatureRequest'));
    }

    /**
     * Show signing interface
     */
    public function sign(SignatureRequest $signatureRequest)
    {
        $this->authorize('sign', $signatureRequest);

        // Check if it's user's turn to sign (for sequential workflow)
        if ($signatureRequest->workflow_type === 'sequential') {
            $nextSigner = $signatureRequest->getNextSigner();
            if (!$nextSigner || $nextSigner->id !== Auth::id()) {
                return redirect()->back()
                    ->with('error', 'Belum waktunya Anda untuk menandatangani dokumen ini.');
            }
        }

        return view('signatures.sign', compact('signatureRequest'));
    }

    /**
     * Process digital signature
     */
    public function processSignature(Request $request, SignatureRequest $signatureRequest)
    {
        $this->authorize('sign', $signatureRequest);

        $validator = Validator::make($request->all(), [
            'signature_method' => 'required|in:digital_signature,electronic_signature,pin_signature',
            'signature_data' => 'required|string',
            'pin' => 'required_if:signature_method,pin_signature|string|min:6',
            'location' => 'nullable|array',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'location.accuracy' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify PIN if required
            if ($request->signature_method === 'pin_signature') {
                // Implement PIN verification logic
                if (!$this->verifyPin($request->pin, Auth::user())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'PIN tidak valid.'
                    ], 422);
                }
            }

            // Create signature record
            $signature = Signature::create([
                'signature_request_id' => $signatureRequest->id,
                'signer_id' => Auth::id(),
                'signature_data' => base64_encode($request->signature_data),
                'signed_at' => now(),
                'signature_method' => $request->signature_method,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'location' => $request->location,
                'status' => 'signed',
                'metadata' => [
                    'browser' => $request->header('User-Agent'),
                    'platform' => $request->header('Sec-Ch-Ua-Platform'),
                    'timestamp' => now()->toISOString()
                ]
            ]);

            // Generate signature hash
            $signature->signature_hash = $signature->generateSignatureHash();
            $signature->verification_code = $signature->generateVerificationCode();
            $signature->save();

            // Store signature on blockchain
            $this->blockchainService->storeSignature($signature);

            // Update signee status
            $signatureRequest->signees()
                ->where('user_id', Auth::id())
                ->update([
                    'status' => 'signed',
                    'responded_at' => now()
                ]);

            // Check if all required signatures are completed
            $this->checkSignatureCompletion($signatureRequest);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditandatangani dan disimpan di blockchain!',
                'signature_id' => $signature->id,
                'verification_code' => $signature->verification_code,
                'blockchain_tx_hash' => $signature->blockchain_tx_hash
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses tanda tangan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject signature request
     */
    public function reject(Request $request, SignatureRequest $signatureRequest)
    {
        $this->authorize('sign', $signatureRequest);

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create rejection signature
            Signature::create([
                'signature_request_id' => $signatureRequest->id,
                'signer_id' => Auth::id(),
                'signed_at' => now(),
                'signature_method' => 'rejection',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'rejected',
                'metadata' => [
                    'rejection_reason' => $request->rejection_reason,
                    'rejected_at' => now()->toISOString()
                ]
            ]);

            // Update signee status
            $signatureRequest->signees()
                ->where('user_id', Auth::id())
                ->update([
                    'status' => 'rejected',
                    'responded_at' => now(),
                    'rejection_reason' => $request->rejection_reason
                ]);

            // Update signature request status
            $signatureRequest->update(['status' => 'rejected']);

            return redirect()->route('signatures.index')
                ->with('success', 'Permintaan tanda tangan berhasil ditolak.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Verify signature authenticity
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|string|size:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $signature = Signature::where('verification_code', $request->verification_code)
            ->with(['signatureRequest.document', 'signer'])
            ->first();

        if (!$signature) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi tidak ditemukan.'
            ], 404);
        }

        // Verify signature integrity
        $localVerification = $signature->verifySignature();
        $blockchainVerification = $this->blockchainService->verifySignature($signature->signature_hash);

        return response()->json([
            'success' => true,
            'signature' => [
                'id' => $signature->id,
                'signer_name' => $signature->signer->name,
                'signed_at' => $signature->signed_at->toISOString(),
                'document_title' => $signature->signatureRequest->document->title,
                'verification_code' => $signature->verification_code,
                'signature_method' => $signature->signature_method,
                'local_verification' => $localVerification,
                'blockchain_verification' => $blockchainVerification,
                'overall_status' => $localVerification && $blockchainVerification ? 'verified' : 'invalid',
                'blockchain_tx_hash' => $signature->blockchain_tx_hash
            ]
        ]);
    }

    /**
     * Download signed document
     */
    public function downloadSigned(SignatureRequest $signatureRequest)
    {
        $this->authorize('view', $signatureRequest);

        if ($signatureRequest->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Dokumen belum selesai ditandatangani.');
        }

        // Generate signed PDF with signature metadata
        $signedPdf = $this->generateSignedPdf($signatureRequest);

        return response()->download($signedPdf,
            'signed_' . $signatureRequest->document->title . '.pdf'
        );
    }

    /**
     * Verify PIN
     */
    private function verifyPin($pin, $user)
    {
        // Implement PIN verification logic
        // This could check against a stored hash or use 2FA
        // $user parameter used for future PIN validation against user record
        return strlen($pin) >= 6 && $user; // Simplified for demo
    }

    /**
     * Check if signature request is completed
     */
    private function checkSignatureCompletion(SignatureRequest $signatureRequest)
    {
        $requiredSignees = $signatureRequest->signees()->wherePivot('required', true)->count();
        $completedSignatures = $signatureRequest->signees()
            ->wherePivot('required', true)
            ->wherePivot('status', 'signed')
            ->count();

        if ($completedSignatures >= $requiredSignees) {
            $signatureRequest->update(['status' => 'completed']);
            $signatureRequest->document->update(['status' => 'signed']);

            // Send completion notifications
            $this->sendCompletionNotifications($signatureRequest);
        }
    }

    /**
     * Generate signed PDF with signature metadata
     */
    private function generateSignedPdf(SignatureRequest $signatureRequest)
    {
        // Implementation for generating PDF with signature overlays
        // This would use PDF manipulation libraries
        return $signatureRequest->document->file_path;
    }

    /**
     * Send completion notifications
     */
    private function sendCompletionNotifications(SignatureRequest $signatureRequest)
    {
        // Implementation for sending email notifications
        // Mail::to($signatureRequest->requester->email)->queue(new SignatureCompletedNotification($signatureRequest));
        Log::info('Signature completed for request: ' . $signatureRequest->id);
    }
}
