<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'created_by',
        'priority',
        'is_published',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('published_at', '>=', now()->subDays($days));
    }

    // Helper untuk mendapatkan icon berdasarkan priority
    public function getPriorityIcon()
    {
        return match($this->priority) {
            'high' => 'mdi mdi-alert',
            'medium' => 'mdi mdi-information',
            'low' => 'mdi mdi-bullhorn-outline',
            default => 'mdi mdi-bullhorn-outline'
        };
    }

    // Helper untuk mendapatkan color berdasarkan priority
    public function getPriorityColor()
    {
        return match($this->priority) {
            'high' => 'text-danger',
            'medium' => 'text-warning',
            'low' => 'text-info',
            default => 'text-primary'
        };
    }
}