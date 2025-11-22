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
                                    <th width="40%">Nama & Posisi</th>
                                    <th width="25%">Status Absensi</th>
                                    <th width="30%">Keterangan / Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myTeam as $key => $member)
                                    <tr>
                                        <td class="ps-4 fw-bold text-muted">{{ $key + 1 }}</td>
                                        
                                        {{-- KOLOM NAMA & FOTO --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {{-- AVATAR (FIXED SIZE) --}}
                                                <div class="me-3 flex-shrink-0" style="width: 50px; height: 50px; min-width: 50px;">
                                                    @if($member->profile_photo_path)
                                                        <img src="{{ Storage::url($member->profile_photo_path) }}" 
                                                             alt="{{ $member->name }}" 
                                                             class="rounded-circle shadow-sm border"
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-light text-dark fw-bold d-flex align-items-center justify-content-center border"
                                                             style="width: 50px; height: 50px; font-size: 20px;">
                                                            {{ substr($member->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                {{-- NAMA & INFO --}}
                                                <div style="min-width: 0;">
                                                    <h6 class="mb-1 fw-bold text-dark text-truncate">{{ $member->name }}</h6>
                                                    
                                                    <div class="d-flex flex-wrap gap-1">
                                                        {{-- Cabang (Abu-abu teks Hitam) --}}
                                                        <span class="badge bg-secondary text-white border border-secondary" style="font-size: 11px; font-weight: normal;">
                                                            <i class="mdi mdi-map-marker me-1"></i>{{ $member->branch->name ?? 'No Branch' }}
                                                        </span>

                                                        {{-- Divisi (Biru Muda teks Hitam - Kontras Tinggi) --}}
                                                        @foreach($member->divisions as $div)
                                                            <span class="badge bg-info text-dark border border-info" style="font-size: 11px; font-weight: normal;">
                                                                {{ $div->name }}
                                                            </span>
                                                            @if($loop->iteration >= 2)
                                                                <span class="badge bg-light text-dark border" style="font-size: 10px;">+{{ $member->divisions->count() - 2 }}</span>
                                                                @break
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- KOLOM STATUS (WARNA SOLID TEKS PUTIH) --}}
                                        <td>
                                            @php
                                                $attendance = $member->attendances->first();
                                            @endphp

                                            @if ($attendance)
                                                @if ($attendance->check_out_time)
                                                    <span class="badge bg-primary text-white mb-1 rounded-pill px-3 py-2">
                                                        <i class="mdi mdi-home-variant me-1"></i>Pulang: {{ $attendance->check_out_time->format('H:i') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success text-white mb-1 rounded-pill px-3 py-2">
                                                        <i class="mdi mdi-briefcase-check me-1"></i>Masuk: {{ $attendance->check_in_time->format('H:i') }}
                                                    </span>
                                                @endif
                                            @elseif ($member->activeLateStatus)
                                                <span class="badge bg-warning text-dark mb-1 rounded-pill px-3 py-2">
                                                    <i class="mdi mdi-file-document me-1"></i>Izin/Sakit
                                                </span>
                                            @else
                                                <span class="badge bg-danger text-white rounded-pill px-3 py-2">
                                                    <i class="mdi mdi-close-circle me-1"></i>Belum Hadir
                                                </span>
                                            @endif
                                        </td>

                                        {{-- KOLOM KETERANGAN / FOTO BUKTI --}}
                                        <td>
                                            @if ($attendance)
                                                @php
                                                    $photo = $attendance->photo_out_path ?? $attendance->photo_path;
                                                @endphp
                                                @if($photo)
                                                    <button type="button" class="btn btn-sm btn-outline-dark image-popup d-flex align-items-center gap-2" 
                                                            data-bs-toggle="modal" data-bs-target="#imageModal" 
                                                            data-src="{{ Storage::url($photo) }}">
                                                        <div style="width: 30px; height: 30px; overflow: hidden;" class="rounded border">
                                                            <img src="{{ Storage::url($photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                        </div>
                                                        <span class="fw-bold">Lihat Foto</span>
                                                    </button>
                                                @else
                                                    <span class="text-muted small fst-italic">- Tidak ada foto -</span>
                                                @endif
                                            @elseif ($member->activeLateStatus)
                                                <div class="small text-dark fst-italic text-truncate border-start border-3 border-warning ps-2 fw-bold" style="max-width: 200px;">
                                                    "{{ $member->activeLateStatus->message }}"
                                                </div>
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
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white p-2 rounded shadow" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="" id="modalImageSrc" class="w-100 rounded" alt="Bukti Absen" style="max-height: 80vh; object-fit: contain; background: #000;">
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