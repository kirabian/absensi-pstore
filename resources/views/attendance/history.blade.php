@extends('layout.master')

@section('title')
    @if(isset($employee))
        Riwayat Absensi - {{ $employee->name }}
    @else
        Riwayat Absensi Saya
    @endif
@endsection

@section('heading')
    @if(isset($employee))
        <div class="d-flex align-items-center">
            <a href="{{ route('team.branch.detail', $employee->branch_id) }}" class="text-decoration-none text-muted me-3">
                <i class="mdi mdi-arrow-left"></i>
            </a>
            <div>
                Riwayat Absensi: {{ $employee->name }}
                <br>
                <small class="text-muted">
                    {{ $employee->division->name ?? '-' }} - {{ $employee->branch->name ?? '-' }}
                </small>
            </div>
        </div>
    @else
        Riwayat Absensi Saya
    @endif
@endsection

@push('styles')
<style>
    .verification-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.7rem;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .verified-check {
        color: #28a745;
        font-size: 1.1rem;
    }
    
    .pending-clock {
        color: #ffc107;
        font-size: 1.1rem;
    }
    
    .audit-mode-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        {{-- HEADER MODE AUDIT --}}
        @if(isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-shield-account display-6 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Mode Cross-Check Audit</h5>
                    <p class="mb-0">Anda dapat memverifikasi dan mengubah status kehadiran karyawan. Setelah verifikasi lengkap, status akan berubah menjadi <span class="badge bg-success">Terverifikasi</span>.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- FILTER BULAN & TAHUN --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <form action="{{ isset($employee) ? route('team.branch.employee.history', ['branchId' => $employee->branch_id, 'employeeId' => $employee->id]) : route('attendance.history') }}" method="GET" class="row align-items-center gx-2">
                    <div class="col-auto">
                        <label class="fw-bold mb-0 me-2"><i class="mdi mdi-filter-outline"></i> Filter Periode:</label>
                    </div>
                    <div class="col-auto">
                        <select name="month" class="form-select form-select-sm">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="year" class="form-select form-select-sm">
                            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm text-white">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RINGKASAN BULANAN --}}
        <div class="row mb-3">
            <div class="col-md-2 mb-2">
                <div class="card bg-primary text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Total</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['total'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <div class="card bg-success text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Terverifikasi</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['hadir'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <div class="card bg-warning text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Total Telat</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['telat'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <div class="card bg-danger text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Pulang Cepat</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['pulang_cepat'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <div class="card bg-info text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Menunggu</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['pending'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-2">
                <div class="card bg-secondary text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Tidak Hadir</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['total'] - $summary['hadir'] - $summary['pending'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">
                        Detail Absensi - {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                        @if(isset($employee))
                            <br><small class="text-muted">Karyawan: {{ $employee->name }}</small>
                        @endif
                    </h4>
                    
                    @if(isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                        <span class="badge audit-mode-badge fs-6 py-2">
                            <i class="mdi mdi-shield-check me-1"></i> Mode Cross-Check Audit
                        </span>
                    @endif
                </div>
                
                @if($history->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Foto Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Foto Pulang</th>
                                    <th>Status</th>
                                    <th>Verifikasi</th>
                                    <th>Metode</th>
                                    @if(isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                        <th width="120">Aksi Audit</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $att)
                                    <tr>
                                        {{-- TANGGAL --}}
                                        <td>
                                            <div class="fw-bold">{{ $att->check_in_time->format('d M Y') }}</div>
                                            <small class="text-muted">{{ $att->check_in_time->format('l') }}</small>
                                        </td>

                                        {{-- JAM MASUK --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-login text-success me-2"></i>
                                                <span class="{{ $att->is_late_checkin ? 'text-danger fw-bold' : '' }}">
                                                    {{ $att->check_in_time->format('H:i') }}
                                                </span>
                                                @if($att->is_late_checkin)
                                                    <span class="badge bg-danger ms-1" style="font-size: 9px;">Telat</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- FOTO MASUK --}}
                                        <td>
                                            @if($att->photo_path)
                                                <a href="{{ asset('storage/' . $att->photo_path) }}" target="_blank" class="d-inline-block">
                                                    <img src="{{ asset('storage/' . $att->photo_path) }}" 
                                                         alt="Masuk" 
                                                         class="rounded shadow-sm" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;">
                                                    <small class="d-block text-center text-muted mt-1">Bukti</small>
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>

                                        {{-- JAM PULANG --}}
                                        <td>
                                            @if($att->check_out_time)
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-logout text-primary me-2"></i>
                                                    <span class="{{ $att->is_early_checkout ? 'text-warning fw-bold' : '' }}">
                                                        {{ $att->check_out_time->format('H:i') }}
                                                    </span>
                                                    @if($att->is_early_checkout)
                                                        <span class="badge bg-warning ms-1" style="font-size: 9px;">Cepat</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">Belum Pulang</span>
                                            @endif
                                        </td>

                                        {{-- FOTO PULANG --}}
                                        <td>
                                            @if($att->photo_out_path)
                                                <a href="{{ asset('storage/' . $att->photo_out_path) }}" target="_blank" class="d-inline-block">
                                                    <img src="{{ asset('storage/' . $att->photo_out_path) }}" 
                                                         alt="Pulang" 
                                                         class="rounded shadow-sm" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;">
                                                    <small class="d-block text-center text-muted mt-1">Bukti</small>
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>

                                        {{-- STATUS KEHADIRAN --}}
                                        <td>
                                            @if($att->presence_status)
                                                @php
                                                    $badgeColor = match($att->presence_status) {
                                                        'Masuk' => 'bg-success',
                                                        'WFH / Dinas Luar' => 'bg-info',
                                                        'Izin Telat' => 'bg-warning text-dark',
                                                        'Sakit' => 'bg-primary',
                                                        'Cuti' => 'bg-secondary',
                                                        'Alpha' => 'bg-danger',
                                                        'Telat' => 'bg-danger',
                                                        default => 'bg-dark'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeColor }}">
                                                    {{ $att->presence_status }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>

                                        {{-- STATUS VERIFIKASI --}}
                                        <td>
                                            @if($att->status == 'verified')
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-check-circle verified-check me-1"></i>
                                                    <span class="badge bg-success verification-badge">
                                                        Terverifikasi
                                                    </span>
                                                </div>
                                                @if($att->verifiedBy)
                                                    <small class="text-muted d-block">
                                                        oleh: {{ $att->verifiedBy->name }}
                                                    </small>
                                                @endif
                                            @elseif($att->status == 'pending_verification')
                                                <div class="d-flex align-items-center">
                                                    <i class="mdi mdi-clock-outline pending-clock me-1"></i>
                                                    <span class="badge bg-warning text-dark verification-badge">
                                                        Menunggu
                                                    </span>
                                                </div>
                                            @else
                                                <span class="badge bg-secondary verification-badge">
                                                    Belum Diverifikasi
                                                </span>
                                            @endif
                                        </td>

                                        {{-- METODE --}}
                                        <td>
                                            @if($att->attendance_type == 'scan')
                                                <span class="badge badge-outline-primary">
                                                    <i class="mdi mdi-qrcode-scan me-1"></i> Scan
                                                </span>
                                            @elseif($att->attendance_type == 'self')
                                                <span class="badge badge-outline-info">
                                                    <i class="mdi mdi-camera-front-variant me-1"></i> Selfie
                                                </span>
                                            @else
                                                <span class="badge badge-outline-secondary">System</span>
                                            @endif
                                        </td>

                                        {{-- AKSI AUDIT --}}
                                        @if(isset($employee) && (auth()->user()->role == 'audit' || auth()->user()->role == 'admin'))
                                            <td class="action-buttons">
                                                @if($att->status != 'verified')
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" 
                                                                class="btn btn-success" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#verifyModal{{ $att->id }}"
                                                                title="Verifikasi Absensi">
                                                            <i class="mdi mdi-check"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#rejectModal{{ $att->id }}"
                                                                title="Tolak Absensi">
                                                            <i class="mdi mdi-close"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-success">
                                                        <i class="mdi mdi-check-all me-1"></i>
                                                        Sudah Diverifikasi
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Modal Verifikasi -->
                                            <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <i class="mdi mdi-check-circle text-success me-2"></i>
                                                                Verifikasi Absensi
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="alert alert-info">
                                                                    <strong>Informasi Absensi:</strong><br>
                                                                    Karyawan: <strong>{{ $employee->name }}</strong><br>
                                                                    Tanggal: <strong>{{ $att->check_in_time->format('d M Y') }}</strong><br>
                                                                    Jam Masuk: <strong>{{ $att->check_in_time->format('H:i') }}</strong>
                                                                    @if($att->check_out_time)
                                                                        <br>Jam Pulang: <strong>{{ $att->check_out_time->format('H:i') }}</strong>
                                                                    @endif
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Status Kehadiran <span class="text-danger">*</span></label>
                                                                            <select name="presence_status" class="form-select" required>
                                                                                <option value="">Pilih Status Kehadiran</option>
                                                                                <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk</option>
                                                                                <option value="WFH / Dinas Luar" {{ $att->presence_status == 'WFH / Dinas Luar' ? 'selected' : '' }}>WFH / Dinas Luar</option>
                                                                                <option value="Izin Telat" {{ $att->presence_status == 'Izin Telat' ? 'selected' : '' }}>Izin Telat</option>
                                                                                <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                                                                <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                                                                <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                                                                            </select>
                                                                            <small class="text-muted">Pilih status kehadiran yang sesuai</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Upload Bukti Audit</label>
                                                                            <input type="file" name="audit_photo" class="form-control" accept="image/*">
                                                                            <small class="text-muted">Opsional: Upload foto bukti verifikasi</small>
                                                                            @if($att->audit_photo_path)
                                                                                <div class="mt-1">
                                                                                    <small class="text-success">
                                                                                        <i class="mdi mdi-check me-1"></i>
                                                                                        Bukti sudah ada: 
                                                                                        <a href="{{ asset('storage/' . $att->audit_photo_path) }}" target="_blank">Lihat</a>
                                                                                    </small>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Catatan Audit</label>
                                                                    <textarea name="audit_note" class="form-control" rows="3" placeholder="Tambahkan catatan verifikasi jika diperlukan...">{{ $att->audit_note ?? '' }}</textarea>
                                                                    <small class="text-muted">Opsional: Catatan untuk dokumentasi</small>
                                                                </div>

                                                                <div class="alert alert-warning">
                                                                    <small>
                                                                        <i class="mdi mdi-information-outline me-1"></i>
                                                                        Setelah diverifikasi, status akan berubah menjadi <strong>Terverifikasi</strong> dan tidak dapat diubah kembali.
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-success">
                                                                    <i class="mdi mdi-check-circle me-1"></i> Verifikasi Absensi
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Tolak -->
                                            <div class="modal fade" id="rejectModal{{ $att->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-danger">
                                                                <i class="mdi mdi-close-circle text-danger me-2"></i>
                                                                Tolak Absensi
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('audit.reject', $att->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="modal-body">
                                                                <div class="alert alert-danger">
                                                                    <strong>Peringatan!</strong> Absensi yang ditolak akan dihapus dari sistem.
                                                                </div>
                                                                
                                                                <p>Apakah Anda yakin ingin menolak absensi berikut?</p>
                                                                <ul>
                                                                    <li><strong>Karyawan:</strong> {{ $employee->name }}</li>
                                                                    <li><strong>Tanggal:</strong> {{ $att->check_in_time->format('d M Y') }}</li>
                                                                    <li><strong>Jam Masuk:</strong> {{ $att->check_in_time->format('H:i') }}</li>
                                                                </ul>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Berikan alasan penolakan..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">
                                                                    <i class="mdi mdi-close-circle me-1"></i> Tolak Absensi
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="mdi mdi-calendar-remove display-4 text-muted"></i>
                        <h5 class="mt-3 text-muted">Tidak ada data absensi</h5>
                        <p class="text-muted">Tidak ada riwayat pada periode {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit filter ketika bulan/tahun berubah
    document.addEventListener('DOMContentLoaded', function() {
        const monthSelect = document.querySelector('select[name="month"]');
        const yearSelect = document.querySelector('select[name="year"]');
        
        if (monthSelect && yearSelect) {
            monthSelect.addEventListener('change', function() {
                this.form.submit();
            });
            
            yearSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
</script>
@endpush