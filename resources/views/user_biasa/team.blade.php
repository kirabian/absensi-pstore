@php
    // Kita butuh 'Storage' untuk menampilkan foto
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
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Anggota Tim & Status Absensi Hari Ini</h4>
                    <p class="card-description">
                        Lihat siapa saja di divisi Anda yang sudah absen hari ini.
                    </p>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Nama </th>
                                    <th> Status Absensi </th>
                                    <th> Foto Absen </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myTeam as $key => $member)
                                    <tr>
                                        <td> {{ $key + 1 }} </td>
                                        <td> {{ $member->name }} </td>

                                        @if ($member->attendances->isNotEmpty())
                                            {{-- Jika user SUDAH absen hari ini --}}
                                            @php
                                                $attendance = $member->attendances->first();
                                            @endphp
                                            <td>
                                                @if($attendance->status == 'verified')
                                                    <span class="badge badge-success">Masuk (Terverifikasi)</span>
                                                @else
                                                    <span class="badge badge-warning">Masuk (Pending)</span>
                                                @endif
                                                <br>
                                                <small>{{ $attendance->check_in_time->format('H:i') }} WIB</small>
                                            </td>
                                            <td>
                                                @if($attendance->photo_path)
                                                    {{-- Tampilkan foto kecil yang bisa diklik --}}
                                                    <a href="{{ Storage::url($attendance->photo_path) }}" target="_blank">
                                                        <img src="{{ Storage::url($attendance->photo_path) }}" alt="foto absen"
                                                            style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                                                    </a>
                                                @else
                                                    <small>Tidak ada foto</small>
                                                @endif
                                            </td>
                                        @else
                                            {{-- Jika user BELUM absen hari ini --}}
                                            <td>
                                                <span class="badge badge-danger">Belum Absen</span>
                                            </td>
                                            <td> N/A </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Anda tidak memiliki rekan satu divisi.</td>
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
