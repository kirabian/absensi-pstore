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
        body { background-color: #000; height: 100vh; overflow: hidden; font-family: sans-serif; }
        
        /* STEP 1: QR SCANNER UI */
        .scanner-container { height: 100%; display: flex; flex-direction: column; }
        .scanner-header { background: #000; color: white; padding: 15px; text-align: center; z-index: 10; border-bottom: 1px solid #333; }
        .scanner-body { flex: 1; position: relative; background: #000; }
        #reader { width: 100%; height: 100%; }
        #reader video { object-fit: cover; width: 100% !important; height: 100% !important; }
        
        /* Guide Kotak Hijau */
        .scan-guide {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 250px; height: 250px; border: 2px solid rgba(255, 255, 255, 0.5); border-radius: 20px; pointer-events: none;
        }
        .scan-line { width: 100%; height: 2px; background: #00ff88; position: absolute; top: 0; animation: scanMov 2s infinite; }
        @keyframes scanMov { 0% {top: 0} 100% {top: 100%} }

        /* STEP 2: VERIFICATION MODAL (Full Screen) */
        .verification-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: #121212; z-index: 50; display: none;
            flex-direction: column; padding: 20px; overflow-y: auto;
        }
        
        .profile-card {
            background: #1e1e1e; border-radius: 15px; padding: 20px;
            text-align: center; color: white; border: 1px solid #333; margin-bottom: 20px;
        }
        .profile-img-db { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #00ff88; object-fit: cover; }
        
        /* Kamera Selfie Security */
        .camera-preview-box {
            width: 100%; height: 300px; background: black; border-radius: 15px; overflow: hidden; position: relative; border: 2px solid #444;
        }
        #camera-stream { width: 100%; height: 100%; object-fit: cover; }
        
        /* Tombol Aksi */
        .action-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 20px; }
        .btn-absen { padding: 15px; font-weight: bold; border-radius: 10px; border: none; color: white; }
        .btn-masuk { background: linear-gradient(45deg, #00b09b, #96c93d); }
        .btn-pulang { background: linear-gradient(45deg, #ff5f6d, #ffc371); }
        .btn-malam { grid-column: span 2; background: linear-gradient(45deg, #4b6cb7, #182848); }

        /* STEP 3: RESULT SUCCESS */
        .result-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.95); z-index: 100; display: none; 
            align-items: center; justify-content: center; text-align: center; color: white;
        }
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
            <div class="scan-guide"><div class="scan-line"></div></div>
        </div>
    </div>

    <div class="verification-overlay" id="verifSection">
        <button class="btn btn-outline-secondary mb-3" onclick="resetScan()">
            <i class="fas fa-arrow-left"></i> Batal / Scan Ulang
        </button>

        <div class="profile-card">
            <img src="" id="dbPhoto" class="profile-img-db mb-3">
            <h4 id="dbName" class="fw-bold m-0">Nama Karyawan</h4>
            <p id="dbRole" class="text-muted small m-0">Jabatan</p>
            <span id="dbBranch" class="badge bg-dark mt-2">Cabang</span>
        </div>

        <div class="text-white mb-2 fw-bold"><i class="fas fa-camera"></i> Ambil Foto Bukti:</div>
        <div class="camera-preview-box">
            <video id="camera-stream" autoplay playsinline></video>
            <canvas id="camera-canvas" style="display:none;"></canvas>
        </div>

        <div class="action-buttons">
            <button class="btn-absen btn-masuk" onclick="submitAttendance('masuk')">
                <i class="fas fa-sign-in-alt fa-lg mb-1 d-block"></i> MASUK
            </button>
            <button class="btn-absen btn-pulang" onclick="submitAttendance('pulang')">
                <i class="fas fa-sign-out-alt fa-lg mb-1 d-block"></i> PULANG
            </button>
            <button class="btn-absen btn-malam" onclick="submitAttendance('malam')">
                <i class="fas fa-moon fa-lg mb-1 d-block"></i> LEMBUR / MALAM
            </button>
        </div>
    </div>

    <div class="result-overlay" id="resultOverlay">
        <div class="p-4">
            <div id="resultIcon" class="mb-3" style="font-size: 4rem;"></div>
            <h2 id="resultTitle" class="fw-bold mb-2"></h2>
            <p id="resultMessage" class="text-muted mb-4"></p>
            <img id="capturedPhoto" src="" style="width: 150px; border-radius: 10px; border: 2px solid white; display: none;" class="mx-auto mb-3">
            <button class="btn btn-light w-100 py-3 rounded-pill fw-bold" onclick="resetScan()">SCAN NEXT</button>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let html5QrcodeScanner = null;
        let currentUserId = null;
        let streamRef = null; // Untuk handle kamera foto

        // --- 1. INIT QR SCANNER ---
        function startQRScanner() {
            const config = { fps: 10, videoConstraints: { facingMode: "environment" } };
            html5QrcodeScanner = new Html5QrcodeScanner("reader", config, false);
            html5QrcodeScanner.render(onScanSuccess, () => {});
        }

        function onScanSuccess(decodedText) {
            if(html5QrcodeScanner) html5QrcodeScanner.clear(); // Stop QR Scanner
            checkUser(decodedText); // Panggil Backend
        }

        // --- 2. CEK USER KE SERVER ---
        function checkUser(qrCode) {
            // Show loading or something if needed
            fetch("{{ route('security.check-user') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showVerificationPage(data.data);
                } else {
                    showResult('error', data.message);
                }
            })
            .catch(err => showResult('error', 'Gagal koneksi server'));
        }

        // --- 3. TAMPILKAN HALAMAN VERIFIKASI & NYALAKAN KAMERA FOTO ---
        function showVerificationPage(user) {
            currentUserId = user.id;
            
            // Isi Data Biodata
            document.getElementById('dbName').innerText = user.name;
            document.getElementById('dbRole').innerText = user.role + ' - ' + user.division;
            document.getElementById('dbBranch').innerText = user.branch;
            document.getElementById('dbPhoto').src = user.photo_url;

            // Ganti Tampilan
            document.getElementById('qrSection').style.display = 'none';
            document.getElementById('verifSection').style.display = 'flex';

            // Nyalakan Kamera Biasa (bukan library QR)
            startCameraStream();
        }

        function startCameraStream() {
            const video = document.getElementById('camera-stream');
            if (navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(function (stream) {
                    streamRef = stream;
                    video.srcObject = stream;
                })
                .catch(function (error) {
                    alert("Kamera tidak dapat diakses untuk foto!");
                });
            }
        }

        // --- 4. FUNGSI SUBMIT ABSEN (CAPTURE PHOTO + KIRIM) ---
        function submitAttendance(type) {
            // Capture Foto dari Video Element
            const video = document.getElementById('camera-stream');
            const canvas = document.getElementById('camera-canvas');
            const context = canvas.getContext('2d');
            
            // Set ukuran canvas sama dengan video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Konversi ke Base64
            const imageBase64 = canvas.toDataURL('image/png');

            // Loading State
            const btn = document.querySelector(`.btn-${type}`);
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            // Kirim ke Server
            fetch("{{ route('security.store-attendance') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify({ 
                    user_id: currentUserId,
                    type: type,
                    image: imageBase64
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    showResult('success', data.message, data.data.photo);
                } else {
                    alert(data.message); // Alert error (misal sudah absen)
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                alert("Terjadi kesalahan sistem");
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        // --- HELPER: HASIL & RESET ---
        function showResult(status, message, photoUrl = null) {
            const overlay = document.getElementById('resultOverlay');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const img = document.getElementById('capturedPhoto');

            overlay.style.display = 'flex';
            document.getElementById('resultMessage').innerText = message;

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
            // Matikan kamera foto jika ada
            if(streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
            }

            // Reset UI
            document.getElementById('verifSection').style.display = 'none';
            document.getElementById('resultOverlay').style.display = 'none';
            document.getElementById('qrSection').style.display = 'flex';
            
            // Enable button again
            document.querySelectorAll('.btn-absen').forEach(b => {
                b.disabled = false;
                // Reset text manual if needed, but simple reload logic is fine too
            });

            // Start QR Scanner again
            startQRScanner();
        }

        // Start on Load
        document.addEventListener('DOMContentLoaded', startQRScanner);
    </script>
</body>
</html>