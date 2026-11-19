@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h4 class="mb-1">Data Pengguna</h4>
            <p class="text-muted mb-0">Pengelolaan admin, guru, dan siswa.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fa-solid fa-user-plus me-1"></i>Tambah Pengguna
            </button>
            <a href="{{ route('admin.users.export', ['template' => 1]) }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-file-circle-plus me-1"></i>Template Excel
            </a>
            <a href="{{ route('admin.users.export') }}" class="btn btn-outline-success">
                <i class="fa-solid fa-file-export me-1"></i>Export Excel
            </a>
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importUserExcelModal">
                <i class="fa-solid fa-file-import me-1"></i>Import Excel
            </button>
        </div>
    </div>

    @include('admin.users.partials.resume-card', ['summary' => $userSummary])

    @php
        $shouldOpenCreateUserModal = old('user_form') === 'store' && $errors->hasAny(['name', 'identity_number', 'role', 'kelas', 'phone']);
        $hasActiveUserFilters = filled($filters['search']) || filled($filters['role']) || filled($filters['kelas']);
    @endphp

    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="Cari ID/Nama/Kelas/HP...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="role">
                                <option value="">Semua Role</option>
                                @foreach($roleOptions as $role)
                                    <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="kelas">
                                <option value="">Semua Kelas</option>
                                @foreach($kelasOptions as $kelas)
                                    <option value="{{ $kelas }}" @selected($filters['kelas'] === $kelas)>{{ $kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <div class="d-grid gap-2 d-sm-flex">
                                <button class="btn btn-outline-secondary flex-fill" type="submit">Go</button>
                                <a
                                    href="{{ route('admin.users.index') }}"
                                    class="btn btn-outline-danger flex-fill {{ $hasActiveUserFilters ? '' : 'disabled' }}"
                                    @if(!$hasActiveUserFilters) aria-disabled="true" tabindex="-1" @endif
                                >
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive user-table-scroll">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="user-col-no">No</th>
                            <th>Identity</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>No. HP</th>
                            <th class="user-col-role">Role</th>
                            <th class="user-col-action">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="user-col-no">{{ $loop->iteration }}</td>
                                <td>{{ $user->identity_number }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->kelas }}</td>
                                <td>{{ $user->phone ?: '-' }}</td>
                                <td class="user-col-role">
                                    @php
                                        $roleBadgeClass = match ($user->role) {
                                            'admin' => 'text-bg-danger',
                                            'teacher' => 'text-bg-warning text-dark',
                                            'student' => 'text-bg-success',
                                            default => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $roleBadgeClass }}">{{ $user->role }}</span>
                                </td>
                                <td class="user-col-action">
                                    @php
                                        $faceEncodingValues = [];
                                        $faceThumbnailUrl = filled($user->face_thumbnail_path) ? asset('storage/' . ltrim($user->face_thumbnail_path, '/')) : null;

                                        if (!empty($user->face_encoding)) {
                                            $decodedFaceEncoding = json_decode((string) $user->face_encoding, true);

                                            if (is_array($decodedFaceEncoding)) {
                                                $faceEncodingValues = array_values($decodedFaceEncoding);
                                            }
                                        }

                                        $hasFaceData = filled($user->face_registered_at) && $faceEncodingValues !== [];
                                        $hasAnyFaceData = $hasFaceData || filled($user->face_thumbnail_path);
                                        $faceEncodingPreview = [];

                                        foreach (array_slice($faceEncodingValues, 0, 10) as $encodingValue) {
                                            $faceEncodingPreview[] = number_format((float) $encodingValue, 4, '.', '');
                                        }
                                    @endphp

                                    <div class="d-inline-flex gap-1 user-action-group">
                                        <button
                                            type="button"
                                            class="btn btn-sm {{ $hasAnyFaceData ? 'btn-info text-white' : 'btn-outline-info' }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#facePreviewModal-{{ $user->id }}"
                                        >
                                            Preview Wajah
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal-{{ $user->id }}"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </div>

                                    <div class="modal fade text-start" id="editUserModal-{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel-{{ $user->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editUserModalLabel-{{ $user->id }}">Edit Pengguna</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="user_form" value="update">
                                                    <div class="modal-body">
                                                        <div class="mb-2">
                                                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">NISN <span class="text-danger">*</span></label>
                                                            <input type="text" name="identity_number" class="form-control" value="{{ $user->identity_number }}" required>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                                                <select name="role" class="form-select" required>
                                                                    @foreach($roleOptions as $roleOption)
                                                                        <option value="{{ $roleOption }}" @selected($user->role === $roleOption)>{{ $roleOption }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                                                <select name="kelas" class="form-select" required>
                                                                    @foreach($kelasOptions as $kelasOption)
                                                                        <option value="{{ $kelasOption }}" @selected($user->kelas === $kelasOption)>{{ $kelasOption }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="mt-2">
                                                            <label class="form-label">No. HP <span class="text-danger">*</span></label>
                                                            <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" required>
                                                        </div>
                                                        <div class="mt-2">
                                                            <label class="form-label">Password Baru (opsional)</label>
                                                            <input type="password" name="password" class="form-control" minlength="8" placeholder="Kosongkan jika tidak diubah">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade text-start" id="facePreviewModal-{{ $user->id }}" tabindex="-1" aria-labelledby="facePreviewModalLabel-{{ $user->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title" id="facePreviewModalLabel-{{ $user->id }}">Preview Data Wajah</h5>
                                                        <div class="small text-muted">Data yang tersimpan adalah encoding wajah, bukan foto asli.</div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="border rounded-3 p-3 h-100">
                                                                <div class="fw-semibold mb-2">Preview Capture Wajah</div>

                                                                @if($faceThumbnailUrl)
                                                                    <img
                                                                        src="{{ $faceThumbnailUrl }}"
                                                                        alt="Thumbnail capture wajah {{ $user->name }}"
                                                                        class="img-fluid rounded border mb-3"
                                                                        style="max-height: 240px; width: 100%; object-fit: cover;"
                                                                    >
                                                                @else
                                                                    <div class="alert alert-secondary small mb-3">
                                                                        Belum ada thumbnail capture tersimpan untuk pengguna ini.
                                                                    </div>
                                                                @endif

                                                                <div class="fw-semibold mb-2">Ringkasan</div>
                                                                <div class="small text-muted mb-1">Nama</div>
                                                                <div class="fw-semibold mb-2">{{ $user->name }}</div>

                                                                <div class="small text-muted mb-1">NISN</div>
                                                                <div class="fw-semibold mb-2">{{ $user->identity_number }}</div>

                                                                <div class="small text-muted mb-1">Kelas</div>
                                                                <div class="fw-semibold mb-2">{{ $user->kelas }}</div>

                                                                <div class="small text-muted mb-1">Status</div>
                                                                <div class="mb-2">
                                                                    <span class="badge {{ $hasFaceData ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                                        {{ $hasFaceData ? 'Data wajah tersimpan' : 'Belum ada data wajah' }}
                                                                    </span>
                                                                </div>

                                                                <div class="small text-muted mb-1">Terakhir registrasi</div>
                                                                <div class="fw-semibold">{{ $user->face_registered_at ? $user->face_registered_at->format('d M Y H:i') : '-' }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="border rounded-3 p-3 h-100">
                                                                <div class="fw-semibold mb-2">Preview Encoding</div>
                                                                <div class="small text-muted mb-2">Jumlah nilai encoding: {{ count($faceEncodingValues) }}</div>

                                                                @if($faceEncodingPreview !== [])
                                                                    <div class="small text-muted mb-2">Cuplikan 10 nilai pertama</div>
                                                                    <div class="bg-light border rounded-3 p-2 small font-monospace text-break">
                                                                        {{ implode(', ', $faceEncodingPreview) }}
                                                                        @if(count($faceEncodingValues) > 10)
                                                                            ...
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <div class="alert alert-secondary mb-0">
                                                                        Belum ada encoding wajah tersimpan untuk pengguna ini.
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    @if($hasAnyFaceData)
                                                        <form
                                                            method="POST"
                                                            action="{{ route('admin.users.face-thumbnail.destroy', $user) }}"
                                                            onsubmit="return confirm('Hapus capture image dan data encoding wajah untuk pengguna ini?')"
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger">
                                                                <i class="fa-solid fa-trash-can me-2"></i>Hapus Data Wajah
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('admin.face-register.index', ['user_id' => $user->id]) }}" class="btn btn-primary">
                                                        <i class="fa-solid fa-pen-to-square me-2"></i>Buka Registrasi Wajah
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data pengguna.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header user-modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">
                        <i class="fa-solid fa-user-plus me-2"></i>Tambah Pengguna
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <input type="hidden" name="user_form" value="store">
                    <div class="modal-body">
                        <div class="user-modal-note mb-3">
                            <div class="fw-semibold mb-1">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i>Data wajib diisi:
                            </div>
                            <div class="small text-muted mb-0">Nama lengkap, NISN, role, kelas, dan nomor HP.</div>
                        </div>

                        @if($errors->hasAny(['name', 'identity_number', 'role', 'kelas', 'phone']))
                            <div class="alert alert-danger small mb-3">
                                Periksa kembali data yang diisi. Field yang bermasalah akan ditandai merah.
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Nama lengkap pengguna" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">NISN / Identity Number <span class="text-danger">*</span></label>
                                <input type="text" name="identity_number" class="form-control @error('identity_number') is-invalid @enderror" value="{{ old('identity_number') }}" placeholder="Nomor NISN atau identitas" required>
                                @error('identity_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    @foreach($roleOptions as $roleOption)
                                        <option value="{{ $roleOption }}" @selected(old('role', 'student') === $roleOption)>{{ $roleOption }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                                <select name="kelas" class="form-select @error('kelas') is-invalid @enderror" required>
                                    @foreach($kelasOptions as $kelasOption)
                                        <option value="{{ $kelasOption }}" @selected(old('kelas', '-') === $kelasOption)>{{ $kelasOption }}</option>
                                    @endforeach
                                </select>
                                @error('kelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">No. HP <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i>Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importUserExcelModal" tabindex="-1" aria-labelledby="importUserExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="importUserExcelModalLabel">Import Data Pengguna dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="importUserExcelFile" class="form-label">File Excel <span class="text-danger">*</span></label>
                            <input
                                type="file"
                                id="importUserExcelFile"
                                name="excel_file"
                                class="form-control @error('excel_file') is-invalid @enderror"
                                accept=".xlsx,.xls,.csv"
                                required
                            >
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format yang didukung: XLSX, XLS, atau CSV. Maksimal 10 MB.</div>
                        </div>

                        <div class="alert alert-light border mb-0">
                            <div class="fw-semibold mb-1">Header Excel harus sama dengan header tabel Data Pengguna:</div>
                            <div class="small font-monospace">No | Identity | Nama | Kelas | No. HP | Role</div>
                            <div class="small mt-2 mb-0">Formula impor: baris kosong atau baris yang tidak lengkap akan otomatis dilewati, sehingga proses import tetap lanjut ke baris berikutnya.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('admin.users.export', ['template' => 1]) }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-file-arrow-down me-2"></i>Download Template
                        </a>
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-file-import me-2"></i>Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var shouldOpenImportUserModal = @json($errors->has('excel_file'));

            if (shouldOpenImportUserModal) {
                var importUserExcelModalElement = document.getElementById('importUserExcelModal');

                if (importUserExcelModalElement) {
                    var importUserExcelModal = new bootstrap.Modal(importUserExcelModalElement);
                    importUserExcelModal.show();
                }
            }

            var shouldOpenCreateUserModal = @json($shouldOpenCreateUserModal);

            if (shouldOpenCreateUserModal) {
                var createUserModalElement = document.getElementById('createUserModal');

                if (createUserModalElement) {
                    var createUserModal = new bootstrap.Modal(createUserModalElement);
                    createUserModal.show();
                }
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .user-modal-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: #ffffff;
        }

        .user-modal-note {
            border-radius: 0.85rem;
            padding: 0.85rem 1rem;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(13, 202, 240, 0.06));
            border: 1px solid rgba(13, 110, 253, 0.12);
        }

        .user-summary-card {
            position: relative;
            border: 0;
            background: transparent;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        }

        .user-summary-card > .card-body {
            background: transparent;
            border: 0;
        }

        .user-summary-card::before,
        .user-summary-card::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
        }

        .user-summary-card::before {
            width: 240px;
            height: 240px;
            left: -90px;
            top: -120px;
            background: transparent;
        }

        .user-summary-card::after {
            width: 180px;
            height: 180px;
            right: -60px;
            bottom: -90px;
            background: transparent;
        }

        .user-summary-hero {
            position: relative;
            z-index: 1;
        }

        .user-summary-overview {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 55%, #0f172a 100%);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1.15rem;
            padding: 1.15rem 1.25rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .user-summary-overview-header h5,
        .user-summary-overview-header .user-summary-title {
            color: #ffffff !important;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
        }

        .user-summary-overview-header .user-summary-review {
            color: rgba(255, 255, 255, 0.88) !important;
            font-size: 0.86rem;
            line-height: 1.45;
        }

        .user-summary-overview .badge.user-summary-badge {
            background: rgba(255, 255, 255, 0.16) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            color: #ffffff !important;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .user-summary-pill {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.72rem;
            letter-spacing: 0.01em;
            padding: 0.2rem 0.55rem;
        }

        .user-summary-quick-strip {
            margin-top: 0.25rem;
            margin-bottom: 0.75rem;
        }

        .user-summary-progress {
            height: 0.55rem;
            background: rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            overflow: hidden;
        }

        .user-summary-progress .progress-bar {
            border-radius: 999px;
            background-color: #ffffff !important;
        }

        .user-summary-stats {
            position: relative;
            z-index: 1;
            margin-top: auto;
        }

        .user-summary-stats-row,
        .row.g-2\.5 {
            --bs-gutter-x: 0.65rem;
            --bs-gutter-y: 0.65rem;
        }

        .user-summary-stat {
            position: relative;
            overflow: hidden;
            border-radius: 0.85rem;
            padding: 0.75rem 0.85rem;
            background: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .user-summary-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.14);
        }

        .user-summary-stat-label {
            position: relative;
            z-index: 1;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #475569 !important;
        }

        .user-summary-stat-icon {
            position: relative;
            z-index: 1;
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 0.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            font-size: 0.82rem;
        }

        .user-summary-stat-primary .user-summary-stat-icon {
            background: rgba(13, 110, 253, 0.12);
            color: #0d6efd;
        }

        .user-summary-stat-info .user-summary-stat-icon {
            background: rgba(8, 145, 178, 0.12);
            color: #0891b2;
        }

        .user-summary-stat-success .user-summary-stat-icon {
            background: rgba(25, 135, 84, 0.12);
            color: #198754;
        }

        .user-summary-stat-body {
            min-height: 82px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0.65rem;
            padding: 0.6rem 0.8rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.15rem;
        }

        .user-summary-stat-value {
            position: relative;
            z-index: 1;
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a !important;
        }

        .user-summary-stat-meta {
            position: relative;
            z-index: 1;
            margin-top: 0.15rem;
            font-size: 0.72rem;
            line-height: 1.35;
            color: #64748b !important;
        }

        .user-summary-carousel {
            position: relative;
        }

        .user-summary-slide-card {
            min-height: 82px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0.65rem;
            padding: 0.55rem 1.65rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.15rem;
        }

        .user-summary-slide-title {
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #334155 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-summary-slide-value {
            font-size: 1.45rem;
            line-height: 1.1;
            font-weight: 800;
            color: #0f172a !important;
        }

        .user-summary-slide-meta {
            font-size: 0.71rem;
            line-height: 1.3;
            color: #64748b !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .user-summary-carousel-control {
            width: 1.4rem;
            opacity: 0.75;
        }

        .user-summary-carousel-control .carousel-control-prev-icon,
        .user-summary-carousel-control .carousel-control-next-icon {
            width: 0.8rem;
            height: 0.8rem;
            filter: brightness(0) saturate(100%) invert(24%) sepia(17%) saturate(810%) hue-rotate(175deg) brightness(97%) contrast(90%);
        }

        .user-summary-carousel-control:hover {
            opacity: 1;
        }

        .user-summary-ring-panel {
            flex: 0 0 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1.15rem 1.25rem;
            border-radius: 1.15rem;
            border: 1px solid rgba(56, 102, 228, 0.18);
            background-color: #FCA311;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        }

        .user-summary-ring {
            --user-summary-rate: 0;
            width: 145px;
            height: 145px;
            border-radius: 50%;
            position: relative;
            padding: 12px;
            background: conic-gradient(#0d6efd calc(var(--user-summary-rate) * 1%), rgba(255, 255, 255, 0.4) 0);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        }

        .user-summary-ring::before {
            content: '';
            position: absolute;
            inset: 12px;
            border-radius: 50%;
            background: #f8fafc;
        }

        .user-summary-ring-inner {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: #f8fafc;
        }

        .user-summary-ring-value {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }

        .user-summary-ring-label {
            margin-top: 0.25rem;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #334155;
        }

        .user-summary-ring-caption {
            max-width: 210px;
            color: #ffffff;
            font-size: 0.78rem;
            line-height: 1.45;
            text-align: center;
        }

        @media (max-width: 1199.98px) {
            .user-summary-ring-panel {
                flex: 1 1 100%;
                flex-direction: row;
                justify-content: center;
                gap: 1.5rem;
                padding: 1rem 1.25rem;
            }

            .user-summary-ring {
                width: 120px;
                height: 120px;
                padding: 10px;
            }

            .user-summary-ring::before {
                inset: 10px;
            }

            .user-summary-ring-value {
                font-size: 1.55rem;
            }

            .user-summary-ring-caption {
                max-width: 320px;
                text-align: start;
            }

            .user-summary-overview {
                flex: 1 1 100%;
            }

            .user-summary-stats {
                margin-top: 1.25rem;
            }

            .user-summary-stats-row,
            .row.g-2\.5 {
                --bs-gutter-x: 0.85rem;
                --bs-gutter-y: 0.85rem;
            }
        }

        @media (max-width: 767.98px) {
            .user-summary-ring-panel {
                flex-direction: column;
                text-align: center;
                gap: 0.65rem;
                padding: 1rem;
            }

            .user-summary-ring-caption {
                text-align: center;
                max-width: 240px;
            }

            .user-summary-stats {
                margin-top: 1.15rem;
            }

            .user-summary-stats-row,
            .row.g-2\.5 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.85rem;
            }

            .user-summary-stat-col:not(:last-child) {
                margin-bottom: 0.25rem;
            }
        }

        @media (max-width: 575.98px) {
            .user-summary-overview,
            .user-summary-ring-panel {
                padding: 0.9rem;
            }

            .user-summary-ring {
                width: 110px;
                height: 110px;
                padding: 9px;
            }

            .user-summary-ring::before {
                inset: 9px;
            }

            .user-summary-ring-value {
                font-size: 1.4rem;
            }

            .user-summary-stat-value {
                font-size: 1.35rem;
            }

            .user-summary-slide-card,
            .user-summary-stat-body {
                min-height: 74px;
                padding: 0.5rem 1.4rem;
            }

            .user-summary-slide-value {
                font-size: 1.35rem;
            }

            .user-summary-stats {
                margin-top: 1rem;
            }

            .user-summary-stats-row,
            .row.g-2\.5 {
                --bs-gutter-x: 0.5rem;
                --bs-gutter-y: 0.75rem;
            }
        }

        .user-col-no {
            text-align: center;
        }

        .user-table-scroll {
            max-height: min(56vh, 460px);
            overflow-y: auto;
            scrollbar-gutter: stable;
        }

        .user-table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--bs-table-bg, #f8f9fa);
            box-shadow: inset 0 -1px 0 rgba(15, 23, 42, 0.08);
        }

        .user-col-role {
            text-align: center;
        }

        .user-col-action {
            text-align: center;
        }

        .user-action-group {
            margin-right: 5px;
        }

        @media (max-width: 991.98px) {
            .user-table-scroll {
                max-height: min(52vh, 380px);
            }
        }
    </style>
@endpush
