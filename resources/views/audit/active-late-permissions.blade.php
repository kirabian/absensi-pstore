@extends('layout.master')

@section('title')
    Izin Telat Masuk
@endsection

@section('heading')
    Daftar Izin Telat (Aktif)
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Laporan Telat Aktif</h4>
                            <p class="text-muted mb-0">
                                User tidak bisa absen mandiri selama laporan aktif
                            </p>
                        </div>
                        <div class="badge badge-warning badge-pill">
                            {{ $latePermissions->count() }} Aktif
                        </div>
                    </div>

                    @if($latePermissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">User & Divisi</th>
                                        <th>Tanggal Izin</th>
                                        <th>Alasan</th>
                                        <th>Bukti Foto</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($latePermissions as $perm)
                                        <tr class="align-middle">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded-circle">
                                                            {{ substr($perm->user->name ?? 'U', 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $perm->user->name ?? 'User Dihapus' }}</h6>
                                                        <span class="badge badge-outline-secondary">{{ $perm->user->division->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-nowrap">
                                                    <i class="mdi mdi-calendar text-muted me-1"></i>
                                                    {{ \Carbon\Carbon::parse($perm->start_date)->format('d M Y') }}
                                                </div>
                                                <small class="text-muted">Diajukan: {{ $perm->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="message-container">
                                                    <p class="mb-0 text-break" style="max-width: 300px;">
                                                        {{ $perm->reason }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                @if($perm->file_proof)
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#proofModal{{ $perm->id }}">
                                                        <i class="mdi mdi-image me-1"></i>Lihat Bukti
                                                    </button>
                                                    
                                                    <!-- Modal untuk melihat foto -->
                                                    <div class="modal fade" id="proofModal{{ $perm->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Bukti Izin Telat - {{ $perm->user->name }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ asset('storage/' . $perm->file_proof) }}" 
                                                                         class="img-fluid rounded" 
                                                                         alt="Bukti Izin Telat"
                                                                         style="max-height: 70vh;">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge badge-outline-danger">Tidak ada bukti</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-warning badge-pill">
                                                    <i class="mdi mdi-clock-alert me-1"></i>
                                                    Sedang Telat
                                                </span>
                                            </td>
                                            <td>
                                                @if(auth()->user()->role == 'admin' || auth()->user()->id == $perm->user_id)
                                                    <form action="{{ route('audit.cancel-late', $perm->id) }}" method="POST" 
                                                          class="d-inline" 
                                                          onsubmit="return confirm('Yakin ingin membatalkan izin telat ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="mdi mdi-cancel me-1"></i>Batalkan
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="mdi mdi-clock-check-outline display-4 text-muted"></i>
                            </div>
                            <h5 class="text-muted">Tidak ada laporan telat aktif</h5>
                            <p class="text-muted mb-4">Semua user tepat waktu hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-title {
            font-weight: 600;
            font-size: 16px;
        }

        .badge-outline-secondary {
            border: 1px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }

        .badge-outline-danger {
            border: 1px solid #dc3545;
            color: #dc3545;
            background: transparent;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 193, 7, 0.04);
            transition: background-color 0.2s ease;
        }

        .message-container {
            max-height: 60px;
            overflow: hidden;
            position: relative;
        }

        .message-container:hover {
            max-height: none;
            background: linear-gradient(transparent, #f8f9fa 10px);
        }

        .table> :not(caption)>*>* {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .avatar-sm {
                width: 32px;
                height: 32px;
            }

            .avatar-title {
                font-size: 14px;
            }

            .message-container {
                max-width: 200px;
            }
        }
    </style>
@endpush