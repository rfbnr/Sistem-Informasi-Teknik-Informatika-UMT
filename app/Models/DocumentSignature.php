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
        'document_hash',
        'signature_value',
        'signature_metadata',
        'temporary_qr_code_path', // temporary QR for drag & drop
        'qr_code_path', // Final QR after signing
        'verification_url',
        'cms_signature',
        'signature_format', // Track signature format (legacy_hash_only or pkcs7_cms_detached)
        'signed_at',
        'signed_by',
        'invalidated_reason',
        'invalidated_at',
        'signature_status',
        'qr_positioning_data', // RENAMED: from positioning_data
        'final_pdf_path',
        'verification_token',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'signature_metadata' => 'array',
        'qr_positioning_data' => 'array', // RENAMED: from positioning_data
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SIGNED = 'signed';
    const STATUS_VERIFIED = 'verified';
    // const STATUS_REJECTED = 'rejected';
    const STATUS_INVALID = 'invalid';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate verification token
            if (empty($model->verification_token)) {
                $model->verification_token = Str::random(64);
            }
        });

        static::created(function ($model) {
            // Log audit trail with standardized metadata
            $metadata = SignatureAuditLog::createMetadata([
                'document_hash' => $model->document_hash,
                'verification_token' => substr($model->verification_token, 0, 20) . '...', // Partial for security
                'approval_request_id' => $model->approval_request_id,
                'signature_method' => $model->signature_method ?? 'digital',
                'initiated_by' => Auth::user()->name ?? 'System',
            ]);

            SignatureAuditLog::create([
                'document_signature_id' => $model->id,
                'approval_request_id' => $model->approval_request_id,
                'kaprodi_id' => Auth::id(),
                'action' => SignatureAuditLog::ACTION_SIGNATURE_INITIATED,
                'status_to' => $model->signature_status,
                'description' => 'Document signature process initiated',
                'metadata' => $metadata,
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
     * REFACTORED: 1-to-1 relationship with DigitalSignature
     * Each document has its own unique key
     */
    public function digitalSignature()
    {
        return $this->hasOne(DigitalSignature::class, 'document_signature_id');
        // return $this->belongsTo(DigitalSignature::class);
    }

    /**
     * Relasi ke Kaprodi (signer)
     */
    public function signer()
    {
        return $this->belongsTo(Kaprodi::class, 'signed_by');
    }

    /**
     * Relasi ke Kaprodi (rejector)
     */
    // public function rejector()
    // {
    //     return $this->belongsTo(Kaprodi::class, 'rejected_by');
    // }

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
    public function verificationLogs()
    {
        return $this->hasMany(SignatureVerificationLog::class);
    }

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
     * Check if signature is valid
     */
    //! DIPAKAI
    public function isValid()
    {
        return $this->signature_status === self::STATUS_VERIFIED &&
               $this->digitalSignature->isValid();
    }

    /**
     * Invalidate signature
     */
    //! DIPAKAI DI CONTROLLER DocumentSignatureController method invalidate
    public function invalidate($reason = null)
    {
        $oldStatus = $this->signature_status;
        $this->signature_status = self::STATUS_INVALID;
        $this->invalidated_reason = $reason;
        $this->invalidated_at = now();
        $this->save();

        $this->approvalRequest->invalidateSignature();

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
    //! DIPAKAI DI DocumentSignatureController method show and quickPreview
    public function getSignatureInfo()
    {
        return [
            'document_name' => $this->approvalRequest->document_name,
            'document_type' => $this->approvalRequest->document_type,
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
            'verification_count' => $this->verificationLogs()->count(),
            // 'last_verified' => $this->verificationLogs()->latest('verified_at')->first()?->verified_at
        ];
    }

    /**
     * Get status label untuk display
     */
    //! DIPAKAI
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_PENDING => 'Menunggu Tanda Tangan',
            self::STATUS_SIGNED => 'Sudah Ditandatangani',
            self::STATUS_VERIFIED => 'Terverifikasi',
            // self::STATUS_REJECTED => 'Ditolak',
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
            // self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_INVALID => 'badge-secondary'
        ];

        return $classes[$this->signature_status] ?? 'badge-secondary';
    }

    /**
     * NEW: Generate temporary QR code for drag & drop positioning
     * Called when Kaprodi approves document
     */
    //! DIPAKAI DI APPROVALREQUEST MODEL
    public function generateTemporaryQRCode()
    {
        try {
            // Generate dummy/temporary text for QR
            $tempData = "TEMP_QR_DOC_{$this->id}_" . now()->timestamp;

            // Use QRCodeService to generate
            $qrCodeService = app(\App\Services\QRCodeService::class);

            // Generate simple QR code
            $qrCode = \Endroid\QrCode\QrCode::create($tempData)
                ->setSize(300)
                ->setMargin(10);

            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            // Save to storage
            $filename = 'temp_qr_' . $this->id . '_' . time() . '.png';
            $path = 'temp-qrcodes/' . $filename;

            Storage::disk('public')->put($path, $result->getString());

            // Update record
            $this->temporary_qr_code_path = $path;
            $this->save();

            Log::info('Temporary QR code generated', [
                'document_signature_id' => $this->id,
                'temp_qr_path' => $path
            ]);

            return $path;

        } catch (\Exception $e) {
            Log::error('Failed to generate temporary QR code: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * NEW: Clear temporary QR code after finalization
     */
    public function clearTemporaryQRCode()
    {
        if ($this->temporary_qr_code_path && Storage::disk('public')->exists($this->temporary_qr_code_path)) {
            Storage::disk('public')->delete($this->temporary_qr_code_path);
            $this->temporary_qr_code_path = null;
            $this->save();

            Log::info('Temporary QR code cleared', [
                'document_signature_id' => $this->id
            ]);
        }
    }

    /**
     * NEW: Save QR positioning data from user drag & drop
     */
    //! DIPAKAI DI CONTROLLER DIGITALSIGNATURE
    public function saveQRPositioning($positioningData)
    {
        try {
            $this->qr_positioning_data = $positioningData;
            $this->save();

            Log::info('QR positioning saved', [
                'document_signature_id' => $this->id,
                'positioning' => $positioningData
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to save QR positioning: ' . $e->getMessage());
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
            // $canvasImage = $this->canvas_data_path;

            // Placeholder untuk PDF processing
            $finalPdfPath = 'signed_documents/final_' . $this->id . '_' . time() . '.pdf';

            // TODO: Implement actual PDF merging logic

            $this->final_pdf_path = $finalPdfPath;
            $this->save();

            return $finalPdfPath;

        } catch (\Exception $e) {
            $this->logAudit('final_pdf_generation_failed', null, null,
                'Failed to generate final PDF with signature: ' . $e->getMessage());
            Log::error('Failed to generate final PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log audit trail with standardized metadata
     */
    private function logAudit($action, $statusFrom = null, $statusTo = null, $description = '', $metadata = [])
    {
        // Merge with standardized metadata
        $enhancedMetadata = SignatureAuditLog::createMetadata(array_merge($metadata, [
            'signature_id' => $this->digitalSignature->signature_id ?? null,
            'document_signature_id' => $this->id,
            'signature_status' => $this->signature_status,
            'status_transition' => $statusFrom ? "{$statusFrom} → {$statusTo}" : $statusTo,
            'signed_by' => $this->signer->name ?? null,
            // 'verified_by' => $this->verifier->name ?? null,
        ]));

        SignatureAuditLog::create([
            'document_signature_id' => $this->id,
            'approval_request_id' => $this->approval_request_id,
            'user_id' => $this->signed_by,
            'kaprodi_id' => $this->verified_by ?? $this->rejected_by,
            'action' => $action,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'description' => $description,
            'metadata' => $enhancedMetadata,
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

    public function scopeInvalidated($query)
    {
        return $query->where('signature_status', self::STATUS_INVALID);
    }
};
