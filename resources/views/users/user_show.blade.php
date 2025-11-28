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
    {{-- KOLOM KANAN: RIWAYAT ABSENSI --}}
    {{-- ========================================================= --}}
    <div class="col-md-8 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Riwayat Absensi Terakhir</h4>
                    {{-- Statistik Singkat --}}
                    <div>
                        <span class="badge badge-outline-success me-1">Hadir: {{ $stats['present'] ?? 0 }}</span>
                        <span class="badge badge-outline-danger">Telat: {{ $stats['late'] ?? 0 }}</span>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jam Masuk/Pulang</th>
                                <th>Bukti Karyawan</th>
                                <th>Status Kehadiran</th>
                                <th>Verifikasi Audit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendance as $att)
                            <tr>
                                {{-- Tanggal --}}
                                <td>
                                    <div class="fw-bold">{{ $att->check_in_time->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $att->check_in_time->format('l') }}</small>
                                </td>

                                {{-- Jam --}}
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-success small mb-1">
                                            <i class="mdi mdi-login"></i> {{ $att->check_in_time->format('H:i') }}
                                        </span>
                                        @if($att->check_out_time)
                                            <span class="text-danger small">
                                                <i class="mdi mdi-logout"></i> {{ $att->check_out_time->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Bukti Karyawan (Selfie/Lokasi) --}}
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($att->photo_path)
                                            <a href="{{ asset('storage/' . $att->photo_path) }}" target="_blank" title="Foto Masuk">
                                                <img src="{{ asset('storage/' . $att->photo_path) }}" class="rounded border" style="width: 35px; height: 35px; object-fit: cover;">
                                            </a>
                                        @endif
                                        @if($att->photo_out_path)
                                            <a href="{{ asset('storage/' . $att->photo_out_path) }}" target="_blank" title="Foto Pulang">
                                                <img src="{{ asset('storage/' . $att->photo_out_path) }}" class="rounded border" style="width: 35px; height: 35px; object-fit: cover;">
                                            </a>
                                        @endif
                                        @if(!$att->photo_path && !$att->photo_out_path)
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Kehadiran (Presence Status) --}}
                                <td>
                                    @if($att->presence_status)
                                        <span class="badge bg-{{ $att->presence_status_badge }}">
                                            {{ $att->presence_status }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Belum Verif</span>
                                    @endif
                                    
                                    @if($att->is_late_checkin)
                                        <div class="mt-1"><small class="text-danger fw-bold" style="font-size: 10px;">Telat System</small></div>
                                    @endif
                                </td>

                                {{-- Status Verifikasi & Bukti Audit --}}
                                <td>
                                    @if($att->status == 'verified')
                                        <div class="text-success fw-bold" style="font-size: 12px;"><i class="mdi mdi-check-circle"></i> Verified</div>
                                        <small class="text-muted d-block" style="font-size: 10px;">by {{ $att->verifier->name ?? 'System' }}</small>
                                        
                                        @if($att->audit_photo_path)
                                            <div class="mt-1">
                                                <a href="{{ asset('storage/' . $att->audit_photo_path) }}" target="_blank" class="badge badge-outline-info">
                                                    <i class="mdi mdi-image"></i> Bukti Audit
                                                </a>
                                            </div>
                                        @endif
                                    @elseif($att->status == 'rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>

                                {{-- AKSI --}}
                                <td>
                                    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
                                        <button class="btn btn-primary btn-sm btn-icon-text" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#verifyModal{{ $att->id }}"
                                                title="Verifikasi / Crosscheck">
                                            <i class="mdi mdi-file-check btn-icon-prepend"></i> Verif
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            {{-- ========================================== --}}
                            {{-- MODAL VERIFIKASI (Looping per item) --}}
                            {{-- ========================================== --}}
                            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'audit')
                            <div class="modal fade" id="verifyModal{{ $att->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('audit.verify.attendance', $att->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Crosscheck Absensi</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                {{-- Pilihan Status --}}
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Status Kehadiran Real <span class="text-danger">*</span></label>
                                                    <select name="presence_status" class="form-select" required>
                                                        <option value="" disabled selected>-- Pilih Status --</option>
                                                        <option value="Masuk" {{ $att->presence_status == 'Masuk' ? 'selected' : '' }}>Masuk (Hadir)</option>
                                                        <option value="Izin Telat" {{ $att->presence_status == 'Izin Telat' ? 'selected' : '' }}>Izin Telat</option>
                                                        <option value="WFH / Dinas Luar" {{ $att->presence_status == 'WFH / Dinas Luar' ? 'selected' : '' }}>WFH / Dinas Luar</option>
                                                        <option value="Telat" {{ $att->presence_status == 'Telat' ? 'selected' : '' }}>Telat (Tanpa Izin)</option>
                                                        <option value="Sakit" {{ $att->presence_status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                                        <option value="Cuti" {{ $att->presence_status == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                                        <option value="Alpha" {{ $att->presence_status == 'Alpha' ? 'selected' : '' }}>Alpha / Tidak Hadir</option>
                                                        <option value="Libur" {{ $att->presence_status == 'Libur' ? 'selected' : '' }}>Libur</option>
                                                    </select>
                                                </div>

                                                {{-- Upload Bukti Audit --}}
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Bukti Audit (Foto/Screenshot)</label>
                                                    <input type="file" name="audit_photo" class="form-control" accept="image/*">
                                                    <small class="text-muted d-block mt-1">Ambil foto langsung atau pilih dari galeri sebagai bukti crosscheck.</small>
                                                </div>

                                                {{-- Catatan --}}
                                                <div class="mb-3">
                                                    <label class="form-label">Catatan Tambahan (Opsional)</label>
                                                    <textarea name="audit_note" class="form-control" rows="2" placeholder="Contoh: Karyawan lupa absen tapi ada di kantor">{{ $att->audit_note }}</textarea>
                                                </div>

                                                <div class="alert alert-info py-2 mb-0">
                                                    <small><i class="mdi mdi-information"></i> Dengan menekan tombol simpan, status verifikasi akan berubah menjadi <b>Verified</b>.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success text-white">
                                                    <i class="mdi mdi-check-all me-1"></i> Simpan & Verifikasi
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif
                            {{-- END MODAL --}}

                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">Belum ada data absensi terbaru.</div>
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