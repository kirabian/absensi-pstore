@extends('layout.master')

@section('title')
    Izin Telat Masuk
@endsection

@section('heading')
    Daftar Izin Telat (Aktif)
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">User yang Memberi Laporan Telat</h4>
                    <p class="card-description">
                        User tidak bisa absen mandiri sebelum laporan ini dihapus (oleh mereka sendiri).
                    </p>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> User </th>
                                    <th> Divisi </th>
                                    <th> Waktu Lapor </th>
                                    <th> Alasan (Pesan) </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latePermissions as $perm)
                                    <tr>
                                        <td>{{ $perm->user->name ?? 'User Dihapus' }}</td>
                                        <td>{{ $perm->user->division->name ?? 'N/A' }}</td>
                                        <td>{{ $perm->created_at->format('d M Y, H:i') }}</td>
                                        <td>{{ $perm->message }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada user yang sedang izin telat.</td>
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
