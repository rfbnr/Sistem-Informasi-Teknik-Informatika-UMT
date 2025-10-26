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
     * Create digital signature dengan enhanced metadata
     */
    public function createDigitalSignature($purpose = 'Document Signing', $createdBy = null, $validityYears = 1)
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
                'signature_purpose' => $purpose,
                'created_by' => $createdBy ?? Auth::id(),
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'status' => DigitalSignature::STATUS_ACTIVE,
                'metadata' => [
                    'created_ip' => request()->ip(),
                    'created_user_agent' => request()->userAgent(),
                    'key_fingerprint' => $keyPair['fingerprint'],
                    'created_at_timestamp' => now()->timestamp
                ]
            ]);

            Log::info('Digital signature created successfully', [
                'signature_id' => $signature->signature_id,
                'created_by' => $createdBy
            ]);

            SignatureAuditLog::create([
                'kaprodi_id' => $createdBy ?? Auth::id(),
                'action' => 'create_digital_signature',
                'status_to' => $signature->status,
                'description' => 'Digital signature created',
                'metadata' => [
                    'signature_id' => $signature->signature_id,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);

            return $signature;

        } catch (\Exception $e) {
            Log::error('Digital signature creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create CMS signature untuk dokumen
     */
    public function createCMSSignature($documentPath, $digitalSignatureId)
    {
        try {
            $digitalSignature = DigitalSignature::findOrFail($digitalSignatureId);

            if (!$digitalSignature->isValid()) {
                throw new \Exception('Digital signature is not valid or expired');
            }

            // Read document content
            // Handle both absolute path (signed PDF) and relative path (original PDF)
            $documentContent = null;

            if (file_exists($documentPath)) {
                // Absolute path (e.g., signed PDF from mergeSignatureIntoPDF)
                $documentContent = file_get_contents($documentPath);
                Log::info('Reading document from absolute path', [
                    'path' => $documentPath,
                    'size' => strlen($documentContent)
                ]);
            } else {
                // Relative path from storage (original PDF)
                $documentContent = Storage::disk('public')->get($documentPath);
                Log::info('Reading document from storage', [
                    'path' => $documentPath,
                    'disk' => 'public',
                    'content' => $documentContent,
                    'size' => $documentContent ? strlen($documentContent) : 0
                ]);
            }

            if (!$documentContent) {
                throw new \Exception('Cannot read document content from: ' . $documentPath);
            }

            // Generate document hash
            $documentHash = hash('sha256', $documentContent);
            // $documentHash = hash('sha256', $documentContent);

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
     * Sign approval request document
     */
    public function signApprovalRequest($approvalRequestId, $digitalSignatureId, $documentPath = null)
    {
        try {
            $approvalRequest = ApprovalRequest::findOrFail($approvalRequestId);

            if ($approvalRequest->status !== ApprovalRequest::STATUS_APPROVED) {
                throw new \Exception('Approval request is not ready for signing');
            }

            // Determine which document path to sign
            // If $documentPath provided (signed PDF), use it
            // Otherwise, use original document path
            $pathToSign = $documentPath ?? $approvalRequest->document_path;

            Log::info('Signing approval request', [
                'approval_request_id' => $approvalRequestId,
                'document_path_provided' => $documentPath !== null,
                'path_to_sign' => $pathToSign,
                'is_signed_pdf' => $documentPath !== null
            ]);

            // Create CMS signature from the correct document (signed PDF or original)
            $signatureData = $this->createCMSSignature($pathToSign, $digitalSignatureId);

            // Create or update DocumentSignature record
            $documentSignature = DocumentSignature::updateOrCreate(
                ['approval_request_id' => $approvalRequestId],
                [
                    'digital_signature_id' => $digitalSignatureId,
                    'document_hash' => $signatureData['document_hash'],
                    'signature_value' => $signatureData['signature_value'],
                    'cms_signature' => $signatureData['cms_signature'],
                    'signed_at' => $signatureData['signed_at'],
                    // 'signed_by' => Auth::id(),
                    // sign by kaprodi
                    // 'signed_by' => Auth::guard('kaprodi')->id(),
                    'signature_status' => DocumentSignature::STATUS_SIGNED,
                    'signature_metadata' => $signatureData['metadata']
                ]
            );

            // Update approval request status
            // $approvalRequest->markUserSigned();

            Log::info('Approval request signed successfully', [
                'approval_request_id' => $approvalRequestId,
                'document_signature_id' => $documentSignature->id
            ]);

            return $documentSignature;

        } catch (\Exception $e) {
            Log::error('Approval request signing failed: ' . $e->getMessage());
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
     * Get signature statistics
     */
    public function getSignatureStatistics($digitalSignatureId)
    {
        try {
            $digitalSignature = DigitalSignature::findOrFail($digitalSignatureId);

            return [
                'signature_id' => $digitalSignature->signature_id,
                'created_at' => $digitalSignature->created_at,
                'status' => $digitalSignature->status,
                'valid_until' => $digitalSignature->valid_until,
                'total_documents_signed' => $digitalSignature->documentSignatures()->count(),
                'successful_signatures' => $digitalSignature->documentSignatures()
                    ->where('signature_status', DocumentSignature::STATUS_VERIFIED)->count(),
                'pending_signatures' => $digitalSignature->documentSignatures()
                    ->where('signature_status', DocumentSignature::STATUS_PENDING)->count(),
                'last_used' => $digitalSignature->documentSignatures()
                    ->latest('signed_at')->first()?->signed_at,
                'days_until_expiry' => $digitalSignature->valid_until->diffInDays(now(), false),
                'usage_stats' => $digitalSignature->getUsageStats()
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get signature statistics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Revoke digital signature
     */
    public function revokeSignature($digitalSignatureId, $reason = null)
    {
        try {
            $digitalSignature = DigitalSignature::findOrFail($digitalSignatureId);
            $digitalSignature->revoke($reason);

            // Invalidate all associated document signatures
            $digitalSignature->documentSignatures()->update([
                'signature_status' => DocumentSignature::STATUS_INVALID
            ]);

            Log::info('Digital signature revoked', [
                'signature_id' => $digitalSignature->signature_id,
                'reason' => $reason
            ]);

            SignatureAuditLog::create([
                'kaprodi_id' => Auth::id(),
                'action' => 'revoke_digital_signature',
                'status_to' => $digitalSignature->status,
                'description' => 'Digital signature revoked: ' . ($reason ?? 'No reason provided'),
                'performed_at' => now()
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to revoke signature: ' . $e->getMessage());
            throw $e;
        }
    }
}
