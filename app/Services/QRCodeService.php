<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Logo\Logo;
use App\Models\ApprovalRequest;
use Endroid\QrCode\Label\Label;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use App\Models\VerificationCodeMapping;
use Endroid\QrCode\Label\Font\NotoSans;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    private $defaultSize = 300;
    private $defaultMargin = 10;
    private $defaultFormat = 'png';

    /**
     * Generate QR Code untuk verification URL
     */
    public function generateVerificationQR($documentSignatureId, $options = [])
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($documentSignatureId);
            $approvalRequest = $documentSignature->approvalRequest;

            // Create verification URL dengan encrypted parameters
            $encryptedData = $this->createEncryptedVerificationData($documentSignature);

            // $verificationToken = $encryptedData;

            $verificationUrl = route('signature.verify', ['token' => $encryptedData]);

            // Generate QR Code
            $qrCode = new QrCode($verificationUrl);
            $qrCode->setSize($options['size'] ?? $this->defaultSize);
            $qrCode->setMargin($options['margin'] ?? $this->defaultMargin);

            // Add logo jika ada
            $logo = null;
            if ($options['add_logo'] ?? true) {
                $logoPath = public_path('assets/logo.JPG');
                if (File::exists($logoPath)) {
                    $logo = new Logo($logoPath);
                    $logo->setResizeToWidth(70);
                }
            }

            // Add label
            $label = null;
            if ($options['add_label'] ?? true) {
                $label = new Label(
                    'Scan untuk verifikasi tanda tangan digital',
                    new NotoSans(12)
                    // null,
                    // null,
                    // null,
                    // 12
                );
            }

            // Generate QR code image
            $writer = new PngWriter();
            $result = $writer->write($qrCode, $logo, $label);

            // Save QR code image
            $qrCodePath = $this->saveQRCodeImage($result->getString(), $documentSignatureId);

            // Update document signature dengan QR code path
            $documentSignature->update(['qr_code_path' => $qrCodePath]);

            Log::info('QR Code generated successfully', [
                'document_signature_id' => $documentSignatureId,
                'qr_code_path' => $qrCodePath,
                'verification_url' => $verificationUrl
            ]);

            return [
                'qr_code_path' => $qrCodePath,
                'qr_code_url' => Storage::url($qrCodePath),
                'verification_url' => $verificationUrl,
                // 'verification_token' => $verificationToken,
                'size' => $qrCode->getSize(),
                'format' => 'png'
            ];

        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR Code dalam format SVG
     */
    public function generateSVGQRCode($documentSignatureId, $options = [])
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($documentSignatureId);

            // Create verification URL
            $encryptedData = $this->createEncryptedVerificationData($documentSignature);
            $verificationUrl = route('signature.verify', ['token' => $encryptedData]);

            // Generate QR Code
            $qrCode = new QrCode($verificationUrl);
            $qrCode->setSize($options['size'] ?? $this->defaultSize);
            $qrCode->setMargin($options['margin'] ?? $this->defaultMargin);

            // Generate SVG
            $writer = new SvgWriter();
            $result = $writer->write($qrCode);

            // Save SVG file
            $svgPath = $this->saveSVGQRCode($result->getString(), $documentSignatureId);

            return [
                'svg_path' => $svgPath,
                'svg_url' => Storage::url($svgPath),
                'svg_content' => $result->getString(),
                'verification_url' => $verificationUrl
            ];

        } catch (\Exception $e) {
            Log::error('SVG QR Code generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR Code untuk canvas positioning
     */
    public function generateCanvasQRCode($documentSignatureId, $canvasOptions = [])
    {
        try {
            $documentSignature = DocumentSignature::findOrFail($documentSignatureId);

            // Canvas-specific options
            $size = $canvasOptions['qr_size'] ?? 150;
            $position = $canvasOptions['qr_position'] ?? ['x' => 50, 'y' => 50];

            // Generate QR code
            $qrData = $this->generateVerificationQR($documentSignatureId, [
                'size' => $size,
                'margin' => 10,
                'add_logo' => $canvasOptions['add_logo'] ?? false,
                'add_label' => false // No label untuk canvas
            ]);

            return [
                'qr_code_data' => $qrData,
                'position' => $position,
                'size' => $size,
                'canvas_ready' => true
            ];

        } catch (\Exception $e) {
            Log::error('Canvas QR Code generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create encrypted verification data with short code mapping
     *
     * HYBRID APPROACH:
     * 1. Maintain full encryption (existing security)
     * 2. Create short code mapping (improved usability)
     * 3. Return short code for QR/URL
     */
    private function createEncryptedVerificationData($documentSignature)
    {
        // ═══════════════════════════════════════════════════════════════════
        // STEP 0: Get DigitalSignature & Calculate Dynamic Expiration
        // ═══════════════════════════════════════════════════════════════════
        // $digitalSignature = $documentSignature->digitalSignature;

        // if (!$digitalSignature) {
        //     Log::error('Digital signature not found for document signature', [
        //         'document_signature_id' => $documentSignature->id
        //     ]);
        //     throw new \Exception('Digital signature not found. Cannot create verification QR code.');
        // }

        // // Validate digital signature status
        // if ($digitalSignature->status === 'revoked') {
        //     Log::warning('Attempting to create QR for revoked digital signature', [
        //         'digital_signature_id' => $digitalSignature->id,
        //         'revoked_at' => $digitalSignature->revoked_at,
        //         'revocation_reason' => $digitalSignature->revocation_reason
        //     ]);
        //     throw new \Exception('Cannot create QR code: Digital signature has been revoked.');
        // }

        // if ($digitalSignature->valid_until < now()) {
        //     Log::warning('Attempting to create QR for expired digital signature', [
        //         'digital_signature_id' => $digitalSignature->id,
        //         'expired_at' => $digitalSignature->valid_until
        //     ]);
        //     throw new \Exception('Cannot create QR code: Digital signature has expired.');
        // }

        // Calculate dynamic expiration: minimum of signature validity or 5 years
        // $signatureExpiry = $digitalSignature->valid_until;
        $defaultExpiry = now()->addYears(3);

        // Use the earlier date (minimum)
        // $expiresAt = $signatureExpiry < $defaultExpiry
        //     ? $signatureExpiry
        //     : $defaultExpiry;
        $expiresAt = $defaultExpiry;

        // Ensure expiration is not in the past
        if ($expiresAt < now()) {
            Log::error('Calculated expiration is in the past', [
                'expires_at' => $expiresAt->toDateTimeString(),
                // 'digital_signature_id' => $digitalSignature->id
            ]);
            throw new \Exception('Cannot create QR code: Calculated expiration date is in the past.');
        }

        Log::info('QR code expiration calculated dynamically', [
            'document_signature_id' => $documentSignature->id,
            // 'digital_signature_id' => $digitalSignature->id,
            // 'signature_expiry' => $signatureExpiry->toDateTimeString(),
            'default_expiry' => $defaultExpiry->toDateTimeString(),
            'chosen_expiry' => $expiresAt->toDateTimeString(),
            // 'expiry_reason' => $signatureExpiry < $defaultExpiry ? 'signature_validity' : 'default_cap',
            'days_until_expiry' => now()->diffInDays($expiresAt)
        ]);

        // ═══════════════════════════════════════════════════════════════════
        // STEP 1: Create full encrypted payload with DYNAMIC expiration
        // ═══════════════════════════════════════════════════════════════════
        $verificationData = [
            'document_signature_id' => $documentSignature->id,
            'approval_request_id' => $documentSignature->approval_request_id,
            'verification_token' => $documentSignature->verification_token,
            'created_at' => now()->timestamp,
            'expires_at' => $expiresAt->timestamp // ✅ DYNAMIC from DigitalSignature!
        ];

        // Full encryption - TETAP SAMA seperti sebelumnya!
        $encryptedPayload = Crypt::encryptString(json_encode($verificationData));

        Log::info('Created encrypted verification payload', [
            'document_signature_id' => $documentSignature->id,
            'payload_length' => strlen($encryptedPayload),
            'expires_at' => $expiresAt->toDateTimeString()
        ]);

        // ═══════════════════════════════════════════════════════════════════
        // STEP 2: Create short code mapping with DYNAMIC expiration
        // ═══════════════════════════════════════════════════════════════════
        try {
            $mapping = VerificationCodeMapping::createMapping(
                $encryptedPayload,
                $documentSignature->id,
                $expiresAt // ✅ Pass Carbon instance with dynamic expiration!
            );

            Log::info('Short code mapping created successfully', [
                'short_code' => $mapping->short_code,
                'document_signature_id' => $documentSignature->id,
                'expires_at' => $mapping->expires_at->toDateTimeString(),
                'url_length_before' => strlen($encryptedPayload),
                'url_length_after' => strlen($mapping->short_code),
                'reduction_percentage' => round((1 - strlen($mapping->short_code) / strlen($encryptedPayload)) * 100, 2)
            ]);

            // Return short code instead of full encrypted string
            return $mapping->short_code;

        } catch (\Exception $e) {
            Log::error('Failed to create short code mapping', [
                'error' => $e->getMessage(),
                'document_signature_id' => $documentSignature->id
            ]);

            // Fallback: return full encrypted payload if mapping fails
            Log::warning('Falling back to full encrypted token in URL');
            return $encryptedPayload;
        }
    }

    /**
     * Decrypt verification data dari QR code
     *
     * HYBRID APPROACH:
     * 1. Check if input is short code or full encrypted string
     * 2. If short code: lookup mapping and get encrypted payload
     * 3. Decrypt payload (same as before)
     * 4. Track access (bonus feature!)
     */
    public function decryptVerificationData($token)
    {
        try {
            $encryptedPayload = null;
            $isShortCode = false;

            // ═══════════════════════════════════════════════════════════════════
            // STEP 1: Determine if token is short code or full encrypted string
            // ═══════════════════════════════════════════════════════════════════

            // Short code pattern: XXXX-XXXX-XXXX (12-20 chars with dashes)
            // Encrypted string: eyJpdiI6... (100+ chars, base64)
            if (strlen($token) <= 30 && strpos($token, '-') !== false) {
                // This looks like a short code
                $isShortCode = true;

                Log::info('Processing short code verification', [
                    'short_code' => $token,
                    'token_length' => strlen($token)
                ]);

                // ═══════════════════════════════════════════════════════════════════
                // STEP 2: Lookup mapping table
                // ═══════════════════════════════════════════════════════════════════
                $mapping = VerificationCodeMapping::findByShortCode($token);

                // Track access (audit trail + analytics)
                $mapping->trackAccess();

                // Check rate limiting (security)
                if ($mapping->shouldRateLimit(10)) {
                    throw new \Exception('Too many verification attempts. Please try again later.');
                }

                // Get encrypted payload from mapping
                $encryptedPayload = $mapping->encrypted_payload;

                Log::info('Short code mapping found and validated', [
                    'short_code' => $token,
                    'document_signature_id' => $mapping->document_signature_id,
                    'access_count' => $mapping->access_count
                ]);

            } else {
                // This looks like full encrypted string (backward compatibility)
                $isShortCode = false;
                $encryptedPayload = $token;

                Log::info('Processing full encrypted token (legacy format)', [
                    'token_length' => strlen($token)
                ]);
            }

            // ═══════════════════════════════════════════════════════════════════
            // STEP 3: Decrypt payload (SAME AS BEFORE - UNCHANGED!)
            // ═══════════════════════════════════════════════════════════════════
            $decryptedJson = Crypt::decryptString($encryptedPayload);
            $data = json_decode($decryptedJson, true);

            if (!$data) {
                throw new \Exception('Failed to decode verification data');
            }

            // Check expiration
            if ($data['expires_at'] < now()->timestamp) {
                throw new \Exception('QR Code has expired');
            }

            Log::info('Verification data decrypted successfully', [
                'document_signature_id' => $data['document_signature_id'],
                'is_short_code' => $isShortCode
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::warning('QR Code verification data decryption failed', [
                'error' => $e->getMessage(),
                'token_preview' => substr($token, 0, 20) . '...'
            ]);

            throw new \Exception('Invalid or expired QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Save QR code image
     */
    private function saveQRCodeImage($imageData, $documentSignatureId)
    {
        $directory = 'qrcodes/document-signatures';
        $filename = 'qr_' . $documentSignatureId . '_' . time() . '.png';
        $fullPath = $directory . '/' . $filename;

        // Ensure directory exists
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Save image
        Storage::disk('public')->put($fullPath, $imageData);

        return $fullPath;
    }

    /**
     * Save SVG QR code
     */
    private function saveSVGQRCode($svgData, $documentSignatureId)
    {
        $directory = 'qrcodes/svg';
        $filename = 'qr_' . $documentSignatureId . '_' . time() . '.svg';
        $fullPath = $directory . '/' . $filename;

        // Ensure directory exists
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Save SVG
        Storage::disk('public')->put($fullPath, $svgData);

        return $fullPath;
    }

    /**
     * Generate batch QR codes untuk multiple documents
     */
    public function generateBatchQRCodes($documentSignatureIds, $options = [])
    {
        $results = [];
        $errors = [];

        foreach ($documentSignatureIds as $id) {
            try {
                $qrData = $this->generateVerificationQR($id, $options);
                $results[$id] = $qrData;
            } catch (\Exception $e) {
                $errors[$id] = $e->getMessage();
                Log::error("Batch QR generation failed for document signature {$id}: " . $e->getMessage());
            }
        }

        return [
            'successful' => $results,
            'failed' => $errors,
            'total_processed' => count($documentSignatureIds),
            'success_count' => count($results),
            'error_count' => count($errors)
        ];
    }

    /**
     * Verify QR code data
     */
    public function verifyQRCode($encryptedToken)
    {
        try {
            $verificationData = $this->decryptVerificationData($encryptedToken);

            // dd($verificationData);

            $documentSignature = DocumentSignature::findOrFail($verificationData['document_signature_id']);
            $approvalRequest = $documentSignature->approvalRequest;

            // Verify Approval Request ID match
            if ($documentSignature->approval_request_id !== $verificationData['approval_request_id']) {
                throw new \Exception('Approval Request ID mismatch');
            }

            // Verify Document Signature ID match
            if ($documentSignature->id !== $verificationData['document_signature_id']) {
                throw new \Exception('Document Signature ID mismatch');
            }

            // Verify existence
            if (!$documentSignature || !$approvalRequest) {
                throw new \Exception('Document or Approval Request not found');
            }

            // Verify Signature Status Verified & Approval Request Sign Approved
            if ($documentSignature->signature_status !== 'verified' ||
                $approvalRequest->status !== 'sign_approved') {
                throw new \Exception('Document is not verified or approval request not approved for signing');
            }

            // Verify token match
            if ($documentSignature->verification_token !== $verificationData['verification_token']) {
                throw new \Exception('Invalid verification token');
            }

            return [
                'is_valid' => true,
                'document_signature' => $documentSignature,
                'approval_request' => $approvalRequest,
                'verification_data' => $verificationData,
                'verified_at' => now()
            ];

        } catch (\Exception $e) {
            Log::warning('QR Code verification failed: ' . $e->getMessage());
            return [
                'is_valid' => false,
                'error_message' => $e->getMessage(),
                'verified_at' => now()
            ];
        }
    }

    /**
     * Get QR code positioning data untuk canvas
     */
    // public function getQRPositioningData($templateId = null)
    // {
    //     // Default positioning
    //     $defaultPositioning = [
    //         'qr_position' => ['x' => 50, 'y' => 50],
    //         'qr_size' => 150,
    //         'qr_style' => [
    //             'border' => true,
    //             'border_color' => '#000000',
    //             'border_width' => 2,
    //             'background' => '#ffffff'
    //         ]
    //     ];

    //     if ($templateId) {
    //         // Get positioning dari template jika ada
    //         try {
    //             $template = \App\Models\SignatureTemplate::find($templateId);
    //             if ($template && isset($template->layout_config['barcode_position'])) {
    //                 $barcodePos = $template->layout_config['barcode_position'];
    //                 return [
    //                     'qr_position' => ['x' => $barcodePos['x'], 'y' => $barcodePos['y']],
    //                     'qr_size' => min($barcodePos['width'], $barcodePos['height']),
    //                     'qr_style' => $template->style_config['qr_code'] ?? $defaultPositioning['qr_style']
    //                 ];
    //             }
    //         } catch (\Exception $e) {
    //             Log::warning('Failed to get template positioning data: ' . $e->getMessage());
    //         }
    //     }

    //     return $defaultPositioning;
    // }

    /**
     * Generate QR code untuk email attachment
     */
    public function generateEmailQRCode($documentSignatureId, $emailOptions = [])
    {
        try {
            $qrData = $this->generateVerificationQR($documentSignatureId, [
                'size' => $emailOptions['size'] ?? 200,
                'margin' => $emailOptions['margin'] ?? 15,
                'add_logo' => $emailOptions['add_logo'] ?? true,
                'add_label' => $emailOptions['add_label'] ?? true
            ]);

            // Generate additional email-specific data
            $documentSignature = DocumentSignature::findOrFail($documentSignatureId);
            $approvalRequest = $documentSignature->approvalRequest;

            return array_merge($qrData, [
                'email_subject' => "Verifikasi Tanda Tangan Digital - {$approvalRequest->document_name}",
                'email_message' => "Dokumen '{$approvalRequest->document_name}' telah ditandatangani secara digital. " .
                                 "Scan QR code atau kunjungi link berikut untuk verifikasi: {$qrData['verification_url']}",
                'document_info' => [
                    'name' => $approvalRequest->document_name,
                    'number' => $approvalRequest->full_document_number,
                    'signed_by' => $documentSignature->signer->name,
                    'signed_at' => $documentSignature->signed_at->format('d F Y H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Email QR Code generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean up old QR code files
     */
    public function cleanupOldQRCodes($daysOld = 30)
    {
        try {
            $cutoffDate = now()->subDays($daysOld);
            $directory = 'qrcodes';

            $files = Storage::disk('public')->allFiles($directory);
            $deletedCount = 0;

            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);
                if ($lastModified < $cutoffDate->timestamp) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }

            Log::info("QR Code cleanup completed", [
                'deleted_files' => $deletedCount,
                'cutoff_date' => $cutoffDate->toDateString()
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('QR Code cleanup failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
