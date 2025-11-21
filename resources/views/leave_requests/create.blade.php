@extends('layout.master')

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Form Pengajuan Izin</h4>
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
            </div>
        @endif

        <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label>Jenis Izin</label>
                <select name="type" id="type" class="form-control" required onchange="toggleInputs()">
                    <option value="sakit">Sakit / Izin (Cuti)</option>
                    <option value="telat">Datang Terlambat</option>
                </select>
            </div>

            {{-- Input Tanggal --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label id="label_start_date">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                {{-- Input Tanggal Selesai (Untuk Sakit) --}}
                <div class="col-md-6 mb-3" id="end_date_box">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                {{-- Input Jam (Untuk Telat) --}}
                <div class="col-md-6 mb-3 d-none" id="time_box">
                    <label>Perkiraan Jam Sampai</label>
                    <input type="time" name="start_time" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label>Alasan</label>
                <textarea name="reason" class="form-control" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label>Bukti Foto (Surat Dokter / Kondisi Jalan)</label>
                <input type="file" name="file_proof" class="form-control" accept="image/*,.pdf" required>
                <small class="text-muted">Wajib menyertakan bukti foto.</small>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<script>
function toggleInputs() {
    let type = document.getElementById('type').value;
    let endDateBox = document.getElementById('end_date_box');
    let timeBox = document.getElementById('time_box');
    let labelDate = document.getElementById('label_start_date');

    if (type === 'telat') {
        endDateBox.classList.add('d-none');
        timeBox.classList.remove('d-none');
        labelDate.innerText = "Tanggal Hari Ini";
    } else {
        endDateBox.classList.remove('d-none');
        timeBox.classList.add('d-none');
        labelDate.innerText = "Tanggal Mulai";
    }
}
</script>
@endsection