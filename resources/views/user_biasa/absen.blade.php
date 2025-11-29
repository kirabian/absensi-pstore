@extends('layout.master')

@section('title')
    Absen Mandiri ({{ ucfirst($mode) }})
@endsection

@section('heading')
    Absen Mandiri ({{ ucfirst($mode) }})
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Form Absen Mandiri ({{ ucfirst($mode) }})</h4>
                        <div class="badge badge-dark badge-pill">
                            <i class="mdi mdi-camera me-1"></i>Selfie dengan Efek
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        Ambil foto selfie untuk melakukan absen <strong>{{ strtoupper($mode) }}</strong>. 
                        Pilih efek yang diinginkan, pastikan wajah terlihat jelas, dan lokasi akurat.
                    </p>

                    <form class="forms-sample" action="{{ route('self.attend.store') }}" method="POST"
                        enctype="multipart/form-data" id="attendance-form">
                        @csrf

                        {{-- Camera Section --}}
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="fw-semibold mb-3">Kamera Selfie <span class="text-danger">*</span></label>
                                    
                                    {{-- Live Camera --}}
                                    <div class="camera-container text-center mb-3">
                                        <div id="live-camera" class="camera-preview mb-3">
                                            <video id="video" autoplay playsinline class="img-fluid rounded shadow-sm"></video>
                                            <canvas id="canvas" class="d-none"></canvas>
                                            <div class="preview-overlay">
                                                <div class="watermark-timestamp" id="watermark-live">
                                                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i:s') }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Preview Image --}}
                                        <div id="image-preview" class="camera-preview mb-3 d-none">
                                            <img id="preview-image" src="" alt="Preview Foto" class="img-fluid rounded shadow-sm">
                                            <div class="preview-overlay">
                                                <div class="watermark-timestamp" id="watermark-preview">
                                                    {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i:s') }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Camera Controls --}}
                                        <div class="camera-controls d-flex gap-2 justify-content-center flex-wrap mt-3">
                                            <button type="button" id="start-camera" class="btn btn-dark">
                                                <i class="mdi mdi-camera me-1"></i>Aktifkan Kamera
                                            </button>
                                            <button type="button" id="capture-btn" class="btn btn-success d-none">
                                                <i class="mdi mdi-camera-iris me-1"></i>Ambil Foto
                                            </button>
                                            <button type="button" id="retake-btn" class="btn btn-warning d-none">
                                                <i class="mdi mdi-camera-retake me-1"></i>Ambil Ulang
                                            </button>
                                            <button type="button" id="switch-camera" class="btn btn-info d-none">
                                                <i class="mdi mdi-camera-switch me-1"></i>Ganti Kamera
                                            </button>
                                        </div>

                                        {{-- Hidden Input for Photo --}}
                                        <input type="hidden" name="photo" id="photo-data">
                                    </div>

                                    {{-- Filter Effects --}}
                                    <div class="filter-section mt-4">
                                        <label class="fw-semibold mb-3">Pilih Efek Kamera</label>
                                        <div class="filter-grid row g-2" id="filter-selector">
                                            <div class="col-4 col-sm-3 col-md-2">
                                                <div class="filter-option active" data-filter="none">
                                                    <div class="filter-preview normal"></div>
                                                    <small class="d-block text-center mt-1">Normal</small>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-3 col-md-2">
                                                <div class="filter-option" data-filter="glasses">
                                                    <div class="filter-preview glasses"></div>
                                                    <small class="d-block text-center mt-1">Kacamata</small>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-3 col-md-2">
                                                <div class="filter-option" data-filter="sunglasses">
                                                    <div class="filter-preview sunglasses"></div>
                                                    <small class="d-block text-center mt-1">Kacamata Hitam</small>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-3 col-md-2">
                                                <div class="filter-option" data-filter="hat">
                                                    <div class="filter-preview hat"></div>
                                                    <small class="d-block text-center mt-1">Topi</small>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-3 col-md-2">
                                                <div class="filter-option" data-filter="vintage">
                                                    <div class="filter-preview vintage"></div>
                                                    <small class="d-block text-center mt-1">Vintage</small>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-3 col-md-2">
                                                <div class="filter-option" data-filter="sepia">
                                                    <div class="filter-preview sepia"></div>
                                                    <small class="d-block text-center mt-1">Sepia</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Location Info --}}
                            <div class="col-md-4">
                                <div class="location-info p-4 rounded bg-light h-100">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="mdi mdi-map-marker-outline me-2"></i>Informasi Lokasi
                                    </h6>

                                    <div id="location-status" class="alert alert-info mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="mdi mdi-loading mdi-spin me-2"></i>
                                            <span>Sedang mengambil lokasi...</span>
                                        </div>
                                    </div>

                                    <div id="location-details" class="d-none">
                                        <div class="mb-2">
                                            <small class="text-muted">Koordinat:</small>
                                            <div class="fw-semibold" id="coordinates-display"></div>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Akurasi:</small>
                                            <div class="fw-semibold" id="accuracy-display"></div>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Waktu:</small>
                                            <div class="fw-semibold" id="time-display">
                                                {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i:s') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hidden Inputs for Location --}}
                                    <input type="hidden" id="latitude" name="latitude">
                                    <input type="hidden" id="longitude" name="longitude">
                                    <input type="hidden" id="accuracy" name="accuracy">
                                </div>
                            </div>
                        </div>

                        {{-- Additional Information --}}
                        <div class="form-group mb-4">
                            <label class="fw-semibold mb-2">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Tambahkan catatan jika diperlukan, misal: Work From Home, meeting di luar kantor, dll."></textarea>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" id="submit-button" class="btn btn-dark" disabled>
                                <i class="mdi mdi-send me-1"></i>Kirim Absen {{ ucfirst($mode) }}
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">
                                <i class="mdi mdi-close me-1"></i>Batal
                            </a>
                        </div>
                    </form>
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
            padding: 1.5rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .camera-preview {
            position: relative;
            max-width: 100%;
            margin: 0 auto;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }

        #video, #preview-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
        }

        .preview-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            padding: 1rem;
            border-radius: 0 0 8px 8px;
        }

        .watermark-timestamp {
            color: white;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
        }

        .filter-grid {
            margin-top: 1rem;
        }

        .filter-option {
            padding: 0.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-option:hover {
            border-color: #000;
            transform: translateY(-2px);
        }

        .filter-option.active {
            border-color: #000;
            background: #000;
            color: white;
        }

        .filter-preview {
            width: 100%;
            height: 60px;
            border-radius: 6px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 0.25rem;
        }

        .filter-preview.normal::before { content: "😊"; }
        .filter-preview.glasses::before { content: "🤓"; }
        .filter-preview.sunglasses::before { content: "😎"; }
        .filter-preview.hat::before { content: "👒"; }
        .filter-preview.vintage::before { content: "📸"; }
        .filter-preview.sepia::before { content: "🟫"; }

        .camera-controls {
            margin-top: 1rem;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-dark {
            background: #000;
            border: 2px solid #000;
        }

        .btn-dark:hover {
            background: #333;
            border-color: #333;
            transform: translateY(-1px);
        }

        .location-info {
            border-left: 4px solid #000;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.18.0/dist/tf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@0.0.1/dist/face-landmarks-detection.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Elements
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const startCameraBtn = document.getElementById('start-camera');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');
        const switchCameraBtn = document.getElementById('switch-camera');
        const liveCamera = document.getElementById('live-camera');
        const imagePreview = document.getElementById('image-preview');
        const previewImage = document.getElementById('preview-image');
        const photoData = document.getElementById('photo-data');
        const submitButton = document.getElementById('submit-button');
        const filterOptions = document.querySelectorAll('.filter-option');
        
        // Location elements
        const locationStatus = document.getElementById('location-status');
        const locationDetails = document.getElementById('location-details');
        const coordinatesDisplay = document.getElementById('coordinates-display');
        const accuracyDisplay = document.getElementById('accuracy-display');
        const timeDisplay = document.getElementById('time-display');
        const watermarkLive = document.getElementById('watermark-live');
        const watermarkPreview = document.getElementById('watermark-preview');

        // Variables
        let currentStream = null;
        let currentFacingMode = 'user';
        let currentFilter = 'none';
        let model = null;
        let isCameraActive = false;

        // Filter configurations
        const filters = {
            none: { 
                draw: (ctx, faces) => {} // No filter
            },
            glasses: {
                draw: (ctx, faces) => {
                    faces.forEach(face => {
                        const keypoints = face.keypoints;
                        const leftEye = keypoints.find(kp => kp.name === 'leftEye');
                        const rightEye = keypoints.find(kp => kp.name === 'rightEye');
                        
                        if (leftEye && rightEye) {
                            const eyeDistance = Math.abs(rightEye.x - leftEye.x);
                            const glassesWidth = eyeDistance * 1.8;
                            const glassesHeight = eyeDistance * 0.4;
                            
                            // Draw glasses frame
                            ctx.strokeStyle = '#2c3e50';
                            ctx.lineWidth = 3;
                            ctx.fillStyle = 'rgba(52, 152, 219, 0.3)';
                            
                            // Left lens
                            ctx.beginPath();
                            ctx.ellipse(leftEye.x, leftEye.y, glassesWidth/2, glassesHeight, 0, 0, 2 * Math.PI);
                            ctx.stroke();
                            ctx.fill();
                            
                            // Right lens
                            ctx.beginPath();
                            ctx.ellipse(rightEye.x, rightEye.y, glassesWidth/2, glassesHeight, 0, 0, 2 * Math.PI);
                            ctx.stroke();
                            ctx.fill();
                            
                            // Bridge
                            ctx.beginPath();
                            ctx.moveTo(leftEye.x + glassesWidth/2, leftEye.y);
                            ctx.lineTo(rightEye.x - glassesWidth/2, rightEye.y);
                            ctx.stroke();
                        }
                    });
                }
            },
            sunglasses: {
                draw: (ctx, faces) => {
                    faces.forEach(face => {
                        const keypoints = face.keypoints;
                        const leftEye = keypoints.find(kp => kp.name === 'leftEye');
                        const rightEye = keypoints.find(kp => kp.name === 'rightEye');
                        
                        if (leftEye && rightEye) {
                            const eyeDistance = Math.abs(rightEye.x - leftEye.x);
                            const glassesWidth = eyeDistance * 1.8;
                            const glassesHeight = eyeDistance * 0.6;
                            
                            // Draw sunglasses
                            ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
                            ctx.strokeStyle = '#000';
                            ctx.lineWidth = 2;
                            
                            // Left lens
                            ctx.beginPath();
                            ctx.ellipse(leftEye.x, leftEye.y, glassesWidth/2, glassesHeight, 0, 0, 2 * Math.PI);
                            ctx.fill();
                            ctx.stroke();
                            
                            // Right lens
                            ctx.beginPath();
                            ctx.ellipse(rightEye.x, rightEye.y, glassesWidth/2, glassesHeight, 0, 0, 2 * Math.PI);
                            ctx.fill();
                            ctx.stroke();
                            
                            // Bridge
                            ctx.beginPath();
                            ctx.moveTo(leftEye.x + glassesWidth/2, leftEye.y);
                            ctx.lineTo(rightEye.x - glassesWidth/2, rightEye.y);
                            ctx.stroke();
                        }
                    });
                }
            },
            hat: {
                draw: (ctx, faces) => {
                    faces.forEach(face => {
                        const box = face.box;
                        const hatWidth = box.width * 1.5;
                        const hatHeight = box.height * 0.4;
                        
                        // Draw hat
                        ctx.fillStyle = '#e74c3c';
                        ctx.strokeStyle = '#c0392b';
                        ctx.lineWidth = 3;
                        
                        // Hat brim
                        ctx.beginPath();
                        ctx.ellipse(box.x + box.width/2, box.y - hatHeight/2, hatWidth/2, hatHeight/3, 0, 0, 2 * Math.PI);
                        ctx.fill();
                        ctx.stroke();
                        
                        // Hat top
                        ctx.beginPath();
                        ctx.ellipse(box.x + box.width/2, box.y - hatHeight, hatWidth/3, hatHeight/2, 0, 0, 2 * Math.PI);
                        ctx.fill();
                        ctx.stroke();
                    });
                }
            },
            vintage: {
                draw: (ctx, faces) => {
                    // Apply vintage filter effect
                    ctx.fillStyle = 'rgba(255, 220, 150, 0.1)';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    
                    ctx.fillStyle = 'rgba(150, 150, 255, 0.1)';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                }
            },
            sepia: {
                draw: (ctx, faces) => {
                    // Apply sepia filter effect
                    ctx.fillStyle = 'rgba(112, 66, 20, 0.3)';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                }
            }
        };

        // Update timestamp every second
        function updateTimestamp() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            const formatter = new Intl.DateTimeFormat('id-ID', options);
            const timestamp = formatter.format(now);

            if (watermarkLive) watermarkLive.textContent = timestamp;
            if (watermarkPreview) watermarkPreview.textContent = timestamp;
            if (timeDisplay) timeDisplay.textContent = timestamp;
        }

        setInterval(updateTimestamp, 1000);
        updateTimestamp();

        // Camera functions
        async function startCamera(facingMode = 'user') {
            try {
                stopCamera();
                
                const constraints = {
                    video: {
                        facingMode: facingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                currentStream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = currentStream;
                
                await video.play();
                
                // Initialize face detection model
                try {
                    model = await faceLandmarksDetection.load(
                        faceLandmarksDetection.SupportedPackages.mediapipeFacemesh,
                        { maxFaces: 1 }
                    );
                } catch (error) {
                    console.warn('Face detection model failed to load:', error);
                }

                startCameraBtn.classList.add('d-none');
                captureBtn.classList.remove('d-none');
                switchCameraBtn.classList.remove('d-none');
                isCameraActive = true;

                // Start face detection loop
                detectFaces();

            } catch (error) {
                console.error('Error starting camera:', error);
                alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin akses kamera.');
            }
        }

        function stopCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            isCameraActive = false;
        }

        async function detectFaces() {
            if (!isCameraActive || !model) return;

            try {
                const faces = await model.estimateFaces({
                    input: video,
                    returnTensors: false,
                    flipHorizontal: false,
                    predictIrises: false
                });

                // Draw filter if faces detected
                if (faces.length > 0 && currentFilter !== 'none') {
                    const ctx = canvas.getContext('2d');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    
                    // Clear canvas
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    // Draw current filter
                    filters[currentFilter].draw(ctx, faces);
                }

            } catch (error) {
                console.warn('Face detection error:', error);
            }

            // Continue detection
            requestAnimationFrame(detectFaces);
        }

        function capturePhoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');

            // Draw video frame
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Apply selected filter
            if (currentFilter !== 'none') {
                try {
                    // For now, we'll apply simple canvas filters for vintage and sepia
                    if (currentFilter === 'vintage') {
                        ctx.fillStyle = 'rgba(255, 220, 150, 0.1)';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                    } else if (currentFilter === 'sepia') {
                        ctx.fillStyle = 'rgba(112, 66, 20, 0.3)';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                    }
                } catch (error) {
                    console.warn('Filter application error:', error);
                }
            }

            // Add watermark
            ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
            ctx.fillRect(0, canvas.height - 40, canvas.width, 40);
            ctx.fillStyle = 'white';
            ctx.font = '16px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(watermarkLive.textContent, canvas.width / 2, canvas.height - 15);

            // Convert to data URL
            const dataURL = canvas.toDataURL('image/jpeg', 0.8);
            photoData.value = dataURL;
            previewImage.src = dataURL;

            // Show preview, hide live camera
            liveCamera.classList.add('d-none');
            imagePreview.classList.remove('d-none');
            captureBtn.classList.add('d-none');
            retakeBtn.classList.remove('d-none');
            switchCameraBtn.classList.add('d-none');

            updateSubmitButton();
        }

        function retakePhoto() {
            imagePreview.classList.add('d-none');
            liveCamera.classList.remove('d-none');
            captureBtn.classList.remove('d-none');
            retakeBtn.classList.add('d-none');
            switchCameraBtn.classList.remove('d-none');
            photoData.value = '';
            updateSubmitButton();
        }

        function switchCamera() {
            currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
            startCamera(currentFacingMode);
        }

        // Filter selection
        filterOptions.forEach(option => {
            option.addEventListener('click', function() {
                filterOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
            });
        });

        // Get user location
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        document.getElementById('accuracy').value = accuracy;

                        coordinatesDisplay.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        accuracyDisplay.textContent = `± ${Math.round(accuracy)} meter`;

                        locationStatus.innerHTML = `
                            <div class="d-flex align-items-center text-success">
                                <i class="mdi mdi-check-circle-outline me-2"></i>
                                <span>Lokasi berhasil didapat!</span>
                            </div>
                        `;
                        locationDetails.classList.remove('d-none');

                        updateSubmitButton();
                    },
                    function (error) {
                        let errorMessage = 'Gagal mengambil lokasi. ';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Izinkan akses lokasi di browser Anda.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Informasi lokasi tidak tersedia.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Permintaan lokasi timeout.';
                                break;
                            default:
                                errorMessage += 'Error tidak diketahui.';
                        }

                        locationStatus.innerHTML = `
                            <div class="d-flex align-items-center text-danger">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                <span>${errorMessage}</span>
                            </div>
                        `;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                locationStatus.innerHTML = `
                    <div class="d-flex align-items-center text-danger">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        <span>Browser Anda tidak mendukung Geolocation.</span>
                    </div>
                `;
            }
        }

        // Update submit button state
        function updateSubmitButton() {
            const hasPhoto = photoData.value !== '';
            const hasLocation = document.getElementById('latitude').value !== '';

            submitButton.disabled = !(hasPhoto && hasLocation);
        }

        // Event listeners
        startCameraBtn.addEventListener('click', () => startCamera());
        captureBtn.addEventListener('click', capturePhoto);
        retakeBtn.addEventListener('click', retakePhoto);
        switchCameraBtn.addEventListener('click', switchCamera);

        // Form submission
        document.getElementById('attendance-form').addEventListener('submit', function (e) {
            if (!photoData.value) {
                e.preventDefault();
                alert('Silakan ambil foto terlebih dahulu.');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Mengirim...';
            submitBtn.disabled = true;
        });

        // Initialize
        getLocation();
        updateSubmitButton();

        // Cleanup on page unload
        window.addEventListener('beforeunload', stopCamera);
    });
</script>
@endpush