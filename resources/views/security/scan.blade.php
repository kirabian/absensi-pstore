@extends('layouts.master') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Scan Absensi Karyawan</h5>
                </div>
                <div class="card-body text-center">

                    {{-- Alert Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Area Kamera --}}
                    <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
                    
                    {{-- Preview Hasil Foto (Hidden by default) --}}
                    <div id="photo-preview-container" class="mt-3" style="display:none;">
                        <label class="form-label fw-bold">Preview Foto:</label><br>
                        <img id="photo-preview" class="img-thumbnail" style="max-width: 200px;">
                        <button type="button" class="btn btn-secondary btn-sm d-block mx-auto mt-2" onclick="resetScanner()">Scan Ulang</button>
                    </div>

                    {{-- Status Text --}}
                    <p class="mt-3 text-muted" id="scan-status">Arahkan kamera ke QR Code Karyawan</p>

                    {{-- Form Absensi (Hidden sampai QR valid ditemukan) --}}
                    <div id="attendance-form" style="display: none;" class="mt-4 text-start border p-3 rounded bg-light">
                        <h5 class="text-primary fw-bold mb-3">Data Karyawan Ditemukan</h5>
                        
                        <form action="{{ route('security.attendance.store') }}" method="POST" enctype="multipart/form-data" id="main-form">
                            @csrf
                            <input type="hidden" name="user_id" id="user_id">
                            
                            {{-- Input File Foto (Hidden, diisi otomatis oleh JS) --}}
                            <input type="file" name="photo" id="photo-input" class="d-none" accept="image/*">

                            <div class="mb-2">
                                <label class="fw-bold">Nama:</label>
                                <input type="text" class="form-control" id="user_name" readonly>
                            </div>

                            <div class="mb-2">
                                <label class="fw-bold">Divisi:</label>
                                <input type="text" class="form-control" id="user_division" readonly>
                            </div>
                            
                            <div class="alert alert-warning mt-2" id="already-absen-alert" style="display:none;">
                                <small>Warning: Karyawan ini tercatat sudah absen hari ini.</small>
                            </div>

                            <hr>
                            <div class="d-grid gap-2">
                                {{-- Tombol ini akan mengambil screenshot dari kamera scanner --}}
                                <button type="button" class="btn btn-success" id="btn-capture-submit">
                                    <i class="bi bi-camera"></i> Ambil Foto & Simpan Absen
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script Library QR Scanner --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    let html5QrcodeScanner;
    const csrfToken = "{{ csrf_token() }}";
    const checkUrl = "{{ route('security.scan.check') }}";

    function onScanSuccess(decodedText, decodedResult) {
        // 1. Stop scanning sementara agar tidak double request
        if(html5QrcodeScanner) {
            html5QrcodeScanner.pause(); 
        }

        document.getElementById('scan-status').innerText = "Memproses QR Code...";

        // 2. Kirim ke Backend
        fetch(checkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ qr_code: decodedText })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if(data.status === 'success') {
                // 3. Tampilkan Data User
                document.getElementById('attendance-form').style.display = 'block';
                document.getElementById('user_id').value = data.user.id;
                document.getElementById('user_name').value = data.user.name;
                document.getElementById('user_division').value = data.division_name;
                
                if(data.already_absen) {
                    document.getElementById('already-absen-alert').style.display = 'block';
                } else {
                    document.getElementById('already-absen-alert').style.display = 'none';
                }

                document.getElementById('scan-status').innerText = "Silakan ambil foto wajah karyawan.";
                
                // Ubah perilaku tombol Capture
                // Kita resume scanner tapi dalam mode video preview saja untuk foto
                html5QrcodeScanner.resume();
            } else {
                alert(data.message);
                html5QrcodeScanner.resume();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memproses QR Code. Pastikan User valid.');
            html5QrcodeScanner.resume();
        });
    }

    function onScanFailure(error) {
        // Handle scan failure, usually better to ignore and keep scanning.
        // console.warn(`Code scan error = ${error}`);
    }

    // --- LOGIKA PENGAMBILAN FOTO DARI VIDEO STREAM ---
    document.getElementById('btn-capture-submit').addEventListener('click', function() {
        // Kita ambil elemen video dari html5-qrcode
        // ID default biasanya 'reader__scan_region' > video
        const videoElement = document.querySelector("#reader video");
        
        if (!videoElement) {
            alert("Kamera tidak terdeteksi! Refresh halaman.");
            return;
        }

        // Buat Canvas untuk menggambar frame video
        const canvas = document.createElement("canvas");
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        const context = canvas.getContext("2d");
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

        // Konversi Canvas ke Blob (File)
        canvas.toBlob(function(blob) {
            // Buat File Object
            const file = new File([blob], "attendance_photo.jpg", { type: "image/jpeg" });

            // Masukkan ke input type="file" secara programatis
            const container = new DataTransfer();
            container.items.add(file);
            document.getElementById('photo-input').files = container.files;

            // Tampilkan preview (opsional)
            document.getElementById('reader').style.display = 'none'; // sembunyikan scanner
            document.getElementById('photo-preview-container').style.display = 'block';
            document.getElementById('photo-preview').src = URL.createObjectURL(blob);

            // Submit Form
            document.getElementById('main-form').submit();
        }, 'image/jpeg', 0.9); // Kualitas 0.9
    });

    function resetScanner() {
        location.reload(); // Cara paling aman untuk reset state
    }

    // Inisialisasi Scanner saat halaman load
    document.addEventListener('DOMContentLoaded', function () {
        html5QrcodeScanner = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        // Mulai kamera belakang
        html5QrcodeScanner.start({ facingMode: "environment" }, config, onScanSuccess);
    });
</script>
@endsection