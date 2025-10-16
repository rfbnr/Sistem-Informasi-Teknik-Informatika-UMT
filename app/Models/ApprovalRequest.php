<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor',
        'document_name',
        'document_path',
        'signed_document_path',
        'notes',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $addHttpCookie = true;
    
     // Generate nomor otomatis saat create
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ambil nomor terakhir dari tabel approval_requests
            $lastNumber = DB::table('approval_requests')->max('nomor');

            if ($lastNumber) {
                // Jika ada nomor, tambahkan 1
                $newNumber = str_pad(intval($lastNumber) + 1, 3, '0', STR_PAD_LEFT);
            } else {
                // Jika tidak ada data, mulai dari 001
                $newNumber = '001';
            }

            // Set nomor ke model
            $model->nomor = $newNumber;
        });
    }

}
