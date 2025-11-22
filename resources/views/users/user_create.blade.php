{{-- resources/views/users/create.blade.php --}}
@extends('layout.master')

@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        /* --- PERBAIKAN FORCE (ANTI-CONFLICT) --- */
        
        /* 1. Hilangkan bullet points yang dipaksa oleh template admin */
        .select2-container ul, 
        .select2-container li,
        .select2-selection__rendered {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* 2. Perbaiki tampilan kotak input agar terlihat rapi (Putih & Border Halus) */
        .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 38px !important;
            background-color: #fff !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            padding: 4px 8px !important;
        }

        /* 3. Style untuk item yang dipilih (Tags/Pills) */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef !important; /* Abu-abu muda soft */
            border: 1px solid #ced4da !important;
            color: #495057 !important;
            border-radius: 4px !important;
            padding: 2px 8px !important;
            margin-top: 4px !important;
            font-size: 0.85rem !important;
        }

        /* 4. Tombol silang (x) pada tag */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
            color: #dc3545 !important; /* Merah saat hover */
            margin-right: 5px !important;
            border: none !important;
            background: transparent !important;
        }

        /* 5. Link Pilih Semua / Hapus Semua */
        .select-action-links a {
            font-size: 0.85rem;
            text-decoration: none;
            font-weight: 500;
        }
        .select-action-links a:hover {
            text-decoration: underline;
        }
    </style>
@endsection

@section('content')
<form class="forms-sample" action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Login & Role</h4>
                    
                    <div class="form-group mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>ID Login *</label>
                        <input type="text" class="form-control" name="login_id" required>
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
                        <label>Cabang Utama</label>
                        <select class="form-select select2-single" name="branch_id" data-placeholder="Pilih Cabang">
                            <option></option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3 d-none" id="multi-branch-group">
                        <label class="text-primary fw-bold mb-2">Akses Wilayah Audit (Multi)</label>
                        
                        <select class="form-select select2-multi" name="multi_branches[]" multiple data-placeholder="Pilih cabang...">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>

                        <div class="mt-2 select-action-links">
                            <a href="javascript:void(0)" class="text-primary me-3" onclick="selectAll('#multi-branch-group .select2-multi')">Pilih Semua</a>
                            <a href="javascript:void(0)" class="text-danger" onclick="clearAll('#multi-branch-group .select2-multi')">Hapus Semua</a>
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
                        <label class="text-success fw-bold mb-2">Pimpin Divisi (Multi)</label>
                        
                        <select class="form-select select2-multi" name="multi_divisions[]" multiple data-placeholder="Pilih divisi...">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                        
                        <div class="mt-2 select-action-links">
                            <a href="javascript:void(0)" class="text-primary me-3" onclick="selectAll('#multi-division-group .select2-multi')">Pilih Semua</a>
                            <a href="javascript:void(0)" class="text-danger" onclick="clearAll('#multi-division-group .select2-multi')">Hapus Semua</a>
                        </div>
                    </div>

                    <hr>
                    <div class="form-group mb-3">
                        <label>Foto Profil</label>
                        <input type="file" class="form-control" name="profile_photo_path">
                    </div>
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="form-group mb-3">
                        <label>WhatsApp</label>
                        <input type="text" class="form-control" name="whatsapp">
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
            // 1. INISIALISASI SELECT2
            // Menggunakan class umum agar codingan lebih pendek
            
            // Single Select
            $('.select2-single').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true
            });

            // Multi Select (Yang tadi rusak)
            $('.select2-multi').select2({
                theme: "bootstrap-5",
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                closeOnSelect: false,
                allowClear: true
            });

            // 2. FUNGSI BANTUAN (Select All / Clear All)
            window.selectAll = function(selector) {
                $(selector).find('option').prop('selected', true);
                $(selector).trigger('change');
            }

            window.clearAll = function(selector) {
                $(selector).val(null).trigger('change');
            }

            // 3. LOGIC GANTI ROLE
            window.toggleInputs = function() {
                const role = $('#role').val();

                // Reset tampilan
                $('#single-branch-group, #single-division-group').removeClass('d-none');
                $('#multi-branch-group, #multi-division-group').addClass('d-none');

                if (role === 'audit') {
                    $('#single-branch-group').addClass('d-none');
                    $('#multi-branch-group').removeClass('d-none');
                } 
                else if (role === 'leader') {
                    $('#single-division-group').addClass('d-none');
                    $('#multi-division-group').removeClass('d-none');
                }
            };

            // Jalankan sekali saat load
            toggleInputs();
        });
    </script>
@endpush