<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'NIM',
        'tahun_lulus',
        'company',
        'jabatan',
        'email',
        'phone',
        'image',
        'linkedin',
        'instagram',
        'youtube',
        'tiktok',
        'achievements',
        'testimonial',
        'status'
    ];

    protected $casts = [
        'achievements' => 'array',
        'tahun_lulus' => 'integer'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('tahun_lulus', $year);
    }
}
