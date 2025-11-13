<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ApprovalRequest;
use App\Services\QRCodeService;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\VerificationCodeMapping;
use Illuminate\Support\Facades\Storage;
use App\Models\SignatureVerificationLog;
use App\Services\DigitalSignatureService;

class VerificationService
{
    private $qrCodeService;
    private $digitalSignatureService;

    public function __construct(QRCodeService $qrCodeService, DigitalSignatureService $digitalSignatureService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->digitalSignatureService = $digitalSignatureService;
    }

    /**
     * Verify document signature by token
     */
    public function verifyByToken($encryptedToken)
    {
        $startTime = microtime(true); // Track verification duration

        try {
            // dd($encryptedToken);
            // Decrypt QR code data
            $qrResult = $this->qrCodeService->verifyQRCode($encryptedToken);

            if (!$qrResult['is_valid']) {
                return $this->createVerificationResult(false, $qrResult['error_message']);
            }

            $documentSignature = $qrResult['document_signature'];
            $approvalRequest = $qrResult['approval_request'];

            // Perform comprehensive verification
            $verificationResult = $this->performComprehensiveVerification($documentSignature);
            // dd($verificationResult);

            // Log verification attempt with duration tracking
            $this->logVerificationAttempt($documentSignature, $verificationResult, $encryptedToken, $startTime);

            return $verificationResult;

        } catch (\Exception $e) {
            Log::error('Token verification failed: ' . $e->getMessage());
            return $this->createVerificationResult(false, 'Verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify document signature by ID
     */
    //! DIPAKAI DI CONTROLLER DocumentSignatureController method show, quickPreview, DI CONTROLLER ApprovalRequestController method show
    public function verifyById($documentSignatureId, bool $isUploadedPdf = false)
    {
        $startTime = microtime(true); // Track verification duration

        try {
            $documentSignature = DocumentSignature::findOrFail($documentSignatureId);

            // Perform comprehensive verification
            $verificationResult = $this->performComprehensiveVerification($documentSignature, $isUploadedPdf);

            // Log verification attempt with duration tracking
            $this->logVerificationAttempt($documentSignature, $verificationResult, $documentSignature->verification_token, $startTime);

            return $verificationResult;

        } catch (\Exception $e) {
            Log::error('ID verification failed: ' . $e->getMessage());
            return $this->createVerificationResult(false, 'Document signature not found');
        }
    }

    /**
     * Perform comprehensive verification
     */
    //! DIPAKAI DI verifyByToken DAN verifyById
    private function performComprehensiveVerification($documentSignature, bool $isUploadedPdf = false)
    {
        $checks = [];
        $overallValid = true;
        $warnings = [];

        try {
            // 1. Check document signature existence and basic info
            // $checks['document_exists'] = [
            //     'status' => true,
            //     'message' => 'Document signature record found',
            //     'details' => [
            //         'id' => $documentSignature->id,
            //         'signed_at' => $documentSignature->signed_at,
            //         'status' => $documentSignature->signature_status
            //     ]
            // ];


            // 1. Check document signature existence and check signature status
            if ($documentSignature->signature_status !== 'verified' && !$isUploadedPdf) {
                $checks['document_exists'] = [
                    'status' => false,
                    'message' => 'Document signature record found and status is "' . $documentSignature->signature_status . '", not "verified"',
                    'details' => [
                        'current_status' => $documentSignature->signature_status
                    ]
                ];
                $overallValid = false;
            } else {
                $checks['document_exists'] = [
                    'status' => true,
                    'message' => 'Document signature record found and status is "' . $documentSignature->signature_status . '"',
                    'details' => [
                        'current_status' => $documentSignature->signature_status
                    ]
                ];
            }

            // 2. Check digital signature validity
            $digitalSignature = $documentSignature->digitalSignature;
            if (!$digitalSignature) {
                $checks['digital_signature'] = [
                    'status' => false,
                    'message' => 'Digital signature key not found'
                ];
                $overallValid = false;
            } else {
                $isValidKey = $digitalSignature->isValid();
                $checks['digital_signature'] = [
                    'status' => $isValidKey,
                    'message' => $isValidKey ? 'Digital signature key is valid' : 'Digital signature key is invalid or expired',
                    'details' => [
                        'signature_id' => $digitalSignature->signature_id,
                        'algorithm' => $digitalSignature->algorithm,
                        'key_length' => $digitalSignature->key_length,
                        'status' => $digitalSignature->status,
                        'valid_until' => $digitalSignature->valid_until,
                        'days_until_expiry' => $digitalSignature->valid_until->diffInDays(now(), false)
                    ]
                ];


                if (!$isValidKey) {
                    $overallValid = false;
                }

                // Warning jika akan expired dalam 30 hari
                if ($digitalSignature->isExpiringSoon(30)) {
                    $warnings[] = 'Digital signature will expire soon';
                }
            }

            // 3. Check approval request status
            $approvalRequest = $documentSignature->approvalRequest;
            if (!$approvalRequest) {
                $checks['approval_request'] = [
                    'status' => false,
                    'message' => 'Associated approval request not found'
                ];
                $overallValid = false;
            } else {
                $checks['approval_request'] = [
                    'status' => true,
                    'message' => 'Approval request found',
                    'details' => [
                        'id' => $approvalRequest->id,
                        'document_name' => $approvalRequest->document_name,
                        'document_type' => $approvalRequest->document_type,
                        'document_number' => $approvalRequest->full_document_number,
                        'status' => $approvalRequest->status,
                        'submitted_by' => $approvalRequest->user->name ?? 'Unknown',
                        'submitted_at' => $approvalRequest->created_at
                    ]
                ];
            }

            // 4. Verify document integrity (if file exists)
            if ($approvalRequest && $approvalRequest->document_path) {
                $documentIntegrityCheck = $this->verifyDocumentIntegrity($documentSignature, $approvalRequest);
                $checks['document_integrity'] = $documentIntegrityCheck;

                if (!$documentIntegrityCheck['status']) {
                    $overallValid = false;
                }
            } else {
                $checks['document_integrity'] = [
                    'status' => false,
                    'message' => 'Original document file not found'
                ];
                $warnings[] = 'Original document file is not available for integrity check';
            }

            // 5. Verify CMS signature
            if ($documentSignature->cms_signature && $digitalSignature) {
                $cmsVerificationCheck = $this->verifyCMSSignature($documentSignature, $approvalRequest);
                $checks['cms_signature'] = $cmsVerificationCheck;

                // dd($cmsVerificationCheck);

                if (!$cmsVerificationCheck['status']) {
                    $overallValid = false;
                }
            } else {
                $checks['cms_signature'] = [
                    'status' => false,
                    'message' => 'CMS signature not found'
                ];
                $overallValid = false;
            }

            // 6. Check signature timestamp validity
            $timestampCheck = $this->verifyTimestamp($documentSignature);
            $checks['timestamp'] = $timestampCheck;

            // 7. Certificate chain validation (if applicable)
            if ($digitalSignature && $digitalSignature->certificate) {
                $certificateCheck = $this->verifyCertificate($digitalSignature);
                $checks['certificate'] = $certificateCheck;

                if (!$certificateCheck['status']) {
                    $warnings[] = $certificateCheck['message'];
                }
            }

            // 8. Special handling for uploaded PDF verification
            if ($isUploadedPdf) {
                $checks['upload_verification'] = [
                    'status' => $overallValid,
                    'message' => $overallValid ? 'Uploaded PDF matches the signed document' : 'Uploaded PDF does not match the signed document',
                ];
            }

            // Get Short code from verification code mappings
            $shortCode = VerificationCodeMapping::getShortCodeFromDocumentSignatureId($documentSignature->id);

            return $this->createVerificationResult(
                $overallValid,
                $overallValid ? 'Document signature is valid' : 'Document signature verification failed',
                [
                    'short_code_token' => $shortCode,
                    'checks' => $checks,
                    'warnings' => $warnings,
                    'document_signature' => $documentSignature,
                    'approval_request' => $approvalRequest,
                    'digital_signature' => $digitalSignature,
                    'verification_summary' => $this->createVerificationSummary($checks, $overallValid)
                ]
            );

        } catch (\Exception $e) {
            Log::error('Comprehensive verification error: ' . $e->getMessage());
            return $this->createVerificationResult(false, 'Verification process failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify document integrity
     */
    private function verifyDocumentIntegrity($documentSignature, $approvalRequest)
    {
        try {
            // ✅ Prioritize final_pdf_path (signed PDF with embedded signature)
            // If not available, fallback to original document_path
            $pathToVerify = null;
            $documentContent = null;

            if ($documentSignature->final_pdf_path && file_exists(Storage::disk('public')->path($documentSignature->final_pdf_path))) {
                // Use signed PDF for verification
                $pathToVerify = $documentSignature->final_pdf_path;
                $documentContent = Storage::disk('public')->get($pathToVerify);
                Log::info('Verifying signed PDF integrity', ['path' => $pathToVerify]);
            } else {
                return [
                    'status' => false,
                    'message' => 'Document Signature final PDF not found for integrity check',
                    'details' => ['file_path' => $documentSignature->final_pdf_path]
                ];
            }
            // elseif ($approvalRequest->document_path) {
            //     // Fallback to original document
            //     $pathToVerify = $approvalRequest->document_path;
            //     $documentContent = Storage::disk('public')->get($pathToVerify);
            //     Log::info('Verifying original PDF integrity', ['path' => $pathToVerify]);
            // }


            if (!$documentContent) {
                return [
                    'status' => false,
                    'message' => 'Cannot read document content',
                    'details' => ['file_path' => $pathToVerify]
                ];
            }

            // Calculate current document hash
            $currentHash = hash('sha256', $documentContent);

            // Compare with stored hash
            $storedHash = $documentSignature->document_hash;
            $hashMatch = hash_equals($storedHash, $currentHash);

            return [
                'status' => $hashMatch,
                'message' => $hashMatch ? 'Document integrity verified' : 'Document has been modified',
                'details' => [
                    'stored_hash' => $storedHash,
                    'current_hash' => $currentHash,
                    'file_size' => strlen($documentContent),
                    'verified_file' => $pathToVerify,
                    'is_signed_pdf' => $documentSignature->final_pdf_path !== null,
                    'last_modified' => Storage::disk('public')->lastModified($pathToVerify)
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Document integrity check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify CMS signature
     */
    private function verifyCMSSignature($documentSignature, $approvalRequest)
    {
        try {
            // ✅ Prioritize final_pdf_path for verification
            // Use signed PDF if available, otherwise use original document
            $pathToVerify = null;

            if ($documentSignature->final_pdf_path && file_exists(Storage::disk('public')->path($documentSignature->final_pdf_path))) {
                // Use absolute path for signed PDF
                $pathToVerify = Storage::disk('public')->path($documentSignature->final_pdf_path);
                Log::info('Verifying CMS signature with signed PDF', ['path' => $pathToVerify]);
            } else {
                // Fallback to original document (relative path)
                $pathToVerify = $approvalRequest->document_path;
                Log::info('Verifying CMS signature with original PDF', ['path' => $pathToVerify]);
            }

            $verificationResult = $this->digitalSignatureService->verifyCMSSignature(
                $pathToVerify,
                $documentSignature->cms_signature,
                $documentSignature->digitalSignature->id
                // $documentSignature->digital_signature_id
            );

            return [
                'status' => $verificationResult['is_valid'],
                'message' => $verificationResult['is_valid'] ? 'CMS signature is valid' : 'CMS signature verification failed',
                'details' => $verificationResult
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'CMS signature verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify timestamp
     */
    private function verifyTimestamp($documentSignature)
    {
        try {
            $signedAt = $documentSignature->signed_at;
            $now = now();

            // Check if signature timestamp is reasonable
            $isFutureTimestamp = $signedAt > $now;
            $isTooOldTimestamp = $signedAt < now()->subYears(10); // Arbitrary limit

            $isValid = !$isFutureTimestamp && !$isTooOldTimestamp;

            $message = 'Timestamp is valid';
            if ($isFutureTimestamp) {
                $message = 'Signature timestamp is in the future';
            } elseif ($isTooOldTimestamp) {
                $message = 'Signature timestamp is too old';
            }

            return [
                'status' => $isValid,
                'message' => $message,
                'details' => [
                    'signed_at' => $signedAt,
                    'current_time' => $now,
                    'age_in_days' => $signedAt->diffInDays($now),
                    'is_future' => $isFutureTimestamp,
                    'is_too_old' => $isTooOldTimestamp
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Timestamp verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify certificate
     */
    private function verifyCertificate($digitalSignature)
    {
        try {
            // Basic certificate validation
            $certificate = $digitalSignature->certificate;

            if (empty($certificate)) {
                return [
                    'status' => false,
                    'message' => 'Certificate not found'
                ];
            }

            // Try to parse certificate (basic validation)
            $certInfo = openssl_x509_parse($certificate);

            if (!$certInfo) {
                return [
                    'status' => false,
                    'message' => 'Certificate format is invalid'
                ];
            }

            // Check certificate validity period
            $validFrom = isset($certInfo['validFrom_time_t']) ?
                Carbon::createFromTimestamp($certInfo['validFrom_time_t']) : null;
            $validTo = isset($certInfo['validTo_time_t']) ?
                Carbon::createFromTimestamp($certInfo['validTo_time_t']) : null;

            $now = now();
            $isExpired = $validTo && $validTo < $now;
            $isNotYetValid = $validFrom && $validFrom > $now;

            $isValid = !$isExpired && !$isNotYetValid;

            return [
                'status' => $isValid,
                'message' => $isValid ? 'Certificate is valid' :
                    ($isExpired ? 'Certificate has expired' : 'Certificate is not yet valid'),
                'details' => [
                    'subject' => $certInfo['subject'] ?? 'Unknown',
                    'issuer' => $certInfo['issuer'] ?? 'Unknown',
                    'valid_from' => $validFrom,
                    'valid_to' => $validTo,
                    'is_expired' => $isExpired,
                    'is_not_yet_valid' => $isNotYetValid,
                    'serial_number' => $certInfo['serialNumber'] ?? 'Unknown'
                ]
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Certificate verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create verification summary
     */
    private function createVerificationSummary($checks, $overallValid)
    {
        $summary = [
            'overall_status' => $overallValid ? 'VALID' : 'INVALID',
            'checks_passed' => 0,
            'checks_failed' => 0,
            'total_checks' => count($checks)
        ];

        foreach ($checks as $check) {
            if ($check['status']) {
                $summary['checks_passed']++;
            } else {
                $summary['checks_failed']++;
            }
        }

        $summary['success_rate'] = $summary['total_checks'] > 0 ?
            round(($summary['checks_passed'] / $summary['total_checks']) * 100, 2) : 0;

        return $summary;
    }

    /**
     * Create verification result
     */
    private function createVerificationResult($isValid, $message, $details = [])
    {
        return [
            'is_valid' => $isValid,
            'message' => $message,
            'verified_at' => now(),
            'verification_id' => uniqid('verify_'),
            'details' => $details
        ];
    }

    /**
     * Log verification attempt
     */
    private function logVerificationAttempt($documentSignature, $verificationResult, $token = null, $startTime = null)
    {
        try {
            // Calculate verification duration if start time provided
            $durationMs = null;
            if ($startTime) {
                $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            }

            // Get previous verification count for this document
            $previousCount = SignatureVerificationLog::where('document_signature_id', $documentSignature->id)
                ->where('created_at', '<', now())
                ->count();

            // Categorize failed reason if verification failed
            $failedReason = null;
            if (!$verificationResult['is_valid']) {
                $failedReason = SignatureVerificationLog::categorizeFailedReason(
                    $verificationResult['message'] ?? 'Unknown error'
                );
            }

            // Create standardized metadata
            $customMetadata = [
                'verification_id' => $verificationResult['verification_id'],
                'message' => $verificationResult['message'],
                'verification_duration_ms' => $durationMs,
                'previous_verification_count' => $previousCount,
                'failed_reason' => $failedReason,
                // Add verification details if available
                'checks_summary' => $verificationResult['details']['verification_summary'] ?? null,
            ];

            $metadata = SignatureVerificationLog::createMetadata($customMetadata);

            $logData = [
                'document_signature_id' => $documentSignature->id,
                'approval_request_id' => $documentSignature->approval_request_id,
                'user_id' => Auth::id(),
                'verification_method' => $token ? SignatureVerificationLog::METHOD_TOKEN : SignatureVerificationLog::METHOD_ID,
                'verification_token_hash' => $token ? hash('sha256', $token) : null,
                'is_valid' => $verificationResult['is_valid'],
                'result_status' => $this->determineResultStatus($verificationResult),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->headers->get('referer'),
                'metadata' => $metadata,
                'verified_at' => now()
            ];

            // Log to application log
            Log::info('Document signature verification attempt', [
                'document_signature_id' => $documentSignature->id,
                'is_valid' => $verificationResult['is_valid'],
                'duration_ms' => $durationMs,
                'user_id' => Auth::id() ?? 'anonymous',
            ]);

            // Save to verification_logs table
            SignatureVerificationLog::create($logData);

        } catch (\Exception $e) {
            Log::error('Failed to log verification attempt: ' . $e->getMessage());
        }
    }

    /**
     * Determine result status from verification result
     */
    private function determineResultStatus($verificationResult)
    {
        if ($verificationResult['is_valid']) {
            return SignatureVerificationLog::STATUS_SUCCESS;
        }

        // Check message for specific error types
        $message = strtolower($verificationResult['message'] ?? '');

        if (strpos($message, 'expired') !== false || strpos($message, 'kadaluarsa') !== false) {
            return SignatureVerificationLog::STATUS_EXPIRED;
        }
        if (strpos($message, 'not found') !== false || strpos($message, 'tidak ditemukan') !== false) {
            return SignatureVerificationLog::STATUS_NOT_FOUND;
        }
        if (strpos($message, 'invalid') !== false || strpos($message, 'tidak valid') !== false) {
            return SignatureVerificationLog::STATUS_INVALID;
        }

        return SignatureVerificationLog::STATUS_FAILED;
    }

    /**
     * Get verification statistics
     */
    //! DIPAKAI DI CONTROLLER DigitalSignatureController method adminDashboard
    public function getVerificationStatistics($period = 30)
    {
        try {
            $cacheKey = "verification_stats_{$period}";

            return Cache::remember($cacheKey, 3600, function () use ($period) {
                $startDate = now()->subDays($period);

                // Since we don't have a verification_logs table yet,
                // we'll calculate based on DocumentSignature data
                $totalSignatures = DocumentSignature::where('created_at', '>=', $startDate)->count();
                $verifiedSignatures = DocumentSignature::where('signature_status', DocumentSignature::STATUS_VERIFIED)
                    ->where('created_at', '>=', $startDate)->count();

                return [
                    'period_days' => $period,
                    'total_signatures' => $totalSignatures,
                    'verified_signatures' => $verifiedSignatures,
                    'verification_rate' => $totalSignatures > 0 ?
                        round(($verifiedSignatures / $totalSignatures) * 100, 2) : 0,
                    'period_start' => $startDate,
                    'period_end' => now()
                ];
            });

        } catch (\Exception $e) {
            Log::error('Failed to get verification statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}
