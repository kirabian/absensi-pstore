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
                        <div class="card-bank-icon"><i class="mdi mdi-account-multiple"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total User</p>
                            <h2 class="card-bank-value">{{ $totalUsers ?? 0 }}</h2>
                            <p class="card-bank-desc">User terdaftar</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-blue">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-sitemap"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Total Divisi</p>
                            <h2 class="card-bank-value">{{ $totalDivisions ?? 0 }}</h2>
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
                        <div class="card-bank-icon"><i class="mdi mdi-calendar-check"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Absensi Hari Ini</p>
                            <h2 class="card-bank-value">{{ $attendancesToday ?? 0 }}</h2>
                            <p class="card-bank-desc">Total kehadiran</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card card-bank gradient-orange">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value">{{ $pendingVerifications ?? 0 }}</h2>
                            <p class="card-bank-desc">Menunggu persetujuan</p>
                        </div>
                        <div class="card-bank-pattern"></div>
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
                        <div class="card-bank-icon"><i class="mdi mdi-alert-circle-outline"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Perlu Verifikasi</p>
                            <h2 class="card-bank-value">{{ $pendingVerifications ?? 0 }}</h2>
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
                        <div class="card-bank-icon"><i class="mdi mdi-account-multiple"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Anggota Tim</p>
                            <h2 class="card-bank-value">{{ $myTeamMembers ?? 0 }}</h2>
                            <p class="card-bank-desc">Total anggota (Cabang)</p>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card card-bank gradient-green">
                    <div class="card-body">
                        <div class="card-bank-chip"></div>
                        <div class="card-bank-icon"><i class="mdi mdi-calendar-check"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Absen Hari Ini</p>
                            <h2 class="card-bank-value">{{ $attendancesToday ?? 0 }}</h2>
                            <p class="card-bank-desc">Tim hadir hari ini</p>
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
                        <p class="text-muted mb-4">Arahkan kamera ke QR Code user.</p>
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
                        <div class="card-bank-icon"><i class="mdi mdi-chart-bar"></i></div>
                        <div class="card-bank-content">
                            <p class="card-bank-label">Pindaian Hari Ini</p>
                            <h2 class="card-bank-value">{{ $myScansToday ?? 0 }}</h2>
                            <p class="card-bank-desc">Total scan QR hari ini</p>
                            <div class="mt-4 pt-3 border-top border-light">
                                <p class="card-bank-label mb-2">User Aktif</p>
                                <h3 class="card-bank-value mb-0">{{ $totalUsers ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- TAMPILAN KARTU ID & STATUS (KHUSUS USER, LEADER, DAN MEREKA YANG BISA ABSEN) --}}
    {{-- ======================================================================= --}}
    @if(in_array(auth()->user()->role, ['user_biasa', 'leader', 'admin', 'audit', 'security']))
        <div class="row">
            {{-- KARTU ID --}}
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
                                {{ strtoupper(Auth::user()->division->name ?? 'BELUM ADA DIVISI') }}
                            </h4>
                        </div>
                        <div class="card-id-footer">
                            <p class="card-id-valid">VALID THRU 12/28</p>
                            <p class="card-id-card-number">**** **** **** {{ substr(Auth::user()->phone ?? '1234', -4) }}</p>
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

                        {{-- 1. SUDAH ABSEN (HADIR) --}}
                        @if (isset($myAttendanceToday) && $myAttendanceToday)
                            @if ($myAttendanceToday->check_out_time || $myAttendanceToday->photo_out_path)
                                <div class="status-card status-success mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon"><i class="mdi mdi-home-variant"></i></div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1 fw-bold">Anda Sudah Pulang</h5>
                                            <p class="text-muted mb-0 small">Terima kasih atas kerja keras Anda!</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row text-center">
                                        <div class="col-6 border-end">
                                            <small class="text-muted d-block">MASUK</small>
                                            <h4 class="fw-bold text-success mb-0">{{ $myAttendanceToday->check_in_time->format('H:i') }}</h4>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">PULANG</small>
                                            <h4 class="fw-bold text-primary mb-0">
                                                {{ $myAttendanceToday->check_out_time ? $myAttendanceToday->check_out_time->format('H:i') : '-' }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="status-card status-success mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="status-icon"><i class="mdi mdi-clock-check"></i></div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1 fw-bold">Sedang Bekerja</h5>
                                            <p class="mb-0">Masuk: <strong>{{ $myAttendanceToday->check_in_time->format('H:i') }}</strong></p>
                                        </div>
                                    </div>
                                    @if ($myAttendanceToday->attendance_type == 'self')
                                        <div class="mt-3 pt-3 border-top text-center">
                                            <a href="{{ route('self.attend.create') }}" class="btn btn-danger btn-sm w-100">
                                                <i class="mdi mdi-logout me-1"></i>Absen Pulang
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        {{-- 2. BELUM ABSEN TAPI ADA IZIN (SAKIT/TELAT) --}}
                        @elseif(isset($myLeaveToday) && $myLeaveToday)
                            @php
                                $leaveColor = $myLeaveToday->status == 'approved' ? 'status-success' : 'status-warning';
                                $leaveIcon  = $myLeaveToday->type == 'sakit' ? 'mdi-hospital-box' : ($myLeaveToday->type == 'telat' ? 'mdi-clock-alert' : 'mdi-bag-suitcase');
                                $leaveTitle = ucfirst($myLeaveToday->type); 
                                
                                if($myLeaveToday->type == 'telat' && $myLeaveToday->start_time) {
                                    $timeInfo = "Datang jam: " . \Carbon\Carbon::parse($myLeaveToday->start_time)->format('H:i');
                                } else {
                                    $end = $myLeaveToday->end_date ? \Carbon\Carbon::parse($myLeaveToday->end_date)->format('d M') : '-';
                                    $timeInfo = "Sampai: " . $end;
                                }
                            @endphp

                            <div class="status-card {{ $leaveColor }} mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="status-icon"><i class="mdi {{ $leaveIcon }}"></i></div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="mb-1 fw-bold">Izin {{ $leaveTitle }}</h5>
                                            <span class="badge {{ $myLeaveToday->status == 'approved' ? 'bg-success' : 'bg-warning' }}">
                                                {{ strtoupper($myLeaveToday->status) }}
                                            </span>
                                        </div>
                                        <p class="text-muted mb-2 small">{{ $timeInfo }}</p>
                                        <div class="bg-white p-2 rounded border mb-2">
                                            <small class="text-muted d-block" style="font-size: 10px;">ALASAN:</small>
                                            <span class="fst-italic">"{{ $myLeaveToday->reason }}"</span>
                                        </div>
                                    </div>
                                </div>
                                {{-- Tombol Masuk (Jika Telat Disetujui) --}}
                                @if($myLeaveToday->type == 'telat' && $myLeaveToday->status == 'approved')
                                    <div class="mt-3 pt-3 border-top text-center">
                                        <p class="small text-muted mb-2">Sudah sampai?</p>
                                        <a href="{{ route('self.attend.create') }}" class="btn btn-dark btn-sm w-100">
                                            <i class="mdi mdi-fingerprint me-2"></i>Absen Masuk
                                        </a>
                                    </div>
                                @endif
                            </div>

                        {{-- 3. BELUM ABSEN & TIDAK IZIN --}}
                        @else
                            <div class="status-card status-info">
                                <div class="text-center py-4">
                                    <i class="mdi mdi-clock-alert display-4 mb-3 text-primary"></i>
                                    <h5 class="mb-2 fw-bold">Belum Absen Hari Ini</h5>
                                    <p class="text-muted mb-4">Silakan scan QR atau gunakan absen mandiri.</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('self.attend.create') }}" class="btn btn-dark">
                                            <i class="mdi mdi-fingerprint me-2"></i>Absen
                                        </a>
                                        {{-- Link Aman: Route ini sekarang bisa diakses semua berkat perbaikan web.php --}}
                                        <a href="{{ route('leave-requests.create') }}" class="btn btn-outline-dark">
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
    @endif

    {{-- ======================================================================= --}}
    {{-- STATISTIK GLOBAL (DITAMPILKAN UNTUK SEMUA) --}}
    {{-- ======================================================================= --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="mdi mdi-chart-pie me-2"></i>Statistik Absensi</h4>
                    <div>
                        <a href="{{ route('dashboard.export-pdf') }}" class="btn btn-danger btn-sm">
                            <i class="mdi mdi-file-pdf-box me-1"></i>PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height:300px;">
                                <canvas id="attendancePieChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            {{-- Tampilan Statistik Angka --}}
                            <div class="row">
                                {{-- Gunakan variabel $attendanceStats yang dikirim dari Controller --}}
                                @if(isset($attendanceStats))
                                    {{-- Kotak 1: Hadir/Verified/Scan Masuk --}}
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-success text-white p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    {{-- Label dinamis berdasarkan role --}}
                                                    <h6 class="mb-1">
                                                        @if(auth()->user()->role == 'audit') Terverifikasi 
                                                        @elseif(auth()->user()->role == 'security') Scan Masuk 
                                                        @else Hadir @endif
                                                    </h6>
                                                    <h3 class="mb-0">
                                                        {{ $attendanceStats['present'] ?? $attendanceStats['verified'] ?? $attendanceStats['check_in_scans'] ?? 0 }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kotak 2: Telat/Pending/Scan Pulang --}}
                                    <div class="col-6 mb-3">
                                        <div class="stat-card bg-warning text-dark p-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        @if(auth()->user()->role == 'audit') Menunggu
                                                        @elseif(auth()->user()->role == 'security') Scan Pulang
                                                        @else Terlambat @endif
                                                    </h6>
                                                    <h3 class="mb-0">
                                                        {{ $attendanceStats['late'] ?? $attendanceStats['pending'] ?? $attendanceStats['check_out_scans'] ?? 0 }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Tambahan kotak lain sesuai kebutuhan... --}}
                                    @if(auth()->user()->role != 'security')
                                        <div class="col-6 mb-3">
                                            <div class="stat-card bg-danger text-white p-3 rounded">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            @if(auth()->user()->role == 'audit') Terlambat
                                                            @else Tidak Hadir @endif
                                                        </h6>
                                                        <h3 class="mb-0">
                                                            {{ $attendanceStats['absent'] ?? $attendanceStats['late'] ?? 0 }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
    {{-- Style CSS Tetap Sama --}}
    <style>
        .card-bank { position: relative; min-height: 200px; border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); transition: all 0.3s ease; }
        .card-bank:hover { transform: translateY(-8px); box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18); }
        .card-bank .card-body { position: relative; z-index: 2; padding: 24px; color: white; display: flex; flex-direction: column; justify-content: space-between; }
        .card-bank-chip { width: 40px; height: 30px; background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); border-radius: 6px; position: relative; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); }
        .card-bank-icon { position: absolute; top: 20px; right: 20px; font-size: 48px; opacity: 0.2; }
        .card-bank-content { position: relative; z-index: 3; }
        .card-bank-value { font-family: 'Consolas', monospace; font-size: 36px; font-weight: 700; margin-bottom: 8px; line-height: 1; }
        .gradient-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .gradient-green { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .gradient-orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .gradient-red { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .gradient-dark { background: linear-gradient(135deg, #2c3e50 0%, #000000 100%); }
        .card-id { position: relative; border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3); color: white; min-height: 220px; display: flex; flex-direction: column; }
        .card-id .card-body { padding: 24px; display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1; }
        .card-status { border-radius: 16px; border: none; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); height: 100%; }
        .status-card { padding: 24px; border-radius: 12px; border: 2px solid; background: #f8fafc; }
        .status-success { border-color: #10b981; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
        .status-warning { border-color: #f59e0b; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); }
        .status-info { border-color: #3b82f6; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); }
        .status-icon { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; font-size: 28px; flex-shrink: 0; }
        .status-success .status-icon { background: #10b981; color: white; }
        .status-warning .status-icon { background: #f59e0b; color: white; }
        .status-info .status-icon { background: #3b82f6; color: white; }
        .stat-card { transition: all 0.3s ease; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendancePieChart').getContext('2d');
    
    // Mengambil data dari PHP controller
    const stats = @json($attendanceStats ?? []);
    
    let labels = [], data = [], bgColors = [];

    // Logika Grafik Dinamis
    @if(auth()->user()->role == 'security')
        labels = ['Scan Masuk', 'Scan Pulang'];
        data = [stats.check_in_scans || 0, stats.check_out_scans || 0];
        bgColors = ['#28a745', '#17a2b8'];
    @elseif(auth()->user()->role == 'audit')
        labels = ['Terverifikasi', 'Menunggu', 'Terlambat'];
        data = [stats.verified || 0, stats.pending || 0, stats.late || 0];
        bgColors = ['#28a745', '#ffc107', '#dc3545'];
    @else
        labels = ['Hadir', 'Terlambat', 'Pending', 'Tidak Hadir'];
        data = [stats.present || 0, stats.late || 0, stats.pending || 0, stats.absent || 0];
        bgColors = ['#28a745', '#ffc107', '#17a2b8', '#dc3545'];
    @endif

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: bgColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
            }
        }
    });
});
</script>
@endpush