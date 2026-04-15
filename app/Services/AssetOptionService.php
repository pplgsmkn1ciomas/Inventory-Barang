<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;

class AssetOptionService
{
    /**
     * @var array<string, string>
     */
    public const OPTION_SETTING_KEYS = [
        'categories' => 'asset_categories',
        'brands' => 'asset_brands',
        'statuses' => 'asset_statuses',
        'conditions' => 'asset_conditions',
        'roles' => 'user_roles',
        'classes' => 'user_classes',
    ];

    /**
     * @var array<string, list<string>>
     */
    public const DEFAULT_OPTIONS = [
        'categories' => ['Laptop', 'Proyektor', 'Printer', 'Aksesoris'],
        'brands' => ['Lenovo', 'Dell', 'Epson', 'Canon', 'Logitech'],
        'statuses' => ['available', 'borrowed', 'maintenance', 'lost'],
        'conditions' => ['good', 'minor_damage', 'major_damage', 'under_repair'],
        'roles' => ['admin', 'teacher', 'student'],
        'classes' => ['-', '10 PPLG 1', '10 PPLG 2', '11 PPLG 1', '11 PPLG 2'],
    ];

    /**
     * @return array<string, list<string>>
     */
    public function getOptions(): array
    {
        $storedValues = Setting::query()
            ->whereIn('setting_key', array_values(self::OPTION_SETTING_KEYS))
            ->pluck('setting_value', 'setting_key');

        $options = self::DEFAULT_OPTIONS;

        foreach (self::OPTION_SETTING_KEYS as $optionType => $settingKey) {
            $storedRaw = $storedValues->get($settingKey);

            if (!is_string($storedRaw) || trim($storedRaw) === '') {
                continue;
            }

            $decoded = json_decode($storedRaw, true);

            if (!is_array($decoded)) {
                continue;
            }

            $normalized = $this->normalizeOptionValues($decoded);

            if ($normalized !== []) {
                $options[$optionType] = $normalized;
            }
        }

        return $options;
    }

    /**
     * @param array<string, list<string>> $options
     */
    public function saveOptions(array $options): void
    {
        foreach (self::OPTION_SETTING_KEYS as $optionType => $settingKey) {
            $normalized = $this->normalizeOptionValues($options[$optionType] ?? []);
            $finalValues = $normalized !== [] ? $normalized : self::DEFAULT_OPTIONS[$optionType];

            Setting::query()->updateOrCreate(
                ['setting_key' => $settingKey],
                ['setting_value' => json_encode($finalValues)]
            );
        }
    }

    /**
     * @param array<int, mixed> $values
     * @return list<string>
     */
    private function normalizeOptionValues(array $values): array
    {
        $normalized = [];
        $seen = [];

        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $clean = trim((string) $value);

            if ($clean === '') {
                continue;
            }

            $dedupeKey = Str::lower($clean);

            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;
            $normalized[] = Str::limit($clean, 120, '');
        }

        return $normalized;
    }
}
