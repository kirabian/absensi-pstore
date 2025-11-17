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
    .camera-container {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 2rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }
    
    #reader {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    
    #reader video {
        width: 100%;
        height: auto;
        border-radius: 8px;
        border: 2px solid #000;
    }
    
    /* Style untuk scan region */
    #reader__scan_region {
        position: relative;
    }
    
    #reader__dashboard_section {
        text-align: center;
        margin-top: 10px;
    }
    
    #reader__dashboard_section_csr {
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

    // --- 1. LOGIKA SCANNER QR ---
    
    btnStartScan.addEventListener('click', function() {
        console.log('Tombol scan diklik');
        
        // Cek apakah library sudah terload
        if (typeof Html5QrcodeScanner === 'undefined') {
            alert("Error: Library QR Code belum termuat. Cek koneksi internet Anda.");
            return;
        }

        // Ubah UI
        scanPlaceholder.classList.add('d-none');
        readerDiv.classList.remove('d-none');
        btnStartScan.classList.add('d-none');
        btnStopScan.classList.remove('d-none');

        // Inisialisasi Scanner
        try {
            // Hapus konten sebelumnya jika ada
            readerDiv.innerHTML = '';
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: {width: 250, height: 250},
                    aspectRatio: 1.0,
                    supportedScanTypes: [
                        Html5QrcodeScanType.SCAN_TYPE_QR_CODE
                    ]
                },
                false // verbose
            );

            // Render scanner
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            console.log('Scanner berhasil diinisialisasi');
            
        } catch (e) {
            console.error("Error inisialisasi scanner:", e);
            alert("Gagal memulai kamera: " + e.message);
            stopScanner();
        }
    });

    // Fungsi Stop Scanner
    btnStopScan.addEventListener('click', stopScanner);

    function stopScanner() {
        console.log('Menghentikan scanner');
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().then(() => {
                console.log("Scanner berhasil dihentikan");
                resetScannerUI();
            }).catch(error => {
                console.error("Gagal membersihkan scanner:", error);
                resetScannerUI();
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
        html5QrcodeScanner = null;
    }

    // Callback Sukses Scan
    function onScanSuccess(decodedText, decodedResult) {
        console.log('QR Code berhasil di-scan:', decodedText);
        
        // Stop scanner
        stopScanner();

        // Kirim data ke server
        fetch("{{ route('security.get.user') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ qr_code: decodedText })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
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
            console.error('Error fetching user:', err);
            alert("Gagal mengambil data user: " + err.message);
            resetScannerUI();
        });
    }

    // Callback Gagal Scan
    function onScanFailure(error) {
        // Biarkan kosong untuk menghindari spam console
        // console.log('Scan error:', error);
    }

    function showForm(user, division) {
        scanSection.classList.add('d-none');
        formSection.classList.remove('d-none');
        resultName.textContent = user.name;
        resultDivision.textContent = division || 'N/A';
        resultUserId.value = user.id;
    }

    // Fungsi reset global
    window.resetPage = function() {
        stopScanner();
        scanSection.classList.remove('d-none');
        formSection.classList.add('d-none');
        document.getElementById('attendance-form').reset();
        
        // Reset elemen foto
        document.getElementById('camera-preview').classList.add('d-none');
        document.getElementById('camera-placeholder').classList.remove('d-none');
        document.getElementById('photo-buttons').classList.remove('d-none');
        document.getElementById('submit-button').disabled = true;
    }
});
</script>
@endpush