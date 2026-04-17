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
        $selectedUserHasFaceData = !empty($selectedUser) && (filled($selectedUser->face_encoding) || filled($selectedUser->face_registered_at) || filled($selectedUser->face_thumbnail_path));
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
                                        data-face-registered="{{ $user->face_registered_at ? '1' : '0' }}"
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
    <style>
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

        #faceCapturePreview {
            aspect-ratio: var(--face-camera-frame-ratio, 1 / 1);
            object-fit: cover;
            object-position: calc(50% + var(--face-camera-horizontal-shift, 0%)) calc(50% + var(--face-camera-vertical-shift, 0%));
        }
    </style>
@endpush

@push('scripts')
    @include('partials.face-recognition-assets')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var userSelect = document.getElementById('faceUserId');
            var kelasInput = document.getElementById('faceUserKelas');
            var statusBadge = document.getElementById('faceRegistrationStatus');
            var startButton = document.getElementById('startFaceCameraBtn');
            var captureButton = document.getElementById('captureFaceBtn');
            var submitButton = document.getElementById('submitFaceRegistrationBtn');
            var video = document.getElementById('faceRegisterVideo');
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

            function stopCamera() {
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

                    if (window.InventoryFaceRecognition) {
                        setAlert('Memuat model face recognition...', 'info');
                        await window.InventoryFaceRecognition.loadFaceApiModels();
                    }

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
                    var response = await fetch(@json(route('admin.face-register.store')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify({
                            user_id: parseInt(userSelect.value, 10),
                            image_base64: capturedImageBase64,
                            face_descriptor: JSON.parse(capturedFaceDescriptor)
                        })
                    });

                    var data = await response.json();

                    if (response.status === 409) {
                        setAlert(data.message || 'Registrasi wajah tidak dapat dilakukan.', 'warning');

                        return;
                    }

                    if (!response.ok) {
                        setAlert(data.message || 'Registrasi wajah gagal.', 'danger');

                        return;
                    }

                    setAlert(data.message || 'Registrasi wajah berhasil.', 'success');

                    var selectedOption = userSelect.options[userSelect.selectedIndex];
                    if (selectedOption) {
                        selectedOption.setAttribute('data-face-registered', '1');
                    }
                    updateSelectedUserInfo();
                } catch (error) {
                    setAlert('Terjadi kesalahan jaringan saat menghubungi server.', 'danger');
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
                if (!stream) {
                    return;
                }

                stream.getTracks().forEach(function (track) {
                    track.stop();
                });
            });
        });
    </script>
@endpush
