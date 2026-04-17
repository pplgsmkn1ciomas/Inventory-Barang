<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\User;
use App\Services\AssetOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        private readonly AssetOptionService $assetOptionService,
    ) {
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('identity_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('kelas', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->string('kelas'));
        }

        $users = $query->latest('id')->paginate(20)->withQueryString();
        $roleOptions = collect($this->resolveRoleOptions());
        $kelasOptions = collect($this->resolveKelasOptions());
        $totalUsers = User::count();
        $totalActiveUsers = User::where('is_active', true)->count();
        $totalInactiveUsers = User::where('is_active', false)->count();
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalFaceRegisteredUsers = User::whereNotNull('face_registered_at')->count();
        $totalFacePendingUsers = max($totalUsers - $totalFaceRegisteredUsers, 0);
        $faceCompletionRate = $totalUsers > 0
            ? (int) round(($totalFaceRegisteredUsers / $totalUsers) * 100)
            : 0;

        if ($totalUsers === 0) {
            $reviewText = 'Belum ada data pengguna yang tersimpan.';
        } elseif ($totalFacePendingUsers > 0) {
            $reviewText = sprintf(
                'Kelengkapan data wajah saat ini %d%%. Ada %d pengguna yang masih perlu registrasi wajah.',
                $faceCompletionRate,
                $totalFacePendingUsers,
            );
        } else {
            $reviewText = sprintf(
                'Kelengkapan data wajah saat ini %d%%. Seluruh pengguna sudah memiliki data wajah.',
                $faceCompletionRate,
            );
        }

        $userSummary = [
            'review' => $reviewText,
            'face_completion_rate' => $faceCompletionRate,
            'highlights' => [
                'Aktif ' . number_format($totalActiveUsers),
                'Nonaktif ' . number_format($totalInactiveUsers),
                'Wajah terekam ' . number_format($totalFaceRegisteredUsers),
                'Perlu registrasi ' . number_format($totalFacePendingUsers),
            ],
            'stats' => [
                [
                    'label' => 'Total Pengguna',
                    'value' => $totalUsers,
                    'meta' => 'Semua akun yang terdaftar.',
                    'icon' => 'fa-solid fa-users',
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Siswa',
                    'value' => $totalStudents,
                    'meta' => 'Akun dengan role student.',
                    'icon' => 'fa-solid fa-user-graduate',
                    'tone' => 'info',
                ],
                [
                    'label' => 'Guru',
                    'value' => $totalTeachers,
                    'meta' => 'Akun dengan role teacher.',
                    'icon' => 'fa-solid fa-chalkboard-user',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Admin',
                    'value' => $totalAdmins,
                    'meta' => 'Akun pengelola sistem.',
                    'icon' => 'fa-solid fa-user-shield',
                    'tone' => 'warning',
                ],
            ],
        ];

        return view('admin.users.index', [
            'users' => $users,
            'roleOptions' => $roleOptions,
            'kelasOptions' => $kelasOptions,
            'userSummary' => $userSummary,
            'filters' => [
                'search' => (string) $request->input('search', ''),
                'role' => (string) $request->input('role', ''),
                'kelas' => (string) $request->input('kelas', ''),
            ],
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $isTemplate = $request->boolean('template');
        $users = $isTemplate
            ? collect()
            : User::query()->latest('id')->get();

        $fileNamePrefix = $isTemplate ? 'template-import-data-pengguna' : 'data-pengguna';
        $fileName = $fileNamePrefix . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new UsersExport($users), $fileName);
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ]);

        $import = new UsersImport($this->resolveRoleOptions(), $this->resolveKelasOptions());

        try {
            Excel::import($import, $validated['excel_file']);
        } catch (Throwable) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Import Excel gagal. Pastikan format file dan header sesuai template.');
        }

        return redirect()->route('admin.users.index')->with(
            'success',
            'Import Excel selesai. Ditambahkan: ' . $import->getCreatedCount()
                . ', Diperbarui: ' . $import->getUpdatedCount()
                . ', Dilewati: ' . $import->getSkippedCount() . '.'
        );
    }

    public function store(Request $request)
    {
        $roleOptions = $this->resolveRoleOptions();
        $kelasOptions = $this->resolveKelasOptions();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'identity_number' => ['required', 'string', 'max:120', 'unique:users,identity_number'],
            'role' => ['required', 'string', 'max:120', Rule::in($roleOptions)],
            'kelas' => ['required', 'string', 'max:120', Rule::in($kelasOptions)],
            'email' => ['nullable', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            $validated['password'] = null;
        }

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $roleOptions = $this->resolveRoleOptions([(string) $user->role]);
        $kelasOptions = $this->resolveKelasOptions([(string) $user->kelas]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'identity_number' => ['required', 'string', 'max:120', Rule::unique('users', 'identity_number')->ignore($user->id)],
            'role' => ['required', 'string', 'max:120', Rule::in($roleOptions)],
            'kelas' => ['required', 'string', 'max:120', Rule::in($kelasOptions)],
            'email' => ['nullable', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $thumbnailPath = $user->face_thumbnail_path;

        if ($user->loans()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Pengguna tidak bisa dihapus karena memiliki riwayat peminjaman.');
        }

        $user->delete();

        if (filled($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    public function destroyFaceThumbnail(User $user): RedirectResponse
    {
        $thumbnailPath = $user->face_thumbnail_path;

        if (!filled($thumbnailPath) && !filled($user->face_encoding) && is_null($user->face_registered_at)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Data wajah belum tersedia untuk dihapus.');
        }

        if (filled($thumbnailPath)) {
            Storage::disk('public')->delete($thumbnailPath);
        }

        $user->update([
            'face_encoding' => null,
            'face_registered_at' => null,
            'face_thumbnail_path' => null,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Data wajah berhasil dihapus.');
    }

    /**
     * @param list<string> $extraValues
     * @return list<string>
     */
    private function resolveRoleOptions(array $extraValues = []): array
    {
        $optionValues = $this->assetOptionService->getOptions();

        return $this->mergeOptionValues(
            $optionValues['roles'] ?? [],
            array_merge(
                User::query()
                    ->whereNotNull('role')
                    ->where('role', '!=', '')
                    ->pluck('role')
                    ->all(),
                $extraValues,
            ),
        );
    }

    /**
     * @param list<string> $extraValues
     * @return list<string>
     */
    private function resolveKelasOptions(array $extraValues = []): array
    {
        $optionValues = $this->assetOptionService->getOptions();

        return $this->mergeOptionValues(
            $optionValues['classes'] ?? [],
            array_merge(
                User::query()
                    ->whereNotNull('kelas')
                    ->where('kelas', '!=', '')
                    ->pluck('kelas')
                    ->all(),
                $extraValues,
            ),
        );
    }

    /**
     * @param list<string> $defaultValues
     * @param list<string> $extraValues
     * @return list<string>
     */
    private function mergeOptionValues(array $defaultValues, array $extraValues): array
    {
        $merged = [];
        $seen = [];

        foreach (array_merge($defaultValues, $extraValues) as $value) {
            $clean = trim((string) $value);

            if ($clean === '') {
                continue;
            }

            $dedupeKey = Str::lower($clean);

            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;
            $merged[] = $clean;
        }

        return $merged;
    }
}
