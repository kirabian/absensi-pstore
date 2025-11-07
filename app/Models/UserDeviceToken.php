<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeviceToken extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'user_id',
        'token',
    ];

    /**
     * Relasi ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
