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
                                            <img id="userPhoto" src="" alt="Foto Profil" 
                                                 class="img-lg rounded-circle" 
                                                 style="width: 100px; height: 100px; object-fit: cover;"
                                                 onerror="this.src='{{ asset('assets/images/default-avatar.png') }}'">
                                        </div>
                                        
                                        {{-- Info User --}}
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Nama:</strong></td>
                                                <td id="userName">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Divisi:</strong></td>
                                                <td id="userDivision">-</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status:</strong></td>
                                                <td>
                                                    <span id="absenStatus" class="badge bg-success">Belum Absen</span>
                                                    <span id="alreadyAbsen" class="badge bg-warning d-none">Sudah Absen</span>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Form Absensi --}}
                                        <form id="attendanceForm" method="POST" action="{{ route('security.attendance.store') }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="user_id" id="formUserId">
                                            
                                            <div class="mb-3">
                                                <label for="photo" class="form-label">Ambil Foto *</label>
                                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*" capture="camera" required>
                                                <small class="text-muted">Foto wajib diambil saat ini (gunakan kamera)</small>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-success w-100">
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
                            <div id="defaultState" class="text-center text-muted">
                                <i class="mdi mdi-account-search display-4 mb-3"></i>
                                <p>Scan QR code atau masukkan kode manual untuk melihat informasi karyawan</p>
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
<!-- Include HTML5 QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>

<script>
    let html5QrcodeScanner;

    // Initialize Scanner
    function initScanner() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10,
                qrbox: { width: 250, height: 250 },
                supportedScanTypes: [
                    Html5QrcodeScanType.SCAN_TYPE_CAMERA
                ]
            },
            false
        );

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }

    // Handle Scan Success
    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanner temporarily
        html5QrcodeScanner.clear();
        
        // Process QR code
        processQrCode(decodedText);
        
        // Restart scanner after 3 seconds
        setTimeout(() => {
            initScanner();
        }, 3000);
    }

    // Handle Scan Failure
    function onScanFailure(error) {
        // Optional: Handle scan failure
        // console.warn(`QR scan failed: ${error}`);
    }

    // Process QR Code (for both scanner and manual input)
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
                showError(data.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Error: ' + error.message);
            console.error('Error:', error);
        });
    }

    // Display User Information
    function displayUserInfo(user, division, alreadyAbsen) {
        // Set user data
        document.getElementById('userName').textContent = user.name;
        document.getElementById('userDivision').textContent = division;
        document.getElementById('formUserId').value = user.id;
        
        // Set photo
        const userPhoto = document.getElementById('userPhoto');
        if (user.profile_photo_path) {
            userPhoto.src = '{{ Storage::url("") }}' + user.profile_photo_path;
        } else {
            userPhoto.src = '{{ asset('assets/images/default-avatar.png') }}';
        }
        
        // Set absen status
        if (alreadyAbsen) {
            document.getElementById('absenStatus').classList.add('d-none');
            document.getElementById('alreadyAbsen').classList.remove('d-none');
        } else {
            document.getElementById('absenStatus').classList.remove('d-none');
            document.getElementById('alreadyAbsen').classList.add('d-none');
        }
        
        // Show user info section
        document.getElementById('userInfo').classList.remove('d-none');
        document.getElementById('defaultState').classList.add('d-none');
    }

    // Show Error Message
    function showError(message) {
        document.getElementById('errorText').textContent = message;
        document.getElementById('errorMessage').classList.remove('d-none');
        document.getElementById('defaultState').classList.add('d-none');
    }

    // Show Loading
    function showLoading() {
        document.getElementById('loading').classList.remove('d-none');
        hideAllSections();
    }

    // Hide Loading
    function hideLoading() {
        document.getElementById('loading').classList.add('d-none');
    }

    // Hide All Sections
    function hideAllSections() {
        document.getElementById('userInfo').classList.add('d-none');
        document.getElementById('errorMessage').classList.add('d-none');
        document.getElementById('defaultState').classList.add('d-none');
    }

    // Reset to Default State
    function resetToDefault() {
        hideAllSections();
        document.getElementById('defaultState').classList.remove('d-none');
        document.getElementById('manualQrInput').value = '';
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize scanner
        initScanner();
        
        // Manual submit
        document.getElementById('manualSubmit').addEventListener('click', function() {
            const manualCode = document.getElementById('manualQrInput').value.trim();
            if (manualCode) {
                processQrCode(manualCode);
            }
        });
        
        // Enter key for manual input
        document.getElementById('manualQrInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('manualSubmit').click();
            }
        });
        
        // Reset form when file input changes (optional)
        document.getElementById('photo').addEventListener('change', function() {
            // Optional: Add preview or validation
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
    }
    
    @media (max-width: 768px) {
        .scanner-section {
            border-right: none;
            border-bottom: 1px solid #e0e0e0;
        }
    }
</style>
@endpush