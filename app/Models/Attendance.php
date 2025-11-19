<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'check_in_time',
        'check_out_time',
        'status',
        'photo_path',
        'photo_out_path',
        'scanned_by_user_id',
        'verified_by_user_id',
        'latitude',
        'longitude',
        'work_schedule_id', // KOLOM BARU
        'is_late_checkin',  // KOLOM BARU
        'is_early_checkout', // KOLOM BARU
        'attendance_type', // KOLOM BARU
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'is_late_checkin' => 'boolean',
        'is_early_checkout' => 'boolean',
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
     * Relasi ke Work Schedule - RELASI BARU
     */
    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
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
     * Scope untuk absensi terlambat
     */
    public function scopeLate($query)
    {
        return $query->where('is_late_checkin', true);
    }

    /**
     * Scope untuk absensi pulang cepat
     */
    public function scopeEarlyCheckout($query)
    {
        return $query->where('is_early_checkout', true);
    }

    /**
     * Cek apakah sudah absen hari ini
     */
    public static function hasUserAttendedToday($userId)
    {
        return static::forUser($userId)->today()->exists();
    }

    /**
     * Get today's attendance for user
     */
    public static function getTodayAttendance($userId)
    {
        return static::forUser($userId)->today()->first();
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
     * Cek validitas check-in berdasarkan work schedule
     */
    public function isValidCheckIn()
    {
        if (!$this->workSchedule) {
            return true; // Jika tidak ada schedule, dianggap valid
        }

        $checkInTime = Carbon::parse($this->check_in_time);
        $scheduleStart = Carbon::parse($this->workSchedule->check_in_start);
        $scheduleEnd = Carbon::parse($this->workSchedule->check_in_end);

        return $checkInTime->between($scheduleStart, $scheduleEnd);
    }

    /**
     * Cek validitas check-out berdasarkan work schedule
     */
    public function isValidCheckOut()
    {
        if (!$this->check_out_time || !$this->workSchedule) {
            return true;
        }

        $checkOutTime = Carbon::parse($this->check_out_time);
        $scheduleStart = Carbon::parse($this->workSchedule->check_out_start);
        $scheduleEnd = Carbon::parse($this->workSchedule->check_out_end);

        return $checkOutTime->between($scheduleStart, $scheduleEnd);
    }

    /**
     * Hitung durasi kerja
     */
    public function getWorkDurationAttribute()
    {
        if (!$this->check_out_time) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);

        return $checkOut->diff($checkIn)->format('%H:%I:%S');
    }

    /**
     * Tandai sebagai terlambat
     */
    public function markAsLate()
    {
        $this->is_late_checkin = true;
        return $this->save();
    }

    /**
     * Tandai sebagai pulang cepat
     */
    public function markAsEarlyCheckout()
    {
        $this->is_early_checkout = true;
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
     * Get formatted check out time
     */
    public function getFormattedCheckOutTimeAttribute()
    {
        return $this->check_out_time ? $this->check_out_time->format('H:i:s') : null;
    }

    /**
     * Get formatted check in date
     */
    public function getFormattedCheckInDateAttribute()
    {
        return $this->check_in_time->format('d-m-Y');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Cek apakah sudah pulang
     */
    public function getHasCheckedOutAttribute()
    {
        return !is_null($this->check_out_time);
    }

    /**
     * Apply work schedule validation
     */
    public function applyWorkScheduleValidation()
    {
        $workSchedule = WorkSchedule::getScheduleForUser($this->user_id);
        
        if ($workSchedule) {
            $this->work_schedule_id = $workSchedule->id;
            
            // Validasi check-in
            if (!$this->isValidCheckIn()) {
                $this->is_late_checkin = true;
                $this->status = 'late';
            }

            // Validasi check-out
            if ($this->check_out_time && !$this->isValidCheckOut()) {
                $this->is_early_checkout = true;
            }
        }

        return $this;
    }

    /**
     * Get attendance type label
     */
    public function getAttendanceTypeLabelAttribute()
    {
        $labels = [
            'scan' => 'Scan QR',
            'self' => 'Absen Mandiri',
            'manual' => 'Manual'
        ];

        return $labels[$this->attendance_type] ?? $this->attendance_type;
    }
}