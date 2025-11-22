<?php

namespace App\Http\Controllers\DigitalSignature;

use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\VerificationService;
use App\Models\SignatureVerificationLog;
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
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return view('digital-signature.verification.rate-limited', compact('seconds'));
        }

        RateLimiter::hit($key, 300); // 5 minutes decay

        try {
            // ✅ FIX: Check if token is short code format (XXXX-XXXX-XXXX)
            $actualToken = $this->resolveToken($token);

            $verificationResult = $this->verificationService->verifyByToken($actualToken);

            if(!$verificationResult) {
                return view('digital-signature.verification.error', [
                    'message' => 'Verification failed. Please check your QR code or verification link.'
                ]);
            }

            //  is valid false
            if(!$verificationResult['is_valid']) {
                return view('digital-signature.verification.result', compact('verificationResult'));
            }

            // Log public verification attempt
            Log::info('Public verification attempt', [
                'token_hash' => hash('sha256', $token),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'result' => $verificationResult['is_valid'],
                'timestamp' => now()
            ]);

            // ✅ FIX: Extract document signature from verification result
            $documentSignature = $verificationResult['details']['document_signature'] ?? null;

            SignatureVerificationLog::create([
                'document_signature_id' => $documentSignature->id ?? null,
                'approval_request_id' => $documentSignature->approval_request_id ?? null,
                'user_id' => Auth::id(),
                'verification_method' => SignatureVerificationLog::METHOD_TOKEN,
                'verification_token_hash' => hash('sha256', $token),
                'is_valid' => $verificationResult['is_valid'],
                'result_status' => $verificationResult['is_valid'] ? SignatureVerificationLog::STATUS_SUCCESS : SignatureVerificationLog::STATUS_INVALID,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('Referer'),
                'verified_at' => now()
            ]);

            return view('digital-signature.verification.result', compact('verificationResult'));

        } catch (\Exception $e) {
            // ✅ FIX: Log detailed error, show generic message with error code
            $errorCode = 'VER_' . strtoupper(substr(md5($e->getMessage()), 0, 8));

            Log::warning('Public verification error', [
                'token_hash' => hash('sha256', $token),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'error_code' => $errorCode,
                'ip_address' => request()->ip()
            ]);

            return view('digital-signature.verification.error', [
                'message' => 'Verifikasi gagal. Silakan coba lagi atau hubungi administrator.',
                'error_code' => $errorCode
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
            // return back()->with('error', "Too many verification attempts. Please try again in {$seconds} seconds.");
            return view('digital-signature.verification.rate-limited', compact('seconds'));
        }

        RateLimiter::hit($key, 300);

        try {
            $input = $request->verification_input;
            $type = $request->verification_type;

            $verificationResult = null;

            switch ($type) {
                case 'token':
                    // ✅ FIX: Resolve short code if needed
                    $actualToken = $this->resolveToken($input);
                    $verificationResult = $this->verificationService->verifyByToken($actualToken);
                    break;

                case 'url':
                    // Extract token from URL
                    $token = $this->extractTokenFromUrl($input);
                    if ($token) {
                        // ✅ FIX: Resolve short code if needed
                        $actualToken = $this->resolveToken($token);
                        $verificationResult = $this->verificationService->verifyByToken($actualToken);
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
                            // ✅ FIX: Resolve short code if needed
                            $actualToken = $this->resolveToken($token);
                            $verificationResult = $this->verificationService->verifyByToken($actualToken);
                        } else {
                            throw new \Exception('Invalid QR code URL format');
                        }
                    } else {
                        // Input is a direct token
                        // ✅ FIX: Resolve short code if needed
                        $actualToken = $this->resolveToken($input);
                        $verificationResult = $this->verificationService->verifyByToken($actualToken);
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

            SignatureVerificationLog::create([
                'document_signature_id' => $verificationResult['document_signature_id'] ?? null,
                'approval_request_id' => $verificationResult['approval_request_id'] ?? null,
                'user_id' => Auth::id(), // Null if not logged in
                'verification_method' => $type,
                'verification_token_hash' => hash('sha256', $input),
                'is_valid' => $verificationResult['is_valid'],
                'result_status' => $verificationResult['is_valid'] ?SignatureVerificationLog::STATUS_SUCCESS : SignatureVerificationLog::STATUS_FAILED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('Referer'),
                'metadata' => [
                    'message' => $verificationResult['message']
                ],
                'verified_at' => now()
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

            SignatureVerificationLog::create([
                'document_signature_id' => $verificationResult['document_signature_id'] ?? null,
                'approval_request_id' => $verificationResult['approval_request_id'] ?? null,
                'user_id' => null, // API calls usually anonymous
                'verification_method' => SignatureVerificationLog::METHOD_TOKEN,
                'verification_token_hash' => hash('sha256', $token),
                'is_valid' => $verificationResult['is_valid'],
                'result_status' => SignatureVerificationLog::STATUS_SUCCESS,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('Referer'),
                'metadata' => [
                    'api_access' => true
                ],
                'verified_at' => now()
            ]);

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
     * ✅ NEW: Resolve token - convert short code to full encrypted token if needed
     *
     * @param string $token Token or short code
     * @return string Full encrypted token
     * @throws \Exception
     */
    private function resolveToken($token)
    {
        // Check if token is short code format: XXXX-XXXX-XXXX (with dashes)
        // Short code: 14 chars (4-4-4 + 2 dashes)
        // Encrypted token: usually 40+ chars, no dashes
        if (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $token)) {
            // This is a short code, resolve to full token
            Log::info('Resolving short code to full token', [
                'short_code' => $token,
                'ip' => request()->ip()
            ]);

            $mapping = \App\Models\VerificationCodeMapping::findByShortCode($token);

            // Track access
            $mapping->trackAccess(request()->ip(), request()->userAgent());

            // Return the full encrypted payload
            return $mapping->encrypted_payload;
        }

        // Already a full encrypted token, return as-is
        return $token;
    }

    /**
     * ✅ HARDENED: Extract token from verification URL with validation
     * Supports both short codes (XXXX-XXXX-XXXX) and full encrypted tokens
     */
    private function extractTokenFromUrl($url)
    {
        // Pattern 1: Short code format (XXXX-XXXX-XXXX)
        $shortCodePattern = '/\/verify\/([A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4})/i';
        if (preg_match($shortCodePattern, $url, $matches)) {
            return $matches[1];
        }

        // Pattern 2: Full encrypted token patterns
        $patterns = [
            '/\/verify\/([a-zA-Z0-9_\-=+\/]{20,500})/',  // Min 20 chars, max 500, valid base64 chars
            '/[\?&]token=([a-zA-Z0-9_\-=+\/]{20,500})/',
            '/\/signature\/verify\/([a-zA-Z0-9_\-=+\/]{20,500})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                $token = $matches[1];

                // ✅ Additional validation: Token length check
                // Encrypted tokens typically 32-256 chars
                if (strlen($token) < 20 || strlen($token) > 500) {
                    continue;  // Skip invalid length tokens
                }

                // ✅ Validate token contains only valid base64-like characters
                if (!preg_match('/^[a-zA-Z0-9_\-=+\/]+$/', $token)) {
                    continue;  // Skip tokens with invalid characters
                }

                return $token;
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

    /**
     * ✅ NEW: View public certificate information (AJAX)
     * Shows SAFE certificate info for public verification
     * HIDES sensitive information (private keys, IP, detailed metadata)
     */
    public function viewPublicCertificate($token)
    {
        try {
            // Verify token and get verification result
            $verificationResult = $this->verificationService->verifyByToken($token);

            if (!$verificationResult['is_valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verifikasi tidak valid. Sertifikat tidak dapat ditampilkan.'
                ], 404);
            }

            // Get document signature
            $documentSignature = $verificationResult['details']['document_signature'] ?? null;

            if (!$documentSignature || !$documentSignature->digitalSignature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sertifikat digital tidak ditemukan.'
                ], 404);
            }

            $digitalSignature = $documentSignature->digitalSignature;
            $certificate = $digitalSignature->certificate;

            if (!$certificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sertifikat tidak tersedia.'
                ], 404);
            }

            // Parse certificate with OpenSSL
            $certInfo = $this->parsePublicCertificateInfo($certificate, $digitalSignature);

            if (!$certInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses sertifikat.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'certificate' => $certInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Public certificate view error', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat sertifikat.'
            ], 500);
        }
    }

    /**
     * ✅ NEW: Parse certificate info for PUBLIC display
     * Returns SAFE information only (no sensitive data)
     */
    private function parsePublicCertificateInfo($certificate, $digitalSignature)
    {
        try {
            // Check if valid X.509 certificate
            if (!str_contains($certificate, 'BEGIN CERTIFICATE') ||
                !str_contains($certificate, 'END CERTIFICATE')) {
                Log::warning('Invalid certificate format for public view');
                return null;
            }

            // Parse certificate
            $certData = openssl_x509_parse($certificate);

            if (!$certData) {
                Log::error('Failed to parse certificate', [
                    'openssl_error' => openssl_error_string()
                ]);
                return null;
            }

            // Calculate days until expiry
            $validUntil = $digitalSignature->valid_until;
            $daysLeft = now()->diffInDays($validUntil, false);

            // Get fingerprint (partial for security)
            $fullFingerprint = openssl_x509_fingerprint($certificate, 'sha256');
            $maskedFingerprint = $this->maskFingerprint($fullFingerprint);

            // ✅ SAFE PUBLIC INFORMATION ONLY
            return [
                // Certificate Basic Info (PUBLIC SAFE)
                'version' => ($certData['version'] ?? 2) + 1,
                // 'serial_number' => $certData['serialNumber'] ?? 'N/A',
                'serial_number' => '*****' . substr($certData['serialNumber'] ?? 'N/A', -5), // Masked for security

                // Subject (Owner) - NO EMAIL for privacy
                'subject' => [
                    'CN' => $certData['subject']['CN'] ?? 'N/A',
                    'OU' => $certData['subject']['OU'] ?? $certData['subject']['organizationalUnitName'] ?? 'N/A',
                    'O' => $certData['subject']['O'] ?? $certData['subject']['organizationName'] ?? 'N/A',
                    'L' => $certData['subject']['L'] ?? $certData['subject']['localityName'] ?? null,
                    'ST' => $certData['subject']['ST'] ?? $certData['subject']['stateOrProvinceName'] ?? null,
                    'C' => $certData['subject']['C'] ?? $certData['subject']['countryName'] ?? 'N/A',
                    // NO EMAIL - Privacy protection
                ],

                // Issuer (Certificate Authority)
                'issuer' => [
                    'CN' => $certData['issuer']['CN'] ?? 'N/A',
                    'OU' => $certData['issuer']['OU'] ?? 'N/A',
                    'O' => $certData['issuer']['O'] ?? 'N/A',
                    'C' => $certData['issuer']['C'] ?? 'N/A',
                ],

                // Validity Period
                'valid_from' => isset($certData['validFrom_time_t'])
                    ? date('d F Y H:i:s', $certData['validFrom_time_t'])
                    : 'N/A',
                'valid_until' => isset($certData['validTo_time_t'])
                    ? date('d F Y H:i:s', $certData['validTo_time_t'])
                    : 'N/A',
                'days_remaining' => (int) $daysLeft,
                'is_expired' => $daysLeft < 0,
                'is_expiring_soon' => $daysLeft >= 0 && $daysLeft <= 30,

                // Cryptographic Info (PUBLIC SAFE)
                'public_key_algorithm' => 'RSA (' . ($certData['bits'] ?? 2048) . ' bit)',
                'signature_algorithm' => $certData['signatureTypeLN'] ?? 'sha256WithRSAEncryption',

                // Fingerprint (MASKED for security)
                'fingerprint_sha256' => $maskedFingerprint,
                'fingerprint_partial' => substr($fullFingerprint, 0, 16) . '...' . substr($fullFingerprint, -16),

                // Certificate Type
                'is_self_signed' => ($certData['issuer']['CN'] ?? '') === ($certData['subject']['CN'] ?? ''),

                // Status
                'status' => $digitalSignature->status,
                'is_revoked' => $digitalSignature->status === 'revoked',

                // ✅ NEW: X.509 v3 Extensions Validation
                'extensions_validation' => $this->parseX509Extensions($certData),

                // NO PRIVATE/SENSITIVE DATA:
                // ❌ No private key
                // ❌ No full serial number
                // ❌ No email addresses
                // ❌ No IP addresses
                // ❌ No metadata (signing IP, user agent, etc.)
                // ❌ No full fingerprint (only masked)
            ];

        } catch (\Exception $e) {
            Log::error('Certificate parsing error for public view', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ NEW: Mask fingerprint for public display (security)
     * Shows only partial fingerprint to prevent tracking
     */
    private function maskFingerprint($fingerprint)
    {
        if (strlen($fingerprint) < 20) {
            return str_repeat('*', strlen($fingerprint));
        }

        // Format: Show first 16 chars, mask middle, show last 16 chars
        $formatted = strtoupper(chunk_split($fingerprint, 2, ':'));
        $formatted = rtrim($formatted, ':');

        $parts = explode(':', $formatted);
        $totalParts = count($parts);

        if ($totalParts < 10) {
            return implode(':', array_fill(0, $totalParts, '**'));
        }

        // Show first 4 pairs, mask middle, show last 4 pairs
        $visible = [];
        for ($i = 0; $i < $totalParts; $i++) {
            if ($i < 4 || $i >= $totalParts - 4) {
                $visible[] = $parts[$i];
            } else {
                $visible[] = '**';
            }
        }

        return implode(':', $visible);
    }

    /**
     * ✅ NEW: Verify uploaded PDF document with comprehensive checks
     * Validates uploaded PDF against stored signature in database
     */
    public function verifyUploadedPDF(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_file' => 'required|file|mimes:pdf|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'File tidak valid. Pastikan file berformat PDF dan maksimal 10MB.');
        }

        // Rate limiting untuk upload
        $key = 'verify_upload:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return view('digital-signature.verification.rate-limited', compact('seconds'));
            // return back()->with('error', "Terlalu banyak percobaan upload. Mohon tunggu {$seconds} detik.");
        }

        RateLimiter::hit($key, 300); // 5 minutes decay

        try {
            $uploadedPdf = $request->file('pdf_file');

            // STEP 1: Calculate uploaded PDF hash
            $uploadedHash = hash_file('sha256', $uploadedPdf->getRealPath());

            Log::info('PDF upload verification attempt', [
                'filename' => $uploadedPdf->getClientOriginalName(),
                'size' => $uploadedPdf->getSize(),
                'hash' => $uploadedHash,
                'ip_address' => request()->ip()
            ]);

            // STEP 2: Find signature in database by hash
            $documentSignature = DocumentSignature::where('document_hash', $uploadedHash)
                ->with(['digitalSignature', 'approvalRequest.user'])
                ->first();

            if (!$documentSignature) {
                Log::warning('No matching signature found for uploaded PDF', [
                    'hash' => $uploadedHash,
                    'ip_address' => request()->ip()
                ]);

                return view('digital-signature.verification.result', [
                    'verificationResult' => [
                        'is_valid' => false,
                        'message' => 'Tidak ditemukan tanda tangan digital yang cocok dengan dokumen ini.',
                        'verified_at' => now(),
                        'verification_id' => uniqid('verify_upload_'),
                        'details' => [
                            'upload_info' => [
                                'filename' => $uploadedPdf->getClientOriginalName(),
                                'size' => $uploadedPdf->getSize(),
                                'hash' => $uploadedHash,
                                'search_method' => 'hash_lookup'
                            ],
                            'checks' => [
                                'hash_lookup' => [
                                    'status' => false,
                                    'message' => 'Dokumen tidak terdaftar dalam sistem atau belum pernah ditandatangani'
                                ]
                            ]
                        ]
                    ]
                ]);
            }

            // STEP 3: Get FINAL signed PDF path (not uploaded PDF)
            $finalSignedPdfPath = \Illuminate\Support\Facades\Storage::disk('public')->path(
                $documentSignature->final_pdf_path
            );

            if (!file_exists($finalSignedPdfPath)) {
                Log::error('Final signed document not found', [
                    'document_signature_id' => $documentSignature->id,
                    'expected_path' => $documentSignature->final_pdf_path
                ]);

                return view('digital-signature.verification.result', [
                    'verificationResult' => [
                        'is_valid' => false,
                        'message' => 'Dokumen yang ditandatangani tidak ditemukan di sistem. Silakan hubungi administrator.',
                        'verified_at' => now(),
                        'verification_id' => uniqid('verify_upload_'),
                        'details' => []
                    ]
                ]);
            }

            // STEP 4: ✅ FIX: Memory-efficient byte-by-byte comparison with streaming
            $contentIdentical = $this->compareFilesInChunks($finalSignedPdfPath, $uploadedPdf->getRealPath());

            // STEP 5: Comprehensive verification
            $verificationResult = $this->verificationService->verifyById($documentSignature->id, true);

            // STEP 6: Add upload-specific checks
            $verificationResult['upload_verification'] = [
                'hash_match' => hash_equals($documentSignature->document_hash, $uploadedHash),
                'content_identical' => $contentIdentical,
                'file_size_match' => filesize($finalSignedPdfPath) === filesize($uploadedPdf->getRealPath()),
                'uploaded_filename' => $uploadedPdf->getClientOriginalName(),
                'uploaded_size' => $uploadedPdf->getSize(),
                'uploaded_at' => now()->toIso8601String(),
                'signature_format' => $documentSignature->signature_format ?? 'unknown'
            ];

            // ✅ Check signature indicators on FINAL signed PDF (ONLY if pkcs7_cms_detached)
            if ($documentSignature->signature_format === 'pkcs7_cms_detached') {
                Log::info('Checking PDF signature indicators', [
                    'checking_file' => 'final_signed_pdf',
                    'path' => $finalSignedPdfPath,
                    'signature_format' => $documentSignature->signature_format
                ]);

                $pdfSignatureCheck = $this->checkPDFSignatureIndicators($finalSignedPdfPath);
                $verificationResult['upload_verification']['pdf_signature_indicators'] = $pdfSignatureCheck;
            }

            // STEP 7: Log verification
            Log::info('Uploaded PDF verification completed', [
                'document_signature_id' => $documentSignature->id,
                'is_valid' => $verificationResult['is_valid'],
                'hash_match' => $verificationResult['upload_verification']['hash_match'],
                'content_identical' => $contentIdentical,
                'signature_indicators' => $pdfSignatureCheck ?? null,
                'ip_address' => request()->ip()
            ]);

            // Create verification log
            SignatureVerificationLog::create([
                'document_signature_id' => $documentSignature->id,
                'approval_request_id' => $documentSignature->approval_request_id,
                'user_id' => Auth::id(),
                'verification_method' => 'upload',
                'verification_token_hash' => hash('sha256', $uploadedPdf->getClientOriginalName()),
                'is_valid' => $verificationResult['is_valid'] && $contentIdentical,
                'result_status' => $verificationResult['is_valid'] && $contentIdentical ?
                    SignatureVerificationLog::STATUS_SUCCESS : SignatureVerificationLog::STATUS_FAILED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('Referer'),
                'metadata' => [
                    'upload_info' => $verificationResult['upload_verification'],
                    'verification_method' => 'pdf_upload'
                ],
                'verified_at' => now()
            ]);

            // Normal form submission (fallback)
            return view('digital-signature.verification.result', compact('verificationResult'));

        } catch (\Exception $e) {
            Log::error('Uploaded PDF verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memverifikasi dokumen: ' . $e->getMessage());
        }
    }

    /**
     * ✅ NEW: Check for PDF digital signature indicators
     *
     * This method checks for common PDF signature markers without requiring
     * a full PDF parsing library. It looks for:
     * - /ByteRange keyword (indicates signature placeholder)
     * - /Contents keyword (contains signature data)
     * - /Type /Sig (signature dictionary type)
     * - /SubFilter /adbe.pkcs7.detached (PKCS#7 detached signature)
     *
     * @param string $pdfPath Path to PDF file
     * @return array Check results
     */
    private function checkPDFSignatureIndicators($pdfPath)
    {
        try {
            // Read PDF content as binary
            $pdfContent = file_get_contents($pdfPath);

            if (!$pdfContent) {
                return [
                    'checked' => false,
                    'error' => 'Could not read PDF file'
                ];
            }

            // Check for PDF signature indicators
            $indicators = [
                'has_byterange' => str_contains($pdfContent, '/ByteRange'),
                'has_contents' => str_contains($pdfContent, '/Contents'),
                'has_sig_type' => str_contains($pdfContent, '/Type /Sig'),
                'has_pkcs7_subfilter' => str_contains($pdfContent, '/SubFilter /adbe.pkcs7.detached') ||
                                        str_contains($pdfContent, '/SubFilter/adbe.pkcs7.detached'),
                'has_signature_field' => str_contains($pdfContent, '/FT /Sig') ||
                                        str_contains($pdfContent, '/FT/Sig'),
            ];

            // Count positive indicators
            $positiveCount = count(array_filter($indicators));
            $totalChecks = count($indicators);

            // Determine likelihood
            $confidence = 'none';
            if ($positiveCount >= 4) {
                $confidence = 'high'; // Very likely has embedded signature
            } elseif ($positiveCount >= 2) {
                $confidence = 'medium'; // Possibly has embedded signature
            } elseif ($positiveCount >= 1) {
                $confidence = 'low'; // Weak indicators
            }

            Log::info('PDF signature indicators check', [
                'path' => $pdfPath,
                'indicators' => $indicators,
                'positive_count' => $positiveCount,
                'confidence' => $confidence
            ]);

            return [
                'checked' => true,
                'indicators' => $indicators,
                'positive_count' => $positiveCount,
                'total_checks' => $totalChecks,
                'confidence' => $confidence,
                'interpretation' => $this->interpretPDFSignatureIndicators($confidence, $indicators),
                'note' => 'This is a heuristic check. Full PDF signature extraction requires specialized libraries.'
            ];

        } catch (\Exception $e) {
            Log::error('PDF signature indicators check failed', [
                'error' => $e->getMessage(),
                'path' => $pdfPath
            ]);

            return [
                'checked' => false,
                'error' => 'Check failed: ' . $e->getMessage()
            ];
        }
    }


    /**
     * ✅ UPDATED: Check for digital signature (both embedded and detached)
     *
     * @param string $pdfPath Path to PDF file (FINAL signed PDF)
     * @param DocumentSignature|null $documentSignature Document signature from database
     * @return array Check results
     */
    // private function checkPDFSignatureIndicators($pdfPath, $documentSignature = null)
    // {
    //     try {
    //         // Read PDF content as binary
    //         $pdfContent = file_get_contents($pdfPath);

    //         if (!$pdfContent) {
    //             return [
    //                 'checked' => false,
    //                 'error' => 'Could not read PDF file'
    //             ];
    //         }

    //         // dd($pdfContent);

    //         // ✅ STEP 1: Check for EMBEDDED signature markers in PDF structure
    //         $embeddedIndicators = [
    //             'has_byterange' => str_contains($pdfContent, '/ByteRange'),
    //             'has_contents' => str_contains($pdfContent, '/Contents') &&
    //                             (str_contains($pdfContent, '/Sig') || str_contains($pdfContent, 'pkcs7')),
    //             'has_sig_type' => str_contains($pdfContent, '/Type /Sig') ||
    //                             str_contains($pdfContent, '/Type/Sig'),
    //             'has_pkcs7_subfilter' => str_contains($pdfContent, '/SubFilter /adbe.pkcs7.detached') ||
    //                                     str_contains($pdfContent, '/SubFilter/adbe.pkcs7.detached'),
    //             'has_signature_field' => str_contains($pdfContent, '/FT /Sig') ||
    //                                     str_contains($pdfContent, '/FT/Sig'),
    //             'has_adobe_ppklite' => str_contains($pdfContent, '/Filter /Adobe.PPKLite') ||
    //                                 str_contains($pdfContent, '/Filter/Adobe.PPKLite'),
    //         ];

    //         // dd($embeddedIndicators);

    //         $embeddedCount = count(array_filter($embeddedIndicators));

    //         // ✅ STEP 2: Check for DETACHED signature (our implementation)
    //         $detachedIndicators = [
    //             'has_database_signature' => false,
    //             'has_cms_signature' => false,
    //             'signature_format_matches' => false,
    //             'hash_matches' => false,
    //             'signature_valid' => false
    //         ];

    //         if ($documentSignature) {
    //             $detachedIndicators['has_database_signature'] = true;
    //             $detachedIndicators['has_cms_signature'] = !empty($documentSignature->cms_signature);
    //             $detachedIndicators['signature_format_matches'] =
    //                 $documentSignature->signature_format === 'pkcs7_cms_detached';

    //             // Verify hash matches with FINAL signed PDF
    //             $finalPdfHash = hash_file('sha256', $pdfPath);
    //             $detachedIndicators['hash_matches'] = hash_equals(
    //                 $documentSignature->document_hash,
    //                 $finalPdfHash
    //             );

    //             // Check if signature is valid (not expired/revoked)
    //             if ($documentSignature->digitalSignature) {
    //                 $detachedIndicators['signature_valid'] = $documentSignature->digitalSignature->isValid();
    //             }
    //         }

    //         $detachedCount = count(array_filter($detachedIndicators));

    //         // ✅ STEP 3: Check for METADATA markers (from our addSignatureMarkersToPDF)
    //         $metadataIndicators = [
    //             'has_creator_umt' => str_contains($pdfContent, 'UMT Digital Signature System'),
    //             'has_producer_umt' => str_contains($pdfContent, 'UMT Digital Signature'),
    //             'has_signature_keywords' => str_contains($pdfContent, 'digital signature') ||
    //                                     str_contains($pdfContent, 'pkcs7'),
    //         ];

    //         $metadataCount = count(array_filter($metadataIndicators));

    //         // ✅ STEP 4: Determine signature type and confidence
    //         $signatureType = 'none';
    //         $confidence = 'none';
    //         $interpretation = '';

    //         if ($embeddedCount >= 4 && $detachedCount >= 4) {
    //             // HYBRID: Both embedded + detached
    //             $signatureType = 'hybrid';
    //             $confidence = 'very_high';
    //             $interpretation = 'PDF contains HYBRID signature (both embedded PKCS#7 in PDF structure + detached in database). Maximum security.';
    //         } elseif ($embeddedCount >= 4) {
    //             // Strong embedded signature
    //             $signatureType = 'embedded';
    //             $confidence = 'high';
    //             $interpretation = 'PDF contains embedded PKCS#7 digital signature in PDF structure';
    //         } elseif ($detachedCount >= 4) {
    //             // Strong detached signature (our main implementation)
    //             $signatureType = 'detached';
    //             $confidence = 'high';
    //             $interpretation = 'PDF signed with DETACHED PKCS#7 signature (stored separately in database)';
    //         } elseif ($detachedCount >= 3 && $metadataCount >= 2) {
    //             // Detached with metadata markers
    //             $signatureType = 'detached_with_metadata';
    //             $confidence = 'high';
    //             $interpretation = 'PDF signed with DETACHED PKCS#7 signature with embedded metadata markers';
    //         } elseif ($embeddedCount >= 2 || $detachedCount >= 2) {
    //             $signatureType = 'partial';
    //             $confidence = 'medium';
    //             $interpretation = 'PDF has partial signature indicators';
    //         } elseif ($embeddedCount >= 1 || $detachedCount >= 1 || $metadataCount >= 1) {
    //             $signatureType = 'weak';
    //             $confidence = 'low';
    //             $interpretation = 'PDF has weak signature indicators';
    //         } else {
    //             $signatureType = 'none';
    //             $confidence = 'none';
    //             $interpretation = 'No digital signature detected';
    //         }

    //         Log::info('PDF signature indicators check (comprehensive)', [
    //             'path' => $pdfPath,
    //             'signature_type' => $signatureType,
    //             'embedded_count' => $embeddedCount,
    //             'detached_count' => $detachedCount,
    //             'metadata_count' => $metadataCount,
    //             'confidence' => $confidence
    //         ]);

    //         return [
    //             'checked' => true,
    //             'signature_type' => $signatureType,
    //             'confidence' => $confidence,
    //             'interpretation' => $interpretation,

    //             // Embedded signature indicators
    //             // 'embedded_indicators' => $embeddedIndicators,
    //             // 'embedded_positive_count' => $embeddedCount,
    //             // 'indicators' => $embeddedIndicators,
    //             // 'positive_count' => $embeddedCount,
    //             // 'total_checks' => count($embeddedIndicators),

    //             // Detached signature indicators
    //             // 'detached_indicators' => $detachedIndicators,
    //             // 'detached_positive_count' => $detachedCount,
    //             'indicators' => $detachedIndicators,
    //             'positive_count' => $detachedCount,
    //             'total_checks' => count($detachedIndicators),

    //             // Metadata indicators
    //             'metadata_indicators' => $metadataIndicators,
    //             'metadata_positive_count' => $metadataCount,

    //             'note' => $signatureType === 'detached' || $signatureType === 'detached_with_metadata'
    //                 ? 'This PDF uses DETACHED signature (stored separately in database). PDF structure may not contain embedded signature dictionary.'
    //                 : 'Checking for embedded PDF signature markers.'
    //         ];

    //     } catch (\Exception $e) {
    //         Log::error('PDF signature indicators check failed', [
    //             'error' => $e->getMessage(),
    //             'path' => $pdfPath
    //         ]);

    //         return [
    //             'checked' => false,
    //             'error' => 'Check failed: ' . $e->getMessage()
    //         ];
    //     }
    // }

    /**
     * ✅ NEW: Interpret PDF signature indicators
     *
     * @param string $confidence Confidence level
     * @param array $indicators Indicator results
     * @return string Human-readable interpretation
     */
    private function interpretPDFSignatureIndicators($confidence, $indicators)
    {
        switch ($confidence) {
            case 'high':
                if ($indicators['has_pkcs7_subfilter']) {
                    return 'PDF contains strong evidence of PKCS#7 digital signature (adbe.pkcs7.detached format)';
                }
                return 'PDF contains strong evidence of digital signature';

            case 'medium':
                return 'PDF may contain digital signature markers, but not all expected fields are present';

            case 'low':
                return 'PDF has minimal signature indicators. May not contain embedded signature.';

            default:
                return 'No digital signature indicators found in PDF structure';
        }
    }

    /**
     * ✅ NEW: Parse X.509 v3 Extensions for validation display
     *
     * @param array $certData Parsed certificate data from openssl_x509_parse()
     * @return array Extensions validation results
     */
    private function parseX509Extensions($certData)
    {
        $extensions = $certData['extensions'] ?? [];

        $validations = [
            'basicConstraints' => [
                'name' => 'Basic Constraints',
                'present' => isset($extensions['basicConstraints']),
                'value' => $extensions['basicConstraints'] ?? null,
                'expected' => 'CA:FALSE',
                'valid' => ($extensions['basicConstraints'] ?? '') === 'CA:FALSE',
                'critical' => true,
                'description' => 'Indicates if certificate can be used as CA'
            ],
            'keyUsage' => [
                'name' => 'Key Usage',
                'present' => isset($extensions['keyUsage']),
                'value' => $extensions['keyUsage'] ?? null,
                'expected' => 'Digital Signature, Non Repudiation',
                'valid' => str_contains($extensions['keyUsage'] ?? '', 'Digital Signature'),
                'critical' => true,
                'description' => 'Defines cryptographic operations allowed'
            ],
            'extendedKeyUsage' => [
                'name' => 'Extended Key Usage',
                'present' => isset($extensions['extendedKeyUsage']),
                'value' => $extensions['extendedKeyUsage'] ?? null,
                'expected' => 'Code Signing, E-mail Protection',
                'valid' => isset($extensions['extendedKeyUsage']),
                'critical' => false,
                'description' => 'Additional usage purposes'
            ],
            'subjectKeyIdentifier' => [
                'name' => 'Subject Key Identifier',
                'present' => isset($extensions['subjectKeyIdentifier']),
                'value' => isset($extensions['subjectKeyIdentifier']) ? substr($extensions['subjectKeyIdentifier'], 0, 20) . '...' : null,
                'expected' => 'Present',
                'valid' => isset($extensions['subjectKeyIdentifier']),
                'critical' => false,
                'description' => 'Unique identifier for public key'
            ],
            'authorityKeyIdentifier' => [
                'name' => 'Authority Key Identifier',
                'present' => isset($extensions['authorityKeyIdentifier']),
                'value' => isset($extensions['authorityKeyIdentifier']) ? 'Present' : null,
                'expected' => 'Present',
                'valid' => isset($extensions['authorityKeyIdentifier']),
                'critical' => false,
                'description' => 'Links to issuer certificate'
            ],
        ];

        // Summary
        $allValid = collect($validations)->every(fn($v) => $v['valid']);
        $validCount = collect($validations)->filter(fn($v) => $v['valid'])->count();
        $totalCount = count($validations);

        return [
            'checks' => $validations,
            'all_valid' => $allValid,
            'valid_count' => $validCount,
            'total_count' => $totalCount,
            'summary' => "{$validCount}/{$totalCount} extensions valid",
            'has_critical_failures' => collect($validations)->filter(fn($v) => $v['critical'] && !$v['valid'])->isNotEmpty()
        ];
    }

    /**
     * ✅ NEW: Memory-efficient file comparison using chunk-based streaming
     * Compares two files byte-by-byte without loading entire files into memory
     *
     * @param string $file1 Path to first file
     * @param string $file2 Path to second file
     * @param int $chunkSize Chunk size in bytes (default 8KB)
     * @return bool True if files are identical, false otherwise
     */
    private function compareFilesInChunks($file1, $file2, $chunkSize = 8192)
    {
        // Quick size check first
        if (filesize($file1) !== filesize($file2)) {
            return false;
        }

        $handle1 = fopen($file1, 'rb');
        $handle2 = fopen($file2, 'rb');

        if (!$handle1 || !$handle2) {
            if ($handle1) fclose($handle1);
            if ($handle2) fclose($handle2);
            return false;
        }

        $identical = true;

        while (!feof($handle1) && !feof($handle2)) {
            $chunk1 = fread($handle1, $chunkSize);
            $chunk2 = fread($handle2, $chunkSize);

            if ($chunk1 !== $chunk2) {
                $identical = false;
                break;
            }
        }

        // Ensure both files reached EOF
        if ($identical && (feof($handle1) !== feof($handle2))) {
            $identical = false;
        }

        fclose($handle1);
        fclose($handle2);

        return $identical;
    }
}
