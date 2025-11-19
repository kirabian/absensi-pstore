@extends('layout.master')

@section('title')
    Management Jam Kerja
@endsection

@section('heading')
    Management Jam Kerja
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Daftar Jam Kerja</h4>
                    @can('create', App\Models\WorkSchedule::class)
                    <a href="{{ route('work-schedules.create') }}" class="btn btn-primary">
                        <i class="mdi mdi-plus me-2"></i>Tambah Jam Kerja
                    </a>
                    @endcan
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="mdi mdi-check-circle-outline me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Schedule</th>
                                <th>Cabang</th>
                                <th>Divisi</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $schedule->schedule_name }}</strong>
                                    @if($schedule->is_default)
                                        <span class="badge bg-success ms-1">Default</span>
                                    @endif
                                </td>
                                <td>{{ $schedule->branch->name ?? 'Semua Cabang' }}</td>
                                <td>{{ $schedule->division->name ?? 'Semua Divisi' }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $schedule->check_in_start->format('H:i') }} - {{ $schedule->check_in_end->format('H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        {{ $schedule->check_out_start->format('H:i') }} - {{ $schedule->check_out_end->format('H:i') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $schedule->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $schedule->is_default ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $schedule->is_default ? 'Ya' : 'Tidak' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @can('update', $schedule)
                                        <a href="{{ route('work-schedules.edit', $schedule) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        @endcan
                                        
                                        @can('delete', $schedule)
                                        @if(!$schedule->is_default)
                                        <form action="{{ route('work-schedules.destroy', $schedule) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Hapus jam kerja ini?')">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan

                                        @can('update', $schedule)
                                        <form action="{{ route('work-schedules.toggle-status', $schedule) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $schedule->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                <i class="mdi mdi-{{ $schedule->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="mdi mdi-clock-alert-outline display-4 text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada jam kerja yang ditambahkan</h5>
                                    <p class="text-muted">Silakan tambah jam kerja terlebih dahulu</p>
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
@endsection