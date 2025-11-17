@extends('layout.master')

@section('title')
    Scan Absensi Security
@endsection

@section('heading')
    Scan Absensi Security
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Form Absensi Security</h4>
                        <div class="badge badge-dark badge-pill">
                            <i class="mdi mdi-shield-account me-1"></i>Security Mode
                        </div>
                    </div>

                    <div id="scan-section">
                        <p class="text-muted mb-4">
                            Silahkan scan QR Code pada HP Karyawan untuk memulai proses absensi.
                        </p>
                        
                        <div class="form-group">
                            <label class="fw-semibold mb-3">Langkah 1: Scan QR Code</label>
                            <div class="camera-container text-center">
                                <div id="reader" class="d-none mb-3" style="border-radius: 8px; overflow: hidden;"></div>
                                
                                <div id="scan-placeholder">
                                    <i class="mdi mdi-qrcode-scan display-4 text-muted mb-3"></i>
                                    <p class="text-muted mb-3">Klik tombol di bawah untuk membuka scanner</p>
                                </div>

                                <button type="button" id="btn-start-scan" class="btn btn-dark">
                                    <i class="mdi mdi-qrcode me-1"></i>Mulai Scan QR
                                </button>
                                
                                <button type="button" id="btn-stop-scan" class="btn btn-danger d-none">
                                    <i class="mdi mdi-close me-1"></i>Tutup Scanner
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="form-section" class="d-none">
                        
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                            <i class="mdi mdi-account-check display-4 me-3"></i>
                            <div>
                                <small class="text-muted">Karyawan Ditemukan:</small>
                                <h4 class="mb-0 fw-bold" id="result-name">Nama User</h4>
                                <span class="badge badge-outline-success mt-1" id="result-division">Divisi</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-light ms-auto" onclick="resetPage()">
                                <i class="mdi mdi-refresh"></i> Reset
                            </button>
                        </div>

                        <form class="forms-sample" action="{{ route('security.attendance.store') }}" method="POST" enctype="multipart/form-data" id="attendance-form">
                            @csrf
                            <input type="hidden" name="user_id" id="result-user-id">

                            <div class="form-group mb-4">
                                <label class="fw-semibold mb-3">Langkah 2: Foto Bukti (Wajah Karyawan) <span class="text-danger">*</span></label>
                                
                                <div class="camera-container text-center">
                                    <div id="camera-preview" class="camera-preview mb-3 d-none">
                                        <img id="preview-image" src="" alt="Preview Foto" class="img-fluid rounded shadow-sm">
                                        <button type="button" id="retake-btn" class="btn btn-danger btn-sm mt-2">
                                            <i class="mdi mdi-camera-retake me-1"></i>Foto Ulang
                                        </button>
                                    </div>

                                    <div id="camera-placeholder" class="camera-placeholder">
                                        <i class="mdi mdi-camera display-4 text-muted mb-3"></i>
                                        <p class="text-muted mb-3">Ambil foto wajah karyawan sebagai bukti</p>
                                    </div>

                                    <input type="file" name="photo" id="photo-input" class="d-none" accept="image/*" capture="environment" required>
                                    
                                    <div id="photo-buttons">
                                        <button type="button" id="capture-btn" class="btn btn-dark">
                                            <i class="mdi mdi-camera me-1"></i>Buka Kamera
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" id="submit-button" class="btn btn-success btn-lg" disabled>
                                    <i class="mdi mdi-check-circle me-1"></i>VERIFIKASI MASUK
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Menggunakan style yang sama persis dengan Absen Mandiri */
        .camera-container {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        .camera-container:hover {
            border-color: #000;
            background: #f1f5f9;
        }
        .camera-preview {
            position: relative;
            max-width: 300px;
            margin: 0 auto;
        }
        #preview-image {
            max-height: 300px;
            object-fit: cover;
            border: 3px solid #000;
        }
        .btn {
            border-radius: 8px; font-weight: 500;
        }
        .btn-dark { background: #000; border: 2px solid #000; color: #fff; }
        .btn-dark:hover { background: #333; border-color: #333; }
        
        /* Style khusus Scanner HTML5-QRCODE */
        #reader video {
            object-fit: cover;
            border-radius: 8px;
        }
        #reader__scan_region {
            background: white;
        }
        /* Sembunyikan elemen UI bawaan library yang jelek */
        #reader__dashboard_section_csr span, 
        #reader__dashboard_section_swaplink {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- VARIABLE SETUP ---
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let html5QrcodeScanner = null;

            // Elements
            const scanSection = document.getElementById('scan-section');
            const btnStartScan = document.getElementById('btn-start-scan');
            const btnStopScan = document.getElementById('btn-stop-scan');
            const scanPlaceholder = document.getElementById('scan-placeholder');
            const readerDiv = document.getElementById('reader');

            const formSection = document.getElementById('form-section');
            const resultName = document.getElementById('result-name');
            const resultDivision = document.getElementById('result-division');
            const resultUserId = document.getElementById('result-user-id');

            // Elements - Evidence
            const photoInput = document.getElementById('photo-input');
            const captureBtn = document.getElementById('capture-btn');
            const previewImage = document.getElementById('preview-image');
            const cameraPreview = document.getElementById('camera-preview');
            const cameraPlaceholder = document.getElementById('camera-placeholder');
            const retakeBtn = document.getElementById('retake-btn');
            const photoButtons = document.getElementById('photo-buttons');
            const submitButton = document.getElementById('submit-button');

            // --- 1. LOGIKA SCANNER QR (DENGAN DEBUGGING) ---
            
            btnStartScan.addEventListener('click', function() {
                // Cek apakah library sudah terload
                if (typeof Html5QrcodeScanner === 'undefined') {
                    alert("Error: Library QR Code belum termuat. Cek koneksi internet Anda.");
                    return;
                }

                // Ubah UI dulu
                scanPlaceholder.classList.add('d-none');
                readerDiv.classList.remove('d-none');
                btnStartScan.classList.add('d-none');
                btnStopScan.classList.remove('d-none');

                // Inisialisasi Scanner
                try {
                    html5QrcodeScanner = new Html5QrcodeScanner(
                        "reader", 
                        { 
                            fps: 10, 
                            qrbox: {width: 250, height: 250},
                            aspectRatio: 1.0
                        },
                        false
                    );

                    // Render dengan penanganan Error startup
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                    
                } catch (e) {
                    alert("Gagal memulai kamera: " + e);
                    stopScanner();
                }
            });

            // Fungsi Stop Scanner
            btnStopScan.addEventListener('click', stopScanner);

            function stopScanner() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear().then(() => {
                        console.log("Scanner stopped");
                        resetScannerUI();
                    }).catch(error => {
                        console.error("Failed to clear scanner", error);
                        resetScannerUI(); // Tetap reset UI meski error
                    });
                } else {
                    resetScannerUI();
                }
            }

            function resetScannerUI() {
                readerDiv.classList.add('d-none');
                scanPlaceholder.classList.remove('d-none');
                btnStartScan.classList.remove('d-none');
                btnStopScan.classList.add('d-none');
                readerDiv.innerHTML = "";
            }

            // Callback Sukses Scan
            function onScanSuccess(decodedText, decodedResult) {
                console.log(`Scan result: ${decodedText}`);
                
                // Matikan scanner
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear(); 
                }

                // Kirim ke Server
                fetch("{{ route('security.get.user') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({ qr_code: decodedText })
                })
                .then(response => {
                    if (!response.ok) throw new Error("Server Error");
                    return response.json();
                })
                .then(data => {
                    if (data.user) {
                        showForm(data.user, data.division_name);
                    } else {
                        alert("QR Code tidak valid atau User tidak ditemukan!");
                        resetScannerUI();
                    }
                })
                .catch(err => {
                    alert("Gagal mengambil data user: " + err);
                    resetScannerUI();
                });
            }

            // Callback Gagal Scan (Sengaja dikosongkan agar tidak spam)
            function onScanFailure(error) {
                // console.warn(`Code scan error = ${error}`);
            }

            function showForm(user, division) {
                scanSection.classList.add('d-none');
                formSection.classList.remove('d-none');
                resultName.textContent = user.name;
                resultDivision.textContent = division || 'N/A';
                resultUserId.value = user.id;
            }

            window.resetPage = function() {
                window.location.reload();
            }

            // --- 2. LOGIKA FOTO BUKTI (Native Input) ---
            
            // Saat tombol "Buka Kamera" diklik
            captureBtn.addEventListener('click', function() {
                photoInput.click();
            });

            // Saat file dipilih/foto diambil
            photoInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        cameraPreview.classList.remove('d-none');
                        cameraPlaceholder.classList.add('d-none');
                        photoButtons.classList.add('d-none');
                        submitButton.disabled = false;
                        submitButton.removeAttribute('disabled');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Tombol Foto Ulang
            retakeBtn.addEventListener('click', function() {
                photoInput.value = '';
                cameraPreview.classList.add('d-none');
                cameraPlaceholder.classList.remove('d-none');
                photoButtons.classList.remove('d-none');
                submitButton.disabled = true;
                submitButton.setAttribute('disabled', true);
            });

            // Loading saat submit
            document.getElementById('attendance-form').addEventListener('submit', function() {
                const btn = document.getElementById('submit-button');
                btn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Memproses...';
                btn.disabled = true;
            });
        });
    </script>
@endpush