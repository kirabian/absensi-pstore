@extends('layout.master')

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Daftar Pengajuan Izin / Telat</h4>
                    {{-- Tombol Buat Baru (Hanya User Biasa) --}}
                    @if(auth()->user()->role == 'user_biasa' || auth()->user()->role == 'leader')
                        <a href="{{ route('leave.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Ajukan Baru
                        </a>
                    @endif
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Tipe</th>
                                <th>Waktu / Tanggal</th>
                                <th>Alasan</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{-- Tampilkan Avatar Kecil --}}
                                        <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center text-white me-2" style="width: 30px; height: 30px;">
                                            {{ substr($req->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block" style="font-size: 12px;">{{ $req->user->name }}</span>
                                            <small class="text-muted">{{ $req->user->division->name ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($req->type == 'sakit')
                                        <span class="badge bg-danger">Sakit</span>
                                    @elseif($req->type == 'izin')
                                        <span class="badge bg-info">Izin</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Telat</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->type == 'telat')
                                        <div class="text-muted" style="font-size: 12px;">
                                            <i class="mdi mdi-calendar"></i> {{ $req->start_date->format('d M') }} <br>
                                            <i class="mdi mdi-clock"></i> {{ \Carbon\Carbon::parse($req->start_time)->format('H:i') }}
                                        </div>
                                    @else
                                        <div class="text-muted" style="font-size: 12px;">
                                            {{ $req->start_date->format('d M') }} 
                                            s/d 
                                            {{ $req->end_date->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td style="max-width: 150px;" class="text-wrap">
                                    {{ $req->reason }}
                                </td>
                                <td>
                                    <a href="{{ asset('storage/'.$req->file_proof) }}" target="_blank" class="btn btn-light btn-sm">
                                        <i class="mdi mdi-image"></i> Lihat
                                    </a>
                                </td>
                                <td>
                                    @if($req->status == 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif($req->status == 'rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @elseif($req->status == 'cancelled')
                                        <span class="badge bg-secondary">Dibatalkan</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- AKSI UNTUK USER (BATALKAN) --}}
                                    @if(auth()->id() == $req->user_id && $req->status != 'cancelled' && $req->status != 'rejected')
                                        <form action="{{ route('leave.cancel', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Sudah sampai kantor atau ingin membatalkan izin ini?')">
                                            @csrf
                                            @method('PATCH')
                                            @if($req->type == 'telat' && $req->status == 'approved')
                                                <button type="submit" class="btn btn-success btn-sm text-white">
                                                    <i class="mdi mdi-check"></i> Sampai Kantor
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-secondary btn-sm">
                                                    <i class="mdi mdi-close"></i> Batalkan
                                                </button>
                                            @endif
                                        </form>
                                    @endif

                                    {{-- AKSI UNTUK ADMIN/AUDIT (APPROVE/REJECT) --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'audit']) && $req->status == 'pending')
                                        <form action="{{ route('leave.approve', $req->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-success btn-sm p-2" title="Setujui"><i class="mdi mdi-check"></i></button>
                                        </form>
                                        <form action="{{ route('leave.reject', $req->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-danger btn-sm p-2" title="Tolak"><i class="mdi mdi-close"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection