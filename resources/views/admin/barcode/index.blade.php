@extends('layouts.app')

@section('content')
    @php
        $printFormat = strtolower((string) request('format', 'a4'));
        $printFormat = in_array($printFormat, ['a4', 'label107', 'label103'], true) ? $printFormat : 'a4';

        $a4GridVariants = [
            'ringkas' => [
                'label' => 'Ringkas',
                'description' => '2 x 3 Grid (6 kartu)',
                'columns' => 2,
                'per_page' => 6,
                'barcode_width' => 1.25,
                'barcode_height' => 44,
                'card_min_height' => '79mm',
                'barcode_min_height' => '20mm',
                'capture_scale' => 2.15,
            ],
            'standar' => [
                'label' => 'Standar',
                'description' => '2 x 4 Grid (8 kartu)',
                'columns' => 2,
                'per_page' => 8,
                'barcode_width' => 1.18,
                'barcode_height' => 40,
                'card_min_height' => '64mm',
                'barcode_min_height' => '19mm',
                'capture_scale' => 2.2,
            ],
            'rapat' => [
                'label' => 'Rapat',
                'description' => '3 x 4 Grid (12 kartu)',
                'columns' => 3,
                'per_page' => 12,
                'barcode_width' => 1.05,
                'barcode_height' => 36,
                'card_min_height' => '57mm',
                'barcode_min_height' => '17mm',
                'capture_scale' => 2.2,
            ],
        ];

        $labelVariants = [
            'label107' => [
                'button_label' => 'Label 107',
                'label' => 'Label T&J 107',
                'dimensions' => '107 x 50 mm',
                'paper_width' => 107,
                'paper_height' => 50,
                'sheet_width' => '107mm',
                'sheet_height' => '50mm',
                'barcode_width' => 1.45,
                'barcode_height' => 62,
                'capture_scale' => 2.6,
                'file_suffix' => 'label-tj-107',
            ],
            'label103' => [
                'button_label' => 'Label 103',
                'label' => 'Label T&J 103',
                'dimensions' => '103 x 50 mm',
                'paper_width' => 103,
                'paper_height' => 50,
                'sheet_width' => '103mm',
                'sheet_height' => '50mm',
                'barcode_width' => 1.38,
                'barcode_height' => 62,
                'capture_scale' => 2.6,
                'file_suffix' => 'label-tj-103',
            ],
        ];

        $gridAliases = [
            '2x3' => 'ringkas',
            '2x4' => 'standar',
            '3x4' => 'rapat',
        ];

        $selectedGridKey = strtolower((string) request('grid', 'rapat'));
        $selectedGridKey = $gridAliases[$selectedGridKey] ?? $selectedGridKey;
        $selectedGridKey = array_key_exists($selectedGridKey, $a4GridVariants) ? $selectedGridKey : 'rapat';
        $selectedGrid = $a4GridVariants[$selectedGridKey];
        $selectedLabelKey = array_key_exists($printFormat, $labelVariants) ? $printFormat : 'label107';
        $selectedLabel = $labelVariants[$selectedLabelKey];
        $a4Pages = $assets->chunk($selectedGrid['per_page']);
        $labelChunks = $assets->chunk(30);
        $printDate = now()->locale('id')->translatedFormat('d F Y, H:i');

        $selectedAsset = $assets->first();
        $selectedBarcode = $selectedAsset ? (string) ($selectedAsset->barcode ?: $selectedAsset->serial_number) : '';
    @endphp

    <div class="barcode-page-shell" id="barcodePageShell">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
            <div>
                <h4 class="mb-1">Barcode Barang</h4>
                <p class="text-muted mb-0">Pilih ukuran kertas dulu, lalu preset grid A4 muncul di panel kiri untuk mengatur kepadatan kartu barcode.</p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white fw-semibold d-flex justify-content-between align-items-center">
                        <span>Setting Barcode</span>
                        <span class="badge text-bg-light text-primary">
                            {{ $printFormat === 'a4' ? $selectedGrid['label'] : $selectedLabel['label'] }}
                        </span>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <div>
                            <label class="barcode-settings-label">Ukuran kertas</label>
                            <div class="d-flex flex-wrap gap-2">
                                <a
                                    href="{{ request()->fullUrlWithQuery(['format' => 'a4']) }}"
                                    class="btn {{ $printFormat === 'a4' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-4"
                                >
                                    A4 Grid
                                </a>
                                <a
                                    href="{{ request()->fullUrlWithQuery(['format' => 'label107']) }}"
                                    class="btn {{ $printFormat === 'label107' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-4"
                                >
                                    Label 107
                                </a>
                                <a
                                    href="{{ request()->fullUrlWithQuery(['format' => 'label103']) }}"
                                    class="btn {{ $printFormat === 'label103' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-4"
                                >
                                    Label 103
                                </a>
                            </div>
                        </div>

                        @if($printFormat === 'a4')
                            <div>
                                <label class="barcode-settings-label">Preset grid A4</label>
                                <div class="dropdown barcode-download-group w-100">
                                    <button class="btn btn-outline-primary dropdown-toggle w-100 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span><i class="fa-solid fa-border-all me-2"></i>{{ $selectedGrid['label'] }}</span>
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        @foreach($a4GridVariants as $gridKey => $gridVariant)
                                            <li>
                                                <a
                                                    class="dropdown-item {{ $selectedGridKey === $gridKey ? 'active' : '' }}"
                                                    href="{{ request()->fullUrlWithQuery(['grid' => $gridKey]) }}"
                                                >
                                                    {{ $gridVariant['label'] }} <span class="text-muted">({{ $gridVariant['description'] }})</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="barcode-settings-label">Format download gambar</label>
                            <div class="dropdown barcode-download-group w-100">
                                <button class="btn btn-outline-success dropdown-toggle w-100 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span><i class="fa-solid fa-image me-2"></i>Pilih format</span>
                                </button>
                                <ul class="dropdown-menu w-100">
                                    <li>
                                        <button class="dropdown-item" type="button" data-image-format="png">PNG</button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" type="button" data-image-format="jpeg">JPEG</button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" id="downloadPdfButton">
                                <i class="fa-solid fa-file-pdf me-2"></i>Download PDF
                            </button>
                            <button type="button" class="btn btn-primary" id="printBarcodeButton">
                                <i class="fa-solid fa-print me-2"></i>Print Epson L4150
                            </button>
                        </div>

                        <div class="small text-muted">
                            @if($printFormat === 'a4')
                                {{ $selectedGrid['label'] }} menampilkan {{ $selectedGrid['per_page'] }} kartu per halaman dan mencetak semua aset yang tersedia.
                            @else
                                {{ $selectedLabel['label'] }} ({{ $selectedLabel['dimensions'] }}) — Semua aset dicetak 30 label per halaman A4.
                            @endif
                        </div>

                        @if($printFormat !== 'a4')
                        {{-- Dropdown info spesifikasi kertas label --}}
                        <div>
                            <div class="accordion accordion-flush" id="labelInfoAccordion">
                                <div class="accordion-item border rounded">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 px-3 small fw-semibold" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#labelInfoBody"
                                                aria-expanded="false" aria-controls="labelInfoBody">
                                            <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                                            Spesifikasi Kertas
                                        </button>
                                    </h2>
                                    <div id="labelInfoBody" class="accordion-collapse collapse">
                                        <div class="accordion-body py-2 px-3">
                                            <table class="table table-sm table-borderless small mb-0">
                                                <tbody>
                                                    <tr><td class="text-muted ps-0 py-1">Merek</td><td class="fw-semibold">Tom &amp; Jerry (T&amp;J)</td></tr>
                                                    <tr><td class="text-muted ps-0 py-1">Kertas induk</td><td class="fw-semibold">A4 (210×297 mm)</td></tr>
                                                    <tr><td class="text-muted ps-0 py-1">Ukuran label</td><td class="fw-semibold">50 mm × 18 mm</td></tr>
                                                    <tr><td class="text-muted ps-0 py-1">Susunan</td><td class="fw-semibold">3 kolom × 10 baris</td></tr>
                                                    <tr><td class="text-muted ps-0 py-1">Label / hal</td><td class="fw-semibold">30 label</td></tr>
                                                    <tr><td class="text-muted ps-0 py-1">Margin H/V</td><td class="fw-semibold">30 mm / 58.5 mm</td></tr>
                                                    <tr><td class="text-muted ps-0 py-1">Isi per pak</td><td class="fw-semibold">10 lembar = 300 label</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($printFormat !== 'a4')
                        {{-- Kalibrasi Posisi Cetak --}}
                        <div id="labelCalibrationPanel">
                            <div class="barcode-settings-label mt-1">
                                <i class="fa-solid fa-sliders me-1"></i> Kalibrasi Posisi Cetak
                            </div>

                            {{-- Offset X --}}
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Geser Horizontal</span>
                                    <span class="badge text-bg-secondary" id="calXDisplay">0 mm</span>
                                </div>
                                <input type="range" class="form-range" id="calOffsetX"
                                       min="-15" max="15" step="0.5" value="0">
                                <div class="d-flex gap-1 mt-1">
                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill cal-step-btn"
                                            data-axis="x" data-dir="-1">
                                        <i class="fa-solid fa-arrow-left"></i> Kiri
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill cal-step-btn"
                                            data-axis="x" data-dir="1">
                                        Kanan <i class="fa-solid fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Offset Y --}}
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Geser Vertikal</span>
                                    <span class="badge text-bg-secondary" id="calYDisplay">0 mm</span>
                                </div>
                                <input type="range" class="form-range" id="calOffsetY"
                                       min="-15" max="15" step="0.5" value="0">
                                <div class="d-flex gap-1 mt-1">
                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill cal-step-btn"
                                            data-axis="y" data-dir="-1">
                                        <i class="fa-solid fa-arrow-up"></i> Atas
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill cal-step-btn"
                                            data-axis="y" data-dir="1">
                                        Bawah <i class="fa-solid fa-arrow-down"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Skala --}}
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Skala Grid</span>
                                    <span class="badge text-bg-secondary" id="calScaleDisplay">100%</span>
                                </div>
                                <input type="range" class="form-range" id="calScale"
                                       min="90" max="110" step="0.5" value="100">
                            </div>

                            <button type="button" class="btn btn-outline-danger btn-sm w-100" id="calResetBtn">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reset Posisi
                            </button>
                            <div class="cal-hint mt-2">
                                Penyesuaian berlaku di Preview <strong>dan</strong> saat Cetak/PDF.
                                Gunakan langkah kecil 0.5 mm untuk akurasi tinggi.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span>Container Preview</span>
                            <div class="small text-muted fw-normal">Format {{ $printFormat === 'a4' ? $selectedGrid['label'] : $selectedLabel['label'] }}</div>
                        </div>
                        <span class="badge text-bg-primary">{{ $assets->count() }} aset</span>
                    </div>
                    <div class="card-body">
                        <div class="barcode-preview-stage">
                            @if($assets->isNotEmpty())
                                @if($printFormat === 'a4')
                                    <div class="barcode-sheet-stack">
                                        @foreach($a4Pages as $pageIndex => $pageAssets)
                                            <div
                                                class="barcode-sheet barcode-sheet--a4"
                                                data-barcode-page
                                                data-page-index="{{ $pageIndex }}"
                                                data-page-total="{{ $a4Pages->count() }}"
                                                style="--barcode-a4-grid-columns: {{ $selectedGrid['columns'] }}; --barcode-a4-grid-card-min-height: {{ $selectedGrid['card_min_height'] }}; --barcode-a4-grid-barcode-min-height: {{ $selectedGrid['barcode_min_height'] }};"
                                            >
                                                <div class="barcode-sheet-topbar"></div>
                                                <div class="barcode-sheet-header barcode-sheet-header--grid">
                                                    <div>
                                                        <div class="barcode-sheet-kicker">Inventory Barang</div>
                                                        <h2 class="barcode-sheet-title">A4 Grid {{ $selectedGrid['label'] }}</h2>
                                                        <div class="barcode-sheet-subtitle">{{ $selectedGrid['description'] }} • {{ $pageAssets->count() }} kartu pada halaman ini</div>
                                                    </div>
                                                    <div class="barcode-sheet-page-chip">Halaman {{ $pageIndex + 1 }}/{{ $a4Pages->count() }}</div>
                                                </div>

                                                <div class="barcode-a4-grid">
                                                    @foreach($pageAssets as $asset)
                                                        @php
                                                            $assetBarcode = (string) ($asset->barcode ?: $asset->serial_number);
                                                            $barcodeSvgId = 'barcode-grid-svg-' . $asset->id . '-' . $pageIndex;
                                                        @endphp
                                                        <div class="barcode-grid-card">
                                                            <div class="barcode-grid-card-meta">
                                                                <div class="barcode-grid-card-brand">{{ $asset->brand }} {{ $asset->model }}</div>
                                                                <div class="barcode-grid-card-serial">{{ $asset->serial_number }}</div>
                                                            </div>

                                                            <div class="barcode-grid-card-barcode">
                                                                <svg
                                                                    id="{{ $barcodeSvgId }}"
                                                                    class="barcode-grid-svg"
                                                                    data-barcode-svg
                                                                    data-barcode-value="{{ $assetBarcode }}"
                                                                    data-barcode-width="{{ $selectedGrid['barcode_width'] }}"
                                                                    data-barcode-height="{{ $selectedGrid['barcode_height'] }}"
                                                                    role="img"
                                                                    aria-label="Barcode {{ $assetBarcode }}"
                                                                ></svg>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{-- Full 30-label-per-page grid for label formats --}}
                                    <div class="barcode-sheet-stack">
                                        @foreach($labelChunks as $lblPageIndex => $lblPageAssets)
                                            <div
                                                class="barcode-sheet barcode-sheet--a4 barcode-label-sheet"
                                                data-barcode-page
                                                data-page-index="{{ $lblPageIndex }}"
                                                data-page-total="{{ $labelChunks->count() }}"
                                            >
                                                <div class="barcode-sheet-topbar"></div>
                                                <div class="barcode-sheet-header barcode-sheet-header--grid">
                                                    <div>
                                                        <div class="barcode-sheet-kicker">Inventory Barang</div>
                                                        <h2 class="barcode-sheet-title">{{ $selectedLabel['label'] }}</h2>
                                                        <div class="barcode-sheet-subtitle">A4 · 3×10 · {{ $lblPageAssets->count() }} label pada halaman ini</div>
                                                    </div>
                                                    <div class="barcode-sheet-page-chip">
                                                        Hal {{ $lblPageIndex + 1 }}/{{ $labelChunks->count() }}
                                                    </div>
                                                </div>

                                                <div class="barcode-label-grid">
                                                    @foreach($lblPageAssets as $lblAsset)
                                                        @php $lblBarcode = (string)($lblAsset->barcode ?: $lblAsset->serial_number); @endphp
                                                        <div class="barcode-label-cell">
                                                            <div class="barcode-label-category">{{ $lblAsset->category }}</div>
                                                            <div class="barcode-label-name">{{ $lblAsset->brand }} {{ $lblAsset->model }}</div>
                                                            <div class="barcode-label-barcode">
                                                                <svg
                                                                    class="barcode-label-svg"
                                                                    data-barcode-svg
                                                                    data-barcode-value="{{ $lblBarcode }}"
                                                                    data-barcode-width="1"
                                                                    data-barcode-height="26"
                                                                    role="img"
                                                                    aria-label="Barcode {{ $lblBarcode }}"
                                                                ></svg>
                                                            </div>
                                                            <div class="barcode-label-date">Cetak: {{ $printDate }}</div>
                                                            <div class="barcode-label-dept">Pengembangan Perangkat Lunak &amp; Gim</div>
                                                        </div>
                                                    @endforeach
                                                    @for($i = $lblPageAssets->count(); $i < 30; $i++)
                                                        <div class="barcode-label-cell barcode-label-cell--empty"></div>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="barcode-empty-state w-100">
                                    <div class="barcode-empty-icon"><i class="fa-solid fa-barcode"></i></div>
                                    <h5 class="mb-2">Belum ada aset</h5>
                                    <p class="text-muted mb-0">Tambahkan data aset terlebih dahulu agar barcode bisa dipreview dan dicetak.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .barcode-page-shell {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding-bottom: 1rem;
        }

        .barcode-settings-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .barcode-download-group .dropdown-menu {
            min-width: 100%;
        }

        .barcode-preview-stage {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            min-height: 650px;
            padding: 1rem;
            overflow: auto;
            border: 1px dashed #c9d6ea;
            border-radius: 1.25rem;
            background: linear-gradient(180deg, rgba(13, 110, 253, 0.08), rgba(255, 255, 255, 0.75));
        }

        .barcode-sheet-stack {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            align-items: center;
            width: 100%;
        }

        .barcode-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 520px;
            padding: 2rem;
            border: 1px dashed #d4dcec;
            border-radius: 1rem;
            background: #fff;
            text-align: center;
        }

        .barcode-empty-icon {
            width: 72px;
            height: 72px;
            margin-bottom: 1rem;
            border-radius: 999px;
            background: #e7f0ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .barcode-sheet {
            position: relative;
            overflow: hidden;
            border: 1px solid #d4ddec;
            border-radius: 1rem;
            color: #1f2937;
            background: #fff;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.16);
        }

        .barcode-sheet::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #0d6efd, #2d7ff9, #6bb4ff);
        }

        .barcode-sheet--a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm 9mm 9mm;
        }

        .barcode-sheet--label {
            width: var(--barcode-label-sheet-width, 107mm);
            min-height: var(--barcode-label-sheet-height, 50mm);
            padding: 4.5mm 5mm 4mm;
        }

        .barcode-sheet-topbar {
            height: 2px;
            background: rgba(13, 110, 253, 0.12);
        }

        .barcode-sheet-header {
            margin-bottom: 1rem;
            text-align: center;
        }

        .barcode-sheet-header--label {
            margin-bottom: 0.65rem;
        }

        .barcode-sheet-header--grid {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            text-align: left;
            margin-bottom: 0.85rem;
        }

        .barcode-sheet-page-chip {
            flex-shrink: 0;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: #eaf2ff;
            color: #0d6efd;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .barcode-sheet-kicker {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.6rem;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            background: #eaf2ff;
            color: #0d6efd;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .barcode-sheet-title {
            margin: 0;
            color: #0f172a;
            font-weight: 800;
            line-height: 1.15;
        }

        .barcode-sheet--a4 .barcode-sheet-title {
            font-size: 1.15rem;
        }

        .barcode-sheet--label .barcode-sheet-title {
            font-size: 1rem;
        }

        .barcode-a4-grid {
            display: grid;
            grid-template-columns: repeat(var(--barcode-a4-grid-columns, 3), minmax(0, 1fr));
            gap: 2.5mm;
        }

        .barcode-grid-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2mm;
            min-height: var(--barcode-a4-grid-card-min-height, 57mm);
            padding: 2.8mm;
            border: 1px solid #dbe6f3;
            border-radius: 0.8rem;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
            break-inside: avoid;
        }

        .barcode-grid-card-meta {
            min-width: 0;
        }

        .barcode-grid-card-brand {
            color: #0f172a;
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1.08;
        }

        .barcode-grid-card-serial {
            margin-top: 0.15rem;
            color: #64748b;
            font-size: 0.68rem;
        }

        .barcode-grid-card-barcode {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.2mm;
            padding: 1.5mm 1.8mm;
            border: 1px dashed #d4e0f0;
            border-radius: 0.6rem;
            background: #fff;
        }

        .barcode-grid-svg {
            width: 100%;
            height: auto;
            min-height: var(--barcode-a4-grid-barcode-min-height, 17mm);
        }

        .barcode-sheet-barcode-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 1rem;
            border: 1px dashed #cbd9ee;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
        }

        .barcode-preview-svg {
            width: 100%;
            height: auto;
            min-height: 56px;
        }

        .barcode-sheet--label .barcode-preview-svg {
            min-height: 58px;
        }

        .barcode-sheet-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        @media (max-width: 1199.98px) {
            .barcode-preview-stage {
                min-height: 520px;
            }
        }

        /* ── Calibration Panel ──────────────────────────────────── */
        #labelCalibrationPanel {
            padding-top: .25rem;
            border-top: 1px dashed #d0dcea;
            margin-top: .25rem;
        }
        .cal-hint {
            font-size: .68rem;
            color: #6d7f9d;
            line-height: 1.35;
        }
        #calOffsetX, #calOffsetY, #calScale {
            accent-color: #0d6efd;
        }

        /* ── Label 107 / 103 grid ───────────────────────────────── */
        .barcode-label-sheet {
            /* screen: add inner padding so the grid has breathing room */
            padding-left: 8mm !important;
            padding-right: 8mm !important;
        }

        .barcode-label-grid {
            display: grid;
            grid-template-columns: repeat(3, 50mm);
            grid-template-rows: repeat(10, 18mm);
            width: 150mm;
            height: 180mm;
            gap: 0;
            margin: 0 auto;
        }

        .barcode-label-cell {
            width: 50mm;
            height: 18mm;
            border: 0.3px solid #bbb;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 1mm 1.5mm;
            overflow: hidden;
            position: relative;
            box-sizing: border-box;
        }

        .barcode-label-cell--empty::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                -45deg,
                transparent, transparent 4px,
                rgba(0,0,0,.03) 4px, rgba(0,0,0,.03) 5px
            );
        }

        .barcode-label-category {
            font-size: 5pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: .3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.1;
        }
        .barcode-label-name {
            font-size: 7pt;
            font-weight: 700;
            color: #111;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        .barcode-label-barcode {
            display: block;
            width: 100%;
            height: 7mm;
            margin: 1mm 0;
            overflow: hidden;
        }
        .barcode-label-svg {
            width: 100% !important;
            height: 7mm !important;
            display: block;
        }
        .barcode-label-date {
            font-size: 4.5pt;
            color: #555;
            letter-spacing: .1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        .barcode-label-dept {
            font-size: 4.5pt;
            font-weight: 700;
            color: #111;
            text-transform: uppercase;
            letter-spacing: .2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        /* ── Print rules for label format ───────────────────────── */
        @media print {
            .barcode-label-sheet {
                width: 210mm !important;
                height: 297mm !important;
                padding: 58.5mm 30mm !important;
            }
            .barcode-label-grid {
                margin: 0 !important;
                width: 150mm !important;
                height: 180mm !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var selectedFormat = @json($printFormat);
            var selectedBarcode = @json($selectedBarcode);
            var selectedGrid = @json($selectedGrid);
            var selectedGridKey = @json($selectedGridKey);
            var selectedLabel = @json($selectedLabel);
            var selectedLabelKey = @json($selectedLabelKey);
            var downloadPdfButton = document.getElementById('downloadPdfButton');
            var printButton = document.getElementById('printBarcodeButton');
            var imageButtons = document.querySelectorAll('[data-image-format]');
            var barcodePages = Array.from(document.querySelectorAll('[data-barcode-page]'));
            var barcodeSvgs = document.querySelectorAll('[data-barcode-svg]');

            var safeFileName = function (value) {
                var normalized = (value || '')
                    .toString()
                    .trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                return normalized || 'barcode';
            };

            var filePrefix = selectedFormat === 'a4'
                ? 'barcode-a4-grid-' + safeFileName(selectedGridKey)
                : 'barcode-' + safeFileName(selectedBarcode) + '-' + safeFileName(selectedLabel.file_suffix || selectedLabelKey);

            var renderBarcodes = function () {
                if (typeof JsBarcode !== 'function') {
                    return;
                }

                barcodeSvgs.forEach(function (svg) {
                    var barcodeValue = svg.dataset.barcodeValue || '';

                    if (!barcodeValue) {
                        return;
                    }

                    var width = parseFloat(svg.dataset.barcodeWidth || (selectedFormat === 'a4' ? String(selectedGrid.barcode_width || 1.05) : String(selectedLabel.barcode_width || 1.45)));
                    var height = parseInt(svg.dataset.barcodeHeight || (selectedFormat === 'a4' ? String(selectedGrid.barcode_height || 36) : String(selectedLabel.barcode_height || 62)), 10);

                    svg.innerHTML = '';

                    try {
                        JsBarcode(svg, barcodeValue, {
                            format: 'CODE128',
                            displayValue: false,
                            margin: 0,
                            lineColor: '#12233f',
                            background: 'transparent',
                            width: width,
                            height: height,
                        });
                    } catch (error) {
                        svg.innerHTML = '';
                    }
                });
            };

            var capturePageCanvas = async function (pageElement) {
                if (!pageElement || typeof html2canvas !== 'function') {
                    alert('Preview barcode belum siap untuk diunduh.');
                    return null;
                }

                return await html2canvas(pageElement, {
                    backgroundColor: '#ffffff',
                    scale: selectedFormat === 'a4' ? parseFloat(selectedGrid.capture_scale || 2.2) : parseFloat(selectedLabel.capture_scale || 2.6),
                    useCORS: true,
                    logging: false,
                });
            };

            var getPageCanvases = async function () {
                var canvases = [];

                for (var index = 0; index < barcodePages.length; index += 1) {
                    // Capture each sheet separately so PDF and print keep proper page breaks.
                    // eslint-disable-next-line no-await-in-loop
                    var canvas = await capturePageCanvas(barcodePages[index]);
                    if (canvas) {
                        canvases.push(canvas);
                    }
                }

                return canvases;
            };

            var downloadBlob = function (blob, fileName) {
                var link = document.createElement('a');
                var url = URL.createObjectURL(blob);

                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            };

            var combineCanvases = function (canvases) {
                if (!canvases.length) {
                    return null;
                }

                if (canvases.length === 1) {
                    return canvases[0];
                }

                var totalWidth = 0;
                var totalHeight = 0;

                canvases.forEach(function (canvas) {
                    totalWidth = Math.max(totalWidth, canvas.width);
                    totalHeight += canvas.height;
                });

                var composite = document.createElement('canvas');
                composite.width = totalWidth;
                composite.height = totalHeight;

                var context = composite.getContext('2d');
                var offsetY = 0;

                canvases.forEach(function (canvas) {
                    context.drawImage(canvas, 0, offsetY);
                    offsetY += canvas.height;
                });

                return composite;
            };

            var downloadImage = async function (mimeType) {
                var canvases = await getPageCanvases();

                if (!canvases.length) {
                    return;
                }

                var composite = combineCanvases(canvases);
                if (!composite) {
                    return;
                }

                var extension = mimeType === 'image/jpeg' ? 'jpg' : 'png';
                var quality = mimeType === 'image/jpeg' ? 0.95 : 1;
                var fileName = filePrefix + '.' + extension;

                if (typeof composite.toBlob === 'function') {
                    composite.toBlob(function (blob) {
                        if (!blob) {
                            alert('Gagal membuat file gambar barcode.');
                            return;
                        }

                        downloadBlob(blob, fileName);
                    }, mimeType, quality);

                    return;
                }

                var link = document.createElement('a');
                link.href = composite.toDataURL(mimeType, quality);
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
            };

            var downloadPdf = async function () {
                var canvases = await getPageCanvases();

                if (!canvases.length) {
                    return;
                }

                var jspdfApi = window.jspdf || {};
                var jsPDF = jspdfApi.jsPDF;

                if (typeof jsPDF !== 'function') {
                    alert('Library PDF belum termuat.');
                    return;
                }

                var pdfFormat = selectedFormat === 'a4' ? 'a4' : [selectedLabel.paper_width || 107, selectedLabel.paper_height || 50];
                var pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: pdfFormat,
                    compress: true,
                });

                canvases.forEach(function (canvas, index) {
                    if (index > 0) {
                        pdf.addPage();
                    }

                    var pageWidth = pdf.internal.pageSize.getWidth();
                    var pageHeight = pdf.internal.pageSize.getHeight();
                    pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, pageWidth, pageHeight);
                });

                pdf.save(filePrefix + '.pdf');
            };

            var openPrintWindow = async function () {
                var canvases = await getPageCanvases();

                if (!canvases.length) {
                    return;
                }

                var pageSize = selectedFormat === 'a4' ? 'A4' : (selectedLabel.paper_width || 107) + 'mm ' + (selectedLabel.paper_height || 50) + 'mm';
                var printWindow = window.open('', '_blank', 'width=1280,height=900');

                if (!printWindow) {
                    alert('Popup print diblokir browser.');
                    return;
                }

                var printPages = canvases.map(function (canvas) {
                    return '<div class="print-page"><img src="' + canvas.toDataURL('image/png') + '" alt="Barcode Preview"></div>';
                }).join('');

                printWindow.document.write(
                    '<!doctype html><html><head><title>Print Barcode</title>' +
                    '<style>' +
                    '@page { size: ' + pageSize + '; margin: 0; }' +
                    'html, body { margin: 0; padding: 0; background: #ffffff; }' +
                    '.print-page { page-break-after: always; width: 100%; height: 100vh; }' +
                    '.print-page:last-child { page-break-after: auto; }' +
                    'img { width: 100%; height: 100%; object-fit: fill; display: block; }' +
                    '</style></head><body>' +
                    printPages +
                    '</body></html>'
                );
                printWindow.document.close();

                setTimeout(function () {
                    printWindow.focus();
                    printWindow.print();
                    setTimeout(function () {
                        printWindow.close();
                    }, 500);
                }, 250);
            };

            renderBarcodes();

            if (downloadPdfButton) {
                downloadPdfButton.addEventListener('click', function () {
                    downloadPdf();
                });
            }

            imageButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var imageFormat = button.dataset.imageFormat === 'jpeg' ? 'image/jpeg' : 'image/png';
                    downloadImage(imageFormat);
                });
            });

            if (printButton) {
                printButton.addEventListener('click', function () {
                    openPrintWindow();
                });
            }

            /* ── Kalibrasi Posisi Cetak ─────────────────────────── */
            (function () {
                var calOffsetX   = document.getElementById('calOffsetX');
                var calOffsetY   = document.getElementById('calOffsetY');
                var calScale     = document.getElementById('calScale');
                var calXDisplay  = document.getElementById('calXDisplay');
                var calYDisplay  = document.getElementById('calYDisplay');
                var calScaleDisp = document.getElementById('calScaleDisplay');
                var calResetBtn  = document.getElementById('calResetBtn');
                var stepBtns     = document.querySelectorAll('.cal-step-btn');

                if (!calOffsetX || !calOffsetY || !calScale) return; // not a label format

                /* Inject a dynamic <style> tag that carries the transform into @media print */
                var calPrintStyle = document.createElement('style');
                calPrintStyle.id  = 'calPrintStyle';
                document.head.appendChild(calPrintStyle);

                var applyCalibration = function () {
                    var x     = parseFloat(calOffsetX.value);
                    var y     = parseFloat(calOffsetY.value);
                    var scale = parseFloat(calScale.value) / 100;

                    /* Format display badges */
                    calXDisplay.textContent  = (x >= 0 ? '+' : '') + x.toFixed(1) + ' mm';
                    calYDisplay.textContent  = (y >= 0 ? '+' : '') + y.toFixed(1) + ' mm';
                    calScaleDisp.textContent = (scale * 100).toFixed(1) + '%';

                    /* Colour badge based on offset direction */
                    calXDisplay.className  = 'badge ' + (x !== 0 ? 'text-bg-primary' : 'text-bg-secondary');
                    calYDisplay.className  = 'badge ' + (y !== 0 ? 'text-bg-primary' : 'text-bg-secondary');
                    calScaleDisp.className = 'badge ' + (scale !== 1 ? 'text-bg-warning text-dark' : 'text-bg-secondary');

                    var transformVal = 'translate(' + x + 'mm, ' + y + 'mm) scale(' + scale + ')';

                    /* Apply to all label grids on screen */
                    document.querySelectorAll('.barcode-label-grid').forEach(function (grid) {
                        grid.style.transform       = transformVal;
                        grid.style.transformOrigin = 'top left';
                    });

                    /* Inject print CSS (overrides default margin:0 auto) */
                    calPrintStyle.textContent =
                        '@media print {' +
                        '  .barcode-label-grid {' +
                        '    transform: translate(' + x + 'mm, ' + y + 'mm) scale(' + scale + ') !important;' +
                        '    transform-origin: top left !important;' +
                        '    margin: 0 !important;' +
                        '  }' +
                        '}';
                };

                /* Slider input events */
                calOffsetX.addEventListener('input', applyCalibration);
                calOffsetY.addEventListener('input', applyCalibration);
                calScale.addEventListener('input', applyCalibration);

                /* Step buttons: nudge by 0.5 mm */
                stepBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var axis = btn.dataset.axis;
                        var dir  = parseFloat(btn.dataset.dir);
                        var step = 0.5;

                        if (axis === 'x') {
                            var newX = Math.max(-15, Math.min(15, parseFloat(calOffsetX.value) + dir * step));
                            calOffsetX.value = newX;
                        } else {
                            var newY = Math.max(-15, Math.min(15, parseFloat(calOffsetY.value) + dir * step));
                            calOffsetY.value = newY;
                        }
                        applyCalibration();
                    });
                });

                /* Reset */
                if (calResetBtn) {
                    calResetBtn.addEventListener('click', function () {
                        calOffsetX.value = '0';
                        calOffsetY.value = '0';
                        calScale.value   = '100';
                        applyCalibration();
                    });
                }

                /* Run once on load */
                applyCalibration();
            })();

        });
    </script>
@endpush