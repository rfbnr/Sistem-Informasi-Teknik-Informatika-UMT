<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * VerificationCodeMapping Model
 *
 * This model maps short verification codes to full encrypted payloads.
 * It enables short URLs for QR codes while maintaining full encryption security.
 *
 * @property int $id
 * @property string $short_code
 * @property string $encrypted_payload
 * @property int $document_signature_id
 * @property \Carbon\Carbon $expires_at
 * @property int $access_count
 * @property \Carbon\Carbon|null $last_accessed_at
 * @property string|null $last_accessed_ip
 * @property string|null $last_accessed_user_agent
 */
class VerificationCodeMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_code',
        'encrypted_payload',
        'document_signature_id',
        'expires_at',
        'access_count',
        'last_accessed_at',
        'last_accessed_ip',
        'last_accessed_user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'access_count' => 'integer',
    ];

    /**
     * Relationship: VerificationCodeMapping belongs to DocumentSignature
     */
    public function documentSignature()
    {
        return $this->belongsTo(DocumentSignature::class);
    }

    /**
     * Generate unique short code
     * Format: ABCD-1234-EFGH (12 characters with dashes for readability)
     *
     * @param int $length Length of each segment (default: 4)
     * @return string
     */
    //! DIPAKAI DI CREATEMAPPING METHOD
    public static function generateShortCode($length = 4)
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            // Generate random segments
            $part1 = strtoupper(Str::random($length));
            $part2 = strtoupper(Str::random($length));
            $part3 = strtoupper(Str::random($length));

            $shortCode = "{$part1}-{$part2}-{$part3}";

            // Check uniqueness
            $exists = self::where('short_code', $shortCode)->exists();

            $attempt++;

            if ($attempt >= $maxAttempts && $exists) {
                Log::warning('Failed to generate unique short code after max attempts', [
                    'attempts' => $maxAttempts
                ]);
                throw new \RuntimeException('Failed to generate unique short code');
            }

        } while ($exists);

        return $shortCode;
    }

    /**
     * Create new mapping with encrypted payload
     *
     * @param string $encryptedPayload Full encrypted verification data
     * @param int $documentSignatureId Reference to document signature
     * @param \Carbon\Carbon|int|null $expiresAt Expiration date (Carbon instance, years as int, or null for default)
     * @return self
     */
    //! DIPAKAI DI QRCODESERVICE
    public static function createMapping($encryptedPayload, $documentSignatureId, $expiresAt = null)
    {
        $shortCode = self::generateShortCode();

        // Handle different types of expiration input
        if ($expiresAt === null) {
            // Default: 5 years from now
            $expirationDate = now()->addYears(5);
            Log::debug('Using default expiration (5 years)', [
                'expires_at' => $expirationDate->toDateTimeString()
            ]);
        } elseif ($expiresAt instanceof \Carbon\Carbon) {
            // Carbon instance provided (preferred method)
            $expirationDate = $expiresAt;
            Log::debug('Using provided Carbon expiration date', [
                'expires_at' => $expirationDate->toDateTimeString()
            ]);
        } elseif (is_numeric($expiresAt)) {
            // Backward compatibility: integer = years
            $expirationDate = now()->addYears((int) $expiresAt);
            Log::debug('Using years-based expiration (backward compatibility)', [
                'years' => $expiresAt,
                'expires_at' => $expirationDate->toDateTimeString()
            ]);
        } elseif (is_string($expiresAt)) {
            // String date provided
            try {
                $expirationDate = \Carbon\Carbon::parse($expiresAt);
                Log::debug('Parsed string expiration date', [
                    'input' => $expiresAt,
                    'expires_at' => $expirationDate->toDateTimeString()
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to parse expiration date string, using default', [
                    'input' => $expiresAt,
                    'error' => $e->getMessage()
                ]);
                $expirationDate = now()->addYears(5);
            }
        } else {
            // Unsupported type, use default
            Log::warning('Unsupported expiration type, using default', [
                'type' => gettype($expiresAt)
            ]);
            $expirationDate = now()->addYears(5);
        }

        // Validate expiration is in the future
        if ($expirationDate < now()) {
            Log::warning('Expiration date is in the past, adjusting to minimum (1 day)', [
                'provided' => $expirationDate->toDateTimeString(),
                'adjusted' => now()->addDay()->toDateTimeString()
            ]);
            $expirationDate = now()->addDay();
        }

        $mapping = self::create([
            'short_code' => $shortCode,
            'encrypted_payload' => $encryptedPayload,
            'document_signature_id' => $documentSignatureId,
            'expires_at' => $expirationDate,
            'access_count' => 0,
        ]);

        Log::info('Verification code mapping created', [
            'short_code' => $shortCode,
            'document_signature_id' => $documentSignatureId,
            'expires_at' => $mapping->expires_at->toDateTimeString(),
            'days_until_expiry' => now()->diffInDays($mapping->expires_at)
        ]);

        return $mapping;
    }

    /**
     * Find mapping by short code with validation
     *
     * @param string $shortCode
     * @return self|null
     * @throws \Exception
     */
    public static function findByShortCode($shortCode)
    {
        $mapping = self::where('short_code', $shortCode)->first();

        if (!$mapping) {
            Log::warning('Verification code not found', [
                'short_code' => $shortCode,
                'ip' => request()->ip()
            ]);
            throw new \Exception('Invalid verification code');
        }

        // Check expiration
        if ($mapping->isExpired()) {
            Log::warning('Verification code has expired', [
                'short_code' => $shortCode,
                'expired_at' => $mapping->expires_at->toDateTimeString()
            ]);
            throw new \Exception('Verification code has expired');
        }

        return $mapping;
    }

    /**
     * Get short code from document signature id
     *
     * @return string|null
     */
    public static function getShortCodeFromDocumentSignatureId($documentSignatureId)
    {
        $mapping = self::where('document_signature_id', $documentSignatureId)->first();

        return $mapping ? $mapping->short_code : null;
    }

    /**
     * Check if mapping has expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Track access to this verification code
     *
     * @param string|null $ip
     * @param string|null $userAgent
     * @return void
     */
    public function trackAccess($ip = null, $userAgent = null)
    {
        $this->increment('access_count');

        $this->update([
            'last_accessed_at' => now(),
            'last_accessed_ip' => $ip ?? request()->ip(),
            'last_accessed_user_agent' => $userAgent ?? request()->userAgent(),
        ]);

        Log::info('Verification code accessed', [
            'short_code' => $this->short_code,
            'access_count' => $this->access_count,
            'ip' => $this->last_accessed_ip
        ]);
    }

    /**
     * Check if access should be rate limited
     *
     * @param int $maxAttemptsPerHour Maximum attempts per hour (default: 10)
     * @return bool
     */
    public function shouldRateLimit($maxAttemptsPerHour = 10)
    {
        // Check if last accessed within 1 hour
        if ($this->last_accessed_at && $this->last_accessed_at->diffInHours(now()) < 1) {
            // Check if access count exceeds limit within the hour
            $recentAccesses = self::where('short_code', $this->short_code)
                ->where('last_accessed_at', '>=', now()->subHour())
                ->sum('access_count');

            if ($recentAccesses >= $maxAttemptsPerHour) {
                Log::warning('Rate limit exceeded for verification code', [
                    'short_code' => $this->short_code,
                    'access_count' => $recentAccesses,
                    'limit' => $maxAttemptsPerHour,
                    'ip' => request()->ip()
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Get only non-expired mappings
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope: Get expired mappings
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope: Get mappings older than X days
     */
    public function scopeOlderThan($query, $days)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    /**
     * Scope: Get most accessed codes
     */
    public function scopeMostAccessed($query, $limit = 10)
    {
        return $query->orderBy('access_count', 'desc')->limit($limit);
    }

    /**
     * Scope: Get recently created
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
