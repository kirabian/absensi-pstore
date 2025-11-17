@extends('layout.master')

@section('title', 'Security Scanner - AbsenPS')

@section('content')
<div class="scanner-container">
    {{-- Header --}}
    <div class="scanner-header text-center">
        <h2 class="mb-1">🔒 SECURITY SCANNER</h2>
        <p class="mb-0" id="scanner-step">Langkah 1: Scan QR Code Karyawan</p>
    </div>

    {{-- Scanner Area --}}
    <div class="scanner-body">
        {{-- QR Scanner --}}
        <div id="qr-scanner">
            <div id="reader"></div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="scanner-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <small id="footer-info">Pastikan QR Code dalam frame kamera</small>
                </div>
                <div class="col-auto">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">
                        ← Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Result Overlay --}}
<div class="result-overlay" id="resultOverlay">
    <div class="result-card">
        <div id="resultContent">
            {{-- Content akan diisi oleh JavaScript --}}
        </div>
        <button class="btn btn-primary mt-3" onclick="closeResult()">
            Scan Lagi
        </button>
    </div>
</div>
@endsection

@push('styles')
<style>
    .scanner-container {
        height: 100vh;
        display: flex;
        flex-direction: column;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .scanner-header {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
        padding: 20px 0;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }
    .scanner-header h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 8px !important;
    }
    .scanner-header p {
        font-size: 1.1rem;
        font-weight: 500;
        margin: 0;
        opacity: 0.95;
    }
    .scanner-body {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px 20px;
        background: #000;
        position: relative;
        overflow: hidden;
    }
    #reader {
        width: 100%;
        max-width: 800px;
        height: 70vh;
        border: 4px solid #fff;
        border-radius: 20px;
        box-shadow: 0 0 40px rgba(255,255,255,0.3);
        overflow: hidden;
        background: #000;
    }
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        border-radius: 16px;
    }
    
    /* Selfie Camera Styles */
    .camera-container {
        position: relative;
        width: 100%;
        max-width: 800px;
        height: 70vh;
        border: 4px solid #fff;
        border-radius: 20px;
        overflow: hidden;
        background: #000;
    }
    #selfie-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Mirror effect for selfie */
        border-radius: 16px;
    }
    .camera-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        background: rgba(0,0,0,0.3);
    }
    .overlay-frame {
        width: 280px;
        height: 280px;
        border: 4px solid #28a745;
        border-radius: 15px;
        box-shadow: 0 0 0 9999px rgba(0,0,0,0.4);
    }
    .camera-overlay p {
        color: white;
        font-size: 1.2rem;
        font-weight: 600;
        margin-top: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    .camera-controls {
        position: absolute;
        bottom: 30px;
        left: 0;
        width: 100%;
        z-index: 10;
        text-align: center;
    }
    .camera-controls .btn {
        font-size: 1.1rem;
        font-weight: 600;
        padding: 12px 30px;
        margin: 0 10px;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .scanner-footer {
        background: #2c3034;
        color: white;
        padding: 20px 0;
        text-align: center;
        flex-shrink: 0;
        border-top: 1px solid #444;
    }
    .scanner-footer small {
        font-size: 1rem;
        opacity: 0.9;
    }
    .scanner-footer .btn {
        font-weight: 500;
    }
    
    .result-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.95);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
    }
    .result-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 450px;
        width: 100%;
        text-align: center;
        animation: popIn 0.5s ease-out;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    @keyframes popIn {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .user-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #28a745;
        margin: 0 auto 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    .user-biodata {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 25px;
        margin: 20px 0;
        text-align: left;
        border: 2px solid #e9ecef;
    }
    .biodata-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #dee2e6;
    }
    .biodata-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    .biodata-label {
        font-weight: 700;
        color: #495057;
        min-width: 100px;
        font-size: 1rem;
    }
    .biodata-value {
        color: #212529;
        font-weight: 500;
        text-align: right;
        flex: 1;
    }
    
    /* Loading and Status Styles */
    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .scanner-header {
            padding: 15px 0;
        }
        .scanner-header h2 {
            font-size: 1.5rem;
        }
        .scanner-header p {
            font-size: 1rem;
        }
        .scanner-body {
            padding: 20px 15px;
        }
        #reader, .camera-container {
            height: 60vh;
            border-width: 3px;
        }
        .overlay-frame {
            width: 220px;
            height: 220px;
        }
        .camera-controls .btn {
            font-size: 1rem;
            padding: 10px 20px;
        }
        .result-card {
            padding: 30px 20px;
            margin: 10px;
        }
    }
    
    @media (max-width: 480px) {
        .scanner-header h2 {
            font-size: 1.3rem;
        }
        .scanner-header p {
            font-size: 0.9rem;
        }
        #reader, .camera-container {
            height: 50vh;
        }
        .overlay-frame {
            width: 180px;
            height: 180px;
        }
        .camera-controls .btn {
            font-size: 0.9rem;
            padding: 8px 16px;
            margin: 0 5px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let html5QrcodeScanner = null;
    let isProcessing = false;
    let currentScannedData = null;
    let selfieStream = null;

    // Step 1: QR Code Scanning
    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        isProcessing = true;
        
        console.log('QR Code detected:', decodedText);
        
        if (html5QrcodeScanner) {
            html5QrcodeScanner.pause();
        }

        processQRCode(decodedText);
    }

    function processQRCode(qrCode) {
        showResult(`
            <div class="text-center">
                <div class="loading-spinner"></div>
                <h4 class="mt-3">Memproses QR Code...</h4>
                <p class="text-muted">Mohon tunggu sebentar</p>
            </div>
        `);

        fetch("{{ route('security.validate') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ qr_code: qrCode })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error: ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            
            if (data.status === 'success') {
                currentScannedData = data.data;
                showBiodataAndProceedToSelfie(data.data);
            } else {
                showResult(`
                    <div class="text-danger">
                        <div class="mb-3" style="font-size: 3rem;">❌</div>
                        <h4 class="text-danger">Scan Gagal</h4>
                        <p class="mb-0">${data.message}</p>
                    </div>
                `);
                isProcessing = false;
                if (html5QrcodeScanner) html5QrcodeScanner.resume();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            showResult(`
                <div class="text-danger">
                    <div class="mb-3" style="font-size: 3rem;">⚠️</div>
                    <h4 class="text-danger">Error Sistem</h4>
                    <p class="mb-0">Terjadi kesalahan koneksi</p>
                    <small class="text-muted">${err.message}</small>
                </div>
            `);
            isProcessing = false;
            if (html5QrcodeScanner) html5QrcodeScanner.resume();
        });
    }

    // Step 2: Show Biodata & Prepare Selfie
    function showBiodataAndProceedToSelfie(userData) {
        document.getElementById('resultOverlay').style.display = 'none';
        
        document.getElementById('scanner-step').textContent = 'Langkah 2: Verifikasi Data & Foto Selfie';
        document.getElementById('footer-info').textContent = 'Verifikasi data karyawan dan ambil foto selfie';

        const scannerBody = document.querySelector('.scanner-body');
        scannerBody.innerHTML = `
            <div class="biodata-container text-white text-center" style="max-width: 800px; width: 100%;">
                <div class="card bg-dark border-0 shadow-lg">
                    <div class="card-header bg-success py-3">
                        <h4 class="mb-0">✅ QR Code Valid</h4>
                        <p class="mb-0 opacity-90">Data karyawan berhasil ditemukan</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-3 mb-md-0">
                                <img src="${userData.photo}" class="user-photo" alt="Photo">
                            </div>
                            <div class="col-md-8 text-start">
                                <div class="biodata-item">
                                    <span class="biodata-label">Nama:</span>
                                    <span class="biodata-value">${userData.name}</span>
                                </div>
                                <div class="biodata-item">
                                    <span class="biodata-label">Jabatan:</span>
                                    <span class="biodata-value">${userData.role}</span>
                                </div>
                                <div class="biodata-item">
                                    <span class="biodata-label">Divisi:</span>
                                    <span class="biodata-value">${userData.division}</span>
                                </div>
                                <div class="biodata-item">
                                    <span class="biodata-label">Cabang:</span>
                                    <span class="biodata-value"><span class="badge bg-success">${userData.branch}</span></span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top border-secondary">
                            <p class="text-warning mb-3"><strong>Sekarang ambil foto selfie untuk menyelesaikan absensi</strong></p>
                            <button class="btn btn-primary btn-lg px-4 py-2" onclick="startSelfieCamera()">
                                📸 Lanjutkan ke Foto Selfie
                            </button>
                            <button class="btn btn-secondary btn-lg px-4 py-2 ms-2" onclick="backToQRScanner()">
                                ↩ Kembali Scan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Step 3: Selfie Camera
    function startSelfieCamera() {
        const scannerBody = document.querySelector('.scanner-body');
        scannerBody.innerHTML = `
            <div class="camera-container">
                <video id="selfie-video" autoplay playsinline></video>
                <canvas id="selfie-canvas" style="display: none;"></canvas>
                <div class="camera-overlay">
                    <div class="overlay-frame"></div>
                    <p>Posisikan wajah dalam frame</p>
                </div>
                <div class="camera-controls">
                    <button class="btn btn-success btn-lg" onclick="captureSelfie()">
                        📸 Ambil Foto
                    </button>
                    <button class="btn btn-secondary btn-lg ms-2" onclick="backToBiodata()">
                        ↩ Kembali
                    </button>
                </div>
            </div>
        `;

        startCamera('user');
    }

    function startCamera(facingMode) {
        const constraints = {
            video: { 
                facingMode: facingMode,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false 
        };

        navigator.mediaDevices.getUserMedia(constraints)
        .then(stream => {
            selfieStream = stream;
            const video = document.getElementById('selfie-video');
            video.srcObject = stream;
            
            video.onloadedmetadata = () => {
                video.play().catch(err => console.error('Video play error:', err));
            };
        })
        .catch(err => {
            console.error('Camera error:', err);
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan dan kamera tidak sedang digunakan aplikasi lain.');
            backToBiodata();
        });
    }

    function captureSelfie() {
        const video = document.getElementById('selfie-video');
        const canvas = document.getElementById('selfie-canvas');
        
        if (!video.videoWidth || !video.videoHeight) {
            alert('Kamera belum siap. Tunggu sebentar hingga kamera aktif.');
            return;
        }

        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        const photoData = canvas.toDataURL('image/jpeg', 0.8);
        
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
        }

        completeAttendanceWithSelfie(photoData);
    }

    function completeAttendanceWithSelfie(selfiePhoto) {
        showResult(`
            <div class="text-center">
                <div class="loading-spinner"></div>
                <h4 class="mt-3">Menyimpan Absensi...</h4>
                <p class="text-muted">Sedang menyimpan data dan foto</p>
            </div>
        `);

        fetch("{{ route('security.complete-attendance') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({
                user_data: currentScannedData,
                selfie_photo: selfiePhoto
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showResult(`
                    <div class="text-success">
                        <div class="mb-3" style="font-size: 3rem;">✅</div>
                        <h4 class="text-success">Absensi Berhasil!</h4>
                        <p class="mb-3">${data.message}</p>
                        <div class="mt-3 p-3 bg-light rounded">
                            <img src="${currentScannedData.photo}" class="user-photo" alt="Photo">
                            <h5 class="mt-2">${currentScannedData.name}</h5>
                            <p class="mb-1">${currentScannedData.role} - ${currentScannedData.division}</p>
                            <span class="badge bg-success">${currentScannedData.branch}</span>
                            <div class="mt-2 small text-muted">
                                ${new Date().toLocaleString('id-ID')}
                            </div>
                        </div>
                    </div>
                `);
            } else {
                throw new Error(data.message);
            }
        })
        .catch(err => {
            showResult(`
                <div class="text-danger">
                    <div class="mb-3" style="font-size: 3rem;">❌</div>
                    <h4 class="text-danger">Gagal Menyimpan</h4>
                    <p class="mb-0">${err.message}</p>
                </div>
            `);
        })
        .finally(() => {
            isProcessing = false;
            currentScannedData = null;
        });
    }

    // Navigation functions
    function backToBiodata() {
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
        }
        showBiodataAndProceedToSelfie(currentScannedData);
    }

    function backToQRScanner() {
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
        }
        resetToQRScanner();
    }

    function resetToQRScanner() {
        document.getElementById('scanner-step').textContent = 'Langkah 1: Scan QR Code Karyawan';
        document.getElementById('footer-info').textContent = 'Pastikan QR Code dalam frame kamera';
        
        const scannerBody = document.querySelector('.scanner-body');
        scannerBody.innerHTML = `
            <div id="qr-scanner">
                <div id="reader"></div>
            </div>
        `;
        
        initializeQRScanner();
        isProcessing = false;
        currentScannedData = null;
    }

    function showResult(content) {
        document.getElementById('resultContent').innerHTML = content;
        document.getElementById('resultOverlay').style.display = 'flex';
    }

    function closeResult() {
        document.getElementById('resultOverlay').style.display = 'none';
        resetToQRScanner();
    }

    function onScanFailure(error) {
        // Biarkan kosong
    }

    // Initialize QR Scanner with BACK camera
    function initializeQRScanner() {
        const readerElement = document.getElementById('reader');
        readerElement.innerHTML = '<div class="text-center text-white p-5"><div class="spinner-border text-light mb-3"></div><p>Memuat kamera...</p></div>';

        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                console.log('Available cameras:', cameras);
                
                let backCamera = cameras.find(cam => 
                    cam.label.toLowerCase().includes('back') || 
                    cam.label.toLowerCase().includes('rear')
                );
                
                if (!backCamera && cameras.length > 1) {
                    backCamera = cameras[1];
                }
                
                if (!backCamera) {
                    backCamera = cameras[0];
                }
                
                const cameraId = backCamera.id;
                console.log('Using camera:', backCamera.label);
                
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", 
                    { 
                        fps: 10, 
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    }, 
                    false
                );
                
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                
            } else {
                readerElement.innerHTML = `
                    <div class="text-center text-white p-5">
                        <div style="font-size: 3rem;">📷</div>
                        <h4 class="mt-3">Kamera Tidak Ditemukan</h4>
                        <p class="mb-3">Pastikan kamera terhubung dan diizinkan</p>
                        <button class="btn btn-warning" onclick="initializeQRScanner()">
                            🔄 Coba Lagi
                        </button>
                    </div>
                `;
            }
        }).catch(err => {
            console.error('Camera access error:', err);
            readerElement.innerHTML = `
                <div class="text-center text-white p-5">
                    <div style="font-size: 3rem;">❌</div>
                    <h4 class="mt-3">Akses Kamera Ditolak</h4>
                    <p class="mb-3">Izinkan akses kamera di pengaturan browser</p>
                    <button class="btn btn-warning" onclick="initializeQRScanner()">
                        🔄 Coba Lagi
                    </button>
                </div>
            `;
        });
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing QR Scanner...');
        initializeQRScanner();
    });

    // Handle page visibility
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && html5QrcodeScanner) {
            html5QrcodeScanner.pause();
        } else if (!document.hidden && html5QrcodeScanner) {
            html5QrcodeScanner.resume();
        }
    });
</script>
@endpush