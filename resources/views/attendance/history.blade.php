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
            <div class="col-md-3 mb-2">
                <div class="card bg-success text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Total Hadir</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['hadir'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card bg-warning text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Total Telat</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['telat'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card bg-danger text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Pulang Cepat</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['pulang_cepat'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card bg-info text-white border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <h6 class="text-white mb-1 fw-bold">Menunggu Verif</h6>
                        <h2 class="fw-bold text-white mb-0">{{ $summary['pending'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">
                    Detail Absensi - {{ date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) }}
                    @if(isset($employee))
                        <br><small class="text-muted">Karyawan: {{ $employee->name }}</small>
                    @endif
                </h4>
                
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
                                    <th>Metode</th>
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
                                                    <span class="badge bg-danger ms-2" style="font-size: 10px;">Telat</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- FOTO MASUK --}}
                                        <td>
                                            @if($att->photo_path)
                                                <a href="{{ asset('storage/' . $att->photo_path) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $att->photo_path) }}" 
                                                         alt="Masuk" 
                                                         class="rounded shadow-sm" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;">
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
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">Belum Pulang</span>
                                            @endif
                                        </td>

                                        {{-- FOTO PULANG --}}
                                        <td>
                                            @if($att->photo_out_path)
                                                <a href="{{ asset('storage/' . $att->photo_out_path) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $att->photo_out_path) }}" 
                                                         alt="Pulang" 
                                                         class="rounded shadow-sm" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e2e8f0;">
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>

                                        {{-- STATUS --}}
                                        <td>
                                            @if($att->status == 'verified' || $att->status == 'present' || $att->status == 'late')
                                                <span class="badge bg-success">Verified</span>
                                            @elseif($att->status == 'pending_verification')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @else
                                                <span class="badge bg-danger">{{ ucfirst($att->status) }}</span>
                                            @endif
                                        </td>

                                        {{-- METODE --}}
                                        <td>
                                            @if($att->attendance_type == 'scan')
                                                <span class="badge badge-outline-primary"><i class="mdi mdi-qrcode-scan me-1"></i> Scan</span>
                                            @elseif($att->attendance_type == 'self')
                                                <span class="badge badge-outline-info"><i class="mdi mdi-camera-front-variant me-1"></i> Selfie</span>
                                            @else
                                                <span class="badge badge-outline-secondary">System</span>
                                            @endif
                                        </td>
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