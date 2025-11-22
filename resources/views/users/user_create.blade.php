@extends('layout.master')
@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

{{-- Tambahkan CSS Select2 --}}
@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* --- SELECT2 CORE UI THEME --- */
    
    /* 1. Container Input */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        background-color: #fff;
        border: 1px solid #d8dbe0;
        border-radius: 0.375rem;
        min-height: 46px; /* Tinggi mirip CoreUI */
        padding: 4px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #8b5cf6; /* Warna ungu fokus */
        box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.25);
    }

    /* 2. Badge/Tags (Pilihan Terpilih) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Gradient Ungu */
        border: none;
        border-radius: 50px; /* Bulat */
        color: #fff;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 4px 12px;
        margin-top: 6px;
        margin-left: 6px;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        border-right: 1px solid rgba(255,255,255,0.3);
        margin-right: 8px;
        padding-right: 8px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background: transparent;
        color: #ffcccc;
    }

    /* 3. DROPDOWN MENU (Mirip Gambar CoreUI Dark) */
    .select2-dropdown {
        background-color: #2f353a; /* Dark Background */
        border: 1px solid #2f353a;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        z-index: 9999;
        padding: 0.5rem 0;
    }
    
    /* Search Box di dalam Dropdown */
    .select2-search--dropdown .select2-search__field {
        background-color: #3c4b64; /* Darker input */
        border: 1px solid #51617a;
        color: #fff;
        border-radius: 0.25rem;
        margin: 5px 10px;
        width: calc(100% - 20px);
    }

    /* 4. OPSI DROPDOWN DENGAN CHECKBOX */
    .select2-results__option {
        padding: 8px 15px;
        font-size: 0.95rem;
        color: #ebedef; /* Teks Putih/Abu terang */
        display: flex;
        align-items: center;
        transition: all 0.2s;
    }
    
    /* Custom Checkbox Box (Pseudo Element) */
    .select2-results__option::before {
        content: '';
        display: inline-block;
        width: 18px;
        height: 18px;
        margin-right: 10px;
        border: 2px solid #768192;
        border-radius: 4px;
        background-color: transparent;
        transition: all 0.2s;
    }

    /* Hover State */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3c4b64; /* Hover Dark Blue */
        color: #fff;
    }

    /* Selected State (Checked) */
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: #2f353a; /* Tetap dark */
        color: #fff;
        font-weight: 600;
    }
    
    /* Ubah kotak checkbox jadi tercentang saat dipilih */
    .select2-container--default .select2-results__option[aria-selected="true"]::before {
        background-color: #9da5b1; /* Ungu CoreUI */
        border-color: #9da5b1;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        background-size: 100% 100%;
        background-position: center;
    }
    
    /* Fokus pada highlight (saat hover), checkbox jadi ungu */
    .select2-container--default .select2-results__option--highlighted[aria-selected]::before {
        border-color: #a78bfa;
    }

    /* Placeholder text */
    .select2-container--default .select2-search--inline .select2-search__field::placeholder {
        color: #6c757d;
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
                    <h4 class="card-title">🔐 Data Login & Role</h4>
                    
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>ID Login *</label>
                        <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" placeholder="Username untuk login" required>
                        <small class="text-muted">Digunakan untuk login ke sistem.</small>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Minimal 8 karakter" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" class="form-control" name="password_confirmation" placeholder="Ketik ulang password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" id="role" name="role" onchange="toggleInputs()" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👑 Super Admin</option>
                            <option value="audit" {{ old('role') == 'audit' ? 'selected' : '' }}>🔍 Audit (Multi Cabang)</option>
                            <option value="leader" {{ old('role') == 'leader' ? 'selected' : '' }}>⭐ Leader (Multi Divisi)</option>
                            <option value="security" {{ old('role') == 'security' ? 'selected' : '' }}>🛡️ Security</option>
                            <option value="user_biasa" {{ old('role') == 'user_biasa' ? 'selected' : '' }}>👤 Karyawan</option>
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

                    {{-- INPUT CABANG (SINGLE & MULTI) --}}
                    <div class="form-group" id="single-branch-group">
                        <label>Cabang Utama (Homebase)</label>
                        <select class="form-control select2-single" name="branch_id" style="width:100%">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-none" id="multi-branch-group">
                        <label class="text-primary fw-bold">
                            🏢 Akses Wilayah Audit <span class="badge badge-primary">Multi Select</span>
                        </label>
                        <select class="form-control select2-multi" name="multi_branches[]" multiple="multiple" style="width:100%">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" 
                                    {{ (collect(old('multi_branches'))->contains($branch->id)) ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block">Klik opsi untuk mencentang cabang.</small>
                    </div>

                    {{-- INPUT DIVISI (SINGLE & MULTI) --}}
                    <div class="form-group" id="single-division-group">
                        <label>Divisi Utama</label>
                        <select class="form-control select2-single" name="division_id" style="width:100%">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-none" id="multi-division-group">
                        <label class="text-success fw-bold">
                            📋 Pimpin Divisi <span class="badge badge-success">Multi Select</span>
                        </label>
                        <select class="form-control select2-multi" name="multi_divisions[]" multiple="multiple" style="width:100%">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ (collect(old('multi_divisions'))->contains($division->id)) ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted mt-1 d-block">Klik opsi untuk mencentang divisi.</small>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label>📸 Foto Profil</label>
                        <input type="file" class="form-control" name="profile_photo_path" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                    </div>

                    <div class="form-group">
                        <label>📧 Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="contoh@email.com">
                    </div>
                    <div class="form-group">
                        <label>💬 WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>📷 Instagram</label>
                                <input type="text" class="form-control" name="instagram" value="{{ old('instagram') }}" placeholder="@username">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>🎵 TikTok</label>
                                <input type="text" class="form-control" name="tiktok" value="{{ old('tiktok') }}" placeholder="@username">
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
        // Init Select2 Single
        $('.select2-single').select2({
            placeholder: "-- Pilih --",
            allowClear: true,
            width: '100%'
        });
        
        // Init Select2 Multi (Dengan Checkbox Style)
        $('.select2-multi').select2({
            placeholder: "Pilih opsi...",
            allowClear: true,
            width: '100%',
            closeOnSelect: false, // PENTING: Agar dropdown tidak nutup pas klik checkbox
            dropdownCssClass: "select2-dark-theme" // Class penanda untuk CSS
        });
        
        toggleInputs();
    });

    function toggleInputs() {
        var role = document.getElementById('role').value;
        
        // Helper untuk animasi smooth
        function switchField(showId, hideId) {
            $(hideId).addClass('d-none');
            // Reset value jika di-hide (opsional, agar tidak kirim sampah)
            // $(hideId).find('select').val(null).trigger('change'); 
            $(showId).removeClass('d-none');
        }

        // Default: Semua Single ON, Multi OFF
        var showMultiBranch = false;
        var showMultiDivision = false;

        if (role === 'audit') {
            showMultiBranch = true;
        } else if (role === 'leader') {
            showMultiDivision = true;
        }

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