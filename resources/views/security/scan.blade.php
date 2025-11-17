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
            padding-top: 20px;
        }
        .scanner-wrapper {
            max-width: 500px;
            margin: 0 auto;
        }
        #reader {
            width: 100%;
            height: 400px;
            border: 2px dashed #ccc;
            border-radius: 10px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        #reader video {
            border-radius: 8px;
        }
        .scan-placeholder {
            text-align: center;
            padding: 20px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="scanner-wrapper">
                    <div class="card">
                        <div class="card-header bg-danger text-white text-center py-3">
                            <h4 class="mb-0">🔒 SECURITY SCANNER</h4>
                        </div>
                        <div class="card-body p-4">
                            {{-- Info --}}
                            <div class="alert alert-info text-center">
                                <strong>Pindai QR Code Karyawan</strong><br>
                                <small>Pastikan kamera telah diizinkan dan pencahayaan cukup</small>
                            </div>

                            {{-- Area Kamera --}}
                            <div id="reader">
                                <div class="scan-placeholder">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p>Memuat kamera...</p>
                                </div>
                            </div>

                            {{-- Area Hasil Scan --}}
                            <div id="result-area" class="mt-4" style="display: none;">
                                <div class="alert" id="alert-box"></div>
                                
                                {{-- Card Detail Karyawan --}}
                                <div id="user-detail" class="card mt-3" style="display: none;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <img id="user-photo" src="" class="rounded-circle me-3" width="70" height="70" style="object-fit: cover; border: 3px solid #28a745;">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1" id="user-name">-</h5>
                                                <p class="card-text mb-1 text-muted">
                                                    <small><span id="user-role">-</span> • <span id="user-div">-</span></small>
                                                </p>
                                                <span class="badge bg-success" id="user-branch">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Manual --}}
                            <div class="text-center mt-4">
                                <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                                    ↻ Restart Scanner
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm ms-2">
                                    ← Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Load Library HTML5-QRCode --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrcodeScanner = null;

        function onScanSuccess(decodedText, decodedResult) {
            console.log('QR Code scanned:', decodedText);
            
            // Pause scanner
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
            }

            // Tampilkan loading
            const resultArea = document.getElementById('result-area');
            const alertBox = document.getElementById('alert-box');
            
            resultArea.style.display = 'block';
            alertBox.className = 'alert alert-warning';
            alertBox.innerHTML = '<strong>⏳ Memproses...</strong>';

            // Kirim request ke Controller
            fetch("{{ route('security.validate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ qr_code: decodedText })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response:', data);
                
                if (data.status === 'success') {
                    // SUKSES
                    alertBox.className = 'alert alert-success';
                    alertBox.innerHTML = `<strong>✅ BERHASIL!</strong> ${data.message}`;
                    
                    // Isi data
                    document.getElementById('user-name').innerText = data.data.name;
                    document.getElementById('user-role').innerText = data.data.role;
                    document.getElementById('user-div').innerText = data.data.division;
                    document.getElementById('user-branch').innerText = data.data.branch;
                    document.getElementById('user-photo').src = data.data.photo;
                    document.getElementById('user-detail').style.display = 'block';
                } else {
                    // GAGAL
                    alertBox.className = 'alert alert-danger';
                    alertBox.innerHTML = `<strong>❌ GAGAL!</strong> ${data.message}`;
                    document.getElementById('user-detail').style.display = 'none';
                }

                // Resume setelah 4 detik
                setTimeout(() => {
                    resultArea.style.display = 'none';
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 4000);
            })
            .catch(err => {
                console.error('Fetch Error:', err);
                alertBox.className = 'alert alert-danger';
                alertBox.innerHTML = `<strong>❌ ERROR!</strong> Gagal terhubung ke server.`;
                document.getElementById('user-detail').style.display = 'none';
                
                setTimeout(() => {
                    resultArea.style.display = 'none';
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 3000);
            });
        }

        function onScanFailure(error) {
            // Biarkan kosong, tidak usah log error terus menerus
        }

        // Initialize scanner
        document.addEventListener('DOMContentLoaded', function() {
            // Cek apakah browser support
            if (!Html5Qrcode.getCameras) {
                document.getElementById('reader').innerHTML = `
                    <div class="scan-placeholder text-danger">
                        <p>❌ Browser tidak mendukung QR Scanner</p>
                        <small>Gunakan Chrome, Firefox, atau Safari versi terbaru</small>
                    </div>
                `;
                return;
            }

            // Dapatkan list kamera
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras && cameras.length) {
                    // Start scanner dengan kamera belakang jika ada
                    const cameraId = cameras.length > 1 ? cameras[1].id : cameras[0].id;
                    
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
                    document.getElementById('reader').innerHTML = `
                        <div class="scan-placeholder text-danger">
                            <p>❌ Kamera tidak ditemukan</p>
                            <small>Pastikan kamera terhubung dan diizinkan</small>
                        </div>
                    `;
                }
            }).catch(err => {
                console.error('Camera Error:', err);
                document.getElementById('reader').innerHTML = `
                    <div class="scan-placeholder text-danger">
                        <p>❌ Gagal mengakses kamera</p>
                        <small>Izinkan akses kamera di browser settings</small>
                        <br><br>
                        <button class="btn btn-warning btn-sm" onclick="location.reload()">
                            Coba Lagi
                        </button>
                    </div>
                `;
            });
        });
    </script>
</body>
</html>