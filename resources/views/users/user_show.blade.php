@extends('layout.master')

@section('title')
    Detail User: {{ $user->name }}
@endsection

@section('heading')
    Detail Pengguna
@endsection

@section('content')
<div class="row">
    {{-- ========================================================= --}}
    {{-- KOLOM KIRI: PROFIL PENGGUNA --}}
    {{-- ========================================================= --}}
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                
                {{-- Foto Profil --}}
                <div class="mb-4">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                             alt="profile" class="img-lg rounded-circle mb-3"
                             style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e3e3e3;">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&size=128" 
                             alt="profile" class="img-lg rounded-circle mb-3">
                    @endif
                    
                    <h4 class="fw-bold">{{ $user->name }}</h4>
                    <p class="text-muted mb-1">{{ strtoupper(str_replace('_', ' ', $user->role)) }}</p>
                    
                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->is_active ? 'Status: Aktif' : 'Status: Non-Aktif' }}
                    </span>
                </div>
                
                {{-- Detail Informasi --}}
                <div class="text-start border-top pt-3">
                    <div class="py-2">
                        <label class="text-muted small fw-bold">ID Login</label>
                        <p class="mb-0 text-dark font-weight-medium">{{ $user->login_id }}</p>
                    </div>
                    <div class="py-2">
                        <label class="text-muted small fw-bold">Cabang (Homebase)</label>
                        <p class="mb-0 text-dark">{{ $user->branch->name ?? 'Semua Cabang (Pusat)' }}</p>
                    </div>
                    <div class="py-2">
                        <label class="text-muted small fw-bold">Divisi</label>
                        <p class="mb-0 text-dark">{{ $user->division->name ?? '-' }}</p>
                    </div>
                    <div class="py-2">
                        <label class="text-muted small fw-bold">Tanggal Bergabung</label>
                        <p class="mb-0 text-dark">{{ $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->translatedFormat('d F Y') : '-' }}</p>
                    </div>
                    <div class="py-2">
                        <label class="text-muted small fw-bold">Kontak</label>
                        <p class="mb-0"><i class="mdi mdi-email me-1 text-primary"></i> {{ $user->email }}</p>
                        @if($user->whatsapp)
                            <p class="mb-0"><i class="mdi mdi-whatsapp me-1 text-success"></i> {{ $user->whatsapp }}</p>
                        @endif
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-4 d-grid gap-2">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm text-white">
                        <i class="mdi mdi-pencil"></i> Edit Profil
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-light btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- KOLOM KANAN: STATISTIK & HISTORY --}}
    {{-- ========================================================= --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Statistik Absensi ({{ $stats['current_month'] }})</h4>
                <p class="card-description">Ringkasan performa kehadiran bulan ini.</p>
                
                {{-- GRID STATISTIK --}}
                <div class="row mt-4">
                    {{-- Total Hadir --}}
                    <div class="col-md-4 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                                    <small>Total Kehadiran</small>
                                </div>
                                <i class="mdi mdi-calendar-check mdi-36px" style="opacity: 0.5"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Tepat Waktu --}}
                    <div class="col-md-4 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0 fw-bold">{{ $stats['on_time'] }}</h2>
                                    <small>Tepat Waktu ({{ $stats['on_time_percentage'] }}%)</small>
                                </div>
                                <i class="mdi mdi-clock-check mdi-36px" style="opacity: 0.5"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Terlambat --}}
                    <div class="col-md-4 mb-4">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0 fw-bold">{{ $stats['late'] }}</h2>
                                    <small>Terlambat ({{ $stats['late_percentage'] }}%)</small>
                                </div>
                                <i class="mdi mdi-clock-alert mdi-36px" style="opacity: 0.5"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Pulang Cepat --}}
                    <div class="col-md-6 mb-4">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0 fw-bold">{{ $stats['early'] }}</h2>
                                    <small>Pulang Cepat</small>
                                </div>
                                <i class="mdi mdi-run-fast mdi-36px" style="opacity: 0.5"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Pending Verifikasi --}}
                    <div class="col-md-6 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="mb-0 fw-bold">{{ $stats['pending'] }}</h2>
                                    <small>Butuh Verifikasi</small>
                                </div>
                                <i class="mdi mdi-account-search mdi-36px" style="opacity: 0.5"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- RECENT ACTIVITY TABLE --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 card-title">5 Riwayat Absensi Terakhir</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Tipe</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendance as $log)
                                <tr>
                                    {{-- Tanggal --}}
                                    <td>{{ \Carbon\Carbon::parse($log->check_in_time)->translatedFormat('d M Y') }}</td>
                                    
                                    {{-- Jam Masuk --}}
                                    <td>
                                        <span class="{{ $log->is_late_checkin ? 'text-danger fw-bold' : 'text-success' }}">
                                            {{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}
                                        </span>
                                        @if($log->is_late_checkin)
                                            <i class="mdi mdi-alert-circle text-danger" title="Terlambat"></i>
                                        @endif
                                    </td>
                                    
                                    {{-- Jam Pulang --}}
                                    <td>
                                        @if($log->check_out_time)
                                            <span class="{{ $log->is_early_checkout ? 'text-warning fw-bold' : 'text-success' }}">
                                                {{ \Carbon\Carbon::parse($log->check_out_time)->format('H:i') }}
                                            </span>
                                            @if($log->is_early_checkout)
                                                <i class="mdi mdi-run text-warning" title="Pulang Cepat"></i>
                                            @endif
                                        @else
                                            <span class="text-muted small">Belum Checkout</span>
                                        @endif
                                    </td>

                                    {{-- Tipe Absen --}}
                                    <td>
                                        @if($log->attendance_type == 'scan')
                                            <span class="badge badge-outline-primary">Security Scan</span>
                                        @elseif($log->attendance_type == 'self')
                                            <span class="badge badge-outline-info">Mandiri (Selfie)</span>
                                        @else
                                            <span class="badge badge-outline-secondary">System</span>
                                        @endif
                                    </td>

                                    {{-- Status Verifikasi --}}
                                    <td>
                                        @if($log->status == 'approved' || $log->status == 'present' || $log->status == 'late')
                                            <span class="badge badge-success">Valid</span>
                                        @elseif($log->status == 'rejected')
                                            <span class="badge badge-danger">Ditolak</span>
                                        @elseif($log->status == 'pending_verification')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $log->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="text-muted">Belum ada data absensi untuk user ini.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection