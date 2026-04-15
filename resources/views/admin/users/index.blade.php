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

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white fw-semibold">Tambah Pengguna</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}" class="row g-2">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">NISN</label>
                            <input type="text" name="identity_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                @foreach($roleOptions as $roleOption)
                                    <option value="{{ $roleOption }}" @selected(old('role', 'student') === $roleOption)>{{ $roleOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas" class="form-select" required>
                                @foreach($kelasOptions as $kelasOption)
                                    <option value="{{ $kelasOption }}" @selected(old('kelas', '-') === $kelasOption)>{{ $kelasOption }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">No. HP <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                        </div>
                    </form>
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
                                    <div class="d-inline-flex gap-1 user-action-group">
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
