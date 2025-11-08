@extends('layout.master')

@section('title')
    Profil Saya
@endsection

@section('heading')
    Profil Saya
@endsection

@section('content')

    {{-- =================================== --}}
    {{-- KARTU BARU UNTUK TAMPILKAN QR --}}
    {{-- =================================== --}}
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="card-title">QR Code Absensi Anda</h4>
                    <p class="card-description">
                        Tunjukkan QR Code ini ke petugas Security untuk absen.
                    </p>

                    @if ($user->qr_code_value)
                        {{-- 1. Tempat untuk menggambar QR Code (kecil) --}}
                        <div id="qrcode-display" class="d-flex justify-content-center mb-3"></div>

                        {{-- 2. Tombol untuk membuka modal (popup) --}}
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#qrModal">
                            Tampilkan Ukuran Penuh
                        </button>
                    @else
                        <div class="alert alert-warning">
                            QR Code Anda belum dibuat. Hubungi Admin.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- =================================== --}}
    {{-- AKHIR KARTU BARU --}}
    {{-- =================================== --}}


    {{-- Ini adalah form edit profil yang sudah ada --}}
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Edit Profil</h4>
                    <p class="card-description">
                        Anda dapat mengubah Nama, Email, dan Password Anda.
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $user->email) }}" required>
                        </div>

                        {{-- Tambahan: Tampilkan Role & Divisi (read-only) --}}
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" class="form-control" value="{{ $user->role }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Divisi</label>
                            <input type="text" class="form-control" value="{{ $user->division->name ?? 'N/A' }}" readonly>
                        </div>
                        {{-- Akhir Tambahan --}}

                        <div class="form-group">
                            <label for="password">Password Baru</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation">
                        </div>

                        <button type="submit" class="btn btn-primary me-2">Update Profil</button>
                        <a href="/" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- =================================== --}}
    {{-- MODAL HTML (POPUP) --}}
    {{-- =================================== --}}
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">QR Code Absensi: {{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    {{-- Konten QR besar akan digambar di sini --}}
                    <div id="qrcode-container-modal" class="d-flex justify-content-center"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection


{{-- =================================== --}}
{{-- SCRIPT JAVASCRIPT --}}
{{-- =================================== --}}
@push('scripts')
    {{-- Import library QR Code --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        // Ambil data QR dari PHP (pastikan user punya QR)
        @if ($user->qr_code_value)
            const qrValue = "{{ $user->qr_code_value }}";

            // 1. Gambar QR Code kecil di halaman
            new QRCode(document.getElementById("qrcode-display"), {
                text: qrValue,
                width: 128,
                height: 128,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            // 2. Gambar QR Code besar saat modal dibuka
            var qrModal = document.getElementById('qrModal');
            qrModal.addEventListener('show.bs.modal', function (event) {
                var qrContainer = document.getElementById('qrcode-container-modal');
                qrContainer.innerHTML = ''; // Kosongkan dulu

                new QRCode(qrContainer, {
                    text: qrValue,
                    width: 400, // Ukuran lebih besar untuk modal
                    height: 400,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            });
        @endif
    </script>
@endpush
