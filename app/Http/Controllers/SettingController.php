<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AssetOptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
    ];

    public function index(): View
    {
        $settingValues = $this->getSettingValues();

        return view('admin.settings.index', [
            'runningText' => $settingValues[self::RUNNING_TEXT_KEY],
            'settingValues' => $settingValues,
            'fontFamilyOptions' => self::FONT_FAMILY_OPTIONS,
            'assetOptions' => $this->assetOptionService->getOptions(),
        ]);
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
        ]);

        $this->saveSettings([
            'public_header_title' => trim((string) $validated['public_header_title']),
            'public_header_subtitle' => trim((string) $validated['public_header_subtitle']),
            'public_borrow_button_label' => trim((string) $validated['public_borrow_button_label']),
            'public_return_button_label' => trim((string) $validated['public_return_button_label']),
        ]);

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
        ]);

        return redirect()->route('admin.settings.index', ['tab' => 'menu-c'])
            ->with('success', 'Pengaturan Menu C berhasil diperbarui.');
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
