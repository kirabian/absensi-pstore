@extends('layout.master')

@section('title')
    Scan Absensi (Security)
@endsection

@section('heading')
    Scan Absensi (Security)
@endsection

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Form Scan Absensi Security</h4>
                    <div class="badge badge-dark badge-pill">
                        <i class="mdi mdi-qrcode-scan me-1"></i>Scan QR
                    </div>
                </div>

                <p class="text-muted mb-4">
                    Arahkan kamera ke QR Code karyawan. Sistem akan otomatis mendeteksi data dan menampilkan detail sebelum menyimpan absensi.
                </p>

                {{-- QR Scanner Section --}}
                <div class="camera-container mb-4 text-center">
                    <div id="reader" style="width: 300px; margin: 0 auto;"></div>

                    <small class="text-muted d-block mt-3">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Pastikan QR Code terang dan tidak buram.
                    </small>
                </div>

                {{-- Manual Input --}}
                <div class="form-group mb-4">
                    <label class="fw-semibold">Atau Input Manual NIK</label>
                    <input type="number" id="manual-nik" class="form-control" placeholder="Masukkan NIK dan tekan Enter">
                </div>

                {{-- Hasil Scan --}}
                <div id="scan-result" class="d-none">
                    <div class="location-info p-4 rounded bg-light mb-4">
                        <h6 class="fw-semibold mb-3">
                            <i class="mdi mdi-account-check-outline me-2"></i>Data Karyawan
                        </h6>

                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <img id="employee-photo" src="" alt="Foto Karyawan"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-height: 200px; object-fit: cover; border: 3px solid #000;">
                            </div>

                            <div class="col-md-8">
                                <p class="mb-1"><small class="text-muted">Nama</small></p>
                                <h6 class="fw-bold" id="employee-name"></h6>

                                <p class="mb-1 mt-3"><small class="text-muted">NIK</small></p>
                                <h6 class="fw-bold" id="employee-nik"></h6>

                                <p class="mb-1 mt-3"><small class="text-muted">Cabang</small></p>
                                <h6 class="fw-bold" id="employee-branch"></h6>
                            </div>
                        </div>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('security.attend.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="nik" id="form-nik">

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" id="submit-button" class="btn btn-dark">
                                <i class="mdi mdi-send me-1"></i>Simpan Absensi
                            </button>

                            <button type="button" id="reset-button" class="btn btn-outline-dark">
                                <i class="mdi mdi-close me-1"></i>Batal
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
    .camera-container:hover {
        border-color: #000;
        background: #f1f5f9;
    }
    .location-info {
        border-left: 4px solid #000;
    }
    .btn {
        border-radius: 8px;
        font-weight: 500;
    }
    .btn-dark {
        background: #000;
        border: 2px solid #000;
    }
    .btn-outline-dark {
        border: 2px solid #000;
        color: #000;
    }
    .btn-outline-dark:hover {
        background: #000;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" ></script>
<script>
    const resultBox = document.getElementById('scan-result');
    const nameField = document.getElementById('employee-name');
    const nikField = document.getElementById('employee-nik');
    const branchField = document.getElementById('employee-branch');
    const photoField = document.getElementById('employee-photo');
    const formNik = document.getElementById('form-nik');
    const resetButton = document.getElementById('reset-button');

    function showEmployee(data) {
        resultBox.classList.remove('d-none');
        nameField.textContent = data.name;
        nikField.textContent = data.nik;
        branchField.textContent = data.branch;

        // Foto security mengikuti folder Selfie (SAMA)
        photoField.src = "/storage/foto_absen/" + data.photo;

        formNik.value = data.nik;
    }

    function fetchEmployee(nik) {
        fetch(`/security/scan/${nik}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showEmployee(data.data);
                } else {
                    alert(data.message);
                }
            });
    }

    // Manual Input
    document.getElementById('manual-nik').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            fetchEmployee(this.value);
        }
    });

    // Reset
    resetButton.addEventListener('click', () => {
        resultBox.classList.add('d-none');
        formNik.value = '';
    });

    // Scanner
    const scanner = new Html5Qrcode("reader");
    Html5Qrcode.getCameras().then(cameras => {
        scanner.start(
            cameras[0].id,
            { fps: 10, qrbox: 250 },
            decodedText => {
                scanner.stop();
                fetchEmployee(decodedText);
            }
        );
    });
</script>
@endpush
