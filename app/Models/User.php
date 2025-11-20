<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
// --- TAMBAHAN UNTUK MERAPIKAN ---
use App\Models\WorkHistory;
use App\Models\Inventory;
use App\Models\LateNotification;
use App\Models\Division;
use App\Models\Branch;
use App\Models\Attendance;
// ---------------------------------

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
        'branch_id',
        // --- TAMBAHAN PROFIL BARU ---
        'profile_photo_path',
        'ktp_photo_path',
        'hire_date',
        'whatsapp',
        'instagram',
        'tiktok',
        'facebook',
        'linkedin',
        // -------------------------
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
        'hire_date' => 'date', // <-- Tanggal Masuk
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

        public function broadcasts()
    {
        return $this->hasMany(Broadcast::class, 'created_by');
    }

    
}