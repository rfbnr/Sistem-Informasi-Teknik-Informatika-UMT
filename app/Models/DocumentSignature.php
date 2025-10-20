<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\ApprovalRequest;
use App\Models\SignatureAuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\SignatureVerificationLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_request_id',
        'digital_signature_id',
        'document_hash',
        'signature_value',
        'signature_metadata',
        'qr_code_path',
        'verification_url',
        'cms_signature',
        'signed_at',
        'signed_by',
        'signature_status',
        'canvas_data_path',
        'positioning_data',
        'final_pdf_path',
        'verification_token',
        'verified_at',
        'verified_by'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'verified_at' => 'datetime',
        'signature_metadata' => 'array',
        'positioning_data' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SIGNED = 'signed';
    const STATUS_VERIFIED = 'verified';
    const STATUS_INVALID = 'invalid';

    public static function boot()
    {
        parent::boot();

        // static::creating(function ($model) {
        //     // Generate verification URL
        //     if (empty($model->verification_url)) {
        //         $encryptedId = Crypt::encryptString($model->approval_request_id . '|' . time());
        //         $model->verification_url = route('signature.verify', ['token' => $encryptedId]);
        //     }

        //     // Generate verification token
        //     if (empty($model->verification_token)) {
        //         $model->verification_token = Str::random(64);
        //     }
        // });

        static::created(function ($model) {
            // Log audit trail
            SignatureAuditLog::create([
                'document_signature_id' => $model->id,
                'approval_request_id' => $model->approval_request_id,
                'user_id' => Auth::id() ?? $model->signed_by,
                'action' => 'signature_initiated',
                'status_to' => $model->signature_status,
                'description' => 'Document signature process initiated',
                'metadata' => [
                    'document_hash' => $model->document_hash,
                    'verification_token' => $model->verification_token
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);
        });
    }

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    /**
     * Relasi ke DigitalSignature
     */
    public function digitalSignature()
    {
        return $this->belongsTo(DigitalSignature::class);
    }

    /**
     * Relasi ke User (signer)
     */
    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Relasi ke User (verifier)
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relasi ke SignatureAuditLog
     */
    public function auditLogs()
    {
        return $this->hasMany(SignatureAuditLog::class);
    }

    /**
     * Relasi ke SignatureVerificationLog
     */
    // public function verificationLogs()
    // {
    //     return $this->hasMany(SignatureVerificationLog::class);
    // }

    /**
     * Generate document hash dari file path
     */
    public static function generateDocumentHash($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        return hash_file('sha256', $filePath);
    }

    /**
     * Create CMS signature dari dokumen
     */
    public function createCMSSignature($documentContent, $privateKey)
    {
        try {
            // Generate hash dari dokumen
            $documentHash = hash('sha256', $documentContent);

            // Create signature dengan RSA-SHA256
            $signature = '';
            if (!openssl_sign($documentHash, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                throw new \Exception('Failed to create digital signature: ' . openssl_error_string());
            }

            // Encode ke base64 untuk storage
            $this->cms_signature = base64_encode($signature);
            $this->signature_value = hash('sha256', $signature); // Hash dari signature sebagai identifier
            $this->signed_at = now();
            $this->signature_status = self::STATUS_SIGNED;

            $this->save();

            // Log audit
            $this->logAudit('document_signed', self::STATUS_PENDING, self::STATUS_SIGNED,
                'Document has been digitally signed');

            return $this->cms_signature;

        } catch (\Exception $e) {
            Log::error('CMS Signature Creation Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify CMS signature
     */
    public function verifyCMSSignature($documentContent, $publicKey)
    {
        try {
            if (!$this->cms_signature) {
                return false;
            }

            // Generate hash dari dokumen
            $documentHash = hash('sha256', $documentContent);

            // Decode signature dari base64
            $signature = base64_decode($this->cms_signature);

            // Verify signature
            $result = openssl_verify($documentHash, $signature, $publicKey, OPENSSL_ALGO_SHA256);

            $isValid = $result === 1;

            // Log verification attempt
            // SignatureVerificationLog::create([
            //     'document_signature_id' => $this->id,
            //     'verification_token' => $this->verification_token,
            //     'ip_address' => request()->ip(),
            //     'user_agent' => request()->userAgent(),
            //     'verification_result' => $isValid,
            //     'verification_details' => $isValid ? 'Signature verification successful' : 'Signature verification failed',
            //     'verification_method' => 'cms_verify',
            //     'verified_at' => now()
            // ]);

            if ($isValid && $this->signature_status !== self::STATUS_VERIFIED) {
                $this->signature_status = self::STATUS_VERIFIED;
                $this->verified_at = now();
                $this->verified_by = Auth::id();
                $this->save();

                $this->logAudit('signature_verified', self::STATUS_SIGNED, self::STATUS_VERIFIED,
                    'Digital signature has been verified');
            }

            return $isValid;

        } catch (\Exception $e) {
            Log::error('CMS Signature Verification Failed: ' . $e->getMessage());

            // Log failed verification
            // SignatureVerificationLog::create([
            //     'document_signature_id' => $this->id,
            //     'verification_token' => $this->verification_token,
            //     'ip_address' => request()->ip(),
            //     'user_agent' => request()->userAgent(),
            //     'verification_result' => false,
            //     'verification_details' => 'Verification error: ' . $e->getMessage(),
            //     'verification_method' => 'cms_verify',
            //     'verified_at' => now()
            // ]);

            return false;
        }
    }

    /**
     * Check if signature is valid
     */
    public function isValid()
    {
        return $this->signature_status === self::STATUS_VERIFIED &&
               $this->digitalSignature->isValid();
    }

    /**
     * Invalidate signature
     */
    public function invalidate($reason = null)
    {
        $oldStatus = $this->signature_status;
        $this->signature_status = self::STATUS_INVALID;
        $this->save();

        $this->logAudit('signature_invalidated', $oldStatus, self::STATUS_INVALID,
            'Signature has been invalidated. Reason: ' . ($reason ?? 'Not specified'));
    }

    /**
     * Generate verification token baru
     */
    public function regenerateVerificationToken()
    {
        $oldToken = $this->verification_token;
        $this->verification_token = Str::random(64);

        // Update verification URL dengan token baru
        $encryptedId = Crypt::encryptString($this->approval_request_id . '|' . time());
        $this->verification_url = route('signature.verify', ['token' => $encryptedId]);

        $this->save();

        $this->logAudit('verification_token_regenerated', null, null,
            'Verification token has been regenerated for security reasons',
            ['old_token_hash' => hash('sha256', $oldToken)]);

        return $this->verification_token;
    }

    /**
     * Get signature info untuk verification display
     */
    public function getSignatureInfo()
    {
        return [
            'document_name' => $this->approvalRequest->document_name,
            'document_number' => $this->approvalRequest->full_document_number,
            'signed_by' => $this->signer->name,
            'signed_at' => $this->signed_at ? $this->signed_at->format('d F Y H:i:s') : null,
            'signature_status' => $this->signature_status,
            'status_label' => $this->getStatusLabel(),
            'is_valid' => $this->isValid(),
            'verification_url' => $this->verification_url,
            'algorithm' => $this->digitalSignature->algorithm,
            'key_length' => $this->digitalSignature->key_length,
            'public_key_fingerprint' => $this->digitalSignature->getPublicKeyFingerprint(),
            // 'verification_count' => $this->verificationLogs()->count(),
            // 'last_verified' => $this->verificationLogs()->latest('verified_at')->first()?->verified_at
        ];
    }

    /**
     * Get status label untuk display
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_PENDING => 'Menunggu Tanda Tangan',
            self::STATUS_SIGNED => 'Sudah Ditandatangani',
            self::STATUS_VERIFIED => 'Terverifikasi',
            self::STATUS_INVALID => 'Tidak Valid'
        ];

        return $labels[$this->signature_status] ?? 'Status Tidak Dikenal';
    }

    /**
     * Get status badge class untuk UI
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_SIGNED => 'badge-info',
            self::STATUS_VERIFIED => 'badge-success',
            self::STATUS_INVALID => 'badge-danger'
        ];

        return $classes[$this->signature_status] ?? 'badge-secondary';
    }

    /**
     * Save canvas data hasil signing
     */
    public function saveCanvasData($canvasDataUrl, $positioningData)
    {
        try {
            // Convert base64 canvas data ke file
            $canvasData = str_replace('data:image/png;base64,', '', $canvasDataUrl);
            $canvasData = base64_decode($canvasData);

            $filename = 'canvas_data_' . $this->id . '_' . time() . '.png';
            $path = 'signature_canvas/' . $filename;

            Storage::disk('public')->put($path, $canvasData);

            $this->canvas_data_path = $path;
            $this->positioning_data = $positioningData;
            $this->save();

            return $path;

        } catch (\Exception $e) {
            Log::error('Failed to save canvas data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate final PDF dengan signature
     */
    public function generateFinalPDF($templatePath)
    {
        try {
            // Logic untuk merge original PDF dengan signature canvas
            // Implementasi tergantung pada library PDF yang digunakan (TCPDF, DomPDF, etc)

            $originalPdf = $this->approvalRequest->document_path;
            $canvasImage = $this->canvas_data_path;

            // Placeholder untuk PDF processing
            $finalPdfPath = 'signed_documents/final_' . $this->id . '_' . time() . '.pdf';

            // TODO: Implement actual PDF merging logic

            $this->final_pdf_path = $finalPdfPath;
            $this->save();

            return $finalPdfPath;

        } catch (\Exception $e) {
            Log::error('Failed to generate final PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log audit trail
     */
    private function logAudit($action, $statusFrom = null, $statusTo = null, $description = '', $metadata = [])
    {
        SignatureAuditLog::create([
            'document_signature_id' => $this->id,
            'approval_request_id' => $this->approval_request_id,
            'user_id' => Auth::id() ?? $this->signed_by,
            'action' => $action,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'description' => $description,
            'metadata' => array_merge($metadata, [
                'signature_id' => $this->digitalSignature->signature_id
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now()
        ]);
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('signature_status', $status);
    }

    public function scopeValid($query)
    {
        return $query->where('signature_status', self::STATUS_VERIFIED);
    }

    public function scopePendingSigning($query)
    {
        return $query->where('signature_status', self::STATUS_PENDING);
    }

    public function scopeRecentlyVerified($query, $days = 7)
    {
        return $query->where('signature_status', self::STATUS_VERIFIED)
                    ->where('verified_at', '>=', now()->subDays($days));
    }
};
