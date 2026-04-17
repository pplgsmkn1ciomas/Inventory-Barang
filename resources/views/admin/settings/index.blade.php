@extends('layouts.app')

@section('content')
    @php
        $allowedTabs = ['running-text', 'menu-a', 'menu-b', 'menu-c'];
        $activeTab = request()->string('tab')->toString();
        $activeTab = in_array($activeTab, $allowedTabs, true) ? $activeTab : 'running-text';
        $fontFamilyPreviewMap = [
            'system' => 'system-ui, sans-serif',
            'arial' => 'Arial, sans-serif',
            'verdana' => 'Verdana, sans-serif',
            'tahoma' => 'Tahoma, sans-serif',
            'georgia' => 'Georgia, serif',
            'mono' => 'monospace',
        ];
        $previewFontFamilyKey = old('public_running_text_font_family', $settingValues['public_running_text_font_family']);
        $previewFontFamily = $fontFamilyPreviewMap[$previewFontFamilyKey] ?? $fontFamilyPreviewMap['system'];
        $cameraPreviewValues = [
            'face_camera_preview_size' => (int) old('face_camera_preview_size', $settingValues['face_camera_preview_size']),
            'face_camera_capture_size' => (int) old('face_camera_capture_size', $settingValues['face_camera_capture_size']),
            'face_camera_border_radius' => (int) old('face_camera_border_radius', $settingValues['face_camera_border_radius']),
            'face_camera_background' => old('face_camera_background', $settingValues['face_camera_background']),
            'face_camera_object_fit' => old('face_camera_object_fit', $settingValues['face_camera_object_fit']),
            'face_camera_frame_mode' => old('face_camera_frame_mode', $settingValues['face_camera_frame_mode']),
            'face_camera_horizontal_shift' => (int) old('face_camera_horizontal_shift', $settingValues['face_camera_horizontal_shift']),
            'face_camera_vertical_shift' => (int) old('face_camera_vertical_shift', $settingValues['face_camera_vertical_shift']),
        ];
        $cameraPreviewValues['face_camera_frame_ratio'] = $cameraPreviewValues['face_camera_frame_mode'] === 'wide' ? '4 / 3' : '1 / 1';
        $cameraPreviewSampleSvg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="800" height="800"><defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f172a"/><stop offset="100%" stop-color="#1d4ed8"/></linearGradient><linearGradient id="accent" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#93c5fd"/><stop offset="100%" stop-color="#60a5fa"/></linearGradient></defs><rect width="800" height="800" rx="72" fill="url(#bg)"/><circle cx="400" cy="250" r="110" fill="url(#accent)" opacity="0.95"/><rect x="210" y="385" width="380" height="260" rx="110" fill="#334155" opacity="0.95"/><rect x="140" y="682" width="520" height="54" rx="18" fill="#0b1220" opacity="0.8"/><rect x="110" y="120" width="180" height="42" rx="21" fill="#ffffff" opacity="0.16"/><rect x="510" y="120" width="180" height="42" rx="21" fill="#ffffff" opacity="0.16"/><text x="400" y="585" text-anchor="middle" font-family="Arial, sans-serif" font-size="42" font-weight="700" fill="#e2e8f0">Preview Kamera</text><text x="400" y="640" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" fill="#cbd5e1">Dynamic setting preview</text></svg>');
        $cameraPreviewSampleImage = 'data:image/svg+xml;charset=UTF-8,' . $cameraPreviewSampleSvg;
        $menuAOptionMeta = [
            'categories' => ['label' => 'Kategori', 'placeholder' => 'Contoh: Laptop'],
            'brands' => ['label' => 'Merk', 'placeholder' => 'Contoh: Lenovo'],
            'statuses' => ['label' => 'Status', 'placeholder' => 'Contoh: available'],
            'conditions' => ['label' => 'Kondisi', 'placeholder' => 'Contoh: good'],
            'roles' => ['label' => 'Role', 'placeholder' => 'Contoh: student'],
            'classes' => ['label' => 'Kelas', 'placeholder' => 'Contoh: 10 PPLG 1'],
        ];
        $menuAOptionValues = [];

        foreach ($menuAOptionMeta as $optionKey => $optionMeta) {
            $rawValues = old($optionKey, $assetOptions[$optionKey] ?? []);
            $rawValues = is_array($rawValues) ? $rawValues : [$rawValues];
            $normalizedValues = array_values(array_map(static fn ($value) => trim((string) $value), $rawValues));
            $menuAOptionValues[$optionKey] = $normalizedValues !== [] ? $normalizedValues : [''];
        }
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Pengaturan</h4>
            <p class="text-muted mb-0">Kelola konfigurasi dinamis untuk halaman dashboard public.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white border-0 pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'running-text' ? 'active' : '' }}" id="tab-running-text-link" data-bs-toggle="tab" data-bs-target="#tab-running-text" type="button" role="tab" aria-controls="tab-running-text" aria-selected="{{ $activeTab === 'running-text' ? 'true' : 'false' }}">
                        Running Teks
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'menu-a' ? 'active' : '' }}" id="tab-menu-a-link" data-bs-toggle="tab" data-bs-target="#tab-menu-a" type="button" role="tab" aria-controls="tab-menu-a" aria-selected="{{ $activeTab === 'menu-a' ? 'true' : 'false' }}">
                        Master Data Sistem
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'menu-b' ? 'active' : '' }}" id="tab-menu-b-link" data-bs-toggle="tab" data-bs-target="#tab-menu-b" type="button" role="tab" aria-controls="tab-menu-b" aria-selected="{{ $activeTab === 'menu-b' ? 'true' : 'false' }}">
                        Menu B
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'menu-c' ? 'active' : '' }}" id="tab-menu-c-link" data-bs-toggle="tab" data-bs-target="#tab-menu-c" type="button" role="tab" aria-controls="tab-menu-c" aria-selected="{{ $activeTab === 'menu-c' ? 'true' : 'false' }}">
                        Kamera
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="settingsTabContent">
                <div class="tab-pane fade {{ $activeTab === 'running-text' ? 'show active' : '' }}" id="tab-running-text" role="tabpanel" aria-labelledby="tab-running-text-link" tabindex="0">
                    <form method="POST" action="{{ route('admin.settings.running-text.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-lg-8">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-sliders text-primary"></i>
                                    <div class="fw-semibold">Konfigurasi Running Teks</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Teks Berjalan Dashboard Public <span class="text-danger">*</span></label>
                                    <textarea
                                        id="runningTextInput"
                                        name="running_text"
                                        class="form-control"
                                        rows="3"
                                        maxlength="255"
                                        required
                                    >{{ old('running_text', $runningText) }}</textarea>
                                    <div class="form-text">Teks ini akan ditampilkan pada banner berjalan di bagian bawah halaman dashboard public.</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Status Running Teks</label>
                                        <select id="runningTextEnabledInput" name="public_reminder_enabled" class="form-select" required>
                                            <option value="1" @selected(old('public_reminder_enabled', $settingValues['public_reminder_enabled']) === '1')>ON</option>
                                            <option value="0" @selected(old('public_reminder_enabled', $settingValues['public_reminder_enabled']) === '0')>OFF</option>
                                        </select>
                                        <div class="form-text">Pilih OFF untuk menyembunyikan banner running teks di dashboard public.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Warna Background</label>
                                        <input type="color" id="runningTextBgInput" name="public_reminder_background" class="form-control form-control-color w-100" value="{{ old('public_reminder_background', $settingValues['public_reminder_background']) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Warna Teks</label>
                                        <input type="color" id="runningTextColorInput" name="public_reminder_text_color" class="form-control form-control-color w-100" value="{{ old('public_reminder_text_color', $settingValues['public_reminder_text_color']) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-flex justify-content-between">
                                            <span>Kecepatan Gerak</span>
                                            <span class="badge text-bg-secondary" id="runningTextSpeedLabel">{{ old('public_running_text_speed', $settingValues['public_running_text_speed']) }}s</span>
                                        </label>
                                        <input type="range" id="runningTextSpeedInput" name="public_running_text_speed" class="form-range" min="5" max="40" step="1" value="{{ old('public_running_text_speed', $settingValues['public_running_text_speed']) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-flex justify-content-between">
                                            <span>Ukuran Font</span>
                                            <span class="badge text-bg-secondary" id="runningTextFontSizeLabel">{{ old('public_running_text_font_size', $settingValues['public_running_text_font_size']) }}px</span>
                                        </label>
                                        <input type="range" id="runningTextFontSizeInput" name="public_running_text_font_size" class="form-range" min="12" max="36" step="1" value="{{ old('public_running_text_font_size', $settingValues['public_running_text_font_size']) }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Font Teks</label>
                                        <select id="runningTextFontFamilyInput" name="public_running_text_font_family" class="form-select" required>
                                            @foreach($fontFamilyOptions as $fontKey => $fontLabel)
                                                <option value="{{ $fontKey }}" @selected(old('public_running_text_font_family', $settingValues['public_running_text_font_family']) === $fontKey)>{{ $fontLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                    <div class="fw-semibold">Preview Live</div>
                                    <span class="badge text-bg-success ms-auto" id="runningTextStatusBadge">ON</span>
                                </div>
                                <div class="small text-muted mb-2">Simulasi tampilan banner running teks di dashboard public.</div>

                                <div id="runningTextPreviewBanner" class="rounded px-2 py-2 overflow-hidden" style="background: {{ old('public_reminder_background', $settingValues['public_reminder_background']) }}; color: {{ old('public_reminder_text_color', $settingValues['public_reminder_text_color']) }}; font-size: {{ old('public_running_text_font_size', $settingValues['public_running_text_font_size']) }}px;">
                                    <div id="runningTextPreviewMarquee" class="fw-semibold text-nowrap" style="display: inline-block; padding-left: 100%; animation: settingsPreviewMarquee {{ old('public_running_text_speed', $settingValues['public_running_text_speed']) }}s linear infinite; font-family: {{ $previewFontFamily }};">
                                        {{ old('running_text', $runningText) }}
                                    </div>
                                </div>

                                <div class="small text-muted mt-2">Kecepatan kecil = gerak lebih cepat.</div>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Pengaturan
                            </button>
                            <a href="{{ route('dashboard.public') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-up-right-from-square me-2"></i>Buka Dashboard Public
                            </a>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'menu-a' ? 'show active' : '' }}" id="tab-menu-a" role="tabpanel" aria-labelledby="tab-menu-a-link" tabindex="0">
                    <form method="POST" action="{{ route('admin.settings.menu-a.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <div class="alert alert-info border mb-0">
                                Kelola opsi dropdown untuk menu Data Barang dan Data Pengguna. Perubahan kategori, merk, status, kondisi, role, dan kelas akan langsung sinkron ke fitur filter, form tambah, dan form edit.
                            </div>
                        </div>

                        @foreach($menuAOptionMeta as $optionKey => $optionMeta)
                            @php
                                $optionRows = $menuAOptionValues[$optionKey] ?? [''];
                                $optionLabel = $optionMeta['label'];
                                $optionPlaceholder = $optionMeta['placeholder'];
                            @endphp
                            <div class="col-12 col-xl-6">
                                <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <div class="fw-semibold">{{ $optionLabel }}</div>
                                        <button type="button" class="btn btn-sm btn-outline-primary js-add-menu-a-option" data-option-key="{{ $optionKey }}" data-option-placeholder="{{ $optionPlaceholder }}">
                                            <i class="fa-solid fa-plus me-1"></i>Tambah
                                        </button>
                                    </div>

                                    <div class="vstack gap-2 mt-3 menu-a-option-list" id="menuAOptionList-{{ $optionKey }}" data-option-key="{{ $optionKey }}" data-option-placeholder="{{ $optionPlaceholder }}">
                                        @foreach($optionRows as $rowIndex => $optionValue)
                                            @php
                                                $fieldKey = $optionKey . '.' . $rowIndex;
                                                $inputHasError = $errors->has($fieldKey);
                                            @endphp
                                            <div class="menu-a-option-row">
                                                <div class="input-group">
                                                    <input type="text" name="{{ $optionKey }}[]" class="form-control {{ $inputHasError ? 'is-invalid' : '' }}" value="{{ $optionValue }}" maxlength="120" placeholder="{{ $optionPlaceholder }}" required>
                                                    <button type="button" class="btn btn-outline-danger js-remove-menu-a-option" aria-label="Hapus opsi {{ strtolower($optionLabel) }}">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                                @if($inputHasError)
                                                    <div class="invalid-feedback d-block">{{ $errors->first($fieldKey) }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($errors->has($optionKey))
                                        <div class="text-danger small mt-2">{{ $errors->first($optionKey) }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Master Data
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'menu-b' ? 'show active' : '' }}" id="tab-menu-b" role="tabpanel" aria-labelledby="tab-menu-b-link" tabindex="0">
                    <form method="POST" action="{{ route('admin.settings.menu-b.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-lg-8">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-heading text-primary"></i>
                                    <div class="fw-semibold">Konfigurasi Header & Tombol Public</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Judul Dashboard Public <span class="text-danger">*</span></label>
                                    <input type="text" id="menuBHeaderTitleInput" name="public_header_title" class="form-control" maxlength="120" value="{{ old('public_header_title', $settingValues['public_header_title']) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Subjudul Dashboard Public <span class="text-danger">*</span></label>
                                    <input type="text" id="menuBHeaderSubtitleInput" name="public_header_subtitle" class="form-control" maxlength="200" value="{{ old('public_header_subtitle', $settingValues['public_header_subtitle']) }}" required>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Label Tombol Peminjaman <span class="text-danger">*</span></label>
                                        <input type="text" id="menuBBorrowLabelInput" name="public_borrow_button_label" class="form-control" maxlength="80" value="{{ old('public_borrow_button_label', $settingValues['public_borrow_button_label']) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Label Tombol Pengembalian <span class="text-danger">*</span></label>
                                        <input type="text" id="menuBReturnLabelInput" name="public_return_button_label" class="form-control" maxlength="80" value="{{ old('public_return_button_label', $settingValues['public_return_button_label']) }}" required>
                                    </div>
                                </div>

                                <div class="form-text mt-2">Perubahan judul dan tombol akan langsung dipakai di dashboard public.</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                    <div class="fw-semibold">Preview Header & Tombol</div>
                                </div>
                                <div class="small text-muted mb-2">Preview live untuk judul, subjudul, dan label tombol public.</div>

                                <div class="border rounded-3 p-3 bg-light mb-3">
                                    <div id="menuBPreviewTitle" class="h5 fw-bold mb-1">{{ old('public_header_title', $settingValues['public_header_title']) }}</div>
                                    <div id="menuBPreviewSubtitle" class="text-secondary">{{ old('public_header_subtitle', $settingValues['public_header_subtitle']) }}</div>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <button type="button" id="menuBPreviewBorrowButton" class="btn btn-outline-primary" disabled>{{ old('public_borrow_button_label', $settingValues['public_borrow_button_label']) }}</button>
                                    <button type="button" id="menuBPreviewReturnButton" class="btn btn-outline-success" disabled>{{ old('public_return_button_label', $settingValues['public_return_button_label']) }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Menu B
                            </button>
                            <a href="{{ route('dashboard.public') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-up-right-from-square me-2"></i>Buka Dashboard Public
                            </a>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'menu-c' ? 'show active' : '' }}" id="tab-menu-c" role="tabpanel" aria-labelledby="tab-menu-c-link" tabindex="0">
                    <form method="POST" action="{{ route('admin.settings.menu-c.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-lg-8">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-video text-primary"></i>
                                    <div class="fw-semibold">Konfigurasi Preview Kamera Dinamis</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Ukuran Preview Kamera</span>
                                            <span class="badge text-bg-secondary" id="faceCameraPreviewSizeLabel">{{ $cameraPreviewValues['face_camera_preview_size'] }}px</span>
                                        </label>
                                        <input type="range" id="faceCameraPreviewSizeInput" name="face_camera_preview_size" class="form-range" min="280" max="720" step="20" value="{{ $cameraPreviewValues['face_camera_preview_size'] }}">
                                        <div class="form-text">Ukuran preview ini akan dipakai pada halaman kamera publik dan registrasi wajah.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Ukuran Capture Output</span>
                                            <span class="badge text-bg-secondary" id="faceCameraCaptureSizeLabel">{{ $cameraPreviewValues['face_camera_capture_size'] }}px</span>
                                        </label>
                                        <input type="range" id="faceCameraCaptureSizeInput" name="face_camera_capture_size" class="form-range" min="320" max="1024" step="32" value="{{ $cameraPreviewValues['face_camera_capture_size'] }}">
                                        <div class="form-text">Saat mode wide aktif, tinggi capture akan menyesuaikan rasio frame secara otomatis.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Model Frame Kamera</label>
                                        <select id="faceCameraFrameModeInput" name="face_camera_frame_mode" class="form-select" required>
                                            <option value="square" @selected($cameraPreviewValues['face_camera_frame_mode'] === 'square')>1:1 (Square)</option>
                                            <option value="wide" @selected($cameraPreviewValues['face_camera_frame_mode'] === 'wide')>Normal Terbuka (Wide)</option>
                                        </select>
                                        <div class="form-text">Pilih 1:1 untuk frame kotak atau wide untuk tampilan landscape yang lebih terbuka.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Geser Horizontal Kamera</span>
                                            <span class="badge text-bg-secondary" id="faceCameraHorizontalShiftLabel">{{ $cameraPreviewValues['face_camera_horizontal_shift'] }}%</span>
                                        </label>
                                        <input type="range" id="faceCameraHorizontalShiftInput" name="face_camera_horizontal_shift" class="form-range" min="-100" max="100" step="5" value="{{ $cameraPreviewValues['face_camera_horizontal_shift'] }}">
                                        <div class="form-text">Nilai minus menggeser kamera ke kiri, nilai plus menggeser ke kanan.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Geser Vertikal Kamera</span>
                                            <span class="badge text-bg-secondary" id="faceCameraVerticalShiftLabel">{{ $cameraPreviewValues['face_camera_vertical_shift'] }}%</span>
                                        </label>
                                        <input type="range" id="faceCameraVerticalShiftInput" name="face_camera_vertical_shift" class="form-range" min="-100" max="100" step="5" value="{{ $cameraPreviewValues['face_camera_vertical_shift'] }}">
                                        <div class="form-text">Nilai minus menggeser kamera ke atas, nilai plus menggeser ke bawah. Gunakan ini agar wajah lebih lurus di tengah frame.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Radius Frame</span>
                                            <span class="badge text-bg-secondary" id="faceCameraBorderRadiusLabel">{{ $cameraPreviewValues['face_camera_border_radius'] }}px</span>
                                        </label>
                                        <input type="range" id="faceCameraBorderRadiusInput" name="face_camera_border_radius" class="form-range" min="0" max="32" step="1" value="{{ $cameraPreviewValues['face_camera_border_radius'] }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Warna Background Frame</label>
                                        <input type="color" id="faceCameraBackgroundInput" name="face_camera_background" class="form-control form-control-color w-100" value="{{ $cameraPreviewValues['face_camera_background'] }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Object Fit</label>
                                        <select id="faceCameraObjectFitInput" name="face_camera_object_fit" class="form-select" required>
                                            <option value="cover" @selected($cameraPreviewValues['face_camera_object_fit'] === 'cover')>Cover</option>
                                            <option value="contain" @selected($cameraPreviewValues['face_camera_object_fit'] === 'contain')>Contain</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-text mt-2">Setting ini akan dipakai pada preview kamera halaman registrasi wajah dan dashboard public.</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                    <div class="fw-semibold">Preview Kamera Dinamis</div>
                                    <span class="badge text-bg-primary" id="faceCameraPreviewModeBadge">{{ $cameraPreviewValues['face_camera_frame_mode'] === 'wide' ? 'Wide' : '1:1' }}</span>
                                    <span class="badge text-bg-success ms-auto" id="faceCameraPreviewSizeBadge">{{ $cameraPreviewValues['face_camera_preview_size'] }}px</span>
                                </div>
                                <div class="small text-muted mb-2">Preview kamera akan aktif otomatis saat tab Kamera dibuka.</div>

                                <div id="faceCameraPreviewShell" class="camera-preview-shell mb-3">
                                    <img
                                        id="faceCameraPreviewFallback"
                                        src="{{ $cameraPreviewSampleImage }}"
                                        alt="Preview kamera dinamis"
                                        class="camera-preview-media camera-preview-fallback"
                                    >
                                    <video
                                        id="faceCameraPreviewMedia"
                                        alt="Preview kamera dinamis"
                                        poster="{{ $cameraPreviewSampleImage }}"
                                        class="camera-preview-media camera-preview-live"
                                        autoplay
                                        playsinline
                                        muted
                                    ></video>
                                    <div id="faceCameraPreviewStatusBadge" class="camera-preview-status badge text-bg-secondary">
                                        Siap
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between small text-muted">
                                    <span id="faceCameraCaptureSizePreviewLabel">Capture {{ $cameraPreviewValues['face_camera_capture_size'] }}px</span>
                                    <span id="faceCameraFramePreviewLabel">{{ $cameraPreviewValues['face_camera_frame_mode'] === 'wide' ? 'Wide 4:3' : '1:1' }} / X {{ $cameraPreviewValues['face_camera_horizontal_shift'] }}% / Y {{ $cameraPreviewValues['face_camera_vertical_shift'] }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Menu C
                            </button>
                            <a href="{{ route('admin.face-register.index') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-up-right-from-square me-2"></i>Buka Registrasi Wajah
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
        @keyframes settingsPreviewMarquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-100%);
            }
        }

        #settingsTab .settings-tab-btn {
            margin-bottom: -1px;
            border-radius: 0.85rem 0.85rem 0 0;
            border: 1px solid transparent;
            color: #475569;
            font-weight: 700;
            padding-inline: 1rem;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        #settingsTab .settings-tab-btn:hover,
        #settingsTab .settings-tab-btn:focus-visible {
            background: #eef4ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
            outline: 0;
        }

        #settingsTab .settings-tab-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border-color: #1d4ed8 #1d4ed8 transparent;
            box-shadow: 0 -2px 8px rgba(29, 78, 216, 0.18);
        }

        #settingsTab .settings-tab-btn.active:hover,
        #settingsTab .settings-tab-btn.active:focus-visible {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border-color: #1d4ed8 #1d4ed8 transparent;
        }

        .camera-preview-shell {
            width: min(100%, var(--face-camera-preview-size, 420px));
            aspect-ratio: var(--face-camera-frame-ratio, 1 / 1);
            margin-inline: auto;
            background: var(--face-camera-background, #111111);
            border-radius: var(--face-camera-border-radius, 16px);
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
        }

        .camera-preview-media {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: var(--face-camera-object-fit, cover);
            object-position: calc(50% + var(--face-camera-horizontal-shift, 0%)) calc(50% + var(--face-camera-vertical-shift, 0%));
            display: block;
            transition: opacity 0.2s ease;
        }

        .camera-preview-fallback {
            z-index: 0;
            opacity: 1;
        }

        .camera-preview-live {
            z-index: 1;
            opacity: 0;
        }

        .camera-preview-shell.is-active .camera-preview-live {
            opacity: 1;
        }

        .camera-preview-shell.is-active .camera-preview-fallback {
            opacity: 0;
        }

        .camera-preview-status {
            position: absolute;
            z-index: 2;
            top: 0.75rem;
            left: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #ffffff;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            pointer-events: none;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.16);
        }

        .camera-preview-shell::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 1px solid rgba(255, 255, 255, 0.12);
            pointer-events: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var textInput = document.getElementById('runningTextInput');
            var enabledInput = document.getElementById('runningTextEnabledInput');
            var bgInput = document.getElementById('runningTextBgInput');
            var colorInput = document.getElementById('runningTextColorInput');
            var speedInput = document.getElementById('runningTextSpeedInput');
            var fontSizeInput = document.getElementById('runningTextFontSizeInput');
            var fontFamilyInput = document.getElementById('runningTextFontFamilyInput');
            var speedLabel = document.getElementById('runningTextSpeedLabel');
            var fontSizeLabel = document.getElementById('runningTextFontSizeLabel');
            var statusBadge = document.getElementById('runningTextStatusBadge');
            var previewBanner = document.getElementById('runningTextPreviewBanner');
            var previewMarquee = document.getElementById('runningTextPreviewMarquee');

            if (textInput && enabledInput && bgInput && colorInput && speedInput && fontSizeInput && fontFamilyInput && previewBanner && previewMarquee) {
                var fontFamilyMap = {
                    system: 'system-ui, sans-serif',
                    arial: 'Arial, sans-serif',
                    verdana: 'Verdana, sans-serif',
                    tahoma: 'Tahoma, sans-serif',
                    georgia: 'Georgia, serif',
                    mono: 'monospace'
                };

                var syncPreview = function () {
                    var isEnabled = enabledInput.value === '1';

                    previewMarquee.textContent = isEnabled
                        ? (textInput.value || 'Preview running text...')
                        : 'Running teks dinonaktifkan';
                    previewBanner.style.background = bgInput.value;
                    previewBanner.style.color = colorInput.value;
                    previewBanner.style.fontSize = fontSizeInput.value + 'px';
                    previewMarquee.style.animationDuration = speedInput.value + 's';
                    previewMarquee.style.fontFamily = fontFamilyMap[fontFamilyInput.value] || fontFamilyMap.system;
                    previewMarquee.style.animationPlayState = isEnabled ? 'running' : 'paused';
                    previewBanner.style.opacity = isEnabled ? '1' : '0.6';

                    if (speedLabel) {
                        speedLabel.textContent = speedInput.value + 's';
                    }

                    if (fontSizeLabel) {
                        fontSizeLabel.textContent = fontSizeInput.value + 'px';
                    }

                    if (statusBadge) {
                        statusBadge.textContent = isEnabled ? 'ON' : 'OFF';
                        statusBadge.classList.toggle('text-bg-success', isEnabled);
                        statusBadge.classList.toggle('text-bg-secondary', !isEnabled);
                    }
                };

                textInput.addEventListener('input', syncPreview);
                enabledInput.addEventListener('change', syncPreview);
                bgInput.addEventListener('input', syncPreview);
                colorInput.addEventListener('input', syncPreview);
                speedInput.addEventListener('input', syncPreview);
                fontSizeInput.addEventListener('input', syncPreview);
                fontFamilyInput.addEventListener('change', syncPreview);

                syncPreview();
            }

            var menuBHeaderTitleInput = document.getElementById('menuBHeaderTitleInput');
            var menuBHeaderSubtitleInput = document.getElementById('menuBHeaderSubtitleInput');
            var menuBBorrowLabelInput = document.getElementById('menuBBorrowLabelInput');
            var menuBReturnLabelInput = document.getElementById('menuBReturnLabelInput');
            var menuBPreviewTitle = document.getElementById('menuBPreviewTitle');
            var menuBPreviewSubtitle = document.getElementById('menuBPreviewSubtitle');
            var menuBPreviewBorrowButton = document.getElementById('menuBPreviewBorrowButton');
            var menuBPreviewReturnButton = document.getElementById('menuBPreviewReturnButton');

            var syncMenuBPreview = function () {
                if (menuBPreviewTitle && menuBHeaderTitleInput) {
                    menuBPreviewTitle.textContent = menuBHeaderTitleInput.value || 'Dashboard Inventaris';
                }

                if (menuBPreviewSubtitle && menuBHeaderSubtitleInput) {
                    menuBPreviewSubtitle.textContent = menuBHeaderSubtitleInput.value || 'Sistem Peminjaman & Pengembalian Aset Sekolah';
                }

                if (menuBPreviewBorrowButton && menuBBorrowLabelInput) {
                    menuBPreviewBorrowButton.textContent = menuBBorrowLabelInput.value || 'Peminjaman Barang';
                }

                if (menuBPreviewReturnButton && menuBReturnLabelInput) {
                    menuBPreviewReturnButton.textContent = menuBReturnLabelInput.value || 'Pengembalian Barang';
                }
            };

            if (menuBHeaderTitleInput && menuBHeaderSubtitleInput && menuBBorrowLabelInput && menuBReturnLabelInput) {
                menuBHeaderTitleInput.addEventListener('input', syncMenuBPreview);
                menuBHeaderSubtitleInput.addEventListener('input', syncMenuBPreview);
                menuBBorrowLabelInput.addEventListener('input', syncMenuBPreview);
                menuBReturnLabelInput.addEventListener('input', syncMenuBPreview);
                syncMenuBPreview();
            }

            var faceCameraPreviewSizeInput = document.getElementById('faceCameraPreviewSizeInput');
            var faceCameraCaptureSizeInput = document.getElementById('faceCameraCaptureSizeInput');
            var faceCameraFrameModeInput = document.getElementById('faceCameraFrameModeInput');
            var faceCameraHorizontalShiftInput = document.getElementById('faceCameraHorizontalShiftInput');
            var faceCameraVerticalShiftInput = document.getElementById('faceCameraVerticalShiftInput');
            var faceCameraBorderRadiusInput = document.getElementById('faceCameraBorderRadiusInput');
            var faceCameraBackgroundInput = document.getElementById('faceCameraBackgroundInput');
            var faceCameraObjectFitInput = document.getElementById('faceCameraObjectFitInput');
            var faceCameraPreviewShell = document.getElementById('faceCameraPreviewShell');
            var faceCameraPreviewMedia = document.getElementById('faceCameraPreviewMedia');
            var faceCameraPreviewStatusBadge = document.getElementById('faceCameraPreviewStatusBadge');
            var faceCameraPreviewSizeLabel = document.getElementById('faceCameraPreviewSizeLabel');
            var faceCameraPreviewSizeBadge = document.getElementById('faceCameraPreviewSizeBadge');
            var faceCameraPreviewModeBadge = document.getElementById('faceCameraPreviewModeBadge');
            var faceCameraPreviewFallback = document.getElementById('faceCameraPreviewFallback');
            var faceCameraCaptureSizeLabel = document.getElementById('faceCameraCaptureSizeLabel');
            var faceCameraCaptureSizePreviewLabel = document.getElementById('faceCameraCaptureSizePreviewLabel');
            var faceCameraBorderRadiusLabel = document.getElementById('faceCameraBorderRadiusLabel');
            var faceCameraHorizontalShiftLabel = document.getElementById('faceCameraHorizontalShiftLabel');
            var faceCameraVerticalShiftLabel = document.getElementById('faceCameraVerticalShiftLabel');
            var faceCameraFramePreviewLabel = document.getElementById('faceCameraFramePreviewLabel');
            var faceCameraPreviewStream = null;
            var faceCameraPreviewStarting = false;

            var getFaceCameraFrameMode = function () {
                return faceCameraFrameModeInput && faceCameraFrameModeInput.value === 'wide' ? 'wide' : 'square';
            };

            var getFaceCameraFrameRatio = function () {
                return getFaceCameraFrameMode() === 'wide' ? 4 / 3 : 1;
            };

            var getFaceCameraFrameRatioCss = function () {
                return getFaceCameraFrameMode() === 'wide' ? '4 / 3' : '1 / 1';
            };

            var getFaceCameraHorizontalShift = function () {
                var shiftValue = faceCameraHorizontalShiftInput ? parseInt(faceCameraHorizontalShiftInput.value, 10) : 0;

                if (!Number.isFinite(shiftValue)) {
                    return 0;
                }

                return Math.max(-100, Math.min(100, shiftValue));
            };

            var restartFaceCameraPreview = function () {
                if (!faceCameraPreviewStream) {
                    return;
                }

                stopFaceCameraPreview();
                startFaceCameraPreview();
            };

            var setFaceCameraPreviewStatus = function (message, badgeClass) {
                if (faceCameraPreviewStatusBadge) {
                    faceCameraPreviewStatusBadge.textContent = message;
                    faceCameraPreviewStatusBadge.className = 'badge ' + badgeClass;
                }
            };

            var stopFaceCameraPreview = function () {
                if (faceCameraPreviewStream) {
                    faceCameraPreviewStream.getTracks().forEach(function (track) {
                        track.stop();
                    });
                }

                faceCameraPreviewStream = null;

                if (faceCameraPreviewMedia) {
                    faceCameraPreviewMedia.srcObject = null;
                }

                if (faceCameraPreviewShell) {
                    faceCameraPreviewShell.classList.remove('is-active');
                }

                setFaceCameraPreviewStatus('Siap', 'text-bg-secondary');
            };

            var startFaceCameraPreview = async function () {
                if (!faceCameraPreviewMedia || faceCameraPreviewStream || faceCameraPreviewStarting) {
                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setFaceCameraPreviewStatus('Tidak didukung', 'text-bg-danger');

                    return;
                }

                faceCameraPreviewStarting = true;
                setFaceCameraPreviewStatus('Meminta izin...', 'text-bg-warning text-dark');

                try {
                    var previewSizeValue = faceCameraPreviewSizeInput ? parseInt(faceCameraPreviewSizeInput.value, 10) : 420;
                    var captureSizeValue = faceCameraCaptureSizeInput ? parseInt(faceCameraCaptureSizeInput.value, 10) : 512;
                    var previewResolution = Number.isFinite(previewSizeValue) ? previewSizeValue : 420;
                    var cameraBaseResolution = Math.max(640, Number.isFinite(captureSizeValue) ? captureSizeValue : 512);
                    var frameRatio = getFaceCameraFrameRatio();
                    var previewWidth = Math.max(cameraBaseResolution, previewResolution);
                    var previewHeight = Math.max(1, Math.round(previewWidth / frameRatio));

                    faceCameraPreviewStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: previewWidth },
                            height: { ideal: previewHeight },
                            aspectRatio: frameRatio
                        },
                        audio: false
                    });

                    faceCameraPreviewMedia.srcObject = faceCameraPreviewStream;

                    if (faceCameraPreviewMedia.play) {
                        faceCameraPreviewMedia.play().catch(function () {
                            return;
                        });
                    }

                    if (faceCameraPreviewShell) {
                        faceCameraPreviewShell.classList.add('is-active');
                    }

                    setFaceCameraPreviewStatus('Aktif', 'text-bg-success');
                } catch (error) {
                    stopFaceCameraPreview();
                    setFaceCameraPreviewStatus('Gagal', 'text-bg-danger');
                } finally {
                    faceCameraPreviewStarting = false;
                }
            };

            var syncFaceCameraPreview = function () {
                if (!faceCameraPreviewShell || !faceCameraPreviewMedia) {
                    return;
                }

                var previewSize = faceCameraPreviewSizeInput ? faceCameraPreviewSizeInput.value : '420';
                var captureSize = faceCameraCaptureSizeInput ? faceCameraCaptureSizeInput.value : '512';
                var frameMode = getFaceCameraFrameMode();
                var frameRatio = getFaceCameraFrameRatioCss();
                var horizontalShift = getFaceCameraHorizontalShift();
                var verticalShift = faceCameraVerticalShiftInput ? parseInt(faceCameraVerticalShiftInput.value, 10) : 0;
                var borderRadius = faceCameraBorderRadiusInput ? faceCameraBorderRadiusInput.value : '16';
                var background = faceCameraBackgroundInput ? faceCameraBackgroundInput.value : '#111111';
                var objectFit = faceCameraObjectFitInput ? faceCameraObjectFitInput.value : 'cover';

                if (!Number.isFinite(verticalShift)) {
                    verticalShift = 0;
                }

                verticalShift = Math.max(-100, Math.min(100, verticalShift));

                faceCameraPreviewShell.style.setProperty('--face-camera-preview-size', previewSize + 'px');
                faceCameraPreviewShell.style.setProperty('--face-camera-border-radius', borderRadius + 'px');
                faceCameraPreviewShell.style.setProperty('--face-camera-background', background);
                faceCameraPreviewShell.style.setProperty('--face-camera-object-fit', objectFit);
                faceCameraPreviewShell.style.setProperty('--face-camera-frame-ratio', frameRatio);
                faceCameraPreviewShell.style.setProperty('--face-camera-horizontal-shift', horizontalShift + '%');
                faceCameraPreviewShell.style.setProperty('--face-camera-vertical-shift', verticalShift + '%');

                if (faceCameraPreviewSizeLabel) {
                    faceCameraPreviewSizeLabel.textContent = previewSize + 'px';
                }

                if (faceCameraPreviewSizeBadge) {
                    faceCameraPreviewSizeBadge.textContent = previewSize + 'px';
                }

                if (faceCameraCaptureSizeLabel) {
                    faceCameraCaptureSizeLabel.textContent = captureSize + 'px';
                }

                if (faceCameraCaptureSizePreviewLabel) {
                    faceCameraCaptureSizePreviewLabel.textContent = 'Capture ' + captureSize + 'px';
                }

                if (faceCameraBorderRadiusLabel) {
                    faceCameraBorderRadiusLabel.textContent = borderRadius + 'px';
                }

                if (faceCameraPreviewModeBadge) {
                    faceCameraPreviewModeBadge.textContent = frameMode === 'wide' ? 'Wide' : '1:1';
                }

                if (faceCameraHorizontalShiftLabel) {
                    faceCameraHorizontalShiftLabel.textContent = horizontalShift + '%';
                }

                if (faceCameraVerticalShiftLabel) {
                    faceCameraVerticalShiftLabel.textContent = verticalShift + '%';
                }

                if (faceCameraFramePreviewLabel) {
                    faceCameraFramePreviewLabel.textContent = (frameMode === 'wide' ? 'Wide 4:3' : '1:1') + ' / X ' + horizontalShift + '% / Y ' + verticalShift + '%';
                }

                if (faceCameraPreviewStream && faceCameraPreviewMedia && faceCameraPreviewMedia.srcObject !== faceCameraPreviewStream) {
                    faceCameraPreviewMedia.srcObject = faceCameraPreviewStream;
                }
            };

            if (faceCameraPreviewSizeInput && faceCameraCaptureSizeInput && faceCameraFrameModeInput && faceCameraHorizontalShiftInput && faceCameraVerticalShiftInput && faceCameraBorderRadiusInput && faceCameraBackgroundInput && faceCameraObjectFitInput) {
                faceCameraPreviewSizeInput.addEventListener('input', syncFaceCameraPreview);
                faceCameraCaptureSizeInput.addEventListener('input', syncFaceCameraPreview);
                faceCameraFrameModeInput.addEventListener('change', function () {
                    syncFaceCameraPreview();
                    restartFaceCameraPreview();
                });
                faceCameraHorizontalShiftInput.addEventListener('input', syncFaceCameraPreview);
                faceCameraVerticalShiftInput.addEventListener('input', syncFaceCameraPreview);
                faceCameraBorderRadiusInput.addEventListener('input', syncFaceCameraPreview);
                faceCameraBackgroundInput.addEventListener('input', syncFaceCameraPreview);
                faceCameraObjectFitInput.addEventListener('change', syncFaceCameraPreview);
                syncFaceCameraPreview();
            }

            var settingsTabButtons = document.querySelectorAll('#settingsTab [data-bs-toggle="tab"]');

            settingsTabButtons.forEach(function (tabButton) {
                tabButton.addEventListener('shown.bs.tab', function (event) {
                    if (event.target && event.target.id === 'tab-menu-c-link') {
                        startFaceCameraPreview();
                        return;
                    }

                    if (event.relatedTarget && event.relatedTarget.id === 'tab-menu-c-link') {
                        stopFaceCameraPreview();
                    }
                });
            });

            if (document.getElementById('tab-menu-c') && document.getElementById('tab-menu-c').classList.contains('show') && document.getElementById('tab-menu-c').classList.contains('active')) {
                startFaceCameraPreview();
            }

            window.addEventListener('beforeunload', stopFaceCameraPreview);

            var createMenuAOptionRow = function (optionKey, optionPlaceholder, optionValue) {
                var rowElement = document.createElement('div');
                rowElement.className = 'menu-a-option-row';

                var inputGroup = document.createElement('div');
                inputGroup.className = 'input-group';

                var inputElement = document.createElement('input');
                inputElement.type = 'text';
                inputElement.name = optionKey + '[]';
                inputElement.className = 'form-control';
                inputElement.maxLength = 120;
                inputElement.placeholder = optionPlaceholder;
                inputElement.required = true;
                inputElement.value = optionValue || '';

                var removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-outline-danger js-remove-menu-a-option';
                removeButton.setAttribute('aria-label', 'Hapus opsi');
                removeButton.innerHTML = '<i class="fa-solid fa-xmark"></i>';

                inputGroup.appendChild(inputElement);
                inputGroup.appendChild(removeButton);
                rowElement.appendChild(inputGroup);

                return rowElement;
            };

            var ensureMenuAListHasRow = function (listElement) {
                if (!listElement) {
                    return;
                }

                if (listElement.querySelectorAll('.menu-a-option-row').length > 0) {
                    return;
                }

                var optionKey = listElement.getAttribute('data-option-key') || '';
                var optionPlaceholder = listElement.getAttribute('data-option-placeholder') || '';

                if (optionKey !== '') {
                    listElement.appendChild(createMenuAOptionRow(optionKey, optionPlaceholder, ''));
                }
            };

            document.querySelectorAll('.menu-a-option-list').forEach(function (listElement) {
                ensureMenuAListHasRow(listElement);
            });

            document.querySelectorAll('.js-add-menu-a-option').forEach(function (addButton) {
                addButton.addEventListener('click', function () {
                    var optionKey = addButton.getAttribute('data-option-key') || '';
                    var optionPlaceholder = addButton.getAttribute('data-option-placeholder') || '';
                    var listElement = document.getElementById('menuAOptionList-' + optionKey);

                    if (!listElement || optionKey === '') {
                        return;
                    }

                    var newRow = createMenuAOptionRow(optionKey, optionPlaceholder, '');
                    listElement.appendChild(newRow);

                    var rowInput = newRow.querySelector('input');
                    if (rowInput) {
                        rowInput.focus();
                    }
                });
            });

            document.addEventListener('click', function (event) {
                var removeButton = event.target.closest('.js-remove-menu-a-option');

                if (!removeButton) {
                    return;
                }

                var rowElement = removeButton.closest('.menu-a-option-row');
                var listElement = removeButton.closest('.menu-a-option-list');

                if (!rowElement || !listElement) {
                    return;
                }

                var totalRows = listElement.querySelectorAll('.menu-a-option-row').length;

                if (totalRows <= 1) {
                    var currentInput = rowElement.querySelector('input');

                    if (currentInput) {
                        currentInput.value = '';
                        currentInput.focus();
                    }

                    return;
                }

                rowElement.remove();
                ensureMenuAListHasRow(listElement);
            });
        });
    </script>
@endpush
