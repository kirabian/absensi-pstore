<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Scanner - AbsenPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 0;
            margin: 0;
        }
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
    </style>
</head>
<body>
    <div class="scanner-container">
        {{-- Header --}}
        <div class="scanner-header text-center">
            <h2 class="mb-1">🔒 SECURITY SCANNER</h2>
            <p class="mb-0">Arahkan kamera ke QR Code karyawan</p>
        </div>

        {{-- Scanner Area --}}
        <div class="scanner-body">
            <div id="reader"></div>
        </div>

        {{-- Footer --}}
        <div class="scanner-footer">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <small>Pastikan QR Code dalam frame kamera</small>
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

    {{-- Load Library --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrcodeScanner = null;
        let isProcessing = false;

        function onScanSuccess(decodedText, decodedResult) {
            // Prevent multiple scans
            if (isProcessing) return;
            isProcessing = true;
            
            console.log('QR Code detected:', decodedText);
            
            // Pause scanner immediately
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
            }

            // Process the scan
            processQRCode(decodedText);
        }

        function processQRCode(qrCode) {
            // Show loading
            showResult(`
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h4>Memproses QR Code...</h4>
                    <p>Mohon tunggu</p>
                </div>
            `);

            // Send to server
            fetch("{{ route('security.validate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                
                if (data.status === 'success') {
                    // SUCCESS - Show user data
                    showResult(`
                        <div class="text-success">
                            <div class="mb-3">
                                <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="text-success">✅ BERHASIL</h4>
                            <p><strong>${data.message}</strong></p>
                        </div>
                        <div class="mt-4 p-3 bg-light rounded">
                            <img src="${data.data.photo}" class="user-photo" alt="Photo">
                            <h5 class="mt-2">${data.data.name}</h5>
                            <p class="mb-1">
                                <strong>${data.data.role}</strong> - ${data.data.division}
                            </p>
                            <span class="badge bg-success">${data.data.branch}</span>
                        </div>
                        <div class="mt-3 text-muted small">
                            Data telah tersimpan secara otomatis
                        </div>
                    `);
                    
                    // Data sudah otomatis tersimpan di server
                    // Tidak perlu action tambahan
                    
                } else {
                    // ERROR
                    showResult(`
                        <div class="text-danger">
                            <div class="mb-3">
                                <i class="fas fa-times-circle" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="text-danger">❌ GAGAL</h4>
                            <p>${data.message}</p>
                        </div>
                    `);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showResult(`
                    <div class="text-danger">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="text-danger">⚠️ ERROR</h4>
                        <p>Terjadi kesalahan sistem</p>
                        <small class="text-muted">${err.message}</small>
                    </div>
                `);
            })
            .finally(() => {
                // Reset processing flag after delay
                setTimeout(() => {
                    isProcessing = false;
                }, 2000);
            });
        }

        function showResult(content) {
            document.getElementById('resultContent').innerHTML = content;
            document.getElementById('resultOverlay').style.display = 'flex';
        }

        function closeResult() {
            document.getElementById('resultOverlay').style.display = 'none';
            
            // Resume scanner
            if (html5QrcodeScanner) {
                html5QrcodeScanner.resume().then(() => {
                    console.log('Scanner resumed');
                }).catch(err => {
                    console.error('Failed to resume scanner:', err);
                });
            }
        }

        function onScanFailure(error) {
            // Biarkan kosong, tidak usah log error terus
        }

        // Initialize scanner
        document.addEventListener('DOMContentLoaded', function() {
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length) {
                    // Use back camera if available, otherwise use first camera
                    const cameraId = cameras.length > 1 ? cameras[cameras.length - 1].id : cameras[0].id;
                    
                    html5QrcodeScanner = new Html5QrcodeScanner(
                        "reader", 
                        { 
                            fps: 10, 
                            qrbox: { 
                                width: 280, 
                                height: 280 
                            },
                            aspectRatio: 1.0,
                            focusMode: "continuous"
                        }, 
                        false
                    );
                    
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                    
                } else {
                    document.getElementById('reader').innerHTML = `
                        <div class="text-center text-white p-5">
                            <h4>❌ Kamera tidak ditemukan</h4>
                            <p>Pastikan kamera terhubung dan diizinkan</p>
                        </div>
                    `;
                }
            }).catch(err => {
                console.error('Camera access error:', err);
                document.getElementById('reader').innerHTML = `
                    <div class="text-center text-white p-5">
                        <h4>❌ Gagal mengakses kamera</h4>
                        <p>Izinkan akses kamera di browser settings</p>
                        <button class="btn btn-warning mt-3" onclick="location.reload()">
                            Coba Lagi
                        </button>
                    </div>
                `;
            });
        });

        // Handle page visibility change (tab switch)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && html5QrcodeScanner) {
                // Pause when tab is hidden
                html5QrcodeScanner.pause();
            } else if (!document.hidden && html5QrcodeScanner) {
                // Resume when tab is visible
                html5QrcodeScanner.resume();
            }
        });
    </script>

    {{-- Font Awesome for icons --}}
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script>
        // Fallback for FontAwesome
        if (!document.querySelector('i.fas')) {
            // If FontAwesome not loaded, use text icons
            const style = document.createElement('style');
            style.textContent = `
                .fa-check-circle:before { content: "✅"; }
                .fa-times-circle:before { content: "❌"; }
                .fa-exclamation-triangle:before { content: "⚠️"; }
            `;
            document.head.appendChild(style);
        }
    </script>
</body>
</html>