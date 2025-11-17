@extends('layout.master') {{-- Pastikan sesuai dengan layout utama Anda --}}

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white text-center">
                    <h5 class="mb-0">Security Scanner</h5>
                </div>
                <div class="card-body text-center">
                    
                    {{-- Area Kamera --}}
                    <div id="reader" style="width: 100%; min-height: 300px;"></div>

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
@endsection

@push('scripts')
{{-- Load Library HTML5-QRCode --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function onScanSuccess(decodedText, decodedResult) {
        // Pause scanner
        html5QrcodeScanner.pause();

        // Kirim request ke Controller
        fetch("{{ route('scan.validate') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ qr_code: decodedText })
        })
        .then(response => response.json())
        .then(data => {
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
                html5QrcodeScanner.resume();
            }, 3000);
        })
        .catch(err => {
            alert("Terjadi kesalahan koneksi.");
            html5QrcodeScanner.resume();
        });
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { fps: 10, qrbox: {width: 250, height: 250} }, false
    );
    html5QrcodeScanner.render(onScanSuccess);
</script>
@endpush