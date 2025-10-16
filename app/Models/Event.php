<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'organizer',
        'max_participants',
        'current_participants',
        'registration_deadline',
        'status',
        'event_type',
        'image'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'registration_deadline' => 'datetime',
        'max_participants' => 'integer',
        'current_participants' => 'integer'
    ];

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOpenRegistration($query)
    {
        return $query->where('registration_deadline', '>=', now())
                    ->where('current_participants', '<', 'max_participants');
    }
}
