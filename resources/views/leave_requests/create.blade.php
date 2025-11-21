@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Form Pengajuan Izin / Telat</h4>
        </div>
        <div class="card-body">
            
            {{-- Tampilkan Error Validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('leave-requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- 1. Pilih Tipe Izin --}}
                <div class="mb-3">
                    <label for="type" class="form-label">Jenis Pengajuan</label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="" disabled selected>-- Pilih Jenis --</option>
                        <option value="sakit" {{ old('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ old('type') == 'izin' ? 'selected' : '' }}>Izin (Cuti)</option>
                        <option value="telat" {{ old('type') == 'telat' ? 'selected' : '' }}>Datang Telat</option>
                    </select>
                </div>

                {{-- 2. Input Tanggal Mulai (Selalu Muncul) --}}
                <div class="mb-3">
                    <label for="start_date" class="form-label" id="label_start_date">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                    <small class="text-muted">Jika telat, isi dengan tanggal hari ini.</small>
                </div>

                {{-- 3. Input Tanggal Selesai (Khusus Sakit/Izin) --}}
                <div class="mb-3" id="end_date_container">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                </div>

                {{-- 4. Input Jam (Khusus Telat) --}}
                <div class="mb-3 d-none" id="time_container">
                    <label for="start_time" class="form-label">Perkiraan Jam Sampai Kantor</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time') }}">
                </div>

                {{-- 5. Alasan --}}
                <div class="mb-3">
                    <label for="reason" class="form-label">Alasan</label>
                    <textarea name="reason" id="reason" rows="3" class="form-control" required>{{ old('reason') }}</textarea>
                </div>

                {{-- 6. Upload Bukti --}}
                <div class="mb-3">
                    <label for="file_proof" class="form-label">Bukti Foto / Surat Dokter (Opsional)</label>
                    <input type="file" name="file_proof" id="file_proof" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript untuk Logika Tampilan --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type');
        const endDateContainer = document.getElementById('end_date_container');
        const timeContainer = document.getElementById('time_container');
        const labelStartDate = document.getElementById('label_start_date');

        function toggleFields() {
            const selectedType = typeSelect.value;

            if (selectedType === 'telat') {
                // Mode Telat: Sembunyikan End Date, Tampilkan Jam
                endDateContainer.classList.add('d-none'); // Hide
                timeContainer.classList.remove('d-none'); // Show
                
                // Ubah label tanggal biar lebih masuk akal
                labelStartDate.innerText = "Tanggal Telat";
            } else {
                // Mode Sakit/Izin: Tampilkan End Date, Sembunyikan Jam
                endDateContainer.classList.remove('d-none'); // Show
                timeContainer.classList.add('d-none'); // Hide

                // Balikin label tanggal
                labelStartDate.innerText = "Tanggal Mulai";
            }
        }

        // Jalankan saat user mengganti pilihan
        typeSelect.addEventListener('change', toggleFields);

        // Jalankan saat halaman dimuat (untuk handle old input jika validasi gagal)
        toggleFields();
    });
</script>
@endsection