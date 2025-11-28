@extends('layout.master')

@section('title')
    Data Cabang
@endsection

@section('heading')
    Manajemen Cabang
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Daftar Cabang PStore</h4>
                        
                        {{-- Tombol Tambah: Hanya muncul untuk Super Admin --}}
                        @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                            <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus me-1"></i> Tambah Baru
                            </a>
                        @endif
                    </div>

                    {{-- Notifikasi --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Tabel Responsive --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%"> # </th>
                                    <th> Nama Cabang </th>
                                    {{-- Sembunyikan Alamat di layar HP kecil (d-none d-md-table-cell) --}}
                                    <th class="d-none d-md-table-cell"> Alamat </th>
                                    <th width="15%" class="text-center"> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $key => $branch)
                                    <tr>
                                        <td> {{ $key + 1 }} </td>
                                        <td> 
                                            <span class="fw-bold text-dark">{{ $branch->name }}</span>
                                            {{-- Tampilkan alamat di bawah nama khusus layar HP --}}
                                            <div class="d-md-none text-muted small mt-1">
                                                <i class="mdi mdi-map-marker"></i> {{ Str::limit($branch->address, 30) }}
                                            </div>
                                        </td>
                                        
                                        {{-- Kolom Alamat (Desktop Only) --}}
                                        <td class="d-none d-md-table-cell"> 
                                            {{ Str::limit($branch->address ?? 'N/A', 50) }} 
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                
                                                {{-- Tombol LIHAT (Mata) - Muncul untuk Semua Role --}}
                                                <a href="{{ route('branches.show', $branch->id) }}"
                                                    class="btn btn-info text-white" 
                                                    title="Lihat Detail & Karyawan">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>

                                                {{-- Tombol EDIT & DELETE: Tidak muncul untuk Audit --}}
                                                @if(auth()->user()->role != 'audit')
                                                    
                                                    <a href="{{ route('branches.edit', $branch->id) }}"
                                                        class="btn btn-warning text-white" 
                                                        title="Edit Data">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>

                                                    {{-- Tombol HAPUS: Hanya Super Admin --}}
                                                    @if(auth()->user()->role == 'admin' && auth()->user()->branch_id == null)
                                                        <form action="{{ route('branches.destroy', $branch->id) }}" method="POST"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Yakin ingin menghapus cabang ini? Data user terkait mungkin akan error.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" title="Hapus">
                                                                <i class="mdi mdi-delete"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="mdi mdi-office-building-marker-outline display-4 mb-2 d-block"></i>
                                                Belum ada data cabang.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- End Table Responsive --}}
                    
                </div>
            </div>
        </div>
    </div>
@endsection