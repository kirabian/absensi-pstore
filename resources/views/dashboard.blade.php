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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0 fw-semibold">Total User</p>
                            <i class="mdi mdi-account-multiple icon-md text-dark"></i>
                        </div>
                        <h3 class="fw-bold text-dark">{{ $totalUsers }}</h3>
                        <p class="text-muted small mb-0">User terdaftar di sistem</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0 fw-semibold">Total Divisi</p>
                            <i class="mdi mdi-sitemap icon-md text-dark"></i>
                        </div>
                        <h3 class="fw-bold text-dark">{{ $totalDivisions }}</h3>
                        <p class="text-muted small mb-0">Divisi aktif</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0 fw-semibold">Absensi Hari Ini</p>
                            <i class="mdi mdi-calendar-check icon-md text-dark"></i>
                        </div>
                        <h3 class="fw-bold text-dark">{{ $attendancesToday }}</h3>
                        <p class="text-muted small mb-0">Total absensi hari ini</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0 fw-semibold">Perlu Verifikasi</p>
                            <i class="mdi mdi-alert-circle-outline icon-md text-dark"></i>
                        </div>
                        <h3 class="fw-bold text-dark">{{ $pendingVerifications }}</h3>
                        <p class="text-muted small mb-0">Menunggu persetujuan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center py-4">
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
                <div class="card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0 fw-semibold">Perlu Verifikasi</p>
                            <i class="mdi mdi-alert-circle-outline icon-md text-dark"></i>
                        </div>
                        <h2 class="fw-bold text-dark">{{ $pendingVerifications }}</h2>
                        <p class="text-muted small mb-3">Absensi menunggu persetujuan</p>
                        <a href="{{ route('audit.verify.list') }}" class="btn btn-dark btn-sm">
                            <i class="mdi mdi-clipboard-check me-1"></i>Lihat Daftar
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0 fw-semibold">Anggota Tim</p>
                            <i class="mdi mdi-account-multiple icon-md text-dark"></i>
                        </div>
                        <h2 class="fw-bold text-dark">{{ $myTeamMembers }}</h2>
                        <p class="text-muted small">Total anggota dalam tim</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="card-title mb-0 fw-semibold">Absen Hari Ini</p>
                            <i class="mdi mdi-calendar-check icon-md text-dark"></i>
                        </div>
                        <h2 class="fw-bold text-dark">{{ $attendancesToday }}</h2>
                        <p class="text-muted small">Tim sudah absen hari ini</p>
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
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
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
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="mdi mdi-chart-bar display-4 text-muted"></i>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="card-title mb-0 fw-semibold">Pindaian Hari Ini</p>
                                <h4 class="fw-bold text-dark">{{ $myScansToday }}</h4>
                            </div>
                            <p class="text-muted small">Total pindaian QR hari ini</p>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="card-title mb-0 fw-semibold">User Aktif</p>
                                <h4 class="fw-bold text-dark">{{ $totalUsers }}</h4>
                            </div>
                            <p class="text-muted small">Total user terdaftar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- TAMPILAN UNTUK USER BIASA --}}
        {{-- ======================================================================= --}}
    @elseif (auth()->user()->role == 'user_biasa')
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Status Absensi Hari Ini</h4>

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
                            <div class="alert alert-success border-0 bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-check-circle-outline display-6 me-3 text-success"></i>
                                    <div>
                                        <h5 class="mb-1 text-dark">Anda Sudah Absen</h5>
                                        <p class="mb-1 text-muted">Pada:
                                            {{ $myAttendanceToday->check_in_time->format('d M Y, H:i') }}</p>
                                        <p class="mb-0 text-muted">
                                            Status:
                                            @if($myAttendanceToday->status == 'verified')
                                                <span class="badge bg-dark">Terverifikasi</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif ($activeLateStatus)
                            <div class="alert alert-warning border-0 bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-clock-alert display-6 me-3 text-warning"></i>
                                    <div>
                                        <h5 class="mb-2 text-dark">Laporan Telat Aktif</h5>
                                        <p class="mb-2 fst-italic text-dark">"{{ $activeLateStatus->message }}"</p>
                                        <small class="text-muted">Dibuat:
                                            {{ $activeLateStatus->created_at->format('d M Y, H:i') }}</small>
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
                            <div class="alert alert-info border-0 bg-light text-center py-4">
                                <i class="mdi mdi-information-outline display-6 text-dark mb-3"></i>
                                <h5 class="mb-3 text-dark">Anda Belum Absen Hari Ini</h5>
                                <a href="{{ route('self.attend.create') }}" class="btn btn-dark btn-lg">
                                    <i class="mdi mdi-calendar-check me-2"></i>Lakukan Absen Mandiri
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="mdi mdi-chart-pie display-4 text-muted"></i>
                        </div>
                        <div class="mb-4">
                            <h2 class="fw-bold text-dark">{{ $myPendingCount }}</h2>
                            <p class="text-muted">Absensi Perlu Verifikasi</p>
                            <small class="text-muted">Menunggu persetujuan audit</small>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <h2 class="fw-bold text-dark">{{ $myTeamCount }}</h2>
                            <p class="text-muted">Teman Satu Divisi</p>
                            <small class="text-muted">Total anggota divisi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fitur Lapor Macet --}}
        @if (!$myAttendanceToday && !$activeLateStatus)
            <div class="row">
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>Lapor Kendala
                            </h4>
                            <p class="text-muted mb-4">Berikan laporan jika mengalami kendala seperti macet, dll. Anda tidak bisa
                                absen selama laporan aktif.</p>
                            <form action="{{ route('late.status.store') }}" method="POST">
                                @csrf
                                <div class="form-group mb-4">
                                    <textarea class="form-control" name="message" rows="4"
                                        placeholder="Contoh: Macet parah di Tol Cikampek, mungkin telat 30 menit. Atau: Kendaraan mogok di jalan, sedang menunggu bantuan."
                                        required style="border-radius: 8px; border: 2px solid #e2e8f0;"></textarea>
                                </div>
                                <button type="submit" class="btn btn-dark btn-lg">
                                    <i class="mdi mdi-send me-2"></i>Kirim Laporan Telat
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

@endsection

@push('styles')
    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .btn-dark {
            background: #000;
            border: 2px solid #000;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-dark:hover {
            background: #333;
            border-color: #333;
            transform: translateY(-1px);
        }

        .btn-outline-dark {
            border: 2px solid #000;
            color: #000;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-dark:hover {
            background: #000;
            color: white;
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .text-muted {
            color: #64748b !important;
        }

        .icon-md {
            color: #000 !important;
        }

        .badge {
            border-radius: 6px;
            font-weight: 500;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush
