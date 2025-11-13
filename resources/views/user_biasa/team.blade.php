@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('layout.master')

@section('title')
    Tim Saya
@endsection

@section('heading')
    Rekan Satu Divisi
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Daftar Anggota Tim & Status Absensi Hari Ini</h4>
                        <div class="badge badge-dark badge-pill">
                            {{ $myTeam->count() }} Anggota
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Nama</th>
                                    <th>Status Absensi</th>
                                    <th>Foto/Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myTeam as $key => $member)
                                    <tr class="align-middle">
                                        <td class="ps-4 fw-semibold">{{ $key + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <div class="avatar-title bg-dark bg-opacity-10 text-dark rounded-circle">
                                                        {{ substr($member->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-semibold">{{ $member->name }}</h6>
                                                    <small class="text-muted">{{ $member->email }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- ======================================= --}}
                                        {{-- BLOK LOGIKA BARU UNTUK STATUS --}}
                                        {{-- ======================================= --}}
                                        @if ($member->attendances->isNotEmpty())
                                            {{-- 1. Jika user SUDAH absen hari ini --}}
                                            @php
                                                $attendance = $member->attendances->first();
                                            @endphp
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($attendance->status == 'verified')
                                                        <span class="badge bg-success badge-pill me-2">
                                                            <i class="mdi mdi-check-circle-outline me-1"></i>Terverifikasi
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark badge-pill me-2">
                                                            <i class="mdi mdi-clock-outline me-1"></i>Menunggu
                                                        </span>
                                                    @endif
                                                    <div>
                                                        <small class="text-muted d-block">{{ $attendance->check_in_time->format('H:i') }} WIB</small>
                                                        <small class="text-muted">{{ $attendance->check_in_time->format('d M Y') }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($attendance->photo_path)
                                                    <a href="{{ Storage::url($attendance->photo_path) }}" target="_blank" class="image-popup">
                                                        <div class="position-relative" style="width: 60px; height: 60px;">
                                                            <img src="{{ Storage::url($attendance->photo_path) }}"
                                                                 alt="foto absen"
                                                                 class="rounded shadow-sm"
                                                                 style="width: 100%; height: 100%; object-fit: cover;">
                                                            <div class="position-absolute top-0 end-0 m-1">
                                                                <i class="mdi mdi-magnify-plus-outline text-white bg-dark bg-opacity-50 rounded-circle p-1"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="mdi mdi-image-off"></i>
                                                        Tidak ada foto
                                                    </span>
                                                @endif
                                            </td>

                                        @elseif ($member->activeLateStatus)
                                            {{-- 2. (BARU) Jika user IZIN TELAT hari ini --}}
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-info badge-pill me-2">
                                                        <i class="mdi mdi-alert-circle-outline me-1"></i>Izin Telat
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="message-container">
                                                    <p class="mb-0 text-break small" style="max-width: 200px;">
                                                        <i class="mdi mdi-chat-alert-outline me-1 text-info"></i>
                                                        "{{ $member->activeLateStatus->message }}"
                                                    </p>
                                                    <small class="text-muted">
                                                        {{ $member->activeLateStatus->created_at->format('H:i') }}
                                                    </small>
                                                </div>
                                            </td>

                                        @else
                                            {{-- 3. Jika user BELUM absen hari ini --}}
                                            <td>
                                                <span class="badge bg-danger badge-pill">
                                                    <i class="mdi mdi-close-circle-outline me-1"></i>Belum Absen
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    <i class="mdi mdi-clock-alert"></i>
                                                    Menunggu absensi
                                                </span>
                                            </td>
                                        @endif
                                        {{-- ======================================= --}}
                                        {{-- AKHIR BLOK LOGIKA BARU --}}
                                        {{-- ======================================= --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-account-multiple-outline display-4 text-muted"></i>
                                            </div>
                                            <h5 class="text-muted">Tidak ada rekan satu divisi</h5>
                                            <p class="text-muted">Anda saat ini tidak memiliki anggota tim di divisi yang sama.</p>
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

        .badge-pill {
            border-radius: 20px;
            padding: 8px 12px;
            font-weight: 500;
        }

        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
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

        .image-popup:hover img {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Image popup functionality
        document.addEventListener('DOMContentLoaded', function() {
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            const modalImage = document.getElementById('modalImage');

            document.querySelectorAll('.image-popup').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    modalImage.src = this.href;
                    imageModal.show();
                });
            });
        });
    </script>
@endpush
