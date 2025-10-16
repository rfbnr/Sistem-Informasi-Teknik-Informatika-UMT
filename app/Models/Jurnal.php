<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'authors',
        'abstract',
        'category',
        'journal_name',
        'publication_date',
        'volume',
        'issue',
        'pages',
        'doi',
        'url',
        'keywords',
        'status',
        'impact_factor'
    ];

    protected $casts = [
        'authors' => 'array',
        'keywords' => 'array',
        'publication_date' => 'date',
        'impact_factor' => 'decimal:2'
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRecent($query, $months = 12)
    {
        return $query->where('publication_date', '>=', now()->subMonths($months));
    }
}
