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
                        <div class_l_s="card-bank-content">
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
                        <div class_l_s="mb-4">
                            <i class="mdi mdi-qrcode-scan display-1 text-dark"></i>
                        </div>
                        <h4 class="card-title mb-3">Pindai QR User</h4>
                        <p class="text-muted mb-4">Arahkan kamera ke QR Code user untuk melakukan absensi.</p>
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
    @elseif (auth()->user()->role == 'user_biasa' || auth()->user()->role == 'leader')
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
                            <h4 class="card-id-division">{{ strtoupper(Auth::user()->division->name ?? 'BELUM ADA DIVISI') }}</h4>
                        </div>
                        <div class="card-id-footer">
                            <p class="card-id-valid">VALID THRU 12/28</p>
                            <p class="card-id-card-number">**** **** **** 1234</p>
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

                        @if ($myAttendanceToday)
                            <div class="status-card status-success">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon">
                                        <i class="mdi mdi-check-circle"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-2">Anda Sudah Absen</h5>
                                        <p class="mb-1">
                                            <i class="mdi mdi-clock-outline me-1"></i>
                                            {{ $myAttendanceToday->check_in_time->format('d M Y, H:i') }}
                                        </p>
                                        <p class="mb-0">
                                            @if ($myAttendanceToday->status == 'verified')
                                                <span class="badge bg-success">
                                                    <i class="mdi mdi-check-decagram me-1"></i>Terverifikasi
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="mdi mdi-timer-sand me-1"></i>Menunggu Verifikasi
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif ($activeLateStatus)
                            <div class="status-card status-warning">
                                <div class="d-flex align-items-center">
                                    <div class="status-icon">
                                        <i class="mdi mdi-clock-alert"></i>
                                    </div>
                                    <div class_l_s="flex-grow-1">
                                        <h5 class="mb-2">Laporan Telat Aktif</h5>
                                        <p class="mb-2 fst-italic">"{{ $activeLateStatus->message }}"</p>
                                        <small class="text-muted">
                                            <i class="mdi mdi-calendar me-1"></i>
                                            {{ $activeLateStatus->created_at->format('d M Y, H:i') }}
                                        </small>
                                    </div>
                                </div>
                                <form action="{{ route('late.status.delete') }}" method="POST" class="mt-3">
                                    @csrf
                                    <button type="submit" class="btn btn-dark btn-sm">
                                        <i class="mdi mdi-delete me-1"></i>Hapus Laporan & Absen Sekarang
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="status-card status-info">
                                <div class="text-center py-4">
                                    <i class="mdi mdi-information-outline display-4 mb-3"></i>
                                    <h5 class="mb-3">Anda Belum Absen Hari Ini</h5>
                                    <a href="{{ route('self.attend.create') }}" class="btn btn-dark btn-lg">
                                        <i class="mdi mdi-calendar-check me-2"></i>Lakukan Absen Mandiri
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU STATISTIK (DIPINDAH KE BARIS BARU) --}}
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
                            <p class="card-bank-desc">Menunggu persetujuan audit</p>
                            <div class="mt-4 pt-3 border-top border-light">
                                <p class="card-bank-label mb-2">Teman Satu Divisi</p>
                                <h3 class="card-bank-value mb-0">{{ $myTeamCount }}</h3>
                            </div>
                        </div>
                        <div class="card-bank-pattern"></div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================= --}}
            {{-- KARTU AKSI BARU UNTUK PENGAJUAN IZIN --}}
            {{-- ======================================================================= --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card card-action">
                    <div class="card-body text-center py-5">
                        <i class="mdi mdi-calendar-plus display-3 text-dark mb-4"></i>
                        <h4 class="card-title mb-3">Manajemen Absensi</h4>
                        <p class="text-muted mb-4">Ajukan izin, sakit, cuti, atau libur mingguan melalui form terpusat.</p>
                        <a href="{{ route('leave.create') }}" class="btn btn-dark btn-lg">
                            <i class="mdi mdi-file-document-box-plus-outline me-2"></i>Buat Pengajuan Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- FORM "LAPOR KENDALA" YANG LAMA DIHAPUS --}}
        {{-- ======================================================================= --}}
        {{-- @if (!$myAttendanceToday && !$activeLateStatus)
            ... (Bagian ini dihapus) ...
        @endif --}}
    @endif

@endsection

@push('styles')
    <style>
        /* Card Bank Style - Mirip Kartu ATM/Debit */
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
            justify-content: space-between; /* Untuk space antara header dan content */
        }

        /* Chip Kartu */
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

        /* Icon di Card */
        .card-bank-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 48px;
            opacity: 0.2;
        }

        /* Content Card */
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
            font-family: 'Consolas', 'Courier New', monospace; /* Font mirip angka kartu */
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

        /* Pattern Background */
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

        /* ========== STYLE KARTU ID BARU (MIRIP ATM) ========== */
        .card-id {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3); /* Bayangan lebih gelap */
            color: white;
            min-height: 220px; /* Sedikit lebih tinggi */
            display: flex;
            flex-direction: column;
            font-family: 'Roboto', sans-serif; /* Font umum untuk kartu */
        }

        .card-id .card-body {
            position: relative;
            z-index: 2;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Distribusi elemen dari atas ke bawah */
            flex-grow: 1;
            gap: 15px; /* Jarak antar bagian utama */
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
            align-items: flex-end; /* Logo di kanan atas */
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        .card-id-logo i {
            font-size: 38px; /* Ukuran ikon kartu */
            margin-bottom: 4px;
            color: #ffed4e; /* Warna ikon mirip chip */
        }

        .card-id-details {
            flex-grow: 1; /* Memberi ruang untuk nama/divisi */
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
            font-family: 'Consolas', 'Courier New', monospace; /* Font mirip kartu */
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

        /* Responsive adjustments for card-id */
        @media (max-width: 768px) {
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
        /* ========== END STYLE KARTU ID BARU (MIRIP ATM) ========== */


        /* Card Action - Card Biasa dengan Style Modern */
        .card-action {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%; /* Menyamakan tinggi */
        }

        .card-action:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }

        /* Card Status - Untuk Status Absensi */
        .card-status {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            height: 100%;
            /* Pastikan tingginya sama */
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

        /* Card Report */
        .card-report {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        /* Buttons */
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

        /* Form Control */
        .form-control-lg {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 14px 18px;
            transition: all 0.3s ease;
        }

        .form-control-lg:focus {
            border-color: #000;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
        }

        /* Alert */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 16px 20px;
        }

        /* Badge */
        .badge {
            border-radius: 8px;
            font-weight: 600;
            padding: 6px 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-bank-value {
                font-size: 28px;
            }

            .card-bank {
                min-height: 180px;
            }
        }
    </style>
@endpush