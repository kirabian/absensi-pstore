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

        {{-- Camera Selfie --}}
        <div id="selfie-camera" style="display: none;">
            <div class="camera-container">
                <video id="selfie-video" autoplay playsinline></video>
                <canvas id="selfie-canvas" style="display: none;"></canvas>
                <div class="camera-overlay">
                    <div class="overlay-frame"></div>
                    <p class="text-white">Ambil foto untuk absensi</p>
                </div>
            </div>
            <div class="camera-controls text-center mt-3">
                <button class="btn btn-success btn-lg" onclick="captureSelfie()">
                    📸 Ambil Foto
                </button>
                <button class="btn btn-secondary btn-lg ms-2" onclick="backToQRScanner()">
                    ↩ Kembali
                </button>
            </div>
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
    }
    .scanner-header {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
        padding: 15px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .scanner-body {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: #000;
        position: relative;
    }
    #reader {
        width: 100%;
        max-width: 800px;
        height: 80vh;
        border: 3px solid #fff;
        border-radius: 15px;
        box-shadow: 0 0 30px rgba(255,255,255,0.2);
        overflow: hidden;
    }
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        border-radius: 12px;
    }
    
    /* Selfie Camera Styles */
    .camera-container {
        position: relative;
        width: 100%;
        max-width: 800px;
        height: 80vh;
        border: 3px solid #fff;
        border-radius: 15px;
        overflow: hidden;
        background: #000;
    }
    #selfie-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Mirror effect for selfie */
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
    }
    .overlay-frame {
        width: 300px;
        height: 300px;
        border: 3px solid #28a745;
        border-radius: 15px;
        box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
    }
    .camera-controls {
        position: absolute;
        bottom: 20px;
        left: 0;
        width: 100%;
        z-index: 10;
    }
    
    .scanner-footer {
        background: #343a40;
        color: white;
        padding: 15px;
        text-align: center;
    }
    .result-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .result-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        max-width: 400px;
        text-align: center;
        animation: popIn 0.5s ease-out;
    }
    @keyframes popIn {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .user-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #28a745;
        margin: 0 auto 15px;
    }
    .user-biodata {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin: 15px 0;
        text-align: left;
    }
    .biodata-item {
        display: flex;
        justify-content: between;
        margin-bottom: 8px;
    }
    .biodata-label {
        font-weight: bold;
        min-width: 100px;
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
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h4>Memproses QR Code...</h4>
                <p>Mohon tunggu</p>
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
                // Simpan data yang di-scan dan lanjut ke step 2
                currentScannedData = data.data;
                showBiodataAndProceedToSelfie(data.data);
            } else {
                showResult(`
                    <div class="text-danger">
                        <div class="mb-3">❌</div>
                        <h4 class="text-danger">GAGAL</h4>
                        <p>${data.message}</p>
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
                    <div class="mb-3">⚠️</div>
                    <h4 class="text-danger">ERROR</h4>
                    <p>Terjadi kesalahan sistem</p>
                </div>
            `);
            isProcessing = false;
            if (html5QrcodeScanner) html5QrcodeScanner.resume();
        });
    }

    // Step 2: Show Biodata & Prepare Selfie
    function showBiodataAndProceedToSelfie(userData) {
        document.getElementById('resultOverlay').style.display = 'none';
        
        // Update UI untuk step 2
        document.getElementById('qr-scanner').style.display = 'none';
        document.getElementById('scanner-step').textContent = 'Langkah 2: Verifikasi Data & Foto Selfie';
        document.getElementById('footer-info').textContent = 'Verifikasi data dan ambil foto selfie';

        // Show biodata
        const scannerBody = document.querySelector('.scanner-body');
        scannerBody.innerHTML = `
            <div class="biodata-container text-white text-center" style="max-width: 800px; width: 100%;">
                <div class="card bg-dark">
                    <div class="card-header bg-success">
                        <h4>✅ QR Code Valid</h4>
                        <p>Data karyawan ditemukan</p>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <img src="${userData.photo}" class="user-photo" alt="Photo">
                            </div>
                            <div class="col-md-8 text-start">
                                <div class="biodata-item">
                                    <span class="biodata-label">Nama:</span>
                                    <span>${userData.name}</span>
                                </div>
                                <div class="biodata-item">
                                    <span class="biodata-label">Jabatan:</span>
                                    <span>${userData.role}</span>
                                </div>
                                <div class="biodata-item">
                                    <span class="biodata-label">Divisi:</span>
                                    <span>${userData.division}</span>
                                </div>
                                <div class="biodata-item">
                                    <span class="biodata-label">Cabang:</span>
                                    <span class="badge bg-success">${userData.branch}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-warning">Sekarang ambil foto selfie untuk menyelesaikan absensi</p>
                            <button class="btn btn-primary btn-lg" onclick="startSelfieCamera()">
                                📸 Lanjutkan ke Foto Selfie
                            </button>
                            <button class="btn btn-secondary btn-lg ms-2" onclick="backToQRScanner()">
                                ↩ Batalkan
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
                    <p class="text-white">Ambil foto untuk absensi</p>
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

        // Start front camera for selfie
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
            
            // Play video when loaded
            video.onloadedmetadata = () => {
                video.play();
            };
        })
        .catch(err => {
            console.error('Camera error:', err);
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
            backToBiodata();
        });
    }

    function captureSelfie() {
        const video = document.getElementById('selfie-video');
        const canvas = document.getElementById('selfie-canvas');
        
        if (!video.videoWidth || !video.videoHeight) {
            alert('Kamera belum siap. Tunggu sebentar.');
            return;
        }

        const context = canvas.getContext('2d');

        // Set canvas size same as video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw video frame to canvas (mirrored for selfie)
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert to base64
        const photoData = canvas.toDataURL('image/jpeg', 0.8);
        
        // Stop camera
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
        }

        // Send selfie to server
        completeAttendanceWithSelfie(photoData);
    }

    function completeAttendanceWithSelfie(selfiePhoto) {
        showResult(`
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h4>Menyimpan absensi...</h4>
                <p>Mohon tunggu</p>
            </div>
        `);

        // Kirim selfie ke server bersama data sebelumnya
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
                        <div class="mb-3">✅</div>
                        <h4 class="text-success">ABSENSI BERHASIL!</h4>
                        <p>${data.message}</p>
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
                    <div class="mb-3">❌</div>
                    <h4 class="text-danger">GAGAL</h4>
                    <p>Gagal menyimpan absensi: ${err.message}</p>
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
        document.getElementById('qr-scanner').style.display = 'block';
        document.getElementById('selfie-camera').style.display = 'none';
        document.getElementById('scanner-step').textContent = 'Langkah 1: Scan QR Code Karyawan';
        document.getElementById('footer-info').textContent = 'Pastikan QR Code dalam frame kamera';
        
        // Reset scanner body to original state
        const scannerBody = document.querySelector('.scanner-body');
        scannerBody.innerHTML = `
            <div id="qr-scanner">
                <div id="reader"></div>
            </div>
            <div id="selfie-camera" style="display: none;"></div>
        `;
        
        // Re-initialize QR scanner
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
        // Clear previous scanner if exists
        const readerElement = document.getElementById('reader');
        if (readerElement.innerHTML.trim() !== '') {
            readerElement.innerHTML = '';
        }

        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                console.log('Available cameras:', cameras);
                
                // Prioritize back camera
                let backCamera = cameras.find(cam => 
                    cam.label.toLowerCase().includes('back') || 
                    cam.label.toLowerCase().includes('rear')
                );
                
                // If no back camera found, try to find the second camera (usually back)
                if (!backCamera && cameras.length > 1) {
                    backCamera = cameras[1];
                }
                
                // Fallback to first camera
                if (!backCamera) {
                    backCamera = cameras[0];
                }
                
                const cameraId = backCamera.id;
                console.log('Using camera:', backCamera.label, cameraId);
                
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", 
                    { 
                        fps: 10, 
                        qrbox: { 
                            width: 250, 
                            height: 250 
                        },
                        aspectRatio: 1.0,
                        supportedScanTypes: [
                            Html5QrcodeScanType.SCAN_TYPE_QR_CODE
                        ]
                    }, 
                    /* verbose= */ false
                );
                
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                
            } else {
                document.getElementById('reader').innerHTML = `
                    <div class="text-center text-white p-5">
                        <h4>❌ Kamera tidak ditemukan</h4>
                        <p>Pastikan kamera terhubung dan diizinkan</p>
                        <button class="btn btn-warning mt-3" onclick="initializeQRScanner()">
                            🔄 Coba Lagi
                        </button>
                    </div>
                `;
            }
        }).catch(err => {
            console.error('Camera access error:', err);
            document.getElementById('reader').innerHTML = `
                <div class="text-center text-white p-5">
                    <h4>❌ Gagal mengakses kamera</h4>
                    <p>Izinkan akses kamera di browser settings</p>
                    <p class="small">${err.message}</p>
                    <button class="btn btn-warning mt-3" onclick="initializeQRScanner()">
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