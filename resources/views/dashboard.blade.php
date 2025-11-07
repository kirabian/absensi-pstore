@extends('layout.master')

@section('title')
    Dashboard
@endsection

@section('heading')
    {{-- Heading ini sekarang dinamis mengambil nama user yang login --}}
    Selamat Datang, {{ Auth::user()->name }}!
@endsection

@section('content')

    {{-- ======================================================================= --}}
    {{-- TAMPILAN UNTUK ADMIN --}}
    {{-- ======================================================================= --}}

    {{-- Komentar @if di sini sudah dibuka --}}
    @if (auth()->user()->role == 'admin')
        <div class="row">
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title text-md-center text-xl-left">Total User</p>
                        <div
                            class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                            {{-- Tanda ?? 0 dihapus, karena controller pasti mengirim data ini --}}
                            <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">{{ $totalUsers }}</h3>
                            <i class="mdi mdi-account-multiple icon-md text-primary ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title text-md-center text-xl-left">Total Divisi</p>
                        <div
                            class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                            <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">{{ $totalDivisions }}</h3>
                            <i class="mdi mdi-sitemap icon-md text-info ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title text-md-center text-xl-left">Absensi Hari Ini</p>
                        <div
                            class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                            <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">{{ $attendancesToday }}</h3>
                            <i class="mdi mdi-calendar-check icon-md text-success ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title text-md-center text-xl-left">Perlu Verifikasi</p>
                        <div
                            class="d-flex flex-wrap justify-content-between justify-content-md-center justify-content-xl-between align-items-center">
                            <h3 class="mb-0 mb-md-2 mb-xl-0 order-md-1 order-xl-0">{{ $pendingVerifications }}</h3>
                            <i class="mdi mdi-alert-circle-outline icon-md text-warning ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Teks dummy dihapus, link route sudah benar --}}
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Ringkasan Sistem</h4>
                        <p>Sebagai Admin, Anda memiliki kontrol penuh atas semua modul.</p>
                        <a href="{{ route('divisions.index') }}" class="btn btn-primary">Kelola Divisi</a>
                        <a href="{{ route('users.index') }}" class="btn btn-info">Kelola User</a>
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
                    <div class="card-body">
                        <p class="card-title">Perlu Verifikasi (Tim Anda)</p>
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="mb-0 mb-md-2">{{ $pendingVerifications }}</h3>
                            <i class="mdi mdi-alert-circle-outline icon-md text-warning ms-auto"></i>
                        </div>
                        <p>Absensi mandiri yang butuh persetujuan Anda.</p>
                        <a href="#" class="btn btn-warning btn-sm mt-2">Lihat Daftar Verifikasi</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title">Anggota Tim Anda</p>
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="mb-0 mb-md-2">{{ $myTeamMembers }}</h3>
                            <i class="mdi mdi-account-multiple icon-md text-primary ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title">Tim Anda Absen Hari Ini</p>
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="mb-0 mb-md-2">{{ $attendancesToday }}</h3>
                            <i class="mdi mdi-calendar-check icon-md text-success ms-auto"></i>
                        </div>
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
                    <div class="card-body text-center">
                        <h4 class="card-title">Pindai QR User</h4>
                        <p>Arahkan kamera ke QR Code user untuk melakukan absensi.</p>
                        <i class="mdi mdi-qrcode-scan display-1 text-primary"></i>
                        <br>
                        {{-- Link ini diarahkan ke route 'security.scan' --}}
                        <a href="{{ route('security.scan') }}" class="btn btn-primary btn-lg mt-3">Mulai Memindai</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title">Total Pindaian Anda Hari Ini</p>
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="mb-0 mb-md-2">{{ $myScansToday }}</h3>
                            <i class="mdi mdi-camera-enhance icon-md text-success ms-auto"></i>
                        </div>
                        <hr>
                        <p class="card-title">Total User Aktif</p>
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="mb-0 mb-md-2">{{ $totalUsers }}</h3>
                            <i class="mdi mdi-account-multiple icon-md text-info ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================================= --}}
        {{-- TAMPILAN UNTUK USER BIASA (SUDAH DIPERBARUI) --}}
        {{-- ======================================================================= --}}
    @elseif (auth()->user()->role == 'user_biasa')
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Status Absensi Hari Ini</h4>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if ($myAttendanceToday)
                            <div class="alert alert-success">
                                <h5 class="mb-0">Anda Sudah Absen</h5>
                                <p class="mb-0">Pada: {{ $myAttendanceToday->check_in_time->format('d M Y, H:i') }}</p>
                                <p class="mb-0">Status:
                                    @if($myAttendanceToday->status == 'verified')
                                        <span class="badge badge-success">Terverifikasi</span>
                                    @else
                                        <span class="badge badge-warning">Menunggu Verifikasi</span>
                                    @endif
                                </p>
                            </div>
                        @elseif ($activeLateStatus)
                            <div class="alert alert-warning">
                                <h5>Anda Punya Laporan Telat Aktif:</h5>
                                <p class="mb-1">"{{ $activeLateStatus->message }}"</p>
                                <small>Dibuat: {{ $activeLateStatus->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            <form action="{{ route('late.status.delete') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger">Hapus Laporan & Absen Sekarang</button>
                            </form>
                        @else
                            <div class="alert alert-info">
                                <h5 class="mb-0">Anda Belum Absen Hari Ini.</h5>
                            </div>
                            <a href="{{ route('self.attend.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-calendar-check"></i> Lakukan Absen Mandiri
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title">Absensi Perlu Verifikasi</p>
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="mb-0 mb-md-2">{{ $myPendingCount }}</h3>
                            <i class="mdi mdi-alert-circle-outline icon-md text-warning ms-auto"></i>
                        </div>
                        <p>Total absensi mandiri Anda yang belum diverifikasi oleh Audit.</p>
                        <hr>
                        <p class="card-title">Teman Satu Divisi</p>
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <h3 class="mb-0 mb-md-2">{{ $myTeamCount }}</h3>
                            <i class="mdi mdi-account-multiple icon-md text-info ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fitur Lapor Macet (HANYA TAMPIL JIKA BELUM ABSEN & BELUM LAPOR) --}}
        @if (!$myAttendanceToday && !$activeLateStatus)
            <div class="row">
                <div class="col-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Lapor Kendala (Macet, dll)</h4>
                            <p class="card-description">Tidak bisa absen jika Anda punya laporan telat aktif.</p>
                            <form action="{{ route('late.status.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <textarea class="form-control" name="message" rows="3"
                                        placeholder="Contoh: Macet parah di Tol Cikampek, mungkin telat 30 menit."
                                        required></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning">Kirim Laporan Telat</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

@endsection
