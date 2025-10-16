<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'access_type',
        'granted_by',
        'granted_at',
        'expires_at'
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grantor()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}