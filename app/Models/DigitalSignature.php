<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DigitalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'signature_id',
        'document_signature_id', // NEW: 1-to-1 relationship
        'public_key',
        'private_key',
        'algorithm',
        'key_length',
        'certificate',
        'valid_from',
        'valid_until',
        'status',
        'revocation_reason',
        'revoked_at',
        'metadata'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'private_key', // Jangan expose private key dalam JSON response
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->signature_id)) {
                $model->signature_id = 'SIG-' . Str::upper(Str::random(12));
            }

            // Set default valid period (1 year from now)
            if (empty($model->valid_from)) {
                $model->valid_from = now();
            }
            if (empty($model->valid_until)) {
                $model->valid_until = now()->addYear();
            }
        });
    }

    /**
     * REFACTORED: 1-to-1 relationship with DocumentSignature
     * Each key is unique per document
     */
    public function documentSignature()
    {
        // return $this->belongsTo(DocumentSignature::class, 'document_signature_id');
        return $this->belongsTo(DocumentSignature::class);
        // return $this->hasOne(DocumentSignature::class);
    }

    /**
     * Check apakah signature masih valid
     */
    public function isValid()
    {
        return $this->status === self::STATUS_ACTIVE &&
               $this->valid_from <= now() &&
               $this->valid_until >= now();
    }

    /**
     * Check apakah akan expired dalam waktu tertentu
     */
    public function isExpiringSoon($days = 30)
    {
        return $this->valid_until <= now()->addDays($days);
    }

    /**
     * Revoke signature (per-document basis now)
     */
    public function revoke($reason = null)
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'revocation_reason' => $reason,
            'revoked_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'revoked_at' => now()->toISOString(),
                'revoke_reason' => $reason
            ])
        ]);

        // Log audit with standardized metadata
        $metadata = SignatureAuditLog::createMetadata([
            'signature_id' => $this->signature_id,
            'reason' => $reason,
            'revoked_by' => Auth::user()->name ?? 'System',
            'algorithm' => $this->algorithm,
            'key_length' => $this->key_length,
            'document_signature_id' => $this->document_signature_id,
            'was_valid_until' => $this->valid_until?->toDateString(),
        ]);

        SignatureAuditLog::create([
            'kaprodi_id' => Auth::guard('kaprodi')->id(),
            'action' => SignatureAuditLog::ACTION_SIGNATURE_KEY_REVOKED,
            'status_from' => self::STATUS_ACTIVE,
            'status_to' => self::STATUS_REVOKED,
            'description' => "Digital signature {$this->signature_id} has been revoked. Reason: {$reason}",
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now()
        ]);
    }

    /**
     * Generate RSA Key Pair
     */
    // public static function generateKeyPair($keyLength = 2048)
    // {
    //     try {
    //         $config = [
    //             "digest_alg" => "sha256",
    //             "private_key_bits" => $keyLength,
    //             "private_key_type" => OPENSSL_KEYTYPE_RSA,
    //         ];

    //         $privateKey = openssl_pkey_new($config);
    //         if (!$privateKey) {
    //             throw new \Exception('Failed to generate private key: ' . openssl_error_string());
    //         }

    //         $publicKey = openssl_pkey_get_details($privateKey);
    //         if (!$publicKey) {
    //             throw new \Exception('Failed to extract public key: ' . openssl_error_string());
    //         }

    //         $privateKeyPem = '';
    //         if (!openssl_pkey_export($privateKey, $privateKeyPem)) {
    //             throw new \Exception('Failed to export private key: ' . openssl_error_string());
    //         }

    //         return [
    //             'private_key' => $privateKeyPem,
    //             'public_key' => $publicKey['key'],
    //             'key_length' => $publicKey['bits']
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error('RSA Key Generation Failed: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

    /**
     * Create new digital signature dengan auto key generation
     */
    // public static function createSignature($purpose = 'Document Signing', $createdBy = null, $validityYears = 1)
    // {
    //     $keyPair = self::generateKeyPair();

    //     dd(now()->addYears($validityYears));

    //     return self::create([
    //         'public_key' => $keyPair['public_key'],
    //         'private_key' => $keyPair['private_key'],
    //         'algorithm' => 'RSA-SHA256',
    //         'key_length' => $keyPair['key_length'],
    //         'signature_purpose' => $purpose,
    //         'created_by' => $createdBy ?? Auth::id(),
    //         'valid_from' => now(),
    //         'valid_until' => now()->addYears($validityYears),
    //         'metadata' => [
    //             'created_ip' => request()->ip(),
    //             'created_user_agent' => request()->userAgent()
    //         ]
    //     ]);
    // }

    /**
     * Encrypt private key untuk storage
     */
    public function setPrivateKeyAttribute($value)
    {
        $this->attributes['private_key'] = encrypt($value);
    }

    /**
     * Decrypt private key saat diambil
     */
    public function getPrivateKeyAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt private key for signature ID: ' . $this->signature_id);
            return null;
        }
    }

    /**
     * Get signature info (refactored for 1-to-1)
     */
    // public function getSignatureInfo()
    // {
    //     return [
    //         'signature_id' => $this->signature_id,
    //         'algorithm' => $this->algorithm,
    //         'key_length' => $this->key_length,
    //         'status' => $this->status,
    //         'valid_from' => $this->valid_from,
    //         'valid_until' => $this->valid_until,
    //         'days_until_expiry' => now()->diffInDays($this->valid_until, false),
    //         'is_valid' => $this->isValid(),
    //         'document_signature_id' => $this->document_signature_id
    //     ];
    // }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeValid($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('valid_until', '<=', now()->addDays($days))
                    ->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Validate RSA key pair
     */
    // public function validateKeyPair()
    // {
    //     try {
    //         $testData = 'test signature validation';
    //         $signature = '';

    //         // Test signing
    //         $result = openssl_sign($testData, $signature, $this->private_key, OPENSSL_ALGO_SHA256);
    //         if (!$result) {
    //             return false;
    //         }

    //         // Test verification
    //         $result = openssl_verify($testData, $signature, $this->public_key, OPENSSL_ALGO_SHA256);
    //         return $result === 1;

    //     } catch (\Exception $e) {
    //         Log::error('Key pair validation failed for signature ID: ' . $this->signature_id . ' - ' . $e->getMessage());
    //         return false;
    //     }
    // }

    /**
     * Get fingerprint dari public key
     */
    //! DIPAKAI
    public function getPublicKeyFingerprint()
    {
        return hash('sha256', $this->public_key);
    }
}
