<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// JANGAN LUPA TAMBAHKAN INI
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'admin', 'audit', 'security', 'user_biasa'
        'division_id',
        'qr_code_value',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // --- TAMBAHKAN FUNGSI INI ---
    /**
     * Mendapatkan divisi tempat user ini berada.
     * Ini adalah relasi 'belongsTo' (satu user memiliki satu divisi).
     */
    public function division(): BelongsTo
    {
        // 'division_id' adalah foreign key di tabel 'users'
        // 'id' adalah primary key di tabel 'divisions'
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }
    // --- BATAS TAMBAHAN ---

}
