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
            background-color: #000; 
            margin: 0; 
            padding: 0; 
            height: 100vh; 
            overflow: hidden; 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        /* Container Utama */
        .scanner-container { 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
            position: relative;
        }
        
        /* Header Styling */
        .scanner-header {
            background: #000; /* Hitam pekat */
            color: white; 
            padding: 20px 15px; 
            z-index: 20; /* Di atas kamera */
            text-align: center; 
            flex-shrink: 0; 
            border-bottom: 1px solid #333;
        }
        
        /* Area Kamera Mengisi Sisa Ruang (Full Height) */
        .scanner-body { 
            flex: 1; /* Ini kuncinya: ambil sisa ruang ke bawah */
            position: relative;
            background: #000;
            overflow: hidden;
        }

        /* Memaksa Library QR Code mengisi ruangan */
        #reader { 
            width: 100% !important; 
            height: 100% !important; 
            border: none !important; 
            padding: 0 !important;
        }
        
        /* Trik agar Video tidak gepeng tapi 'Cover' area */
        #reader video { 
            object-fit: cover; 
            width: 100% !important; 
            height: 100% !important; 
        }

        /* Membuat Frame Kotak Putih Manual di Tengah (Overlay) */
        .scan-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            z-index: 15;
            pointer-events: none; /* Agar tidak menghalangi klik */
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5); /* Efek gelap di luar kotak */
        }
        
        /* Hiasan Sudut Frame */
        .scan-guide::before, .scan-guide::after {
            content: ''; position: absolute; width: 40px; height: 40px;
            border-color: #00ff88; border-style: solid;
        }
        .scan-guide::before { top: -2px; left: -2px; border-width: 4px 0 0 4px; border-radius: 20px 0 0 0; }
        .scan-guide::after { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; border-radius: 0 0 20px 0; }
        
        /* Animasi Garis Scan */
        .scan-line {
            width: 100%; height: 2px; background: #00ff88;
            position: absolute; top: 0;
            animation: scanMov 2s infinite linear;
            box-shadow: 0 0 10px #00ff88;
        }
        @keyframes scanMov { 0% {top: 10%; opacity: 0;} 50% {opacity: 1;} 100% {top: 90%; opacity: 0;} }

        /* Overlay Result */
        .result-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            display: none; align-items: center; justify-content: center;
            z-index: 9999;
        }
        .result-card {
            background: #1a1a1a; color: white;
            width: 90%; max-width: 350px;
            border-radius: 20px; padding: 30px; text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid #333;
        }
        .user-photo {
            width: 100px; height: 100px; border-radius: 50%;
            object-fit: cover; border: 3px solid #00ff88;
            margin-bottom: 15px;
        }

        /* Sembunyikan elemen bawaan library yang mengganggu */
        #reader__dashboard_section_csr, 
        #reader__dashboard_section_swaplink { display: none !important; }
    </style>
</head>
<body>

    <div class="scanner-container">
        <div class="scanner-header">
            <h4 class="m-0 fw-bold text-uppercase" style="letter-spacing: 1px;">
                <i class="fas fa-shield-alt text-warning me-2"></i>Security Scanner
            </h4>
            <small class="text-muted" style="font-size: 0.85rem;">Arahkan kamera ke QR Code Karyawan</small>
        </div>

        <div class="scanner-body">
            <div id="reader"></div>
            
            <div class="scan-guide">
                <div class="scan-line"></div>
            </div>
        </div>
    </div>

    {{-- Result Overlay --}}
    <div class="result-overlay" id="resultOverlay">
        <div class="result-card">
            <div id="resultContent"></div>
            <button class="btn btn-light w-100 mt-4 py-3 fw-bold rounded-pill" onclick="closeResult()">
                SCAN LAGI
            </button>
        </div>
    </div>

    {{-- Library --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrcodeScanner = null;
        let isProcessing = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;
            if (html5QrcodeScanner) html5QrcodeScanner.pause();
            processQRCode(decodedText);
        }

        function processQRCode(qrCode) {
            showResult(`
                <div class="spinner-border text-light mb-3" role="status"></div>
                <h5>Memproses...</h5>
            `);

            fetch("{{ route('security.validate') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Error.');
                return data;
            })
            .then(data => {
                showResult(`
                    <div class="mb-2 text-success" style="font-size: 3rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="fw-bold text-success">DITERIMA</h3>
                    <p class="text-muted small mb-3">${data.message}</p>
                    
                    <div class="bg-dark p-3 rounded-3 border border-secondary">
                        <img src="${data.data.photo}" class="user-photo" alt="User">
                        <h5 class="fw-bold mb-0 text-white">${data.data.name}</h5>
                        <small class="text-muted text-uppercase">${data.data.role}</small>
                        <div class="mt-2"><span class="badge bg-success">${data.data.branch}</span></div>
                    </div>
                `);
            })
            .catch(err => {
                showResult(`
                    <div class="mb-2 text-danger" style="font-size: 3rem;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3 class="fw-bold text-danger">DITOLAK</h3>
                    <p class="text-light small">${err.message}</p>
                `);
            })
            .finally(() => setTimeout(() => { isProcessing = false; }, 1000));
        }

        function showResult(html) {
            document.getElementById('resultContent').innerHTML = html;
            document.getElementById('resultOverlay').style.display = 'flex';
        }

        function closeResult() {
            document.getElementById('resultOverlay').style.display = 'none';
            if (html5QrcodeScanner) html5QrcodeScanner.resume();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const config = {
                fps: 10,
                // qrbox: { width: 250, height: 250 }, // Kita hapus biar library ga bingung
                // aspectRatio: 1.0, // HAPUS INI AGAR TIDAK KOTAK
                videoConstraints: {
                    facingMode: "environment" 
                }
            };

            html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            html5QrcodeScanner.render(onScanSuccess, () => {});
        });
    </script>
</body>
</html>