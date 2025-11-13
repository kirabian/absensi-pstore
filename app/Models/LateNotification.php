<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'is_active',
    ];

    /**
     * Casting attributes
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
