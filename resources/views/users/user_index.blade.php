@extends('layout.master')

@section('title')
    Data User
@endsection

@section('heading')
    Manajemen User
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Daftar Semua User</h4>

                    {{-- CONTAINER: TOMBOL TAMBAH & SEARCH FORM --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        
                        {{-- Tombol Tambah --}}
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah User Baru
                        </a>

                        {{-- Form Pencarian --}}
                        <form action="{{ route('users.index') }}" method="GET" class="d-flex">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Cari Nama / ID / Email..." 
                                       value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                                {{-- Tombol Reset (Hanya muncul jika ada search) --}}
                                @if(request('search'))
                                    <a href="{{ route('users.index') }}" class="btn btn-secondary" title="Reset Pencarian">
                                        <i class="mdi mdi-refresh"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    {{-- AKHIR CONTAINER --}}

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
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th> # </th>
                                    <th> Profil Pengguna </th>
                                    <th> Kontak </th>
                                    <th> Role </th>
                                    <th> Penempatan </th>
                                    <th> Tanggal Join </th>
                                    <th> QR Code </th>
                                    <th> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $key => $user)
                                    <tr>
                                        <td> {{ $users->firstItem() + $key }} </td>

                                        {{-- PROFIL --}}
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    @if ($user->profile_photo_path)
                                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                                            alt="profile" class="img-sm rounded-circle"
                                                            style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                                                            alt="profile" class="img-sm rounded-circle">
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $user->name }}</div>
                                                    <small class="text-muted">ID: {{ $user->login_id ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- KONTAK --}}
                                        <td>
                                            <div><i class="mdi mdi-email-outline me-1"></i> {{ $user->email }}</div>
                                            @if ($user->whatsapp)
                                                <div class="text-success mt-1">
                                                    <i class="mdi mdi-whatsapp me-1"></i> {{ $user->whatsapp }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- ROLE --}}
                                        <td>
                                            @if ($user->role == 'admin' && $user->branch_id == null)
                                                <span class="badge badge-danger">Super Admin</span>
                                            @elseif($user->role == 'admin' && $user->branch_id != null)
                                                <span class="badge badge-primary">Admin Cabang</span>
                                            @elseif($user->role == 'audit')
                                                <span class="badge badge-info">Audit</span>
                                            @elseif($user->role == 'leader')
                                                <span class="badge badge-success">Leader</span>
                                            @elseif($user->role == 'security')
                                                <span class="badge badge-warning">Security</span>
                                            @else
                                                <span class="badge badge-secondary">User Biasa</span>
                                            @endif
                                        </td>

                                        {{-- PENEMPATAN --}}
                                        <td>
                                            @if ($user->role == 'audit')
                                                <div class="fw-bold text-primary">Audit Wilayah:</div>
                                                <small class="text-muted" style="white-space: normal;">
                                                    {{ $user->branches->pluck('name')->join(', ') ?: 'Belum ada wilayah' }}
                                                </small>
                                            @elseif($user->role == 'leader')
                                                <div class="fw-bold">{{ $user->branch->name ?? 'N/A' }}</div>
                                                <div class="text-success text-small fw-bold mt-1">Memimpin Divisi:</div>
                                                <small class="text-muted" style="white-space: normal;">
                                                    {{ $user->divisions->pluck('name')->join(', ') ?: 'Belum ada divisi' }}
                                                </small>
                                            @else
                                                <div class="fw-bold">{{ $user->branch->name ?? 'Semua Cabang' }}</div>
                                                <small
                                                    class="text-muted">{{ $user->division->name ?? 'Tanpa Divisi' }}</small>
                                            @endif
                                        </td>

                                        {{-- TANGGAL JOIN --}}
                                        <td>
                                            {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '-' }}
                                        </td>

                                        {{-- QR CODE --}}
                                        <td>
                                            @if ($user->qr_code_value)
                                                <button type="button" class="btn btn-inverse-dark btn-icon btn-sm"
                                                    data-bs-toggle="modal" data-bs-target="#qrModal"
                                                    data-name="{{ $user->name }}" data-qr="{{ $user->qr_code_value }}">
                                                    <i class="mdi mdi-qrcode"></i>
                                                </button>
                                            @else
                                                <span class="text-muted text-small">N/A</span>
                                            @endif
                                        </td>

                                        {{-- AKSI --}}
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="btn btn-inverse-info btn-icon btn-sm" title="Lihat Detail">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="btn btn-inverse-warning btn-icon btn-sm" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            @if ($user->id != auth()->id())
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm"
                                                        title="Hapus">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">Tidak ada data user yang ditemukan.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL QR Code --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode-container" class="d-flex justify-content-center my-3"></div>
                    <p class="text-muted small mt-2">Scan QR ini untuk absensi</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        var qrModal = document.getElementById('qrModal');
        qrModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var name = button.getAttribute('data-name');
            var qrValue = button.getAttribute('data-qr');
            var modalTitle = qrModal.querySelector('.modal-title');
            modalTitle.textContent = 'QR Code: ' + name;
            var qrContainer = document.getElementById('qrcode-container');
            qrContainer.innerHTML = '';

            if (qrValue) {
                new QRCode(qrContainer, {
                    text: qrValue,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            } else {
                qrContainer.innerHTML = '<span class="text-danger">Value QR Code tidak ditemukan</span>';
            }
        });
    </script>
@endpush