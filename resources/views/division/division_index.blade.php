@extends('layout.master')

@section('title')
    Data Divisi
@endsection

@section('heading')
    Manajemen Divisi
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Divisi</h4>

                    {{-- Tombol Tambah Data --}}
                    <a href="{{ route('divisions.create') }}" class="btn btn-primary btn-sm mb-3">
                        <i class="mdi mdi-plus"></i> Tambah Divisi Baru
                    </a>

                    {{-- Notifikasi Sukses --}}
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Notifikasi Gagal Hapus (karena relasi) --}}
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Nama Divisi </th>
                                    <th> Dibuat Pada </th>
                                    <th> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Loop data divisi --}}
                                @forelse ($divisions as $key => $division)
                                    <tr>
                                        <td> {{ $key + 1 }} </td>
                                        <td> {{ $division->name }} </td>
                                        <td> {{ $division->created_at->format('d M Y') }} </td>
                                        <td>
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('divisions.edit', $division->id) }}"
                                                class="btn btn-inverse-warning btn-icon">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            {{-- Tombol Hapus (WAJIB pakai form) --}}
                                            <form action="{{ route('divisions.destroy', $division->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus divisi ini? User terkait akan kehilangan divisi.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-icon">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Jika data kosong --}}
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada data divisi.</td>
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
