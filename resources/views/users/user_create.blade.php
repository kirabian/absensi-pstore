@extends('layout.master')

@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('content')
{{-- LOAD CSS LANGSUNG DI SINI (ANTI-CONFLICT) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    /* --- CSS KHUSUS AGAR TAMPILAN SELECT2 RAPI --- */
    .select2-container ul, .select2-container li, .select2-selection__rendered, span.select2-selection__choice {
        list-style: none !important; list-style-type: none !important; padding-left: 0 !important; margin-left: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple {
        background-color: #fff !important; border: 1px solid #ced4da !important; min-height: 38px !important; padding: 4px !important; display: flex !important; flex-wrap: wrap !important; align-items: center !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #e9ecef !important; border: 1px solid #dee2e6 !important; border-radius: 20px !important; padding: 2px 10px !important; margin: 2px 4px !important; font-size: 0.85rem !important; color: #333 !important; display: inline-flex !important; align-items: center !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
        border: none !important; background: transparent !important; margin-right: 5px !important; color: #999 !important; font-weight: bold !important; padding: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove:hover {
        color: #dc3545 !important;
    }
    .select2-results__option { padding: 6px 12px !important; font-size: 14px !important; }
    .select2-container--bootstrap-5 .select2-results__option--selected { background-color: #e9ecef !important; color: #333 !important; }
    .select2-container--bootstrap-5 .select2-results__option--highlighted { background-color: #0d6efd !important; color: #fff !important; }
</style>

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
        <!-- KARTU 1: DATA LOGIN & ROLE -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Login & Role</h4>
                    
                    <div class="form-group mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>ID Login *</label>
                        <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Konfirmasi Password</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Role</label>
                        <select class="form-select" id="role" name="role" onchange="toggleInputs()" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Super Admin</option>
                            <option value="audit">Audit (Multi Cabang)</option>
                            <option value="leader">Leader</option>
                            <option value="security">Security</option>
                            <option value="user_biasa">Karyawan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- KARTU 2: PENEMPATAN -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Penempatan & Kontak</h4>

                    {{-- SINGLE BRANCH (HOMEBASE) --}}
                    {{-- WAJIB UNTUK: Audit, Leader, Security, User Biasa --}}
                    {{-- HANYA ADMIN YANG BOLEH KOSONG --}}
                    <div class="form-group mb-3" id="single-branch-group">
                        <label>Cabang Utama (Homebase)</label>
                        <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang">
                            <option></option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- MULTI BRANCH (WILAYAH AUDIT) --}}
                    {{-- HANYA MUNCUL JIKA ROLE = AUDIT --}}
                    <div class="form-group mb-3 d-none" id="multi-branch-group">
                        <label class="text-primary fw-bold">Akses Wilayah Audit (Multi)</label>
                        <br>
                        <select class="form-select select2-multi" name="multi_branches[]" multiple="multiple" style="width: 100%">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <a href="javascript:void(0)" onclick="selectAll('#multi-branch-group .select2-multi')" class="me-2">Pilih Semua</a>
                            <a href="javascript:void(0)" onclick="clearAll('#multi-branch-group .select2-multi')" class="text-danger">Hapus Semua</a>
                        </div>
                    </div>

                    {{-- DIVISI (MULTI SELECT UNTUK SEMUA ROLE) --}}
                    <div class="form-group mb-3" id="multi-division-group">
                        <label class="text-success fw-bold">Divisi (Multi Select)</label>
                        <br>
                        <select class="form-select select2-multi" name="multi_divisions[]" multiple="multiple" style="width: 100%">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <a href="javascript:void(0)" onclick="selectAll('#multi-division-group .select2-multi')" class="me-2">Pilih Semua</a>
                            <a href="javascript:void(0)" onclick="clearAll('#multi-division-group .select2-multi')" class="text-danger">Hapus Semua</a>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group mb-3">
                        <label>Foto Profil</label>
                        <input type="file" class="form-control" name="profile_photo_path">
                    </div>
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" placeholder="contoh@email.com">
                    </div>
                    <div class="form-group mb-3">
                        <label>WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp" placeholder="08xxx">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary btn-lg me-3">Simpan User</button>
            <a href="{{ route('users.index') }}" class="btn btn-light btn-lg">Batal</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            
            // Init Select2
            $('.select2-single').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Silahkan pilih...", allowClear: true });
            $('.select2-multi').select2({ theme: "bootstrap-5", width: '100%', placeholder: "Pilih satu atau lebih...", closeOnSelect: false, allowClear: true });

            // Helper Functions
            window.selectAll = function(selector) { $(selector).find('option').prop('selected', true); $(selector).trigger('change'); }
            window.clearAll = function(selector) { $(selector).val(null).trigger('change'); }

            // LOGIKA PENTING: TOGGLE INPUT BERDASARKAN ROLE
            window.toggleInputs = function() {
                const role = $('#role').val();

                // 1. SINGLE BRANCH (HOMEBASE)
                // Aturan: Wajib untuk SEMUA ROLE kecuali 'admin' (Super Admin).
                // Jadi jika role = audit, field ini HARUS MUNCUL.
                if (role === 'admin') {
                    $('#single-branch-group').addClass('d-none'); 
                } else {
                    $('#single-branch-group').removeClass('d-none'); 
                }

                // 2. MULTI BRANCH (WILAYAH AUDIT)
                // Aturan: Hanya muncul jika role = 'audit'.
                if (role === 'audit') {
                    $('#multi-branch-group').removeClass('d-none');
                } else {
                    $('#multi-branch-group').addClass('d-none');
                }

                // 3. DIVISI
                // Selalu muncul untuk semua role.
            };

            // Jalankan saat halaman dimuat
            toggleInputs();
        });
    </script>
@endpush