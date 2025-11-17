@extends('layout.master')

@section('title')
    Scan Absensi Security
@endsection

@section('content')
<div class="row">
    <div class="col-md-5 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title">1. Scan QR Code User</h4>
                <p class="text-muted">Arahkan kamera ke QR Code Karyawan</p>
                
                <div id="reader" style="width: 100%; border-radius: 8px; overflow: hidden;"></div>
                
                <div id="scan-status" class="mt-3 badge badge-outline-info">
                    Siap memindai...
                </div>
                
                <button id="btn-reset" class="btn btn-secondary mt-3 d-none" onclick="resetScanner()">
                    <i class="mdi mdi-refresh me-1"></i> Scan Ulang
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-7 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">2. Verifikasi & Foto Wajah</h4>
                
                <div id="empty-state" class="text-center py-5">
                    <i class="mdi mdi-qrcode-scan display-1 text-muted"></i>
                    <p class="mt-3 text-muted">Silahkan scan QR Code terlebih dahulu.</p>
                </div>

                <div id="attendance-form-section" class="d-none">
                    
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="mdi mdi-account-check display-4 me-3"></i>
                        <div>
                            <h5 class="mb-1" id="user-name">Nama User</h5>
                            <p class="mb-0" id="user-division">Divisi: -</p>
                        </div>
                    </div>

                    <form action="{{ route('security.attendance.store') }}" method="POST" enctype="multipart/form-data" id="form-absen">
                        @csrf
                        <input type="hidden" name="user_id" id="user_id_input">

                        <div class="form-group mt-4">
                            <label class="fw-bold">Ambil Foto Bukti (Wajah Karyawan)</label>
                            
                            <div class="camera-container text-center border rounded p-3 bg-light">
                                <video id="evidence-video" autoplay playsinline style="width: 100%; max-height: 300px; border-radius: 8px; object-fit: cover;"></video>
                                
                                <canvas id="evidence-canvas" class="d-none"></canvas>
                                
                                <img id="evidence-preview" src="" class="img-fluid rounded d-none" style="max-height: 300px;">
                                
                                <input type="file" name="photo" id="photo-input" class="d-none">
                            </div>

                            <div class="text-center mt-2">
                                <button type="button" id="btn-capture" class="btn btn-dark btn-icon-text">
                                    <i class="mdi mdi-camera btn-icon-prepend"></i> Ambil Foto
                                </button>
                                <button type="button" id="btn-retake" class="btn btn-warning btn-icon-text d-none">
                                    <i class="mdi mdi-camera-retake btn-icon-prepend"></i> Foto Ulang
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" id="btn-submit" class="btn btn-primary btn-lg disabled" disabled>
                                <i class="mdi mdi-check-circle me-1"></i> KONFIRMASI MASUK
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    // --- VARIABEL GLOBAL ---
    let html5QrcodeScanner;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Elemen DOM
    const emptyState = document.getElementById('empty-state');
    const formSection = document.getElementById('attendance-form-section');
    const userNameEl = document.getElementById('user-name');
    const userDivEl = document.getElementById('user-division');
    const userIdInput = document.getElementById('user_id_input');
    const scanStatus = document.getElementById('scan-status');
    const btnReset = document.getElementById('btn-reset');

    // Elemen Kamera Bukti
    const evidenceVideo = document.getElementById('evidence-video');
    const evidenceCanvas = document.getElementById('evidence-canvas');
    const evidencePreview = document.getElementById('evidence-preview');
    const photoInput = document.getElementById('photo-input'); // Input file hidden
    const btnCapture = document.getElementById('btn-capture');
    const btnRetake = document.getElementById('btn-retake');
    const btnSubmit = document.getElementById('btn-submit');

    // --- 1. INISIALISASI QR SCANNER ---
    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning supaya tidak spam request
        html5QrcodeScanner.pause(); 
        scanStatus.className = "mt-3 badge badge-warning";
        scanStatus.innerText = "Memproses QR...";

        // Panggil API untuk cek user
        fetch("{{ route('security.get.user') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken
    },
    body: JSON.stringify({ qr_code: decodedText })
})
        .then(response => response.json())
        .then(data => {
            if (data.user) {
                // User Ditemukan
                scanStatus.className = "mt-3 badge badge-success";
                scanStatus.innerText = "User Ditemukan!";
                
                // Tampilkan Form
                showAttendanceForm(data.user, data.division_name);
                
                // Matikan Scanner QR sepenuhnya (opsional, biar hemat baterai/resource)
                // html5QrcodeScanner.clear(); 
                btnReset.classList.remove('d-none');

            } else {
                // QR tidak valid / user tidak ketemu
                alert("User tidak ditemukan atau QR salah.");
                html5QrcodeScanner.resume(); // Lanjut scan
                scanStatus.className = "mt-3 badge badge-danger";
                scanStatus.innerText = "User tidak ditemukan, coba lagi.";
            }
        })
        .catch(err => {
            console.error(err);
            alert("Terjadi kesalahan sistem.");
            html5QrcodeScanner.resume();
        });
    }

    function onScanFailure(error) {
        // Biarkan kosong agar console tidak penuh, library akan terus mencoba scan
    }

    // Render Scanner
    html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", 
        { fps: 10, qrbox: {width: 250, height: 250} },
        /* verbose= */ false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);


    // --- 2. LOGIKA TAMPILAN SETELAH SCAN ---
    function showAttendanceForm(user, division) {
        // Isi Data User
        userNameEl.innerText = user.name;
        userDivEl.innerText = user.role === 'admin' ? 'Administrator' : (division || 'Tanpa Divisi');
        userIdInput.value = user.id;

        // Ganti Tampilan Kanan
        emptyState.classList.add('d-none');
        formSection.classList.remove('d-none');

        // Nyalakan Kamera Bukti (Security Camera)
        startEvidenceCamera();
    }

    function resetScanner() {
        // Reload halaman atau reset state JS (Reload lebih aman untuk membersihkan stream kamera)
        window.location.reload();
    }


    // --- 3. LOGIKA KAMERA BUKTI (EVIDENCE) ---
    let evidenceStream = null;

    async function startEvidenceCamera() {
        try {
            evidenceStream = await navigator.mediaDevices.getUserMedia({ video: true });
            evidenceVideo.srcObject = evidenceStream;
            evidenceVideo.classList.remove('d-none');
            evidencePreview.classList.add('d-none');
        } catch (err) {
            console.error("Error akses kamera bukti:", err);
            alert("Gagal membuka kamera untuk foto bukti. Pastikan izin diberikan.");
        }
    }

    btnCapture.addEventListener('click', function() {
        const context = evidenceCanvas.getContext('2d');
        
        // Set ukuran canvas sesuai video
        evidenceCanvas.width = evidenceVideo.videoWidth;
        evidenceCanvas.height = evidenceVideo.videoHeight;
        
        // Gambar video ke canvas
        context.drawImage(evidenceVideo, 0, 0, evidenceCanvas.width, evidenceCanvas.height);
        
        // Konversi ke Data URL (Preview)
        const dataUrl = evidenceCanvas.toDataURL('image/jpeg');
        evidencePreview.src = dataUrl;
        
        // Tampilkan Preview, Sembunyikan Video
        evidenceVideo.classList.add('d-none');
        evidencePreview.classList.remove('d-none');
        
        // Ubah tombol
        btnCapture.classList.add('d-none');
        btnRetake.classList.remove('d-none');
        
        // Aktifkan tombol submit
        btnSubmit.classList.remove('disabled');
        btnSubmit.removeAttribute('disabled');

        // --- PENTING: Masukkan data gambar ke Input File (sebagai Blob) ---
        evidenceCanvas.toBlob(function(blob) {
            // Membuat file object simulasi agar bisa dikirim seperti upload biasa
            const file = new File([blob], "attendance_evidence.jpg", { type: "image/jpeg" });
            
            // Menggunakan DataTransfer untuk mengisi input type="file" secara programatik
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;
        }, 'image/jpeg', 0.9);
    });

    btnRetake.addEventListener('click', function() {
        // Reset UI
        evidencePreview.src = "";
        evidenceVideo.classList.remove('d-none');
        evidencePreview.classList.add('d-none');
        
        btnCapture.classList.remove('d-none');
        btnRetake.classList.add('d-none');
        
        btnSubmit.classList.add('disabled');
        btnSubmit.setAttribute('disabled', true);
        
        // Kosongkan input file
        photoInput.value = '';
    });

    // Matikan stream saat meninggalkan halaman (opsional, untuk kebersihan memori)
    window.addEventListener('beforeunload', function() {
        if (evidenceStream) {
            evidenceStream.getTracks().forEach(track => track.stop());
        }
    });

</script>
@endpush

@push('styles')
<style>
    #reader {
        border: none !important;
    }
    #reader video {
        object-fit: cover;
        border-radius: 8px;
    }
    /* Sembunyikan tombol bawaan library scanner yang kurang bagus */
    #reader__dashboard_section_csr span, 
    #reader__dashboard_section_swaplink {
        display: none !important;
    }
</style>
@endpush