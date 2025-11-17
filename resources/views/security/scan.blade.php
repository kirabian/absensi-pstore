@extends('layout.master')

@section('title')
    Scan QR Absensi
@endsection

@section('heading')
    Scan QR Code Absensi
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    {{-- Kolom Scanner --}}
                    <div class="col-md-6">
                        <div class="scanner-section text-center">
                            <h4 class="mb-4">Pindai QR Code</h4>
                            
                            {{-- Video Camera --}}
                            <div id="reader" class="mb-3" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
                            
                            {{-- Manual Input --}}
                            <div class="manual-input mt-4">
                                <label class="form-label">Atau masukkan kode manual:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="manualQrInput" placeholder="Masukkan kode QR">
                                    <button class="btn btn-primary" type="button" id="manualSubmit">Cek</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Info User --}}
                    <div class="col-md-6">
                        <div class="user-info-section">
                            <h4 class="mb-4">Informasi Karyawan</h4>
                            
                            {{-- Loading --}}
                            <div id="loading" class="text-center d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Memproses...</p>
                            </div>

                            {{-- User Info --}}
                            <div id="userInfo" class="d-none">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{-- Foto Profil --}}
                                        <div class="text-center mb-3">
                                            {{-- ID ditambahkan untuk manipulasi JS --}}
                                            <img id="userPhoto" src="" alt="Foto Profil" 
                                                 class="img-lg rounded-circle shadow" 
                                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #fff;">
                                        </div>
                                        
                                        {{-- Info User --}}
                                        <table class="table table-borderless">
                                            <tr>
                                                <td style="width: 30%;"><strong>Nama:</strong></td>
                                                <td id="userName" class="fw-bold">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Divisi:</strong></td>
                                                <td id="userDivision">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    <span id="absenStatus" class="badge bg-success">Belum Absen</span>
                                                    <span id="alreadyAbsen" class="badge bg-warning text-dark d-none">Sudah Absen Hari Ini</span>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Form Absensi --}}
                                        <form id="attendanceForm" method="POST" action="{{ route('security.attendance.store') }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="user_id" id="formUserId">
                                            
                                            <div class="mb-3 mt-3">
                                                <label for="photo" class="form-label fw-bold">Bukti Foto (Wajib) *</label>
                                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*" capture="camera" required>
                                                <small class="text-muted">Ambil foto wajah karyawan saat ini.</small>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success w-100 py-2">
                                                <i class="mdi mdi-check-circle me-2"></i>Konfirmasi Absensi
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Error Message --}}
                            <div id="errorMessage" class="alert alert-danger d-none">
                                <i class="mdi mdi-alert-circle me-2"></i>
                                <span id="errorText"></span>
                            </div>

                            {{-- Default State --}}
                            <div id="defaultState" class="text-center text-muted py-5">
                                <i class="mdi mdi-qrcode-scan display-3 mb-3 opacity-50"></i>
                                <p class="h5">Siap Memindai</p>
                                <p>Arahkan kamera ke QR Code karyawan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>

<script>
    // ==========================================
    // KONFIGURASI URL (Agar tidak error syntax)
    // ==========================================
    const BASE_STORAGE_URL = "{{ Storage::url('') }}"; 
    const DEFAULT_AVATAR = "{{ asset('assets/images/default-avatar.png') }}";
    
    let html5QrcodeScanner;

    // Initialize Scanner
    function initScanner() {
        // Cek apakah element reader ada untuk menghindari error null
        if (!document.getElementById('reader')) return;

        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10,
                qrbox: { width: 250, height: 250 },
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            },
            false
        );

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }

    // Handle Scan Success
    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanner sementara
        html5QrcodeScanner.clear();
        
        // Process QR code
        processQrCode(decodedText);
        
        // Restart scanner setelah 5 detik (memberi waktu user melihat hasil)
        setTimeout(() => {
            initScanner();
        }, 5000);
    }

    function onScanFailure(error) {
        // Biarkan kosong agar console bersih
    }

    // Process QR Code
    function processQrCode(qrCode) {
        showLoading();
        hideAllSections();

        fetch('{{ route("security.scan.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_code: qrCode })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.status === 'success') {
                displayUserInfo(data.user, data.division_name, data.already_absen);
            } else {
                showError(data.message || 'Karyawan tidak ditemukan / Salah Cabang.');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Koneksi Error: ' + error.message);
            console.error('Error:', error);
        });
    }

    // ==========================================
    // FUNGSI TAMPILAN USER INFO (FIXED)
    // ==========================================
    function displayUserInfo(user, division, alreadyAbsen) {
        // 1. Set Teks
        document.getElementById('userName').textContent = user.name;
        document.getElementById('userDivision').textContent = division;
        document.getElementById('formUserId').value = user.id;
        
        // 2. Logika Foto Profil (Yang Diperbaiki)
        const userPhoto = document.getElementById('userPhoto');
        
        if (user.profile_photo_path) {
            // Jika ada foto di database, gabungkan dengan URL Storage
            // Hapus slash di depan jika ada agar tidak double slash
            let cleanPath = user.profile_photo_path.replace(/^\/+/, '');
            userPhoto.src = BASE_STORAGE_URL + cleanPath;
        } else {
            // Jika tidak ada foto, gunakan UI Avatars (Inisial Nama)
            // Ini lebih baik daripada gambar rusak
            let encodedName = encodeURIComponent(user.name);
            userPhoto.src = `https://ui-avatars.com/api/?name=${encodedName}&background=random&color=fff&size=128`;
        }

        // Handler jika gambar error (misal file terhapus fisik) -> balik ke default/avatar
        userPhoto.onerror = function() {
            this.onerror = null; // Mencegah loop
            let encodedName = encodeURIComponent(user.name);
            this.src = `https://ui-avatars.com/api/?name=${encodedName}&background=random&color=fff`;
        };
        
        // 3. Set Status Absen
        if (alreadyAbsen) {
            document.getElementById('absenStatus').classList.add('d-none');
            document.getElementById('alreadyAbsen').classList.remove('d-none');
            
            // Opsional: Disable tombol jika sudah absen
            // document.querySelector('button[type="submit"]').disabled = true;
        } else {
            document.getElementById('absenStatus').classList.remove('d-none');
            document.getElementById('alreadyAbsen').classList.add('d-none');
            // document.querySelector('button[type="submit"]').disabled = false;
        }
        
        // 4. Tampilkan UI
        document.getElementById('userInfo').classList.remove('d-none');
        document.getElementById('defaultState').classList.add('d-none');
    }

    function showError(message) {
        document.getElementById('errorText').textContent = message;
        document.getElementById('errorMessage').classList.remove('d-none');
        document.getElementById('defaultState').classList.add('d-none');
    }

    function showLoading() {
        document.getElementById('loading').classList.remove('d-none');
        hideAllSections();
    }

    function hideLoading() {
        document.getElementById('loading').classList.add('d-none');
    }

    function hideAllSections() {
        document.getElementById('userInfo').classList.add('d-none');
        document.getElementById('errorMessage').classList.add('d-none');
        document.getElementById('defaultState').classList.add('d-none');
    }

    function resetToDefault() {
        hideAllSections();
        document.getElementById('defaultState').classList.remove('d-none');
        document.getElementById('manualQrInput').value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        initScanner();
        
        document.getElementById('manualSubmit').addEventListener('click', function() {
            const manualCode = document.getElementById('manualQrInput').value.trim();
            if (manualCode) {
                processQrCode(manualCode);
            }
        });
        
        document.getElementById('manualQrInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('manualSubmit').click();
            }
        });
    });
</script>

<style>
    .scanner-section {
        padding: 20px;
        border-right: 1px solid #e0e0e0;
    }
    
    .user-info-section {
        padding: 20px;
    }
    
    #reader {
        border: 2px dashed #007bff;
        border-radius: 10px;
        padding: 10px;
        background: #f8f9fa;
        overflow: hidden; /* Mencegah video keluar batas */
    }

    #reader video {
        border-radius: 8px; /* Membuat sudut video agak melengkung */
        object-fit: cover;
    }
    
    @media (max-width: 768px) {
        .scanner-section {
            border-right: none;
            border-bottom: 1px solid #e0e0e0;
        }
    }
</style>
@endpush