@extends('layout.master')
@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple {
        min-height: 42px;
        border-radius: 6px;
        border: 1px solid #d0d5dd;
        padding: 6px;
    }
    .select2-selection__choice {
        background: #4f46e5 !important;
        color: white !important;
        border: none !important;
        border-radius: 4px !important;
        padding: 2px 8px !important;
        font-size: 13px;
    }
    .select2-selection__choice__remove {
        color: #fff !important;
        margin-right: 6px;
    }
</style>
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

<form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row">

    {{-- CARD 1 --}}
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">🔐 Data Login & Role</h5>

                <div class="form-group mb-2">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group mb-2">
                    <label>ID Login</label>
                    <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" required>
                </div>

                <div class="form-group mb-2">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <div class="form-group mb-3">
                    <label>Konfirmasi Password</label>
                    <input type="password" class="form-control" name="password_confirmation" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select class="form-control" id="role" name="role" onchange="toggleInputs()" required>
                        <option value="">Pilih Role</option>
                        <option value="admin">Super Admin</option>
                        <option value="audit">Audit (Multi Cabang)</option>
                        <option value="leader">Leader (Multi Divisi)</option>
                        <option value="security">Security</option>
                        <option value="user_biasa">Karyawan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2 --}}
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">📍 Penempatan & Kontak</h5>

                {{-- SINGLE CABANG --}}
                <div id="single-branch-group" class="form-group mb-2">
                    <label>Cabang Utama</label>
                    <select class="form-control select2" name="branch_id">
                        <option value="">Pilih Cabang</option>
                        @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- MULTI CABANG --}}
                <div id="multi-branch-group" class="form-group mb-2 d-none">
                    <label>Akses Wilayah Audit (Multi)</label>
                    <select class="form-control select2" name="multi_branches[]" multiple>
                        @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- SINGLE DIVISI --}}
                <div id="single-division-group" class="form-group mb-2">
                    <label>Divisi Utama</label>
                    <select class="form-control select2" name="division_id">
                        <option value="">Pilih Divisi</option>
                        @foreach ($divisions as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- MULTI DIVISI --}}
                <div id="multi-division-group" class="form-group mb-2 d-none">
                    <label>Pimpin Divisi (Multi)</label>
                    <select class="form-control select2" name="multi_divisions[]" multiple>
                        @foreach ($divisions as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label>Foto Profil</label>
                    <input type="file" name="profile_photo_path" class="form-control">
                </div>

                <div class="form-group mt-2">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>

                <div class="form-group mt-2">
                    <label>WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}">
                </div>
            </div>
        </div>
    </div>

</div>

<div class="text-center mt-3">
    <button class="btn btn-primary">💾 Simpan User</button>
    <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
</div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() { $('.select2').select2(); toggleInputs(); });
    function toggleInputs() {
        let r = $('#role').val();
        $('#single-branch-group').toggleClass('d-none', r === 'audit');
        $('#multi-branch-group').toggleClass('d-none', r !== 'audit');
        $('#single-division-group').toggleClass('d-none', r === 'leader');
        $('#multi-division-group').toggleClass('d-none', r !== 'leader');
    }
</script>
@endpush
