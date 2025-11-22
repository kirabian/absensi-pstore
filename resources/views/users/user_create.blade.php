@extends('layout.master')
@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

{{-- Tambahkan CSS Select2 --}}
@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 46px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 46px; }
    /* Style untuk multi select */
    .select2-container--default .select2-selection--multiple .select2-selection__choice { 
        background-color: #4B49AC; border: none; color: white; font-size: 12px;
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

<form class="forms-sample" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- KARTU 1: LOGIN & ROLE --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Login & Role</h4>
                    
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label>ID Login *</label>
                        <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" required>
                        <small class="text-muted">Digunakan untuk login.</small>
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
                        <select class="form-control" id="role" name="role" onchange="toggleInputs()" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="audit" {{ old('role') == 'audit' ? 'selected' : '' }}>Audit (Multi Cabang)</option>
                            <option value="leader" {{ old('role') == 'leader' ? 'selected' : '' }}>Leader (Multi Divisi)</option>
                            <option value="security" {{ old('role') == 'security' ? 'selected' : '' }}>Security</option>
                            <option value="user_biasa" {{ old('role') == 'user_biasa' ? 'selected' : '' }}>Karyawan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: PENEMPATAN & KONTAK --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Penempatan & Kontak</h4>

                    {{-- INPUT CABANG (Single vs Multi) --}}
                    <div class="form-group" id="single-branch-group">
                        <label>Cabang Utama (Homebase)</label>
                        <select class="form-control select2" name="branch_id" style="width:100%">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-none" id="multi-branch-group">
                        <label class="text-primary fw-bold">Akses Wilayah Audit (Multi Select)</label>
                        <select class="form-control select2" name="multi_branches[]" multiple="multiple" style="width:100%">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" 
                                    {{ (collect(old('multi_branches'))->contains($branch->id)) ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- INPUT DIVISI (Single vs Multi) --}}
                    <div class="form-group" id="single-division-group">
                        <label>Divisi Utama</label>
                        <select class="form-control select2" name="division_id" style="width:100%">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-none" id="multi-division-group">
                        <label class="text-success fw-bold">Pimpin Divisi (Multi Select)</label>
                        <select class="form-control select2" name="multi_divisions[]" multiple="multiple" style="width:100%">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ (collect(old('multi_divisions'))->contains($division->id)) ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label>Foto Profil</label>
                        <input type="file" class="form-control" name="profile_photo_path">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp') }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Instagram</label>
                                <input type="text" class="form-control" name="instagram" value="{{ old('instagram') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>TikTok</label>
                                <input type="text" class="form-control" name="tiktok" value="{{ old('tiktok') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-2">Simpan User</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2(); // Init Select2
        toggleInputs(); // Run on load in case of old input
    });

    function toggleInputs() {
        var role = document.getElementById('role').value;
        
        // Reset Visibility
        $('#single-branch-group').removeClass('d-none');
        $('#multi-branch-group').addClass('d-none');
        $('#single-division-group').removeClass('d-none');
        $('#multi-division-group').addClass('d-none');

        if (role === 'audit') {
            // Audit: Multi Branch, Hide Single Branch
            $('#single-branch-group').addClass('d-none');
            $('#multi-branch-group').removeClass('d-none');
        } 
        else if (role === 'leader') {
            // Leader: Multi Division, Hide Single Division
            $('#single-division-group').addClass('d-none');
            $('#multi-division-group').removeClass('d-none');
        }
    }
</script>
@endpush