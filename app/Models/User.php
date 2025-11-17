<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
// --- PERBAIKAN IMPORT ---
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str; // INI YANG BENAR
// ---------------------------------
use App\Models\WorkHistory;
use App\Models\Inventory;
use App\Models\LateNotification;
use App\Models\Division;
use App\Models\Branch;
use App\Models\Attendance;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'division_id',
        'qr_code_value',
        'qr_code_path', // TAMBAHKAN INI
        'branch_id',
        'profile_photo_path',
        'ktp_photo_path',
        'hire_date',
        'whatsapp',
        'instagram',
        'tiktok',
        'facebook',
        'linkedin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'hire_date' => 'date',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function activeLateStatus(): HasOne
    {
        return $this->hasOne(LateNotification::class)
                    ->where('is_active', true)
                    ->whereDate('created_at', today());
    }

    /**
     * Relasi ke Riwayat Pekerjaan
     */
    public function workHistories()
    {
        return $this->hasMany(WorkHistory::class);
    }

    /**
    * Relasi ke Inventory
    */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Generate QR Code for user
     */
    public function generateQrCode()
    {
        // Generate unique QR value jika belum ada
        if (!$this->qr_code_value) {
            $this->qr_code_value = 'EMP-' . $this->id . '-' . Str::random(8) . '-' . time();
            $this->save();
        }
        
        // Generate QR Code image
        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate($this->qr_code_value);
            
        $fileName = 'qrcodes/' . $this->id . '_' . time() . '.png';
        Storage::disk('public')->put($fileName, $qrCode);
        
        $this->update(['qr_code_path' => $fileName]);
        
        return $this;
    }

    /**
     * Get QR Code URL
     */
    public function getQrCodeUrlAttribute()
    {
        return $this->qr_code_path ? Storage::url($this->qr_code_path) : null;
    }

    /**
     * Check if user has QR code
     */
    public function hasQrCode()
    {
        return !empty($this->qr_code_value) && !empty($this->qr_code_path);
    }

    /**
     * Get user's today attendance
     */
    public function todayAttendance()
    {
        return $this->attendances()
                    ->whereDate('check_in_time', today())
                    ->first();
    }

    /**
     * Check if user has attended today
     */
    public function hasAttendedToday()
    {
        return $this->attendances()
                    ->whereDate('check_in_time', today())
                    ->exists();
    }

    /**
     * Get profile photo URL
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return Storage::url($this->profile_photo_path);
        }
        
        return null;
    }

    /**
     * Get KTP photo URL
     */
    public function getKtpPhotoUrlAttribute()
    {
        if ($this->ktp_photo_path) {
            return Storage::url($this->ktp_photo_path);
        }
        
        return null;
    }
}