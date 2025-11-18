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
            height: 100vh; /* Pastikan body full height */
            overflow: hidden; 
        }

        /* Container Utama: Flex Column */
        .scanner-container { 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
        }
        
        /* Header: Tidak Absolute lagi, tapi Relative agar mengambil ruang sendiri */
        .scanner-header {
            position: relative; /* Ubah dari absolute ke relative */
            width: 100%;
            background: #000; /* Hitam pekat agar kontras */
            color: white; 
            padding: 20px 15px; 
            z-index: 10;
            text-align: center; 
            box-shadow: 0 2px 10px rgba(255,255,255,0.1); /* Sedikit shadow pemisah */
            flex-shrink: 0; /* Header jangan mengecil */
        }
        
        /* Body: Mengisi sisa ruang & Center Content */
        .scanner-body { 
            flex: 1; /* Ambil sisa ruang ke bawah */
            display: flex;
            align-items: center; /* Tengahkan Vertikal */
            justify-content: center; /* Tengahkan Horizontal */
            background: #111; /* Sedikit lebih terang dari header */
            position: relative;
            overflow: hidden;
        }

        /* Kotak Kamera */
        #reader { 
            width: 100%; 
            max-width: 600px; /* Batasi lebar di layar besar */
            border: none; 
            border-radius: 15px; /* Sudut kamera tumpul biar bagus */
            overflow: hidden;
        }
        
        /* Memaksa video fill area */
        #reader video { 
            object-fit: cover; 
            width: 100% !important; 
            height: 100% !important; 
            border-radius: 15px;
        }

        /* Overlay Result (Tidak berubah) */
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
        
        /* Sembunyikan elemen bawaan library */
        #reader__dashboard_section_csr span, 
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
        </div>
    </div>

    {{-- Result Overlay --}}
    <div class="result-overlay" id="resultOverlay">
        <div class="result-card">
            <div id="resultContent"></div>
            <button class="btn btn-dark w-100 mt-4 py-3 fw-bold rounded-pill" onclick="closeResult()">
                <i class="fas fa-qrcode me-2"></i> SCAN LAGI
            </button>
        </div>
    </div>

    {{-- Library --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrcodeScanner = null;
        let isProcessing = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;

            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
            }

            processQRCode(decodedText);
        }

        function processQRCode(qrCode) {
            showResult(`
                <div class="spinner-border text-dark mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h4 class="fw-bold">Memproses Data...</h4>
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
                if (!response.ok) throw new Error(data.message || 'Terjadi kesalahan server.');
                return data;
            })
            .then(data => {
                showResult(`
                    <div class="mb-3 text-success">
                        <i class="fas fa-check-circle" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-success mb-2">BERHASIL!</h2>
                    <p class="text-secondary mb-4">${data.message}</p>
                    
                    <div class="bg-light p-4 rounded-3 border">
                        <img src="${data.data.photo}" class="user-photo" alt="User">
                        <h4 class="fw-bold mb-1 text-dark">${data.data.name}</h4>
                        <p class="text-muted small mb-2 text-uppercase fw-bold">${data.data.role} • ${data.data.division}</p>
                        <span class="badge bg-dark px-3 py-2 rounded-pill">${data.data.branch}</span>
                    </div>
                `);
            })
            .catch(err => {
                showResult(`
                    <div class="mb-3 text-danger">
                        <i class="fas fa-times-circle" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="fw-bold text-danger mb-2">GAGAL!</h2>
                    <p class="text-secondary">${err.message}</p>
                `);
            })
            .finally(() => {
                setTimeout(() => { isProcessing = false; }, 1000);
            });
        }

        function showResult(html) {
            document.getElementById('resultContent').innerHTML = html;
            document.getElementById('resultOverlay').style.display = 'flex';
        }

        function closeResult() {
            document.getElementById('resultOverlay').style.display = 'none';
            if (html5QrcodeScanner) {
                html5QrcodeScanner.resume();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
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