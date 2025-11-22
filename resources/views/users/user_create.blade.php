@extends('layout.master')
@section('title', 'Tambah User')
@section('heading', 'Tambah User Baru')

{{-- Tambahkan CSS Select2 --}}
@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 Styling */
    .select2-container .select2-selection--single { 
        height: 46px; 
        border: 1px solid #e8ecf1;
        border-radius: 8px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { 
        line-height: 46px;
        padding-left: 16px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
    }
    
    /* Multi Select Styling */
    .select2-container--default .select2-selection--multiple { 
        border: 1px solid #e8ecf1;
        border-radius: 8px;
        min-height: 46px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none; 
        color: white; 
        font-size: 13px;
        border-radius: 6px;
        padding: 4px 10px;
        margin: 4px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 6px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffd1dc;
    }

    /* Card Enhancements */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .card-title {
        font-weight: 700;
        font-size: 1.25rem;
        color: #2c2c54;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid #f0f0f0;
        position: relative;
    }
    .card-title::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }

    /* Form Group Styling */
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    /* Input Styling */
    .form-control {
        border: 1px solid #e8ecf1;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    /* Alert Styling */
    .alert-danger {
        border-radius: 12px;
        border: none;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(238, 90, 111, 0.3);
    }
    .alert-danger ul {
        padding-left: 1.25rem;
    }

    /* Button Styling */
    .btn {
        border-radius: 8px;
        padding: 0.65rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
    .btn-light {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #e9ecef;
    }
    .btn-light:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }

    /* Badge for Multi-select Labels */
    .badge-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .badge-success-gradient {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Icon Styling */
    .icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 1.2rem;
    }

    /* Divider */
    hr {
        border-top: 2px solid #f0f0f0;
        margin: 1.5rem 0;
    }

    /* Small Text */
    .text-muted {
        font-size: 0.85rem;
        color: #9ca3af !important;
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .card {
        animation: fadeInUp 0.5s ease;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .card-title {
            font-size: 1.1rem;
        }
        .btn {
            padding: 0.6rem 1.5rem;
            font-size: 0.9rem;
        }
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
                        <label><span class="badge badge-gradient text-white">Akses Wilayah Audit (Multi Select)</span></label>
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
                        <label><span class="badge badge-success-gradient text-white">Pimpin Divisi (Multi Select)</span></label>
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
        $('.select2').select2({
            placeholder: "-- Pilih --",
            allowClear: true
        }); 
        toggleInputs();
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