<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockchainTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'signature_request_id',
        'signature_id',
        'transaction_hash',
        'block_number',
        'transaction_type',
        'contract_address',
        'gas_used',
        'gas_price',
        'status',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'block_number' => 'integer',
        'gas_used' => 'integer',
        'gas_price' => 'decimal:18',
        'metadata' => 'array'
    ];

    public function signatureRequest()
    {
        return $this->belongsTo(SignatureRequest::class);
    }

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getExplorerUrl()
    {
        // Sesuaikan dengan blockchain yang digunakan (Ethereum, Polygon, dll)
        $baseUrl = config('blockchain.explorer_url', 'https://etherscan.io/tx/');
        return $baseUrl . $this->transaction_hash;
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed' && !empty($this->block_number);
    }
}