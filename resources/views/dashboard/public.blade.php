@extends('layouts.app')

@section('content')
    @php
        $initialMode = old('flow_mode', old('condition') ? 'return' : 'borrow');
        $publicSettings = $publicSettings ?? [];
        $publicHeaderTitle = $publicSettings['public_header_title'] ?? 'Dashboard Inventaris';
        $publicHeaderSubtitle = $publicSettings['public_header_subtitle'] ?? 'Sistem Peminjaman & Pengembalian Aset Sekolah';
        $publicBorrowButtonLabel = $publicSettings['public_borrow_button_label'] ?? 'Peminjaman Barang';
        $publicReturnButtonLabel = $publicSettings['public_return_button_label'] ?? 'Pengembalian Barang';
        $publicReminderEnabled = ($publicSettings['public_reminder_enabled'] ?? '1') === '1';
        $publicReminderBackground = $publicSettings['public_reminder_background'] ?? '#0A0A0A';
        $publicReminderTextColor = $publicSettings['public_reminder_text_color'] ?? '#FFFFFF';
        $publicReminderSpeed = (int) ($publicSettings['public_running_text_speed'] ?? 15);
        $publicReminderFontSize = (int) ($publicSettings['public_running_text_font_size'] ?? 17);
        $publicReminderFontFamily = $publicSettings['public_running_text_font_family'] ?? 'system-ui, sans-serif';
        $classOptions = $classOptions ?? [];
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
        $registerFacePreviewSampleSvg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="800" height="800"><defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f172a"/><stop offset="100%" stop-color="#2563eb"/></linearGradient><linearGradient id="accent" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#bfdbfe"/><stop offset="100%" stop-color="#60a5fa"/></linearGradient></defs><rect width="800" height="800" rx="72" fill="url(#bg)"/><circle cx="400" cy="245" r="118" fill="url(#accent)" opacity="0.9"/><rect x="210" y="370" width="380" height="270" rx="120" fill="#334155" opacity="0.96"/><rect x="150" y="670" width="500" height="48" rx="18" fill="#0b1220" opacity="0.82"/><text x="400" y="585" text-anchor="middle" font-family="Arial, sans-serif" font-size="42" font-weight="700" fill="#e2e8f0">Face Recognition</text><text x="400" y="640" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" fill="#cbd5e1">Siapkan kamera sebelum simpan</text></svg>');
        $registerFacePreviewSampleImage = 'data:image/svg+xml;charset=UTF-8,' . $registerFacePreviewSampleSvg;
    @endphp

    <div class="public-dashboard">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3 js-public-flash-notification" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3 js-public-flash-notification" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3 pb-3 border-bottom public-header">
            <div>
                <h1 class="h2 fw-bold mb-1">{{ $publicHeaderTitle }}</h1>
                <p class="text-secondary mb-0">{{ $publicHeaderSubtitle }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#publicRegisterModal">
                    <i class="fa-solid fa-user-plus me-2"></i>Register Siswa
                </button>
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#howToUseModal">
                    <i class="fa-solid fa-circle-info me-2"></i>Cara Pakai Aplikasi
                </button>
            </div>
        </div>

        <div class="row g-3 justify-content-center mb-4">
            <div class="col-md-6 col-lg-4">
                <button type="button" class="btn mode-toggle-btn mode-borrow w-100" data-target-mode="borrow">
                    <i class="fa-solid fa-handshake-angle me-2"></i>{{ $publicBorrowButtonLabel }}
                </button>
            </div>
            <div class="col-md-6 col-lg-4">
                <button type="button" class="btn mode-toggle-btn mode-return w-100" data-target-mode="return">
                    <i class="fa-solid fa-rotate-left me-2"></i>{{ $publicReturnButtonLabel }}
                </button>
            </div>
        </div>

        <div class="row g-3 align-items-stretch">
            <div class="col-lg-4">
                <div class="card h-100 public-card">
                    <div id="scanCardHeader" class="card-header border-0 py-3 text-white bg-primary public-scan-header">
                        <i class="fa-solid fa-qrcode me-2"></i><span id="scanCardTitle">Scan Station Peminjaman</span>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.loans.borrow') }}" class="vstack gap-3" data-mode-form="borrow">
                            @csrf
                            <input type="hidden" name="flow_mode" value="borrow">
                            <input type="hidden" name="identity_number" id="borrowIdentityNumber" value="{{ old('identity_number') }}">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                                    <label class="form-label text-secondary mb-0">Identitas Peminjam (Face Recognition)</label>
                                    <span id="borrowFaceStatusBadge" class="badge rounded-pill text-bg-secondary">Menunggu scan</span>
                                </div>
                                <div class="borrow-face-camera-shell border rounded-3 overflow-hidden" style="{{ $faceCameraShellStyle }}">
                                    <video id="borrowFaceVideo" class="w-100" autoplay playsinline muted></video>
                                </div>
                                <canvas id="borrowFaceCanvas" class="d-none"></canvas>
                                <div class="small text-muted mt-2">Kamera aktif otomatis. Pastikan hanya satu wajah terlihat jelas di frame.</div>
                                <div id="borrowFaceResult" class="alert alert-secondary py-2 mt-2 mb-2">
                                    Wajah belum dikenali.
                                </div>
                                <div id="borrowRecognizedUser" class="small text-dark fw-semibold">-</div>
                                <button type="button" id="borrowFaceRescanBtn" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="fa-solid fa-rotate me-1"></i>Scan Ulang Wajah
                                </button>
                            </div>
                            <div>
                                <label class="form-label text-secondary">Barcode Barang</label>
                                <input
                                    type="text"
                                    name="asset_code"
                                    class="form-control form-control-lg"
                                    placeholder="Scan barcode..."
                                    value="{{ old('asset_code') }}"
                                    required
                                >
                            </div>
                            <button type="submit" id="borrowSubmitButton" class="btn btn-primary btn-lg fw-semibold w-100" disabled>
                                <i class="fa-solid fa-circle-check me-2"></i>Konfirmasi Peminjaman
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.loans.return') }}" class="vstack gap-3 d-none" data-mode-form="return">
                            @csrf
                            <input type="hidden" name="flow_mode" value="return">
                            <input type="hidden" name="condition" value="good">
                            <input type="hidden" name="notes" value="">
                            <input type="hidden" name="identity_number" id="returnIdentityNumber" value="{{ old('identity_number') }}">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2 gap-2 flex-wrap">
                                    <label class="form-label text-secondary mb-0">Identitas Pengembali (Face Recognition)</label>
                                    <span id="returnFaceStatusBadge" class="badge rounded-pill text-bg-secondary">Menunggu scan</span>
                                </div>
                                <div class="return-face-camera-shell border rounded-3 overflow-hidden" style="{{ $faceCameraShellStyle }}">
                                    <video id="returnFaceVideo" class="w-100" autoplay playsinline muted></video>
                                </div>
                                <canvas id="returnFaceCanvas" class="d-none"></canvas>
                                <div class="small text-muted mt-2">Kamera aktif otomatis. Pastikan hanya satu wajah terlihat jelas di frame.</div>
                                <div id="returnFaceResult" class="alert alert-secondary py-2 mt-2 mb-2">
                                    Wajah belum dikenali.
                                </div>
                                <div id="returnRecognizedUser" class="small text-dark fw-semibold">-</div>
                                <button type="button" id="returnFaceRescanBtn" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="fa-solid fa-rotate me-1"></i>Scan Ulang Wajah
                                </button>
                            </div>
                            <div>
                                <label class="form-label text-secondary">Barcode Barang</label>
                                <input
                                    type="text"
                                    name="asset_code"
                                    class="form-control form-control-lg"
                                    placeholder="Scan barcode..."
                                    value="{{ old('asset_code') }}"
                                    required
                                >
                            </div>
                            <button type="submit" id="returnSubmitButton" class="btn btn-success btn-lg fw-semibold w-100" disabled>
                                <i class="fa-solid fa-circle-check me-2"></i>Konfirmasi Pengembalian
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100 public-card">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div class="fw-bold fs-4 text-dark" id="publicStockCardTitle">
                            <i class="fa-solid fa-circle-check text-success me-2"></i>Stok Barang Tersedia
                        </div>
                        <span id="publicStockCardBadge" class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">{{ $availableAssets->count() }} Items</span>
                    </div>

                    <div class="table-responsive public-stock-wrapper" data-mode-table="borrow">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary text-uppercase small">No</th>
                                    <th class="text-secondary text-uppercase small">Barang</th>
                                    <th class="text-secondary text-uppercase small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableAssets as $asset)
                                    @php
                                        $category = strtolower((string) $asset->category);
                                        $icon = 'fa-box-archive';

                                        if (str_contains($category, 'projector')) {
                                            $icon = 'fa-video';
                                        } elseif (str_contains($category, 'mouse')) {
                                            $icon = 'fa-computer-mouse';
                                        } elseif (str_contains($category, 'laptop') || str_contains($category, 'notebook')) {
                                            $icon = 'fa-laptop';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-secondary">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="asset-icon"><i class="fa-solid {{ $icon }}"></i></span>
                                                <div>
                                                    <div class="fw-semibold">{{ $asset->brand }}</div>
                                                    <div class="small text-secondary">{{ $asset->model }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">Ready</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Tidak ada stok tersedia saat ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive public-stock-wrapper d-none" data-mode-table="return">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary text-uppercase small">No</th>
                                    <th class="text-secondary text-uppercase small">Peminjam</th>
                                    <th class="text-secondary text-uppercase small">Barang Dipinjam</th>
                                    <th class="text-secondary text-uppercase small">Tanggal Pinjam</th>
                                    <th class="text-secondary text-uppercase small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeLoans as $loan)
                                    @php
                                        $loanStatus = strtolower((string) $loan->status);
                                        $loanStatusLabel = $loanStatus === 'overdue' ? 'Terlambat' : 'Dipinjam';
                                        $loanStatusClass = $loanStatus === 'overdue'
                                            ? 'bg-danger-subtle text-danger-emphasis'
                                            : 'bg-warning-subtle text-warning-emphasis';
                                    @endphp
                                    <tr>
                                        <td class="text-secondary">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $loan->user?->name ?? '-' }}</div>
                                            <div class="small text-secondary">
                                                {{ $loan->user?->identity_number ?? '-' }}
                                                @if(($loan->user?->kelas ?? '-') !== '-')
                                                    · {{ $loan->user?->kelas }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $loan->asset?->brand ?? '-' }}</div>
                                            <div class="small text-secondary">{{ $loan->asset?->model ?? '-' }}</div>
                                        </td>
                                        <td>
                                            @if($loan->loan_date)
                                                <div>{{ $loan->loan_date->format('d/m/Y') }}</div>
                                                <div class="small text-secondary">{{ $loan->loan_date->format('H:i') }} WIB</div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $loanStatusClass }} px-3 py-2">{{ $loanStatusLabel }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Tidak ada data barang dipinjam untuk mode public.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="howToUseModal" tabindex="-1" aria-labelledby="howToUseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title" id="howToUseModalLabel">Cara Pakai Aplikasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ol class="mb-0 ps-3">
                            <li>Pilih mode Peminjaman atau Pengembalian.</li>
                            <li>Pada mode peminjaman dan pengembalian, pastikan wajah terdeteksi hingga user dikenali otomatis.</li>
                            <li>Scan barcode/serial barang. yang ada di bagian belakang laptop</li>
                            <li>Klik tombol konfirmasi untuk menyimpan transaksi.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="publicRegisterModal" tabindex="-1" aria-labelledby="publicRegisterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="publicRegisterModalLabel">Register Siswa Baru</h5>
                            <div class="small text-muted">Lengkapi data siswa, lalu lanjut ke proses face recognition sebelum disimpan.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('dashboard.public.register') }}">
                        @csrf
                        <input type="hidden" name="public_register_step" id="publicRegisterStepInput" value="details">
                        <input type="hidden" name="public_register_image_base64" id="publicRegisterImageBase64Input" value="{{ old('public_register_image_base64') }}">
                        <input type="hidden" name="public_register_face_descriptor" id="publicRegisterFaceDescriptorInput" value="{{ old('public_register_face_descriptor') }}">
                        <div class="modal-body">
                            <div class="public-register-step-panel" data-public-register-step="details">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                                    <div>
                                        <div class="small text-uppercase text-muted fw-semibold">Tahap 1</div>
                                        <div class="h5 fw-bold mb-0">Data Siswa</div>
                                    </div>
                                    <span class="badge text-bg-primary rounded-pill px-3 py-2">1. Kelas, nama, NISN, dan HP</span>
                                </div>

                                <div class="alert alert-info border small mb-3">
                                    Pilih kelas dulu, lalu nama murid akan menyesuaikan data yang tersedia pada kelas tersebut.
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="publicRegisterKelas" class="form-label">1. Dropdown Kelas <span class="text-danger">*</span></label>
                                        <select
                                            id="publicRegisterKelas"
                                            name="public_register_kelas"
                                            class="form-select @error('public_register_kelas') is-invalid @enderror"
                                            required
                                        >
                                            <option value="">Pilih kelas</option>
                                            @foreach($classOptions as $classOption)
                                                <option value="{{ $classOption }}" @selected(old('public_register_kelas') === $classOption)>{{ $classOption }}</option>
                                            @endforeach
                                        </select>
                                        @error('public_register_kelas')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="publicRegisterUserId" class="form-label">3. Select Nama Lengkap <span class="text-danger">*</span></label>
                                        <select
                                            id="publicRegisterUserId"
                                            name="public_register_user_id"
                                            class="form-select @error('public_register_user_id') is-invalid @enderror"
                                            required
                                            disabled
                                        >
                                            <option value="">Pilih nama murid</option>
                                        </select>
                                        <div class="form-text">Nama murid otomatis disaring berdasarkan kelas yang dipilih.</div>
                                        @error('public_register_user_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="publicRegisterIdentityNumber" class="form-label">4. Input NISN <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="publicRegisterIdentityNumber"
                                            name="public_register_identity_number"
                                            class="form-control @error('public_register_identity_number') is-invalid @enderror"
                                            value="{{ old('public_register_identity_number') }}"
                                            placeholder="Masukkan NISN"
                                            required
                                        >
                                        <div class="form-text">NISN wajib unik karena sudah dipakai sebagai identitas pengguna.</div>
                                        @error('public_register_identity_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="publicRegisterPhone" class="form-label">5. Input Nomor HP <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            id="publicRegisterPhone"
                                            name="public_register_phone"
                                            class="form-control @error('public_register_phone') is-invalid @enderror"
                                            value="{{ old('public_register_phone') }}"
                                            placeholder="Masukkan nomor HP"
                                            required
                                        >
                                        @error('public_register_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="public-register-step-panel d-none" data-public-register-step="capture">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                                    <div>
                                        <div class="small text-uppercase text-muted fw-semibold">Tahap 2</div>
                                        <div class="h5 fw-bold mb-0">Proses Face Recognition</div>
                                    </div>
                                    <span class="badge text-bg-success rounded-pill px-3 py-2">6. Lanjut ke kamera, capture, lalu simpan</span>
                                </div>

                                <div class="alert alert-warning border small mb-3">
                                    Klik Capture Wajah setelah kamera aktif. Jika wajah sudah sesuai, lanjutkan dengan tombol Simpan.
                                </div>

                                <div class="public-register-face-shell mb-3" id="publicRegisterFaceShell" style="{{ $faceCameraShellStyle }}">
                                    <video id="publicRegisterFaceVideo" class="public-register-face-media" autoplay playsinline muted></video>
                                    <img id="publicRegisterFaceFallback" class="public-register-face-media public-register-face-fallback" src="{{ $registerFacePreviewSampleImage }}" alt="Preview face recognition">
                                    <div id="publicRegisterFaceStatusBadge" class="camera-preview-status badge text-bg-secondary">Siap</div>
                                </div>

                                <canvas id="publicRegisterFaceCanvas" class="d-none"></canvas>

                                <div class="row g-3 align-items-start">
                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 bg-light h-100">
                                            <div class="small text-muted mb-1">Ringkasan Siswa</div>
                                            <div class="fw-semibold" id="publicRegisterSummaryName">-</div>
                                            <div class="small text-secondary" id="publicRegisterSummaryKelas">-</div>
                                            <div class="small text-secondary" id="publicRegisterSummaryIdentityNumber">NISN: -</div>
                                            <div class="small text-secondary" id="publicRegisterSummaryPhone">HP: -</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="border rounded-3 p-3 bg-light h-100">
                                            <div class="small text-muted mb-2">Preview Capture</div>
                                            <img id="publicRegisterFacePreview" class="img-fluid rounded d-none public-register-face-preview" alt="Preview capture wajah">
                                            <div id="publicRegisterFacePreviewPlaceholder" class="small text-secondary">Belum ada hasil capture.</div>
                                            @error('public_register_image_base64')
                                                <div class="text-danger small mt-2">{{ $message }}</div>
                                            @enderror
                                            @error('public_register_face_descriptor')
                                                <div class="text-danger small mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div id="publicRegisterFaceAlert" class="alert d-none mt-3 mb-0"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                            <button type="button" id="publicRegisterBackBtn" class="btn btn-outline-secondary d-none">Kembali</button>
                            <button type="button" id="publicRegisterNextBtn" class="btn btn-primary">Lanjut</button>
                            <button type="button" id="publicRegisterCaptureBtn" class="btn btn-outline-primary d-none">Capture Wajah</button>
                            <button type="submit" id="publicRegisterSaveBtn" class="btn btn-success d-none">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($publicReminderEnabled)
            <div
                class="public-reminder-banner"
                aria-label="Pengumuman waktu pengembalian barang"
                style="background: {{ $publicReminderBackground }}; color: {{ $publicReminderTextColor }}; font-size: {{ $publicReminderFontSize }}px; font-family: {{ $publicReminderFontFamily }};"
            >
                <div class="public-reminder-marquee" style="--public-reminder-speed: {{ $publicReminderSpeed }}s;">
                    {{ $runningText }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .public-dashboard {
            padding-bottom: 64px;
        }

        .public-header {
            border-color: #d7dee9 !important;
        }

        .mode-toggle-btn {
            border-radius: 0.8rem;
            border: 2px solid #111111;
            min-height: 84px;
            font-size: 1.2rem;
            line-height: 1.25;
            font-weight: 700;
            padding: 1rem 1.2rem;
            transition: all 0.2s ease;
        }

        .mode-toggle-btn i {
            font-size: 1.05em;
        }

        .mode-toggle-btn:hover,
        .mode-toggle-btn:focus-visible,
        .mode-toggle-btn:active {
            border-color: #111111;
            outline: 0;
            box-shadow: 0 0 0 0.18rem rgba(17, 17, 17, 0.14);
        }

        .mode-borrow {
            color: #0d6efd;
            background: #ffffff;
        }

        .mode-return {
            color: #198754;
            background: #ffffff;
        }

        .mode-borrow.is-active {
            color: #ffffff;
            background: #2f66e0;
            border-color: #111111;
            box-shadow: 0 10px 24px rgba(47, 102, 224, 0.25);
        }

        .mode-return.is-active {
            color: #ffffff;
            background: #1f9462;
            border-color: #111111;
            box-shadow: 0 10px 24px rgba(31, 148, 98, 0.25);
        }

        .mode-borrow.is-active:hover,
        .mode-borrow.is-active:focus-visible,
        .mode-return.is-active:hover,
        .mode-return.is-active:focus-visible {
            border-color: #111111;
        }

        .public-card {
            border-radius: 0.75rem;
            border: 1px solid #dce4ef;
            box-shadow: 0 8px 18px rgba(40, 58, 90, 0.08);
            overflow: hidden;
        }

        .public-scan-header {
            font-weight: 700;
            font-size: 1.05rem;
        }

        .public-stock-wrapper {
            max-height: 410px;
        }

        .public-stock-wrapper thead th {
            border-top: 0;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .public-stock-wrapper tbody tr {
            border-color: #e6ebf3;
        }

        .asset-icon {
            width: 32px;
            height: 32px;
            border-radius: 0.5rem;
            background: #edf1f7;
            color: #7a8798;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .public-register-step-panel {
            border: 1px solid #d8e1ef;
            border-radius: 1rem;
            background: #ffffff;
            padding: 1rem;
        }

        .public-register-face-shell {
            width: min(100%, var(--face-camera-preview-size, 420px));
            aspect-ratio: var(--face-camera-frame-ratio, 1 / 1);
            margin-inline: auto;
            border-radius: var(--face-camera-border-radius, 16px);
            background: var(--face-camera-background, #111111);
            overflow: hidden;
            position: relative;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.18);
        }

        .public-register-face-media {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: var(--face-camera-object-fit, cover);
            object-position: calc(50% + var(--face-camera-horizontal-shift, 0%)) calc(50% + var(--face-camera-vertical-shift, 0%));
            display: block;
            transition: opacity 0.2s ease;
        }

        .public-register-face-fallback {
            z-index: 0;
            opacity: 1;
        }

        #publicRegisterFaceVideo {
            z-index: 1;
            opacity: 0;
            background: var(--face-camera-background, #111111);
        }

        .public-register-face-shell.is-active #publicRegisterFaceVideo {
            opacity: 1;
        }

        .public-register-face-shell.is-active .public-register-face-fallback {
            opacity: 0;
        }

        .public-register-face-preview {
            aspect-ratio: var(--face-camera-frame-ratio, 1 / 1);
            object-fit: cover;
            object-position: calc(50% + var(--face-camera-horizontal-shift, 0%)) calc(50% + var(--face-camera-vertical-shift, 0%));
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

        .borrow-face-camera-shell,
        .return-face-camera-shell {
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

        #borrowFaceVideo,
        #returnFaceVideo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: var(--face-camera-object-fit, cover);
            object-position: calc(50% + var(--face-camera-horizontal-shift, 0%)) calc(50% + var(--face-camera-vertical-shift, 0%));
            display: block;
            background: var(--face-camera-background, #111111);
        }

        .public-reminder-banner {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1050;
            background: #0a0a0a;
            color: #ffffff;
            overflow: hidden;
            padding: 0.68rem 1rem;
        }

        .public-reminder-marquee {
            display: inline-block;
            white-space: nowrap;
            font-weight: 700;
            letter-spacing: 0.02em;
            padding-left: 100%;
            animation: publicMarquee var(--public-reminder-speed, 15s) linear infinite;
            will-change: transform;
        }

        @keyframes publicMarquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-100%);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .public-reminder-marquee {
                animation: none;
                padding-left: 0;
                width: 100%;
                text-align: center;
            }
        }

        @media (min-width: 1600px) and (max-width: 2560px) and (min-height: 850px) {
            .public-dashboard {
                max-width: 1840px;
                margin-inline: auto;
                padding-bottom: 72px;
            }

            .public-header .h2 {
                font-size: clamp(2rem, 1.05vw + 1.05rem, 2.45rem);
            }

            .public-header p {
                font-size: 1.08rem;
            }

            .mode-toggle-btn {
                min-height: 92px;
                font-size: 1.26rem;
                padding: 1.05rem 1.3rem;
            }

            .public-scan-header {
                font-size: 1.12rem;
            }

            .public-card .card-body,
            .public-card .card-header {
                padding-left: 1.2rem;
                padding-right: 1.2rem;
            }

            .public-stock-wrapper {
                max-height: min(60vh, 560px);
            }

            .public-stock-wrapper .table {
                font-size: 1.02rem;
            }

            .public-stock-wrapper tbody td {
                padding-top: 0.88rem;
                padding-bottom: 0.88rem;
            }

            .asset-icon {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }

            .public-reminder-banner {
                padding-top: 0.76rem;
                padding-bottom: 0.76rem;
            }
        }

        @media (min-width: 2200px) and (max-width: 2560px) {
            .public-dashboard {
                max-width: 2060px;
            }

            .mode-toggle-btn {
                min-height: 98px;
                font-size: 1.34rem;
            }

            .public-stock-wrapper {
                max-height: min(62vh, 640px);
            }
        }

        @media (max-width: 991.98px) {
            .mode-toggle-btn {
                min-height: 70px;
                font-size: 1.05rem;
                padding: 0.85rem 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .mode-toggle-btn {
                min-height: 62px;
                font-size: 0.98rem;
            }
        }
    </style>
@endpush

@push('scripts')
    @include('partials.face-recognition-assets')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modeButtons = document.querySelectorAll('[data-target-mode]');
            var forms = document.querySelectorAll('[data-mode-form]');
            var tables = document.querySelectorAll('[data-mode-table]');
            var title = document.getElementById('scanCardTitle');
            var scanHeader = document.getElementById('scanCardHeader');
            var stockCardTitle = document.getElementById('publicStockCardTitle');
            var stockCardBadge = document.getElementById('publicStockCardBadge');
            var availableCount = @json($availableAssets->count());
            var activeLoanCount = @json($activeLoans->count());
            var initialMode = @json($initialMode);
            var activeMode = initialMode === 'return' ? 'return' : 'borrow';

            var faceModes = {
                borrow: {
                    identityInput: document.getElementById('borrowIdentityNumber'),
                    submitButton: document.getElementById('borrowSubmitButton'),
                    video: document.getElementById('borrowFaceVideo'),
                    canvas: document.getElementById('borrowFaceCanvas'),
                    result: document.getElementById('borrowFaceResult'),
                    statusBadge: document.getElementById('borrowFaceStatusBadge'),
                    recognizedUser: document.getElementById('borrowRecognizedUser'),
                    rescanButton: document.getElementById('borrowFaceRescanBtn'),
                },
                return: {
                    identityInput: document.getElementById('returnIdentityNumber'),
                    submitButton: document.getElementById('returnSubmitButton'),
                    video: document.getElementById('returnFaceVideo'),
                    canvas: document.getElementById('returnFaceCanvas'),
                    result: document.getElementById('returnFaceResult'),
                    statusBadge: document.getElementById('returnFaceStatusBadge'),
                    recognizedUser: document.getElementById('returnRecognizedUser'),
                    rescanButton: document.getElementById('returnFaceRescanBtn'),
                }
            };

            var faceRecognitionState = {
                stream: null,
                intervalId: null,
                mode: null,
                isRecognitionRequestRunning: false,
                locked: {
                    borrow: false,
                    return: false,
                }
            };

            var FACE_CAPTURE_SIZE = @json((int) ($faceCameraSettings['face_camera_capture_size'] ?? 512));
            var FACE_CAMERA_FRAME_MODE = @json((string) ($faceCameraSettings['face_camera_frame_mode'] ?? 'square'));
            var FACE_CAMERA_HORIZONTAL_SHIFT = @json((int) ($faceCameraSettings['face_camera_horizontal_shift'] ?? 0));
            var FACE_CAMERA_VERTICAL_SHIFT = @json((int) ($faceCameraSettings['face_camera_vertical_shift'] ?? 0));

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

            function getFaceElements(mode) {
                return faceModes[mode] || faceModes.borrow;
            }

            function stopFaceCamera() {
                if (faceRecognitionState.intervalId) {
                    window.clearInterval(faceRecognitionState.intervalId);
                    faceRecognitionState.intervalId = null;
                }

                if (faceRecognitionState.stream) {
                    faceRecognitionState.stream.getTracks().forEach(function (track) {
                        track.stop();
                    });

                    faceRecognitionState.stream = null;
                }

                if (faceRecognitionState.mode) {
                    var currentElements = getFaceElements(faceRecognitionState.mode);

                    if (currentElements.video) {
                        currentElements.video.srcObject = null;
                    }
                }

                faceRecognitionState.mode = null;
                faceRecognitionState.isRecognitionRequestRunning = false;
            }

            function setMode(mode) {
                activeMode = mode;

                modeButtons.forEach(function (button) {
                    button.classList.toggle('is-active', button.dataset.targetMode === mode);
                });

                forms.forEach(function (form) {
                    form.classList.toggle('d-none', form.dataset.modeForm !== mode);
                });

                tables.forEach(function (table) {
                    table.classList.toggle('d-none', table.dataset.modeTable !== mode);
                });

                if (title && scanHeader) {
                    title.textContent = mode === 'return' ? 'Scan Station Pengembalian' : 'Scan Station Peminjaman';
                    scanHeader.classList.toggle('bg-primary', mode !== 'return');
                    scanHeader.classList.toggle('bg-success', mode === 'return');
                }

                if (stockCardTitle) {
                    stockCardTitle.innerHTML = mode === 'return'
                        ? '<i class="fa-solid fa-right-left text-warning me-2"></i>Daftar Barang Dipinjam'
                        : '<i class="fa-solid fa-circle-check text-success me-2"></i>Stok Barang Tersedia';
                }

                if (stockCardBadge) {
                    stockCardBadge.textContent = (mode === 'return' ? activeLoanCount : availableCount) + ' Items';
                    stockCardBadge.classList.toggle('bg-success-subtle', mode !== 'return');
                    stockCardBadge.classList.toggle('text-success-emphasis', mode !== 'return');
                    stockCardBadge.classList.toggle('bg-warning-subtle', mode === 'return');
                    stockCardBadge.classList.toggle('text-warning-emphasis', mode === 'return');
                }

                startFaceCamera(mode);
            }

            function updateFaceStatus(mode, message, badgeClass, alertClass, userLabel, isRecognized) {
                var elements = getFaceElements(mode);

                if (elements.result) {
                    elements.result.className = 'alert py-2 mt-2 mb-2 ' + alertClass;
                    elements.result.textContent = message;
                }

                if (elements.statusBadge) {
                    elements.statusBadge.className = 'badge rounded-pill ' + badgeClass;
                    elements.statusBadge.textContent = isRecognized ? 'Dikenali' : 'Menunggu scan';
                }

                if (elements.recognizedUser) {
                    elements.recognizedUser.textContent = userLabel || '-';
                }

                if (elements.submitButton) {
                    elements.submitButton.disabled = !isRecognized;
                }
            }

            function lockRecognizedUser(mode, user, confidenceScore) {
                var elements = getFaceElements(mode);

                if (!user || !elements.identityInput) {
                    return;
                }

                faceRecognitionState.locked[mode] = true;
                elements.identityInput.value = user.identity_number || '';

                var userLabel = (user.name || 'Tanpa Nama')
                    + ' | ' + (user.kelas || '-')
                    + ' | ID: ' + (user.identity_number || '-');

                var confidenceText = typeof confidenceScore === 'number'
                    ? ' (score: ' + confidenceScore.toFixed(4) + ')'
                    : '';

                var recognizedMessage = mode === 'return'
                    ? 'Wajah dikenali. Lanjutkan scan barcode barang untuk pengembalian.'
                    : 'Wajah dikenali. Lanjutkan scan barcode barang.';

                updateFaceStatus(
                    mode,
                    recognizedMessage + confidenceText,
                    'text-bg-success',
                    'alert-success',
                    userLabel,
                    true
                );
            }

            function clearRecognizedUser(mode, message, alertClass) {
                var elements = getFaceElements(mode);

                faceRecognitionState.locked[mode] = false;

                if (elements.identityInput) {
                    elements.identityInput.value = '';
                }

                updateFaceStatus(
                    mode,
                    message || 'Wajah tidak dikenali.',
                    'text-bg-secondary',
                    alertClass || 'alert-secondary',
                    '-',
                    false
                );
            }

            async function recognizeFace() {
                var mode = faceRecognitionState.mode || activeMode;

                if (mode !== 'borrow' && mode !== 'return') {
                    return;
                }

                if (faceRecognitionState.isRecognitionRequestRunning || faceRecognitionState.locked[mode]) {
                    return;
                }

                var elements = getFaceElements(mode);

                if (!window.InventoryFaceRecognition || !elements.video || !elements.canvas) {
                    clearRecognizedUser(mode, 'Library face recognition belum siap.', 'alert-danger');

                    return;
                }

                var captureResult;

                faceRecognitionState.isRecognitionRequestRunning = true;
                var requestMode = mode;

                try {
                    captureResult = await window.InventoryFaceRecognition.captureFaceData(elements.video, elements.canvas, {
                        captureSize: FACE_CAPTURE_SIZE,
                        frameMode: FACE_CAMERA_FRAME_MODE,
                        horizontalShift: FACE_CAMERA_HORIZONTAL_SHIFT,
                        verticalShift: FACE_CAMERA_VERTICAL_SHIFT,
                        includeImage: false,
                        detectorInputSize: 416,
                    });

                    if (requestMode !== faceRecognitionState.mode) {
                        return;
                    }

                    if (captureResult.status === 'no_face') {
                        clearRecognizedUser(requestMode, 'Tidak ada wajah terdeteksi. Arahkan wajah ke kamera.', 'alert-secondary');

                        return;
                    }

                    if (captureResult.status === 'multiple_faces') {
                        clearRecognizedUser(requestMode, 'Terdeteksi lebih dari satu wajah. Mohon hanya satu orang di frame.', 'alert-warning');

                        return;
                    }

                    if (captureResult.status !== 'ok' || !Array.isArray(captureResult.descriptor)) {
                        clearRecognizedUser(requestMode, 'Descriptor wajah tidak valid. Coba arahkan wajah lagi.', 'alert-danger');

                        return;
                    }

                    var response = await fetch(@json(route('face-recognition.recognize')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify({
                            face_descriptor: captureResult.descriptor
                        })
                    });

                    var data = await response.json();

                    if (requestMode !== faceRecognitionState.mode) {
                        return;
                    }

                    if (response.ok && data.recognized === true && data.user) {
                        lockRecognizedUser(requestMode, data.user, data.confidence_score);

                        return;
                    }

                    if (data.status === 'no_face') {
                        clearRecognizedUser(requestMode, data.message || 'Wajah tidak terdeteksi.', 'alert-secondary');

                        return;
                    }

                    if (data.status === 'multiple_faces') {
                        clearRecognizedUser(requestMode, data.message || 'Terdeteksi lebih dari satu wajah.', 'alert-warning');

                        return;
                    }

                    if (data.status === 'empty_reference') {
                        clearRecognizedUser(requestMode, data.message || 'Belum ada data wajah terdaftar.', 'alert-warning');

                        return;
                    }

                    if (data.status === 'invalid_descriptor') {
                        clearRecognizedUser(requestMode, data.message || 'Descriptor wajah tidak valid.', 'alert-warning');

                        return;
                    }

                    clearRecognizedUser(requestMode, data.message || 'Wajah tidak dikenali.', 'alert-danger');
                } catch (error) {
                    if (requestMode === faceRecognitionState.mode) {
                        clearRecognizedUser(requestMode, 'Gagal memproses face recognition di browser atau server.', 'alert-danger');
                    }
                } finally {
                    faceRecognitionState.isRecognitionRequestRunning = false;
                }
            }

            async function startFaceCamera(mode) {
                var elements = getFaceElements(mode);

                if (!elements.video) {
                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    clearRecognizedUser(mode, 'Browser tidak mendukung akses kamera.', 'alert-danger');

                    return;
                }

                stopFaceCamera();

                try {
                    if (window.InventoryFaceRecognition) {
                        await window.InventoryFaceRecognition.loadFaceApiModels();
                    }

                    faceRecognitionState.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            aspectRatio: 1,
                            width: { ideal: 640 },
                            height: { ideal: 640 }
                        },
                        audio: false
                    });

                    faceRecognitionState.mode = mode;
                    faceRecognitionState.locked[mode] = false;
                    elements.video.srcObject = faceRecognitionState.stream;
                    clearRecognizedUser(mode, 'Kamera aktif. Arahkan wajah ke kamera untuk dikenali otomatis.', 'alert-info');

                    faceRecognitionState.intervalId = window.setInterval(recognizeFace, 1800);
                } catch (error) {
                    stopFaceCamera();
                    clearRecognizedUser(mode, error && error.message ? error.message : 'Izin kamera ditolak atau perangkat kamera tidak tersedia.', 'alert-danger');
                }
            }

            modeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setMode(button.dataset.targetMode);
                });
            });

            if (faceModes.borrow.rescanButton) {
                faceModes.borrow.rescanButton.addEventListener('click', function () {
                    clearRecognizedUser('borrow', 'Silakan arahkan wajah untuk scan ulang.', 'alert-info');
                });
            }

            if (faceModes.return.rescanButton) {
                faceModes.return.rescanButton.addEventListener('click', function () {
                    clearRecognizedUser('return', 'Silakan arahkan wajah untuk scan ulang.', 'alert-info');
                });
            }

            @php
                $publicRegisterErrorFields = [
                    'public_register_user_id',
                    'public_register_identity_number',
                    'public_register_kelas',
                    'public_register_phone',
                    'public_register_image_base64',
                ];
                $shouldOpenPublicRegisterModal = $errors->hasAny($publicRegisterErrorFields);
            @endphp

            var shouldOpenPublicRegisterModal = @json($shouldOpenPublicRegisterModal);

            if (shouldOpenPublicRegisterModal) {
                var publicRegisterModalElement = document.getElementById('publicRegisterModal');

                if (publicRegisterModalElement) {
                    var publicRegisterModal = new bootstrap.Modal(publicRegisterModalElement);
                    publicRegisterModal.show();
                }
            }

            var publicFlashNotifications = document.querySelectorAll('.js-public-flash-notification');

            if (publicFlashNotifications.length) {
                window.setTimeout(function () {
                    publicFlashNotifications.forEach(function (notification) {
                        if (!notification || !notification.classList.contains('show')) {
                            return;
                        }

                        var alertInstance = bootstrap.Alert.getOrCreateInstance(notification);
                        alertInstance.close();
                    });
                }, 4000);
            }

            window.addEventListener('beforeunload', stopFaceCamera);

            setMode(activeMode);
        });
    </script>

    @php
        $publicRegisterWizardDetailsErrorFields = [
            'public_register_user_id',
            'public_register_kelas',
            'public_register_identity_number',
            'public_register_phone',
        ];
        $publicRegisterWizardCaptureErrorFields = [
            'public_register_image_base64',
            'public_register_face_descriptor',
        ];
        $publicRegisterWizardInitialStep = $errors->hasAny($publicRegisterWizardDetailsErrorFields) ? 'details' : 'capture';
        if (!$errors->hasAny(array_merge($publicRegisterWizardDetailsErrorFields, $publicRegisterWizardCaptureErrorFields))) {
            $publicRegisterWizardInitialStep = 'details';
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var publicRegisterModalElement = document.getElementById('publicRegisterModal');
            var publicRegisterForm = publicRegisterModalElement ? publicRegisterModalElement.querySelector('form') : null;
            var publicRegisterStepInput = document.getElementById('publicRegisterStepInput');
            var publicRegisterKelasInput = document.getElementById('publicRegisterKelas');
            var publicRegisterUserIdInput = document.getElementById('publicRegisterUserId');
            var publicRegisterIdentityNumberInput = document.getElementById('publicRegisterIdentityNumber');
            var publicRegisterPhoneInput = document.getElementById('publicRegisterPhone');
            var publicRegisterImageInput = document.getElementById('publicRegisterImageBase64Input');
            var publicRegisterDetailsPanel = document.querySelector('[data-public-register-step="details"]');
            var publicRegisterCapturePanel = document.querySelector('[data-public-register-step="capture"]');
            var publicRegisterNextBtn = document.getElementById('publicRegisterNextBtn');
            var publicRegisterBackBtn = document.getElementById('publicRegisterBackBtn');
            var publicRegisterCaptureBtn = document.getElementById('publicRegisterCaptureBtn');
            var publicRegisterSaveBtn = document.getElementById('publicRegisterSaveBtn');
            var publicRegisterFaceShell = document.getElementById('publicRegisterFaceShell');
            var publicRegisterFaceVideo = document.getElementById('publicRegisterFaceVideo');
            var publicRegisterFaceFallback = document.getElementById('publicRegisterFaceFallback');
            var publicRegisterFaceCanvas = document.getElementById('publicRegisterFaceCanvas');
            var publicRegisterFacePreview = document.getElementById('publicRegisterFacePreview');
            var publicRegisterFacePreviewPlaceholder = document.getElementById('publicRegisterFacePreviewPlaceholder');
            var publicRegisterFaceStatusBadge = document.getElementById('publicRegisterFaceStatusBadge');
            var publicRegisterFaceAlert = document.getElementById('publicRegisterFaceAlert');
            var publicRegisterSummaryName = document.getElementById('publicRegisterSummaryName');
            var publicRegisterSummaryKelas = document.getElementById('publicRegisterSummaryKelas');
            var publicRegisterSummaryIdentityNumber = document.getElementById('publicRegisterSummaryIdentityNumber');
            var publicRegisterSummaryPhone = document.getElementById('publicRegisterSummaryPhone');
            var publicRegisterCapturedImage = publicRegisterImageInput ? publicRegisterImageInput.value || '' : '';
            var publicRegisterFaceDescriptorInput = document.getElementById('publicRegisterFaceDescriptorInput');
            var publicRegisterCapturedFaceDescriptor = publicRegisterFaceDescriptorInput ? publicRegisterFaceDescriptorInput.value || '' : '';
            var publicRegisterRosterByClass = @json($publicStudentRosterByClass);
            var publicRegisterFrameMode = @json((string) ($faceCameraSettings['face_camera_frame_mode'] ?? 'square'));
            var publicRegisterCaptureSize = @json((int) ($faceCameraSettings['face_camera_capture_size'] ?? 512));
            var publicRegisterHorizontalShift = @json((int) ($faceCameraSettings['face_camera_horizontal_shift'] ?? 0));
            var publicRegisterVerticalShift = @json((int) ($faceCameraSettings['face_camera_vertical_shift'] ?? 0));
            var publicRegisterInitialStep = @json($publicRegisterWizardInitialStep);
            var publicRegisterShouldOpenModal = @json($errors->hasAny(array_merge($publicRegisterWizardDetailsErrorFields, $publicRegisterWizardCaptureErrorFields)));
            var publicRegisterInitialClass = @json(old('public_register_kelas', ''));
            var publicRegisterInitialUserId = @json((int) old('public_register_user_id', 0));
            var publicRegisterCameraState = {
                stream: null,
                starting: false,
            };
            var publicRegisterStudentIndex = {};

            Object.keys(publicRegisterRosterByClass || {}).forEach(function (className) {
                var rosterItems = publicRegisterRosterByClass[className] || [];

                rosterItems.forEach(function (student) {
                    publicRegisterStudentIndex[String(student.id)] = student;
                });
            });

            function getPublicRegisterFrameRatio() {
                return publicRegisterFrameMode === 'wide' ? 4 / 3 : 1;
            }

            function getPublicRegisterHorizontalShift() {
                if (!Number.isFinite(publicRegisterHorizontalShift)) {
                    return 0;
                }

                return Math.max(-100, Math.min(100, publicRegisterHorizontalShift));
            }

            function getPublicRegisterVerticalShift() {
                if (!Number.isFinite(publicRegisterVerticalShift)) {
                    return 0;
                }

                return Math.max(-100, Math.min(100, publicRegisterVerticalShift));
            }

            function getPublicRegisterCaptureDimensions() {
                var targetRatio = getPublicRegisterFrameRatio();
                var outputWidth = Math.max(1, publicRegisterCaptureSize);

                return {
                    width: outputWidth,
                    height: Math.max(1, Math.round(outputWidth / targetRatio))
                };
            }

            function setPublicRegisterAlert(message, type) {
                if (!publicRegisterFaceAlert) {
                    return;
                }

                if (!message) {
                    publicRegisterFaceAlert.className = 'alert d-none mt-3 mb-0';
                    publicRegisterFaceAlert.textContent = '';
                    return;
                }

                publicRegisterFaceAlert.className = 'alert alert-' + type + ' mt-3 mb-0';
                publicRegisterFaceAlert.textContent = message;
            }

            function setPublicRegisterCameraStatus(message, badgeClass) {
                if (!publicRegisterFaceStatusBadge) {
                    return;
                }

                publicRegisterFaceStatusBadge.className = 'badge ' + badgeClass;
                publicRegisterFaceStatusBadge.textContent = message;
            }

            function stopPublicRegisterCamera() {
                if (publicRegisterCameraState.stream) {
                    publicRegisterCameraState.stream.getTracks().forEach(function (track) {
                        track.stop();
                    });
                }

                publicRegisterCameraState.stream = null;

                if (publicRegisterFaceVideo) {
                    publicRegisterFaceVideo.srcObject = null;
                }

                if (publicRegisterFaceShell) {
                    publicRegisterFaceShell.classList.remove('is-active');
                }

                setPublicRegisterCameraStatus('Siap', 'text-bg-secondary');
            }

            function syncPublicRegisterSummary() {
                var selectedStudent = publicRegisterStudentIndex[String(publicRegisterUserIdInput ? publicRegisterUserIdInput.value : '')] || null;

                if (publicRegisterSummaryName) {
                    publicRegisterSummaryName.textContent = selectedStudent ? selectedStudent.name : '-';
                }

                if (publicRegisterSummaryKelas) {
                    publicRegisterSummaryKelas.textContent = selectedStudent ? selectedStudent.kelas : '-';
                }

                if (publicRegisterSummaryIdentityNumber) {
                    publicRegisterSummaryIdentityNumber.textContent = 'NISN: ' + ((publicRegisterIdentityNumberInput && publicRegisterIdentityNumberInput.value) || '-');
                }

                if (publicRegisterSummaryPhone) {
                    publicRegisterSummaryPhone.textContent = 'HP: ' + ((publicRegisterPhoneInput && publicRegisterPhoneInput.value) || '-');
                }
            }

            function clearPublicRegisterCapture() {
                publicRegisterCapturedImage = '';
                publicRegisterCapturedFaceDescriptor = '';

                if (publicRegisterImageInput) {
                    publicRegisterImageInput.value = '';
                }

                if (publicRegisterFaceDescriptorInput) {
                    publicRegisterFaceDescriptorInput.value = '';
                }

                if (publicRegisterFacePreview) {
                    publicRegisterFacePreview.classList.add('d-none');
                }

                if (publicRegisterFacePreviewPlaceholder) {
                    publicRegisterFacePreviewPlaceholder.classList.remove('d-none');
                }

                if (publicRegisterSaveBtn) {
                    publicRegisterSaveBtn.disabled = true;
                }
            }

            function populatePublicRegisterStudents(className, selectedUserId, preserveCapture) {
                if (!publicRegisterUserIdInput) {
                    return;
                }

                var rosterItems = publicRegisterRosterByClass[className] || [];
                publicRegisterUserIdInput.innerHTML = '';

                var defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Pilih nama murid';
                publicRegisterUserIdInput.appendChild(defaultOption);

                rosterItems.forEach(function (student) {
                    var optionLabel = student.name;

                    if (student.identity_number) {
                        optionLabel += ' (' + student.identity_number + ')';
                    }

                    var option = document.createElement('option');
                    option.value = student.id;
                    option.setAttribute('data-identity-number', student.identity_number || '');
                    option.setAttribute('data-phone', student.phone || '');
                    option.textContent = optionLabel;
                    publicRegisterUserIdInput.appendChild(option);
                });
                publicRegisterUserIdInput.disabled = rosterItems.length === 0;

                var targetUserId = String(selectedUserId || '');
                var matchedStudent = publicRegisterStudentIndex[targetUserId] || null;

                if (!matchedStudent && rosterItems.length > 0) {
                    matchedStudent = rosterItems[0];
                    targetUserId = String(matchedStudent.id);
                }

                if (matchedStudent) {
                    publicRegisterUserIdInput.value = targetUserId;

                    if (publicRegisterIdentityNumberInput) {
                        publicRegisterIdentityNumberInput.value = matchedStudent.identity_number || '';
                    }

                    if (publicRegisterPhoneInput) {
                        publicRegisterPhoneInput.value = matchedStudent.phone || '';
                    }
                } else {
                    publicRegisterUserIdInput.value = '';

                    if (publicRegisterIdentityNumberInput && !preserveCapture) {
                        publicRegisterIdentityNumberInput.value = '';
                    }

                    if (publicRegisterPhoneInput && !preserveCapture) {
                        publicRegisterPhoneInput.value = '';
                    }
                }

                if (!preserveCapture) {
                    clearPublicRegisterCapture();
                }

                syncPublicRegisterSummary();
            }

            async function startPublicRegisterCamera() {
                if (!publicRegisterFaceVideo || publicRegisterCameraState.stream || publicRegisterCameraState.starting) {
                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setPublicRegisterCameraStatus('Tidak didukung', 'text-bg-danger');
                    setPublicRegisterAlert('Browser tidak mendukung akses kamera.', 'danger');

                    return;
                }

                publicRegisterCameraState.starting = true;
                setPublicRegisterCameraStatus('Meminta izin...', 'text-bg-warning text-dark');

                try {
                    var frameRatio = getPublicRegisterFrameRatio();
                    var cameraBaseResolution = Math.max(640, publicRegisterCaptureSize);

                    publicRegisterCameraState.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: cameraBaseResolution },
                            height: { ideal: Math.max(1, Math.round(cameraBaseResolution / frameRatio)) },
                            aspectRatio: frameRatio
                        },
                        audio: false
                    });

                    publicRegisterFaceVideo.srcObject = publicRegisterCameraState.stream;

                    if (publicRegisterFaceVideo.play) {
                        publicRegisterFaceVideo.play().catch(function () {
                            return;
                        });
                    }

                    if (publicRegisterFaceShell) {
                        publicRegisterFaceShell.classList.add('is-active');
                    }

                    if (window.InventoryFaceRecognition) {
                        setPublicRegisterCameraStatus('Memuat model...', 'text-bg-warning text-dark');
                        await window.InventoryFaceRecognition.loadFaceApiModels();
                    }

                    setPublicRegisterCameraStatus('Aktif', 'text-bg-success');
                } catch (error) {
                    stopPublicRegisterCamera();
                    setPublicRegisterCameraStatus('Gagal', 'text-bg-danger');
                    setPublicRegisterAlert(error && error.message ? error.message : 'Gagal mengakses kamera atau memuat model face recognition.', 'danger');
                } finally {
                    publicRegisterCameraState.starting = false;
                }
            }

            async function capturePublicRegisterFace() {
                if (!publicRegisterFaceVideo || !publicRegisterFaceCanvas || publicRegisterFaceVideo.videoWidth <= 0 || publicRegisterFaceVideo.videoHeight <= 0) {
                    setPublicRegisterAlert('Kamera belum siap. Tunggu beberapa detik lalu coba lagi.', 'warning');

                    return;
                }

                if (!window.InventoryFaceRecognition) {
                    setPublicRegisterAlert('Library face recognition belum siap.', 'danger');

                    return;
                }

                setPublicRegisterAlert('Memproses wajah...', 'info');

                try {
                    var captureResult = await window.InventoryFaceRecognition.captureFaceData(publicRegisterFaceVideo, publicRegisterFaceCanvas, {
                        captureSize: publicRegisterCaptureSize,
                        frameMode: publicRegisterFrameMode,
                        horizontalShift: publicRegisterHorizontalShift,
                        verticalShift: publicRegisterVerticalShift,
                        includeImage: true,
                        detectorInputSize: 416,
                        imageQuality: 0.85,
                    });

                    if (captureResult.status === 'no_face') {
                        setPublicRegisterAlert('Tidak ada wajah terdeteksi. Pastikan wajah berada di tengah kamera.', 'warning');

                        return;
                    }

                    if (captureResult.status === 'multiple_faces') {
                        setPublicRegisterAlert('Terdeteksi lebih dari satu wajah. Pastikan hanya satu orang di frame.', 'warning');

                        return;
                    }

                    if (captureResult.status !== 'ok' || !Array.isArray(captureResult.descriptor) || !captureResult.imageBase64) {
                        setPublicRegisterAlert('Descriptor wajah tidak valid. Ulangi capture dengan satu wajah yang jelas.', 'danger');

                        return;
                    }

                    publicRegisterCapturedImage = captureResult.imageBase64;
                    publicRegisterCapturedFaceDescriptor = JSON.stringify(captureResult.descriptor);

                    if (publicRegisterImageInput) {
                        publicRegisterImageInput.value = publicRegisterCapturedImage;
                    }

                    if (publicRegisterFaceDescriptorInput) {
                        publicRegisterFaceDescriptorInput.value = publicRegisterCapturedFaceDescriptor;
                    }

                    if (publicRegisterFacePreview) {
                        publicRegisterFacePreview.src = publicRegisterCapturedImage;
                        publicRegisterFacePreview.classList.remove('d-none');
                    }

                    if (publicRegisterFacePreviewPlaceholder) {
                        publicRegisterFacePreviewPlaceholder.classList.add('d-none');
                    }

                    if (publicRegisterSaveBtn) {
                        publicRegisterSaveBtn.disabled = false;
                    }

                    setPublicRegisterAlert('Capture wajah berhasil. Klik Simpan untuk melanjutkan.', 'success');
                } catch (error) {
                    setPublicRegisterAlert(error && error.message ? error.message : 'Gagal memproses face recognition di browser.', 'danger');
                }
            }

            function setPublicRegisterStep(step) {
                var isCaptureStep = step === 'capture';

                if (publicRegisterStepInput) {
                    publicRegisterStepInput.value = isCaptureStep ? 'capture' : 'details';
                }

                if (publicRegisterDetailsPanel) {
                    publicRegisterDetailsPanel.classList.toggle('d-none', isCaptureStep);
                }

                if (publicRegisterCapturePanel) {
                    publicRegisterCapturePanel.classList.toggle('d-none', !isCaptureStep);
                }

                if (publicRegisterNextBtn) {
                    publicRegisterNextBtn.classList.toggle('d-none', isCaptureStep);
                }

                if (publicRegisterBackBtn) {
                    publicRegisterBackBtn.classList.toggle('d-none', !isCaptureStep);
                }

                if (publicRegisterCaptureBtn) {
                    publicRegisterCaptureBtn.classList.toggle('d-none', !isCaptureStep);
                }

                if (publicRegisterSaveBtn) {
                    publicRegisterSaveBtn.classList.toggle('d-none', !isCaptureStep);
                    publicRegisterSaveBtn.disabled = !publicRegisterCapturedImage || !publicRegisterCapturedFaceDescriptor;
                }

                if (!isCaptureStep) {
                    stopPublicRegisterCamera();
                    setPublicRegisterAlert('', 'info');
                    return;
                }

                if (publicRegisterCapturedImage && publicRegisterCapturedFaceDescriptor) {
                    if (publicRegisterFacePreview) {
                        publicRegisterFacePreview.src = publicRegisterCapturedImage;
                        publicRegisterFacePreview.classList.remove('d-none');
                    }

                    if (publicRegisterFacePreviewPlaceholder) {
                        publicRegisterFacePreviewPlaceholder.classList.add('d-none');
                    }

                    if (publicRegisterSaveBtn) {
                        publicRegisterSaveBtn.disabled = false;
                    }
                }

                startPublicRegisterCamera();
            }

            function setInitialPublicRegisterStep() {
                var selectedClass = publicRegisterKelasInput ? publicRegisterKelasInput.value || publicRegisterInitialClass : publicRegisterInitialClass;
                populatePublicRegisterStudents(selectedClass, publicRegisterInitialUserId, true);

                if (!publicRegisterCapturedImage && publicRegisterImageInput && publicRegisterImageInput.value) {
                    publicRegisterCapturedImage = publicRegisterImageInput.value;
                }

                if (!publicRegisterCapturedFaceDescriptor && publicRegisterFaceDescriptorInput && publicRegisterFaceDescriptorInput.value) {
                    publicRegisterCapturedFaceDescriptor = publicRegisterFaceDescriptorInput.value;
                }

                if (publicRegisterCapturedImage && publicRegisterFacePreview) {
                    publicRegisterFacePreview.src = publicRegisterCapturedImage;
                }

                if (publicRegisterCapturedImage && publicRegisterCapturedFaceDescriptor && publicRegisterFacePreviewPlaceholder) {
                    publicRegisterFacePreviewPlaceholder.classList.add('d-none');
                    publicRegisterFacePreview.classList.remove('d-none');
                }

                setPublicRegisterStep(publicRegisterInitialStep);
            }

            if (publicRegisterKelasInput) {
                publicRegisterKelasInput.addEventListener('change', function () {
                    populatePublicRegisterStudents(publicRegisterKelasInput.value, 0, false);
                    setPublicRegisterStep('details');
                });
            }

            if (publicRegisterUserIdInput) {
                publicRegisterUserIdInput.addEventListener('change', function () {
                    var selectedStudent = publicRegisterStudentIndex[String(publicRegisterUserIdInput.value)] || null;

                    if (selectedStudent) {
                        if (publicRegisterIdentityNumberInput) {
                            publicRegisterIdentityNumberInput.value = selectedStudent.identity_number || '';
                        }

                        if (publicRegisterPhoneInput) {
                            publicRegisterPhoneInput.value = selectedStudent.phone || '';
                        }
                    }

                    syncPublicRegisterSummary();
                });
            }

            if (publicRegisterIdentityNumberInput) {
                publicRegisterIdentityNumberInput.addEventListener('input', syncPublicRegisterSummary);
            }

            if (publicRegisterPhoneInput) {
                publicRegisterPhoneInput.addEventListener('input', syncPublicRegisterSummary);
            }

            if (publicRegisterNextBtn) {
                publicRegisterNextBtn.addEventListener('click', function () {
                    if (publicRegisterForm && !publicRegisterForm.reportValidity()) {
                        return;
                    }

                    if (!publicRegisterUserIdInput || publicRegisterUserIdInput.disabled || !publicRegisterUserIdInput.value) {
                        setPublicRegisterAlert('Daftar nama murid belum tersedia untuk kelas ini. Pilih kelas lain terlebih dahulu.', 'warning');

                        return;
                    }

                    setPublicRegisterStep('capture');
                });
            }

            if (publicRegisterBackBtn) {
                publicRegisterBackBtn.addEventListener('click', function () {
                    setPublicRegisterStep('details');
                });
            }

            if (publicRegisterCaptureBtn) {
                publicRegisterCaptureBtn.addEventListener('click', function () {
                    capturePublicRegisterFace();
                });
            }

            if (publicRegisterSaveBtn) {
                publicRegisterSaveBtn.addEventListener('click', function (event) {
                    if (!publicRegisterCapturedImage) {
                        event.preventDefault();
                        setPublicRegisterAlert('Silakan capture wajah terlebih dahulu.', 'warning');
                        return;
                    }

                    if (!publicRegisterCapturedFaceDescriptor) {
                        event.preventDefault();
                        setPublicRegisterAlert('Descriptor wajah belum tersedia. Capture ulang wajah terlebih dahulu.', 'warning');
                        return;
                    }

                    if (publicRegisterStepInput) {
                        publicRegisterStepInput.value = 'capture';
                    }
                });
            }

            if (publicRegisterForm) {
                publicRegisterForm.addEventListener('submit', function (event) {
                    if (publicRegisterStepInput && publicRegisterStepInput.value !== 'capture') {
                        event.preventDefault();
                        return;
                    }

                    if (!publicRegisterCapturedImage || !publicRegisterCapturedFaceDescriptor) {
                        event.preventDefault();
                        setPublicRegisterAlert('Silakan capture wajah terlebih dahulu.', 'warning');
                    }
                });
            }

            if (publicRegisterModalElement) {
                publicRegisterModalElement.addEventListener('shown.bs.modal', function () {
                    setInitialPublicRegisterStep();
                });

                publicRegisterModalElement.addEventListener('hidden.bs.modal', function () {
                    stopPublicRegisterCamera();
                });
            }

            window.addEventListener('beforeunload', stopPublicRegisterCamera);
        });
    </script>
@endpush
