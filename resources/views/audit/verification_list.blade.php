@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layout.master')

@section('title')
    Verifikasi Absensi
@endsection

@section('heading')
    Verifikasi Absensi Mandiri
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Daftar Absensi Menunggu Persetujuan</h4>
                        <div class="badge badge-info badge-pill">
                            {{ $pendingAttendances->count() }} Menunggu
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle-outline me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($pendingAttendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">User & Divisi</th>
                                        <th>Waktu Absen</th>
                                        <th>Foto</th>
                                        <th>Lokasi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingAttendances as $att)
                                        <tr class="align-middle">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div
                                                            class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle">
                                                            {{ substr($att->user->name ?? 'U', 0, 1) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $att->user->name ?? 'User Dihapus' }}</h6>
                                                        <span
                                                            class="badge badge-outline-secondary">{{ $att->user->division->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-nowrap">
                                                    <i class="mdi mdi-clock-outline text-muted me-1"></i>
                                                    {{ $att->check_in_time->format('d M Y') }}
                                                </div>
                                                <small class="text-muted">{{ $att->check_in_time->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ Storage::url($att->photo_path) }}" target="_blank" class="image-popup">
                                                    <div class="position-relative" style="width: 60px; height: 60px;">
                                                        <img src="{{ Storage::url($att->photo_path) }}" alt="foto absen"
                                                            class="rounded shadow-sm"
                                                            style="width: 100%; height: 100%; object-fit: cover;">
                                                        <div class="position-absolute top-0 end-0 m-1">
                                                            <i
                                                                class="mdi mdi-magnify-plus-outline text-white bg-dark bg-opacity-50 rounded-circle p-1"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>
                                                @if($att->latitude && $att->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $att->latitude }},{{ $att->longitude }}"
                                                        target="_blank" class="btn btn-outline-info btn-sm btn-icon">
                                                        <i class="mdi mdi-map-marker-outline"></i>
                                                        Maps
                                                    </a>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="mdi mdi-map-marker-off"></i>
                                                        No Location
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <form action="{{ route('audit.approve', $att->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success btn-sm btn-icon"
                                                            data-bs-toggle="tooltip" title="Setujui Absensi">
                                                            <i class="mdi mdi-check"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('audit.reject', $att->id) }}" method="POST"
                                                        onsubmit="return confirm('Yakin ingin menolak absensi ini? Data akan dihapus.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm btn-icon"
                                                            data-bs-toggle="tooltip" title="Tolak Absensi">
                                                            <i class="mdi mdi-close"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="mdi mdi-clipboard-check-outline display-4 text-muted"></i>
                            </div>
                            <h5 class="text-muted">Tidak ada absensi menunggu persetujuan</h5>
                            <p class="text-muted mb-4">Semua absensi sudah terverifikasi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Image Popup Modal --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foto Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Foto Absensi" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Image popup functionality
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Image popup
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            const modalImage = document.getElementById('modalImage');

            document.querySelectorAll('.image-popup').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    modalImage.src = this.href;
                    imageModal.show();
                });
            });
        });
    </script>
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

        .btn-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 0.75rem;
        }

        .badge-outline-secondary {
            border: 1px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }

        .image-popup:hover img {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
@endpush
