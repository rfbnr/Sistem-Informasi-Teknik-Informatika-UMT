<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'signature_request_id',
        'signer_id',
        'signature_data',
        'signature_hash',
        'signed_at',
        'signature_method',
        'ip_address',
        'user_agent',
        'location',
        'status',
        'blockchain_tx_hash',
        'verification_code',
        'metadata'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'location' => 'array',
        'metadata' => 'array'
    ];

    public function signatureRequest()
    {
        return $this->belongsTo(SignatureRequest::class);
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    public function validations()
    {
        return $this->hasMany(SignatureValidation::class);
    }

    public function blockchainTransaction()
    {
        return $this->hasOne(BlockchainTransaction::class, 'signature_id');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function generateSignatureHash()
    {
        $data = [
            'signature_request_id' => $this->signature_request_id,
            'signer_id' => $this->signer_id,
            'signature_data' => $this->signature_data,
            'signed_at' => $this->signed_at->toISOString(),
            'ip_address' => $this->ip_address
        ];

        return hash('sha256', json_encode($data));
    }

    public function verifySignature()
    {
        $currentHash = $this->generateSignatureHash();
        return $currentHash === $this->signature_hash;
    }

    public function generateVerificationCode()
    {
        return strtoupper(substr(hash('sha256', $this->id . $this->signature_hash . now()), 0, 8));
    }
}