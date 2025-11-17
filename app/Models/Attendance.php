<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id', // TAMBAHKAN INI - SANGAT PENTING!
        'check_in_time',
        'status',
        'photo_path',
        'scanned_by_user_id',
        'verified_by_user_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
    ];

    /**
     * Relasi many-to-one: Absensi ini milik satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi many-to-one: Absensi ini milik satu Branch.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relasi many-to-one: Absensi ini di-scan oleh satu Security (User).
     */
    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    /**
     * Relasi many-to-one: Absensi ini diverifikasi oleh satu Audit (User).
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /**
     * Scope untuk absensi hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('check_in_time', today());
    }

    /**
     * Scope untuk absensi berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Cek apakah sudah absen hari ini
     */
    public static function hasUserAttendedToday($userId)
    {
        return static::forUser($userId)->today()->exists();
    }
}