<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'NIDN',
        'jabatan',
        'email',
        'phone',
        'image',
        'linkedin',
        'instagram',
        'youtube',
        'tiktok',
        'bio',
        'research_interests',
        'status'
    ];

    protected $casts = [
        'research_interests' => 'array'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
