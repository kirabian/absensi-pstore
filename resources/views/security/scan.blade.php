<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Scanner - AbsenPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; margin: 0; padding: 0; overflow: hidden; }
        .scanner-container { height: 100vh; display: flex; flex-direction: column; }
        
        /* Header Overlay */
        .scanner-header {
            position: absolute; top: 0; left: 0; width: 100%;
            background: rgba(0, 0, 0, 0.6);
            color: white; padding: 15px; z-index: 10;
            text-align: center; backdrop-filter: blur(5px);
        }
        
        /* Scanner Area Full Screen */
        .scanner-body { flex: 1; position: relative; }
        #reader { width: 100%; height: 100%; border: none; }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; }

        /* Overlay Result */
        .result-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            display: none; align-items: center; justify-content: center;
            z-index: 9999;
        }
        .result-card {
            background: white; width: 90%; max-width: 400px;
            border-radius: 20px; padding: 30px; text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .user-photo {
            width: 120px; height: 120px; border-radius: 50%;
            object-fit: cover; border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-bottom: 15px;
        }
        
        /* Hide default HTML5QRcode strings */
        #reader__dashboard_section_csr span, 
        #reader__dashboard_section_swaplink { display: none !important; }
    </style>
</head>
<body>

    <div class="scanner-container">
        <div class="scanner-header">
            <h4 class="m-0 fw-bold">🔒 SECURITY SCANNER</h4>
            <small>Arahkan ke QR Code Karyawan</small>
        </div>

        <div class="scanner-body">
            <div id="reader"></div>
        </div>
    </div>

    {{-- Result Overlay --}}
    <div class="result-overlay" id="resultOverlay">
        <div class="result-card">
            <div id="resultContent"></div>
            <button class="btn btn-primary w-100 mt-4 py-2 fw-bold" onclick="closeResult()">
                SCAN LAGI
            </button>
        </div>
    </div>

    {{-- Library --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrcodeScanner = null;
        let isProcessing = false;

        // 1. Fungsi saat Scan Berhasil
        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;

            // Pause kamera agar tidak scan berulang kali
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
            }

            processQRCode(decodedText);
        }

        // 2. Kirim Data ke Server
        function processQRCode(qrCode) {
            // Tampilkan Loading
            showResult(`
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h4>Memproses...</h4>
            `);

            fetch("{{ route('security.validate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    // Jika error (403/404/409), lempar ke catch dengan pesan dari server
                    throw new Error(data.message || 'Terjadi kesalahan server.');
                }
                return data;
            })
            .then(data => {
                // === SUKSES ===
                showResult(`
                    <div class="text-success mb-3">
                        <span style="font-size: 4rem;">✅</span>
                    </div>
                    <h3 class="fw-bold text-success mb-1">BERHASIL!</h3>
                    <p class="text-muted mb-4">${data.message}</p>
                    
                    <div class="bg-light p-3 rounded-3">
                        <img src="${data.data.photo}" class="user-photo" alt="User">
                        <h4 class="fw-bold mb-0 text-dark">${data.data.name}</h4>
                        <p class="text-muted small mb-2">${data.data.role} | ${data.data.division}</p>
                        <span class="badge bg-primary">${data.data.branch}</span>
                    </div>
                `);
            })
            .catch(err => {
                // === ERROR ===
                console.error(err);
                showResult(`
                    <div class="text-danger mb-3">
                        <span style="font-size: 4rem;">❌</span>
                    </div>
                    <h3 class="fw-bold text-danger">GAGAL</h3>
                    <p class="text-dark mt-2">${err.message}</p>
                `);
            })
            .finally(() => {
                // Delay sedikit agar tidak double click
                setTimeout(() => { isProcessing = false; }, 1000);
            });
        }

        // Helper: Tampilkan Overlay
        function showResult(html) {
            document.getElementById('resultContent').innerHTML = html;
            document.getElementById('resultOverlay').style.display = 'flex';
        }

        // Helper: Tutup Overlay & Resume Kamera
        function closeResult() {
            document.getElementById('resultOverlay').style.display = 'none';
            if (html5QrcodeScanner) {
                html5QrcodeScanner.resume();
            }
        }

        // 3. Inisialisasi Kamera (Default Belakang)
        document.addEventListener('DOMContentLoaded', function() {
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                // CONFIG INI MEMAKSA KAMERA BELAKANG ("environment")
                videoConstraints: {
                    facingMode: "environment" 
                }
            };

            html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            
            html5QrcodeScanner.render(onScanSuccess, (errorMessage) => {
                // Biarkan kosong agar tidak spam log console saat mencari QR
            });
        });
    </script>
</body>
</html>