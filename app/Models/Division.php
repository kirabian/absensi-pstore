<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'branch_id',
    ];

    // Relasi ke Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relasi ke User (PERBAIKAN: Many-to-Many)
    // Kita gunakan belongsToMany agar bisa mengambil data dari tabel pivot 'division_user'
    public function users()
    {
        return $this->belongsToMany(User::class, 'division_user', 'division_id', 'user_id')
                    ->withTimestamps();
    }
}