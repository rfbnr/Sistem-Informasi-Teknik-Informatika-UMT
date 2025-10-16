<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->roles == 'admin';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'NIM',
        'phone',
        'address',
        'semester',
        'angkatan',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function signatureRequests()
    {
        return $this->belongsToMany(SignatureRequest::class, 'signature_request_signees')
                    ->withPivot(['role', 'order', 'status', 'required', 'notified_at', 'viewed_at', 'responded_at', 'rejection_reason'])
                    ->withTimestamps();
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class, 'signer_id');
    }

    public function requestedSignatures()
    {
        return $this->hasMany(SignatureRequest::class, 'requester_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (!$user->status) {
                $user->status = 'active';
            }
        });
    }
}
