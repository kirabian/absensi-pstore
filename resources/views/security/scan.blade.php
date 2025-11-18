<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Scanner - AbsenPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #000; height: 100vh; overflow: hidden; font-family: 'Segoe UI', sans-serif; }
        
        /* --- STEP 1: QR SCANNER UI --- */
        .scanner-container { height: 100%; display: flex; flex-direction: column; }
        .scanner-header { 
            background: #000; color: white; padding: 15px; 
            text-align: center; z-index: 10; border-bottom: 1px solid #333; 
        }
        .scanner-body { flex: 1; position: relative; background: #000; overflow: hidden; }
        
        /* Paksa Video Full Screen */
        #reader { width: 100% !important; height: 100% !important; border: none; }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; }
        
        /* Guide Kotak Hijau di Tengah */
        .scan-guide {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 250px; height: 250px; 
            border: 2px solid rgba(255, 255, 255, 0.6); border-radius: 20px; 
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.5); /* Gelapkan area luar */
            pointer-events: none; z-index: 10;
        }
        .scan-line { 
            width: 100%; height: 3px; background: #00ff88; 
            position: absolute; top: 0; 
            box-shadow: 0 0 4px #00ff88;
            animation: scanMov 2s infinite linear; 
        }
        @keyframes scanMov { 0% {top: 0} 50% {top: 100%} 100% {top: 0} }

        /* --- STEP 2: VERIFICATION MODAL (Full Screen) --- */
        .verification-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #121212; z-index: 50; display: none;
            flex-direction: column; padding: 20px; overflow-y: auto;
        }
        
        .profile-card {
            background: #1e1e1e; border-radius: 15px; padding: 20px;
            text-align: center; color: white; border: 1px solid #333; margin-bottom: 20px;
        }
        .profile-img-db { 
            width: 80px; height: 80px; border-radius: 50%; 
            border: 3px solid #00ff88; object-fit: cover; 
        }
        
        /* Kamera Selfie Security */
        .camera-preview-box {
            width: 100%; height: 350px; background: black; 
            border-radius: 15px; overflow: hidden; position: relative; 
            border: 2px solid #444; margin-bottom: 20px;
        }
        #camera-stream { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); /* Mirror effect */ }
        
        /* Tombol Aksi */
        .action-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn-absen { 
            padding: 15px; font-weight: bold; border-radius: 12px; 
            border: none; color: white; font-size: 1rem;
            transition: transform 0.1s;
        }
        .btn-absen:active { transform: scale(0.98); }
        
        .btn-masuk { background: linear-gradient(45deg, #00b09b, #96c93d); box-shadow: 0 4px 15px rgba(0, 176, 155, 0.3); }
        .btn-pulang { background: linear-gradient(45deg, #ff5f6d, #ffc371); box-shadow: 0 4px 15px rgba(255, 95, 109, 0.3); }
        .btn-malam { grid-column: span 2; background: linear-gradient(45deg, #4b6cb7, #182848); box-shadow: 0 4px 15px rgba(75, 108, 183, 0.3); }

        /* --- STEP 3: RESULT SUCCESS --- */
        .result-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.95); z-index: 100; display: none; 
            align-items: center; justify-content: center; text-align: center; color: white;
        }

        /* Hide HTML5QRcode nonsense */
        #reader__dashboard_section_csr, #reader__dashboard_section_swaplink { display: none !important; }
    </style>
</head>
<body>

    <div class="scanner-container" id="qrSection">
        <div class="scanner-header">
            <h4 class="m-0 fw-bold"><i class="fas fa-shield-alt text-warning me-2"></i>SECURITY SCAN</h4>
            <small class="text-muted">Scan QR Code Karyawan</small>
        </div>
        <div class="scanner-body">
            <div id="reader"></div>
            <div class="scan-guide">
                <div class="scan-line"></div>
            </div>
        </div>
    </div>

    <div class="verification-overlay" id="verifSection">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="text-white m-0">Konfirmasi Absensi</h5>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetScan()">
                <i class="fas fa-times me-1"></i> Batal
            </button>
        </div>

        <div class="profile-card">
            <img src="" id="dbPhoto" class="profile-img-db mb-3" alt="User">
            <h4 id="dbName" class="fw-bold m-0">Nama Karyawan</h4>
            <p id="dbRole" class="text-muted small m-0">Jabatan</p>
            <span id="dbBranch" class="badge bg-primary mt-2">Cabang</span>
        </div>

        <div class="text-white mb-2 fw-bold small text-uppercase">
            <i class="fas fa-camera me-1"></i> Ambil Foto Bukti (Wajib)
        </div>
        <div class="camera-preview-box">
            <video id="camera-stream" autoplay playsinline></video>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>

        <div class="action-buttons">
            <button class="btn-absen btn-masuk" onclick="submitAttendance('masuk')">
                <i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> 
                MASUK
            </button>
            <button class="btn-absen btn-pulang" onclick="submitAttendance('pulang')">
                <i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> 
                PULANG
            </button>
            <button class="btn-absen btn-malam" onclick="submitAttendance('malam')">
                <i class="fas fa-moon fa-lg mb-1 d-block"></i> 
                LEMBUR / MALAM
            </button>
        </div>
    </div>

    <div class="result-overlay" id="resultOverlay">
        <div class="p-4 w-100" style="max-width: 400px;">
            <div id="resultIcon" class="mb-3" style="font-size: 5rem;"></div>
            <h2 id="resultTitle" class="fw-bold mb-2"></h2>
            <p id="resultMessage" class="text-white-50 mb-4 fs-5"></p>
            
            <img id="capturedPhoto" src="" style="width: 200px; height: 200px; object-fit: cover; border-radius: 15px; border: 4px solid white; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.5);" class="mx-auto mb-4">
            
            <button class="btn btn-light w-100 py-3 rounded-pill fw-bold text-uppercase" onclick="resetScan()">
                <i class="fas fa-qrcode me-2"></i> Scan Selanjutnya
            </button>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrcodeScanner = null;
        let currentUserId = null;
        let streamRef = null; // Referensi stream kamera foto

        // --- 1. FUNGSI START QR SCANNER ---
        function startQRScanner() {
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 }, // Area scan
                videoConstraints: { facingMode: "environment" } // Kamera Belakang
            };
            
            // Render scanner ke div #reader
            html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            html5QrcodeScanner.render(onScanSuccess, (error) => {
                // Error scanning (bisa diabaikan biar ga spam console)
            });
        }

        // Callback saat Scan Berhasil
        function onScanSuccess(decodedText) {
            if(html5QrcodeScanner) {
                html5QrcodeScanner.clear(); // Matikan Scanner QR
            }
            checkUser(decodedText); // Validasi ke Server
        }

        // --- 2. CEK USER (AJAX) ---
        function checkUser(qrCode) {
            fetch("{{ route('security.check-user') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showVerificationPage(data.data); // Tampilkan Halaman Foto
                } else {
                    showResult('error', data.message);
                }
            })
            .catch(err => showResult('error', 'Gagal terhubung ke server. Cek koneksi internet.'));
        }

        // --- 3. TAMPILKAN HALAMAN VERIFIKASI & NYALAKAN KAMERA FOTO ---
        function showVerificationPage(user) {
            currentUserId = user.id;
            
            // Isi Data ke Card
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role + ' - ' + user.division;
            document.getElementById('dbBranch').innerText = user.branch;
            document.getElementById('dbPhoto').src = user.photo_url;

            // Ganti Tampilan dari QR ke Verifikasi
            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex';

            // Nyalakan Kamera Depan/Belakang untuk Selfie Bukti
            startCameraStream();
        }

        function startCameraStream() {
            const video = document.getElementById('camera-stream');
            
            // Coba akses kamera (utamakan kamera belakang kalau di tablet/hp security, atau depan jika preferensi)
            // facingMode: "environment" = Belakang, "user" = Depan
            const constraints = { video: { facingMode: "environment", width: { ideal: 640 }, height: { ideal: 480 } } };

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia(constraints)
                .then(function (stream) {
                    streamRef = stream;
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function (error) {
                    console.error("Kamera Error:", error);
                    alert("Gagal mengakses kamera untuk foto bukti. Pastikan izin diberikan.");
                });
            }
        }

        // --- 4. SUBMIT ABSEN (CAPTURE FOTO & KIRIM) ---
        function submitAttendance(type) {
            // 1. Capture Foto dari Video
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            const context = canvas.getContext('2d');
            
            // Set ukuran canvas sesuai video asli
            if (video.videoWidth === 0 || video.videoHeight === 0) {
                alert("Tunggu kamera siap...");
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Gambar video ke canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Convert ke Base64 String
            const imageBase64 = canvas.toDataURL('image/png');

            // 2. Efek Loading di Tombol
            const btn = document.querySelector(`.btn-${type}`);
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> MEMPROSES...';
            btn.disabled = true;

            // Matikan tombol lain biar ga double click
            document.querySelectorAll('.btn-absen').forEach(b => b.disabled = true);

            // 3. Kirim ke Server
            fetch("{{ route('security.store-attendance') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ 
                    user_id: currentUserId,
                    type: type, // 'masuk', 'pulang', atau 'malam'
                    image: imageBase64
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showResult('success', data.message, data.data.photo);
                } else {
                    // Error logic (misal double absen)
                    alert(data.message); 
                    // Reset tombol
                    btn.innerHTML = originalContent;
                    document.querySelectorAll('.btn-absen').forEach(b => b.disabled = false);
                }
            })
            .catch(err => {
                alert("Terjadi kesalahan sistem: " + err);
                btn.innerHTML = originalContent;
                document.querySelectorAll('.btn-absen').forEach(b => b.disabled = false);
            });
        }

        // --- HELPER: HASIL & RESET ---
        function showResult(status, message, photoUrl = null) {
            const overlay = document.getElementById('resultOverlay');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            const img = document.getElementById('capturedPhoto');

            // Matikan kamera foto jika masih nyala
            if(streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }

            overlay.style.display = 'flex';
            msg.innerText = message;

            if(status === 'success') {
                icon.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                title.innerText = "BERHASIL";
                title.className = "fw-bold mb-2 text-success";
                
                if(photoUrl) {
                    img.src = photoUrl;
                    img.style.display = 'block';
                }
            } else {
                icon.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                title.innerText = "GAGAL";
                title.className = "fw-bold mb-2 text-danger";
                img.style.display = 'none';
            }
        }

        function resetScan() {
            // Matikan stream kamera foto
            if(streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }

            // Reset UI ke awal
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'none';
            document.getElementById('qrSection').style.display = 'flex';
            
            // Reset Tombol
            document.querySelectorAll('.btn-absen').forEach(b => {
                b.disabled = false;
                // Reset text manual agak ribet, reload aja lebih bersih, 
                // tapi biar cepet kita biarkan, nanti textnya balik pas showVerificationPage dipanggil lagi
            });
            
            // Kembalikan text tombol manual jika perlu (opsional)
            document.querySelector('.btn-masuk').innerHTML = '<i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK';
            document.querySelector('.btn-pulang').innerHTML = '<i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG';
            document.querySelector('.btn-malam').innerHTML = '<i class="fas fa-moon fa-lg mb-1 d-block"></i> LEMBUR / MALAM';

            // Start QR Scanner lagi
            startQRScanner();
        }

        // Start saat halaman dimuat
        document.addEventListener('DOMContentLoaded', startQRScanner);
    </script>
</body>
</html>