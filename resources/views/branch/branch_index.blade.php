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
                    <h4 class="card-title">Daftar Semua Cabang PStore</h4>
                    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm mb-3">
                        <i class="mdi mdi-plus"></i> Tambah Cabang Baru
                    </a>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
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
                                    <th> Nama Cabang </th>
                                    <th> Alamat </th>
                                    <th> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $key => $branch)
                                    <tr>
                                        <td> {{ $key + 1 }} </td>
                                        <td> {{ $branch->name }} </td>
                                        <td> {{ $branch->address ?? 'N/A' }} </td>
                                        <td>
                                            <a href="{{ route('branches.edit', $branch->id) }}"
                                                class="btn btn-inverse-warning btn-icon">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            <form action="{{ route('branches.destroy', $branch->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus cabang ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-inverse-danger btn-icon">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada data cabang.</td>
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
