{{-- resources/views/users/create.blade.php --}}
@extends('layout.master')

@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('styles')
    <!-- Select2 Core -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap-5 Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* Batasi tinggi dropdown supaya tidak tumpah */
        .select2-results__options {
            max-height: 250px !important;
            overflow-y: auto !important;
        }

        /* Dropdown mirip card: background putih + shadow */
        .select2-dropdown {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Kotak input multi-select lebih rapi */
        .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 48px;
            max-height: 130px;
            overflow-y: auto;
            padding: 6px 10px;
            border: 1px solid #ced4da !important;
        }

        /* Pill pilihan cantik */
        .select2-selection__choice {
            background-color: #e3f2fd !important;
            border: 1px solid #2196F3 !important;
            border-radius: 20px !important;
            color: #1976d2 !important;
            font-size: 0.875rem;
            padding: 2px 10px !important;
            margin: 2px 4px 2px 0 !important;
        }

        .select2-selection__choice__remove {
            color: #1976d2 !important;
            margin-right: 6px !important;
        }

        /* Tombol "Pilih Semua / Hapus Semua" */
        .select-all-link {
            font-size: 0.875rem;
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
        <!-- KARTU 1: LOGIN & ROLE -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Login & Role</h4>
                    
                    <div class="form-group mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>ID Login *</label>
                        <input type="text" class="form-control" name="login_id" value="{{ old('login_id') }}" placeholder="Username untuk login" required>
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
                            <option value="leader">Leader (Multi Divisi)</option>
                            <option value="security">Security</option>
                            <option value="user_biasa">Karyawan</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- KARTU 2: PENEMPATAN & KONTAK -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Penempatan & Kontak</h4>

                    <!-- SINGLE BRANCH (default) -->
                    <div class="form-group mb-3" id="single-branch-group">
                        <label>Cabang Utama (Homebase)</label>
                        <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang Homebase">
                            <option></option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- MULTI BRANCH (hanya untuk role audit) -->
                    <div class="form-group mb-3 d-none" id="multi-branch-group">
                        <label class="text-primary fw-bold">Akses Wilayah Audit (Multi)</label>
                        <select class="form-select select2-multi" name="multi_branches[]" multiple data-placeholder="Pilih satu atau beberapa cabang">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <a href="javascript:void(0)" class="text-primary select-all-link me-3" onclick="selectAll('#multi-branch-group .select2-multi')">
                                Pilih Semua
                            </a>
                            <a href="javascript:void(0)" class="text-danger select-all-link" onclick="clearAll('#multi-branch-group .select2-multi')">
                                Hapus Semua
                            </a>
                        </div>
                    </div>

                    <!-- SINGLE DIVISION -->
                    <div class="form-group mb-3" id="single-division-group">
                        <label>Divisi Utama</label>
                        <select class="form-select select2-single" name="division_id" data-placeholder="Pilih Divisi">
                            <option></option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- MULTI DIVISION (hanya untuk role leader) -->
                    <div class="form-group mb-3 d-none" id="multi-division-group">
                        <label class="text-success fw-bold">Pimpin Divisi (Multi)</label>
                        <select class="form-select select2-multi" name="multi_divisions[]" multiple data-placeholder="Pilih divisi yang dipimpin">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2">
                            <a href="javascript:void(0)" class="text-primary select-all-link me-3" onclick="selectAll('#multi-division-group .select2-multi')">
                                Pilih Semua
                            </a>
                            <a href="javascript:void(0)" class="text-danger select-all-link" onclick="clearAll('#multi-division-group .select2-multi')">
                                Hapus Semua
                            </a>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group mb-3">
                        <label>Foto Profil</label>
                        <input type="file" class="form-control" name="profile_photo_path" accept="image/*">
                    </div>

                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="contoh@email.com">
                    </div>

                    <div class="form-group mb-3">
                        <label>WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp" value="{{ old('whatsapp') }}" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Instagram</label>
                                <input type="text" class="form-control" name="instagram" value="{{ old('instagram') }}" placeholder="@username">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>TikTok</label>
                                <input type="text" class="form-control" name="tiktok" value="{{ old('tiktok') }}" placeholder="@username">
                            </div>
                        </div>
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
    <!-- Plugin pagination (ringan banget) -->
    <script src="https://cdn.jsdelivr.net/npm/select2-pagination@1.0.0/dist/select2-pagination.min.js"></script>

    <script>
        // Fungsi select all
        function selectAll(selector) {
            $(selector).find('option').prop('selected', true);
            $(selector).trigger('change');
        }

        // Fungsi clear all
        function clearAll(selector) {
            $(selector).val(null).trigger('change');
        }

        $(document).ready(function() {
            // Inisialisasi single select
            function initSingle() {
                $('.select2-single').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $(this).data('placeholder'),
                    allowClear: true
                });
            }

            // Inisialisasi multi select dengan pagination
            function initMulti() {
                $('.select2-multi').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $(this).data('placeholder'),
                    closeOnSelect: false,
                    pagination: {
                        pageSize: 15,
                        infiniteScroll: true
                    }
                });
            }

            // Init pertama kali
            initSingle();
            initMulti();

            // Fungsi toggle saat ganti role
            window.toggleInputs = function() {
                const role = $('#role').val();

                // --- BRANCH ---
                if (role === 'audit') {
                    $('#multi-branch-group').removeClass('d-none');
                    $('#single-branch-group').addClass('d-none');
                    setTimeout(() => {
                        $('#multi-branch-group .select2-multi').select2('destroy');
                        initMulti();
                    }, 50);
                } else {
                    $('#single-branch-group').removeClass('d-none');
                    $('#multi-branch-group').addClass('d-none');
                    setTimeout(() => {
                        $('#single-branch-group .select2-single').select2('destroy');
                        initSingle();
                    }, 50);
                }

                // --- DIVISION ---
                if (role === 'leader') {
                    $('#multi-division-group').removeClass('d-none');
                    $('#single-division-group').addClass('d-none');
                    setTimeout(() => {
                        $('#multi-division-group .select2-multi').select2('destroy');
                        initMulti();
                    }, 50);
                } else {
                    $('#single-division-group').removeClass('d-none');
                    $('#multi-division-group').addClass('d-none');
                    setTimeout(() => {
                        $('#single-division-group .select2-single').select2('destroy');
                        initSingle();
                    }, 50);
                }
            };

            // Trigger pertama kali (jika ada old input)
            toggleInputs();
        });
    </script>
@endpush