<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'file_proof',
        'status',
        'rejection_reason',
        'is_active', // tambahkan ini
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean', // tambahkan ini
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk izin telat aktif
    public function scopeActiveLatePermissions($query)
    {
        return $query->where('type', 'telat')
                    ->where('is_active', true)
                    ->where('status', 'approved')
                    ->whereDate('start_date', today());
    }
}