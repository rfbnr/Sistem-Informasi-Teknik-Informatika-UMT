<?php

namespace App\Http\Controllers\DigitalSignature;

use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

class VerificationController extends Controller
{
    protected $verificationService;
    protected $qrCodeService;

    public function __construct(VerificationService $verificationService, QRCodeService $qrCodeService)
    {
        $this->verificationService = $verificationService;
        $this->qrCodeService = $qrCodeService;

        // Apply rate limiting untuk public endpoints
        // $this->middleware('throttle:verification')->only(['verifyByToken', 'verifyPublic']);
    }

    /**
     * Public verification page
     */
    public function verificationPage()
    {
        return view('digital-signature.verification.index');
    }

    /**
     * Verify document signature by QR token (public endpoint)
     */
    public function verifyByToken($token)
    {
        // Rate limiting
        $key = 'verify_token:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return view('digital-signature.verification.rate-limited', compact('seconds'));
        }

        RateLimiter::hit($key, 300); // 5 minutes decay

        try {
            $verificationResult = $this->verificationService->verifyByToken($token);

            // Log public verification attempt
            Log::info('Public verification attempt', [
                'token_hash' => hash('sha256', $token),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => $verificationResult['is_valid'],
                'timestamp' => now()
            ]);

            return view('digital-signature.verification.result', compact('verificationResult'));

        } catch (\Exception $e) {
            Log::warning('Public verification error', [
                'token_hash' => hash('sha256', $token),
                'error' => $e->getMessage(),
                'ip_address' => request()->ip()
            ]);

            return view('digital-signature.verification.error', [
                'message' => 'Verification failed. Please check your QR code or verification link.'
            ]);
        }
    }

    /**
     * Public verification form endpoint
     */
    public function verifyPublic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_input' => 'required|string',
            'verification_type' => 'required|in:token,url,qr,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Rate limiting
        $key = 'verify_public:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Too many verification attempts. Please try again in {$seconds} seconds.");
        }

        RateLimiter::hit($key, 300);

        try {
            $input = $request->verification_input;
            $type = $request->verification_type;

            $verificationResult = null;

            switch ($type) {
                case 'token':
                    $verificationResult = $this->verificationService->verifyByToken($input);
                    break;

                case 'url':
                    // Extract token from URL
                    $token = $this->extractTokenFromUrl($input);
                    if ($token) {
                        $verificationResult = $this->verificationService->verifyByToken($token);
                    } else {
                        throw new \Exception('Invalid verification URL format');
                    }
                    break;

                case 'qr':
                    // QR code can contain either a URL or a direct token
                    if (filter_var($input, FILTER_VALIDATE_URL)) {
                        // Input is a URL, extract token from it
                        $token = $this->extractTokenFromUrl($input);
                        if ($token) {
                            $verificationResult = $this->verificationService->verifyByToken($token);
                        } else {
                            throw new \Exception('Invalid QR code URL format');
                        }
                    } else {
                        // Input is a direct token
                        $verificationResult = $this->verificationService->verifyByToken($input);
                    }
                    break;

                case 'id':
                    // Verify by document signature ID (limited access)
                    if (is_numeric($input)) {
                        $verificationResult = $this->verificationService->verifyById($input);
                    } else {
                        throw new \Exception('Invalid document signature ID');
                    }
                    break;

                default:
                    throw new \Exception('Invalid verification type');
            }

            if (!$verificationResult) {
                throw new \Exception('Verification failed');
            }

            Log::info('Public verification via form', [
                'type' => $type,
                'input_hash' => hash('sha256', $input),
                'result' => $verificationResult['is_valid'],
                'ip_address' => request()->ip()
            ]);

            return view('digital-signature.verification.result', compact('verificationResult'));

        } catch (\Exception $e) {
            Log::warning('Public verification form error', [
                'error' => $e->getMessage(),
                'ip_address' => request()->ip()
            ]);

            return back()->with('error', 'Verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get verification details as JSON (API endpoint)
     */
    public function getVerificationDetails($token)
    {
        // Rate limiting untuk API
        $key = 'api_verify:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, 300);

        try {
            $verificationResult = $this->verificationService->verifyByToken($token);

            // Return formatted response
            return response()->json([
                'success' => true,
                'verification' => [
                    'is_valid' => $verificationResult['is_valid'],
                    'message' => $verificationResult['message'],
                    'verified_at' => $verificationResult['verified_at'],
                    'verification_id' => $verificationResult['verification_id'],
                    'document_info' => $this->formatDocumentInfo($verificationResult),
                    'signature_info' => $this->formatSignatureInfo($verificationResult)
                ]
            ]);

        } catch (\Exception $e) {
            Log::warning('API verification error', [
                'token_hash' => hash('sha256', $token),
                'error' => $e->getMessage(),
                'ip_address' => request()->ip()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Verification failed',
                'message' => 'Invalid or expired verification token'
            ], 400);
        }
    }

    /**
     * Bulk verification endpoint untuk external systems
     */
    public function bulkVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tokens' => 'required|array|max:10',
            'tokens.*' => 'required|string',
            'api_key' => 'required|string' // Require API key untuk bulk operations
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Validate API key (simple implementation)
        if ($request->api_key !== config('app.verification_api_key')) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // Rate limiting untuk bulk operations
        $key = 'bulk_verify:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'error' => 'Bulk verification rate limit exceeded',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        RateLimiter::hit($key, 600); // 10 minutes decay

        try {
            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($request->tokens as $token) {
                try {
                    $verificationResult = $this->verificationService->verifyByToken($token);
                    $results[] = [
                        'token_hash' => hash('sha256', $token),
                        'is_valid' => $verificationResult['is_valid'],
                        'message' => $verificationResult['message'],
                        'verified_at' => $verificationResult['verified_at'],
                        'document_info' => $this->formatDocumentInfo($verificationResult)
                    ];

                    if ($verificationResult['is_valid']) {
                        $successCount++;
                    } else {
                        $failureCount++;
                    }
                } catch (\Exception $e) {
                    $results[] = [
                        'token_hash' => hash('sha256', $token),
                        'is_valid' => false,
                        'message' => 'Verification error',
                        'error' => $e->getMessage()
                    ];
                    $failureCount++;
                }
            }

            Log::info('Bulk verification completed', [
                'total_tokens' => count($request->tokens),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'ip_address' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'summary' => [
                    'total' => count($request->tokens),
                    'verified' => $successCount,
                    'failed' => $failureCount
                ],
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk verification error: ' . $e->getMessage());
            return response()->json(['error' => 'Bulk verification failed'], 500);
        }
    }

    /**
     * Download verification certificate
     */
    public function downloadCertificate($token)
    {
        try {
            $verificationResult = $this->verificationService->verifyByToken($token);

            if (!$verificationResult['is_valid']) {
                return back()->with('error', 'Cannot generate certificate for invalid signature');
            }

            // Generate verification certificate PDF
            $certificate = $this->generateVerificationCertificate($verificationResult);

            $filename = 'verification_certificate_' .
                       $verificationResult['verification_id'] . '_' .
                       now()->format('Y-m-d') . '.pdf';

            return response($certificate)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Certificate download error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate verification certificate');
        }
    }

    /**
     * Verification statistics untuk public display
     */
    public function getPublicStatistics()
    {
        try {
            $stats = [
                'total_verifications_today' => $this->getVerificationCount('today'),
                'total_verifications_this_month' => $this->getVerificationCount('month'),
                'system_status' => 'operational',
                'last_updated' => now()->toISOString()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'system_status' => 'maintenance',
                'message' => 'System temporarily unavailable'
            ], 503);
        }
    }

    /**
     * Extract token from verification URL
     */
    private function extractTokenFromUrl($url)
    {
        // Handle different URL formats
        $patterns = [
            '/\/verify\/([^\/\?]+)/',
            '/[\?&]token=([^&]+)/',
            '/\/signature\/verify\/([^\/\?]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Format document information untuk response
     */
    private function formatDocumentInfo($verificationResult)
    {
        if (!isset($verificationResult['details']['approval_request'])) {
            return null;
        }

        $approvalRequest = $verificationResult['details']['approval_request'];

        return [
            'name' => $approvalRequest->document_name,
            'number' => $approvalRequest->full_document_number,
            'submitted_by' => $approvalRequest->user->name ?? 'Unknown',
            'submitted_at' => $approvalRequest->created_at->toISOString(),
            'status' => $approvalRequest->status
        ];
    }

    /**
     * Format signature information untuk response
     */
    private function formatSignatureInfo($verificationResult)
    {
        if (!isset($verificationResult['details']['document_signature'])) {
            return null;
        }

        $documentSignature = $verificationResult['details']['document_signature'];

        return [
            'algorithm' => $documentSignature->digitalSignature->algorithm ?? 'Unknown',
            'key_length' => $documentSignature->digitalSignature->key_length ?? 'Unknown',
            'signed_at' => $documentSignature->signed_at ? $documentSignature->signed_at->toISOString() : null,
            'signed_by' => $documentSignature->signer->name ?? 'Unknown',
            'status' => $documentSignature->signature_status,
            'verification_checks' => $verificationResult['details']['checks'] ?? []
        ];
    }

    /**
     * Generate verification certificate (placeholder)
     */
    private function generateVerificationCertificate($verificationResult)
    {
        // Placeholder untuk PDF generation
        // Implementasi actual akan menggunakan library seperti TCPDF atau DomPDF

        $content = "VERIFICATION CERTIFICATE\n\n";
        $content .= "Verification ID: " . $verificationResult['verification_id'] . "\n";
        $content .= "Verified At: " . $verificationResult['verified_at'] . "\n";
        $content .= "Status: " . ($verificationResult['is_valid'] ? 'VALID' : 'INVALID') . "\n";
        $content .= "Message: " . $verificationResult['message'] . "\n";

        if (isset($verificationResult['details']['approval_request'])) {
            $doc = $verificationResult['details']['approval_request'];
            $content .= "\nDocument Information:\n";
            $content .= "Name: " . $doc->document_name . "\n";
            $content .= "Number: " . $doc->full_document_number . "\n";
        }

        // Return basic text content for now
        return $content;
    }

    /**
     * Get verification count untuk statistics
     */
    private function getVerificationCount($period)
    {
        // Placeholder implementation
        // Actual implementation would query verification logs

        switch ($period) {
            case 'today':
                return DocumentSignature::whereDate('verified_at', today())->count();
            case 'month':
                return DocumentSignature::where('verified_at', '>=', now()->startOfMonth())->count();
            default:
                return 0;
        }
    }
}
