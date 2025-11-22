@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@extends('layout.master')

@section('title', 'Tim Saya')
@section('heading', 'Rekan Kerja')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">
                            @if(Auth::user()->role == 'audit')
                                Daftar Rekan di Wilayah Audit
                            @else
                                Daftar Anggota Tim (Multi Divisi)
                            @endif
                        </h4>
                        <span class="badge bg-dark rounded-pill px-3 py-2">
                            {{ $myTeam->count() }} Orang
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" width="5%">#</th>
                                    <th width="35%">Nama & Posisi</th> {{-- Kolom Lebar --}}
                                    <th width="30%">Status Absensi</th>
                                    <th width="30%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myTeam as $key => $member)
                                    <tr>
                                        <td class="ps-4 fw-bold text-muted">{{ $key + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- Avatar Inisial --}}
                                                <div class="avatar-sm me-3 flex-shrink-0">
                                                    @if($member->profile_photo_path)
                                                        <img src="{{ Storage::url($member->profile_photo_path) }}" alt="" class="rounded-circle w-100 h-100 object-fit-cover">
                                                    @else
                                                        <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fw-bold d-flex align-items-center justify-content-center w-100 h-100">
                                                            {{ substr($member->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                {{-- Nama & Divisi --}}
                                                <div class="overflow-hidden">
                                                    <h6 class="mb-1 text-truncate fw-bold text-dark">{{ $member->name }}</h6>
                                                    
                                                    {{-- Tampilkan Cabang (Untuk Audit) atau Divisi (Untuk Lainnya) --}}
                                                    <div class="d-flex flex-wrap gap-1">
                                                        {{-- Tampilkan Cabang --}}
                                                        <span class="badge bg-light text-dark border" style="font-size: 10px;">
                                                            <i class="mdi mdi-map-marker me-1"></i>{{ $member->branch->name ?? 'No Branch' }}
                                                        </span>

                                                        {{-- Tampilkan Divisi (Looping karena Multi) --}}
                                                        @foreach($member->divisions as $div)
                                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" style="font-size: 10px;">
                                                                {{ $div->name }}
                                                            </span>
                                                            {{-- Batasi tampilan jika terlalu banyak divisi --}}
                                                            @if($loop->iteration >= 3)
                                                                <span class="badge bg-light text-muted border" style="font-size: 10px;">+{{ $member->divisions->count() - 3 }}</span>
                                                                @break
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- LOGIKA ABSENSI (Copy paste dari yang lama tapi dirapikan) --}}
                                        <td>
                                            @php
                                                $attendance = $member->attendances->first();
                                            @endphp

                                            @if ($attendance)
                                                @if ($attendance->check_out_time)
                                                    <span class="badge bg-primary bg-opacity-10 text-primary mb-1">
                                                        <i class="mdi mdi-home-variant me-1"></i>Pulang: {{ $attendance->check_out_time->format('H:i') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-10 text-success mb-1">
                                                        <i class="mdi mdi-briefcase-check me-1"></i>Masuk: {{ $attendance->check_in_time->format('H:i') }}
                                                    </span>
                                                @endif
                                            @elseif ($member->activeLateStatus)
                                                <span class="badge bg-warning bg-opacity-10 text-warning mb-1">
                                                    <i class="mdi mdi-file-document me-1"></i>Izin/Sakit
                                                </span>
                                                <div class="small text-muted fst-italic text-truncate" style="max-width: 150px;">
                                                    "{{ $member->activeLateStatus->message }}"
                                                </div>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                                    <i class="mdi mdi-close-circle me-1"></i>Belum Hadir
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Kolom Foto / Aksi --}}
                                        <td>
                                            @if ($attendance)
                                                @php
                                                    // Prioritas foto pulang, kalau ga ada foto masuk
                                                    $photo = $attendance->photo_out_path ?? $attendance->photo_path;
                                                @endphp
                                                @if($photo)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary image-popup" 
                                                            data-bs-toggle="modal" data-bs-target="#imageModal" 
                                                            data-src="{{ Storage::url($photo) }}">
                                                        <i class="mdi mdi-camera me-1"></i>Lihat Foto
                                                    </button>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted opacity-50 mb-2">
                                                <i class="mdi mdi-account-off display-4"></i>
                                            </div>
                                            <h6 class="text-muted">Tidak ada rekan kerja yang ditemukan.</h6>
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

    {{-- Modal Image --}}
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="" id="modalImageSrc" class="w-100 rounded" alt="Bukti Absen">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var imageModal = document.getElementById('imageModal');
        imageModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var src = button.getAttribute('data-src');
            var modalImg = document.getElementById('modalImageSrc');
            modalImg.src = src;
        });
    });
</script>
@endpush