@extends('layout.master')

@section('title')
    Tambah Jam Kerja
@endsection

@section('heading')
    Tambah Jam Kerja
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('work-schedules.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="schedule_name" class="form-label">Nama Schedule *</label>
                                <input type="text" class="form-control @error('schedule_name') is-invalid @enderror" 
                                       id="schedule_name" name="schedule_name"
                                       value="{{ old('schedule_name') }}" required placeholder="Contoh: Jam Kerja Bebas / Office Hour">
                                @error('schedule_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branch_id" class="form-label">Cabang (Opsional)</label>
                                <select class="form-select" id="branch_id" name="branch_id">
                                    <option value="">Semua Cabang</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="division_id" class="form-label">Divisi (Opsional)</label>
                                <select class="form-select" id="division_id" name="division_id">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-2 mb-4">
                        <i class="mdi mdi-information-outline me-2"></i>
                        <strong>Catatan:</strong> Kosongkan jam di bawah ini jika ingin membuat jadwal <strong>Fleksibel / Bebas</strong> (Karyawan bisa absen kapan saja).
                    </div>

                    <div class="row">
                        {{-- CARD JAM MASUK --}}
                        <div class="col-md-6">
                            <div class="card border mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="mdi mdi-clock-in me-2"></i>Jam Masuk</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="check_in_start" class="form-label">Mulai Check In</label>
                                                <input type="time" class="form-control" id="check_in_start" 
                                                       name="check_in_start" 
                                                       value="{{ old('check_in_start', '08:00') }}">
                                                <small class="text-muted" style="font-size: 10px;">Biarkan kosong jika bebas</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="check_in_end" class="form-label">Akhir Check In</label>
                                                <input type="time" class="form-control" id="check_in_end" 
                                                       name="check_in_end" 
                                                       value="{{ old('check_in_end', '10:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CARD JAM PULANG --}}
                        <div class="col-md-6">
                            <div class="card border mb-3">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="mdi mdi-clock-out me-2"></i>Jam Pulang</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="check_out_start" class="form-label">Mulai Check Out</label>
                                                <input type="time" class="form-control" id="check_out_start" 
                                                       name="check_out_start" 
                                                       value="{{ old('check_out_start', '16:00') }}">
                                                <small class="text-muted" style="font-size: 10px;">Biarkan kosong jika bebas</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="check_out_end" class="form-label">Akhir Check Out</label>
                                                <input type="time" class="form-control" id="check_out_end" 
                                                       name="check_out_end" 
                                                       value="{{ old('check_out_end', '18:00') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_default" 
                                       name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    Jadikan sebagai schedule default
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Aktifkan schedule
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="mdi mdi-content-save me-2"></i>Simpan
                        </button>
                        <a href="{{ route('work-schedules.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection