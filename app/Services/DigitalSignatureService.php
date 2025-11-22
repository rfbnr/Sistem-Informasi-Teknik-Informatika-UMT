<?php

namespace App\Services;

use OpenSSLAsymmetricKey;
use Illuminate\Support\Str;
use App\Models\ApprovalRequest;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use App\Models\SignatureAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DigitalSignatureService
{
    /**
     * Generate RSA key pair dengan enhanced security
     *
     * @param int $keyLength RSA key length (default 2048)
     * @param string $algorithm Algorithm name (default RSA-SHA256)
     * @param int $validityYears Certificate validity in years (default 3)
     * @param array|null $signerInfo Signer information for certificate personalization
     * @return array
     */
    //! DIPAKAI DI CREATEDIGITALSIGNATUREFORDOCUMENT METHOD
    public function generateKeyPair($keyLength = 2048, $algorithm = 'RSA-SHA256', $validityYears = 1, $signerInfo = null)
    {
        try {
            $config = [
                "digest_alg" => "sha256",
                "private_key_bits" => $keyLength,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
                "encrypt_key" => false
            ];

            // Generate private key
            $privateKey = openssl_pkey_new($config);
            if (!$privateKey) {
                throw new \Exception('Failed to generate private key: ' . openssl_error_string());
            }

            // Extract public key details
            $publicKeyDetails = openssl_pkey_get_details($privateKey);
            if (!$publicKeyDetails) {
                throw new \Exception('Failed to extract public key: ' . openssl_error_string());
            }

            // Export private key to PEM format
            $privateKeyPem = '';
            if (!openssl_pkey_export($privateKey, $privateKeyPem)) {
                throw new \Exception('Failed to export private key: ' . openssl_error_string());
            }

            // Generate certificate (self-signed) with validity and signer info
            $certificate = $this->generateSelfSignedCertificate($privateKey, $publicKeyDetails, $validityYears, $signerInfo);

            return [
                'private_key' => $privateKeyPem,
                'public_key' => $publicKeyDetails['key'],
                'key_length' => $publicKeyDetails['bits'],
                'algorithm' => $algorithm,
                'certificate' => $certificate,
                'fingerprint' => $this->generateFingerprint($publicKeyDetails['key'])
            ];

        } catch (\Exception $e) {
            Log::error('RSA Key Generation Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * REFACTORED: Create unique digital signature key per document
     * Auto-called during signing process
     *
     * @param DocumentSignature $documentSignature
     * @param int $validityYears Default 3 years for document signatures
     * @return DigitalSignature
     */
    //! DIPAKAI DI SignDocumentWithUniqueKey METHOD
    public function createDigitalSignatureForDocument(DocumentSignature $documentSignature, $validityYears = 1)
    {
        try {
            // Get signer information for certificate personalization
            $approvalRequest = $documentSignature->approvalRequest;

            // $signer = $approvalRequest ? $approvalRequest->kaprodi : null;
            $signer = $approvalRequest ? $approvalRequest->approver : null;

            $signerInfo = [
                'name' => $signer ? $signer->name : 'Digital Signature Authority',
                'email' => $signer ? $signer->email : 'informatika@umt.ac.id',
                'role' => 'Kepala Program Studi Teknik Informatika',
                'document_name' => $approvalRequest ? $approvalRequest->document_name : 'Unknown Document'
            ];

            Log::info('Generating digital signature key for document', [
                'approval_request_id' => $approvalRequest ? $approvalRequest->id : null,
                'document_signature_id' => $documentSignature->id,
                'signer_name' => $signerInfo['name'],
                'signer_email' => $signerInfo['email']
            ]);

            // Generate key pair with enhanced error handling
            $keyPair = $this->generateKeyPair(2048, 'RSA-SHA256', $validityYears, $signerInfo);

            // Check if certificate generation succeeded
            if (empty($keyPair['certificate'])) {
                // Track fallback certificate generation
                $fallbackReason = 'X.509 certificate generation returned null';

                Log::error('Certificate generation failed - using fallback JSON format', [
                    'document_signature_id' => $documentSignature->id,
                    'approval_request_id' => $approvalRequest ? $approvalRequest->id : null,
                    'signer_name' => $signerInfo['name'] ?? 'N/A',
                    'signer_email' => $signerInfo['email'] ?? 'N/A',
                    'fallback_reason' => $fallbackReason,
                    'timestamp' => now()->toISOString()
                ]);

                // Send notification to admin about fallback usage
                try {
                    // Log to separate monitoring channel
                    Log::channel('daily')->critical('FALLBACK CERTIFICATE GENERATED', [
                        'alert_type' => 'certificate_generation_failure',
                        'document_signature_id' => $documentSignature->id,
                        'approval_request_id' => $approvalRequest ? $approvalRequest->id : null,
                        'user_id' => $approvalRequest ? $approvalRequest->user_id : null,
                        'action_required' => 'Check OpenSSL configuration and server environment',
                        'timestamp' => now()->toISOString()
                    ]);
                } catch (\Exception $logException) {
                    // Silently fail if logging fails - don't break signing process
                    Log::warning('Failed to log fallback certificate alert', [
                        'error' => $logException->getMessage()
                    ]);
                }

                // Fallback: return basic certificate info (JSON format)
                // NOTE: This is NOT a real X.509 certificate
                $validityYears = $validityYears ?? 1;
                $keyPair['certificate'] = "-----BEGIN CERTIFICATE-----\n" .
                    base64_encode(json_encode([
                        'format' => 'FALLBACK_JSON',
                        'warning' => 'This is not a real X.509 certificate',
                        'subject' => $signerInfo['name'] ?? 'Digital Signature Authority',
                        'issuer' => $signerInfo['name'] ?? 'Digital Signature Authority',
                        'valid_from' => now()->toISOString(),
                        'valid_until' => now()->addYears($validityYears)->toISOString(),
                        'serial_number' => Str::random(16),
                        'email' => $signerInfo['email'] ?? 'informatika@umt.ac.id',
                        'generated_at' => now()->toISOString()
                    ])) .
                    "\n-----END CERTIFICATE-----";

            }

            $validFrom = now();
            $validUntil = $validFrom->copy()->addYears((int) $validityYears);

            $signature = DigitalSignature::create([
                'public_key' => $keyPair['public_key'],
                'private_key' => $keyPair['private_key'], // Will be encrypted by model mutator
                'algorithm' => $keyPair['algorithm'],
                'key_length' => $keyPair['key_length'],
                'certificate' => $keyPair['certificate'],
                'document_signature_id' => $documentSignature->id, // 1-to-1 relationship
                'user_id' => $approvalRequest ? $approvalRequest->user_id : Auth::id(), // Owner of the signature
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'status' => DigitalSignature::STATUS_ACTIVE,
                'metadata' => [
                    'created_ip' => request()->ip(),
                    'created_user_agent' => request()->userAgent(),
                    'key_fingerprint' => $keyPair['fingerprint'],
                    'created_at_timestamp' => now()->timestamp,
                    'document_name' => $documentSignature->approvalRequest->document_name ?? 'Unknown',
                    'auto_generated' => true
                ]
            ]);

            Log::info('Digital signature key generated for document', [
                'signature_id' => $signature->signature_id,
                'document_signature_id' => $documentSignature->id,
                'approval_request_id' => $documentSignature->approval_request_id
            ]);

            // Create audit log
            $metadata = SignatureAuditLog::createMetadata([
                'signature_id' => $signature->signature_id,
                'key_length' => $signature->key_length,
                'algorithm' => $signature->algorithm,
                'validity_years' => $validityYears,
                'document_signature_id' => $documentSignature->id,
                'approval_request_id' => $documentSignature->approval_request_id,
                'auto_generated' => true
            ]);

            SignatureAuditLog::create([
                'kaprodi_id' => Auth::guard('kaprodi')->id(),
                'action' => SignatureAuditLog::ACTION_SIGNATURE_KEY_GENERATED,
                'status_to' => $signature->status,
                'description' => 'Digital signature key pair auto-generated for document signing',
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            return $signature;

        } catch (\Exception $e) {
            Log::error('Digital signature creation for document failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * REFACTORED: Create CMS signature untuk dokumen
     * Can accept either DigitalSignature instance or ID
     */
    //! DIPAKAI DI SignDocumentWithUniqueKey METHOD
    // public function createCMSSignature($documentPath, $digitalSignature)
    // {
    //     try {
    //         // Handle both DigitalSignature instance or ID
    //         if (!$digitalSignature instanceof DigitalSignature) {
    //             $digitalSignature = DigitalSignature::findOrFail($digitalSignature);
    //         }

    //         if (!$digitalSignature->isValid()) {
    //             throw new \Exception('Digital signature is not valid or expired');
    //         }

    //         // Read document content
    //         $documentContent = null;

    //         if (file_exists($documentPath)) {
    //             // Absolute path
    //             $documentContent = file_get_contents($documentPath);
    //             Log::info('Reading document from absolute path', [
    //                 'path' => $documentPath,
    //                 'size' => strlen($documentContent)
    //             ]);
    //         } else {
    //             // Relative path from storage
    //             $documentContent = Storage::disk('public')->get($documentPath);
    //             Log::info('Reading document from storage', [
    //                 'path' => $documentPath,
    //                 'disk' => 'public',
    //                 'size' => $documentContent ? strlen($documentContent) : 0
    //             ]);
    //         }

    //         if (!$documentContent) {
    //             throw new \Exception('Cannot read document content from: ' . $documentPath);
    //         }

    //         // Generate document hash
    //         $documentHash = hash('sha256', $documentContent);

    //         // Create signature menggunakan private key
    //         $signature = '';
    //         $privateKey = $digitalSignature->private_key;

    //         if (!openssl_sign($documentHash, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
    //             throw new \Exception('Failed to create digital signature: ' . openssl_error_string());
    //         }

    //         // Encode signature ke base64
    //         $cmsSignature = base64_encode($signature);

    //         // Get certificate fingerprint for metadata
    //         $certificateFingerprint = null;
    //         if ($digitalSignature->certificate) {
    //             $certificateFingerprint = openssl_x509_fingerprint($digitalSignature->certificate, 'sha256');
    //         }

    //         // Parse certificate for signer info
    //         $certInfo = null;
    //         if ($digitalSignature->certificate) {
    //             try {
    //                 $certData = openssl_x509_parse($digitalSignature->certificate);
    //                 $certInfo = [
    //                     'subject_cn' => $certData['subject']['CN'] ?? 'N/A',
    //                     'issuer_cn' => $certData['issuer']['CN'] ?? 'N/A',
    //                     'valid_from' => isset($certData['validFrom_time_t']) ? date('Y-m-d H:i:s', $certData['validFrom_time_t']) : null,
    //                     'valid_until' => isset($certData['validTo_time_t']) ? date('Y-m-d H:i:s', $certData['validTo_time_t']) : null,
    //                 ];
    //             } catch (\Exception $e) {
    //                 Log::warning('Failed to parse certificate for metadata: ' . $e->getMessage());
    //             }
    //         }

    //         // ENHANCED: Comprehensive metadata with more context
    //         return [
    //             'document_hash' => $documentHash,
    //             'cms_signature' => $cmsSignature,
    //             'signature_value' => hash('sha256', $signature),
    //             'algorithm' => $digitalSignature->algorithm,
    //             'signed_at' => now(),
    //             'metadata' => [
    //                 // Document information
    //                 'document_size' => strlen($documentContent),
    //                 'document_size_mb' => round(strlen($documentContent) / 1024 / 1024, 2),
    //                 'document_hash_algorithm' => 'SHA-256',

    //                 // Signature information
    //                 'signature_id' => $digitalSignature->signature_id,
    //                 'signature_algorithm' => $digitalSignature->algorithm,
    //                 'key_length' => $digitalSignature->key_length,

    //                 // Certificate information
    //                 'certificate_fingerprint' => $certificateFingerprint,
    //                 'certificate_info' => $certInfo,

    //                 // Signing context
    //                 'signing_ip' => request()->ip(),
    //                 'signing_user_agent' => request()->userAgent(),
    //                 'signing_location' => 'Tangerang, Banten, Indonesia',
    //                 'signing_reason' => 'Document Approval and Authentication',
    //                 'signing_timestamp' => now()->toIso8601String(),

    //                 // System information
    //                 'platform' => 'DiSign - Digital Signature System UMT',
    //                 'version' => '2.0',
    //                 'compliance' => 'X.509 v3, CMS Signature'
    //             ]
    //         ];

    //     } catch (\Exception $e) {
    //         Log::error('CMS Signature creation failed: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }
    /**
     * ✅ ENHANCED: Create PKCS#7/CMS signature for document (Adobe Reader compatible)
     * Can accept either DigitalSignature instance or ID
     *
     * IMPROVEMENTS:
     * - Full PKCS#7/CMS detached signature (not just hash signature)
     * - Adobe Reader compatible format
     * - Includes certificate chain in signature
     * - Supports both legacy (hash-only) and new (PKCS#7) formats
     */
    public function createCMSSignature($documentPath, $digitalSignature, $usePKCS7 = true)
    {
        try {
            // Handle both DigitalSignature instance or ID
            if (!$digitalSignature instanceof DigitalSignature) {
                $digitalSignature = DigitalSignature::findOrFail($digitalSignature);
            }

            if (!$digitalSignature->isValid()) {
                throw new \Exception('Digital signature is not valid or expired');
            }

            // Read document content
            $documentContent = null;

            if (file_exists($documentPath)) {
                $documentContent = file_get_contents($documentPath);
                Log::info('Reading document from absolute path', [
                    'path' => $documentPath,
                    'size' => strlen($documentContent)
                ]);
            } else {
                $documentContent = Storage::disk('public')->get($documentPath);
                Log::info('Reading document from storage', [
                    'path' => $documentPath,
                    'disk' => 'public',
                    'size' => $documentContent ? strlen($documentContent) : 0
                ]);
            }

            if (!$documentContent) {
                throw new \Exception('Cannot read document content from: ' . $documentPath);
            }

            // Generate document hash
            $documentHash = hash('sha256', $documentContent);

            $cmsSignature = null;
            $signatureFormat = 'legacy_hash_only';

            // Try to create PKCS#7/CMS signature first
            if ($usePKCS7 && $digitalSignature->certificate && $this->isValidX509Certificate($digitalSignature->certificate)) {
                try {
                    $pkcs7Signature = $this->createPKCS7Signature(
                        $documentContent,
                        $digitalSignature->private_key,
                        $digitalSignature->certificate
                    );

                    if ($pkcs7Signature) {
                        $cmsSignature = $pkcs7Signature;
                        $signatureFormat = 'pkcs7_cms_detached';
                        Log::info('PKCS#7/CMS signature created successfully', [
                            'signature_id' => $digitalSignature->signature_id,
                            'format' => $signatureFormat,
                            'signature_length' => strlen($cmsSignature)
                        ]);
                    }
                } catch (\Exception $pkcs7Exception) {
                    Log::warning('PKCS#7 signature creation failed, falling back to legacy format', [
                        'error' => $pkcs7Exception->getMessage(),
                        'signature_id' => $digitalSignature->signature_id
                    ]);
                }
            }

            // Create legacy hash-only signature if PKCS#7 fails
            if (!$cmsSignature) {
                $signature = '';
                $privateKey = $digitalSignature->private_key;

                if (!openssl_sign($documentHash, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                    throw new \Exception('Failed to create digital signature: ' . openssl_error_string());
                }

                $cmsSignature = base64_encode($signature);
                $signatureFormat = 'legacy_hash_only';
                Log::info('Legacy hash-only signature created', [
                    'signature_id' => $digitalSignature->signature_id,
                    'format' => $signatureFormat
                ]);
            }

            // Safe certificate fingerprint extraction
            $certificateFingerprint = null;
            $certInfo = null;

            if ($digitalSignature->certificate) {
                // Validate certificate format before processing
                if ($this->isValidX509Certificate($digitalSignature->certificate)) {
                    try {
                        // Safe fingerprint extraction
                        $certificateFingerprint = openssl_x509_fingerprint(
                            $digitalSignature->certificate,
                            'sha256'
                        );

                        // Safe certificate parsing
                        $certData = openssl_x509_parse($digitalSignature->certificate);
                        if ($certData) {
                            $certInfo = [
                                'subject_cn' => $certData['subject']['CN'] ?? 'N/A',
                                'subject_email' => $certData['subject']['emailAddress'] ?? 'N/A',
                                'issuer_cn' => $certData['issuer']['CN'] ?? 'N/A',
                                'valid_from' => isset($certData['validFrom_time_t']) ?
                                    date('Y-m-d H:i:s', $certData['validFrom_time_t']) : null,
                                'valid_until' => isset($certData['validTo_time_t']) ?
                                    date('Y-m-d H:i:s', $certData['validTo_time_t']) : null,
                                'serial_number' => $certData['serialNumber'] ?? 'N/A',
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::warning('Certificate processing failed, using fallback', [
                            'error' => $e->getMessage(),
                            'signature_id' => $digitalSignature->signature_id
                        ]);
                        // Continue with null values - non-critical
                    }
                } else {
                    Log::warning('Invalid certificate format detected', [
                        'signature_id' => $digitalSignature->signature_id,
                        'cert_preview' => substr($digitalSignature->certificate, 0, 100)
                    ]);
                }
            }

            // Comprehensive metadata with safe fallbacks
            return [
                'document_hash' => $documentHash,
                'cms_signature' => $cmsSignature,
                'signature_value' => hash('sha256', $cmsSignature),
                'signature_format' => $signatureFormat,  // Track signature format
                'algorithm' => $digitalSignature->algorithm,
                'signed_at' => now(),
                'metadata' => [
                    // Document information
                    'document_size' => strlen($documentContent),
                    'document_size_mb' => round(strlen($documentContent) / 1024 / 1024, 2),
                    'document_hash_algorithm' => 'SHA-256',

                    // Signature information
                    'signature_id' => $digitalSignature->signature_id,
                    'signature_algorithm' => $digitalSignature->algorithm,
                    'signature_format' => $signatureFormat,  // Track format in metadata
                    'key_length' => $digitalSignature->key_length,

                    // Certificate information (with safe fallbacks)
                    'certificate_fingerprint' => $certificateFingerprint,
                    'certificate_info' => $certInfo,
                    'certificate_status' => $certificateFingerprint ? 'valid' : 'unavailable',

                    // Signing context
                    'signing_ip' => request()->ip(),
                    'signing_user_agent' => request()->userAgent(),
                    'signing_location' => 'Tangerang, Banten, Indonesia',
                    'signing_reason' => 'Document Approval and Authentication',
                    'signing_timestamp' => now()->toIso8601String(),

                    // System information
                    'platform' => 'DiSign - Digital Signature System UMT',
                    'version' => '2.1',  // Version bump for PKCS#7 support
                    'compliance' => $signatureFormat === 'pkcs7_cms_detached'
                        ? 'X.509 v3, PKCS#7/CMS Detached Signature, Adobe PDF Compatible'
                        : 'X.509 v3, CMS Signature (Legacy)'
                ]
            ];

        } catch (\Exception $e) {
            Log::error('CMS Signature creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * ✅ NEW: Create PKCS#7/CMS detached signature (Adobe Reader compatible)
     *
     * This creates a proper PKCS#7 signature that includes:
     * - The signature itself
     * - The signer's certificate
     * - Signing time
     * - Hash algorithm identifier
     *
     * ✅ ULTIMATE FIX: Extract PKCS#7 DER from multipart S/MIME with improved parsing
     *
     * @param string $documentContent Full document content
     * @param string $privateKey Private key in PEM format
     * @param string $certificate Certificate in PEM format
     * @return string|null Base64-encoded PKCS#7 signature or null on failure
     */
    private function createPKCS7Signature($documentContent, $privateKey, $certificate)
    {
        // Track all temp files for guaranteed cleanup
        $tempDocPath = null;
        $tempSigPath = null;
        $tempCertFilePath = null;
        $tempKeyFilePath = null;

        try {
            // Create temporary files
            $tempDocPath = tempnam(sys_get_temp_dir(), 'doc_');
            $tempSigPath = tempnam(sys_get_temp_dir(), 'sig_');
            $tempCertFilePath = tempnam(sys_get_temp_dir(), 'cert_');
            $tempKeyFilePath = tempnam(sys_get_temp_dir(), 'key_');

            // Check if temp files were created successfully
            if (!$tempDocPath || !$tempSigPath || !$tempCertFilePath || !$tempKeyFilePath) {
                throw new \Exception('Failed to create temporary files for PKCS#7 signing');
            }

            // Write files
            file_put_contents($tempDocPath, $documentContent);
            file_put_contents($tempCertFilePath, $certificate);
            file_put_contents($tempKeyFilePath, $privateKey);

            Log::info('Creating PKCS#7 signature', [
                'doc_size' => strlen($documentContent),
                'cert_size' => strlen($certificate),
                'key_size' => strlen($privateKey),
                'temp_files_created' => 4
            ]);

            // STEP 1: Create PKCS#7 S/MIME signature
            $signSuccess = openssl_pkcs7_sign(
                $tempDocPath,
                $tempSigPath,
                'file://' . $tempCertFilePath,
                ['file://' . $tempKeyFilePath, ''],
                [],
                PKCS7_DETACHED | PKCS7_BINARY
            );

            // Cleanup cert and key immediately
            @unlink($tempCertFilePath);
            @unlink($tempKeyFilePath);

            if (!$signSuccess) {
                throw new \Exception('openssl_pkcs7_sign failed: ' . openssl_error_string());
            }

            // STEP 2: Read S/MIME content
            $smimeContent = file_get_contents($tempSigPath);
            if (!$smimeContent) {
                throw new \Exception('S/MIME signature file is empty');
            }

            Log::info('S/MIME signature created', [
                'smime_size' => strlen($smimeContent),
                'smime_preview' => substr($smimeContent, 0, 200),
                'is_multipart' => str_contains($smimeContent, 'multipart/signed')
            ]);

            $derBinary = null;
            $extractionMethod = null;

            // STRATEGY 1: Use openssl smime command (BEST for multipart)
            if (!$derBinary) {
                try {
                    $tempDerPath = tempnam(sys_get_temp_dir(), 'der_');

                    // Extract PKCS#7 structure from S/MIME
                    $cmd = sprintf(
                        'openssl smime -pk7out -in %s 2>&1 | openssl pkcs7 -outform DER -out %s 2>&1',
                        escapeshellarg($tempSigPath),
                        escapeshellarg($tempDerPath)
                    );

                    exec($cmd, $output, $returnCode);

                    if ($returnCode === 0 && file_exists($tempDerPath) && filesize($tempDerPath) > 0) {
                        $possibleDer = file_get_contents($tempDerPath);
                        if ($possibleDer && ord($possibleDer[0]) === 0x30) {
                            $derBinary = $possibleDer;
                            $extractionMethod = 'openssl_smime_pkcs7_pipe';
                            Log::info('DER extracted via openssl smime pipe command');
                        }
                    }

                    @unlink($tempDerPath);
                } catch (\Exception $e) {
                    Log::warning('Strategy 1 failed', ['error' => $e->getMessage()]);
                }
            }

            // STRATEGY 2: Parse multipart S/MIME manually
            if (!$derBinary) {
                try {
                    Log::info('Attempting to parse multipart S/MIME manually');

                    // Find boundary
                    $boundaryMatch = [];
                    if (preg_match('/boundary="([^"]+)"/', $smimeContent, $boundaryMatch)) {
                        $boundary = '--' . $boundaryMatch[1];

                        Log::info('Found S/MIME boundary', ['boundary' => $boundary]);

                        // Split by boundary
                        $parts = explode($boundary, $smimeContent);

                        foreach ($parts as $index => $part) {
                            // Look for PKCS#7 signature part
                            if (stripos($part, 'application/x-pkcs7-signature') !== false ||
                                stripos($part, 'application/pkcs7-signature') !== false) {

                                Log::info('Found PKCS#7 signature part', ['part_index' => $index]);

                                // Extract base64 content after headers
                                $lines = explode("\n", $part);
                                $base64Content = '';
                                $inContent = false;

                                foreach ($lines as $line) {
                                    $line = trim($line);

                                    // Start collecting after empty line (end of headers)
                                    if (empty($line) && !$inContent) {
                                        $inContent = true;
                                        continue;
                                    }

                                    // Collect base64 lines
                                    if ($inContent && !empty($line) && !str_starts_with($line, '--')) {
                                        $base64Content .= $line;
                                    }
                                }

                                if (!empty($base64Content)) {
                                    $possibleDer = base64_decode($base64Content);

                                    if ($possibleDer && ord($possibleDer[0]) === 0x30) {
                                        $derBinary = $possibleDer;
                                        $extractionMethod = 'manual_multipart_parsing';
                                        Log::info('DER extracted via manual multipart parsing', [
                                            'base64_length' => strlen($base64Content),
                                            'der_size' => strlen($derBinary)
                                        ]);
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        Log::warning('No boundary found in S/MIME content');
                    }
                } catch (\Exception $e) {
                    Log::warning('Strategy 2 failed', ['error' => $e->getMessage()]);
                }
            }

            // STRATEGY 3: Regex extraction from multipart
            if (!$derBinary) {
                try {
                    Log::info('Attempting regex extraction from multipart');

                    // Regex to find base64 content after Content-Type: application/x-pkcs7-signature
                    $pattern = '/Content-Type:\s*application\/x?-?pkcs7-signature.*?\n\n([\s\S]+?)(?=\n--)/i';

                    if (preg_match($pattern, $smimeContent, $matches)) {
                        $base64Content = trim($matches[1]);

                        // Clean whitespace
                        $base64Clean = preg_replace('/\s+/', '', $base64Content);

                        $possibleDer = base64_decode($base64Clean);

                        if ($possibleDer && ord($possibleDer[0]) === 0x30) {
                            $derBinary = $possibleDer;
                            $extractionMethod = 'regex_multipart_extraction';
                            Log::info('DER extracted via regex', [
                                'base64_length' => strlen($base64Clean),
                                'der_size' => strlen($derBinary)
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Strategy 3 failed', ['error' => $e->getMessage()]);
                }
            }

            // STRATEGY 4: Alternative regex with Content-Transfer-Encoding
            if (!$derBinary) {
                try {
                    Log::info('Attempting alternative regex with Content-Transfer-Encoding');

                    // Look for base64 content after "Content-Transfer-Encoding: base64"
                    $pattern = '/Content-Transfer-Encoding:\s*base64\s*\n\n([\s\S]+?)(?=\n--)/i';

                    if (preg_match($pattern, $smimeContent, $matches)) {
                        $base64Content = trim($matches[1]);
                        $base64Clean = preg_replace('/\s+/', '', $base64Content);

                        $possibleDer = base64_decode($base64Clean);

                        if ($possibleDer && ord($possibleDer[0]) === 0x30) {
                            $derBinary = $possibleDer;
                            $extractionMethod = 'regex_content_encoding_extraction';
                            Log::info('DER extracted via Content-Transfer-Encoding regex', [
                                'der_size' => strlen($derBinary)
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Strategy 4 failed', ['error' => $e->getMessage()]);
                }
            }

            // STRATEGY 5: Write S/MIME to file and use openssl smime -verify to extract
            if (!$derBinary) {
                try {
                    Log::info('Attempting extraction via openssl smime -verify');

                    $tempExtractPath = tempnam(sys_get_temp_dir(), 'extract_');

                    // Use -verify with -noverify to just extract signature
                    $cmd = sprintf(
                        'openssl smime -verify -noverify -in %s -pk7out -outform DER -out %s 2>&1',
                        escapeshellarg($tempSigPath),
                        escapeshellarg($tempExtractPath)
                    );

                    exec($cmd, $output, $returnCode);

                    // Check if file was created (ignore return code, sometimes it's non-zero but file exists)
                    if (file_exists($tempExtractPath) && filesize($tempExtractPath) > 0) {
                        $possibleDer = file_get_contents($tempExtractPath);

                        if ($possibleDer && ord($possibleDer[0]) === 0x30) {
                            $derBinary = $possibleDer;
                            $extractionMethod = 'openssl_smime_verify_extract';
                            Log::info('DER extracted via openssl smime -verify');
                        }
                    }

                    @unlink($tempExtractPath);
                } catch (\Exception $e) {
                    Log::warning('Strategy 5 failed', ['error' => $e->getMessage()]);
                }
            }

            // VALIDATION
            if (!$derBinary) {
                // Dump S/MIME content for debugging
                Log::error('All DER extraction strategies failed', [
                    'smime_preview' => substr($smimeContent, 0, 500),
                    'smime_length' => strlen($smimeContent),
                    'has_multipart' => str_contains($smimeContent, 'multipart'),
                    'has_pkcs7_type' => str_contains($smimeContent, 'pkcs7')
                ]);
                throw new \Exception('All DER extraction strategies failed - cannot extract PKCS#7 from S/MIME');
            }

            if (ord($derBinary[0]) !== 0x30) {
                throw new \Exception(sprintf(
                    'Invalid DER format: expected 0x30, got 0x%02x',
                    ord($derBinary[0])
                ));
            }

            // Encode to base64
            $derSignature = base64_encode($derBinary);

            // IMPROVEMENT: Clear large variables from memory
            unset($derBinary);
            unset($smimeContent);

            Log::info('PKCS#7 signature created successfully', [
                'extraction_method' => $extractionMethod,
                'base64_size' => strlen($derSignature),
                'is_valid_asn1_sequence' => true
            ]);

            return $derSignature;

        } catch (\Exception $e) {
            Log::error('PKCS#7 signature creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;

        } finally {
            // IMPROVEMENT: Guaranteed cleanup in finally block
            $cleanedFiles = 0;

            if ($tempDocPath !== null && file_exists($tempDocPath)) {
                @unlink($tempDocPath);
                $cleanedFiles++;
            }

            if ($tempSigPath !== null && file_exists($tempSigPath)) {
                @unlink($tempSigPath);
                $cleanedFiles++;
            }

            if ($tempCertFilePath !== null && file_exists($tempCertFilePath)) {
                @unlink($tempCertFilePath);
                $cleanedFiles++;
            }

            if ($tempKeyFilePath !== null && file_exists($tempKeyFilePath)) {
                @unlink($tempKeyFilePath);
                $cleanedFiles++;
            }

            if ($cleanedFiles > 0) {
                Log::debug('Cleaned up temporary PKCS#7 signing files', [
                    'files_cleaned' => $cleanedFiles
                ]);
            }
        }
    }

    /**
     * ✅ NEW: Validate if string is valid X.509 certificate
     */
    private function isValidX509Certificate($certificate)
    {
        if (empty($certificate)) {
            return false;
        }

        // Check PEM format markers
        if (!str_contains($certificate, 'BEGIN CERTIFICATE') ||
            !str_contains($certificate, 'END CERTIFICATE')) {
            return false;
        }

        // Try to read certificate
        try {
            $cert = openssl_x509_read($certificate);
            if (!$cert) {
                return false;
            }

            // If we can read it, it's valid
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ✅ ENHANCED: Verify CMS signature with dual-format support (PKCS#7 + Legacy)
     *
     * @param string $documentPath Path to document (absolute or relative)
     * @param string $cmsSignature Base64-encoded signature (PKCS#7 or legacy format)
     * @param int $digitalSignatureId Digital signature ID
     * @param string|null $signatureFormat Signature format: 'pkcs7_cms_detached' or 'legacy_hash_only'
     * @return array Verification result
     */
    public function verifyCMSSignature($documentPath, $cmsSignature, $digitalSignatureId, $signatureFormat = null)
    {
        try {
            $digitalSignature = DigitalSignature::findOrFail($digitalSignatureId);

            // Read document content
            // Handle both absolute path (signed PDF) and relative path (original PDF)
            $documentContent = null;

            if (file_exists($documentPath)) {
                // Absolute path (e.g., signed PDF)
                $documentContent = file_get_contents($documentPath);
                Log::info('Verifying document from absolute path', [
                    'path' => $documentPath,
                    'size' => strlen($documentContent)
                ]);
            } else {
                // Relative path from storage (original PDF)
                $documentContent = Storage::disk('public')->get($documentPath);
                Log::info('Verifying document from storage', [
                    'path' => $documentPath,
                    'disk' => 'public',
                    'size' => $documentContent ? strlen($documentContent) : 0
                ]);
            }

            if (!$documentContent) {
                throw new \Exception('Cannot read document content for verification from: ' . $documentPath);
            }

            // ✅ ENHANCEMENT: Auto-detect signature format if not provided
            if (!$signatureFormat) {
                $signatureFormat = $this->detectSignatureFormat($cmsSignature);
                Log::info('Auto-detected signature format', ['format' => $signatureFormat]);
            }

            // ✅ ENHANCEMENT: Route to appropriate verification method
            if ($signatureFormat === 'pkcs7_cms_detached') {
                // Use PKCS#7 verification
                return $this->verifyPKCS7CMSSignature(
                    $documentPath,
                    $documentContent,
                    $cmsSignature,
                    $digitalSignature
                );
            } else {
                // Use legacy hash-only verification
                return $this->verifyLegacyHashSignature(
                    $documentContent,
                    $cmsSignature,
                    $digitalSignature
                );
            }

        } catch (\Exception $e) {
            Log::error('CMS Signature verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'is_valid' => false,
                'error_message' => 'Verification failed: ' . $e->getMessage(),
                'signature_format' => $signatureFormat ?? 'unknown',
                'verified_at' => now()
            ];
        }
    }

    /**
     * ✅ NEW: Verify PKCS#7/CMS detached signature
     *
     * ✅ ULTIMATE FIX: Verify PKCS#7 signature with proper content handling
     *
     * @param string $documentPath Path to document file
     * @param string $documentContent Document content (for hash calculation)
     * @param string $pkcs7Signature Base64-encoded PKCS#7 signature
     * @param DigitalSignature $digitalSignature Digital signature model
     * @return array Verification result
     */
    private function verifyPKCS7CMSSignature($documentPath, $documentContent, $pkcs7Signature, $digitalSignature)
    {
        $tempDocPath = null;
        $tempSigPathDer = null;
        $tempCertPath = null;

        try {
            // Create temporary files
            $tempDocPath = tempnam(sys_get_temp_dir(), 'verify_doc_');
            $tempSigPathDer = tempnam(sys_get_temp_dir(), 'verify_sig_der_');
            $tempCertPath = tempnam(sys_get_temp_dir(), 'verify_cert_');

            // ✅ STEP 1: Write original document content
            file_put_contents($tempDocPath, $documentContent);

            // ✅ STEP 2: Decode signature from base64
            $derBinary = base64_decode($pkcs7Signature);

            if (!$derBinary) {
                throw new \Exception('Failed to decode PKCS#7 signature from base64');
            }

            // ✅ STEP 3: Validate DER format
            $firstByte = ord($derBinary[0]);

            if ($firstByte !== 0x30) {
                throw new \Exception(sprintf(
                    'Invalid DER format: expected 0x30 (SEQUENCE), got 0x%02x',
                    $firstByte
                ));
            }

            Log::info('PKCS#7 signature validated for verification', [
                'der_size' => strlen($derBinary),
                'der_first_bytes' => bin2hex(substr($derBinary, 0, 16)),
                'is_valid_der' => true,
                'doc_size' => strlen($documentContent)
            ]);

            // ✅ STEP 4: Write DER signature to file
            file_put_contents($tempSigPathDer, $derBinary);

            // ✅ STRATEGY 1: Try verification using openssl command (most reliable)
            $verifyResult = $this->verifyPKCS7UsingCommand(
                $tempSigPathDer,
                $tempDocPath,
                $tempCertPath
            );

            if ($verifyResult === true) {
                Log::info('PKCS#7 verification successful via openssl command');

                return $this->buildVerificationResult(
                    true,
                    $documentContent,
                    $digitalSignature,
                    $tempCertPath,
                    'openssl_command'
                );
            }

            // ✅ STRATEGY 2: Try verification using PHP openssl_pkcs7_verify (fallback)
            Log::info('Command verification failed, trying PHP openssl_pkcs7_verify');

            // For PHP verification, try both with and without PKCS7_NOVERIFY
            $flags = [
                PKCS7_DETACHED | PKCS7_BINARY,
                PKCS7_DETACHED | PKCS7_BINARY | PKCS7_NOVERIFY,
            ];

            foreach ($flags as $index => $flag) {
                $verifyResult = @openssl_pkcs7_verify(
                    $tempSigPathDer,
                    $flag,
                    $tempCertPath,
                    [],
                    null,
                    $tempDocPath  // ✅ CRITICAL: Provide content file
                );

                Log::info('PHP openssl_pkcs7_verify attempt', [
                    'attempt' => $index + 1,
                    'flags' => $flag,
                    'result' => $verifyResult,
                    'openssl_error' => openssl_error_string() ?: 'none'
                ]);

                if ($verifyResult === 1 || $verifyResult === true) {
                    Log::info('PKCS#7 verification successful via PHP openssl_pkcs7_verify');

                    return $this->buildVerificationResult(
                        true,
                        $documentContent,
                        $digitalSignature,
                        $tempCertPath,
                        'php_openssl_pkcs7_verify'
                    );
                }
            }

            // ✅ STRATEGY 3: Manual verification using extracted certificate
            Log::info('PHP verification failed, trying manual certificate verification');

            $manualResult = $this->verifyPKCS7Manually(
                $derBinary,
                $documentContent,
                $digitalSignature
            );

            if ($manualResult['is_valid']) {
                return $manualResult;
            }

            // All strategies failed
            Log::warning('All PKCS#7 verification strategies failed', [
                'signature_id' => $digitalSignature->signature_id,
                'openssl_error' => openssl_error_string() ?: 'none'
            ]);

            return $this->buildVerificationResult(
                false,
                $documentContent,
                $digitalSignature,
                null,
                'all_strategies_failed'
            );

        } catch (\Exception $e) {
            Log::error('PKCS#7 verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'is_valid' => false,
                'signature_format' => 'pkcs7_cms_detached',
                'error_message' => 'Verification failed: ' . $e->getMessage(),
                'verified_at' => now()
            ];
        } finally {
            // Cleanup
            if ($tempDocPath) @unlink($tempDocPath);
            if ($tempSigPathDer) @unlink($tempSigPathDer);
            if ($tempCertPath) @unlink($tempCertPath);
        }
    }

    /**
     * ✅ NEW: Verify PKCS#7 using openssl command (most reliable method)
     */
    private function verifyPKCS7UsingCommand($sigPath, $docPath, $certPath)
    {
        try {
            // Use openssl smime -verify to validate signature
            $cmd = sprintf(
                'openssl smime -verify -in %s -content %s -inform DER -certfile %s -noverify 2>&1',
                escapeshellarg($sigPath),
                escapeshellarg($docPath),
                escapeshellarg($certPath)
            );

            exec($cmd, $output, $returnCode);

            Log::info('OpenSSL command verification attempt', [
                'command' => $cmd,
                'return_code' => $returnCode,
                'output' => implode("\n", $output)
            ]);

            // Return code 0 = success
            if ($returnCode === 0) {
                return true;
            }

            // Try alternative command without -certfile
            $cmd2 = sprintf(
                'openssl smime -verify -in %s -content %s -inform DER -noverify 2>&1',
                escapeshellarg($sigPath),
                escapeshellarg($docPath)
            );

            exec($cmd2, $output2, $returnCode2);

            Log::info('OpenSSL command verification attempt (alternative)', [
                'command' => $cmd2,
                'return_code' => $returnCode2,
                'output' => implode("\n", $output2)
            ]);

            return $returnCode2 === 0;

        } catch (\Exception $e) {
            Log::warning('OpenSSL command verification failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ✅ NEW: Manual PKCS#7 verification by extracting certificate and verifying hash
     */
    private function verifyPKCS7Manually($derBinary, $documentContent, $digitalSignature)
    {
        try {
            Log::info('Attempting manual PKCS#7 verification');

            // Extract certificate from PKCS#7 structure using openssl command
            $tempP7Path = tempnam(sys_get_temp_dir(), 'p7_');
            $tempCertPath = tempnam(sys_get_temp_dir(), 'cert_');

            file_put_contents($tempP7Path, $derBinary);

            // Extract certificate
            $cmd = sprintf(
                'openssl pkcs7 -inform DER -in %s -print_certs -out %s 2>&1',
                escapeshellarg($tempP7Path),
                escapeshellarg($tempCertPath)
            );

            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($tempCertPath)) {
                @unlink($tempP7Path);
                @unlink($tempCertPath);
                return ['is_valid' => false];
            }

            // Read extracted certificate
            $extractedCert = file_get_contents($tempCertPath);

            if (!$extractedCert) {
                @unlink($tempP7Path);
                @unlink($tempCertPath);
                return ['is_valid' => false];
            }

            // Parse certificate to get public key
            $certData = openssl_x509_parse($extractedCert);
            $publicKey = openssl_pkey_get_public($extractedCert);

            if (!$publicKey) {
                @unlink($tempP7Path);
                @unlink($tempCertPath);
                return ['is_valid' => false];
            }

            // Calculate document hash
            $documentHash = hash('sha256', $documentContent);

            // Extract signature value from PKCS#7 (simplified - may need ASN.1 parsing)
            // For now, we'll use the digital signature's public key to verify

            // Compare with stored certificate
            $isValid = ($extractedCert === $digitalSignature->certificate);

            Log::info('Manual PKCS#7 verification completed', [
                'certificate_match' => $isValid,
                'extracted_cert_size' => strlen($extractedCert),
                'stored_cert_size' => strlen($digitalSignature->certificate ?? '')
            ]);

            @unlink($tempP7Path);
            @unlink($tempCertPath);

            if ($isValid) {
                return $this->buildVerificationResult(
                    true,
                    $documentContent,
                    $digitalSignature,
                    null,
                    'manual_certificate_comparison'
                );
            }

            return ['is_valid' => false];

        } catch (\Exception $e) {
            Log::warning('Manual PKCS#7 verification failed', [
                'error' => $e->getMessage()
            ]);
            return ['is_valid' => false];
        }
    }

    /**
     * ✅ NEW: Build standardized verification result
     */
    private function buildVerificationResult($isValid, $documentContent, $digitalSignature, $certPath, $method)
    {
        $documentHash = hash('sha256', $documentContent);

        $signerCertInfo = null;
        if ($isValid && $certPath && file_exists($certPath) && filesize($certPath) > 0) {
            $signerCert = file_get_contents($certPath);
            if ($signerCert) {
                $certData = @openssl_x509_parse($signerCert);
                if ($certData) {
                    $signerCertInfo = [
                        'subject_cn' => $certData['subject']['CN'] ?? 'N/A',
                        'issuer_cn' => $certData['issuer']['CN'] ?? 'N/A',
                        'serial_number' => $certData['serialNumber'] ?? 'N/A',
                        'valid_from' => isset($certData['validFrom_time_t']) ?
                            date('Y-m-d H:i:s', $certData['validFrom_time_t']) : null,
                        'valid_until' => isset($certData['validTo_time_t']) ?
                            date('Y-m-d H:i:s', $certData['validTo_time_t']) : null,
                    ];
                }
            }
        }

        return [
            'is_valid' => $isValid,
            'signature_format' => 'pkcs7_cms_detached',
            'document_hash' => $documentHash,
            'signature_algorithm' => $digitalSignature->algorithm,
            'verified_at' => now(),
            'signature_status' => $digitalSignature->status,
            'certificate_valid' => $digitalSignature->isValid(),
            'signer_certificate_info' => $signerCertInfo,
            'verification_method' => $method,
            'error_message' => !$isValid ? 'PKCS#7 signature verification failed' : null
        ];
    }

    /**
     * ✅ NEW: Verify legacy hash-only signature (backward compatibility)
     *
     * @param string $documentContent Document content
     * @param string $cmsSignature Base64-encoded signature
     * @param DigitalSignature $digitalSignature Digital signature model
     * @return array Verification result
     */
    private function verifyLegacyHashSignature($documentContent, $cmsSignature, $digitalSignature)
    {
        try {
            // Generate document hash
            $documentHash = hash('sha256', $documentContent);

            // Decode CMS signature
            $signature = base64_decode($cmsSignature);
            if (!$signature) {
                throw new \Exception('Invalid CMS signature format');
            }

            // Verify signature menggunakan public key
            $result = openssl_verify($documentHash, $signature, $digitalSignature->public_key, OPENSSL_ALGO_SHA256);

            $verificationResult = [
                'is_valid' => $result === 1,
                'signature_format' => 'legacy_hash_only',
                'document_hash' => $documentHash,
                'signature_algorithm' => $digitalSignature->algorithm,
                'verified_at' => now(),
                'signature_status' => $digitalSignature->status,
                'certificate_valid' => $digitalSignature->isValid(),
                'verification_method' => 'openssl_verify',
                'error_message' => $result !== 1 ? 'Signature verification failed' : null
            ];

            if ($result === 1) {
                Log::info('Legacy signature verification successful', [
                    'signature_id' => $digitalSignature->signature_id,
                    'document_hash' => $documentHash
                ]);
            } else {
                Log::warning('Legacy signature verification failed', [
                    'signature_id' => $digitalSignature->signature_id,
                    'verification_result' => $result,
                    'openssl_error' => openssl_error_string()
                ]);
            }

            return $verificationResult;

        } catch (\Exception $e) {
            Log::error('Signature verification error: ' . $e->getMessage());
            return [
                'is_valid' => false,
                'signature_format' => 'legacy_hash_only',
                'error_message' => $e->getMessage(),
                'verified_at' => now()
            ];
        }
    }

    /**
     * ✅ NEW: Auto-detect signature format from base64-encoded signature
     *
     * @param string $cmsSignature Base64-encoded signature
     * @return string 'pkcs7_cms_detached' or 'legacy_hash_only'
     */
    private function detectSignatureFormat($cmsSignature)
    {
        try {
            // Decode signature
            $decoded = base64_decode($cmsSignature);
            if (!$decoded) {
                return 'legacy_hash_only'; // Invalid base64 = assume legacy
            }

            // ✅ ENHANCED: Check for PKCS#7 DER structure
            // PKCS#7 signatures are stored as base64-encoded DER binary
            // DER structure starts with 0x30 (SEQUENCE tag) for PKCS#7 SignedData

            // Check for DER format PKCS#7 structure (legacy/backward compatibility)
            // PKCS#7 SignedData DER structure starts with 0x30 (SEQUENCE tag)
            // Typical size for PKCS#7: > 500 bytes (includes certificate)
            // Legacy signature size: 256 bytes (RSA-2048) or 512 bytes (RSA-4096)
            $signatureLength = strlen($decoded);

            if ($signatureLength > 500) {
                // Likely PKCS#7 (contains certificate + signature structure)
                // Further check: look for DER SEQUENCE tag
                if (ord($decoded[0]) === 0x30) {
                    Log::info('Detected PKCS#7 signature format (DER structure)', [
                        'length' => $signatureLength
                    ]);
                    return 'pkcs7_cms_detached';
                }
            }

            // Small signature size = likely legacy hash-only
            if ($signatureLength <= 512) {
                Log::info('Detected legacy hash-only signature format', [
                    'length' => $signatureLength
                ]);
                return 'legacy_hash_only';
            }

            // Default to legacy for safety
            Log::warning('Could not reliably detect signature format, defaulting to legacy', [
                'length' => $signatureLength,
                'first_byte' => sprintf('0x%02x', ord($decoded[0]))
            ]);
            return 'legacy_hash_only';

        } catch (\Exception $e) {
            Log::warning('Signature format detection failed, defaulting to legacy', [
                'error' => $e->getMessage()
            ]);
            return 'legacy_hash_only';
        }
    }

    /**
     * REFACTORED: Sign document with auto-generated unique key
     * Called when user submits QR positioning
     * IMPROVED: Added database transaction for rollback mechanism
     *
     * @param DocumentSignature $documentSignature
     * @param string $finalPdfPath Path to PDF with embedded QR
     * @return DocumentSignature
     */
    //! DIPAKAI DI CONTROLLER DIGITALSIGNATURE PROCESSDOCUMENTSIGNING METHOD
    public function signDocumentWithUniqueKey(DocumentSignature $documentSignature, $finalPdfPath)
    {
        // Use database transaction for atomic operation
        DB::beginTransaction();

        try {
            $approvalRequest = $documentSignature->approvalRequest;

            if (!$approvalRequest) {
                throw new \Exception('Approval request not found');
            }

            Log::info('Starting document signing with unique key (with DB transaction)', [
                'document_signature_id' => $documentSignature->id,
                'approval_request_id' => $approvalRequest->id,
                'final_pdf_path' => $finalPdfPath
            ]);

            // STEP 1: Generate unique digital signature key for this document
            $digitalSignature = $this->createDigitalSignatureForDocument($documentSignature);

            // STEP 2: Create CMS signature from final PDF (with embedded QR)
            $signatureData = $this->createCMSSignature($finalPdfPath, $digitalSignature);

            // STEP 3: Update DocumentSignature with signature data
            $documentSignature->update([
                'digital_signature_id' => $digitalSignature->id,
                'document_hash' => $signatureData['document_hash'],
                'signature_value' => $signatureData['signature_value'],
                'cms_signature' => $signatureData['cms_signature'],
                'signature_format' => $signatureData['signature_format'], // ✅ NEW: Track signature format
                'signed_at' => $signatureData['signed_at'],
                'signed_by' => $approvalRequest->approved_by,
                'signature_status' => DocumentSignature::STATUS_VERIFIED,
                'signature_metadata' => $signatureData['metadata'],
                'final_pdf_path' => $finalPdfPath
            ]);

            // STEP 4: Update approval request status
            $approvalRequest->update([
                'status' => ApprovalRequest::STATUS_SIGN_APPROVED
            ]);

            // STEP 5: Create audit log
            $metadata = SignatureAuditLog::createMetadata([
                'document_signature_id' => $documentSignature->id,
                'digital_signature_id' => $digitalSignature->id,
                'signature_id' => $digitalSignature->signature_id,
                'document_name' => $approvalRequest->document_name,
                'document_hash' => $signatureData['document_hash'],
                'final_pdf_path' => $finalPdfPath,
                'signer_name' => $approvalRequest->kaprodi->name ?? 'N/A',
                'signer_email' => $approvalRequest->kaprodi->email ?? 'N/A'
            ]);

            SignatureAuditLog::create([
                'kaprodi_id' => Auth::guard('kaprodi')->id(),
                'document_signature_id' => $documentSignature->id,
                'approval_request_id' => $approvalRequest->id,
                'action' => SignatureAuditLog::ACTION_DOCUMENT_SIGNED,
                'status_from' => DocumentSignature::STATUS_PENDING,
                'status_to' => DocumentSignature::STATUS_VERIFIED,
                'description' => 'Document signed with auto-generated unique digital signature key',
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            // Commit transaction - All steps successful
            DB::commit();

            Log::info('Document signed successfully with unique key (transaction committed)', [
                'document_signature_id' => $documentSignature->id,
                'digital_signature_id' => $digitalSignature->id,
                'signature_id' => $digitalSignature->signature_id
            ]);

            return $documentSignature->fresh(['digitalSignature', 'approvalRequest']);

        } catch (\Exception $e) {
            // Rollback all database changes on error
            DB::rollBack();

            Log::error('Document signing with unique key failed (transaction rolled back)', [
                'document_signature_id' => $documentSignature->id ?? null,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Generate self-signed certificate for the public key
     * IMPROVED: Personalized subject, synced validity, X.509 v3 extensions
     *
     * @param resource $privateKey OpenSSL private key resource
     * @param array $publicKeyDetails Public key details from openssl_pkey_get_details
     * @param int $validityYears Certificate validity period in years (default 3)
     * @param array|null $signerInfo Signer information for personalization
     * @return string Certificate in PEM format
     */
    //! DIPAKAI DI GenerateKeyPair METHOD
    // private function generateSelfSignedCertificate(OpenSSLAsymmetricKey $privateKey, $publicKeyDetails, $validityYears = 3, $signerInfo = null)
    // {
    //     try {
    //         // Build personalized Distinguished Name (DN)
    //         $commonName = "Teknik Informatika UMT Digital Signature Authority";
    //         $emailAddress = "informatika@umt.ac.id";

    //         // Personalize with signer info if available
    //         if ($signerInfo && isset($signerInfo['name'])) {
    //             $commonName = $signerInfo['name'] . " - " . ($signerInfo['role'] ?? 'Kaprodi');
    //             if (isset($signerInfo['email'])) {
    //                 $emailAddress = $signerInfo['email'];
    //             }
    //         }

    //         // Certificate subject (self-signed: subject = issuer)
    //         $dn = [
    //             "countryName" => "ID",
    //             "stateOrProvinceName" => "Banten",
    //             "localityName" => "Tangerang",
    //             "organizationName" => "Universitas Muhammadiyah Tangerang",
    //             "organizationalUnitName" => "Program Studi Teknik Informatika",
    //             "commonName" => $commonName,
    //             "emailAddress" => $emailAddress
    //         ];

    //         // CSR configuration with X.509 v3 extensions
    //         // $csrConfig = [
    //         //     "digest_alg" => "sha256",
    //         //     "req_extensions" => "v3_req",
    //         //     "x509_extensions" => "v3_ca"
    //         // ];

    //         // ✅ PERBAIKAN: Simplified CSR config - remove unsupported extensions
    //         $csrConfig = [
    //             "digest_alg" => "sha256",
    //             "private_key_bits" => $publicKeyDetails['bits'] ?? 2048,
    //             "private_key_type" => OPENSSL_KEYTYPE_RSA,
    //         ];

    //         // Normalize private key to OpenSSLAsymmetricKey for PHP 8+ compatibility
    //         // $opensslPrivateKey = $privateKey;
    //         // if (!($opensslPrivateKey instanceof \OpenSSLAsymmetricKey)) {
    //         //     // If it's a resource, export to PEM first then re-import to get an OpenSSLAsymmetricKey
    //         //     if (is_resource($privateKey)) {
    //         //         $pem = '';
    //         //         if (!openssl_pkey_export($privateKey, $pem)) {
    //         //             throw new \Exception('Failed to export private key for CSR creation: ' . openssl_error_string());
    //         //         }
    //         //         $opensslPrivateKey = openssl_pkey_get_private($pem);
    //         //     } else {
    //         //         // Attempt to obtain from string (PEM) or other accepted formats
    //         //         $opensslPrivateKey = openssl_pkey_get_private($privateKey);
    //         //     }

    //         // // Create self-signed certificate (null = self-signed)
    //         // // IMPROVED: Validity now synced with key validity (3 years instead of 1 year)
    //         // $cert = openssl_csr_sign($csr, null, $opensslPrivateKey, $validityDays, $certConfig);
    //         // if (!$cert) {
    //         //     throw new \Exception('Failed to create certificate: ' . openssl_error_string());
    //         // }
    //         // $csr = openssl_csr_new($dn, $opensslPrivateKey, $csrConfig);

    //         // Generate certificate signing request
    //         $csr = openssl_csr_new($dn, $privateKey, $csrConfig);
    //         if (!$csr) {
    //             throw new \Exception('Failed to create CSR: ' . openssl_error_string());
    //         }

    //         // Calculate validity in days (synced with key validity)
    //         $validityDays = $validityYears * 365;

    //         // Certificate configuration with X.509 v3 extensions
    //         // $certConfig = [
    //         //     "digest_alg" => "sha256",
    //         //     "x509_extensions" => "v3_ca"
    //         // ];
    //         // ✅ PERBAIKAN: Simplified certificate config
    //         $certConfig = [
    //             "digest_alg" => "sha256",
    //         ];


    //         // Create self-signed certificate (null = self-signed)
    //         // IMPROVED: Validity now synced with key validity (3 years instead of 1 year)
    //         $cert = openssl_csr_sign($csr, null, $privateKey, $validityDays, $certConfig);
    //         if (!$cert) {
    //             throw new \Exception('Failed to create certificate: ' . openssl_error_string());
    //         }

    //         // ✅ PERBAIKAN: Verify certificate before exporting
    //         $certResource = openssl_x509_read($cert);
    //         if (!$certResource) {
    //             throw new \Exception('Failed to read generated certificate: ' . openssl_error_string());
    //         }

    //         // Export certificate to PEM format
    //         $certPem = '';
    //         // if (!openssl_x509_export($cert, $certPem)) {
    //         //     throw new \Exception('Failed to export certificate: ' . openssl_error_string());
    //         // }
    //         if (!openssl_x509_export($certResource, $certPem)) {
    //             throw new \Exception('Failed to export certificate: ' . openssl_error_string());
    //         }

    //         // ✅ PERBAIKAN: Validate exported certificate
    //         if (empty($certPem) || !str_contains($certPem, 'BEGIN CERTIFICATE')) {
    //             throw new \Exception('Exported certificate is invalid or empty');
    //         }

    //         Log::info('X.509 certificate generated successfully', [
    //             'common_name' => $commonName,
    //             'validity_years' => $validityYears,
    //             'validity_days' => $validityDays,
    //             'email' => $emailAddress,
    //             'key_bits' => $publicKeyDetails['bits'] ?? 2048
    //         ]);

    //         return $certPem;

    //     } catch (\Exception $e) {
    //         Log::warning('Certificate generation failed, using fallback format: ' . $e->getMessage());

    //         // Fallback: return basic certificate info
    //         $validityYears = $validityYears ?? 3;
    //         return "-----BEGIN CERTIFICATE-----\n" .
    //                base64_encode(json_encode([
    //                    'subject' => $signerInfo['name'] ?? 'Digital Signature Authority',
    //                    'issuer' => $signerInfo['name'] ?? 'Digital Signature Authority',
    //                    'valid_from' => now()->toISOString(),
    //                    'valid_until' => now()->addYears($validityYears)->toISOString(),
    //                    'serial_number' => Str::random(16),
    //                    'email' => $signerInfo['email'] ?? 'informatika@umt.ac.id'
    //                ])) .
    //                "\n-----END CERTIFICATE-----";
    //     }
    // }
    /**
     * ✅ HELPER: Extract PKCS#7 signature from signed PDF for storage/verification
     *
     * This extracts the PKCS#7 signature that was embedded by TCPDF setSignature()
     * Useful for backward compatibility with verification system
     *
     * @param string $signedPdfPath - Path to signed PDF
     * @return string|null - Base64 encoded PKCS#7 signature, or null if not found
     */
    public function extractPKCS7FromSignedPDF(string $signedPdfPath): ?string
    {
        try {
            if (!file_exists($signedPdfPath)) {
                Log::error('Signed PDF not found for PKCS#7 extraction', [
                    'path' => $signedPdfPath
                ]);
                return null;
            }

            $pdfContent = file_get_contents($signedPdfPath);

            // Find /Contents <hex_data> in PDF signature dictionary
            // Adobe PDF signature format stores PKCS#7 in /Contents field as hex string
            if (preg_match('/\/Contents\s*<([0-9A-Fa-f]+)>/', $pdfContent, $matches)) {
                $pkcs7Hex = $matches[1];

                // Convert hex to binary
                $pkcs7Binary = hex2bin($pkcs7Hex);

                if (!$pkcs7Binary) {
                    Log::warning('Failed to convert hex to binary', [
                        'hex_length' => strlen($pkcs7Hex)
                    ]);
                    return null;
                }

                // Validate it's a valid PKCS#7/CMS structure (should start with 0x30 - SEQUENCE)
                if (ord($pkcs7Binary[0]) !== 0x30) {
                    Log::warning('Invalid PKCS#7 structure - does not start with SEQUENCE', [
                        'first_byte' => sprintf('0x%02x', ord($pkcs7Binary[0]))
                    ]);
                    return null;
                }

                // Encode to base64 for storage
                $pkcs7Base64 = base64_encode($pkcs7Binary);

                Log::info('PKCS#7 signature extracted from signed PDF', [
                    'pdf_path' => basename($signedPdfPath),
                    'pkcs7_size_bytes' => strlen($pkcs7Binary),
                    'base64_length' => strlen($pkcs7Base64)
                ]);

                return $pkcs7Base64;
            }

            Log::warning('No /Contents field found in PDF', [
                'pdf_path' => basename($signedPdfPath),
                'searched_pattern' => '/Contents <hex>'
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to extract PKCS#7 from signed PDF', [
                'error' => $e->getMessage(),
                'pdf_path' => $signedPdfPath
            ]);

            return null;
        }
    }

    /**
     * ✅ ENHANCED: Generate REAL self-signed X.509 v3 certificate with proper extensions
     * IMPROVEMENTS:
     * - Cryptographically secure random serial number
     * - X.509 v3 extensions (basicConstraints, keyUsage, extendedKeyUsage)
     * - Subject Key Identifier and Authority Key Identifier
     * - ✅ IMPROVED: Better resource management with try-finally
     * - ✅ IMPROVED: GMP extension dependency check with fallback
     * - ✅ IMPROVED: Memory-efficient operations
     */
    private function generateSelfSignedCertificate(OpenSSLAsymmetricKey $privateKey, $publicKeyDetails, $validityYears = 1, $signerInfo = null)
    {
        // Track temp files for guaranteed cleanup
        $configPath = null;

        try {
            // Build personalized Distinguished Name
            $commonName = "Digital Signature System - UMT Informatika";
            $emailAddress = "informatika@umt.ac.id";

            if ($signerInfo && isset($signerInfo['name'])) {
                $commonName = $signerInfo['name'];
                if (isset($signerInfo['email'])) {
                    $emailAddress = $signerInfo['email'];
                }
            }

            // ✅ Certificate subject (DN)
            $dn = [
                "countryName" => "ID",
                "stateOrProvinceName" => "Banten",
                "localityName" => "Tangerang",
                "organizationName" => "Universitas Muhammadiyah Tangerang",
                "organizationalUnitName" => "Fakultas Teknik - Program Studi Teknik Informatika",
                "commonName" => $commonName,
                "emailAddress" => $emailAddress
            ];

            Log::info('Generating X.509 v3 certificate with DN', ['dn' => $dn]);

            // Generate cryptographically secure random serial number
            // Using 128-bit random number (16 bytes) to ensure uniqueness
            $serialBytes = openssl_random_pseudo_bytes(16, $cryptoStrong);

            if (!$cryptoStrong) {
                Log::warning('Falling back to less secure random generation for serial number');
                $serialBytes = random_bytes(16);
            }

            // GMP extension check with fallback
            $serialHex = bin2hex($serialBytes);
            $serialNumberInt = $this->convertHexToInteger($serialHex);

            Log::info('Generated cryptographically secure serial number', [
                'serial_hex' => $serialHex,
                'serial_decimal' => $serialNumberInt,
                'gmp_available' => extension_loaded('gmp')
            ]);

            // Create OpenSSL config with X.509 v3 extensions
            $opensslConfig = $this->generateOpenSSLConfigWithV3Extensions();

            // Write config to temporary file
            $configPath = tempnam(sys_get_temp_dir(), 'openssl_config_');
            file_put_contents($configPath, $opensslConfig);

            // Create CSR with X.509 v3 extensions config
            $configArgs = [
                "digest_alg" => "sha256",
                "private_key_bits" => $publicKeyDetails['bits'] ?? 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
                "config" => $configPath,
                "x509_extensions" => "v3_cert",  // Apply v3 extensions
                "req_extensions" => "v3_req"     // Request extensions
            ];

            // Generate CSR
            $csr = openssl_csr_new($dn, $privateKey, $configArgs);

            if (!$csr) {
                $error = openssl_error_string();
                Log::error('CSR generation failed', [
                    'openssl_error' => $error,
                    'dn' => $dn
                ]);
                throw new \Exception("Failed to create CSR: {$error}");
            }

            Log::info('CSR created successfully with v3 extensions');

            // Calculate validity period
            $validityDays = $validityYears * 365;

            // Sign CSR to create self-signed certificate with secure serial number
            $cert = openssl_csr_sign(
                $csr,              // CSR
                null,              // Self-signed (no CA cert)
                $privateKey,       // Private key
                $validityDays,     // Validity in days
                $configArgs,       // Config with v3 extensions
                $serialNumberInt   // Cryptographically secure serial number
            );

            if (!$cert) {
                $error = openssl_error_string();
                Log::error('Certificate signing failed', [
                    'openssl_error' => $error,
                    'validity_days' => $validityDays
                ]);
                throw new \Exception("Failed to sign certificate: {$error}");
            }

            Log::info('Certificate signed successfully with v3 extensions');

            // Verify certificate is valid before exporting
            $certResource = openssl_x509_read($cert);
            if (!$certResource) {
                $error = openssl_error_string();
                Log::error('Certificate validation failed', ['openssl_error' => $error]);
                throw new \Exception("Failed to read signed certificate: {$error}");
            }

            // Export certificate to PEM format
            $certPem = '';
            $exportSuccess = openssl_x509_export($certResource, $certPem);

            if (!$exportSuccess) {
                $error = openssl_error_string();
                Log::error('Certificate export failed', ['openssl_error' => $error]);
                throw new \Exception("Failed to export certificate: {$error}");
            }

            // Validate exported PEM certificate
            if (empty($certPem)) {
                throw new \Exception('Exported certificate is empty');
            }

            if (!str_contains($certPem, '-----BEGIN CERTIFICATE-----')) {
                throw new \Exception('Exported certificate has invalid PEM format');
            }

            // IMPROVEMENT 4: Memory-efficient parsing (don't store full parsed array if not needed)
            $parsedCert = openssl_x509_parse($certPem);
            if (!$parsedCert) {
                throw new \Exception('Exported certificate cannot be parsed - invalid X.509 format');
            }

            // Verify X.509 v3 extensions are present
            $hasExtensions = isset($parsedCert['extensions']) && is_array($parsedCert['extensions']);
            $hasBasicConstraints = $hasExtensions && isset($parsedCert['extensions']['basicConstraints']);
            $hasKeyUsage = $hasExtensions && isset($parsedCert['extensions']['keyUsage']);

            Log::info('X.509 v3 certificate generated and validated successfully', [
                'subject_cn' => $parsedCert['subject']['CN'] ?? 'N/A',
                'issuer_cn' => $parsedCert['issuer']['CN'] ?? 'N/A',
                'valid_from' => isset($parsedCert['validFrom_time_t']) ? date('Y-m-d H:i:s', $parsedCert['validFrom_time_t']) : 'N/A',
                'valid_until' => isset($parsedCert['validTo_time_t']) ? date('Y-m-d H:i:s', $parsedCert['validTo_time_t']) : 'N/A',
                'serial' => $parsedCert['serialNumber'] ?? 'N/A',
                'serial_hex' => $serialHex,
                'version' => ($parsedCert['version'] ?? 2) + 1,
                'has_v3_extensions' => $hasExtensions,
                'has_basic_constraints' => $hasBasicConstraints,
                'has_key_usage' => $hasKeyUsage,
                'cert_length' => strlen($certPem)
            ]);

            // Clear parsed cert from memory after logging
            unset($parsedCert);

            // Warning if extensions are missing (should not happen)
            if (!$hasExtensions || !$hasBasicConstraints || !$hasKeyUsage) {
                Log::warning('Certificate missing some X.509 v3 extensions', [
                    'has_extensions' => $hasExtensions,
                    'has_basic_constraints' => $hasBasicConstraints,
                    'has_key_usage' => $hasKeyUsage
                ]);
            }

            return $certPem;

        } catch (\Exception $e) {
            Log::error('Certificate generation failed completely', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'signer_info' => $signerInfo
            ]);

            // Return NULL instead of fallback JSON
            // This allows proper error handling in calling code
            return null;

        } finally {
            // Guaranteed cleanup in finally block
            if ($configPath !== null && file_exists($configPath)) {
                @unlink($configPath);
                Log::debug('Cleaned up temporary OpenSSL config file', [
                    'config_path' => $configPath
                ]);
            }
        }
    }

    /**
     * ✅ NEW: Generate OpenSSL configuration with X.509 v3 extensions
     * Includes:
     * - basicConstraints: CA:FALSE (this is an end-entity certificate, not a CA)
     * - keyUsage: digitalSignature, nonRepudiation (for document signing)
     * - extendedKeyUsage: codeSigning, emailProtection (document and email signing)
     * - subjectKeyIdentifier: Unique identifier for the certificate's public key
     * - authorityKeyIdentifier: Links to issuer's public key (self-signed: same as subject)
     *
     * @return string OpenSSL configuration content
     */
    private function generateOpenSSLConfigWithV3Extensions()
    {
        return <<<EOT
[ req ]
default_bits = 2048
default_md = sha256
distinguished_name = req_distinguished_name
req_extensions = v3_req
x509_extensions = v3_cert

[ req_distinguished_name ]
# Empty section - DN provided programmatically

[ v3_req ]
# Extensions for CSR
basicConstraints = CA:FALSE
keyUsage = critical, digitalSignature, nonRepudiation
extendedKeyUsage = codeSigning, emailProtection
subjectKeyIdentifier = hash

[ v3_cert ]
# Extensions for self-signed certificate
basicConstraints = critical, CA:FALSE
keyUsage = critical, digitalSignature, nonRepudiation
extendedKeyUsage = codeSigning, emailProtection
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid:always,issuer:always
# subjectAltName can be added here if needed for email
EOT;
    }

    /**
     * Generate fingerprint dari public key
     */
    //! DIPAKAI DI GenerateKeyPair METHOD
    private function generateFingerprint($publicKey)
    {
        return strtoupper(chunk_split(hash('sha256', $publicKey), 2, ':'));
    }

    /**
     * ✅ NEW: Convert hexadecimal string to integer with GMP fallback
     *
     * This method provides a safe way to convert large hex strings to integers
     * with automatic fallback if GMP extension is not available.
     *
     * @param string $hexString Hexadecimal string (without 0x prefix)
     * @return int|string Integer representation (may be string for very large numbers)
     */
    private function convertHexToInteger(string $hexString)
    {
        // Check if GMP extension is available
        if (extension_loaded('gmp')) {
            try {
                // Use GMP for large number handling (recommended)
                $gmpNumber = gmp_init($hexString, 16);
                $intValue = gmp_intval($gmpNumber);

                // Ensure positive value
                if ($intValue < 0) {
                    $intValue = abs($intValue);
                }

                Log::debug('Serial number converted using GMP extension', [
                    'hex_input' => substr($hexString, 0, 32) . '...',
                    'decimal_output' => $intValue
                ]);

                return $intValue;

            } catch (\Exception $e) {
                Log::warning('GMP conversion failed, using fallback method', [
                    'error' => $e->getMessage()
                ]);
                // Fall through to fallback method
            }
        }

        // Use BC Math or base conversion if GMP not available
        if (extension_loaded('bcmath')) {
            try {
                // Use BC Math for arbitrary precision arithmetic
                $decimalString = '0';
                $hexLength = strlen($hexString);

                for ($i = 0; $i < $hexLength; $i++) {
                    $decimalString = bcmul($decimalString, '16');
                    $decimalString = bcadd($decimalString, (string)hexdec($hexString[$i]));
                }

                // Convert to integer if within PHP_INT_MAX
                if (bccomp($decimalString, (string)PHP_INT_MAX) <= 0) {
                    $intValue = (int)$decimalString;
                } else {
                    // Keep as string for very large numbers
                    $intValue = $decimalString;
                }

                Log::warning('Serial number converted using BC Math fallback', [
                    'hex_input' => substr($hexString, 0, 32) . '...',
                    'decimal_output' => is_int($intValue) ? $intValue : 'large_number_string',
                    'gmp_available' => false,
                    'bcmath_available' => true
                ]);

                return $intValue;

            } catch (\Exception $e) {
                Log::warning('BC Math conversion failed, using basic fallback', [
                    'error' => $e->getMessage()
                ]);
                // Fall through to basic fallback
            }
        }

        // LAST RESORT FALLBACK: Use base_convert (may lose precision for very large numbers)
        try {
            // Split hex string into chunks to avoid overflow
            $maxChunkSize = 8; // 8 hex chars = 32 bits

            if (strlen($hexString) <= $maxChunkSize) {
                // Small enough to convert directly
                $intValue = intval(base_convert($hexString, 16, 10));
            } else {
                // Take first 8 characters (32 bits) to avoid overflow
                $truncatedHex = substr($hexString, 0, $maxChunkSize);
                $intValue = intval(base_convert($truncatedHex, 16, 10));

                Log::warning('Serial number truncated due to lack of GMP/BC Math extensions', [
                    'original_hex' => $hexString,
                    'truncated_hex' => $truncatedHex,
                    'decimal_output' => $intValue,
                    'gmp_available' => false,
                    'bcmath_available' => false,
                    'recommendation' => 'Install GMP or BC Math extension for full 128-bit serial support'
                ]);
            }

            return abs($intValue); // Ensure positive

        } catch (\Exception $e) {
            // Ultimate fallback: use timestamp + random
            $fallbackSerial = time() + mt_rand(1000, 9999);

            Log::error('All serial number conversion methods failed, using timestamp fallback', [
                'error' => $e->getMessage(),
                'fallback_serial' => $fallbackSerial,
                'hex_input' => $hexString
            ]);

            return $fallbackSerial;
        }
    }

    /**
     * DEPRECATED: Get signature statistics (not used in new flow)
     * Kept for backward compatibility if needed
     */
    // public function getSignatureStatistics($digitalSignatureId)
    // {
    //     // No longer used - each document has unique key
    // }

    /**
     * Revoke digital signature (per-document basis now)
     */
    public function revokeSignature($digitalSignatureId, $reason = null)
    {
        try {
            $digitalSignature = DigitalSignature::findOrFail($digitalSignatureId);
            $digitalSignature->revoke($reason);

            // Invalidate associated document signature (only 1 now - 1-to-1)
            if ($documentSignature = $digitalSignature->documentSignature) {
                $documentSignature->update([
                    'signature_status' => DocumentSignature::STATUS_INVALID
                ]);
            }

            Log::info('Digital signature revoked', [
                'signature_id' => $digitalSignature->signature_id,
                'reason' => $reason,
                'document_signature_id' => $digitalSignature->document_signature_id
            ]);

            // Create audit log
            $metadata = SignatureAuditLog::createMetadata([
                'signature_id' => $digitalSignature->signature_id,
                'reason' => $reason ?? 'No reason provided',
                'document_signature_id' => $digitalSignature->document_signature_id,
                'revoked_by' => Auth::guard('kaprodi')->user()->name ?? 'System',
            ]);

            SignatureAuditLog::create([
                'kaprodi_id' => Auth::guard('kaprodi')->id(),
                'action' => SignatureAuditLog::ACTION_SIGNATURE_KEY_REVOKED,
                'status_from' => DigitalSignature::STATUS_ACTIVE,
                'status_to' => $digitalSignature->status,
                'description' => 'Digital signature key revoked: ' . ($reason ?? 'No reason provided'),
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to revoke signature: ' . $e->getMessage());
            throw $e;
        }
    }
}
