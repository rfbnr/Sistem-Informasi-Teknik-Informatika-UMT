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
}
