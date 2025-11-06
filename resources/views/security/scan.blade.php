@extends('layout.master')

@section('title')
    Pindai Absensi
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Pemindai QR Code</h4>
                    {{-- 1. Ini adalah 'kotak' untuk menampilkan kamera --}}
                    <div id="qr-reader" style="width: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data User</h4>

                    {{-- 2. Area ini awalnya kosong, akan diisi oleh JavaScript --}}
                    <div id="user-info" style="display:none;">
                        <p><strong>Nama:</strong> <span id="user-name"></span></p>
                        <p><strong>Email:</strong> <span id="user-email"></span></p>
                        <p><strong>Divisi:</strong> <span id="user-division"></span></p>

                        <hr>

                        {{-- 3. Form untuk menyimpan absensi, awalnya disembunyikan --}}
                        <form action="{{ route('security.attendance.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            {{-- ID user akan diisi di sini oleh JS --}}
                            <input type="hidden" id="user_id" name="user_id">

                            <div class="form-group">
                                <label>Ambil Foto (Wajib)</label>
                                <input type="file" name="photo" class="form-control" accept="image/*" capture="user"
                                    required>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                        </form>
                    </div>

                    {{-- Pesan jika user tidak ditemukan --}}
                    <div id="user-not-found" class="alert alert-danger" style="display:none;">
                        User tidak ditemukan!
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- 4. Import Library Scanner --}}
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // 'decodedText' adalah isi dari QR Code (qr_code_value)
            console.log(`Scan berhasil: ${decodedText}`);

            // Matikan scanner
            html5QrcodeScanner.clear();

            // Kirim 'decodedText' ke server Laravel kita
            fetchUserData(decodedText);
        }

        function onScanError(errorMessage) {
            // handle scan error
        }

        // 5. Inisialisasi scanner
        var html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanError);


        // 6. FUNGSI AJAX UNTUK BERTANYA KE LARAVEL
        function fetchUserData(qrCode) {
            // Kirim request ke route 'api.get.user'
            fetch('{{ route("api.get.user") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Penting untuk keamanan
                },
                body: JSON.stringify({ qr_code: qrCode })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        // Tampilkan pesan error jika user tidak ada
                        document.getElementById('user-not-found').style.display = 'block';
                    } else {
                        // Jika user ada, isi datanya dan tampilkan form
                        document.getElementById('user-name').innerText = data.user.name;
                        document.getElementById('user-email').innerText = data.user.email;
                        document.getElementById('user-division').innerText = data.division_name ?? 'N/A';
                        document.getElementById('user_id').value = data.user.id;

                        document.getElementById('user-info').style.display = 'block';
                        document.getElementById('user-not-found').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('user-not-found').style.display = 'block';
                });
        }

    </script>
@endpush
