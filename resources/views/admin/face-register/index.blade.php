@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h4 class="mb-1">Register Wajah Pengguna</h4>
            <p class="text-muted mb-0">Pilih pengguna, scan wajah, lalu simpan encoding untuk proses peminjaman otomatis.</p>
        </div>
        <div>
            <span class="badge text-bg-light border text-dark px-3 py-2">
                <i class="fa-solid fa-camera me-2"></i>Face Recognition Setup
            </span>
        </div>
    </div>

    @php
        $selectedUserFaceEncoding = trim((string) ($selectedUser->face_encoding ?? ''));
        $selectedUserHasFaceEncoding = $selectedUserFaceEncoding !== '' && $selectedUserFaceEncoding !== '[]';
        $selectedUserHasFaceData = !empty($selectedUser) && ($selectedUserHasFaceEncoding || filled($selectedUser->face_thumbnail_path));
        $faceCameraSettings = $faceCameraSettings ?? [
            'face_camera_preview_size' => 420,
            'face_camera_capture_size' => 512,
            'face_camera_border_radius' => 16,
            'face_camera_background' => '#111111',
            'face_camera_object_fit' => 'cover',
            'face_camera_frame_mode' => 'square',
            'face_camera_horizontal_shift' => 0,
            'face_camera_vertical_shift' => 0,
        ];
        $faceCameraFrameRatio = ($faceCameraSettings['face_camera_frame_mode'] ?? 'square') === 'wide' ? '4 / 3' : '1 / 1';
        $faceCameraShellStyle = sprintf(
            '--face-camera-preview-size: %dpx; --face-camera-border-radius: %dpx; --face-camera-background: %s; --face-camera-object-fit: %s; --face-camera-frame-ratio: %s; --face-camera-horizontal-shift: %d%%; --face-camera-vertical-shift: %d%%;',
            (int) $faceCameraSettings['face_camera_preview_size'],
            (int) $faceCameraSettings['face_camera_border_radius'],
            $faceCameraSettings['face_camera_background'],
            $faceCameraSettings['face_camera_object_fit'],
            $faceCameraFrameRatio,
            (int) $faceCameraSettings['face_camera_horizontal_shift'],
            (int) $faceCameraSettings['face_camera_vertical_shift']
        );
    @endphp

    @if(!empty($selectedUser))
        <div class="alert {{ $selectedUserHasFaceData ? 'alert-warning' : 'alert-info' }} border mb-3">
            <div class="fw-semibold">
                {{ $selectedUserHasFaceData ? 'Data wajah sudah terdaftar' : 'Mode registrasi wajah aktif' }} untuk {{ $selectedUser->name }} ({{ $selectedUser->kelas }})
            </div>
            <div class="small mb-0">
                {{ $selectedUserHasFaceData
                    ? 'Hapus data wajah terlebih dahulu sebelum registrasi ulang.'
                    : 'Pengguna ini belum memiliki data wajah. Silakan capture lalu simpan.' }}
            </div>

            @if(filled($selectedUser->face_thumbnail_path))
                <div class="d-flex align-items-start gap-3 mt-3 flex-wrap">
                    <img
                        src="{{ asset('storage/' . ltrim($selectedUser->face_thumbnail_path, '/')) }}"
                        alt="Thumbnail capture wajah {{ $selectedUser->name }}"
                        class="rounded border bg-white"
                        style="width: 120px; height: 120px; object-fit: cover;"
                    >
                    <div class="small text-muted">
                        Preview capture terakhir yang tersimpan. Jika Anda simpan ulang, thumbnail ini akan ikut diperbarui.
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-primary text-white fw-semibold">Pengaturan Scan</div>
                <div class="card-body">
                    <div class="vstack gap-3">
                        <div>
                            <label for="faceUserId" class="form-label">Nama Lengkap</label>
                            <select id="faceUserId" class="form-select" required>
                                <option value="">Pilih pengguna</option>
                                @foreach($users as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        @selected((int) $selectedUserId === $user->id)
                                        data-kelas="{{ $user->kelas }}"
                                        data-face-registered="{{ (int) ($user->has_face_data ?? 0) === 1 ? '1' : '0' }}"
                                    >
                                        {{ $user->name }} ({{ $user->identity_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="faceUserKelas" class="form-label">Kelas</label>
                            <input type="text" id="faceUserKelas" class="form-control" value="-" readonly>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="small text-muted">Status data wajah:</span>
                            <span id="faceRegistrationStatus" class="badge text-bg-secondary">Belum dipilih</span>
                        </div>

                        <div class="d-grid gap-2 d-md-flex">
                            <button type="button" id="startFaceCameraBtn" class="btn btn-outline-primary flex-fill">
                                <i class="fa-solid fa-video me-2"></i>Mulai Scan Wajah
                            </button>
                            <button type="button" id="captureFaceBtn" class="btn btn-outline-success flex-fill" disabled>
                                <i class="fa-solid fa-camera me-2"></i>Capture
                            </button>
                        </div>

                        <button type="button" id="submitFaceRegistrationBtn" class="btn btn-primary btn-lg" disabled>
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Data Wajah
                        </button>

                        <div id="faceRegisterAlert" class="alert d-none mb-0" role="alert"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold border-0">Preview Kamera</div>
                <div class="card-body">
                    <div class="face-camera-shell border rounded-3 overflow-hidden mb-3" style="{{ $faceCameraShellStyle }}">
                        <video id="faceRegisterVideo" class="w-100" autoplay playsinline muted></video>
                        <canvas id="faceRegisterOverlay" class="face-camera-overlay" aria-hidden="true"></canvas>
                    </div>

                    <canvas id="faceCaptureCanvas" class="d-none"></canvas>

                    <div class="border rounded-3 p-2 bg-light-subtle">
                        <div class="small text-muted mb-2">Hasil Capture Terakhir</div>
                        <img id="faceCapturePreview" class="img-fluid rounded d-none" alt="Preview hasil capture wajah">
                        <div id="faceCapturePlaceholder" class="text-secondary small">Belum ada gambar yang di-capture.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .ts-wrapper.single .ts-control {
            min-height: calc(2.25rem + 2px);
            border-radius: 0.375rem;
        }

        .ts-dropdown .dropdown-input-wrap {
            padding: 0.4rem 0.45rem;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .ts-dropdown .dropdown-input {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.35rem 0.5rem;
        }

        .ts-dropdown .no-results {
            padding: 0.55rem 0.7rem;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .face-camera-shell {
            width: min(100%, var(--face-camera-preview-size, 420px));
            aspect-ratio: var(--face-camera-frame-ratio, 1 / 1);
            min-height: 0;
            margin-inline: auto;
            background: var(--face-camera-background, #111111);
            border-radius: var(--face-camera-border-radius, 16px);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #faceRegisterVideo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: var(--face-camera-object-fit, cover);
            object-position: calc(50% + var(--face-camera-horizontal-shift, 0%)) calc(50% + var(--face-camera-vertical-shift, 0%));
            display: block;
            background: var(--face-camera-background, #111111);
        }

        #faceRegisterOverlay {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }

        #faceCapturePreview {
            aspect-ratio: var(--face-camera-frame-ratio, 1 / 1);
            object-fit: cover;
            object-position: calc(50% + var(--face-camera-horizontal-shift, 0%)) calc(50% + var(--face-camera-vertical-shift, 0%));
        }
    </style>
@endpush

@push('scripts')
    @include('partials.face-recognition-assets')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var userSelect = document.getElementById('faceUserId');
            var kelasInput = document.getElementById('faceUserKelas');
            var statusBadge = document.getElementById('faceRegistrationStatus');
            var startButton = document.getElementById('startFaceCameraBtn');
            var captureButton = document.getElementById('captureFaceBtn');
            var submitButton = document.getElementById('submitFaceRegistrationBtn');
            var video = document.getElementById('faceRegisterVideo');
            var overlayCanvas = document.getElementById('faceRegisterOverlay');
            var canvas = document.getElementById('faceCaptureCanvas');
            var previewImage = document.getElementById('faceCapturePreview');
            var previewPlaceholder = document.getElementById('faceCapturePlaceholder');
            var alertBox = document.getElementById('faceRegisterAlert');
            var FACE_CAPTURE_SIZE = @json((int) ($faceCameraSettings['face_camera_capture_size'] ?? 512));
            var FACE_CAMERA_FRAME_MODE = @json((string) ($faceCameraSettings['face_camera_frame_mode'] ?? 'square'));
            var FACE_CAMERA_HORIZONTAL_SHIFT = @json((int) ($faceCameraSettings['face_camera_horizontal_shift'] ?? 0));
            var FACE_CAMERA_VERTICAL_SHIFT = @json((int) ($faceCameraSettings['face_camera_vertical_shift'] ?? 0));

            var stream = null;
            var capturedImageBase64 = '';
            var capturedFaceDescriptor = '';
            var previewDetectionCanvas = document.createElement('canvas');
            var previewDetectionIntervalId = null;
            var previewDetectionBusy = false;

            function initializeUserSearchableSelect() {
                if (!userSelect || typeof window.TomSelect === 'undefined' || userSelect.tomselect) {
                    return;
                }

                new window.TomSelect(userSelect, {
                    create: false,
                    allowEmptyOption: true,
                    plugins: {
                        dropdown_input: {},
                    },
                    searchField: ['text'],
                    sortField: [
                        {
                            field: '$order',
                        },
                    ],
                    closeAfterSelect: true,
                    placeholder: 'Cari nama atau NISN pengguna...',
                    render: {
                        no_results: function () {
                            return '<div class="no-results">Data pengguna tidak ditemukan.</div>';
                        },
                    },
                });
            }

            function getFaceCameraFrameRatio() {
                return FACE_CAMERA_FRAME_MODE === 'wide' ? 4 / 3 : 1;
            }

            function getFaceCameraHorizontalShift() {
                if (!Number.isFinite(FACE_CAMERA_HORIZONTAL_SHIFT)) {
                    return 0;
                }

                return Math.max(-100, Math.min(100, FACE_CAMERA_HORIZONTAL_SHIFT));
            }

            function getFaceCameraVerticalShift() {
                if (!Number.isFinite(FACE_CAMERA_VERTICAL_SHIFT)) {
                    return 0;
                }

                return Math.max(-100, Math.min(100, FACE_CAMERA_VERTICAL_SHIFT));
            }

            function getFaceCameraCaptureDimensions() {
                var targetRatio = getFaceCameraFrameRatio();
                var outputWidth = Math.max(1, FACE_CAPTURE_SIZE);

                return {
                    width: outputWidth,
                    height: Math.max(1, Math.round(outputWidth / targetRatio)),
                    ratio: targetRatio
                };
            }

            function captureFaceFrame() {
                if (!video || !canvas || video.videoWidth <= 0 || video.videoHeight <= 0) {
                    return '';
                }

                var targetRatio = getFaceCameraFrameRatio();
                var sourceWidth = video.videoWidth;
                var sourceHeight = video.videoHeight;
                var sourceAspect = sourceWidth / sourceHeight;
                var sourceX = 0;
                var sourceY = 0;
                var cropWidth = sourceWidth;
                var cropHeight = sourceHeight;
                var horizontalShift = getFaceCameraHorizontalShift();
                var verticalShift = getFaceCameraVerticalShift();

                if (sourceAspect > targetRatio) {
                    cropHeight = sourceHeight;
                    cropWidth = Math.round(cropHeight * targetRatio);

                    var horizontalSpace = Math.max(0, sourceWidth - cropWidth);
                    sourceX = Math.round((horizontalSpace / 2) + ((horizontalShift / 100) * (horizontalSpace / 2)));
                } else if (sourceAspect < targetRatio) {
                    cropWidth = sourceWidth;
                    cropHeight = Math.round(cropWidth / targetRatio);

                    var verticalSpace = Math.max(0, sourceHeight - cropHeight);
                    sourceY = Math.round((verticalSpace / 2) + ((verticalShift / 100) * (verticalSpace / 2)));
                }

                sourceX = Math.max(0, Math.min(sourceWidth - cropWidth, sourceX));
                sourceY = Math.max(0, Math.min(sourceHeight - cropHeight, sourceY));

                var captureDimensions = getFaceCameraCaptureDimensions();
                canvas.width = captureDimensions.width;
                canvas.height = captureDimensions.height;

                var context = canvas.getContext('2d');
                context.drawImage(
                    video,
                    sourceX,
                    sourceY,
                    cropWidth,
                    cropHeight,
                    0,
                    0,
                    captureDimensions.width,
                    captureDimensions.height
                );

                return canvas.toDataURL('image/jpeg', 0.85);
            }

            function setAlert(message, type) {
                if (!alertBox) {
                    return;
                }

                if (!message) {
                    alertBox.className = 'alert d-none mb-0';
                    alertBox.textContent = '';
                    return;
                }

                alertBox.className = 'alert alert-' + type + ' mb-0';
                alertBox.textContent = message;
            }

            function getFaceBoxStroke(status) {
                if (status === 'ok') {
                    return '#22c55e';
                }

                if (status === 'multiple_faces') {
                    return '#f59e0b';
                }

                return '#ef4444';
            }

            function clearFaceBoundingOverlay() {
                if (!overlayCanvas) {
                    return;
                }

                var overlayContext = overlayCanvas.getContext('2d');

                if (!overlayContext) {
                    return;
                }

                overlayContext.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);
            }

            function syncFaceBoundingOverlaySize(referenceWidth, referenceHeight) {
                if (!overlayCanvas) {
                    return {
                        width: 0,
                        height: 0,
                    };
                }

                var displayWidth = Math.max(1, Math.round(overlayCanvas.clientWidth || referenceWidth || 1));
                var displayHeight = Math.max(1, Math.round(overlayCanvas.clientHeight || referenceHeight || 1));
                var devicePixelRatio = Math.max(1, Number(window.devicePixelRatio) || 1);
                var targetWidth = Math.max(1, Math.round(displayWidth * devicePixelRatio));
                var targetHeight = Math.max(1, Math.round(displayHeight * devicePixelRatio));

                if (overlayCanvas.width !== targetWidth || overlayCanvas.height !== targetHeight) {
                    overlayCanvas.width = targetWidth;
                    overlayCanvas.height = targetHeight;
                }

                return {
                    width: targetWidth,
                    height: targetHeight,
                };
            }

            function drawFaceScanningGuide(context, canvasWidth, canvasHeight, status) {
                if (!context || canvasWidth <= 0 || canvasHeight <= 0) {
                    return;
                }

                var guideWidth = canvasWidth * 0.56;
                var guideHeight = canvasHeight * 0.72;
                var guideLeft = (canvasWidth - guideWidth) / 2;
                var guideTop = (canvasHeight - guideHeight) / 2;
                var guideStroke = status === 'invalid_descriptor' ? '#ef4444' : 'rgba(255, 255, 255, 0.82)';

                context.save();
                context.lineWidth = Math.max(2, Math.round(canvasWidth * 0.008));
                context.strokeStyle = guideStroke;
                context.setLineDash([12, 10]);
                context.strokeRect(guideLeft, guideTop, guideWidth, guideHeight);
                context.restore();
            }

            function drawFaceBoundingOverlay(captureResult) {
                if (!overlayCanvas) {
                    return;
                }

                if (!captureResult || !captureResult.captureDimensions) {
                    clearFaceBoundingOverlay();

                    return;
                }

                var overlayContext = overlayCanvas.getContext('2d');

                if (!overlayContext) {
                    return;
                }

                var captureWidth = Math.max(1, Math.round(Number(captureResult.captureDimensions.width) || 1));
                var captureHeight = Math.max(1, Math.round(Number(captureResult.captureDimensions.height) || 1));
                var overlaySize = syncFaceBoundingOverlaySize(captureWidth, captureHeight);
                var scaleX = overlaySize.width / captureWidth;
                var scaleY = overlaySize.height / captureHeight;
                var detectedBoxes = captureResult && Array.isArray(captureResult.detectedBoxes)
                    ? captureResult.detectedBoxes
                    : [];

                overlayContext.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);

                if (!detectedBoxes.length) {
                    drawFaceScanningGuide(overlayContext, overlayCanvas.width, overlayCanvas.height, captureResult.status);

                    return;
                }

                var strokeStyle = getFaceBoxStroke(captureResult.status);

                detectedBoxes.forEach(function (box) {
                    var left = Number(box.x);
                    var top = Number(box.y);
                    var width = Number(box.width);
                    var height = Number(box.height);

                    if (!Number.isFinite(left) || !Number.isFinite(top) || !Number.isFinite(width) || !Number.isFinite(height)) {
                        return;
                    }

                    if (width <= 1 && height <= 1) {
                        left = left * captureWidth;
                        top = top * captureHeight;
                        width = width * captureWidth;
                        height = height * captureHeight;
                    }

                    left = left * scaleX;
                    top = top * scaleY;
                    width = width * scaleX;
                    height = height * scaleY;

                    left = Math.max(0, Math.min(overlayCanvas.width, left));
                    top = Math.max(0, Math.min(overlayCanvas.height, top));
                    width = Math.max(1, Math.min(overlayCanvas.width - left, width));
                    height = Math.max(1, Math.min(overlayCanvas.height - top, height));

                    overlayContext.lineWidth = Math.max(2, Math.round(overlayCanvas.width * 0.009));
                    overlayContext.strokeStyle = strokeStyle;
                    overlayContext.setLineDash([]);
                    overlayContext.strokeRect(left, top, width, height);
                });
            }

            function stopLiveBoundingPreview() {
                if (previewDetectionIntervalId) {
                    window.clearInterval(previewDetectionIntervalId);
                    previewDetectionIntervalId = null;
                }

                previewDetectionBusy = false;
                clearFaceBoundingOverlay();
            }

            async function runLiveBoundingPreview() {
                if (previewDetectionBusy || !stream || !video || video.readyState < 2 || !window.InventoryFaceRecognition) {
                    return;
                }

                previewDetectionBusy = true;

                try {
                    var detectionResult = await window.InventoryFaceRecognition.captureFaceData(video, previewDetectionCanvas, {
                        captureSize: FACE_CAPTURE_SIZE,
                        frameMode: FACE_CAMERA_FRAME_MODE,
                        horizontalShift: FACE_CAMERA_HORIZONTAL_SHIFT,
                        verticalShift: FACE_CAMERA_VERTICAL_SHIFT,
                        includeImage: false,
                        detectorInputSize: 320,
                        scoreThreshold: 0.45,
                        enableFallbackDetection: true,
                        fallbackDetectorInputSize: 256,
                        fallbackScoreThreshold: 0.35,
                    });

                    drawFaceBoundingOverlay(detectionResult);
                } catch (error) {
                    clearFaceBoundingOverlay();
                } finally {
                    previewDetectionBusy = false;
                }
            }

            function startLiveBoundingPreview() {
                stopLiveBoundingPreview();

                if (!overlayCanvas || !window.InventoryFaceRecognition) {
                    return;
                }

                syncFaceBoundingOverlaySize(video ? video.videoWidth : 0, video ? video.videoHeight : 0);
                runLiveBoundingPreview();
                previewDetectionIntervalId = window.setInterval(runLiveBoundingPreview, 600);
            }

            function stopCamera() {
                stopLiveBoundingPreview();

                if (stream) {
                    stream.getTracks().forEach(function (track) {
                        track.stop();
                    });

                    stream = null;
                }

                if (video) {
                    video.srcObject = null;
                }
            }

            function updateSelectedUserInfo() {
                if (!userSelect || !kelasInput || !statusBadge) {
                    return;
                }

                var selectedOption = userSelect.options[userSelect.selectedIndex];
                var alreadyRegistered = false;

                if (!selectedOption || !selectedOption.value) {
                    kelasInput.value = '-';
                    statusBadge.className = 'badge text-bg-secondary';
                    statusBadge.textContent = 'Belum dipilih';
                    startButton.disabled = true;
                    captureButton.disabled = true;
                    submitButton.disabled = true;
                    setAlert('', 'info');

                    return;
                }

                kelasInput.value = selectedOption.getAttribute('data-kelas') || '-';

                alreadyRegistered = selectedOption.getAttribute('data-face-registered') === '1';
                statusBadge.className = alreadyRegistered
                    ? 'badge text-bg-warning text-dark'
                    : 'badge text-bg-success';
                statusBadge.textContent = alreadyRegistered
                    ? 'Hapus dulu untuk registrasi ulang'
                    : 'Belum terdaftar';

                startButton.disabled = alreadyRegistered;
                captureButton.disabled = alreadyRegistered || capturedImageBase64 === '';
                submitButton.disabled = alreadyRegistered || capturedImageBase64 === '';

                if (alreadyRegistered) {
                    setAlert('Data wajah untuk pengguna ini sudah terdaftar. Hapus data wajah terlebih dahulu sebelum registrasi ulang.', 'warning');
                } else if (!capturedImageBase64) {
                    setAlert('', 'info');
                }
            }

            function resetCapturedFace() {
                capturedImageBase64 = '';
                capturedFaceDescriptor = '';
                stopCamera();

                if (previewImage) {
                    previewImage.classList.add('d-none');
                    previewImage.removeAttribute('src');
                }

                if (previewPlaceholder) {
                    previewPlaceholder.classList.remove('d-none');
                }

                if (submitButton) {
                    submitButton.disabled = true;
                }

                if (captureButton) {
                    captureButton.disabled = true;
                }
            }

            async function startCamera() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setAlert('Browser tidak mendukung akses kamera.', 'danger');

                    return;
                }

                try {
                    stopCamera();

                    var frameRatio = getFaceCameraFrameRatio();
                    var cameraBaseResolution = Math.max(640, FACE_CAPTURE_SIZE);

                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: cameraBaseResolution },
                            height: { ideal: Math.max(1, Math.round(cameraBaseResolution / frameRatio)) },
                            aspectRatio: frameRatio
                        },
                        audio: false
                    });

                    video.srcObject = stream;

                    if (video.play) {
                        video.play().catch(function () {
                            return;
                        });
                    }

                    if (window.InventoryFaceRecognition) {
                        setAlert('Memuat model face recognition...', 'info');
                        await window.InventoryFaceRecognition.loadFaceApiModels();
                    }

                    startLiveBoundingPreview();
                    captureButton.disabled = false;
                    setAlert('Kamera aktif. Silakan posisikan wajah lalu klik Capture.', 'info');
                } catch (error) {
                    stopCamera();
                    captureButton.disabled = true;
                    setAlert(error && error.message ? error.message : 'Gagal mengakses kamera atau memuat model face recognition.', 'danger');
                }
            }

            async function captureFace() {
                if (userSelect && userSelect.selectedIndex >= 0 && userSelect.options[userSelect.selectedIndex].getAttribute('data-face-registered') === '1') {
                    setAlert('Data wajah untuk pengguna ini sudah terdaftar. Hapus data wajah terlebih dahulu sebelum registrasi ulang.', 'warning');

                    return;
                }

                if (!window.InventoryFaceRecognition) {
                    setAlert('Library face recognition belum siap.', 'danger');

                    return;
                }

                try {
                    setAlert('Memproses wajah...', 'info');

                    var captureResult = await window.InventoryFaceRecognition.captureFaceData(video, canvas, {
                        captureSize: FACE_CAPTURE_SIZE,
                        frameMode: FACE_CAMERA_FRAME_MODE,
                        horizontalShift: FACE_CAMERA_HORIZONTAL_SHIFT,
                        verticalShift: FACE_CAMERA_VERTICAL_SHIFT,
                        includeImage: true,
                        detectorInputSize: 416,
                        imageQuality: 0.85,
                    });

                    drawFaceBoundingOverlay(captureResult);

                    if (captureResult.status === 'no_face') {
                        setAlert('Tidak ada wajah terdeteksi. Pastikan wajah berada di tengah kamera.', 'warning');

                        return;
                    }

                    if (captureResult.status === 'multiple_faces') {
                        setAlert('Terdeteksi lebih dari satu wajah. Pastikan hanya satu orang di frame.', 'warning');

                        return;
                    }

                    if (captureResult.status !== 'ok' || !Array.isArray(captureResult.descriptor) || !captureResult.imageBase64) {
                        setAlert('Descriptor wajah tidak valid. Ulangi capture dengan satu wajah yang jelas.', 'danger');

                        return;
                    }

                    capturedImageBase64 = captureResult.imageBase64;
                    capturedFaceDescriptor = JSON.stringify(captureResult.descriptor);
                    previewImage.src = capturedImageBase64;
                    previewImage.classList.remove('d-none');
                    previewPlaceholder.classList.add('d-none');

                    submitButton.disabled = !userSelect.value;
                    setAlert('Capture berhasil. Klik Simpan Data Wajah untuk proses registrasi.', 'success');
                } catch (error) {
                    setAlert(error && error.message ? error.message : 'Gagal memproses face recognition di browser.', 'danger');
                }
            }

            async function submitRegistration() {
                if (!userSelect.value) {
                    setAlert('Pilih pengguna terlebih dahulu.', 'warning');

                    return;
                }

                if (!capturedImageBase64) {
                    setAlert('Silakan capture wajah terlebih dahulu.', 'warning');

                    return;
                }

                if (!capturedFaceDescriptor) {
                    setAlert('Descriptor wajah belum tersedia. Capture ulang wajah terlebih dahulu.', 'warning');

                    return;
                }

                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';

                try {
                    var faceDescriptorPayload = [];

                    try {
                        faceDescriptorPayload = JSON.parse(capturedFaceDescriptor);
                    } catch (parseError) {
                        setAlert('Descriptor wajah tidak valid. Silakan capture ulang wajah.', 'danger');

                        return;
                    }

                    var response = await fetch(@json(route('admin.face-register.store')), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            user_id: parseInt(userSelect.value, 10),
                            image_base64: capturedImageBase64,
                            face_descriptor: faceDescriptorPayload
                        })
                    });

                    var contentType = (response.headers.get('content-type') || '').toLowerCase();
                    var isJsonResponse = contentType.indexOf('application/json') !== -1;
                    var data = {};

                    if (isJsonResponse) {
                        data = await response.json();
                    } else {
                        await response.text();
                    }

                    if (response.status === 401 || response.status === 419 || response.redirected) {
                        setAlert(data.message || 'Sesi admin berakhir. Silakan login ulang lalu coba lagi.', 'warning');

                        return;
                    }

                    if (response.status === 409) {
                        setAlert(data.message || 'Registrasi wajah tidak dapat dilakukan.', 'warning');

                        return;
                    }

                    if (!response.ok) {
                        setAlert(data.message || ('Registrasi wajah gagal (HTTP ' + response.status + ').'), 'danger');

                        return;
                    }

                    setAlert(data.message || 'Registrasi wajah berhasil.', 'success');

                    var selectedOption = userSelect.options[userSelect.selectedIndex];
                    if (selectedOption) {
                        selectedOption.setAttribute('data-face-registered', '1');
                    }
                    updateSelectedUserInfo();
                } catch (error) {
                    setAlert('Terjadi kesalahan jaringan saat menghubungi server. Periksa koneksi lalu coba lagi.', 'danger');
                } finally {
                    var selectedOption = userSelect.options[userSelect.selectedIndex];
                    var shouldDisableSubmit = !capturedImageBase64 || !capturedFaceDescriptor;

                    if (selectedOption && selectedOption.getAttribute('data-face-registered') === '1') {
                        shouldDisableSubmit = true;
                    }

                    submitButton.disabled = shouldDisableSubmit;
                    submitButton.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Simpan Data Wajah';
                }
            }

            if (userSelect) {
                userSelect.addEventListener('change', updateSelectedUserInfo);
                userSelect.addEventListener('change', resetCapturedFace);
                updateSelectedUserInfo();
                initializeUserSearchableSelect();
            }

            if (startButton) {
                startButton.addEventListener('click', startCamera);
            }

            if (captureButton) {
                captureButton.addEventListener('click', captureFace);
            }

            if (submitButton) {
                submitButton.addEventListener('click', function () {
                    submitRegistration();
                });
            }

            window.addEventListener('beforeunload', function () {
                stopCamera();
            });
        });
    </script>
@endpush
