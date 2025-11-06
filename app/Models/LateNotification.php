<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateNotification extends Model
{
    use HasFactory;

    // Nama tabel otomatis 'late_notifications'
    // Primary key otomatis 'id'

    protected $fillable = [
        'user_id',
        'message',
        'is_active',
    ];
}
