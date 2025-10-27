<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureVerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_signature_id',
        'approval_request_id',
        'user_id',
        'verification_method',
        'verification_token_hash',
        'is_valid',
        'result_status',
        'ip_address',
        'user_agent',
        'referrer',
        'metadata',
        'verified_at'
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'metadata' => 'array',
        'verified_at' => 'datetime'
    ];

    protected $appends = [
        'device_type',
        'browser_name',
        'is_anonymous',
        'failed_reason',
        'verification_duration_ms',
    ];

    // Result status constants
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_INVALID = 'invalid';
    const STATUS_NOT_FOUND = 'not_found';

    // Verification method constants
    const METHOD_TOKEN = 'token';
    const METHOD_URL = 'url';
    const METHOD_QR = 'qr';
    const METHOD_ID = 'id';


    // Relasi
      public function documentSignature()
      {
          return $this->belongsTo(DocumentSignature::class);
      }

      public function approvalRequest()
      {
          return $this->belongsTo(ApprovalRequest::class);
      }

      public function user()
      {
          return$this->belongsTo(User::class);
      }

      // Simple scopes
      public function scopeSuccessful($query)
      {
          return $query->where('is_valid', true);
      }

      public function scopeFailed($query)
      {
          return $query->where('is_valid', false);
      }

      public function scopeByMethod($query, $method)
      {
          return $query->where('verification_method', $method);
      }

      public function scopeByDocument($query, $documentSignatureId)
      {
          return $query->where('document_signature_id', $documentSignatureId);
      }

      public function scopeToday($query)
      {
          return $query->whereDate('verified_at', today());
      }

      public function scopeInPeriod($query, $startDate, $endDate = null)
      {
          $endDate = $endDate ?? now();
          return $query->whereBetween('verified_at', [$startDate, $endDate]);
      }

      // Simple statistics
      public static function getStatistics($startDate = null, $endDate = null)
      {
        $query = self::query();

        if ($startDate) {
        $query->where('verified_at', '>=', $startDate);
        }
        if ($endDate) {
        $query->where('verified_at', '<=', $endDate);
        }

        return [
            'total_verifications' => $query->count(),
            'successful_verifications' => $query->where('is_valid', true)->count(),
            'failed_verifications' => $query->where('is_valid', false)->count(),
            'unique_ips' => $query->distinct('ip_address')->count(),
            'verifications_by_method' => $query->selectRaw('verification_method, COUNT(*) as count')
            ->groupBy('verification_method')
            ->pluck('count', 'verification_method')
            ->toArray(),
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
     * Check if verification was anonymous (no user logged in)
     * Returns: boolean
     */
    public function getIsAnonymousAttribute()
    {
        return $this->user_id === null;
    }

    /**
     * Get failed reason category
     * Returns: string or null
     */
    public function getFailedReasonAttribute()
    {
        if ($this->is_valid) {
            return null;
        }

        // Check metadata first
        if (isset($this->metadata['failed_reason'])) {
            return $this->metadata['failed_reason'];
        }

        // Fallback to result_status
        return $this->result_status;
    }

    /**
     * Get verification duration in milliseconds
     * Returns: integer (ms) or null
     */
    public function getVerificationDurationMsAttribute()
    {
        return $this->metadata['verification_duration_ms'] ?? null;
    }

    /**
     * Get human-readable verification duration
     * Returns: string like "1.5s" or null
     */
    public function getVerificationDurationHumanAttribute()
    {
        $ms = $this->verification_duration_ms;
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

    /**
     * Get verification count for this document before this verification
     * Returns: integer or null
     */
    public function getPreviousVerificationCountAttribute()
    {
        return $this->metadata['previous_verification_count'] ?? null;
    }

    /**
     * Get geolocation data
     * Returns: array or null
     */
    public function getGeolocationAttribute()
    {
        return $this->metadata['geolocation'] ?? null;
    }

    /**
     * Get country from geolocation
     * Returns: string or null
     */
    public function getCountryAttribute()
    {
        $geo = $this->geolocation;
        return $geo['country'] ?? null;
    }

    /**
     * Get city from geolocation
     * Returns: string or null
     */
    public function getCityAttribute()
    {
        $geo = $this->geolocation;
        return $geo['city'] ?? null;
    }

    /**
     * Get verification result label for UI
     */
    public function getResultLabelAttribute()
    {
        $labels = [
            self::STATUS_SUCCESS => 'Berhasil Diverifikasi',
            self::STATUS_FAILED => 'Gagal Verifikasi',
            self::STATUS_EXPIRED => 'Token Kadaluarsa',
            self::STATUS_INVALID => 'Tanda Tangan Tidak Valid',
            self::STATUS_NOT_FOUND => 'Dokumen Tidak Ditemukan',
        ];

        return $labels[$this->result_status] ?? ucfirst(str_replace('_', ' ', $this->result_status));
    }

    /**
     * Get result icon for UI
     */
    public function getResultIconAttribute()
    {
        $icons = [
            self::STATUS_SUCCESS => 'fas fa-check-circle',
            self::STATUS_FAILED => 'fas fa-times-circle',
            self::STATUS_EXPIRED => 'fas fa-clock',
            self::STATUS_INVALID => 'fas fa-exclamation-triangle',
            self::STATUS_NOT_FOUND => 'fas fa-question-circle',
        ];

        return $icons[$this->result_status] ?? 'fas fa-info-circle';
    }

    /**
     * Get result color for UI
     */
    public function getResultColorAttribute()
    {
        $colors = [
            self::STATUS_SUCCESS => 'text-success',
            self::STATUS_FAILED => 'text-danger',
            self::STATUS_EXPIRED => 'text-warning',
            self::STATUS_INVALID => 'text-danger',
            self::STATUS_NOT_FOUND => 'text-secondary',
        ];

        return $colors[$this->result_status] ?? 'text-secondary';
    }

    /**
     * Get method label for UI
     */
    public function getMethodLabelAttribute()
    {
        $labels = [
            self::METHOD_TOKEN => 'Token',
            self::METHOD_URL => 'URL',
            self::METHOD_QR => 'QR Code',
            self::METHOD_ID => 'ID',
        ];

        return $labels[$this->verification_method] ?? ucfirst($this->verification_method);
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
     * Scope untuk filter anonymous verifications
     */
    public function scopeAnonymous($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope untuk filter authenticated verifications
     */
    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope untuk filter by result status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('result_status', $status);
    }

    /**
     * Scope untuk logs N hari terakhir
     */
    public function scopeLastNDays($query, $days = 7)
    {
        return $query->where('verified_at', '>=', now()->subDays($days));
    }

    /**
     * Scope untuk suspicious activity (multiple failed attempts from same IP)
     */
    public function scopeSuspiciousActivity($query, $threshold = 5, $hours = 24)
    {
        $startTime = now()->subHours($hours);

        return $query->where('is_valid', false)
                    ->where('verified_at', '>=', $startTime)
                    ->selectRaw('ip_address, COUNT(*) as attempts')
                    ->groupBy('ip_address')
                    ->havingRaw('COUNT(*) >= ?', [$threshold]);
    }

    /**
     * Scope untuk filter by device type
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('metadata->device_type', $deviceType);
    }

    /**
     * Scope untuk filter by IP address
     */
    public function scopeByIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
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

    /**
     * Get failed reason category from error details
     */
    public static function categorizeFailedReason($errorMessage)
    {
        $message = strtolower($errorMessage);

        if (strpos($message, 'expired') !== false || strpos($message, 'kadaluarsa') !== false) {
            return 'expired';
        }
        if (strpos($message, 'modified') !== false || strpos($message, 'diubah') !== false) {
            return 'document_modified';
        }
        if (strpos($message, 'not found') !== false || strpos($message, 'tidak ditemukan') !== false) {
            return 'not_found';
        }
        if (strpos($message, 'invalid signature') !== false || strpos($message, 'tanda tangan tidak valid') !== false) {
            return 'invalid_signature';
        }
        if (strpos($message, 'revoked') !== false || strpos($message, 'dicabut') !== false) {
            return 'revoked';
        }

        return 'unknown_error';
    }
}
