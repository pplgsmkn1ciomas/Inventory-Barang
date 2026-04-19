@extends('layouts.app')

@section('content')
    @php
        $allowedTabs = ['running-text', 'menu-a', 'menu-b', 'menu-c', 'user-data', 'admin', 'log'];
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
            'face_camera_debug_enabled' => old('face_camera_debug_enabled', $settingValues['face_camera_debug_enabled'] ?? '1') === '1' ? '1' : '0',
        ];
        $cameraPreviewValues['face_camera_frame_ratio'] = $cameraPreviewValues['face_camera_frame_mode'] === 'wide' ? '4 / 3' : '1 / 1';
        $cameraPreviewSampleSvg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="800" height="800"><defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f172a"/><stop offset="100%" stop-color="#1d4ed8"/></linearGradient><linearGradient id="accent" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#93c5fd"/><stop offset="100%" stop-color="#60a5fa"/></linearGradient></defs><rect width="800" height="800" rx="72" fill="url(#bg)"/><circle cx="400" cy="250" r="110" fill="url(#accent)" opacity="0.95"/><rect x="210" y="385" width="380" height="260" rx="110" fill="#334155" opacity="0.95"/><rect x="140" y="682" width="520" height="54" rx="18" fill="#0b1220" opacity="0.8"/><rect x="110" y="120" width="180" height="42" rx="21" fill="#ffffff" opacity="0.16"/><rect x="510" y="120" width="180" height="42" rx="21" fill="#ffffff" opacity="0.16"/><text x="400" y="585" text-anchor="middle" font-family="Arial, sans-serif" font-size="42" font-weight="700" fill="#e2e8f0">Preview Kamera</text><text x="400" y="640" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" fill="#cbd5e1">Dynamic setting preview</text></svg>');
        $cameraPreviewSampleImage = 'data:image/svg+xml;charset=UTF-8,' . $cameraPreviewSampleSvg;
        $publicNavLogoPath = trim((string) ($settingValues['public_nav_logo_path'] ?? ''));
        $adminNavLogoPath = trim((string) ($settingValues['admin_nav_logo_path'] ?? ''));
        $publicNavLogoPreviewUrl = $publicNavLogoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($publicNavLogoPath)
            ? \Illuminate\Support\Facades\Storage::url($publicNavLogoPath)
            : '';
        $adminNavLogoPreviewUrl = $adminNavLogoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($adminNavLogoPath)
            ? \Illuminate\Support\Facades\Storage::url($adminNavLogoPath)
            : '';
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

        $logSearch = old('log_search', $activityLogFilters['search'] ?? '');
        $logAction = old('log_action', $activityLogFilters['action'] ?? '');
        $logTable = old('log_table', $activityLogFilters['table'] ?? '');
        $logDateFrom = old('log_date_from', $activityLogFilters['date_from'] ?? '');
        $logDateTo = old('log_date_to', $activityLogFilters['date_to'] ?? '');
        $logExportQuery = [
            'log_search' => $logSearch,
            'log_action' => $logAction,
            'log_table' => $logTable,
            'log_date_from' => $logDateFrom,
            'log_date_to' => $logDateTo,
        ];
        $latestActivityTimestamp = $activityLogStats['latest_timestamp'] ?? null;
        $latestActivityLabel = $latestActivityTimestamp !== null ? (string) $latestActivityTimestamp : '-';
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
                        Dashboard
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'menu-c' ? 'active' : '' }}" id="tab-menu-c-link" data-bs-toggle="tab" data-bs-target="#tab-menu-c" type="button" role="tab" aria-controls="tab-menu-c" aria-selected="{{ $activeTab === 'menu-c' ? 'true' : 'false' }}">
                        Kamera
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'user-data' ? 'active' : '' }}" id="tab-user-data-link" data-bs-toggle="tab" data-bs-target="#tab-user-data" type="button" role="tab" aria-controls="tab-user-data" aria-selected="{{ $activeTab === 'user-data' ? 'true' : 'false' }}">
                        Data Pengguna
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'admin' ? 'active' : '' }}" id="tab-admin-link" data-bs-toggle="tab" data-bs-target="#tab-admin" type="button" role="tab" aria-controls="tab-admin" aria-selected="{{ $activeTab === 'admin' ? 'true' : 'false' }}">
                        Admin
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link settings-tab-btn {{ $activeTab === 'log' ? 'active' : '' }}" id="tab-log-link" data-bs-toggle="tab" data-bs-target="#tab-log" type="button" role="tab" aria-controls="tab-log" aria-selected="{{ $activeTab === 'log' ? 'true' : 'false' }}">
                        Log
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

                                <div class="row g-3 align-items-start">
                                    <div class="col-md-4">
                                        <label class="form-label">Status Running Teks</label>
                                        <select id="runningTextEnabledInput" name="public_reminder_enabled" class="form-select" required>
                                            <option value="1" @selected(old('public_reminder_enabled', $settingValues['public_reminder_enabled']) === '1')>ON</option>
                                            <option value="0" @selected(old('public_reminder_enabled', $settingValues['public_reminder_enabled']) === '0')>OFF</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Warna Background</label>
                                        <input type="color" id="runningTextBgInput" name="public_reminder_background" class="form-control form-control-color w-100" value="{{ old('public_reminder_background', $settingValues['public_reminder_background']) }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Warna Teks</label>
                                        <input type="color" id="runningTextColorInput" name="public_reminder_text_color" class="form-control form-control-color w-100" value="{{ old('public_reminder_text_color', $settingValues['public_reminder_text_color']) }}" required>
                                    </div>
                                </div>

                                <div class="form-text mt-2">Pilih OFF untuk menyembunyikan banner running teks di dashboard public.</div>

                                <div class="row g-3 align-items-start mt-1">
                                    <div class="col-md-4">
                                        <label class="form-label d-flex justify-content-between">
                                            <span>Kecepatan Gerak</span>
                                            <span class="badge text-bg-secondary" id="runningTextSpeedLabel">{{ old('public_running_text_speed', $settingValues['public_running_text_speed']) }}s</span>
                                        </label>
                                        <input type="range" id="runningTextSpeedInput" name="public_running_text_speed" class="form-range" min="5" max="40" step="1" value="{{ old('public_running_text_speed', $settingValues['public_running_text_speed']) }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label d-flex justify-content-between">
                                            <span>Ukuran Font</span>
                                            <span class="badge text-bg-secondary" id="runningTextFontSizeLabel">{{ old('public_running_text_font_size', $settingValues['public_running_text_font_size']) }}px</span>
                                        </label>
                                        <input type="range" id="runningTextFontSizeInput" name="public_running_text_font_size" class="form-range" min="12" max="36" step="1" value="{{ old('public_running_text_font_size', $settingValues['public_running_text_font_size']) }}">
                                    </div>

                                    <div class="col-md-4">
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
                    <form method="POST" action="{{ route('admin.settings.menu-b.update') }}" class="row g-3" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="col-lg-8">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-heading text-primary"></i>
                                    <div class="fw-semibold">Konfigurasi Dashboard Public</div>
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

                                <hr class="my-3">

                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-bars-progress text-primary"></i>
                                    <div class="fw-semibold">Konfigurasi Navbar Public & Admin</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Logo Public (PNG)</label>
                                        <input type="file" id="menuBPublicNavLogoInput" name="public_nav_logo_file" class="form-control" accept="image/png">
                                        <div class="form-text">Upload logo PNG untuk mode public. Maksimal 2 MB.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Logo Admin (PNG)</label>
                                        <input type="file" id="menuBAdminNavLogoInput" name="admin_nav_logo_file" class="form-control" accept="image/png">
                                        <div class="form-text">Upload logo PNG untuk mode admin. Maksimal 2 MB.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Teks Brand Public <span class="text-danger">*</span></label>
                                        <input type="text" id="menuBPublicNavTextInput" name="public_nav_brand_text" class="form-control" maxlength="60" value="{{ old('public_nav_brand_text', $settingValues['public_nav_brand_text']) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Subteks Brand Public <span class="text-danger">*</span></label>
                                        <input type="text" id="menuBPublicNavSubtextInput" name="public_nav_brand_subtext" class="form-control" maxlength="120" value="{{ old('public_nav_brand_subtext', $settingValues['public_nav_brand_subtext']) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Teks Brand Admin <span class="text-danger">*</span></label>
                                        <input type="text" id="menuBAdminNavTitleInput" name="admin_nav_brand_title" class="form-control" maxlength="80" value="{{ old('admin_nav_brand_title', $settingValues['admin_nav_brand_title']) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Subteks Brand Admin <span class="text-danger">*</span></label>
                                        <input type="text" id="menuBAdminNavSubtitleInput" name="admin_nav_brand_subtitle" class="form-control" maxlength="140" value="{{ old('admin_nav_brand_subtitle', $settingValues['admin_nav_brand_subtitle']) }}" required>
                                    </div>
                                </div>

                                <div class="form-text mt-2">Perubahan logo dan teks navbar akan otomatis dipakai pada mode public dan admin.</div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3 h-100 bg-white">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                    <div class="fw-semibold">Preview Dashboard & Navbar</div>
                                </div>
                                <div class="small text-muted mb-2">Preview live untuk header, tombol, dan brand navbar.</div>

                                <div class="border rounded-3 p-3 bg-light mb-3">
                                    <div id="menuBPreviewTitle" class="h5 fw-bold mb-1">{{ old('public_header_title', $settingValues['public_header_title']) }}</div>
                                    <div id="menuBPreviewSubtitle" class="text-secondary">{{ old('public_header_subtitle', $settingValues['public_header_subtitle']) }}</div>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <button type="button" id="menuBPreviewBorrowButton" class="btn btn-outline-primary" disabled>{{ old('public_borrow_button_label', $settingValues['public_borrow_button_label']) }}</button>
                                    <button type="button" id="menuBPreviewReturnButton" class="btn btn-outline-success" disabled>{{ old('public_return_button_label', $settingValues['public_return_button_label']) }}</button>
                                </div>

                                <div class="small text-muted mt-3 mb-2">Preview Navbar Public</div>
                                <div class="border rounded-3 p-2 bg-light mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width: 34px; height: 34px;">
                                            <img
                                                id="menuBPreviewPublicNavLogo"
                                                src="{{ $publicNavLogoPreviewUrl }}"
                                                alt="Logo Public"
                                                class="{{ $publicNavLogoPreviewUrl === '' ? 'd-none' : '' }}"
                                                style="width: 100%; height: 100%; object-fit: contain; padding: 0.25rem;"
                                            >
                                            <i id="menuBPreviewPublicNavLogoPlaceholder" class="fa-solid fa-image {{ $publicNavLogoPreviewUrl === '' ? '' : 'd-none' }}"></i>
                                        </span>
                                        <div class="d-flex flex-column lh-sm">
                                            <span id="menuBPreviewPublicNavText" class="fw-bold">{{ old('public_nav_brand_text', $settingValues['public_nav_brand_text']) }}</span>
                                            <span id="menuBPreviewPublicNavSubtext" class="text-secondary small">| {{ old('public_nav_brand_subtext', $settingValues['public_nav_brand_subtext']) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="small text-muted mb-2">Preview Navbar Admin</div>
                                <div class="rounded-3 p-2 text-white" style="background: linear-gradient(118deg, #0a2349 0%, #0b5ed7 50%, #1f7ee5 100%);">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width: 34px; height: 34px; background: rgba(255, 255, 255, 0.16); border: 1px solid rgba(255, 255, 255, 0.35);">
                                            <img
                                                id="menuBPreviewAdminNavLogo"
                                                src="{{ $adminNavLogoPreviewUrl }}"
                                                alt="Logo Admin"
                                                class="{{ $adminNavLogoPreviewUrl === '' ? 'd-none' : '' }}"
                                                style="width: 100%; height: 100%; object-fit: contain; padding: 0.22rem;"
                                            >
                                            <i id="menuBPreviewAdminNavLogoPlaceholder" class="fa-solid fa-image {{ $adminNavLogoPreviewUrl === '' ? '' : 'd-none' }}"></i>
                                        </span>
                                        <div class="d-flex flex-column" style="line-height: 1.05;">
                                            <span id="menuBPreviewAdminNavTitle" class="fw-bold">{{ old('admin_nav_brand_title', $settingValues['admin_nav_brand_title']) }}</span>
                                            <span id="menuBPreviewAdminNavSubtitle" class="small" style="letter-spacing: 0.12em; text-transform: uppercase; opacity: 0.88;">{{ old('admin_nav_brand_subtitle', $settingValues['admin_nav_brand_subtitle']) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Dashboard
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

                                    <div class="col-12">
                                        <div class="border rounded-3 p-3 bg-white">
                                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                                <div>
                                                    <div class="fw-semibold">Debug On-Screen Face Scan</div>
                                                    <div class="small text-muted">Tampilkan panel debug skor deteksi per frame pada Scan Station Public.</div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input type="hidden" name="face_camera_debug_enabled" value="0">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="faceCameraDebugEnabledInput"
                                                        name="face_camera_debug_enabled"
                                                        value="1"
                                                        @checked($cameraPreviewValues['face_camera_debug_enabled'] === '1')
                                                    >
                                                    <label class="form-check-label fw-semibold ms-2" id="faceCameraDebugEnabledLabel" for="faceCameraDebugEnabledInput">
                                                        {{ $cameraPreviewValues['face_camera_debug_enabled'] === '1' ? 'ON' : 'OFF' }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
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
                                    <span class="badge {{ $cameraPreviewValues['face_camera_debug_enabled'] === '1' ? 'text-bg-dark' : 'text-bg-secondary' }}" id="faceCameraDebugPreviewBadge">{{ $cameraPreviewValues['face_camera_debug_enabled'] === '1' ? 'Debug ON' : 'Debug OFF' }}</span>
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

                <div class="tab-pane fade {{ $activeTab === 'user-data' ? 'show active' : '' }}" id="tab-user-data" role="tabpanel" aria-labelledby="tab-user-data-link" tabindex="0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#bulkDeleteUserModal"
                                @disabled($userBulkDeleteClassSummaries === [])
                            >
                                <i class="fa-solid fa-trash-can me-1"></i>Hapus Massal Data Pengguna
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-info btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#bulkDeleteUserInfoModal"
                            >
                                <i class="fa-solid fa-circle-info me-1"></i>Informasi Hapus Massal
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-warning border mb-3">
                        Fitur ini untuk mempercepat penghapusan data pengguna berdasarkan kategori kelas. Demi keamanan, akun admin dan pengguna yang memiliki riwayat peminjaman akan otomatis dilewati.
                    </div>

                    <div class="border rounded-3 p-3 bg-light-subtle mt-4">
                        <div class="fw-semibold mb-2">Ringkasan Dampak per Kelas</div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kelas</th>
                                        <th>Total Pengguna</th>
                                        <th>Siap Dihapus</th>
                                        <th>Admin Dilindungi</th>
                                        <th>Punya Riwayat Pinjaman</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($userBulkDeleteClassSummaries as $classSummary)
                                        <tr>
                                            <td class="fw-semibold">{{ $classSummary['kelas'] }}</td>
                                            <td>{{ number_format($classSummary['total_users']) }}</td>
                                            <td><span class="badge text-bg-success">{{ number_format($classSummary['deletable_users']) }}</span></td>
                                            <td><span class="badge text-bg-primary">{{ number_format($classSummary['admin_users']) }}</span></td>
                                            <td><span class="badge text-bg-warning">{{ number_format($classSummary['users_with_loans']) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">Belum ada data pengguna untuk dihapus.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal fade" id="bulkDeleteUserModal" tabindex="-1" aria-labelledby="bulkDeleteUserModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bulkDeleteUserModalLabel">Hapus Massal Data Pengguna</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form method="POST" action="{{ route('admin.settings.user-data.bulk-delete') }}" onsubmit="return confirm('Hapus massal data pengguna pada kelas yang dipilih? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')

                                    <div class="modal-body">
                                        <div class="alert alert-warning border mb-3">
                                            Hanya pengguna non-admin tanpa riwayat peminjaman yang akan dihapus. Pastikan kategori kelas yang dipilih sudah benar.
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="bulkDeleteClassInput" class="form-label">Kategori Kelas <span class="text-danger">*</span></label>
                                                <select
                                                    id="bulkDeleteClassInput"
                                                    name="bulk_delete_class"
                                                    class="form-select @error('bulk_delete_class') is-invalid @enderror"
                                                    @disabled($userBulkDeleteClassSummaries === [])
                                                    required
                                                >
                                                    <option value="">Pilih kelas...</option>
                                                    @foreach($userBulkDeleteClassSummaries as $classSummary)
                                                        <option value="{{ $classSummary['kelas'] }}" @selected(old('bulk_delete_class') === $classSummary['kelas'])>
                                                            {{ $classSummary['kelas'] }} - {{ number_format($classSummary['deletable_users']) }} siap dihapus
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('bulk_delete_class')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <div class="form-check user-select-none">
                                                    <input class="form-check-input @error('bulk_delete_confirm') is-invalid @enderror" type="checkbox" id="bulkDeleteConfirmInput" name="bulk_delete_confirm" value="1" @checked(old('bulk_delete_confirm'))>
                                                    <label class="form-check-label" for="bulkDeleteConfirmInput">
                                                        Saya memahami tindakan ini bersifat permanen.
                                                    </label>
                                                    @error('bulk_delete_confirm')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger" @disabled($userBulkDeleteClassSummaries === [])>
                                            <i class="fa-solid fa-trash-can me-1"></i>Hapus Massal Data Pengguna
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="bulkDeleteUserInfoModal" tabindex="-1" aria-labelledby="bulkDeleteUserInfoModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bulkDeleteUserInfoModalLabel">Informasi Hapus Massal Data Pengguna</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-3">Fitur ini dirancang untuk mempercepat pembersihan data pengguna berdasarkan kelas tanpa perlu menghapus satu per satu.</p>

                                    <div class="fw-semibold mb-2">Cara menggunakan</div>
                                    <ol class="mb-3 ps-3">
                                        <li>Pilih kategori kelas yang ingin diproses.</li>
                                        <li>Cek estimasi dampak pada tabel Ringkasan Dampak per Kelas.</li>
                                        <li>Centang konfirmasi tindakan permanen.</li>
                                        <li>Klik tombol Hapus Massal Data Pengguna.</li>
                                    </ol>

                                    <div class="fw-semibold mb-2">Aturan keamanan otomatis</div>
                                    <ul class="mb-3 ps-3">
                                        <li>Akun dengan role admin tidak akan dihapus.</li>
                                        <li>Pengguna yang memiliki riwayat peminjaman tidak akan dihapus.</li>
                                        <li>Face thumbnail pengguna yang terhapus akan ikut dibersihkan dari storage.</li>
                                    </ul>

                                    <div class="alert alert-danger mb-0">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Tindakan hapus massal bersifat permanen dan tidak dapat dibatalkan.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'admin' ? 'show active' : '' }}" id="tab-admin" role="tabpanel" aria-labelledby="tab-admin-link" tabindex="0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <div class="fw-semibold">Pengaturan Akun Admin</div>
                            <div class="text-muted small">Ubah password akun admin yang sedang login.</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.admin-password.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')

                        <div class="col-lg-7">
                            <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                <div class="mb-3">
                                    <label for="adminCurrentPasswordInput" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            id="adminCurrentPasswordInput"
                                            name="current_password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            autocomplete="current-password"
                                            required
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary js-password-toggle"
                                            data-password-target="adminCurrentPasswordInput"
                                            data-show-label="Lihat"
                                            data-hide-label="Sembunyikan"
                                            data-show-aria="Tampilkan password saat ini"
                                            data-hide-aria="Sembunyikan password saat ini"
                                            aria-label="Tampilkan password saat ini"
                                            aria-pressed="false"
                                        >
                                            <i class="fa-solid fa-eye" data-password-toggle-icon></i>
                                            <span class="ms-1 d-none d-sm-inline" data-password-toggle-label>Lihat</span>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="adminNewPasswordInput" class="form-label">Password Baru <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            id="adminNewPasswordInput"
                                            name="new_password"
                                            class="form-control @error('new_password') is-invalid @enderror"
                                            autocomplete="new-password"
                                            minlength="8"
                                            required
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary js-password-toggle"
                                            data-password-target="adminNewPasswordInput"
                                            data-show-label="Lihat"
                                            data-hide-label="Sembunyikan"
                                            data-show-aria="Tampilkan password baru"
                                            data-hide-aria="Sembunyikan password baru"
                                            aria-label="Tampilkan password baru"
                                            aria-pressed="false"
                                        >
                                            <i class="fa-solid fa-eye" data-password-toggle-icon></i>
                                            <span class="ms-1 d-none d-sm-inline" data-password-toggle-label>Lihat</span>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label for="adminNewPasswordConfirmationInput" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            id="adminNewPasswordConfirmationInput"
                                            name="new_password_confirmation"
                                            class="form-control"
                                            autocomplete="new-password"
                                            minlength="8"
                                            required
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary js-password-toggle"
                                            data-password-target="adminNewPasswordConfirmationInput"
                                            data-show-label="Lihat"
                                            data-hide-label="Sembunyikan"
                                            data-show-aria="Tampilkan konfirmasi password baru"
                                            data-hide-aria="Sembunyikan konfirmasi password baru"
                                            aria-label="Tampilkan konfirmasi password baru"
                                            aria-pressed="false"
                                        >
                                            <i class="fa-solid fa-eye" data-password-toggle-icon></i>
                                            <span class="ms-1 d-none d-sm-inline" data-password-toggle-label>Lihat</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="border rounded-3 p-3 bg-white h-100">
                                <div class="fw-semibold mb-2">Aturan Password</div>
                                <ul class="mb-0 ps-3 small text-muted">
                                    <li>Minimal 8 karakter.</li>
                                    <li>Harus berbeda dari password saat ini.</li>
                                    <li>Simpan password dengan aman dan jangan dibagikan.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-key me-2"></i>Ubah Password Admin
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'log' ? 'show active' : '' }}" id="tab-log" role="tabpanel" aria-labelledby="tab-log-link" tabindex="0">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <div class="fw-semibold">Log Aktivitas</div>
                            <div class="text-muted small">Pantau jejak aktivitas sistem untuk proses audit dan troubleshooting.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.settings.logs.export', $logExportQuery) }}" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-file-excel me-1"></i>Export Excel
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cleanupLogModal">
                                <i class="fa-solid fa-broom me-1"></i>Cleanup Log Lama
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                <div class="text-muted small">Total Aktivitas</div>
                                <div class="h4 mb-0 fw-bold">{{ number_format((int) ($activityLogStats['total'] ?? 0)) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                <div class="text-muted small">Hari Ini</div>
                                <div class="h4 mb-0 fw-bold">{{ number_format((int) ($activityLogStats['today'] ?? 0)) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                <div class="text-muted small">7 Hari Terakhir</div>
                                <div class="h4 mb-0 fw-bold">{{ number_format((int) ($activityLogStats['last_7_days'] ?? 0)) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                <div class="text-muted small">Aktivitas Terakhir</div>
                                <div class="small fw-semibold text-break">{{ $latestActivityLabel }}</div>
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.settings.index') }}" class="row g-2 align-items-end mb-3">
                        <input type="hidden" name="tab" value="log">

                        <div class="col-12 col-lg-3">
                            <label for="logSearchInput" class="form-label">Cari Aktivitas</label>
                            <input
                                type="text"
                                id="logSearchInput"
                                name="log_search"
                                value="{{ $logSearch }}"
                                class="form-control"
                                placeholder="Cari aksi, tabel, data, detail"
                            >
                        </div>

                        <div class="col-6 col-lg-2">
                            <label for="logActionInput" class="form-label">Aksi</label>
                            <select id="logActionInput" name="log_action" class="form-select">
                                <option value="">Semua aksi</option>
                                @foreach($activityLogActionOptions as $actionOption)
                                    <option value="{{ $actionOption }}" @selected($logAction === $actionOption)>{{ $actionOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label for="logTableInput" class="form-label">Tabel</label>
                            <select id="logTableInput" name="log_table" class="form-select">
                                <option value="">Semua tabel</option>
                                @foreach($activityLogTableOptions as $tableOption)
                                    <option value="{{ $tableOption }}" @selected($logTable === $tableOption)>{{ $tableOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-lg-2">
                            <label for="logDateFromInput" class="form-label">Dari Tanggal</label>
                            <input type="date" id="logDateFromInput" name="log_date_from" value="{{ $logDateFrom }}" class="form-control">
                        </div>

                        <div class="col-6 col-lg-2">
                            <label for="logDateToInput" class="form-label">Sampai Tanggal</label>
                            <input type="date" id="logDateToInput" name="log_date_to" value="{{ $logDateTo }}" class="form-control">
                        </div>

                        <div class="col-12 col-lg-1 d-grid gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa-solid fa-filter"></i>
                            </button>
                            <a href="{{ route('admin.settings.index', ['tab' => 'log']) }}" class="btn btn-outline-secondary w-100">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive border rounded-3">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 160px;">Waktu</th>
                                    <th style="width: 120px;">Aksi</th>
                                    <th style="width: 130px;">Tabel</th>
                                    <th>Data</th>
                                    <th>Detail</th>
                                    <th style="width: 220px;">User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityLogs as $activityLog)
                                    @php
                                        $action = strtoupper((string) $activityLog->action);
                                        $actionBadgeClass = match ($action) {
                                            'BORROW' => 'text-bg-primary',
                                            'RETURN' => 'text-bg-success',
                                            'UPDATE' => 'text-bg-warning',
                                            'DELETE', 'BULK_DELETE' => 'text-bg-danger',
                                            default => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="small text-nowrap">{{ $activityLog->timestamp?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                        <td><span class="badge {{ $actionBadgeClass }}">{{ $action }}</span></td>
                                        <td><span class="badge text-bg-light">{{ $activityLog->table_name }}</span></td>
                                        <td class="activity-log-data" title="{{ (string) $activityLog->data }}">{{ \Illuminate\Support\Str::limit((string) $activityLog->data, 140) }}</td>
                                        <td class="activity-log-details" title="{{ (string) ($activityLog->details ?? '') }}">{{ \Illuminate\Support\Str::limit((string) ($activityLog->details ?? '-'), 180) }}</td>
                                        <td class="activity-log-user-agent" title="{{ (string) ($activityLog->user_agent ?? '-') }}">{{ \Illuminate\Support\Str::limit((string) ($activityLog->user_agent ?? '-'), 80) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada log aktivitas yang tersimpan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($activityLogs->hasPages())
                        <div class="mt-3">{{ $activityLogs->links() }}</div>
                    @endif

                    <div class="modal fade" id="cleanupLogModal" tabindex="-1" aria-labelledby="cleanupLogModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cleanupLogModalLabel">Cleanup Log Lama</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <form method="POST" action="{{ route('admin.settings.logs.cleanup') }}" id="cleanupLogForm">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" id="cleanupMasterPasswordInput" name="cleanup_master_password" value="">

                                    <div class="modal-body">
                                        <div class="alert alert-warning border mb-3">
                                            Gunakan fitur ini untuk menghapus log aktivitas pada rentang tanggal tertentu. Anda wajib memasukkan password admin aktif untuk konfirmasi.
                                        </div>

                                        @error('cleanup_master_password')
                                            <div class="alert alert-danger border py-2 mb-3">{{ $message }}</div>
                                        @enderror

                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label for="cleanupDateFromInput" class="form-label">Dari Tanggal</label>
                                                <input
                                                    type="date"
                                                    id="cleanupDateFromInput"
                                                    name="cleanup_date_from"
                                                    value="{{ old('cleanup_date_from', '') }}"
                                                    class="form-control @error('cleanup_date_from') is-invalid @enderror"
                                                    required
                                                >
                                                @error('cleanup_date_from')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label for="cleanupDateToInput" class="form-label">Sampai Tanggal</label>
                                                <input
                                                    type="date"
                                                    id="cleanupDateToInput"
                                                    name="cleanup_date_to"
                                                    value="{{ old('cleanup_date_to', '') }}"
                                                    class="form-control @error('cleanup_date_to') is-invalid @enderror"
                                                    required
                                                >
                                                @error('cleanup_date_to')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <label for="cleanupPasswordInput" class="form-label">Password Admin</label>
                                                <input
                                                    type="password"
                                                    id="cleanupPasswordInput"
                                                    name="cleanup_password"
                                                    class="form-control @error('cleanup_password') is-invalid @enderror"
                                                    autocomplete="current-password"
                                                    placeholder="Masukkan password admin"
                                                    required
                                                >
                                                @error('cleanup_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <div class="form-check user-select-none">
                                                    <input class="form-check-input @error('cleanup_confirm') is-invalid @enderror" type="checkbox" id="cleanupConfirmInput" name="cleanup_confirm" value="1" @checked(old('cleanup_confirm'))>
                                                    <label class="form-check-label" for="cleanupConfirmInput">
                                                        Saya paham cleanup log bersifat permanen.
                                                    </label>
                                                    @error('cleanup_confirm')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <div class="w-100 text-muted small mb-2 text-start">
                                            Setelah klik tombol Cleanup Log, sistem akan meminta verifikasi password master emergency.
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fa-solid fa-trash-can me-1"></i>Cleanup Log
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="cleanupLogMasterModal" tabindex="-1" aria-labelledby="cleanupLogMasterModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cleanupLogMasterModalLabel">Verifikasi Master Emergency</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-warning border mb-3">
                                        Langkah keamanan tambahan: masukkan password master emergency untuk melanjutkan cleanup log.
                                    </div>

                                    <div class="mb-0">
                                        <label for="cleanupMasterPasswordPromptInput" class="form-label">Password Master Emergency</label>
                                        <input
                                            type="password"
                                            id="cleanupMasterPasswordPromptInput"
                                            class="form-control"
                                            autocomplete="off"
                                            placeholder="Masukkan password master emergency"
                                        >
                                        <div class="invalid-feedback d-none" id="cleanupMasterPasswordPromptFeedback">Password master emergency wajib diisi.</div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button>
                                    <button type="button" class="btn btn-danger" id="cleanupMasterConfirmButton">
                                        <i class="fa-solid fa-shield-halved me-1"></i>Verifikasi & Cleanup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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

        .activity-log-data,
        .activity-log-details {
            max-width: 320px;
        }

        .activity-log-user-agent {
            max-width: 220px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var textInput = document.getElementById('runningTextInput');
            var shouldOpenBulkDeleteUserModal = @json($activeTab === 'user-data' && ($errors->has('bulk_delete_class') || $errors->has('bulk_delete_confirm')));
            var bulkDeleteUserModalElement = document.getElementById('bulkDeleteUserModal');
            var shouldOpenCleanupLogModal = @json($activeTab === 'log' && ($errors->has('cleanup_date_from') || $errors->has('cleanup_date_to') || $errors->has('cleanup_password') || $errors->has('cleanup_master_password') || $errors->has('cleanup_confirm')));
            var cleanupLogModalElement = document.getElementById('cleanupLogModal');
            var cleanupLogForm = document.getElementById('cleanupLogForm');
            var cleanupMasterPasswordInput = document.getElementById('cleanupMasterPasswordInput');
            var cleanupMasterModalElement = document.getElementById('cleanupLogMasterModal');
            var cleanupMasterPasswordPromptInput = document.getElementById('cleanupMasterPasswordPromptInput');
            var cleanupMasterPasswordPromptFeedback = document.getElementById('cleanupMasterPasswordPromptFeedback');
            var cleanupMasterConfirmButton = document.getElementById('cleanupMasterConfirmButton');
            var cleanupLogModal = null;
            var cleanupMasterModal = null;
            var cleanupMasterVerificationPassed = false;
            var cleanupMasterFlowActive = false;
            var cleanupOpenMasterAfterLogHidden = false;

            if (shouldOpenBulkDeleteUserModal && bulkDeleteUserModalElement && typeof bootstrap !== 'undefined') {
                var bulkDeleteUserModal = new bootstrap.Modal(bulkDeleteUserModalElement);
                bulkDeleteUserModal.show();
            }

            if (cleanupLogModalElement && typeof bootstrap !== 'undefined') {
                cleanupLogModal = new bootstrap.Modal(cleanupLogModalElement);
            }

            if (cleanupMasterModalElement && typeof bootstrap !== 'undefined') {
                cleanupMasterModal = new bootstrap.Modal(cleanupMasterModalElement);
            }

            if (shouldOpenCleanupLogModal && cleanupLogModal) {
                cleanupLogModal.show();
            }

            var setCleanupMasterPromptError = function (message) {
                if (!cleanupMasterPasswordPromptInput || !cleanupMasterPasswordPromptFeedback) {
                    return;
                }

                var normalizedMessage = (message || '').toString().trim();

                if (normalizedMessage !== '') {
                    cleanupMasterPasswordPromptFeedback.textContent = normalizedMessage;
                    cleanupMasterPasswordPromptFeedback.classList.remove('d-none');
                    cleanupMasterPasswordPromptInput.classList.add('is-invalid');

                    return;
                }

                cleanupMasterPasswordPromptFeedback.classList.add('d-none');
                cleanupMasterPasswordPromptInput.classList.remove('is-invalid');
            };

            var openCleanupMasterModal = function () {
                if (!cleanupLogModal || !cleanupMasterModal || !cleanupMasterPasswordPromptInput) {
                    return;
                }

                cleanupMasterFlowActive = true;
                cleanupOpenMasterAfterLogHidden = true;
                cleanupMasterPasswordPromptInput.value = '';
                setCleanupMasterPromptError('');

                if (cleanupMasterPasswordInput) {
                    cleanupMasterPasswordInput.value = '';
                }

                cleanupLogModal.hide();
            };

            if (cleanupLogForm && cleanupMasterPasswordInput) {
                cleanupLogForm.addEventListener('submit', function (event) {
                    if (cleanupMasterVerificationPassed) {
                        cleanupMasterVerificationPassed = false;
                        return;
                    }

                    if (!cleanupLogModal || !cleanupMasterModal) {
                        return;
                    }

                    event.preventDefault();
                    openCleanupMasterModal();
                });
            }

            if (cleanupLogModalElement && cleanupLogModal) {
                cleanupLogModalElement.addEventListener('hidden.bs.modal', function () {
                    if (cleanupOpenMasterAfterLogHidden && cleanupMasterModal) {
                        cleanupMasterModal.show();
                    }

                    cleanupOpenMasterAfterLogHidden = false;
                });

                cleanupLogModalElement.addEventListener('shown.bs.modal', function () {
                    cleanupMasterVerificationPassed = false;

                    if (cleanupMasterPasswordInput) {
                        cleanupMasterPasswordInput.value = '';
                    }
                });
            }

            if (cleanupMasterModalElement) {
                cleanupMasterModalElement.addEventListener('hidden.bs.modal', function () {
                    if (cleanupMasterFlowActive && cleanupLogModal) {
                        cleanupMasterFlowActive = false;
                        cleanupLogModal.show();

                        return;
                    }

                    cleanupMasterFlowActive = false;
                });
            }

            if (cleanupMasterConfirmButton && cleanupMasterPasswordPromptInput && cleanupLogForm && cleanupMasterPasswordInput) {
                cleanupMasterConfirmButton.addEventListener('click', function () {
                    var masterPassword = cleanupMasterPasswordPromptInput.value.trim();

                    if (masterPassword === '') {
                        setCleanupMasterPromptError('Password master emergency wajib diisi.');
                        cleanupMasterPasswordPromptInput.focus();

                        return;
                    }

                    setCleanupMasterPromptError('');
                    cleanupMasterPasswordInput.value = masterPassword;
                    cleanupMasterVerificationPassed = true;
                    cleanupMasterFlowActive = false;

                    if (cleanupMasterModal) {
                        cleanupMasterModal.hide();
                    }

                    if (typeof cleanupLogForm.requestSubmit === 'function') {
                        cleanupLogForm.requestSubmit();

                        return;
                    }

                    cleanupLogForm.submit();
                });

                cleanupMasterPasswordPromptInput.addEventListener('input', function () {
                    if (cleanupMasterPasswordPromptInput.value.trim() !== '') {
                        setCleanupMasterPromptError('');
                    }
                });

                cleanupMasterPasswordPromptInput.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();
                    cleanupMasterConfirmButton.click();
                });
            }

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
            var menuBPublicNavLogoInput = document.getElementById('menuBPublicNavLogoInput');
            var menuBAdminNavLogoInput = document.getElementById('menuBAdminNavLogoInput');
            var menuBPublicNavTextInput = document.getElementById('menuBPublicNavTextInput');
            var menuBPublicNavSubtextInput = document.getElementById('menuBPublicNavSubtextInput');
            var menuBAdminNavTitleInput = document.getElementById('menuBAdminNavTitleInput');
            var menuBAdminNavSubtitleInput = document.getElementById('menuBAdminNavSubtitleInput');
            var menuBPreviewTitle = document.getElementById('menuBPreviewTitle');
            var menuBPreviewSubtitle = document.getElementById('menuBPreviewSubtitle');
            var menuBPreviewBorrowButton = document.getElementById('menuBPreviewBorrowButton');
            var menuBPreviewReturnButton = document.getElementById('menuBPreviewReturnButton');
            var menuBPreviewPublicNavLogo = document.getElementById('menuBPreviewPublicNavLogo');
            var menuBPreviewAdminNavLogo = document.getElementById('menuBPreviewAdminNavLogo');
            var menuBPreviewPublicNavLogoPlaceholder = document.getElementById('menuBPreviewPublicNavLogoPlaceholder');
            var menuBPreviewAdminNavLogoPlaceholder = document.getElementById('menuBPreviewAdminNavLogoPlaceholder');
            var menuBPreviewPublicNavText = document.getElementById('menuBPreviewPublicNavText');
            var menuBPreviewPublicNavSubtext = document.getElementById('menuBPreviewPublicNavSubtext');
            var menuBPreviewAdminNavTitle = document.getElementById('menuBPreviewAdminNavTitle');
            var menuBPreviewAdminNavSubtitle = document.getElementById('menuBPreviewAdminNavSubtitle');

            var syncMenuBLogoPreview = function (fileInput, imageElement, placeholderElement) {
                if (!fileInput || !imageElement || !fileInput.files || !fileInput.files[0]) {
                    return;
                }

                var selectedFile = fileInput.files[0];

                if (selectedFile.type !== 'image/png') {
                    return;
                }

                var fileReader = new FileReader();
                fileReader.onload = function (event) {
                    var result = event && event.target ? event.target.result : null;

                    if (typeof result !== 'string' || result === '') {
                        return;
                    }

                    imageElement.src = result;
                    imageElement.classList.remove('d-none');

                    if (placeholderElement) {
                        placeholderElement.classList.add('d-none');
                    }
                };
                fileReader.readAsDataURL(selectedFile);
            };

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

                if (menuBPreviewPublicNavText && menuBPublicNavTextInput) {
                    menuBPreviewPublicNavText.textContent = menuBPublicNavTextInput.value || 'SIM-IV';
                }

                if (menuBPreviewPublicNavSubtext && menuBPublicNavSubtextInput) {
                    menuBPreviewPublicNavSubtext.textContent = '| ' + (menuBPublicNavSubtextInput.value || 'School Inventory System');
                }

                if (menuBPreviewAdminNavTitle && menuBAdminNavTitleInput) {
                    menuBPreviewAdminNavTitle.textContent = menuBAdminNavTitleInput.value || 'Inventory Barang';
                }

                if (menuBPreviewAdminNavSubtitle && menuBAdminNavSubtitleInput) {
                    menuBPreviewAdminNavSubtitle.textContent = menuBAdminNavSubtitleInput.value || 'SMK NEGERI 1 CIOMAS';
                }
            };

            var menuBPreviewInputs = [
                menuBHeaderTitleInput,
                menuBHeaderSubtitleInput,
                menuBBorrowLabelInput,
                menuBReturnLabelInput,
                menuBPublicNavTextInput,
                menuBPublicNavSubtextInput,
                menuBAdminNavTitleInput,
                menuBAdminNavSubtitleInput,
            ].filter(function (element) {
                return Boolean(element);
            });

            if (menuBPreviewInputs.length > 0) {
                menuBPreviewInputs.forEach(function (element) {
                    element.addEventListener('input', syncMenuBPreview);
                });
                syncMenuBPreview();
            }

            if (menuBPublicNavLogoInput && menuBPreviewPublicNavLogo) {
                menuBPublicNavLogoInput.addEventListener('change', function () {
                    syncMenuBLogoPreview(menuBPublicNavLogoInput, menuBPreviewPublicNavLogo, menuBPreviewPublicNavLogoPlaceholder);
                });
            }

            if (menuBAdminNavLogoInput && menuBPreviewAdminNavLogo) {
                menuBAdminNavLogoInput.addEventListener('change', function () {
                    syncMenuBLogoPreview(menuBAdminNavLogoInput, menuBPreviewAdminNavLogo, menuBPreviewAdminNavLogoPlaceholder);
                });
            }

            var faceCameraPreviewSizeInput = document.getElementById('faceCameraPreviewSizeInput');
            var faceCameraCaptureSizeInput = document.getElementById('faceCameraCaptureSizeInput');
            var faceCameraFrameModeInput = document.getElementById('faceCameraFrameModeInput');
            var faceCameraHorizontalShiftInput = document.getElementById('faceCameraHorizontalShiftInput');
            var faceCameraVerticalShiftInput = document.getElementById('faceCameraVerticalShiftInput');
            var faceCameraBorderRadiusInput = document.getElementById('faceCameraBorderRadiusInput');
            var faceCameraBackgroundInput = document.getElementById('faceCameraBackgroundInput');
            var faceCameraObjectFitInput = document.getElementById('faceCameraObjectFitInput');
            var faceCameraDebugEnabledInput = document.getElementById('faceCameraDebugEnabledInput');
            var faceCameraDebugEnabledLabel = document.getElementById('faceCameraDebugEnabledLabel');
            var faceCameraPreviewShell = document.getElementById('faceCameraPreviewShell');
            var faceCameraPreviewMedia = document.getElementById('faceCameraPreviewMedia');
            var faceCameraPreviewStatusBadge = document.getElementById('faceCameraPreviewStatusBadge');
            var faceCameraPreviewSizeLabel = document.getElementById('faceCameraPreviewSizeLabel');
            var faceCameraPreviewSizeBadge = document.getElementById('faceCameraPreviewSizeBadge');
            var faceCameraPreviewModeBadge = document.getElementById('faceCameraPreviewModeBadge');
            var faceCameraDebugPreviewBadge = document.getElementById('faceCameraDebugPreviewBadge');
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
                var debugEnabled = faceCameraDebugEnabledInput ? faceCameraDebugEnabledInput.checked : true;

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

                if (faceCameraDebugEnabledLabel) {
                    faceCameraDebugEnabledLabel.textContent = debugEnabled ? 'ON' : 'OFF';
                }

                if (faceCameraDebugPreviewBadge) {
                    faceCameraDebugPreviewBadge.textContent = debugEnabled ? 'Debug ON' : 'Debug OFF';
                    faceCameraDebugPreviewBadge.className = 'badge ' + (debugEnabled ? 'text-bg-dark' : 'text-bg-secondary');
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

                if (faceCameraDebugEnabledInput) {
                    faceCameraDebugEnabledInput.addEventListener('change', syncFaceCameraPreview);
                }

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

            document.querySelectorAll('.js-password-toggle').forEach(function (toggleButton) {
                var targetId = toggleButton.getAttribute('data-password-target') || '';
                var targetInput = targetId !== '' ? document.getElementById(targetId) : null;

                if (!targetInput) {
                    return;
                }

                var toggleIcon = toggleButton.querySelector('[data-password-toggle-icon]');
                var toggleLabel = toggleButton.querySelector('[data-password-toggle-label]');
                var showLabel = toggleButton.getAttribute('data-show-label') || 'Lihat';
                var hideLabel = toggleButton.getAttribute('data-hide-label') || 'Sembunyikan';
                var showAria = toggleButton.getAttribute('data-show-aria') || 'Tampilkan password';
                var hideAria = toggleButton.getAttribute('data-hide-aria') || 'Sembunyikan password';

                var syncPasswordToggleState = function () {
                    var isMasked = targetInput.type !== 'text';

                    toggleButton.setAttribute('aria-pressed', isMasked ? 'false' : 'true');
                    toggleButton.setAttribute('aria-label', isMasked ? showAria : hideAria);

                    if (toggleLabel) {
                        toggleLabel.textContent = isMasked ? showLabel : hideLabel;
                    }

                    if (toggleIcon) {
                        toggleIcon.classList.toggle('fa-eye', isMasked);
                        toggleIcon.classList.toggle('fa-eye-slash', !isMasked);
                    }
                };

                toggleButton.addEventListener('click', function () {
                    targetInput.type = targetInput.type === 'password' ? 'text' : 'password';
                    syncPasswordToggleState();
                });

                targetInput.addEventListener('blur', function () {
                    if (targetInput.type === 'text') {
                        targetInput.type = 'password';
                        syncPasswordToggleState();
                    }
                });

                syncPasswordToggleState();
            });
        });
    </script>
@endpush
