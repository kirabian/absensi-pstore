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
        padding: 4px;
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
    
    /* Select2 Dropdown Styling */
    .select2-container--default .select2-results__option {
        padding: 10px 15px;
        font-size: 0.95rem;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .select2-dropdown {
        border: 1px solid #e8ecf1;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #e8ecf1;
        border-radius: 6px;
        padding: 8px 12px;
    }
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #667eea;
        outline: none;
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
    .badge-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }
    .badge-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }
    .badge-success-gradient {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(86, 171, 47, 0.3);
    }

    /* Container for Toggle Fields */
    .position-relative {
        transition: min-height 0.3s ease;
    }
    .position-absolute {
        transition: opacity 0.3s ease, visibility 0.3s ease;
        opacity: 1;
        visibility: visible;
        z-index: 1;
    }
    .position-absolute.d-none {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        z-index: 0;
    }
    
    /* Ensure Select2 dropdown appears above everything */
    .select2-container {
        z-index: 9999 !important;
    }
    .select2-dropdown {
        z-index: 9999 !important;
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

                    {{-- INPUT CABANG (SELALU MULTI SELECT) --}}
                    <div class="form-group">
                        <label>
                            🏢 Cabang <span class="badge badge-info text-white">Multi Select</span>
                        </label>
                        <select class="form-control select2-multi" name="branches[]" multiple="multiple" style="width:100%" id="branch-select">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" 
                                    {{ (collect(old('branches'))->contains($branch->id)) ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                            💡 <span id="branch-count">0</span> cabang dipilih
                        </small>
                    </div>

                    {{-- INPUT DIVISI (SELALU MULTI SELECT) --}}
                    <div class="form-group">
                        <label>
                            📋 Divisi <span class="badge badge-info text-white">Multi Select</span>
                        </label>
                        <select class="form-control select2-multi" name="divisions[]" multiple="multiple" style="width:100%" id="division-select">
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}"
                                    {{ (collect(old('divisions'))->contains($division->id)) ? 'selected' : '' }}>
                                    {{ $division->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                            💡 <span id="division-count">0</span> divisi dipilih
                        </small>
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
        // Init Select2 untuk single select
        $('.select2-single').select2({
            placeholder: "-- Pilih --",
            allowClear: true,
            width: '100%',
            dropdownParent: $('.card-body')
        });
        
        // Init Select2 untuk multi select
        $('.select2-multi').select2({
            placeholder: "-- Pilih satu atau lebih --",
            allowClear: true,
            width: '100%',
            closeOnSelect: false,
            dropdownParent: $('.card-body')
        });
        
        toggleInputs();
    });

    function toggleInputs() {
        var role = document.getElementById('role').value;
        
        // Helper function untuk smooth toggle
        function showElement(showId, hideId) {
            $(hideId).addClass('d-none');
            $(hideId).find('select').val(null).trigger('change'); // Clear selection
            setTimeout(function() {
                $(showId).removeClass('d-none');
            }, 50);
        }

        if (role === 'audit') {
            // Audit: Multi Branch, Single Division
            showElement('#multi-branch-group', '#single-branch-group');
            showElement('#single-division-group', '#multi-division-group');
        } 
        else if (role === 'leader') {
            // Leader: Single Branch, Multi Division
            showElement('#single-branch-group', '#multi-branch-group');
            showElement('#multi-division-group', '#single-division-group');
        }
        else {
            // Default: Single mode untuk keduanya
            showElement('#single-branch-group', '#multi-branch-group');
            showElement('#single-division-group', '#multi-division-group');
        }
    }
</script>
@endpush