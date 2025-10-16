<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'file_path',
        'file_size',
        'file_hash',
        'mime_type',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function hashes()
    {
        return $this->hasMany(DocumentHash::class);
    }

    public function access()
    {
        return $this->hasMany(DocumentAccess::class);
    }

    public function signatureRequests()
    {
        return $this->hasMany(SignatureRequest::class);
    }

    public function signatures()
    {
        return $this->hasManyThrough(Signature::class, SignatureRequest::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getCurrentVersion()
    {
        return $this->versions()->latest()->first();
    }

    public function generateHash()
    {
        if (file_exists(storage_path('app/public/' . $this->file_path))) {
            return hash_file('sha256', storage_path('app/public/' . $this->file_path));
        }
        return null;
    }

    public function verifyIntegrity()
    {
        $currentHash = $this->generateHash();
        return $currentHash === $this->file_hash;
    }
}