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
        'kaprodi_id',
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

    protected $appends = [
        'device_type',
        'browser_name',
        'duration_ms',
        'session_id',
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
    const ACTION_TEMPLATE_CLONED = 'template_cloned';
    const ACTION_TEMPLATE_ACTIVATED = 'template_activated';
    const ACTION_TEMPLATE_DEACTIVATED = 'template_deactivated';
    const ACTION_SIGNING_FAILED = 'signing_failed';

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

    // ========================================================================
    // COMPUTED PROPERTIES (Accessor Methods)
    // ========================================================================

    /**
     * Get device type from user agent
     * Returns: desktop, mobile, tablet, bot, unknown
     */
    public function getDeviceTypeAttribute()
    {
        // Check metadata first
        if (isset($this->metadata['device_type'])) {
            return $this->metadata['device_type'];
        }

        // Parse from user_agent if not in metadata
        return $this->parseDeviceType($this->user_agent);
    }

    /**
     * Get browser name from user agent
     * Returns: Chrome, Firefox, Safari, Edge, Opera, etc.
     */
    public function getBrowserNameAttribute()
    {
        // Check metadata first
        if (isset($this->metadata['browser'])) {
            return $this->metadata['browser'];
        }

        // Parse from user_agent if not in metadata
        return $this->parseBrowserName($this->user_agent);
    }

    /**
     * Get duration in milliseconds
     * Returns: integer (ms) or null
     */
    public function getDurationMsAttribute()
    {
        return $this->metadata['duration_ms'] ?? null;
    }

    /**
     * Get session ID
     * Returns: string or null
     */
    public function getSessionIdAttribute()
    {
        return $this->metadata['session_id'] ?? null;
    }

    /**
     * Get error code if action failed
     * Returns: string or null
     */
    public function getErrorCodeAttribute()
    {
        return $this->metadata['error_code'] ?? null;
    }

    /**
     * Get error message if action failed
     * Returns: string or null
     */
    public function getErrorMessageAttribute()
    {
        return $this->metadata['error_message'] ?? null;
    }

    /**
     * Check if action was successful
     * Returns: boolean
     */
    public function getIsSuccessAttribute()
    {
        return $this->action !== self::ACTION_SIGNING_FAILED && empty($this->metadata['error_code']);
    }

    /**
     * Get human-readable duration
     * Returns: string like "1.5s" or null
     */
    public function getDurationHumanAttribute()
    {
        $ms = $this->duration_ms;
        if ($ms === null) {
            return null;
        }

        if ($ms < 1000) {
            return $ms . 'ms';
        } elseif ($ms < 60000) {
            return round($ms / 1000, 1) . 's';
        } else {
            return round($ms / 60000, 1) . 'm';
        }
    }

    // ========================================================================
    // HELPER METHODS FOR PARSING
    // ========================================================================

    /**
     * Parse device type from user agent string
     */
    private function parseDeviceType($userAgent)
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        // Check for bots
        if (preg_match('/bot|crawl|spider|slurp|facebook|whatsapp/i', $userAgent)) {
            return 'bot';
        }

        // Check for mobile devices
        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $userAgent)) {
            return 'mobile';
        }

        // Check for tablets
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Parse browser name from user agent string
     */
    private function parseBrowserName($userAgent)
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        // Check for common browsers
        if (strpos($userAgent, 'Edg') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            return 'Opera';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
            return 'Internet Explorer';
        }

        return 'Other';
    }

    // ========================================================================
    // ADDITIONAL SCOPE METHODS
    // ========================================================================

    /**
     * Scope untuk filter failed actions
     */
    public function scopeFailedActions($query)
    {
        return $query->where('action', self::ACTION_SIGNING_FAILED)
                    ->orWhereNotNull('metadata->error_code');
    }

    /**
     * Scope untuk filter successful actions
     */
    public function scopeSuccessfulActions($query)
    {
        return $query->where('action', '!=', self::ACTION_SIGNING_FAILED)
                    ->whereNull('metadata->error_code');
    }

    /**
     * Scope untuk logs N hari terakhir
     */
    public function scopeLastNDays($query, $days = 7)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope untuk logs hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('performed_at', today());
    }

    /**
     * Scope untuk filter by device type
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('metadata->device_type', $deviceType);
    }

    /**
     * Scope untuk filter by kaprodi
     */
    public function scopeByKaprodi($query, $kaprodiId)
    {
        return $query->where('kaprodi_id', $kaprodiId);
    }

    // ========================================================================
    // STATIC HELPER METHODS
    // ========================================================================

    /**
     * Create standardized metadata structure
     *
     * @param array $customData Additional custom data to merge
     * @return array Standardized metadata
     */
    public static function createMetadata($customData = [])
    {
        $request = request();

        $baseMetadata = [
            'timestamp' => now()->timestamp,
            'session_id' => session()->getId() ?? null,
            'device_type' => self::detectDeviceType($request->userAgent()),
            'browser' => self::detectBrowserName($request->userAgent()),
            'platform' => self::detectPlatform($request->userAgent()),
        ];

        return array_merge($baseMetadata, $customData);
    }

    /**
     * Detect device type from user agent
     */
    private static function detectDeviceType($userAgent)
    {
        if (empty($userAgent)) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/bot|crawl|spider|slurp/i', $userAgent)) {
            return 'bot';
        }
        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Detect browser name from user agent
     */
    private static function detectBrowserName($userAgent)
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        if (strpos($userAgent, 'Edg') !== false) return 'Edge';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) return 'Opera';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'IE';

        return 'Other';
    }

    /**
     * Detect platform from user agent
     */
    private static function detectPlatform($userAgent)
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        if (stripos($userAgent, 'Windows') !== false) return 'Windows';
        if (stripos($userAgent, 'Mac') !== false) return 'macOS';
        if (stripos($userAgent, 'Linux') !== false) return 'Linux';
        if (stripos($userAgent, 'Android') !== false) return 'Android';
        if (stripos($userAgent, 'iOS') !== false || stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) return 'iOS';

        return 'Other';
    }
}
