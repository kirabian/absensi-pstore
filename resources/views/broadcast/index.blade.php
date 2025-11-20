@extends('layout.master')

@section('title', 'Broadcast')
@section('heading', 'Manajemen Broadcast')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Broadcast</h5>
                @can('create', App\Models\Broadcast::class)
                <a href="{{ route('broadcast.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-2"></i>Buat Broadcast
                </a>
                @endcan
            </div>
            <div class="card-body">
                @if($broadcasts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Pesan</th>
                                    <th>Prioritas</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($broadcasts as $broadcast)
                                <tr>
                                    <td>{{ $broadcast->title }}</td>
                                    <td>{{ Str::limit($broadcast->message, 50) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $broadcast->priority == 'high' ? 'danger' : ($broadcast->priority == 'medium' ? 'warning' : 'secondary') }}">
                                            {{ $broadcast->priority }}
                                        </span>
                                    </td>
                                    <td>{{ $broadcast->creator->name ?? 'Unknown' }}</td>
                                    <td>{{ $broadcast->published_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('broadcast.show', $broadcast) }}" class="btn btn-sm btn-info">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            @can('update', $broadcast)
                                            <a href="{{ route('broadcast.edit', $broadcast) }}" class="btn btn-sm btn-warning">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            @endcan
                                            @can('delete', $broadcast)
                                            <form action="{{ route('broadcast.destroy', $broadcast) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus broadcast?')">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $broadcasts->links() }}
                @else
                    <div class="text-center py-5">
                        <i class="mdi mdi-bullhorn-outline display-4 text-muted"></i>
                        <h4 class="text-muted mt-3">Belum ada broadcast</h4>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection