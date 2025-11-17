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
        }
        .scanner-container {
            max-width: 500px;
            margin: 0 auto;
        }
        #reader {
            width: 100%;
            min-height: 300px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Security Scanner</span>
            <div class="navbar-nav">
                <a class="nav-link text-white" href="{{ route('dashboard') }}">← Kembali ke Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white text-center">
                        <h5 class="mb-0">Security Scanner</h5>
                    </div>
                    <div class="card-body text-center">
                        
                        {{-- Area Kamera --}}
                        <div id="reader"></div>

                        {{-- Area Hasil Scan --}}
                        <div id="result-area" class="mt-4" style="display: none;">
                            <div class="alert" id="alert-box"></div>
                            
                            {{-- Card Detail Karyawan --}}
                            <div id="user-detail" class="card mt-2 text-start" style="display: none;">
                                <div class="card-body d-flex align-items-center">
                                    <img id="user-photo" src="" class="rounded-circle me-3" width="60" height="60" style="object-fit: cover; border: 2px solid #ddd;">
                                    <div>
                                        <h5 class="card-title mb-1" id="user-name"></h5>
                                        <p class="card-text mb-0 text-muted">
                                            <small><span id="user-role"></span> - <span id="user-div"></span></small>
                                        </p>
                                        <span class="badge bg-success" id="user-branch"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-muted small">
                            Pastikan pencahayaan cukup saat memindai QR Code.
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
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response:', data);
                const resultArea = document.getElementById('result-area');
                const alertBox = document.getElementById('alert-box');
                const userDetail = document.getElementById('user-detail');

                resultArea.style.display = 'block';

                if (data.status === 'success') {
                    // SUKSES
                    alertBox.className = 'alert alert-success';
                    alertBox.innerHTML = `<strong>✅ VALID!</strong> ${data.message}`;
                    
                    // Isi data
                    document.getElementById('user-name').innerText = data.data.name;
                    document.getElementById('user-role').innerText = data.data.role;
                    document.getElementById('user-div').innerText = data.data.division;
                    document.getElementById('user-branch').innerText = data.data.branch;
                    document.getElementById('user-photo').src = data.data.photo;
                    userDetail.style.display = 'block';
                } else {
                    // GAGAL / ERROR
                    alertBox.className = 'alert alert-danger';
                    alertBox.innerHTML = `<strong>⛔ DITOLAK!</strong> ${data.message}`;
                    userDetail.style.display = 'none';
                }

                // Resume setelah 3 detik
                setTimeout(() => {
                    resultArea.style.display = 'none';
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 3000);
            })
            .catch(err => {
                console.error('Error:', err);
                const resultArea = document.getElementById('result-area');
                const alertBox = document.getElementById('alert-box');
                
                resultArea.style.display = 'block';
                alertBox.className = 'alert alert-danger';
                alertBox.innerHTML = `<strong>⛔ ERROR!</strong> Terjadi kesalahan koneksi.`;
                
                setTimeout(() => {
                    resultArea.style.display = 'none';
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 3000);
            });
        }

        function onScanFailure(error) {
            // Handle scan failure, but don't log too much to avoid spam
            console.log('Scan failed:', error);
        }

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', function() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    supportedScanTypes: [
                        Html5QrcodeScanType.SCAN_TYPE_QR_CODE
                    ]
                }, 
                false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        });
    </script>
</body>
</html>