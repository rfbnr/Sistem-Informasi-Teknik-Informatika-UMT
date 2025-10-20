<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor',
        'document_name',
        'document_path',
        'signed_document_path',
        'notes',
        'status',
        'approved_at',
        'approved_by',
        'user_signed_at',
        'sign_approved_at',
        'sign_approved_by',
        'approval_notes',
        'rejection_reason',
        'document_type',
        'priority',
        'workflow_metadata',
        'department',
        'deadline',
        'revision_count',
        'admin_notes'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'user_signed_at' => 'datetime',
        'sign_approved_at' => 'datetime',
        'deadline' => 'datetime',
        'workflow_metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_USER_SIGNED = 'user_signed';
    const STATUS_SIGN_APPROVED = 'sign_approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Boot method untuk auto-generate nomor
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate nomor otomatis saat create
            $lastNumber = DB::table('approval_requests')->max('nomor');

            if ($lastNumber) {
                $newNumber = str_pad(intval($lastNumber) + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $model->nomor = $newNumber;

            // Set default values
            if (empty($model->priority)) {
                $model->priority = self::PRIORITY_NORMAL;
            }

            if (empty($model->department) && $model->user) {
                $model->department = 'Teknik Informatika'; // Default department
            }
        });

        static::created(function ($model) {
            // Log audit trail untuk request creation
            SignatureAuditLog::create([
                'approval_request_id' => $model->id,
                'user_id' => $model->user_id,
                'action' => 'approval_request_created',
                'status_to' => $model->status,
                'description' => "Approval request '{$model->document_name}' has been created",
                'metadata' => [
                    'document_name' => $model->document_name,
                    'nomor' => $model->nomor,
                    'priority' => $model->priority
                ],
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
     * Relasi ke User (approver)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi ke User (sign approver)
     */
    public function signApprover()
    {
        return $this->belongsTo(User::class, 'sign_approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // public function digitalSignature()
    // {
    //     return $this->hasOneThrough(
    //         DigitalSignature::class,
    //         DocumentSignature::class,
    //         'approval_request_id', // Foreign key on DocumentSignature table
    //         'id',                 // Foreign key on DigitalSignature table
    //         'id',                 // Local key on ApprovalRequest table
    //         'digital_signature_id'// Local key on DocumentSignature table
    //     );
    // }

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
     * Check apakah bisa ditandatangani user
     */
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
     * Check apakah mendekati deadline
     */
    public function isNearDeadline($days = 3)
    {
        if (!$this->deadline) {
            return false;
        }

        return $this->deadline <= now()->addDays($days) && !$this->isCompleted();
    }

    /**
     * Check apakah sudah melewati deadline
     */
    public function isOverdue()
    {
        if (!$this->deadline) {
            return false;
        }

        return $this->deadline < now() && !$this->isCompleted();
    }

    /**
     * Approve dokumen (admin/kaprodi)
     */
    public function approveApprovalRequest($approverId, $notes = null)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $approverId,
            'approval_notes' => $notes
        ]);

        // FIX #7: Create DocumentSignature record dan return hasilnya
        $documentSignature = $this->createDocumentSignature();

        // Log audit
        $this->logStatusChange('approved', $oldStatus, self::STATUS_APPROVED,
            'Document has been approved for signing', ['notes' => $notes]);

        return $documentSignature;
    }

    /**
     * Reject dokumen
     */
    public function reject($reason = null, $rejectedBy = null)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $rejectedBy ?? Auth::id()
        ]);

        // Log audit
        $this->logStatusChange('rejected', $oldStatus, self::STATUS_REJECTED,
            'Document has been rejected', ['reason' => $reason]);
    }

    /**
     * Mark sebagai sudah ditandatangani user
     */
    public function markUserSigned()
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_USER_SIGNED,
            'user_signed_at' => now()
        ]);

        // Log audit
        $this->logStatusChange('user_signed', $oldStatus, self::STATUS_USER_SIGNED,
            'Document has been digitally signed by user');
    }

    /**
     * Approve tanda tangan
     */
    public function approveSignature($approverId, $notes = null)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_SIGN_APPROVED,
            'sign_approved_at' => now(),
            'sign_approved_by' => $approverId,
            'approval_notes' => $notes
        ]);

        // Log audit
        $this->logStatusChange('signature_approved', $oldStatus, self::STATUS_SIGN_APPROVED,
            'Digital signature has been approved and verified', ['notes' => $notes]);
    }

    /**
     * Cancel approval request
     */
    public function cancel($reason = null)
    {
        $oldStatus = $this->status;

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'rejection_reason' => $reason
        ]);

        // Log audit
        $this->logStatusChange('cancelled', $oldStatus, self::STATUS_CANCELLED,
            'Approval request has been cancelled', ['reason' => $reason]);
    }

    /**
     * Create DocumentSignature record
     */
    private function createDocumentSignature()
    {
        if ($this->documentSignature) {
            return $this->documentSignature;
        }

        // Get active digital signature
        $digitalSignature = DigitalSignature::active()->valid()->first();

        // dd($digitalSignature);

        if (!$digitalSignature) {
            throw new \Exception('No valid digital signature available for signing process');
        }

        // Generate document hash
        $documentPath = Storage::disk('public')->path($this->document_path);
        $documentHash = DocumentSignature::generateDocumentHash($documentPath);

        return DocumentSignature::create([
            'approval_request_id' => $this->id,
            'digital_signature_id' => $digitalSignature->id,
            'document_hash' => $documentHash,
            // 'signed_by' => $this->user_id,
            'signature_status' => DocumentSignature::STATUS_PENDING
        ]);
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
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan'
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
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'Rendah',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Tinggi',
            self::PRIORITY_URGENT => 'Mendesak'
        ];

        return $labels[$this->priority] ?? 'Normal';
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClassAttribute()
    {
        $classes = [
            self::PRIORITY_LOW => 'badge-light',
            self::PRIORITY_NORMAL => 'badge-secondary',
            self::PRIORITY_HIGH => 'badge-warning',
            self::PRIORITY_URGENT => 'badge-danger'
        ];

        return $classes[$this->priority] ?? 'badge-secondary';
    }

    /**
     * Generate nomor surat lengkap
     */
    public function getFullDocumentNumberAttribute()
    {
        $currentYear = now()->year;
        return "{$this->nomor}/III.3.AU/KEP-FT/VIII/{$currentYear}";
    }

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
            self::STATUS_REJECTED => 0,
            self::STATUS_CANCELLED => 0
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
            self::STATUS_REJECTED => 'Dokumen ditolak',
            self::STATUS_CANCELLED => 'Dokumen dibatalkan'
        ];

        return $actions[$this->status] ?? 'Status tidak dikenal';
    }

    /**
     * Increment revision count
     */
    public function incrementRevision($notes = null)
    {
        $this->increment('revision_count');

        if ($notes) {
            $this->update(['admin_notes' => $notes]);
        }

        $this->logStatusChange('revision_requested', null, null,
            'Document revision requested', ['revision_count' => $this->revision_count, 'notes' => $notes]);
    }

    /**
     * Set priority
     */
    public function setPriority($priority, $reason = null)
    {
        $oldPriority = $this->priority;
        $this->update(['priority' => $priority]);

        $this->logStatusChange('priority_changed', $oldPriority, $priority,
            'Document priority has been changed', ['reason' => $reason]);
    }

    /**
     * Set deadline
     */
    public function setDeadline($deadline, $reason = null)
    {
        $oldDeadline = $this->deadline;
        $this->update(['deadline' => $deadline]);

        $this->logStatusChange('deadline_set',
            $oldDeadline ? $oldDeadline->toDateString() : null,
            $deadline->toDateString(),
            'Document deadline has been set', ['reason' => $reason]);
    }

    /**
     * Log status change untuk audit trail
     */
    private function logStatusChange($action, $statusFrom = null, $statusTo = null, $description = '', $metadata = [])
    {
        SignatureAuditLog::create([
            'approval_request_id' => $this->id,
            'user_id' => Auth::id() ?? $this->user_id,
            'action' => $action,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'description' => $description,
            'metadata' => array_merge($metadata, [
                'document_name' => $this->document_name,
                'nomor' => $this->nomor
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

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                    ->whereNotIn('status', [self::STATUS_SIGN_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED]);
    }

    public function scopeNearDeadline($query, $days = 3)
    {
        return $query->where('deadline', '<=', now()->addDays($days))
                    ->where('deadline', '>=', now())
                    ->whereNotIn('status', [self::STATUS_SIGN_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }
}
