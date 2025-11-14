<?php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class WorkHistory extends Model
    {
        use HasFactory;
        
        // Kolom yang boleh diisi
        protected $fillable = [
            'user_id', 'position', 'department', 'start_date', 'end_date'
        ];
        
        // Otomatis ubah jadi objek Carbon (Tanggal)
        protected $casts = [
            'start_date' => 'date',
            'end_date' => 'date',
        ];

        // Relasi ke User
        public function user()
        {
            return $this->belongsTo(User::class);
        }
    }