<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentHash extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'hash_type',
        'hash_value',
        'blockchain_tx_hash',
        'verified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}