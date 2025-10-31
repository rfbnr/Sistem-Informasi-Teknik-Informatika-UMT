<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\ApprovalRequest;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use App\Models\SignatureAuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DigitalSignatureService
{
    /**
     * Generate RSA key pair dengan enhanced security
     */
    public function generateKeyPair($keyLength = 2048, $algorithm = 'RSA-SHA256')
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

            // Generate certificate (self-signed untuk testing)
            $certificate = $this->generateSelfSignedCertificate($privateKey, $publicKeyDetails);

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
    public function createDigitalSignatureForDocument(DocumentSignature $documentSignature, $validityYears = 3)
    {
        try {
            $keyPair = $this->generateKeyPair();

            $validFrom = now();
            $validUntil = $validFrom->copy()->addYears((int) $validityYears);

            $signature = DigitalSignature::create([
                'public_key' => $keyPair['public_key'],
                'private_key' => $keyPair['private_key'], // Will be encrypted by model mutator
                'algorithm' => $keyPair['algorithm'],
                'key_length' => $keyPair['key_length'],
                'certificate' => $keyPair['certificate'],
                'document_signature_id' => $documentSignature->id, // 1-to-1 relationship
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
                // Absolute path
                $documentContent = file_get_contents($documentPath);
                Log::info('Reading document from absolute path', [
                    'path' => $documentPath,
                    'size' => strlen($documentContent)
                ]);
            } else {
                // Relative path from storage
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

            return [
                'document_hash' => $documentHash,
                'cms_signature' => $cmsSignature,
                'signature_value' => hash('sha256', $signature),
                'algorithm' => $digitalSignature->algorithm,
                'signed_at' => now(),
                'metadata' => [
                    'document_size' => strlen($documentContent),
                    'signature_id' => $digitalSignature->signature_id,
                    'signing_ip' => request()->ip(),
                    'signing_user_agent' => request()->userAgent()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('CMS Signature creation failed: ' . $e->getMessage());
            throw $e;
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
     *
     * @param DocumentSignature $documentSignature
     * @param string $finalPdfPath Path to PDF with embedded QR
     * @return DocumentSignature
     */
    public function signDocumentWithUniqueKey(DocumentSignature $documentSignature, $finalPdfPath)
    {
        try {
            $approvalRequest = $documentSignature->approvalRequest;

            if (!$approvalRequest) {
                throw new \Exception('Approval request not found');
            }

            Log::info('Starting document signing with unique key', [
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
                'signed_by' => Auth::guard('kaprodi')->id(),
                'signature_status' => DocumentSignature::STATUS_VERIFIED,
                'signature_metadata' => $signatureData['metadata'],
                'final_pdf_path' => $finalPdfPath
            ]);

            // STEP 4: Update approval request status
            $approvalRequest->update([
                'status' => ApprovalRequest::STATUS_SIGN_APPROVED
            ]);

            Log::info('Document signed successfully with unique key', [
                'document_signature_id' => $documentSignature->id,
                'digital_signature_id' => $digitalSignature->id,
                'signature_id' => $digitalSignature->signature_id
            ]);

            // STEP 5: Create audit log
            $metadata = SignatureAuditLog::createMetadata([
                'document_signature_id' => $documentSignature->id,
                'digital_signature_id' => $digitalSignature->id,
                'signature_id' => $digitalSignature->signature_id,
                'document_name' => $approvalRequest->document_name,
                'document_hash' => $signatureData['document_hash'],
                'final_pdf_path' => $finalPdfPath
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

            return $documentSignature->fresh(['digitalSignature', 'approvalRequest']);

        } catch (\Exception $e) {
            Log::error('Document signing with unique key failed: ' . $e->getMessage(), [
                'document_signature_id' => $documentSignature->id ?? null,
                'error' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate self-signed certificate untuk testing
     */
    private function generateSelfSignedCertificate($privateKey, $publicKeyDetails)
    {
        try {
            // Certificate subject
            $dn = [
                "countryName" => "ID",
                "stateOrProvinceName" => "Jakarta",
                "localityName" => "Jakarta",
                "organizationName" => "Digital Signature System",
                "organizationalUnitName" => "IT Department",
                "commonName" => "Digital Signature Authority",
                "emailAddress" => "admin@example.com"
            ];

            // Generate certificate signing request
            $csr = openssl_csr_new($dn, $privateKey, ["digest_alg" => "sha256"]);
            if (!$csr) {
                throw new \Exception('Failed to create CSR: ' . openssl_error_string());
            }

            // Create self-signed certificate
            $cert = openssl_csr_sign($csr, null, $privateKey, 365, ["digest_alg" => "sha256"]);
            if (!$cert) {
                throw new \Exception('Failed to create certificate: ' . openssl_error_string());
            }

            // Export certificate to PEM format
            $certPem = '';
            if (!openssl_x509_export($cert, $certPem)) {
                throw new \Exception('Failed to export certificate: ' . openssl_error_string());
            }

            return $certPem;

        } catch (\Exception $e) {
            Log::warning('Certificate generation failed, using basic format: ' . $e->getMessage());

            // Fallback: return basic certificate info
            return "-----BEGIN CERTIFICATE-----\n" .
                   base64_encode(json_encode([
                       'subject' => 'CN=Digital Signature Authority',
                       'issuer' => 'CN=Digital Signature Authority',
                       'valid_from' => now()->toISOString(),
                       'valid_until' => now()->addYear()->toISOString(),
                       'serial_number' => Str::random(16)
                   ])) .
                   "\n-----END CERTIFICATE-----";
        }
    }

    /**
     * Generate fingerprint dari public key
     */
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
