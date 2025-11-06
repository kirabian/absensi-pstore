<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // Nama tabel otomatis 'attendances'
    // Primary key otomatis 'id'

    protected $fillable = [
        'user_id',
        'check_in_time',
        'status',
        'photo_path',
        'scanned_by_user_id',
        'verified_by_user_id',
    ];
}
