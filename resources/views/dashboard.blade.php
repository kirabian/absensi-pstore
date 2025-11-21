@extends('layout.master')

@section('title')
    Dashboard
@endsection

@section('heading')
    Selamat Datang, {{ Auth::user()->name }}!
@endsection

@section('content')

    {{-- ======================================================================= --}}
    {{-- TAMPILAN UNTUK ADMIN --}}
    {{-- ======================================================================= --}}
    @if (auth()->user()->role == 'admin')
        <div class="row">
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-purple">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-account-multiple"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total User</p>
                            <h2 class="card-bank-value">{{ $totalUsers }}</h2>
                            <p class="card-bank-desc">User terdaftar di sistem</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-blue">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-sitemap"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total Divisi</p>
                            <h2 class="card-bank-value">{{ $totalDivisions }}</h2>
                            <p class="card-bank-desc">Divisi aktif</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-green">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-calendar-check"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Absensi Hari Ini</p>
                            <h2 class="card-bank-value">{{ $attendancesToday }}</h2>
                            <p class="card-bank-desc">Total absensi hari ini</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-orange">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value">{{ $pendingVerifications }}</h2>
                            <p class="card-bank-desc">Menunggu persetujuan</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card card-action">
                    <div class="card-body text-center py-5">
                        <i class="mdi mdi-shield-account display-3 text-dark mb-4"></i>
                        <h4 class="card-title mb-3">Ringkasan Sistem</h4>
                        <p class="text-muted mb-4">Sebagai Admin, Anda memiliki kontrol penuh atas semua modul sistem.</p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ route('divisions.index') }}" class="btn btn-dark btn-lg">
                                <i class="mdi mdi-sitemap me-2"></i>Kelola Divisi
                            </a>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-dark btn-lg">
                                <i class="mdi mdi-account-multiple me-2"></i>Kelola User
                            </a>
                            <a href="{{ route('broadcast.index') }}" class="btn btn-dark btn-lg">
                                <i class="mdi mdi-bullhorn me-2"></i>Kelola Broadcast
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- TAMPILAN UNTUK AUDIT --}}
        {{-- ======================================================================= --}}
    @elseif (auth()->user()->role == 'audit')
        <div class="row">
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card card-bank gradient-red">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value">{{ $pendingVerifications }}</h2>
                            <p class="card-bank-desc">Absensi menunggu persetujuan</p>
                            <a href="{{ route('audit.verify.list') }}" class="btn btn-sm btn-light mt-2">
                                <i class="mdi mdi-clipboard-check me-1"></i>Lihat Daftar
                            </a>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card card-bank gradient-blue">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-account-multiple"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Anggota Tim</p>
                            <h2 class="card-bank-value">{{ $myTeamMembers }}</h2>
                            <p class="card-bank-desc">Total anggota dalam tim</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card card-bank gradient-green">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-calendar-check"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Absen Hari Ini</p>
                            <h2 class="card-bank-value">{{ $attendancesToday }}</h2>
                            <p class="card-bank-desc">Tim sudah absen hari ini</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- TAMPILAN UNTUK SECURITY --}}
        {{-- ======================================================================= --}}
    @elseif (auth()->user()->role == 'security')
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-action">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="mdi mdi-qrcode-scan display-1 text-dark"></i>
                        </div>
                        <h4 class="card-title mb-3">Pindai QR User</h4>
                        <p class="text-muted mb-4">Arahkan kamera ke QR Code user untuk melakukan absensi (Masuk/Pulang).
                        </p>
                        <a href="{{ route('security.scan') }}" class="btn btn-dark btn-lg">
                            <i class="mdi mdi-camera-enhance me-2"></i>Mulai Memindai
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-bank gradient-dark">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-chart-bar"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Pindaian Hari Ini</p>
                            <h2 class="card-bank-value">{{ $myScansToday }}</h2>
                            <p class="card-bank-desc">Total pindaian QR hari ini</p>
                            <div class="mt-4 pt-3 border-top border-light">
                                <p class="card-bank-label mb-2">User Aktif</p>
                                <h3 class="card-bank-value mb-0">{{ $totalUsers }}</h3>
                            </div>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- TAMPILAN UNTUK USER BIASA & LEADER --}}
        {{-- ======================================================================= --}}
    @elseif (auth()->user()->role == 'user_biasa' || auth()->user()->role == 'leader' || auth()->user()->role == 'admin' || auth()->user()->role == 'security' || auth()->user()->role == 'audit')
        <div class="row">
            {{-- KARTU ID BARU --}}
            <div class="col-md-5 grid-margin stretch-card">
                <div class="card card-id gradient-dark">
                    <div class="card-body">
                        <div class="card-id-header">
                            <div class="card-bank-chip"></div>
                            <div class="card-id-logo">
                                <i class="mdi mdi-credit-card-outline"></i>
                                <span>ID Card</span>
                            </div>
                        </div>
                        <div class="card-id-details">
                            <p class="card-id-label">NAMA</p>
                            <h3 class="card-id-name">{{ strtoupper(Auth::user()->name) }}</h3>
                            <p class="card-id-label">DIVISI</p>
                            <h4 class="card-id-division">
                                {{ strtoupper(Auth::user()->division->name ?? 'BELUM ADA DIVISI') }}</h4>
                        </div>
                        <div class="card-id-footer">
                            <p class="card-id-valid">VALID THRU 12/28</p>
                            <p class="card-id-card-number">**** **** **** {{ substr(Auth::user()->phone ?? '1234', -4) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU STATUS ABSENSI --}}
            <div class="col-md-7 grid-margin stretch-card">
                <div class="card card-status">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="mdi mdi-calendar-today me-2"></i>Status Absensi Hari Ini
                        </h4>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="mdi mdi-check-circle-outline me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- LOGIKA TAMPILAN DENGAN HANDLE DATA RUSAK --}}
                        @if ($myAttendanceToday)
                            {{-- KONDISI 1: SUDAH PULANG (check_out_time ADA ATAU photo_out_path ADA) --}}
                            @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                                <div class="status-card status-success mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon">
                                            <i class="mdi mdi-home-variant"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1 fw-bold">Anda Sudah Pulang</h5>
                                            <p class="text-muted mb-0 small">Terima kasih atas kerja keras Anda hari ini!
                                            </p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row text-center">
                                        <div class="col-6 border-end">
                                            <small class="text-muted d-block">JAM MASUK</small>
                                            <h4 class="fw-bold text-success mb-0">
                                                {{ $myAttendanceToday->check_in_time->format('H:i') }}</h4>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">JAM PULANG</small>
                                            <h4 class="fw-bold text-primary mb-0">
                                                @if ($myAttendanceToday->check_out_time)
                                                    {{ $myAttendanceToday->check_out_time->format('H:i') }}
                                                @else
                                                    {{ $myAttendanceToday->check_in_time->addHour()->format('H:i') }}
                                                @endif
                                            </h4>
                                        </div>
                                    </div>

                                    {{-- Tampilkan Foto Pulang Jika Ada --}}
                                    @if ($myAttendanceToday->photo_out_path)
                                        <div class="mt-3 text-center border-top pt-3">
                                            <p class="small text-muted mb-2"><i class="mdi mdi-camera me-1"></i>Bukti Foto
                                                Pulang</p>
                                            <img src="{{ asset('storage/' . $myAttendanceToday->photo_out_path) }}"
                                                class="img-fluid rounded shadow-sm border"
                                                style="height: 100px; object-fit: cover;" alt="Foto Pulang">
                                        </div>
                                    @endif
                                </div>

                                {{-- KONDISI 2: BARU MASUK (BELUM PULANG) --}}
                            @else
                                @php
                                    // Tentukan Warna dan Icon berdasarkan Status & Tipe Absen
                                    if (
                                        $myAttendanceToday->attendance_type == 'scan' ||
                                        $myAttendanceToday->status == 'present'
                                    ) {
                                        // Jika Scan Security ATAU Sudah Diapprove Audit -> HIJAU
                                        $cardClass = 'status-success';
                                        $iconClass = 'mdi-check-circle';
                                        $statusText = 'Sedang Bekerja (Terverifikasi)';
                                    } else {
                                        // Jika Absen Mandiri DAN Masih Pending -> KUNING/ORANGE
                                        $cardClass = 'status-warning';
                                        $iconClass = 'mdi-clock-alert';
                                        $statusText = 'Menunggu Verifikasi Audit';
                                    }
                                @endphp

                                <div class="status-card {{ $cardClass }} mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon">
                                            <i class="mdi {{ $iconClass }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1 fw-bold">{{ $statusText }}</h5>
                                            <p class="mb-0">
                                                Masuk Pukul:
                                                <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong>
                                            </p>

                                            {{-- Badge Tipe Absen --}}
                                            <div class="mt-1">
                                                @if ($myAttendanceToday->attendance_type == 'scan')
                                                    <span class="badge bg-success text-white" style="font-size: 10px;">
                                                        <i class="mdi mdi-security me-1"></i>Security Scan
                                                    </span>
                                                @elseif($myAttendanceToday->attendance_type == 'self')
                                                    <span class="badge bg-warning text-dark" style="font-size: 10px;">
                                                        <i class="mdi mdi-face-recognition me-1"></i>Selfie Mandiri
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tombol Absen Pulang (Muncul jika Self Attendance & Belum Pulang) --}}
                                    @if ($myAttendanceToday->attendance_type == 'self' && !$myAttendanceToday->check_out_time)
                                        <div class="mt-3 pt-3 border-top text-center">
                                            <p class="text-muted small mb-2">Ingin pulang? Lakukan absen mandiri lagi.</p>
                                            <a href="{{ route('self.attend.create') }}"
                                                class="btn btn-danger btn-sm w-100">
                                                <i class="mdi mdi-logout me-1"></i>Absen Pulang Mandiri
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Tampilkan Foto Masuk --}}
                                    @if ($myAttendanceToday->photo_path)
                                        <div class="mt-3 text-center border-top pt-3">
                                            <p class="small text-muted mb-2"><i class="mdi mdi-camera me-1"></i>Bukti Foto
                                                Masuk</p>
                                            <img src="{{ asset('storage/' . $myAttendanceToday->photo_path) }}"
                                                class="img-fluid rounded shadow-sm border"
                                                style="height: 100px; object-fit: cover;" alt="Foto Masuk">
                                        </div>
                                    @endif
                                </div>

                                {{-- Pesan Info --}}
                                @if ($myAttendanceToday->attendance_type == 'scan')
                                    <div class="alert alert-info border-0 bg-light text-dark mt-2">
                                        <i class="mdi mdi-information me-2"></i> Jangan lupa scan QR
                                        <strong>Pulang</strong> di Security.
                                    </div>
                                @endif
                            @endif

                            {{-- KONDISI 3: BELUM ABSEN SAMA SEKALI --}}
                        @else
                            <div class="status-card status-info">
                                <div class="text-center py-4">
                                    <i class="mdi mdi-clock-alert display-4 mb-3 text-primary"></i>
                                    <h5 class="mb-2 fw-bold">Anda Belum Absen Hari Ini</h5>
                                    <p class="text-muted mb-4">Silakan scan QR di pos security atau gunakan absen mandiri
                                        jika WFH/Dinas.</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('self.attend.create') }}" class="btn btn-dark">
                                            <i class="mdi mdi-fingerprint me-2"></i>Absen Mandiri
                                        </a>
                                        <a href="{{ route('leave.create') }}" class="btn btn-outline-dark">
                                            <i class="mdi mdi-file-document-edit-outline me-2"></i>Izin/Sakit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU STATISTIK --}}
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-bank gradient-indigo">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon">
                            <i class="mdi mdi-chart-pie"></i>
                        </div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value">{{ $myPendingCount }}</h2>
                            <p class="card-bank-desc">Absensi menunggu persetujuan audit</p>
                            <div class="mt-4 pt-3 border-top border-light">
                                <p class="card-bank-label mb-2">Rekan Satu Divisi</p>
                                <h3 class="card-bank-value mb-0">{{ $myTeamCount }}</h3>
                            </div>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>

            {{-- KARTU AKSI CEPAT --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-action">
                    <div class="card-body py-4">
                        <h5 class="card-title mb-4"><i class="mdi mdi-lightning-bolt me-2"></i>Aksi Cepat</h5>
                        <div class="d-grid gap-3">
                            <a href="{{ route('leave.create') }}" class="btn btn-light text-start p-3 border">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger text-white rounded p-2 me-3">
                                        <i class="mdi mdi-hospital-box"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Ajukan Sakit / Izin</h6>
                                        <small class="text-muted">Formulir ketidakhadiran</small>
                                    </div>
                                </div>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="btn btn-light text-start p-3 border">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded p-2 me-3">
                                        <i class="mdi mdi-account-cog"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Update Profil</h6>
                                        <small class="text-muted">Ubah foto & data diri</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- SECTION STATISTIK ABSENSI UNTUK SEMUA ROLE --}}
    {{-- ======================================================================= --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="mdi mdi-chart-pie me-2"></i>Statistik Absensi
                    </h4>
                    <div>
                        <a href="{{ route('dashboard.export-pdf') }}" class="btn btn-danger btn-sm">
                            <i class="mdi mdi-file-pdf-box me-1"></i>Export PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Pie Chart --}}
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="attendancePieChart"></canvas>
                            </div>
                        </div>
                        
                        {{-- Statistik Detail --}}
                        <div class="col-md-6">
                            <div class="row">
                                @if(auth()->user()->role == 'admin')
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-primary text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Hadir</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['present'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-primary">{{ $attendanceStats['present_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-warning text-dark p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Terlambat</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['late'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-warning">{{ $attendanceStats['late_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-info text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Pending</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['pending'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-info">{{ $attendanceStats['pending_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-danger text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Tidak Hadir</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['absent'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-danger">{{ $attendanceStats['absent_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                @elseif(auth()->user()->role == 'audit')
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-success text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Terverifikasi</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['verified'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-success">{{ $attendanceStats['verified_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-warning text-dark p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Menunggu</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['pending'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-warning">{{ $attendanceStats['pending_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-danger text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Terlambat</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['late'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-danger">{{ $attendanceStats['late_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                @elseif(auth()->user()->role == 'security')
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-primary text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Total Scan</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['total_scans'] }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-success text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Scan Masuk</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['check_in_scans'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-success">{{ $attendanceStats['check_in_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-info text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Scan Pulang</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['check_out_scans'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-info">{{ $attendanceStats['check_out_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                @elseif(auth()->user()->role == 'user_biasa' || auth()->user()->role == 'leader' || auth()->user()->role == 'admin' || auth()->user()->role == 'security' || auth()->user()->role == 'audit')
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-success text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Hadir</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['present'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-success">{{ $attendanceStats['present_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-warning text-dark p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Terlambat</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['late'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-warning">{{ $attendanceStats['late_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-info text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Tepat Waktu</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['on_time'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-info">{{ $attendanceStats['on_time_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-secondary text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Pending</h6>
                                                    <h3 class="mb-0">{{ $attendanceStats['pending'] }}</h3>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-secondary">{{ $attendanceStats['pending_percentage'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* Card Bank Style */
        .card-bank {
            position: relative;
            min-height: 200px;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }

        .card-bank:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        }

        .card-bank .card-body {
            position: relative;
            z-index: 2;
            padding: 24px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-bank-chip {
            width: 40px;
            height: 30px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            border-radius: 6px;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .card-bank-chip::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 50%;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 100%);
            border-radius: 6px 6px 0 0;
        }

        .card-bank-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 48px;
            opacity: 0.2;
        }

        .card-bank-content {
            position: relative;
            z-index: 3;
        }

        .card-bank-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .card-bank-value {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1;
        }

        .card-bank-desc {
            font-size: 13px;
            opacity: 0.85;
            margin-bottom: 0;
        }

        .card-bank-pattern {
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 1;
        }

        /* Gradient Themes */
        .gradient-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .gradient-green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .gradient-orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .gradient-red {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .gradient-dark {
            background: linear-gradient(135deg, #2c3e50 0%, #000000 100%);
        }

        .gradient-indigo {
            background: linear-gradient(135deg, #5f72bd 0%, #9b23ea 100%);
        }

        /* ID Card Style */
        .card-id {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            color: white;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            font-family: 'Roboto', sans-serif;
        }

        .card-id .card-body {
            position: relative;
            z-index: 2;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
            gap: 15px;
        }

        .card-id-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-id-logo {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        .card-id-logo i {
            font-size: 38px;
            margin-bottom: 4px;
            color: #ffed4e;
        }

        .card-id-details {
            flex-grow: 1;
        }

        .card-id-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            opacity: 0.7;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .card-id-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.2;
            word-break: break-word;
            font-family: 'Consolas', 'Courier New', monospace;
        }

        .card-id-division {
            font-size: 16px;
            font-weight: 500;
            opacity: 0.9;
            word-break: break-word;
            font-family: 'Consolas', 'Courier New', monospace;
        }

        .card-id-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
        }

        .card-id-valid,
        .card-id-card-number {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.8;
            font-family: 'Consolas', 'Courier New', monospace;
            letter-spacing: 0.5px;
        }

        /* Other Cards */
        .card-action {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .card-action:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        .card-status {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .status-card {
            padding: 24px;
            border-radius: 12px;
            border: 2px solid;
            background: #f8fafc;
        }

        .status-success {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        }

        .status-warning {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }

        .status-info {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        .status-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 28px;
            flex-shrink: 0;
        }

        .status-success .status-icon {
            background: #10b981;
            color: white;
        }

        .status-warning .status-icon {
            background: #f59e0b;
            color: white;
        }

        .status-info .status-icon {
            background: #3b82f6;
            color: white;
        }

        /* Statistik Cards */
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .badge {
            border-radius: 8px;
            font-weight: 600;
            padding: 6px 12px;
        }

        .btn-dark {
            background: #000;
            border: 2px solid #000;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 28px;
            transition: all 0.3s ease;
        }

        .btn-dark:hover {
            background: #1f2937;
            border-color: #1f2937;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-dark {
            border: 2px solid #000;
            color: #000;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 28px;
            transition: all 0.3s ease;
        }

        .btn-outline-dark:hover {
            background: #000;
            color: white;
            transform: translateY(-2px);
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            color: #1f2937;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-light:hover {
            background: white;
            color: #000;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 16px 20px;
        }

        @media (max-width: 768px) {
            .card-bank-value {
                font-size: 28px;
            }

            .card-bank {
                min-height: 180px;
            }

            .card-id {
                min-height: 200px;
            }

            .card-id .card-body {
                padding: 20px;
                gap: 10px;
            }

            .card-id-name {
                font-size: 20px;
            }

            .card-id-division {
                font-size: 14px;
            }

            .card-id-logo i {
                font-size: 32px;
            }

            .card-id-valid,
            .card-id-card-number {
                font-size: 10px;
            }
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendancePieChart').getContext('2d');
    
    @if(auth()->user()->role == 'admin')
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Hadir', 'Terlambat', 'Pending', 'Tidak Hadir'],
                datasets: [{
                    data: [
                        {{ $attendanceStats['present'] }},
                        {{ $attendanceStats['late'] }},
                        {{ $attendanceStats['pending'] }},
                        {{ $attendanceStats['absent'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#17a2b8',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    @elseif(auth()->user()->role == 'audit')
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Terverifikasi', 'Menunggu', 'Terlambat'],
                datasets: [{
                    data: [
                        {{ $attendanceStats['verified'] }},
                        {{ $attendanceStats['pending'] }},
                        {{ $attendanceStats['late'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    @elseif(auth()->user()->role == 'security')
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Scan Masuk', 'Scan Pulang'],
                datasets: [{
                    data: [
                        {{ $attendanceStats['check_in_scans'] }},
                        {{ $attendanceStats['check_out_scans'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    @else
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Hadir', 'Terlambat', 'Tepat Waktu', 'Pending'],
                datasets: [{
                    data: [
                        {{ $attendanceStats['present'] }},
                        {{ $attendanceStats['late'] }},
                        {{ $attendanceStats['on_time'] }},
                        {{ $attendanceStats['pending'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#17a2b8',
                        '#6c757d'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    @endif
});
</script>
@endpush