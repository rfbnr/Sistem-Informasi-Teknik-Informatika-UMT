<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureValidation extends Model
{
    use HasFactory;

    protected $fillable = [
        'signature_request_id',
        'signature_id',
        'validation_type',
        'validation_result',
        'validated_by',
        'validated_at',
        'validation_data'
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'validation_data' => 'array',
        'validation_result' => 'boolean'
    ];

    public function signatureRequest()
    {
        return $this->belongsTo(SignatureRequest::class);
    }

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}