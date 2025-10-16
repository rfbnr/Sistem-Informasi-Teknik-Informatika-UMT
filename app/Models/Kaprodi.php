<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kaprodi extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'NIDN',
        'phone',
        'jabatan',
        'status'
    ];

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class, 'approved_by');
    }

    public function signatureRequests()
    {
        return $this->belongsToMany(SignatureRequest::class, 'signature_request_signees', 'user_id', 'signature_request_id')
                    ->withPivot(['role', 'order', 'status', 'required', 'notified_at', 'viewed_at', 'responded_at', 'rejection_reason'])
                    ->withTimestamps();
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class, 'signer_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
