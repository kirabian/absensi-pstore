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
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mb-3">
                        <i class="mdi mdi-plus"></i> Tambah User Baru
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
                                    <th> Nama </th>
                                    <th> Email </th>
                                    <th> Role </th>
                                    <th> Divisi </th>
                                    <th> QR Code </th> {{-- <-- KOLOM BARU --}} <th> Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $key => $user)
                                    <tr>
                                        <td> {{ $key + 1 }} </td>
                                        <td> {{ $user->name }} </td>
                                        <td> {{ $user->email }} </td>
                                        <td>
                                            @if($user->role == 'admin')
                                                <span class="badge badge-primary">Admin</span>
                                            @elseif($user->role == 'audit')
                                                <span class="badge badge-info">Audit</span>
                                            @elseif($user->role == 'security')
                                                <span class="badge badge-warning">Security</span>
                                            @else
                                                <span class="badge badge-secondary">User Biasa</span>
                                            @endif
                                        </td>
                                        <td> {{ $user->division->name ?? 'N/A' }} </td>

                                        {{-- =================================== --}}
                                        {{-- TOMBOL QR BARU --}}
                                        {{-- =================================== --}}
                                        <td>
                                            {{-- Pastikan user punya QR value sebelum tampilkan tombol --}}
                                            @if($user->qr_code_value)
                                                <button type="button" class="btn btn-inverse-info btn-icon" data-bs-toggle="modal"
                                                    data-bs-target="#qrModal" data-name="{{ $user->name }}"
                                                    data-qr="{{ $user->qr_code_value }}">
                                                    <i class="mdi mdi-qrcode"></i>
                                                </button>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        {{-- =================================== --}}

                                        <td>
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="btn btn-inverse-warning btn-icon">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>

                                            @if ($user->id != auth()->id())
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
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
                                        <td colspan="7" class="text-center">Belum ada data user.</td> {{-- <-- Ubah colspan jadi
                                            7 --}} </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- =================================== --}}
    {{-- MODAL HTML BARU --}}
    {{-- =================================== --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code untuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    {{-- Konten QR akan digambar oleh JavaScript di sini --}}
                    <div id="qrcode-container" class="d-flex justify-content-center"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection


{{-- =================================== --}}
{{-- SCRIPT BARU (WAJIB) --}}
{{-- =================================== --}}
@push('scripts')
    {{-- 1. Import library untuk generate QR Code --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        // 2. Tangkap event saat modal akan ditampilkan
        var qrModal = document.getElementById('qrModal');
        qrModal.addEventListener('show.bs.modal', function (event) {

            // Tombol yang di-klik
            var button = event.relatedTarget;

            // Ambil data dari atribut 'data-*' di tombol
            var name = button.getAttribute('data-name');
            var qrValue = button.getAttribute('data-qr');

            // Update judul modal
            var modalTitle = qrModal.querySelector('.modal-title');
            modalTitle.textContent = 'QR Code Absensi untuk ' + name;

            // Cari container QR Code di dalam modal
            var qrContainer = document.getElementById('qrcode-container');

            // Hapus QR code lama (jika ada)
            qrContainer.innerHTML = '';

            // Buat QR code baru
            new QRCode(qrContainer, {
                text: qrValue,
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
@endpush
