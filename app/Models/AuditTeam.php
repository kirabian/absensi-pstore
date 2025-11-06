<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTeam extends Model
{
    use HasFactory;

    // Nama tabel otomatis 'audit_teams'
    // Primary key otomatis 'id'

    protected $fillable = [
        'user_id',
        'division_id',
    ];

    /**
     * Kita nonaktifkan timestamps (created_at, updated_at)
     * untuk tabel pivot ini.
     */
    public $timestamps = false;
}
