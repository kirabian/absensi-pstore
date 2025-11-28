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
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
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
                        <div class="text-muted small">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Mode Cross-Check Audit Aktif
                        </div>
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
                                        <th>Aksi Audit</th>
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
                                                <span class="badge bg-info text-dark">
                                                    {{ ucfirst($att->presence_status) }}
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
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Verifikasi Absensi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <p>Verifikasi absensi <strong>{{ $employee->name }}</strong> pada <strong>{{ $att->check_in_time->format('d M Y') }}</strong>?</p>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Kehadiran</label>
                                                                    <select name="presence_status" class="form-select" required>
                                                                        <option value="hadir" {{ $att->presence_status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                                        <option value="sakit" {{ $att->presence_status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                                        <option value="cuti" {{ $att->presence_status == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                                                        <option value="izin" {{ $att->presence_status == 'izin' ? 'selected' : '' }}>Izin</option>
                                                                        <option value="dinas_luar" {{ $att->presence_status == 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Catatan Audit (Opsional)</label>
                                                                    <textarea name="audit_note" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan...">{{ $att->audit_note }}</textarea>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Upload Bukti Audit (Opsional)</label>
                                                                    <input type="file" name="audit_photo" class="form-control" accept="image/*">
                                                                    @if($att->audit_photo_path)
                                                                        <small class="text-muted">Bukti saat ini: 
                                                                            <a href="{{ asset('storage/' . $att->audit_photo_path) }}" target="_blank">Lihat</a>
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-success">Verifikasi</button>
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
                                                            <h5 class="modal-title">Tolak Absensi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('audit.reject', $att->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="modal-body">
                                                                <p>Apakah Anda yakin ingin menolak absensi <strong>{{ $employee->name }}</strong> pada <strong>{{ $att->check_in_time->format('d M Y') }}</strong>?</p>
                                                                <p class="text-danger"><small>Absensi yang ditolak akan dihapus dari sistem.</small></p>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Alasan Penolakan</label>
                                                                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Berikan alasan penolakan..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Tolak Absensi</button>
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
    // Auto-update summary ketika filter berubah
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