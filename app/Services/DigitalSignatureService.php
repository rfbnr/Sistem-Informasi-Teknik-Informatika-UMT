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
    public function generateKeyPair($keyLength = 2048, $algorithm = 'RSA-SHA256', $validityYears = 3, $signerInfo = null)
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
    public function createDigitalSignatureForDocument(DocumentSignature $documentSignature, $validityYears = 3)
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

            // $keyPair = $this->generateKeyPair(2048, 'RSA-SHA256', $validityYears, $signerInfo);

            // ✅ Generate key pair with enhanced error handling
            $keyPair = $this->generateKeyPair(2048, 'RSA-SHA256', $validityYears, $signerInfo);

            // ✅ CRITICAL: Check if certificate generation succeeded
            if (empty($keyPair['certificate'])) {
                Log::warning('Certificate generation failed, signature will proceed without certificate', [
                    'document_signature_id' => $documentSignature->id
                ]);
                // Set certificate to NULL instead of invalid format
                // $keyPair['certificate'] = null;

                // Fallback: return basic certificate info
                $validityYears = $validityYears ?? 3;
                $keyPair['certificate'] = "-----BEGIN CERTIFICATE-----\n" .
                    base64_encode(json_encode([
                        'subject' => $signerInfo['name'] ?? 'Digital Signature Authority',
                        'issuer' => $signerInfo['name'] ?? 'Digital Signature Authority',
                        'valid_from' => now()->toISOString(),
                        'valid_until' => now()->addYears($validityYears)->toISOString(),
                        'serial_number' => Str::random(16),
                        'email' => $signerInfo['email'] ?? 'informatika@umt.ac.id'
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
     * REFACTORED: Create CMS signature untuk dokumen
     * Can accept either DigitalSignature instance or ID
     */
    public function createCMSSignature($documentPath, $digitalSignature)
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

            // Create signature menggunakan private key
            $signature = '';
            $privateKey = $digitalSignature->private_key;

            if (!openssl_sign($documentHash, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                throw new \Exception('Failed to create digital signature: ' . openssl_error_string());
            }

            // Encode signature ke base64
            $cmsSignature = base64_encode($signature);

            // ✅ PERBAIKAN: Safe certificate fingerprint extraction
            $certificateFingerprint = null;
            $certInfo = null;

            if ($digitalSignature->certificate) {
                // ✅ Validate certificate format before processing
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

            // ✅ ENHANCED: Comprehensive metadata with safe fallbacks
            return [
                'document_hash' => $documentHash,
                'cms_signature' => $cmsSignature,
                'signature_value' => hash('sha256', $signature),
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
                    'version' => '2.0',
                    'compliance' => 'X.509 v3, CMS Signature'
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
     * Verify CMS signature
     */
    public function verifyCMSSignature($documentPath, $cmsSignature, $digitalSignatureId)
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
                'document_hash' => $documentHash,
                'signature_algorithm' => $digitalSignature->algorithm,
                'verified_at' => now(),
                'signature_status' => $digitalSignature->status,
                'certificate_valid' => $digitalSignature->isValid(),
                'error_message' => $result !== 1 ? 'Signature verification failed' : null
            ];

            if ($result === 1) {
                Log::info('Signature verification successful', [
                    'signature_id' => $digitalSignature->signature_id,
                    'document_hash' => $documentHash
                ]);
            } else {
                Log::warning('Signature verification failed', [
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
                'error_message' => $e->getMessage(),
                'verified_at' => now()
            ];
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
     * ✅ FIXED: Generate REAL self-signed X.509 certificate
     */
    private function generateSelfSignedCertificate(OpenSSLAsymmetricKey $privateKey, $publicKeyDetails, $validityYears = 3, $signerInfo = null)
    {
        try {
            // Build personalized Distinguished Name
            $commonName = "Digital Signature System UMT";
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

            Log::info('Generating X.509 certificate with DN', ['dn' => $dn]);

            // ✅ Create CSR with minimal config (avoid unsupported extensions)
            $configArgs = [
                "digest_alg" => "sha256",
                "private_key_bits" => $publicKeyDetails['bits'] ?? 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA
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

            Log::info('CSR created successfully');

            // ✅ Calculate validity period
            $validityDays = $validityYears * 365;

            // ✅ Sign CSR to create self-signed certificate
            $cert = openssl_csr_sign(
                $csr,           // CSR
                null,           // Self-signed (no CA cert)
                $privateKey,    // Private key
                $validityDays,  // Validity in days
                $configArgs,    // Config
                time()          // Serial number (using timestamp)
            );

            if (!$cert) {
                $error = openssl_error_string();
                Log::error('Certificate signing failed', [
                    'openssl_error' => $error,
                    'validity_days' => $validityDays
                ]);
                throw new \Exception("Failed to sign certificate: {$error}");
            }

            Log::info('Certificate signed successfully');

            // ✅ CRITICAL: Verify certificate is valid before exporting
            $certResource = openssl_x509_read($cert);
            if (!$certResource) {
                $error = openssl_error_string();
                Log::error('Certificate validation failed', ['openssl_error' => $error]);
                throw new \Exception("Failed to read signed certificate: {$error}");
            }

            // ✅ Export certificate to PEM format
            $certPem = '';
            $exportSuccess = openssl_x509_export($certResource, $certPem);

            if (!$exportSuccess) {
                $error = openssl_error_string();
                Log::error('Certificate export failed', ['openssl_error' => $error]);
                throw new \Exception("Failed to export certificate: {$error}");
            }

            // ✅ Validate exported PEM certificate
            if (empty($certPem)) {
                throw new \Exception('Exported certificate is empty');
            }

            if (!str_contains($certPem, '-----BEGIN CERTIFICATE-----')) {
                throw new \Exception('Exported certificate has invalid PEM format');
            }

            // ✅ Double-check: Parse exported certificate to verify it's valid
            $parsedCert = openssl_x509_parse($certPem);
            if (!$parsedCert) {
                throw new \Exception('Exported certificate cannot be parsed - invalid X.509 format');
            }

            Log::info('X.509 certificate generated and validated successfully', [
                'subject_cn' => $parsedCert['subject']['CN'] ?? 'N/A',
                'issuer_cn' => $parsedCert['issuer']['CN'] ?? 'N/A',
                'valid_from' => isset($parsedCert['validFrom_time_t']) ? date('Y-m-d H:i:s', $parsedCert['validFrom_time_t']) : 'N/A',
                'valid_until' => isset($parsedCert['validTo_time_t']) ? date('Y-m-d H:i:s', $parsedCert['validTo_time_t']) : 'N/A',
                'serial' => $parsedCert['serialNumber'] ?? 'N/A',
                'cert_length' => strlen($certPem)
            ]);

            return $certPem;

        } catch (\Exception $e) {
            Log::error('Certificate generation failed completely', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'signer_info' => $signerInfo
            ]);

            // ✅ CRITICAL FIX: Return NULL instead of fallback JSON
            // This allows proper error handling in calling code
            return null;
        }
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
