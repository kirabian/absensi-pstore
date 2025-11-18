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
                                    <th>Status & Waktu</th>
                                    <th>Foto / Keterangan</th>
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
                                        {{-- KOLOM STATUS ABSENSI (UPDATED) --}}
                                        {{-- ======================================= --}}
                                        @if ($member->attendances->isNotEmpty())
                                            @php
                                                $attendance = $member->attendances->first();
                                            @endphp
                                            <td>
                                                {{-- Badge Status --}}
                                                <div class="mb-2">
                                                    @if ($attendance->check_out_time)
                                                        <span class="badge bg-primary badge-pill">
                                                            <i class="mdi mdi-home-variant-outline me-1"></i>Sudah Pulang
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success badge-pill">
                                                            <i class="mdi mdi-briefcase-check-outline me-1"></i>Sedang Bekerja
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Detail Waktu --}}
                                                <div class="small text-muted">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="mdi mdi-login text-success me-2"></i>
                                                        <span>Masuk: <strong>{{ $attendance->check_in_time->format('H:i') }}</strong></span>
                                                    </div>
                                                    @if ($attendance->check_out_time)
                                                        <div class="d-flex align-items-center">
                                                            <i class="mdi mdi-logout text-primary me-2"></i>
                                                            <span>Pulang: <strong>{{ $attendance->check_out_time->format('H:i') }}</strong></span>
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center text-muted opacity-50">
                                                            <i class="mdi mdi-logout me-2"></i>
                                                            <span>Belum Pulang</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            {{-- Foto Absen --}}
                                            <td>
                                                @if($attendance->photo_path)
                                                    <a href="{{ Storage::url($attendance->photo_path) }}" target="_blank" class="image-popup">
                                                        <div class="position-relative" style="width: 60px; height: 60px;">
                                                            <img src="{{ Storage::url($attendance->photo_path) }}"
                                                                 alt="foto absen"
                                                                 class="rounded shadow-sm border"
                                                                 style="width: 100%; height: 100%; object-fit: cover;">
                                                            <div class="position-absolute top-0 end-0 m-1">
                                                                <i class="mdi mdi-magnify-plus-outline text-white bg-dark bg-opacity-50 rounded-circle p-1" style="font-size: 10px;"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                @else
                                                    <span class="text-muted small fst-italic">
                                                        <i class="mdi mdi-image-off me-1"></i>No Photo
                                                    </span>
                                                @endif
                                            </td>

                                        @elseif ($member->activeLateStatus)
                                            {{-- 2. KASUS IZIN / SAKIT / TELAT --}}
                                            <td>
                                                <div class="mb-2">
                                                    <span class="badge bg-info badge-pill">
                                                        <i class="mdi mdi-file-document-outline me-1"></i>Izin / Sakit
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    Diajukan: {{ $member->activeLateStatus->created_at->format('H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="message-container p-2 bg-light rounded border border-light">
                                                    <p class="mb-0 text-break small text-dark">
                                                        <i class="mdi mdi-format-quote-open text-info me-1"></i>
                                                        {{ Str::limit($member->activeLateStatus->message, 50) }}
                                                    </p>
                                                </div>
                                            </td>

                                        @else
                                            {{-- 3. KASUS BELUM ABSEN --}}
                                            <td>
                                                <span class="badge bg-danger badge-pill">
                                                    <i class="mdi mdi-close-circle-outline me-1"></i>Belum Hadir
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    <i class="mdi mdi-clock-alert me-1"></i>Menunggu
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="mdi mdi-account-group-outline display-4 text-muted opacity-25"></i>
                                            </div>
                                            <h5 class="text-muted">Tidak ada rekan satu divisi</h5>
                                            <p class="text-muted small">Anda saat ini sendirian di divisi ini.</p>
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
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Bukti Foto Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4 bg-light">
                    <img id="modalImage" src="" alt="Foto Absensi" class="img-fluid rounded shadow">
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
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }

        .badge-pill {
            border-radius: 50rem;
            padding: 0.5em 1em;
            font-weight: 600;
        }

        .table > :not(caption) > * > * {
            padding: 1.2rem 1rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.015);
        }

        .image-popup {
            display: inline-block;
            transition: transform 0.2s;
        }
        
        .image-popup:hover {
            transform: scale(1.05);
        }

        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            border-radius: 1rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
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