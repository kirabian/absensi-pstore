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
                                        <th>Waktu Lapor</th>
                                        <th>Alasan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($latePermissions as $perm)
                                        <tr class="align-middle">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div
                                                            class="avatar-title bg-warning bg-opacity-10 text-warning rounded-circle">
                                                            {{ substr($perm->user->name ?? 'U', 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $perm->user->name ?? 'User Dihapus' }}</h6>
                                                        <span
                                                            class="badge badge-outline-secondary">{{ $perm->user->division->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-nowrap">
                                                    <i class="mdi mdi-clock-outline text-muted me-1"></i>
                                                    {{ $perm->created_at->format('d M Y') }}
                                                </div>
                                                <small class="text-muted">{{ $perm->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="message-container">
                                                    <p class="mb-0 text-break" style="max-width: 300px;">
                                                        {{ $perm->message }}
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning badge-pill">
                                                    <i class="mdi mdi-clock-alert me-1"></i>
                                                    Sedang Telat
                                                </span>
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

        /* Smooth animations */
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
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

        @media (max-width: 576px) {
            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .badge-pill {
                margin-top: 0.5rem;
            }

            .message-container {
                max-width: 150px;
            }
        }
    </style>
@endpush
