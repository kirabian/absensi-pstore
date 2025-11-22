@extends('layout.master')
@section('title', 'Edit User')
@section('heading', 'Edit User: ' . $user->name)

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single { height: 46px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 46px; }
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

    <form class="forms-sample" action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Cek Hak Akses Edit Struktur --}}
        @php
            $isSuperAdmin = Auth::user()->role == 'admin';
            // Jika bukan admin, input struktur akan disabled (hanya visual, backend tetap protect)
            $disabledAttr = $isSuperAdmin ? '' : 'disabled';
        @endphp

        <div class="row">
            {{-- KARTU 1: DATA LOGIN --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Data Login & Role</h4>

                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>ID Login</label>
                            <input type="text" class="form-control" name="login_id" value="{{ old('login_id', $user->login_id) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select class="form-control" id="role" name="role" onchange="toggleInputs()" required {{ $disabledAttr }}>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="audit" {{ old('role', $user->role) == 'audit' ? 'selected' : '' }}>Audit</option>
                                <option value="leader" {{ old('role', $user->role) == 'leader' ? 'selected' : '' }}>Leader</option>
                                <option value="security" {{ old('role', $user->role) == 'security' ? 'selected' : '' }}>Security</option>
                                <option value="user_biasa" {{ old('role', $user->role) == 'user_biasa' ? 'selected' : '' }}>Karyawan</option>
                            </select>
                            @if(!$isSuperAdmin)
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <small class="text-danger">Hanya Super Admin yang bisa mengubah Role.</small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" class="form-control" name="password" placeholder="Isi jika ingin ganti password">
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation">
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: PENEMPATAN --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Penempatan & Kontak</h4>

                        {{-- SINGLE BRANCH --}}
                        <div class="form-group" id="single-branch-group">
                            <label>Cabang Utama (Homebase)</label>
                            <select class="form-control select2" name="branch_id" style="width:100%" {{ $disabledAttr }}>
                                <option value="">-- Pilih Cabang --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- MULTI BRANCH (AUDIT) --}}
                        <div class="form-group d-none" id="multi-branch-group">
                            <label class="text-primary fw-bold">Akses Wilayah Audit</label>
                            <select class="form-control select2" name="multi_branches[]" multiple="multiple" style="width:100%" {{ $disabledAttr }}>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" 
                                        {{ $user->branches->contains($branch->id) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SINGLE DIVISION --}}
                        <div class="form-group" id="single-division-group">
                            <label>Divisi Utama</label>
                            <select class="form-control select2" name="division_id" style="width:100%" {{ $disabledAttr }}>
                                <option value="">-- Pilih Divisi --</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id', $user->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- MULTI DIVISION (LEADER) --}}
                        <div class="form-group d-none" id="multi-division-group">
                            <label class="text-success fw-bold">Pimpin Divisi</label>
                            <select class="form-control select2" name="multi_divisions[]" multiple="multiple" style="width:100%" {{ $disabledAttr }}>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}" 
                                        {{ $user->divisions->contains($division->id) ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        @if(!$isSuperAdmin)
                            <small class="text-danger d-block mb-3">Hubungi Super Admin untuk pindah Cabang/Divisi.</small>
                            {{-- Kirim hidden value jika disabled agar data tidak hilang saat update profil lain --}}
                            @if($user->branch_id) <input type="hidden" name="branch_id" value="{{ $user->branch_id }}"> @endif
                            @if($user->division_id) <input type="hidden" name="division_id" value="{{ $user->division_id }}"> @endif
                        @endif

                        <hr>
                        <div class="form-group">
                            <label>Ganti Foto Profil</label>
                            <input type="file" class="form-control" name="profile_photo_path">
                            @if($user->profile_photo_path)
                                <small class="text-muted">Foto saat ini tersedia.</small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                        </div>
                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Instagram</label>
                                    <input type="text" class="form-control" name="instagram" value="{{ old('instagram', $user->instagram) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>TikTok</label>
                                    <input type="text" class="form-control" name="tiktok" value="{{ old('tiktok', $user->tiktok) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Masuk</label>
                            <input type="date" class="form-control" name="hire_date" 
                                value="{{ old('hire_date', $user->hire_date ? $user->hire_date->format('Y-m-d') : '') }}">
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary me-2">Update User</button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        toggleInputs(); // Jalankan saat load untuk menyesuaikan tampilan dengan Role saat ini
    });

    function toggleInputs() {
        var role = document.getElementById('role').value;
        
        // Reset Default (Single Show, Multi Hide)
        $('#single-branch-group').removeClass('d-none');
        $('#multi-branch-group').addClass('d-none');
        $('#single-division-group').removeClass('d-none');
        $('#multi-division-group').addClass('d-none');

        if (role === 'audit') {
            $('#single-branch-group').addClass('d-none');
            $('#multi-branch-group').removeClass('d-none');
        } 
        else if (role === 'leader') {
            $('#single-division-group').addClass('d-none');
            $('#multi-division-group').removeClass('d-none');
        }
    }
</script>
@endpush