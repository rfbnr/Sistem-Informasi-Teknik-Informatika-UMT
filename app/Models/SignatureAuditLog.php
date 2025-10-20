<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_signature_id',
        'approval_request_id',
        'user_id',
        'action',
        'status_from',
        'status_to',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'performed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    // Action constants
    const ACTION_SIGNATURE_INITIATED = 'signature_initiated';
    const ACTION_DOCUMENT_SIGNED = 'document_signed';
    const ACTION_SIGNATURE_VERIFIED = 'signature_verified';
    const ACTION_SIGNATURE_INVALIDATED = 'signature_invalidated';
    const ACTION_VERIFICATION_TOKEN_REGENERATED = 'verification_token_regenerated';
    const ACTION_TEMPLATE_CREATED = 'template_created';
    const ACTION_TEMPLATE_UPDATED = 'template_updated';
    const ACTION_TEMPLATE_SET_DEFAULT = 'template_set_default';
    const ACTION_SIGNATURE_KEY_GENERATED = 'signature_key_generated';
    const ACTION_SIGNATURE_KEY_REVOKED = 'signature_key_revoked';

    /**
     * Relasi ke DocumentSignature
     */
    public function documentSignature()
    {
        return $this->belongsTo(DocumentSignature::class);
    }

    /**
     * Relasi ke ApprovalRequest
     */
    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action label untuk display
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            self::ACTION_SIGNATURE_INITIATED => 'Proses Tanda Tangan Dimulai',
            self::ACTION_DOCUMENT_SIGNED => 'Dokumen Ditandatangani',
            self::ACTION_SIGNATURE_VERIFIED => 'Tanda Tangan Diverifikasi',
            self::ACTION_SIGNATURE_INVALIDATED => 'Tanda Tangan Dibatalkan',
            self::ACTION_VERIFICATION_TOKEN_REGENERATED => 'Token Verifikasi Diperbarui',
            self::ACTION_TEMPLATE_CREATED => 'Template Dibuat',
            self::ACTION_TEMPLATE_UPDATED => 'Template Diperbarui',
            self::ACTION_TEMPLATE_SET_DEFAULT => 'Template Diset Default',
            self::ACTION_SIGNATURE_KEY_GENERATED => 'Kunci Digital Dibuat',
            self::ACTION_SIGNATURE_KEY_REVOKED => 'Kunci Digital Dicabut',
        ];

        return $labels[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Get action icon untuk UI
     */
    public function getActionIconAttribute()
    {
        $icons = [
            self::ACTION_SIGNATURE_INITIATED => 'fas fa-play-circle',
            self::ACTION_DOCUMENT_SIGNED => 'fas fa-signature',
            self::ACTION_SIGNATURE_VERIFIED => 'fas fa-check-circle',
            self::ACTION_SIGNATURE_INVALIDATED => 'fas fa-times-circle',
            self::ACTION_VERIFICATION_TOKEN_REGENERATED => 'fas fa-sync-alt',
            self::ACTION_TEMPLATE_CREATED => 'fas fa-plus-square',
            self::ACTION_TEMPLATE_UPDATED => 'fas fa-edit',
            self::ACTION_TEMPLATE_SET_DEFAULT => 'fas fa-star',
            self::ACTION_SIGNATURE_KEY_GENERATED => 'fas fa-key',
            self::ACTION_SIGNATURE_KEY_REVOKED => 'fas fa-ban',
        ];

        return $icons[$this->action] ?? 'fas fa-info-circle';
    }

    /**
     * Get action color untuk UI
     */
    public function getActionColorAttribute()
    {
        $colors = [
            self::ACTION_SIGNATURE_INITIATED => 'text-info',
            self::ACTION_DOCUMENT_SIGNED => 'text-primary',
            self::ACTION_SIGNATURE_VERIFIED => 'text-success',
            self::ACTION_SIGNATURE_INVALIDATED => 'text-danger',
            self::ACTION_VERIFICATION_TOKEN_REGENERATED => 'text-warning',
            self::ACTION_TEMPLATE_CREATED => 'text-success',
            self::ACTION_TEMPLATE_UPDATED => 'text-info',
            self::ACTION_TEMPLATE_SET_DEFAULT => 'text-warning',
            self::ACTION_SIGNATURE_KEY_GENERATED => 'text-success',
            self::ACTION_SIGNATURE_KEY_REVOKED => 'text-danger',
        ];

        return $colors[$this->action] ?? 'text-secondary';
    }

    /**
     * Scope untuk filter berdasarkan action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope untuk logs dalam periode tertentu
     */
    public function scopeInPeriod($query, $startDate, $endDate = null)
    {
        $endDate = $endDate ?? now();
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk logs user tertentu
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk logs dokumen tertentu
     */
    public function scopeByDocument($query, $approvalRequestId)
    {
        return $query->where('approval_request_id', $approvalRequestId);
    }

    /**
     * Get summary statistics dari audit logs
     */
    public static function getStatistics($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate) {
            $query->where('performed_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('performed_at', '<=', $endDate);
        }

        return [
            'total_actions' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count(),
            'unique_documents' => $query->whereNotNull('approval_request_id')->distinct('approval_request_id')->count(),
            'actions_by_type' => $query->selectRaw('action, COUNT(*) as count')
                                      ->groupBy('action')
                                      ->pluck('count', 'action')
                                      ->toArray(),
            'recent_activity' => $query->latest('performed_at')->limit(10)->get()
        ];
    }
}
