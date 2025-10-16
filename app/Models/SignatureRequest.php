<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'requester_id',
        'title',
        'description',
        'workflow_type',
        'deadline',
        'status',
        'priority',
        'metadata',
        'blockchain_tx_hash'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'metadata' => 'array'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    public function signees()
    {
        return $this->belongsToMany(User::class, 'signature_request_signees')
                    ->withPivot(['role', 'order', 'status', 'required'])
                    ->withTimestamps();
    }

    public function validations()
    {
        return $this->hasMany(SignatureValidation::class);
    }

    public function blockchainTransactions()
    {
        return $this->hasMany(BlockchainTransaction::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('deadline', '<', now())
                    ->where('status', 'pending');
    }

    public function isExpired()
    {
        return $this->deadline && $this->deadline->isPast() && $this->status !== 'completed';
    }

    public function getProgress()
    {
        $totalSignees = $this->signees()->count();
        $completedSignatures = $this->signatures()->where('status', 'signed')->count();

        return $totalSignees > 0 ? ($completedSignatures / $totalSignees) * 100 : 0;
    }

    public function getNextSigner()
    {
        return $this->signees()
                    ->wherePivot('status', 'pending')
                    ->orderBy('signature_request_signees.order')
                    ->first();
    }
}