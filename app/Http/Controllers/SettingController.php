<?php

namespace App\Http\Controllers;

use App\Exports\ActivityLogsExport;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\AssetOptionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SettingController extends Controller
{
    public function __construct(
        private readonly AssetOptionService $assetOptionService,
    ) {
    }

    private const RUNNING_TEXT_KEY = 'public_running_text';

    private const DEFAULT_RUNNING_TEXT = 'Kembalikan barang sebelum pukul 15.30 WIB !';

    /**
     * @var array<string, string>
     */
    private const FONT_FAMILY_OPTIONS = [
        'system' => 'System UI',
        'arial' => 'Arial',
        'verdana' => 'Verdana',
        'tahoma' => 'Tahoma',
        'georgia' => 'Georgia',
        'mono' => 'Monospace',
    ];

    /**
     * @var array<string, string>
     */
    private const DEFAULT_SETTINGS = [
        'public_running_text' => self::DEFAULT_RUNNING_TEXT,
        'public_reminder_enabled' => '1',
        'public_reminder_background' => '#0a0a0a',
        'public_reminder_text_color' => '#ffffff',
        'public_running_text_speed' => '15',
        'public_running_text_font_size' => '17',
        'public_running_text_font_family' => 'system',
        'public_nav_logo_path' => '',
        'public_nav_brand_text' => 'SIM-IV',
        'public_nav_brand_subtext' => 'School Inventory System',
        'admin_nav_logo_path' => '',
        'admin_nav_brand_title' => 'Inventory Barang',
        'admin_nav_brand_subtitle' => 'SMK NEGERI 1 CIOMAS',
        'public_header_title' => 'Dashboard Inventaris',
        'public_header_subtitle' => 'Sistem Peminjaman & Pengembalian Aset Sekolah',
        'public_borrow_button_label' => 'Peminjaman Barang',
        'public_return_button_label' => 'Pengembalian Barang',
        'face_camera_preview_size' => '420',
        'face_camera_capture_size' => '512',
        'face_camera_border_radius' => '16',
        'face_camera_background' => '#111111',
        'face_camera_object_fit' => 'cover',
        'face_camera_frame_mode' => 'square',
        'face_camera_horizontal_shift' => '0',
        'face_camera_vertical_shift' => '0',
        'face_camera_debug_enabled' => '1',
    ];

    public function index(Request $request): View
    {
        $settingValues = $this->getSettingValues();
        $userBulkDeleteClassSummaries = $this->getUserBulkDeleteClassSummaries();
        $activityLogFilters = $this->resolveActivityLogFilters($request);
        $activityLogs = $this->getActivityLogs($activityLogFilters);
        $activityLogStats = $this->getActivityLogStats();
        $activityLogActionOptions = $this->getActivityLogActionOptions();
        $activityLogTableOptions = $this->getActivityLogTableOptions();

        return view('admin.settings.index', [
            'runningText' => $settingValues[self::RUNNING_TEXT_KEY],
            'settingValues' => $settingValues,
            'fontFamilyOptions' => self::FONT_FAMILY_OPTIONS,
            'assetOptions' => $this->assetOptionService->getOptions(),
            'userBulkDeleteClassSummaries' => $userBulkDeleteClassSummaries,
            'activityLogFilters' => $activityLogFilters,
            'activityLogs' => $activityLogs,
            'activityLogStats' => $activityLogStats,
            'activityLogActionOptions' => $activityLogActionOptions,
            'activityLogTableOptions' => $activityLogTableOptions,
        ]);
    }

    public function exportActivityLogs(Request $request): BinaryFileResponse
    {
        $filters = $this->resolveActivityLogFilters($request);
        $activityLogs = $this->buildActivityLogsQuery($filters)
            ->orderByDesc('timestamp')
            ->orderByDesc('id')
            ->get();

        $fileName = 'log-aktivitas-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ActivityLogsExport($activityLogs), $fileName);
    }

    public function cleanupActivityLogs(Request $request): RedirectResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'cleanup_date_from' => ['required', 'date'],
                'cleanup_date_to' => ['required', 'date', 'after_or_equal:cleanup_date_from'],
                'cleanup_password' => ['required', 'string'],
                'cleanup_master_password' => ['required', 'string'],
                'cleanup_confirm' => ['accepted'],
            ],
            [
                'cleanup_date_from.required' => 'Tanggal awal cleanup log wajib diisi.',
                'cleanup_date_from.date' => 'Format tanggal awal cleanup tidak valid.',
                'cleanup_date_to.required' => 'Tanggal akhir cleanup log wajib diisi.',
                'cleanup_date_to.date' => 'Format tanggal akhir cleanup tidak valid.',
                'cleanup_date_to.after_or_equal' => 'Tanggal akhir cleanup harus sama atau setelah tanggal awal.',
                'cleanup_password.required' => 'Password admin wajib diisi untuk menjalankan cleanup log.',
                'cleanup_master_password.required' => 'Password master emergency wajib diisi untuk verifikasi akhir cleanup log.',
                'cleanup_confirm.accepted' => 'Centang konfirmasi sebelum cleanup log dijalankan.',
            ],
        );

        if ($validator->fails()) {
            return redirect()->route('admin.settings.index', ['tab' => 'log'])
                ->withErrors($validator)
                ->withInput($request->except(['cleanup_password', 'cleanup_master_password']));
        }

        $validated = $validator->validated();
        $adminId = (int) $request->session()->get('admin_access.user_id', 0);
        $admin = User::query()
            ->whereKey($adminId)
            ->whereRaw('LOWER(role) = ?', ['admin'])
            ->where('is_active', true)
            ->first();

        if (!$admin || !filled($admin->password) || !Hash::check((string) $validated['cleanup_password'], (string) $admin->password)) {
            return redirect()->route('admin.settings.index', ['tab' => 'log'])
                ->withErrors([
                    'cleanup_password' => 'Password admin tidak valid untuk proses cleanup log.',
                ])
                ->withInput($request->except(['cleanup_password', 'cleanup_master_password']));
        }

        $masterPasswordHash = trim((string) config('auth.master_admin_password_hash', ''));

        if ($masterPasswordHash === '') {
            return redirect()->route('admin.settings.index', ['tab' => 'log'])
                ->withErrors([
                    'cleanup_master_password' => 'Master emergency password belum dikonfigurasi. Isi env MASTER_ADMIN_PASSWORD_HASH terlebih dahulu.',
                ])
                ->withInput($request->except(['cleanup_password', 'cleanup_master_password']));
        }

        if (!Hash::check((string) $validated['cleanup_master_password'], $masterPasswordHash)) {
            return redirect()->route('admin.settings.index', ['tab' => 'log'])
                ->withErrors([
                    'cleanup_master_password' => 'Password master emergency tidak valid untuk proses cleanup log.',
                ])
                ->withInput($request->except(['cleanup_password', 'cleanup_master_password']));
        }

        $dateFrom = (string) $validated['cleanup_date_from'];
        $dateTo = (string) $validated['cleanup_date_to'];
        $startTimestamp = $dateFrom . ' 00:00:00';
        $endTimestamp = $dateTo . ' 23:59:59';

        $cleanupQuery = ActivityLog::query()
            ->where('timestamp', '>=', $startTimestamp)
            ->where('timestamp', '<=', $endTimestamp);

        $candidateCount = (clone $cleanupQuery)->count();

        if ($candidateCount <= 0) {
            return redirect()->route('admin.settings.index', ['tab' => 'log'])
                ->with('error', 'Tidak ada log aktivitas pada rentang tanggal yang dipilih.');
        }

        $deletedCount = (int) (clone $cleanupQuery)->delete();

        if ($deletedCount <= 0) {
            return redirect()->route('admin.settings.index', ['tab' => 'log'])
                ->with('error', 'Cleanup log gagal dijalankan. Silakan coba lagi.');
        }

        $this->logActivity(
            'BULK_DELETE',
            'activity_logs',
            sprintf('Admin menghapus %d log aktivitas dari %s sampai %s.', $deletedCount, $dateFrom, $dateTo),
            [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'deleted_logs' => $deletedCount,
                'master_emergency_verified' => true,
            ],
        );

        return redirect()->route('admin.settings.index', ['tab' => 'log'])
            ->with('success', sprintf('Berhasil menghapus %d log aktivitas pada rentang %s sampai %s.', $deletedCount, $dateFrom, $dateTo));
    }

    public function updateRunningText(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'running_text' => ['required', 'string', 'max:255'],
            'public_reminder_enabled' => ['required', 'in:0,1'],
            'public_reminder_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'public_reminder_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'public_running_text_speed' => ['required', 'integer', 'between:5,40'],
            'public_running_text_font_size' => ['required', 'integer', 'between:12,36'],
            'public_running_text_font_family' => ['required', Rule::in(array_keys(self::FONT_FAMILY_OPTIONS))],
        ]);

        $this->saveSettings([
            self::RUNNING_TEXT_KEY => trim((string) $validated['running_text']),
            'public_reminder_enabled' => (string) $validated['public_reminder_enabled'],
            'public_reminder_background' => strtolower((string) $validated['public_reminder_background']),
            'public_reminder_text_color' => strtolower((string) $validated['public_reminder_text_color']),
            'public_running_text_speed' => (string) $validated['public_running_text_speed'],
            'public_running_text_font_size' => (string) $validated['public_running_text_font_size'],
            'public_running_text_font_family' => (string) $validated['public_running_text_font_family'],
        ]);

        $this->logActivity(
            'UPDATE',
            'settings',
            'Admin memperbarui pengaturan running teks dashboard public.',
            [
                'enabled' => (string) $validated['public_reminder_enabled'],
                'speed' => (string) $validated['public_running_text_speed'],
                'font_size' => (string) $validated['public_running_text_font_size'],
                'font_family' => (string) $validated['public_running_text_font_family'],
            ],
        );

        return redirect()->route('admin.settings.index', ['tab' => 'running-text'])
            ->with('success', 'Running teks dashboard public berhasil diperbarui.');
    }

    public function updateMenuA(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['nullable', 'string', 'max:120'],
            'brands' => ['required', 'array', 'min:1'],
            'brands.*' => ['nullable', 'string', 'max:120'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['nullable', 'string', 'max:120'],
            'conditions' => ['required', 'array', 'min:1'],
            'conditions.*' => ['nullable', 'string', 'max:120'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['nullable', 'string', 'max:120'],
            'classes' => ['required', 'array', 'min:1'],
            'classes.*' => ['nullable', 'string', 'max:120'],
        ]);

        $normalizedOptions = [
            'categories' => $this->normalizeOptionItems($validated['categories']),
            'brands' => $this->normalizeOptionItems($validated['brands']),
            'statuses' => $this->normalizeOptionItems($validated['statuses']),
            'conditions' => $this->normalizeOptionItems($validated['conditions']),
            'roles' => $this->normalizeOptionItems($validated['roles']),
            'classes' => $this->normalizeOptionItems($validated['classes']),
        ];

        $fieldLabels = [
            'categories' => 'kategori',
            'brands' => 'merk',
            'statuses' => 'status',
            'conditions' => 'kondisi',
            'roles' => 'role',
            'classes' => 'kelas',
        ];

        foreach ($normalizedOptions as $field => $values) {
            if ($values === []) {
                return back()
                    ->withInput()
                    ->withErrors([
                        $field => 'Minimal satu opsi ' . $fieldLabels[$field] . ' wajib diisi.',
                    ]);
            }
        }

        $containsAdminRole = collect($normalizedOptions['roles'])->contains(
            static fn (string $role): bool => Str::lower($role) === 'admin'
        );

        if (!$containsAdminRole) {
            return back()
                ->withInput()
                ->withErrors([
                    'roles' => 'Opsi role wajib mengandung "admin" agar akses admin tetap tersedia.',
                ]);
        }

        $this->assetOptionService->saveOptions($normalizedOptions);

        $this->logActivity(
            'UPDATE',
            'settings',
            'Admin memperbarui master data sistem (Menu A).',
            [
                'categories_count' => count($normalizedOptions['categories']),
                'brands_count' => count($normalizedOptions['brands']),
                'statuses_count' => count($normalizedOptions['statuses']),
                'conditions_count' => count($normalizedOptions['conditions']),
                'roles_count' => count($normalizedOptions['roles']),
                'classes_count' => count($normalizedOptions['classes']),
            ],
        );

        return redirect()->route('admin.settings.index', ['tab' => 'menu-a'])
            ->with('success', 'Master data barang dan pengguna berhasil diperbarui.');
    }

    public function updateMenuB(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'public_header_title' => ['required', 'string', 'max:120'],
            'public_header_subtitle' => ['required', 'string', 'max:200'],
            'public_borrow_button_label' => ['required', 'string', 'max:80'],
            'public_return_button_label' => ['required', 'string', 'max:80'],
            'public_nav_logo_file' => ['nullable', 'file', 'mimes:png', 'max:2048'],
            'public_nav_brand_text' => ['required', 'string', 'max:60'],
            'public_nav_brand_subtext' => ['required', 'string', 'max:120'],
            'admin_nav_logo_file' => ['nullable', 'file', 'mimes:png', 'max:2048'],
            'admin_nav_brand_title' => ['required', 'string', 'max:80'],
            'admin_nav_brand_subtitle' => ['required', 'string', 'max:140'],
        ]);

        $existingSettingValues = $this->getSettingValues();
        $publicNavLogoPath = $this->replaceNavbarLogo(
            $request->file('public_nav_logo_file'),
            'branding/public',
            $existingSettingValues['public_nav_logo_path'] ?? null,
        );
        $adminNavLogoPath = $this->replaceNavbarLogo(
            $request->file('admin_nav_logo_file'),
            'branding/admin',
            $existingSettingValues['admin_nav_logo_path'] ?? null,
        );

        $this->saveSettings([
            'public_header_title' => trim((string) $validated['public_header_title']),
            'public_header_subtitle' => trim((string) $validated['public_header_subtitle']),
            'public_borrow_button_label' => trim((string) $validated['public_borrow_button_label']),
            'public_return_button_label' => trim((string) $validated['public_return_button_label']),
            'public_nav_logo_path' => (string) ($publicNavLogoPath ?? ''),
            'public_nav_brand_text' => trim((string) $validated['public_nav_brand_text']),
            'public_nav_brand_subtext' => trim((string) $validated['public_nav_brand_subtext']),
            'admin_nav_logo_path' => (string) ($adminNavLogoPath ?? ''),
            'admin_nav_brand_title' => trim((string) $validated['admin_nav_brand_title']),
            'admin_nav_brand_subtitle' => trim((string) $validated['admin_nav_brand_subtitle']),
        ]);

        $this->logActivity(
            'UPDATE',
            'settings',
            'Admin memperbarui konfigurasi dashboard public (Menu B).',
            [
                'header_title' => trim((string) $validated['public_header_title']),
                'borrow_button_label' => trim((string) $validated['public_borrow_button_label']),
                'return_button_label' => trim((string) $validated['public_return_button_label']),
                'public_nav_brand_text' => trim((string) $validated['public_nav_brand_text']),
                'admin_nav_brand_title' => trim((string) $validated['admin_nav_brand_title']),
                'public_nav_logo_uploaded' => $request->hasFile('public_nav_logo_file'),
                'admin_nav_logo_uploaded' => $request->hasFile('admin_nav_logo_file'),
            ],
        );

        return redirect()->route('admin.settings.index', ['tab' => 'menu-b'])
            ->with('success', 'Pengaturan Menu B berhasil diperbarui.');
    }

    public function updateMenuC(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'face_camera_preview_size' => ['required', 'integer', 'between:280,720'],
            'face_camera_capture_size' => ['required', 'integer', 'between:320,1024'],
            'face_camera_border_radius' => ['required', 'integer', 'between:0,32'],
            'face_camera_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'face_camera_object_fit' => ['required', Rule::in(['cover', 'contain'])],
            'face_camera_frame_mode' => ['required', Rule::in(['square', 'wide'])],
            'face_camera_horizontal_shift' => ['required', 'integer', 'between:-100,100'],
            'face_camera_vertical_shift' => ['required', 'integer', 'between:-100,100'],
            'face_camera_debug_enabled' => ['required', 'in:0,1'],
        ]);

        $this->saveSettings([
            'face_camera_preview_size' => (string) $validated['face_camera_preview_size'],
            'face_camera_capture_size' => (string) $validated['face_camera_capture_size'],
            'face_camera_border_radius' => (string) $validated['face_camera_border_radius'],
            'face_camera_background' => strtolower((string) $validated['face_camera_background']),
            'face_camera_object_fit' => (string) $validated['face_camera_object_fit'],
            'face_camera_frame_mode' => (string) $validated['face_camera_frame_mode'],
            'face_camera_horizontal_shift' => (string) $validated['face_camera_horizontal_shift'],
            'face_camera_vertical_shift' => (string) $validated['face_camera_vertical_shift'],
            'face_camera_debug_enabled' => (string) $validated['face_camera_debug_enabled'],
        ]);

        $this->logActivity(
            'UPDATE',
            'settings',
            'Admin memperbarui konfigurasi kamera (Menu C).',
            [
                'preview_size' => (string) $validated['face_camera_preview_size'],
                'capture_size' => (string) $validated['face_camera_capture_size'],
                'frame_mode' => (string) $validated['face_camera_frame_mode'],
                'horizontal_shift' => (string) $validated['face_camera_horizontal_shift'],
                'vertical_shift' => (string) $validated['face_camera_vertical_shift'],
                'debug_enabled' => (string) $validated['face_camera_debug_enabled'],
            ],
        );

        return redirect()->route('admin.settings.index', ['tab' => 'menu-c'])
            ->with('success', 'Pengaturan Menu C berhasil diperbarui.');
    }

    public function updateAdminPassword(Request $request): RedirectResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            ],
            [
                'current_password.required' => 'Password saat ini wajib diisi.',
                'new_password.required' => 'Password baru wajib diisi.',
                'new_password.min' => 'Password baru minimal 8 karakter.',
                'new_password.confirmed' => 'Konfirmasi password baru tidak sama.',
                'new_password.different' => 'Password baru harus berbeda dari password saat ini.',
            ],
        );

        if ($validator->fails()) {
            return redirect()->route('admin.settings.index', ['tab' => 'admin'])
                ->withErrors($validator)
                ->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
        }

        $validated = $validator->validated();
        $adminId = (int) $request->session()->get('admin_access.user_id', 0);
        $admin = User::query()
            ->whereKey($adminId)
            ->whereRaw('LOWER(role) = ?', ['admin'])
            ->where('is_active', true)
            ->first();

        if (!$admin) {
            return redirect()->route('admin.settings.index', ['tab' => 'admin'])
                ->with('error', 'Sesi admin tidak valid. Silakan login ulang.');
        }

        if (!Hash::check((string) $validated['current_password'], (string) $admin->password)) {
            return redirect()->route('admin.settings.index', ['tab' => 'admin'])
                ->withErrors([
                    'current_password' => 'Password saat ini tidak sesuai.',
                ]);
        }

        $admin->forceFill([
            'password' => Hash::make((string) $validated['new_password']),
        ])->save();

        $this->logActivity(
            'UPDATE',
            'users',
            'Admin memperbarui password akun admin.',
            [
                'admin_id' => (int) $admin->id,
            ],
        );

        return redirect()->route('admin.settings.index', ['tab' => 'admin'])
            ->with('success', 'Password admin berhasil diperbarui.');
    }

    public function bulkDeleteUsersByClass(Request $request): RedirectResponse
    {
        $classOptions = array_values(array_map(
            static fn (array $summary): string => $summary['kelas'],
            $this->getUserBulkDeleteClassSummaries(),
        ));

        $validator = Validator::make(
            $request->all(),
            [
                'bulk_delete_class' => ['required', 'string', Rule::in($classOptions)],
                'bulk_delete_confirm' => ['accepted'],
            ],
            [
                'bulk_delete_class.required' => 'Pilih kategori kelas yang ingin dihapus.',
                'bulk_delete_class.in' => 'Kategori kelas yang dipilih tidak valid.',
                'bulk_delete_confirm.accepted' => 'Centang konfirmasi sebelum menjalankan hapus massal.',
            ],
        );

        if ($validator->fails()) {
            return redirect()->route('admin.settings.index', ['tab' => 'user-data'])
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $selectedClass = trim((string) $validated['bulk_delete_class']);
        $classUsersQuery = $this->queryUsersByClass($selectedClass);
        $totalUsers = (clone $classUsersQuery)->count();

        if ($totalUsers === 0) {
            return redirect()->route('admin.settings.index', ['tab' => 'user-data'])
                ->with('error', 'Tidak ada data pengguna pada kategori kelas yang dipilih.');
        }

        $adminCount = (clone $classUsersQuery)
            ->where('role', 'admin')
            ->count();

        $usersWithLoanCount = (clone $classUsersQuery)
            ->where('role', '!=', 'admin')
            ->whereHas('loans')
            ->count();

        $deletableUsers = (clone $classUsersQuery)
            ->where('role', '!=', 'admin')
            ->whereDoesntHave('loans')
            ->get(['id', 'face_thumbnail_path']);

        if ($deletableUsers->isEmpty()) {
            return redirect()->route('admin.settings.index', ['tab' => 'user-data'])
                ->with('error', 'Tidak ada pengguna yang bisa dihapus untuk kelas tersebut. Akun admin dan pengguna dengan riwayat pinjaman akan dilewati.');
        }

        $deletableUserIds = $deletableUsers
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->values()
            ->all();

        $thumbnailPathByUserId = $deletableUsers
            ->filter(static fn (User $user): bool => filled($user->face_thumbnail_path))
            ->mapWithKeys(static fn (User $user): array => [(int) $user->id => (string) $user->face_thumbnail_path]);

        try {
            $deletedCount = DB::transaction(function () use ($deletableUserIds): int {
                return (int) User::query()
                    ->whereIn('id', $deletableUserIds)
                    ->where('role', '!=', 'admin')
                    ->whereDoesntHave('loans')
                    ->delete();
            });
        } catch (Throwable) {
            return redirect()->route('admin.settings.index', ['tab' => 'user-data'])
                ->with('error', 'Hapus massal gagal dijalankan. Silakan coba lagi.');
        }

        if ($deletedCount <= 0) {
            return redirect()->route('admin.settings.index', ['tab' => 'user-data'])
                ->with('error', 'Tidak ada data pengguna yang berhasil dihapus.');
        }

        $remainingIds = User::query()
            ->whereIn('id', $deletableUserIds)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $deletedThumbnailPaths = collect(array_diff($deletableUserIds, $remainingIds))
            ->map(static fn (int $userId): ?string => $thumbnailPathByUserId->get($userId))
            ->filter(static fn (?string $thumbnailPath): bool => filled($thumbnailPath))
            ->values()
            ->all();

        if ($deletedThumbnailPaths !== []) {
            Storage::disk('public')->delete($deletedThumbnailPaths);
        }

        $messageParts = [
            sprintf('Berhasil menghapus %d pengguna dari kelas %s.', $deletedCount, $selectedClass),
        ];

        if ($adminCount > 0) {
            $messageParts[] = sprintf('%d akun admin dilewati.', $adminCount);
        }

        if ($usersWithLoanCount > 0) {
            $messageParts[] = sprintf('%d pengguna dengan riwayat pinjaman tidak dihapus.', $usersWithLoanCount);
        }

        $this->logActivity(
            'BULK_DELETE',
            'users',
            sprintf('Admin menghapus massal %d pengguna pada kelas %s.', $deletedCount, $selectedClass),
            [
                'class' => $selectedClass,
                'deleted_users' => $deletedCount,
                'skipped_admin_users' => $adminCount,
                'skipped_users_with_loans' => $usersWithLoanCount,
            ],
        );

        return redirect()->route('admin.settings.index', ['tab' => 'user-data'])
            ->with('success', implode(' ', $messageParts));
    }

    /**
     * @return array<string, string>
     */
    private function getSettingValues(): array
    {
        $storedValues = Setting::query()
            ->whereIn('setting_key', array_keys(self::DEFAULT_SETTINGS))
            ->pluck('setting_value', 'setting_key');

        $settingValues = self::DEFAULT_SETTINGS;

        foreach ($storedValues as $key => $value) {
            if (!is_string($key) || !array_key_exists($key, $settingValues)) {
                continue;
            }

            if ($value !== null && trim((string) $value) !== '') {
                $settingValues[$key] = (string) $value;
            }
        }

        return $settingValues;
    }

    private function saveSetting(string $key, string $value): void
    {
        $this->saveSettings([$key => $value]);
    }

    private function replaceNavbarLogo(?UploadedFile $logoFile, string $directory, ?string $previousPath): ?string
    {
        $normalizedPreviousPath = $previousPath !== null ? trim($previousPath) : null;

        if ($logoFile === null) {
            return $normalizedPreviousPath !== '' ? $normalizedPreviousPath : null;
        }

        $newPath = $logoFile->store($directory, 'public');

        if ($normalizedPreviousPath !== null && $normalizedPreviousPath !== '' && $normalizedPreviousPath !== $newPath) {
            Storage::disk('public')->delete($normalizedPreviousPath);
        }

        return $newPath;
    }

    /**
     * @return list<array{kelas: string, total_users: int, deletable_users: int, admin_users: int, users_with_loans: int}>
     */
    private function getUserBulkDeleteClassSummaries(): array
    {
        $users = User::query()
            ->select(['id', 'kelas', 'role'])
            ->withCount('loans')
            ->get();

        $summaryByClass = [];

        foreach ($users as $user) {
            $className = $this->normalizeClassName($user->kelas);

            if (!array_key_exists($className, $summaryByClass)) {
                $summaryByClass[$className] = [
                    'total_users' => 0,
                    'deletable_users' => 0,
                    'admin_users' => 0,
                    'users_with_loans' => 0,
                ];
            }

            $summaryByClass[$className]['total_users']++;

            if (Str::lower((string) $user->role) === 'admin') {
                $summaryByClass[$className]['admin_users']++;
                continue;
            }

            if ((int) $user->loans_count > 0) {
                $summaryByClass[$className]['users_with_loans']++;
                continue;
            }

            $summaryByClass[$className]['deletable_users']++;
        }

        if ($summaryByClass === []) {
            return [];
        }

        uksort($summaryByClass, static fn (string $left, string $right): int => strnatcasecmp($left, $right));

        return collect($summaryByClass)
            ->map(static fn (array $summary, string $className): array => [
                'kelas' => $className,
                'total_users' => $summary['total_users'],
                'deletable_users' => $summary['deletable_users'],
                'admin_users' => $summary['admin_users'],
                'users_with_loans' => $summary['users_with_loans'],
            ])
            ->values()
            ->all();
    }

    private function normalizeClassName(?string $className): string
    {
        $normalized = trim((string) $className);

        return $normalized !== '' ? $normalized : '-';
    }

    private function queryUsersByClass(string $className): Builder
    {
        return User::query()
            ->where(function (Builder $query) use ($className): void {
                if ($className === '-') {
                    $query->where('kelas', '-')
                        ->orWhereNull('kelas')
                        ->orWhere('kelas', '');

                    return;
                }

                $query->where('kelas', $className);
            });
    }

    /**
     * @return array{search: string, action: string, table: string, date_from: string, date_to: string}
     */
    private function resolveActivityLogFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('log_search', '')),
            'action' => trim((string) $request->input('log_action', '')),
            'table' => trim((string) $request->input('log_table', '')),
            'date_from' => trim((string) $request->input('log_date_from', '')),
            'date_to' => trim((string) $request->input('log_date_to', '')),
        ];
    }

    /**
     * @param array{search: string, action: string, table: string, date_from: string, date_to: string} $filters
     */
    private function getActivityLogs(array $filters): LengthAwarePaginator
    {
        return $this->buildActivityLogsQuery($filters)
            ->orderByDesc('timestamp')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'log_page')
            ->withQueryString();
    }

    /**
     * @param array{search: string, action: string, table: string, date_from: string, date_to: string} $filters
     */
    private function buildActivityLogsQuery(array $filters): Builder
    {
        $query = ActivityLog::query();

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('action', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%")
                    ->orWhere('data', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }

        if ($filters['table'] !== '') {
            $query->where('table_name', $filters['table']);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_from']) === 1) {
            $query->where('timestamp', '>=', $filters['date_from'] . ' 00:00:00');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_to']) === 1) {
            $query->where('timestamp', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $query;
    }

    /**
     * @return list<string>
     */
    private function getActivityLogActionOptions(): array
    {
        return ActivityLog::query()
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(static fn ($action): string => (string) $action)
            ->all();
    }

    /**
     * @return list<string>
     */
    private function getActivityLogTableOptions(): array
    {
        return ActivityLog::query()
            ->whereNotNull('table_name')
            ->where('table_name', '!=', '')
            ->select('table_name')
            ->distinct()
            ->orderBy('table_name')
            ->pluck('table_name')
            ->map(static fn ($tableName): string => (string) $tableName)
            ->all();
    }

    /**
     * @return array{total: int, today: int, last_7_days: int, latest_timestamp: ?string}
     */
    private function getActivityLogStats(): array
    {
        $todayStart = now()->startOfDay();
        $lastSevenDaysStart = now()->subDays(6)->startOfDay();

        return [
            'total' => ActivityLog::query()->count(),
            'today' => ActivityLog::query()->where('timestamp', '>=', $todayStart)->count(),
            'last_7_days' => ActivityLog::query()->where('timestamp', '>=', $lastSevenDaysStart)->count(),
            'latest_timestamp' => ActivityLog::query()->orderByDesc('timestamp')->value('timestamp'),
        ];
    }

    /**
     * @param array<string, int|string> $details
     */
    private function logActivity(string $action, string $tableName, string $data, array $details = []): void
    {
        try {
            $adminSession = request()->session()->get('admin_access');
            $adminName = is_array($adminSession)
                ? trim((string) ($adminSession['user_name'] ?? ''))
                : '';
            $serializedDetails = $details !== []
                ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;
            $detailParts = [];

            if ($adminName !== '') {
                $detailParts[] = 'admin=' . $adminName;
            }

            if (is_string($serializedDetails) && $serializedDetails !== '') {
                $detailParts[] = 'payload=' . $serializedDetails;
            }

            ActivityLog::query()->create([
                'timestamp' => now(),
                'action' => strtoupper($action),
                'table_name' => $tableName,
                'data' => $data,
                'details' => $detailParts !== [] ? implode(' | ', $detailParts) : null,
                'user_agent' => request()->userAgent(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param array<int, string> $items
     * @return list<string>
     */
    private function normalizeOptionItems(array $items): array
    {
        $normalized = [];
        $seen = [];

        foreach ($items as $item) {
            $value = trim((string) $item);

            if ($value === '') {
                continue;
            }

            $dedupeKey = Str::lower($value);

            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;
            $normalized[] = Str::limit($value, 120, '');
        }

        return $normalized;
    }

    /**
     * @param array<string, string> $settings
     */
    private function saveSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }
    }
}
