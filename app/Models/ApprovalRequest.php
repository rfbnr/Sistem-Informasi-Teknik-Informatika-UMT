<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_name',
        'document_path',
        'signed_document_path',
        'notes',
        'status',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'user_signed_at',
        'sign_approved_at',
        'sign_approved_by',
        'approval_notes',
        'rejection_reason',
        'document_type',
        'workflow_metadata',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'user_signed_at' => 'datetime',
        'sign_approved_at' => 'datetime',
        'workflow_metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_USER_SIGNED = 'user_signed';
    const STATUS_SIGN_APPROVED = 'sign_approved';
    const STATUS_INVALID_SIGN = 'invalid_sign';
    const STATUS_REJECTED = 'rejected';
    // const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot method untuk auto-generate nomor
     */
    public static function boot()
    {
        parent::boot();

        // static::creating(function ($model) {
        //     // Auto-generate nomor jika belum diisi
        //     if (empty($model->nomor)) {
        //         $currentYear = now()->year;
        //         $countThisYear = self::whereYear('created_at', $currentYear)->count() + 1;
        //         $model->nomor = str_pad($countThisYear, 4, '0', STR_PAD_LEFT);
        //     }
        // });

        static::created(function ($model) {
            // Log audit trail untuk request creation with standardized metadata
            $metadata = SignatureAuditLog::createMetadata([
                'document_name' => $model->document_name,
                'requester' => $model->user->name ?? 'Unknown',
                'document_type' => $model->document_type ?? 'general',
            ]);

            SignatureAuditLog::create([
                'approval_request_id' => $model->id,
                'user_id' => $model->user_id,
                'action' => SignatureAuditLog::ACTION_SIGNATURE_INITIATED,
                'status_to' => $model->status,
                'description' => "Approval request '{$model->document_name}' has been created",
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now()
            ]);
        });
    }

    /**
     * Relasi ke User (pemohon)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Kaprodi (approver)
     */
    public function approver()
    {
        return $this->belongsTo(Kaprodi::class, 'approved_by');
    }

    /**
     * Relasi ke Kaprodi (sign approver)
     */
    public function signApprover()
    {
        return $this->belongsTo(Kaprodi::class, 'sign_approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(Kaprodi::class, 'rejected_by');
    }

    public function digitalSignature()
    {
        return $this->hasOneThrough(
            DigitalSignature::class,
            DocumentSignature::class,
            'approval_request_id', // Foreign key on DocumentSignature table
            'id',                 // Foreign key on DigitalSignature table
            'id',                 // Local key on ApprovalRequest table
            'digital_signature_id'// Local key on DocumentSignature table
        );
    }

    /**
     * Relasi ke DocumentSignature
     */
    public function documentSignature()
    {
        return $this->hasOne(DocumentSignature::class);
    }

    /**
     * Relasi ke SignatureAuditLog
     */
    public function auditLogs()
    {
        return $this->hasMany(SignatureAuditLog::class);
    }

    /**
     * Check apakah dokumen sudah ditandatangani
     */
    public function isSigned()
    {
        return in_array($this->status, [
            self::STATUS_USER_SIGNED,
            self::STATUS_SIGN_APPROVED
        ]);
    }

    /**
     * Check apakah sudah selesai (final)
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_SIGN_APPROVED;
    }

    /**
     * Check apakah dokumen ditolak
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check apakah dokument tidak valid (invalidated)
     */
    public function isInvalidated()
    {
        return $this->status === self::STATUS_INVALID_SIGN;
    }

    /**
     * Check apakah bisa ditandatangani user
     */
    //! DIPAKAI DI CONTROLLER DIGITAL SIGNATURE
    public function canBeSignedByUser()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check apakah bisa diapprove sign
     */
    public function canBeSignApproved()
    {
        return $this->status === self::STATUS_USER_SIGNED;
    }

    /**
     * Approve dokumen (admin/kaprodi)
     */
    //! DIPAKAI DI CONTROLLER
    public function approveApprovalRequest($approverId, $notes = null)
    {
        $oldStatus = $this->status;

        // ✅ SECURITY FIX: Generate document hash at approval time
        $documentPath = Storage::disk('public')->path($this->document_path);

        if (!file_exists($documentPath)) {
            throw new \Exception("Document file not found: {$this->document_path}");
        }

        $documentHash = hash_file('sha256', $documentPath);

        if (!$documentHash) {
            throw new \Exception("Failed to generate document hash");
        }

        // Store hash in workflow_metadata
        $workflowMetadata = $this->workflow_metadata ?? [];
        $workflowMetadata['document_hash'] = $documentHash;
        $workflowMetadata['hash_generated_at'] = now()->toIso8601String();
        $workflowMetadata['hash_algorithm'] = 'sha256';
        $workflowMetadata['document_size_bytes'] = filesize($documentPath);

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $approverId,
            'approval_notes' => $notes,
            'workflow_metadata' => $workflowMetadata
        ]);

        Log::info('Document hash generated at approval', [
            'approval_request_id' => $this->id,
            'document_hash' => $documentHash,
            'document_size' => filesize($documentPath)
        ]);

        // FIX #7: Create DocumentSignature record dan return hasilnya
        $documentSignature = $this->createDocumentSignature();

        // Log audit
        $this->logStatusChange('approved', $oldStatus, self::STATUS_APPROVED,
            'Document has been approved for signing', [
                'notes' => $notes,
                'document_hash' => $documentHash
            ]);

        return $documentSignature;
    }

    /**
     * Reject dokumen
     */
    //! DIPAKAI DI CONTROLLER ApprovalRequestController method reject
    public function reject($reason = null, $rejectedBy = null)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'rejected_by' => $rejectedBy ?? Auth::id(),
            'rejected_at' => now()
        ]);

        // Log audit
        $this->logStatusChange('rejected', $oldStatus, self::STATUS_REJECTED,
            'Document has been rejected', ['reason' => $reason]);
    }

    /**
     * Mark sebagai sudah ditandatangani user
     * REFACTORED: Auto-approve signature after user signs (no manual kaprodi verification)
     */
    //! DIPAKAI DI CONTROLLER DIGITAL SIGNATURE
    public function markUserSigned($signPath)
    {
        $oldStatus = $this->status;

        $this->update([
            // 'status' => self::STATUS_USER_SIGNED,  // Old flow: manual verify needed
            'status' => self::STATUS_SIGN_APPROVED,    // New flow: auto-approved
            'user_signed_at' => now(),
            'sign_approved_at' => now(),               // Auto-approved at same time
            'sign_approved_by' => $this->approved_by,  // Same kaprodi who approved
            'signed_document_path' => $signPath,
        ]);

        // FIXED: Log audit with correct final status (SIGN_APPROVED, not USER_SIGNED)
        $this->logStatusChange(
            'user_signed_auto_approved',
            $oldStatus,
            self::STATUS_SIGN_APPROVED,  // Fixed: Use actual final status
            'Document has been digitally signed by user and automatically approved by system'
        );
    }

    /**
     * Mark signature as invalidated
     */
    public function invalidateSignature()
    {
        $oldStatus = $this->status;
        $this->update([
            'status' => self::STATUS_INVALID_SIGN,
        ]);
    }

    /**
     * Approve tanda tangan
     */
    public function approveSignature($signPath)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_SIGN_APPROVED,
            'user_signed_at' => now(),
            'sign_approved_at' => now(),
            'sign_approved_by' => $this->approved_by,
            'signed_document_path' => $signPath,
            // 'approval_notes' => $notes
        ]);

        // Log audit
        // $this->logStatusChange('signature_approved', $oldStatus, self::STATUS_SIGN_APPROVED,
        //     'Digital signature has been approved and verified', ['notes' => $notes]);
        $this->logStatusChange('signature_approved', $oldStatus, self::STATUS_SIGN_APPROVED,
            'Digital signature has been approved and verified');
    }

    /**
     * Cancel approval request
     */
    // public function cancel($reason = null)
    // {
    //     $oldStatus = $this->status;

    //     $this->update([
    //         'status' => self::STATUS_CANCELLED,
    //         'rejection_reason' => $reason
    //     ]);

    //     // Log audit
    //     $this->logStatusChange('cancelled', $oldStatus, self::STATUS_CANCELLED,
    //         'Approval request has been cancelled', ['reason' => $reason]);
    // }

    /**
     * REFACTORED: Create DocumentSignature record with temporary QR code
     * Digital signature key will be auto-generated during signing (not now)
     */
    //! DIPAKAI DI APPROVE METHOD
    private function createDocumentSignature()
    {
        if ($this->documentSignature) {
            return $this->documentSignature;
        }

        try {
            // Create DocumentSignature with pending status
            // Digital signature key will be generated later during signing
            $documentSignature = DocumentSignature::create([
                'approval_request_id' => $this->id,
                // 'digital_signature_id' => null, // Will be auto-generated during signing
                // 'document_hash' => null, // Will be generated during signing
                'signature_status' => DocumentSignature::STATUS_PENDING
            ]);

            // Auto-generate temporary QR code for drag & drop positioning
            try {
                $documentSignature->generateTemporaryQRCode();

                Log::info('Temporary QR code generated for DocumentSignature', [
                    'document_signature_id' => $documentSignature->id,
                    'approval_request_id' => $this->id,
                    'temporary_qr_path' => $documentSignature->temporary_qr_code_path
                ]);
            } catch (\Exception $qrException) {
                // Log QR generation failure but don't fail the entire process
                Log::error('Temporary QR code generation failed', [
                    'document_signature_id' => $documentSignature->id,
                    'error' => $qrException->getMessage()
                ]);
            }

            $this->logStatusChange('document_signature_created', null, null,
                'DocumentSignature created with temporary QR code for positioning', [
                    'document_signature_id' => $documentSignature->id
                ]);

            return $documentSignature;

        } catch (\Exception $e) {
            $this->logStatusChange('signature_creation_failed', null, null,
                'Failed to create document signature: ' . $e->getMessage());

            throw new \Exception('Failed to create document signature: ' . $e->getMessage());
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui - Siap Ditandatangani',
            self::STATUS_USER_SIGNED => 'Sudah Ditandatangani - Menunggu Verifikasi',
            self::STATUS_SIGN_APPROVED => 'Selesai - Tanda Tangan Terverifikasi',
            self::STATUS_INVALID_SIGN => 'Tanda Tangan Tidak Valid',
            self::STATUS_REJECTED => 'Ditolak',
            // self::STATUS_CANCELLED => 'Dibatalkan'
        ];

        return $labels[$this->status] ?? 'Status Tidak Dikenal';
    }

    /**
     * Get status badge class untuk UI
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_APPROVED => 'badge-info',
            self::STATUS_USER_SIGNED => 'badge-primary',
            self::STATUS_SIGN_APPROVED => 'badge-success',
            self::STATUS_INVALID_SIGN => 'badge-dark',
            self::STATUS_REJECTED => 'badge-danger',
            // self::STATUS_CANCELLED => 'badge-secondary'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Generate nomor surat lengkap
     */
    // public function getFullDocumentNumberAttribute()
    // {
    //     $currentYear = now()->year;
    //     return "{$this->nomor}/III.3.AU/KEP-FT/VIII/{$currentYear}";
    // }

    /**
     * Get workflow progress percentage
     */
    public function getWorkflowProgressAttribute()
    {
        $progress = [
            self::STATUS_PENDING => 20,
            self::STATUS_APPROVED => 40,
            self::STATUS_USER_SIGNED => 80,
            self::STATUS_SIGN_APPROVED => 100,
            self::STATUS_INVALID_SIGN => 0,
            self::STATUS_REJECTED => 0,
            // self::STATUS_CANCELLED => 0
        ];

        return $progress[$this->status] ?? 0;
    }

    /**
     * Get next action untuk workflow
     */
    public function getNextActionAttribute()
    {
        $actions = [
            self::STATUS_PENDING => 'Menunggu approval dari admin/kaprodi',
            self::STATUS_APPROVED => 'Silahkan lakukan tanda tangan digital',
            self::STATUS_USER_SIGNED => 'Menunggu verifikasi tanda tangan',
            self::STATUS_SIGN_APPROVED => 'Proses selesai',
            self::STATUS_INVALID_SIGN => 'Tanda tangan tidak valid',
            self::STATUS_REJECTED => 'Dokumen ditolak',
            // self::STATUS_CANCELLED => 'Dokumen dibatalkan'
        ];

        return $actions[$this->status] ?? 'Status tidak dikenal';
    }

    /**
     * Log status change untuk audit trail with standardized metadata
     */
    private function logStatusChange($action, $statusFrom = null, $statusTo = null, $description = '', $metadata = [])
    {
        // Merge with standardized metadata
        $enhancedMetadata = SignatureAuditLog::createMetadata(array_merge($metadata, [
            'document_name' => $this->document_name,
            // 'nomor' => $this->nomor,
            'approval_request_id' => $this->id,
            'status_transition' => $statusFrom ? "{$statusFrom} → {$statusTo}" : $statusTo,
            'changed_by' => Auth::user()->name ?? 'System',
        ]));

        SignatureAuditLog::create([
            'approval_request_id' => $this->id,
            'user_id' => $this->user_id,
            'kaprodi_id' => $this->approved_by,
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
        return $query->where('status', $status);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReadyToSign($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePendingSignApproval($query)
    {
        return $query->where('status', self::STATUS_USER_SIGNED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_SIGN_APPROVED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
