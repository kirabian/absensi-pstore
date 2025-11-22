@extends('layout.master')
@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('styles')
{{-- 1. CSS Select2 Standard --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
{{-- 2. CSS Select2 Bootstrap 5 Theme --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="forms-sample" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- KARTU 1: LOGIN & ROLE --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">🔐 Data Login & Role</h4>
                    
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>ID Login *</label>
                        <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" placeholder="Username untuk login" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        {{-- Gunakan class 'form-select' untuk Bootstrap 5 --}}
                        <select class="form-select" id="role" name="role" onchange="toggleInputs()" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">👑 Super Admin</option>
                            <option value="audit">🔍 Audit (Multi Cabang)</option>
                            <option value="leader">⭐ Leader (Multi Divisi)</option>
                            <option value="security">🛡️ Security</option>
                            <option value="user_biasa">👤 Karyawan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: PENEMPATAN & KONTAK --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">📍 Penempatan & Kontak</h4>

                    {{-- BRANCH SELECTS --}}
                    <div class="form-group" id="single-branch-group">
                        <label>Cabang Utama (Homebase)</label>
                        <select class="form-select select2" name="branch_id" data-placeholder="Pilih Cabang Homebase">
                            <option></option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-none" id="multi-branch-group">
                        <label class="text-primary fw-bold">Akses Wilayah Audit (Multi)</label>
                        {{-- Tambahkan attribute multiple --}}
                        <select class="form-select select2" name="multi_branches[]" multiple data-placeholder="Pilih Wilayah Audit">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DIVISION SELECTS --}}
                    <div class="form-group" id="single-division-group">
                        <label>Divisi Utama</label>
                        <select class="form-select select2" name="division_id" data-placeholder="Pilih Divisi">
                            <option></option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-none" id="multi-division-group">
                        <label class="text-success fw-bold">Pimpin Divisi (Multi)</label>
                        <select class="form-select select2" name="multi_divisions[]" multiple data-placeholder="Pilih Divisi yang Dipimpin">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label>📸 Foto Profil</label>
                        <input type="file" class="form-control" name="profile_photo_path" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>📧 Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label>💬 WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>📷 Instagram</label>
                                <input type="text" class="form-control" name="instagram" value="{{ old('instagram') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>🎵 TikTok</label>
                                <input type="text" class="form-control" name="tiktok" value="{{ old('tiktok') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary me-2">💾 Simpan User</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">↩️ Batal</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Penerapan Kode Select2 Bootstrap 5 Anda
        $('.select2').each(function() {
            $(this).select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                // closeOnSelect: false, // Gunakan ini jika ingin dropdown tetap terbuka selamanya
                // ATAU Logika Cerdas: Tutup jika single select, Tetap buka jika multi select
                closeOnSelect: !$(this).attr('multiple'), 
            });
        });

        toggleInputs();
    });

    function toggleInputs() {
        var role = document.getElementById('role').value;
        
        // Helper untuk switch tampilan
        function switchField(showId, hideId) {
            $(hideId).addClass('d-none');
            $(showId).removeClass('d-none');
        }

        var showMultiBranch = (role === 'audit');
        var showMultiDivision = (role === 'leader');

        if (showMultiBranch) {
            switchField('#multi-branch-group', '#single-branch-group');
        } else {
            switchField('#single-branch-group', '#multi-branch-group');
        }

        if (showMultiDivision) {
            switchField('#multi-division-group', '#single-division-group');
        } else {
            switchField('#single-division-group', '#multi-division-group');
        }
    }
</script>
@endpush