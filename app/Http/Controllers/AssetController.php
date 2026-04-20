<?php

namespace App\Http\Controllers;

use App\Exports\AssetsExport;
use App\Imports\AssetsImport;
use App\Models\Asset;
use App\Services\AssetOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class AssetController extends Controller
{
    public function __construct(
        private readonly AssetOptionService $assetOptionService,
    ) {
    }

    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search): void {
                $builder->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $assets = $query->latest('id')->get();
        $allAssets = Asset::query()->get();
        $optionValues = $this->assetOptionService->getOptions();

        $categoryCounts = $allAssets
            ->groupBy(fn (Asset $asset) => trim((string) ($asset->category ?: 'Tanpa Kategori')))
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $laptopBrandCounts = $allAssets
            ->filter(fn (Asset $asset) => strtolower((string) $asset->category) === 'laptop')
            ->groupBy(fn (Asset $asset) => trim((string) ($asset->brand ?: 'Tanpa Merk')))
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $allCategories = collect($this->mergeOptionValues(
            $optionValues['categories'],
            Asset::query()
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->pluck('category')
                ->all(),
        ));

        $allBrands = collect($this->mergeOptionValues(
            $optionValues['brands'],
            Asset::query()
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->pluck('brand')
                ->all(),
        ));

        $allStatuses = collect($this->mergeOptionValues(
            $optionValues['statuses'],
            Asset::query()
                ->whereNotNull('status')
                ->where('status', '!=', '')
                ->pluck('status')
                ->all(),
        ));

        $allConditions = collect($this->mergeOptionValues(
            $optionValues['conditions'],
            Asset::query()
                ->whereNotNull('condition')
                ->where('condition', '!=', '')
                ->pluck('condition')
                ->all(),
        ));

        return view('admin.assets.index', [
            'assets' => $assets,
            'allCategories' => $allCategories,
            'allBrands' => $allBrands,
            'allStatuses' => $allStatuses,
            'allConditions' => $allConditions,
            'totalAssets' => $allAssets->count(),
            'categoryCounts' => $categoryCounts,
            'laptopBrandCounts' => $laptopBrandCounts,
            'totalLaptopAssets' => $laptopBrandCounts->sum(),
            'filters' => [
                'search' => (string) $request->input('search', ''),
                'category' => (string) $request->input('category', ''),
                'status' => (string) $request->input('status', ''),
            ],
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $isTemplate = $request->boolean('template');
        $assets = $isTemplate
            ? collect()
            : Asset::query()->latest('id')->get();

        $fileNamePrefix = $isTemplate ? 'template-import-data-barang' : 'data-barang';
        $fileName = $fileNamePrefix . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AssetsExport($assets), $fileName);
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ]);

        $import = new AssetsImport($this->assetOptionService);

        try {
            Excel::import($import, $validated['excel_file']);
        } catch (Throwable) {
            return redirect()->route('admin.assets.index')
                ->with('error', 'Import Excel gagal. Pastikan format file dan header sesuai template.');
        }

        return redirect()->route('admin.assets.index')->with(
            'success',
            'Import Excel selesai. Ditambahkan: ' . $import->getCreatedCount()
                . ', Diperbarui: ' . $import->getUpdatedCount()
                . ', Dilewati: ' . $import->getSkippedCount() . '.'
        );
    }

    public function store(Request $request)
    {
        $optionValues = $this->assetOptionService->getOptions();

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:120', Rule::in($optionValues['brands'])],
            'model' => ['required', 'string', 'max:120'],
            'serial_number' => ['required', 'string', 'max:120', 'unique:assets,serial_number'],
            'category' => ['required', 'string', 'max:120', Rule::in($optionValues['categories'])],
            'barcode' => ['nullable', 'string', 'max:120', 'unique:assets,barcode'],
            'status' => ['required', 'string', 'max:120', Rule::in($optionValues['statuses'])],
            'condition' => ['required', 'string', 'max:120', Rule::in($optionValues['conditions'])],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['barcode'] = $validated['barcode'] ?? $validated['serial_number'];
        $validated['qr_code_hash'] = hash('sha256', $validated['serial_number']);

        Asset::create($validated);

        return redirect()->route('admin.assets.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, Asset $asset)
    {
        $optionValues = $this->assetOptionService->getOptions();
        $categoryOptions = $this->mergeOptionValues($optionValues['categories'], [(string) $asset->category]);
        $brandOptions = $this->mergeOptionValues($optionValues['brands'], [(string) $asset->brand]);
        $statusOptions = $this->mergeOptionValues($optionValues['statuses'], [(string) $asset->status]);
        $conditionOptions = $this->mergeOptionValues($optionValues['conditions'], [(string) $asset->condition]);

        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:120', Rule::in($brandOptions)],
            'model' => ['required', 'string', 'max:120'],
            'serial_number' => ['required', 'string', 'max:120', Rule::unique('assets', 'serial_number')->ignore($asset->id)],
            'category' => ['required', 'string', 'max:120', Rule::in($categoryOptions)],
            'barcode' => ['nullable', 'string', 'max:120', Rule::unique('assets', 'barcode')->ignore($asset->id)],
            'status' => ['required', 'string', 'max:120', Rule::in($statusOptions)],
            'condition' => ['required', 'string', 'max:120', Rule::in($conditionOptions)],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['barcode'] = $validated['barcode'] ?? $validated['serial_number'];
        $validated['qr_code_hash'] = hash('sha256', $validated['serial_number']);

        $asset->update($validated);

        return redirect()->route('admin.assets.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->loans()->exists()) {
            return redirect()->route('admin.assets.index')
                ->with('error', 'Barang tidak bisa dihapus karena memiliki riwayat peminjaman.');
        }

        $asset->delete();

        return redirect()->route('admin.assets.index')->with('success', 'Barang berhasil dihapus.');
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
