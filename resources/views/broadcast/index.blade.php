@extends('layout.master')

@section('title')
    Daftar Broadcast
@endsection

@section('heading')
    Manajemen Broadcast
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    @if (auth()->user()->role == 'admin')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Daftar Broadcast</h4>
                            <a href="{{ route('broadcast.create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus"></i> Buat Broadcast Baru
                            </a>
                        </div>
                    @else
                        <h4 class="card-title mb-4">Daftar Pengumuman</h4>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Judul</th>
                                    <th>Prioritas</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($broadcasts as $key => $broadcast)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $broadcast->title }}</td>
                                        <td>
                                            <span
                                                class="badge 
                                                @if ($broadcast->priority == 'high') badge-danger
                                                @elseif($broadcast->priority == 'medium') badge-warning
                                                @else badge-info @endif">
                                                {{ ucfirst($broadcast->priority) }}
                                            </span>
                                        </td>
                                        <td>{{ $broadcast->creator->name ?? 'User Tidak Ditemukan' }}</td>
                                        <td>{{ $broadcast->published_at->format('d M Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('broadcast.show', $broadcast->id) }}"
                                                class="btn btn-inverse-info btn-icon">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            @if (auth()->user()->role == 'admin')
                                                <form action="{{ route('broadcast.destroy', $broadcast->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus broadcast ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-inverse-danger btn-icon">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada broadcast.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $broadcasts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
