{{-- resources/views/users/create.blade.php --}}
@extends('layout.master')

@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <style>
        /* Memastikan dropdown container select2 mengikuti lebar bootstrap */
        .select2-container--bootstrap-5 .select2-selection {
            border: 1px solid #ced4da !important; 
        }
        
        /* Agar opsi "Pilih Semua" terlihat rapi */
        .select-all-link {
            font-size: 0.875rem;
            text-decoration: none;
        }
        .select-all-link:hover {
            text-decoration: underline;
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

        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Penempatan & Kontak</h4>

                    <div class="form-group mb-3" id="single-branch-group">
                        <label>Cabang Utama (Homebase)</label>
                        <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang Homebase">
                            <option></option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

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

                    <div class="form-group mb-3" id="single-division-group">
                        <label>Divisi Utama</label>
                        <select class="form-select select2-single" name="division_id" data-placeholder="Pilih Divisi">
                            <option></option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

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
            
            // 1. Init Single Select (Standard Bootstrap 5 Theme)
            function initSingle() {
                $('.select2-single').select2({
                    theme: "bootstrap-5",
                    width: '100%',
                    placeholder: $(this).data('placeholder'),
                    allowClear: true
                });
            }

            // 2. Init Multi Select (Standard Bootstrap 5 Theme)
            // Sesuai dokumentasi: Simple, bersih, background putih
            function initMulti() {
                $('.select2-multi').select2({
                    theme: "bootstrap-5",
                    width: '100%',
                    placeholder: $(this).data('placeholder'),
                    closeOnSelect: false, // Biarkan terbuka saat memilih banyak item
                    allowClear: true
                });
            }

            // Jalankan inisialisasi
            initSingle();
            initMulti();

            // Fungsi toggle saat ganti role (menampilkan/menyembunyikan input)
            window.toggleInputs = function() {
                const role = $('#role').val();

                // --- BRANCH LOGIC ---
                if (role === 'audit') {
                    $('#multi-branch-group').removeClass('d-none');
                    $('#single-branch-group').addClass('d-none');
                    
                    // Re-init untuk memastikan lebar render benar saat element muncul
                    setTimeout(() => {
                        /* Opsional: destroy dulu kalau ada isu render, tapi biasanya langsung init ulang aman */
                        // $('#multi-branch-group .select2-multi').select2('destroy'); 
                        initMulti();
                    }, 50);
                } else {
                    $('#single-branch-group').removeClass('d-none');
                    $('#multi-branch-group').addClass('d-none');
                }

                // --- DIVISION LOGIC ---
                if (role === 'leader') {
                    $('#multi-division-group').removeClass('d-none');
                    $('#single-division-group').addClass('d-none');
                    
                    setTimeout(() => {
                        initMulti();
                    }, 50);
                } else {
                    $('#single-division-group').removeClass('d-none');
                    $('#multi-division-group').addClass('d-none');
                }
            };

            // Trigger pertama kali saat halaman load
            toggleInputs();
        });
    </script>
@endpush