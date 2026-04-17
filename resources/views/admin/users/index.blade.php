@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Data Pengguna</h4>
            <p class="text-muted mb-0">Pengelolaan admin, guru, dan siswa.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
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
        $shouldOpenCreateUserAccordion = old('user_form') === 'store';
    @endphp

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="accordion user-create-accordion" id="userCreateAccordion">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="userCreateAccordionHeading">
                        <button
                            class="accordion-button user-create-accordion-button {{ $shouldOpenCreateUserAccordion ? '' : 'collapsed' }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#userCreateAccordionCollapse"
                            aria-expanded="{{ $shouldOpenCreateUserAccordion ? 'true' : 'false' }}"
                            aria-controls="userCreateAccordionCollapse"
                        >
                            <div class="d-flex align-items-center gap-3 w-100 pe-2">
                                <span class="user-create-accordion-icon">
                                    <i class="fa-solid fa-user-plus"></i>
                                </span>
                                <div class="flex-grow-1 text-start">
                                    <div class="fw-bold">Tambah Pengguna</div>
                                    <div class="small user-create-accordion-subtitle">Isi data admin, guru, atau siswa baru secara cepat.</div>
                                </div>
                                <span class="badge rounded-pill text-bg-light border user-create-accordion-badge">Form</span>
                            </div>
                        </button>
                    </h2>
                    <div
                        id="userCreateAccordionCollapse"
                        class="accordion-collapse collapse {{ $shouldOpenCreateUserAccordion ? 'show' : '' }}"
                        aria-labelledby="userCreateAccordionHeading"
                    >
                        <div class="accordion-body user-create-accordion-body">
                            <div class="user-create-accordion-note mb-3">
                                <div class="fw-semibold mb-1">Data wajib diisi</div>
                                <div class="small text-muted mb-0">Nama lengkap, NISN, role, kelas, dan nomor HP.</div>
                            </div>

                            @if($errors->hasAny(['name', 'identity_number', 'role', 'kelas', 'phone']))
                                <div class="alert alert-danger small mb-3">
                                    Periksa kembali data yang diisi. Field yang bermasalah akan ditandai merah.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
                                @csrf
                                <input type="hidden" name="user_form" value="store">
                                <div class="col-12">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">NISN</label>
                                    <input type="text" name="identity_number" class="form-control @error('identity_number') is-invalid @enderror" value="{{ old('identity_number') }}" required>
                                    @error('identity_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
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
                                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
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
                                    <label class="form-label">No. HP <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Simpan Pengguna</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2">
                        <div class="col-md-5">
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
                        <div class="col-md-1 d-grid">
                            <button class="btn btn-outline-secondary" type="submit">Go</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
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
                                <td class="user-col-no">{{ $users->firstItem() + $loop->index }}</td>
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
                @if($users->hasPages())
                    <div class="card-footer bg-white">{{ $users->links() }}</div>
                @endif
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

            if (!shouldOpenImportUserModal) {
                return;
            }

            var importUserExcelModalElement = document.getElementById('importUserExcelModal');

            if (!importUserExcelModalElement) {
                return;
            }

            var importUserExcelModal = new bootstrap.Modal(importUserExcelModalElement);
            importUserExcelModal.show();
        });
    </script>
@endpush

@push('styles')
    <style>
        .user-create-accordion {
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        }

        .user-create-accordion .accordion-item {
            border: 0;
            background: transparent;
        }

        .user-create-accordion-button {
            padding: 1rem 1.1rem;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 55%, #0f172a 100%);
            color: #ffffff;
            border: 0;
            box-shadow: none;
        }

        .user-create-accordion-button:not(.collapsed) {
            color: #ffffff;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 55%, #0f172a 100%);
            box-shadow: none;
        }

        .user-create-accordion-button::after {
            filter: brightness(0) invert(1);
        }

        .user-create-accordion-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            flex: 0 0 auto;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .user-create-accordion-subtitle {
            color: rgba(255, 255, 255, 0.82);
        }

        .user-create-accordion-badge {
            color: #0f172a;
            font-weight: 700;
        }

        .user-create-accordion-body {
            padding: 1.15rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid rgba(13, 110, 253, 0.10);
            border-top: 0;
        }

        .user-create-accordion-note {
            border-radius: 1rem;
            padding: 0.95rem 1rem;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(13, 202, 240, 0.06));
            border: 1px solid rgba(13, 110, 253, 0.12);
        }

        .user-summary-card {
            position: relative;
            border: 1px solid rgba(13, 110, 253, 0.12);
            background: linear-gradient(135deg, #ffffff 0%, #f7fbff 40%, #eefbf2 100%);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
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
            background: radial-gradient(circle, rgba(13, 110, 253, 0.16), transparent 68%);
        }

        .user-summary-card::after {
            width: 180px;
            height: 180px;
            right: -60px;
            bottom: -90px;
            background: radial-gradient(circle, rgba(25, 135, 84, 0.12), transparent 68%);
        }

        .user-summary-hero,
        .user-summary-stats {
            position: relative;
            z-index: 1;
        }

        .user-summary-overview {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1.25rem;
            padding: 1.25rem;
            backdrop-filter: blur(12px);
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
        }

        .user-summary-review {
            color: #334155;
            font-size: 1rem;
            line-height: 1.65;
        }

        .user-summary-pill {
            background: rgba(255, 255, 255, 0.92);
            border-color: rgba(148, 163, 184, 0.24) !important;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: 0.01em;
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
        }

        .user-summary-progress {
            height: 0.8rem;
            background: rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            overflow: hidden;
        }

        .user-summary-progress .progress-bar {
            border-radius: 999px;
        }

        .user-summary-quick-strip {
            margin-top: 1rem;
        }

        .user-summary-ring-panel {
            flex: 0 0 280px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            padding: 1.25rem;
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(241, 245, 249, 0.98) 100%);
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
        }

        .user-summary-ring {
            --user-summary-rate: 0;
            width: 170px;
            height: 170px;
            border-radius: 50%;
            position: relative;
            padding: 14px;
            background: conic-gradient(#10b981 calc(var(--user-summary-rate) * 1%), rgba(148, 163, 184, 0.18) 0);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        }

        .user-summary-ring::before {
            content: '';
            position: absolute;
            inset: 14px;
            border-radius: 50%;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
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
            background: radial-gradient(circle at top, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.96));
        }

        .user-summary-ring-value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }

        .user-summary-ring-label {
            margin-top: 0.35rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #64748b;
        }

        .user-summary-ring-caption {
            max-width: 220px;
            color: #64748b;
            font-size: 0.85rem;
            line-height: 1.5;
            text-align: center;
        }

        .user-summary-stat {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
            border: 1px solid transparent;
        }

        .user-summary-stat::after {
            content: '';
            position: absolute;
            inset: auto -18% -38% auto;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.45), transparent 65%);
            pointer-events: none;
        }

        .user-summary-stat-primary {
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 55%, #ffffff 100%);
            border-color: rgba(59, 130, 246, 0.16);
        }

        .user-summary-stat-info {
            background: linear-gradient(135deg, #cffafe 0%, #ecfeff 55%, #ffffff 100%);
            border-color: rgba(6, 182, 212, 0.16);
        }

        .user-summary-stat-success {
            background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 55%, #ffffff 100%);
            border-color: rgba(34, 197, 94, 0.16);
        }

        .user-summary-stat-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 55%, #ffffff 100%);
            border-color: rgba(245, 158, 11, 0.16);
        }

        .user-summary-stat-label {
            position: relative;
            z-index: 1;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
        }

        .user-summary-stat-value {
            position: relative;
            z-index: 1;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }

        .user-summary-stat-meta {
            position: relative;
            z-index: 1;
            margin-top: 0.35rem;
            font-size: 0.84rem;
            color: #475569;
        }

        .user-summary-stat-icon {
            position: relative;
            z-index: 1;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            background: rgba(255, 255, 255, 0.7);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
        }

        .user-summary-stat-primary .user-summary-stat-icon {
            color: #0d6efd;
        }

        .user-summary-stat-info .user-summary-stat-icon {
            color: #0891b2;
        }

        .user-summary-stat-success .user-summary-stat-icon {
            color: #198754;
        }

        .user-summary-stat-warning .user-summary-stat-icon {
            color: #b45309;
        }

        @media (max-width: 1199.98px) {
            .user-summary-ring-panel {
                flex: 1 1 100%;
            }

            .user-summary-overview {
                flex: 1 1 100%;
            }
        }

        @media (max-width: 575.98px) {
            .user-create-accordion-button {
                padding: 0.9rem 0.95rem;
            }

            .user-create-accordion-icon {
                width: 2.6rem;
                height: 2.6rem;
                border-radius: 0.85rem;
            }

            .user-create-accordion-body {
                padding: 1rem;
            }

            .user-summary-overview,
            .user-summary-ring-panel {
                padding: 1rem;
            }

            .user-summary-ring {
                width: 150px;
                height: 150px;
            }

            .user-summary-ring-value {
                font-size: 1.85rem;
            }

            .user-summary-stat-value {
                font-size: 1.55rem;
            }
        }

        .user-col-no {
            text-align: center;
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
    </style>
@endpush
