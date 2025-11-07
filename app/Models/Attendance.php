<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Nama tabel otomatis terdeteksi sebagai 'attendances'.
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'user_id',
        'check_in_time',
        'status',
        'photo_path',
        'scanned_by_user_id',
        'verified_by_user_id',
        'latitude', // Kolom baru dari migrasi
        'longitude', // Kolom baru dari migrasi
    ];

    // --- INI ADALAH PERBAIKANNYA ---
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // Ini akan mengubah 'check_in_time' menjadi objek Carbon
        // sehingga Anda bisa menggunakan ->format()
        'check_in_time' => 'datetime',
    ];
    // --- BATAS PERBAIKAN ---

    /**
     * Relasi many-to-one: Absensi ini milik satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi many-to-one: Absensi ini di-scan oleh satu Security (User).
     */
    public function scanner()
    {
        // Kita beri nama 'scanner' agar beda, tapi menunjuk ke User
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    /**
     * Relasi many-to-one: Absensi ini diverifikasi oleh satu Audit (User).
     */
    public function verifier()
    {
        // Kita beri nama 'verifier', menunjuk ke User
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
