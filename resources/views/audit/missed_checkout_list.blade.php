@extends('layout.master')

@section('title')
    Lupa Absen Pulang
@endsection

@section('heading')
    Verifikasi Lupa Absen Pulang
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Daftar Karyawan Belum Check-Out (Hari Sebelumnya)</h4>
                    
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($missedCheckouts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Karyawan</th>
                                        <th>Tanggal Masuk</th>
                                        <th>Jam Masuk</th>
                                        <th>Durasi Gantung</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($missedCheckouts as $att)
                                        <tr class="align-middle">
                                            <td>
                                                <div class="fw-bold">{{ $att->user->name ?? 'User Dihapus' }}</div>
                                                <small class="text-muted">{{ $att->user->division->name ?? '-' }}</small>
                                            </td>
                                            <td>{{ $att->check_in_time->format('d M Y') }}</td>
                                            <td><span class="badge bg-success">{{ $att->check_in_time->format('H:i') }}</span></td>
                                            <td>
                                                <span class="text-danger fw-bold">
                                                    {{ $att->check_in_time->diffForHumans(null, true) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalManualCheckout{{ $att->id }}">
                                                    <i class="mdi mdi-clock-check-outline me-1"></i> Set Jam Pulang
                                                </button>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="modalManualCheckout{{ $att->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Manual Checkout: {{ $att->user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('audit.missed-checkout.update', $att->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label">Tanggal Masuk</label>
                                                                <input type="text" class="form-control" value="{{ $att->check_in_time->format('d F Y') }}" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Jam Masuk</label>
                                                                <input type="text" class="form-control" value="{{ $att->check_in_time->format('H:i') }}" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Set Jam Pulang <span class="text-danger">*</span></label>
                                                                <input type="time" name="checkout_time" class="form-control" required>
                                                                <small class="text-muted">Masukkan jam pulang sebenarnya.</small>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Catatan Audit</label>
                                                                <textarea name="notes" class="form-control" rows="2" placeholder="Alasan lupa absen / keterangan lain"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan & Selesaikan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-check-circle-outline display-4 text-muted"></i>
                            <h5 class="text-muted mt-3">Tidak ada data gantung</h5>
                            <p class="text-muted">Semua karyawan sudah absen pulang dengan tertib.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection