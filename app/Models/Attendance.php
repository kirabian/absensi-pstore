<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'check_in_time',
        'status',
        'photo_path',
        'photo_out_path', // Foto Pulang (TAMBAHKAN INI)
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
     * Scope untuk absensi berdasarkan scanner (security)
     */
    public function scopeScannedBy($query, $securityId)
    {
        return $query->where('scanned_by_user_id', $securityId);
    }

    /**
     * Scope untuk absensi yang belum diverifikasi
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('verified_by_user_id');
    }

    /**
     * Cek apakah sudah absen hari ini
     */
    public static function hasUserAttendedToday($userId)
    {
        return static::forUser($userId)->today()->exists();
    }

    /**
     * Verifikasi absensi oleh audit
     */
    public function verify($auditUserId)
    {
        $this->verified_by_user_id = $auditUserId;
        return $this->save();
    }

    /**
     * Get formatted check in time
     */
    public function getFormattedCheckInTimeAttribute()
    {
        return $this->check_in_time->format('H:i:s');
    }

    /**
     * Get formatted check in date
     */
    public function getFormattedCheckInDateAttribute()
    {
        return $this->check_in_time->format('d-m-Y');
    }
}