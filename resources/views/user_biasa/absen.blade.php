@extends('layout.master')

@section('title')
    Absen Mandiri
@endsection

@section('heading')
    Absen Mandiri
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Absen Mandiri</h4>
                    <p class="card-description">
                        Ambil foto selfie dan pastikan lokasi Anda akurat.
                    </p>

                    <form class="forms-sample" action="{{ route('self.attend.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label>Ambil Foto Selfie</label>
                            {{-- 'capture="user"' akan meminta kamera selfie di HP --}}
                            <input type="file" name="photo" class="form-control" accept="image/*" capture="user" required>
                            <small class="text-muted">Di HP akan membuka kamera. Di PC akan membuka file.</small>
                        </div>

                        {{-- Input tersembunyi untuk Lokasi --}}
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">

                        <p id="location-status" class="text-warning">Sedang mengambil lokasi...</p>

                        <button type="submit" id="submit-button" class="btn btn-primary me-2" disabled>Kirim Absen</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Ambil lokasi user saat halaman dimuat
        window.onload = function () {
            var locationStatus = document.getElementById('location-status');
            var latInput = document.getElementById('latitude');
            var longInput = document.getElementById('longitude');
            var submitButton = document.getElementById('submit-button');

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    // Sukses
                    latInput.value = position.coords.latitude;
                    longInput.value = position.coords.longitude;
                    locationStatus.textContent = 'Lokasi berhasil didapat!';
                    locationStatus.className = 'text-success';
                    submitButton.disabled = false; // Aktifkan tombol submit
                }, function (error) {
                    // Gagal
                    locationStatus.textContent = 'Gagal mengambil lokasi. Izinkan akses lokasi di browser Anda.';
                    locationStatus.className = 'text-danger';
                });
            } else {
                locationStatus.textContent = 'Browser Anda tidak mendukung Geolocation.';
                locationStatus.className = 'text-danger';
            }
        };
    </script>
@endpush
