<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Loan;
use App\Models\Setting;
use App\Models\User;
use App\Services\FaceDescriptorService;
use App\Services\FaceEncodingMatcher;
use App\Services\AssetOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AssetOptionService $assetOptionService,
        private readonly FaceDescriptorService $faceDescriptorService,
        private readonly FaceEncodingMatcher $faceEncodingMatcher,
    ) {
    }

    public function showAdminLogin(): RedirectResponse
    {
        return redirect()
            ->route('dashboard.public')
            ->with('show_admin_login', true);
    }

    public function publicIndex(): View
    {
        $publicSettingDefaults = [
            'public_running_text' => 'Kembalikan barang sebelum pukul 15.30 WIB !',
            'public_reminder_enabled' => '1',
            'public_reminder_background' => '#0A0A0A',
            'public_reminder_text_color' => '#FFFFFF',
            'public_running_text_speed' => '15',
            'public_running_text_font_size' => '17',
            'public_running_text_font_family' => 'system',
            'public_header_title' => 'Dashboard Inventaris',
            'public_header_subtitle' => 'Sistem Peminjaman & Pengembalian Aset Sekolah',
            'public_borrow_button_label' => 'Peminjaman Barang',
            'public_return_button_label' => 'Pengembalian Barang',
        ];

        $storedSettingValues = Setting::query()
            ->whereIn('setting_key', array_keys($publicSettingDefaults))
            ->pluck('setting_value', 'setting_key');

        $publicSettings = $publicSettingDefaults;
        foreach ($storedSettingValues as $key => $value) {
            if (!is_string($key) || !array_key_exists($key, $publicSettings)) {
                continue;
            }

            if ($value !== null && trim((string) $value) !== '') {
                $publicSettings[$key] = (string) $value;
            }
        }

        $runningText = $publicSettings['public_running_text'];

        $fontFamilyMap = [
            'system' => 'system-ui, sans-serif',
            'arial' => 'Arial, sans-serif',
            'verdana' => 'Verdana, sans-serif',
            'tahoma' => 'Tahoma, sans-serif',
            'georgia' => 'Georgia, serif',
            'mono' => 'monospace',
        ];

        $speed = (int) ($publicSettings['public_running_text_speed'] ?? 15);
        $fontSize = (int) ($publicSettings['public_running_text_font_size'] ?? 17);
        $fontFamilyKey = (string) ($publicSettings['public_running_text_font_family'] ?? 'system');
        $backgroundColor = strtoupper((string) ($publicSettings['public_reminder_background'] ?? '#0A0A0A'));
        $textColor = strtoupper((string) ($publicSettings['public_reminder_text_color'] ?? '#FFFFFF'));

        if (!preg_match('/^#[0-9A-F]{6}$/', $backgroundColor)) {
            $backgroundColor = '#0A0A0A';
        }

        if (!preg_match('/^#[0-9A-F]{6}$/', $textColor)) {
            $textColor = '#FFFFFF';
        }

        $publicSettings['public_running_text_speed'] = (string) max(5, min(40, $speed));
        $publicSettings['public_running_text_font_size'] = (string) max(12, min(36, $fontSize));
        $publicSettings['public_running_text_font_family'] = $fontFamilyMap[$fontFamilyKey] ?? $fontFamilyMap['system'];
        $publicSettings['public_reminder_background'] = $backgroundColor;
        $publicSettings['public_reminder_text_color'] = $textColor;

        $classOptions = $this->resolvePublicClassOptions();
        $publicStudentRosterByClass = $this->resolvePublicStudentRosterByClass($classOptions);
        $faceCameraSettings = $this->resolveFaceCameraSettings();

        $availableAssets = Asset::query()
            ->where('status', 'available')
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $activeLoans = Loan::query()
            ->with([
                'user:id,name,identity_number,kelas,phone,role',
                'asset:id,brand,model',
            ])
            ->whereIn('status', ['active', 'overdue'])
            ->whereHas('user', function ($query): void {
                $query->where('role', '!=', 'teacher');
            })
            ->latest('loan_date')
            ->get();

        return view('dashboard.public', [
            'availableAssets' => $availableAssets,
            'activeLoans' => $activeLoans,
            'runningText' => $runningText,
            'publicSettings' => $publicSettings,
            'classOptions' => $classOptions,
            'publicStudentRosterByClass' => $publicStudentRosterByClass,
            'faceCameraSettings' => $faceCameraSettings,
        ]);
    }

    public function registerPublicUser(Request $request): RedirectResponse
    {
        $classOptions = $this->resolvePublicClassOptions();

        $validated = $request->validate([
            'public_register_user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(static function ($query): void {
                $query->where('role', 'student')->where('is_active', true);
            })],
            'public_register_kelas' => ['required', 'string', 'max:120', Rule::in($classOptions)],
            'public_register_identity_number' => ['required', 'string', 'max:120'],
            'public_register_phone' => ['required', 'string', 'max:30'],
            'public_register_image_base64' => ['required', 'string'],
            'public_register_face_descriptor' => ['required', 'string'],
        ]);

        $student = User::query()->findOrFail((int) $validated['public_register_user_id']);

        if (trim((string) $student->kelas) !== trim((string) $validated['public_register_kelas'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'public_register_kelas' => 'Kelas yang dipilih tidak sesuai dengan data murid.',
                ]);
        }

        if ($this->hasRegisteredFace($student)) {
            return back()
                ->withInput()
                ->withErrors([
                    'public_register_user_id' => 'Data wajah untuk siswa ini sudah tersimpan. Hapus data wajah terlebih dahulu sebelum registrasi ulang.',
                ]);
        }

        if (User::query()
            ->where('identity_number', trim((string) $validated['public_register_identity_number']))
            ->where('id', '!=', $student->id)
            ->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'public_register_identity_number' => 'NISN sudah terdaftar di data pengguna lain.',
                ]);
        }

        $faceDescriptor = $this->faceDescriptorService->normalize($validated['public_register_face_descriptor']);

        if ($faceDescriptor === null) {
            return back()
                ->withInput()
                ->withErrors([
                    'public_register_face_descriptor' => 'Descriptor wajah tidak valid. Pastikan hanya satu wajah terdeteksi sebelum menyimpan.',
                ]);
        }

        $matchingUser = $this->faceEncodingMatcher->findMatchingUserByEncoding($faceDescriptor, $student->id);

        if ($matchingUser !== null) {
            return back()
                ->withInput()
                ->withErrors([
                    'public_register_image_base64' => sprintf(
                        'Wajah ini sudah terdaftar pada akun %s (%s). Hapus data wajah pengguna tersebut terlebih dahulu sebelum registrasi baru.',
                        $matchingUser->name,
                        $matchingUser->kelas
                    ),
                ]);
        }

        $previousThumbnailPath = $student->face_thumbnail_path;
        $thumbnailPath = $this->storeFaceThumbnail($student, (string) $validated['public_register_image_base64']);

        $student->update([
            'identity_number' => trim((string) $validated['public_register_identity_number']),
            'kelas' => trim((string) $validated['public_register_kelas']),
            'phone' => trim((string) $validated['public_register_phone']),
            'face_encoding' => json_encode(array_values($faceDescriptor)),
            'face_registered_at' => now(),
            'face_thumbnail_path' => $thumbnailPath ?? $previousThumbnailPath,
            'is_active' => true,
        ]);

        if ($thumbnailPath && filled($previousThumbnailPath) && $previousThumbnailPath !== $thumbnailPath) {
            Storage::disk('public')->delete($previousThumbnailPath);
        }

        return redirect()
            ->route('dashboard.public')
            ->with('success', 'Registrasi siswa dan face recognition berhasil disimpan.');
    }

    public function loginAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $admin = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->get(['id', 'name', 'password'])
            ->first(function (User $user) use ($validated): bool {
                return filled($user->password) && Hash::check($validated['password'], $user->password);
            });

        if (!$admin) {
            return redirect()
                ->route('dashboard.public')
                ->with('error', 'Password admin tidak valid.')
                ->with('show_admin_login', true);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_access', [
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'granted_at' => now()->getTimestamp(),
        ]);

        return redirect()
            ->route('dashboard.admin')
            ->with('success', 'Login admin berhasil.');
    }

    public function logoutAdmin(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_access');
        $request->session()->regenerateToken();

        return redirect()
            ->route('dashboard.public')
            ->with('success', 'Logout admin berhasil.');
    }

    public function adminIndex(): View
    {
        $assets = Asset::query()->get();
        $activeLoans = Loan::query()
            ->with([
                'user:id,name,identity_number,kelas,phone',
                'asset:id,brand,model,serial_number,barcode',
            ])
            ->where('status', 'active')
            ->latest('loan_date')
            ->get();

        $normalizeCategory = static function (?string $category): string {
            $value = trim((string) $category);

            return $value !== '' ? $value : 'Tanpa Kategori';
        };

        $isLaptopCategory = static function (?string $category): bool {
            $value = strtolower(trim((string) $category));

            return str_contains($value, 'laptop') || str_contains($value, 'notebook');
        };

        $toSlides = static function ($items, callable $groupBy): Collection {
            return $items
                ->groupBy($groupBy)
                ->map(fn ($group, $label) => [
                    'label' => (string) $label,
                    'count' => $group->count(),
                ])
                ->sortByDesc('count')
                ->values();
        };

        $totalCategorySlides = $toSlides(
            $assets->reject(fn (Asset $asset) => $isLaptopCategory($asset->category)),
            fn (Asset $asset) => $normalizeCategory($asset->category)
        );

        $laptopAssets = $assets->filter(
            fn (Asset $asset) => $isLaptopCategory($asset->category)
        );

        $totalLaptopBrandSlides = $toSlides(
            $laptopAssets,
            fn (Asset $asset) => ($brand = trim((string) $asset->brand)) !== '' ? $brand : 'Tanpa Merek'
        );

        $availableLaptopAssets = $laptopAssets
            ->where('status', 'available')
            ->sortBy(fn (Asset $asset) => strtolower(trim((string) $asset->brand . ' ' . (string) $asset->model)))
            ->values();

        $availableLaptopBrandSlides = $toSlides(
            $availableLaptopAssets,
            fn (Asset $asset) => ($brand = trim((string) $asset->brand)) !== '' ? $brand : 'Tanpa Merek'
        );

        $borrowedCategorySlides = $toSlides(
            $activeLoans
                ->filter(fn (Loan $loan) => $loan->asset !== null)
                ->map(fn (Loan $loan) => $loan->asset),
            fn (Asset $asset) => $normalizeCategory($asset->category)
        );

        $damagedAssets = $assets->filter(function (Asset $asset): bool {
            return in_array((string) $asset->condition, ['minor_damage', 'major_damage'], true)
                || $asset->status === 'maintenance';
        });

        $damagedCategorySlides = $toSlides(
            $damagedAssets,
            fn (Asset $asset) => $normalizeCategory($asset->category)
        );

        return view('dashboard.admin', [
            'totalAssets' => $assets->count(),
            'totalLaptopAssets' => $laptopAssets->count(),
            'availableLaptopAssetsCount' => $availableLaptopAssets->count(),
            'borrowedAssets' => $activeLoans->count(),
            'damagedAssets' => $damagedAssets->count(),
            'totalCategorySlides' => $totalCategorySlides,
            'totalLaptopBrandSlides' => $totalLaptopBrandSlides,
            'availableLaptopBrandSlides' => $availableLaptopBrandSlides,
            'borrowedCategorySlides' => $borrowedCategorySlides,
            'damagedCategorySlides' => $damagedCategorySlides,
            'activeLoans' => $activeLoans,
            'availableAssets' => $availableLaptopAssets,
        ]);
    }

    /**
     * @return list<string>
     */
    private function resolvePublicClassOptions(): array
    {
        $options = $this->assetOptionService->getOptions();
        $classOptions = array_values(array_filter(
            $options['classes'] ?? [],
            fn (string $class) => trim($class) !== '-'
        ));

        return $classOptions !== [] ? $classOptions : ['10 PPLG 1', '10 PPLG 2', '11 PPLG 1', '11 PPLG 2'];
    }

    /**
     * @param list<string> $classOptions
        * @return array<string, list<array{id: int, name: string, kelas: string, identity_number: ?string, phone: ?string, has_face_data: bool}>>
     */
    private function resolvePublicStudentRosterByClass(array $classOptions): array
    {
        $students = User::query()
            ->where('role', 'student')
            ->where('is_active', true)
            ->orderBy('kelas')
            ->orderBy('name')
            ->get(['id', 'name', 'kelas', 'identity_number', 'phone', 'face_encoding', 'face_registered_at', 'face_thumbnail_path']);

        $rosterByClass = [];

        foreach ($classOptions as $classOption) {
            $rosterByClass[$classOption] = [];
        }

        foreach ($students as $student) {
            $className = trim((string) $student->kelas);

            if (!array_key_exists($className, $rosterByClass)) {
                continue;
            }

            $rosterByClass[$className][] = [
                'id' => $student->id,
                'name' => $student->name,
                'kelas' => $className,
                'identity_number' => $student->identity_number,
                'phone' => $student->phone,
                'has_face_data' => filled($student->face_encoding) || filled($student->face_registered_at) || filled($student->face_thumbnail_path),
            ];
        }

        return $rosterByClass;
    }

    private function hasRegisteredFace(User $user): bool
    {
        return filled($user->face_encoding) || filled($user->face_registered_at) || filled($user->face_thumbnail_path);
    }

    /**
     * @return array<string, int|string>
     */
    private function resolveFaceCameraSettings(): array
    {
        $defaults = [
            'face_camera_preview_size' => '420',
            'face_camera_capture_size' => '512',
            'face_camera_border_radius' => '16',
            'face_camera_background' => '#111111',
            'face_camera_object_fit' => 'cover',
            'face_camera_frame_mode' => 'square',
            'face_camera_horizontal_shift' => '0',
            'face_camera_vertical_shift' => '0',
        ];

        $storedSettingValues = Setting::query()
            ->whereIn('setting_key', array_keys($defaults))
            ->pluck('setting_value', 'setting_key');

        $settings = $defaults;

        foreach ($storedSettingValues as $key => $value) {
            if (!is_string($key) || !array_key_exists($key, $settings)) {
                continue;
            }

            if ($value !== null && trim((string) $value) !== '') {
                $settings[$key] = (string) $value;
            }
        }

        $previewSize = max(280, min(720, (int) $settings['face_camera_preview_size']));
        $captureSize = max(320, min(1024, (int) $settings['face_camera_capture_size']));
        $borderRadius = max(0, min(32, (int) $settings['face_camera_border_radius']));
        $background = strtolower((string) $settings['face_camera_background']);
        $objectFit = in_array($settings['face_camera_object_fit'], ['cover', 'contain'], true)
            ? (string) $settings['face_camera_object_fit']
            : 'cover';
        $frameMode = in_array($settings['face_camera_frame_mode'], ['square', 'wide'], true)
            ? (string) $settings['face_camera_frame_mode']
            : 'square';
        $horizontalShift = max(-100, min(100, (int) $settings['face_camera_horizontal_shift']));
        $verticalShift = max(-100, min(100, (int) $settings['face_camera_vertical_shift']));

        if (!preg_match('/^#[0-9a-f]{6}$/', $background)) {
            $background = '#111111';
        }

        return [
            'face_camera_preview_size' => $previewSize,
            'face_camera_capture_size' => $captureSize,
            'face_camera_border_radius' => $borderRadius,
            'face_camera_background' => $background,
            'face_camera_object_fit' => $objectFit,
            'face_camera_frame_mode' => $frameMode,
            'face_camera_frame_ratio' => $frameMode === 'wide' ? '4 / 3' : '1 / 1',
            'face_camera_horizontal_shift' => $horizontalShift,
            'face_camera_vertical_shift' => $verticalShift,
        ];
    }

    private function storeFaceThumbnail(User $user, string $imageBase64): ?string
    {
        try {
            $imageBytes = $this->extractImageBytes($imageBase64);
            $sourceImage = imagecreatefromstring($imageBytes);

            if ($sourceImage === false) {
                return null;
            }

            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);

            if ($sourceWidth <= 0 || $sourceHeight <= 0) {
                imagedestroy($sourceImage);

                return null;
            }

            $targetWidth = min(320, $sourceWidth);
            $targetHeight = (int) max(1, round($sourceHeight * ($targetWidth / $sourceWidth)));
            $thumbnailImage = imagecreatetruecolor($targetWidth, $targetHeight);

            if ($thumbnailImage === false) {
                imagedestroy($sourceImage);

                return null;
            }

            imagecopyresampled(
                $thumbnailImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight,
            );

            ob_start();
            imagejpeg($thumbnailImage, null, 82);
            $jpegData = ob_get_clean();

            imagedestroy($sourceImage);
            imagedestroy($thumbnailImage);

            if (!is_string($jpegData) || $jpegData === '') {
                return null;
            }

            $thumbnailPath = sprintf('face-thumbnails/user-%d-%s.jpg', $user->id, now()->format('Ymd_His'));
            Storage::disk('public')->put($thumbnailPath, $jpegData);

            return $thumbnailPath;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function extractImageBytes(string $imageBase64): string
    {
        if (trim($imageBase64) === '') {
            throw new RuntimeException('image_base64 wajib diisi.');
        }

        $payload = trim($imageBase64);

        if (str_contains($payload, ',')) {
            $payload = explode(',', $payload, 2)[1];
        }

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            throw new RuntimeException('image_base64 tidak valid.');
        }

        return $decoded;
    }
}
