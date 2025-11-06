<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    // Nama tabel otomatis 'divisions', jadi tidak perlu $table
    // Primary key otomatis 'id', jadi tidak perlu $primaryKey

    protected $fillable = [
        'name',
    ];
}
