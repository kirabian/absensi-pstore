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
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Absensi Menunggu Persetujuan</h4>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> User </th>
                                    <th> Waktu Absen </th>
                                    <th> Foto </th>
                                    <th> Lokasi </th>
                                    <th> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingAttendances as $att)
                                    <tr>
                                        <td>
                                            {{ $att->user->name ?? 'User Dihapus' }}
                                            <br>
                                            <small>{{ $att->user->division->name ?? 'N/A' }}</small>
                                        </td>
                                        <td> {{ $att->check_in_time->format('d M Y, H:i') }} </td>
                                        <td>
                                            <a href="{{ Storage::url($att->photo_path) }}" target="_blank">
                                                <img src="{{ Storage::url($att->photo_path) }}" alt="foto absen"
                                                    style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                                            </a>
                                        </td>
                                        <td>
                                            {{-- INI LINK KE GOOGLE MAPS --}}
                                            <a href="https://www.google.com/maps?q={{ $att->latitude }},{{ $att->longitude }}"
                                                target="_blank" class="btn btn-info btn-sm">
                                                Lihat Lokasi
                                            </a>
                                        </td>
                                        <td>
                                            {{-- Tombol Approve --}}
                                            <form action="{{ route('audit.approve', $att->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                            </form>

                                            {{-- Tombol Reject --}}
                                            <form action="{{ route('audit.reject', $att->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menolak absensi ini? Data akan dihapus.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data absensi untuk diverifikasi.</td>
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
