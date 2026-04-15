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
                    <button class="nav-link {{ $activeTab === 'running-text' ? 'active' : '' }}" id="tab-running-text-link" data-bs-toggle="tab" data-bs-target="#tab-running-text" type="button" role="tab" aria-controls="tab-running-text" aria-selected="{{ $activeTab === 'running-text' ? 'true' : 'false' }}">
                        Running Teks
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'menu-a' ? 'active' : '' }}" id="tab-menu-a-link" data-bs-toggle="tab" data-bs-target="#tab-menu-a" type="button" role="tab" aria-controls="tab-menu-a" aria-selected="{{ $activeTab === 'menu-a' ? 'true' : 'false' }}">
                        Master Data Sistem
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'menu-b' ? 'active' : '' }}" id="tab-menu-b-link" data-bs-toggle="tab" data-bs-target="#tab-menu-b" type="button" role="tab" aria-controls="tab-menu-b" aria-selected="{{ $activeTab === 'menu-b' ? 'true' : 'false' }}">
                        Menu B
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'menu-c' ? 'active' : '' }}" id="tab-menu-c-link" data-bs-toggle="tab" data-bs-target="#tab-menu-c" type="button" role="tab" aria-controls="tab-menu-c" aria-selected="{{ $activeTab === 'menu-c' ? 'true' : 'false' }}">
                        Menu C
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
                        <div class="col-12">
                            <label class="form-label">Judul Dashboard Public <span class="text-danger">*</span></label>
                            <input type="text" name="public_header_title" class="form-control" maxlength="120" value="{{ old('public_header_title', $settingValues['public_header_title']) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subjudul Dashboard Public <span class="text-danger">*</span></label>
                            <input type="text" name="public_header_subtitle" class="form-control" maxlength="200" value="{{ old('public_header_subtitle', $settingValues['public_header_subtitle']) }}" required>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted mb-1">Preview Header</div>
                            <div class="border rounded px-3 py-3 bg-light">
                                <div class="h5 fw-bold mb-1">{{ old('public_header_title', $settingValues['public_header_title']) }}</div>
                                <div class="text-secondary">{{ old('public_header_subtitle', $settingValues['public_header_subtitle']) }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Menu B
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'menu-c' ? 'show active' : '' }}" id="tab-menu-c" role="tabpanel" aria-labelledby="tab-menu-c-link" tabindex="0">
                    <form method="POST" action="{{ route('admin.settings.menu-c.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <label class="form-label">Label Tombol Peminjaman <span class="text-danger">*</span></label>
                            <input type="text" name="public_borrow_button_label" class="form-control" maxlength="80" value="{{ old('public_borrow_button_label', $settingValues['public_borrow_button_label']) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Label Tombol Pengembalian <span class="text-danger">*</span></label>
                            <input type="text" name="public_return_button_label" class="form-control" maxlength="80" value="{{ old('public_return_button_label', $settingValues['public_return_button_label']) }}" required>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted mb-1">Preview Tombol</div>
                            <div class="d-flex flex-column flex-md-row gap-2">
                                <button type="button" class="btn btn-outline-primary" disabled>{{ old('public_borrow_button_label', $settingValues['public_borrow_button_label']) }}</button>
                                <button type="button" class="btn btn-outline-success" disabled>{{ old('public_return_button_label', $settingValues['public_return_button_label']) }}</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Menu C
                            </button>
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
