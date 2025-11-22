{{-- resources/views/users/create.blade.php --}}
@extends('layout.master')

@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('content')
{{-- LOAD CSS LANGSUNG DI SINI SUPAYA TIDAK KENA TIMPA MASTER LAYOUT --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    /* --- CSS ANTI-CONFLICT (SAMA SEPERTI SEBELUMNYA) --- */
    .select2-container ul, 
    .select2-container li, 
    .select2-selection__rendered,
    span.select2-selection__choice {
        list-style: none !important;
        list-style-type: none !important;
        padding-left: 0 !important;
        margin-left: 0 !important;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple {
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        min-height: 38px !important;
        padding: 4px !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
    }

    /* Desain Tag/Pills */
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #e9ecef !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 20px !important;
        padding: 2px 10px !important;
        margin: 2px 4px !important;
        font-size: 0.85rem !important;
        color: #333 !important;
        display: inline-flex !important;
        align-items: center !important;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
        border: none !important;
        background: transparent !important;
        margin-right: 5px !important;
        color: #999 !important;
        font-weight: bold !important;
        padding: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove:hover {
        color: #dc3545 !important;
    }

    /* Dropdown items */
    .select2-results__option {
        padding: 6px 12px !important;
        font-size: 14px !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--selected {
        background-color: #e9ecef !important;
        color: #333 !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
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

        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Penempatan & Kontak</h4>

                    {{-- LOGIKA CABANG: Masih dipisah (Audit Multi, Lainnya Single) --}}
                    <div class="form-group mb-3" id="single-branch-group">
                        <label>Cabang Utama</label>
                        <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang">
                            <option></option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

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

                    {{-- LOGIKA DIVISI: SEMUA ROLE PAKAI MULTI SELECT --}}
                    {{-- Input Single Division dihapus karena semua role bisa multi --}}
                    
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
            
            // Inisialisasi Single Select (Untuk Branch Biasa)
            $('.select2-single').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Silahkan pilih...",
                allowClear: true
            });

            // Inisialisasi Multi Select (Untuk Branch Audit & SEMUA DIVISI)
            $('.select2-multi').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: "Pilih satu atau lebih...",
                closeOnSelect: false,
                allowClear: true
            });

            // Helper Functions
            window.selectAll = function(selector) {
                $(selector).find('option').prop('selected', true);
                $(selector).trigger('change');
            }

            window.clearAll = function(selector) {
                $(selector).val(null).trigger('change');
            }

            // Logic Ganti Role
            window.toggleInputs = function() {
                const role = $('#role').val();

                // --- LOGIKA CABANG (Audit vs Lainnya) ---
                if (role === 'audit') {
                    $('#single-branch-group').addClass('d-none');
                    $('#multi-branch-group').removeClass('d-none');
                } else {
                    // Default semua role selain audit pakai single branch
                    $('#single-branch-group').removeClass('d-none');
                    $('#multi-branch-group').addClass('d-none');
                }

                // --- LOGIKA DIVISI (Semua Role = Multi) ---
                // Tidak perlu ada logika toggle/hide karena div "multi-division-group" 
                // sudah kita set visible permanen di HTML dan single division sudah dihapus.
            };

            // Jalankan saat load
            toggleInputs();
        });
    </script>
@endpush