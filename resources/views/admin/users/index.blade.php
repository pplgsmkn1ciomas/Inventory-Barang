@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Data Pengguna</h4>
            <p class="text-muted mb-0">Pengelolaan admin, guru, dan siswa.</p>
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
                                <option value="student">student</option>
                                <option value="teacher">teacher</option>
                                <option value="admin">admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <input type="text" name="kelas" class="form-control" value="-" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
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
                            <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="Cari ID/Nama/Kelas/Email/HP...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="role">
                                <option value="">Semua Role</option>
                                @foreach(['admin', 'teacher', 'student'] as $role)
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
                            <th>No</th>
                            <th>Identity</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Email / HP</th>
                            <th>Role</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>{{ $user->identity_number }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->kelas }}</td>
                                <td>
                                    <div>{{ $user->email ?: '-' }}</div>
                                    <small class="text-muted">{{ $user->phone ?: '-' }}</small>
                                </td>
                                <td><span class="badge text-bg-secondary">{{ $user->role }}</span></td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
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
@endsection
