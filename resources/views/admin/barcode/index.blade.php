@extends('layouts.app')

@section('content')
    @php
        $printFormat = strtolower((string) request('format', 'a4'));
        $printFormat = in_array($printFormat, ['a4', 'label107'], true) ? $printFormat : 'a4';

        $a4GridVariants = [
            '2x3' => [
                'label' => '2 x 3 Grid (6 kartu)',
                'columns' => 2,
                'per_page' => 6,
                'barcode_width' => 1.25,
                'barcode_height' => 44,
                'card_min_height' => '79mm',
                'barcode_min_height' => '20mm',
                'capture_scale' => 2.15,
            ],
            '2x4' => [
                'label' => '2 x 4 Grid (8 kartu)',
                'columns' => 2,
                'per_page' => 8,
                'barcode_width' => 1.18,
                'barcode_height' => 40,
                'card_min_height' => '64mm',
                'barcode_min_height' => '19mm',
                'capture_scale' => 2.2,
            ],
            '3x4' => [
                'label' => '3 x 4 Grid (12 kartu)',
                'columns' => 3,
                'per_page' => 12,
                'barcode_width' => 1.05,
                'barcode_height' => 36,
                'card_min_height' => '57mm',
                'barcode_min_height' => '17mm',
                'capture_scale' => 2.2,
            ],
        ];

        $selectedGridKey = strtolower((string) request('grid', '3x4'));
        $selectedGridKey = array_key_exists($selectedGridKey, $a4GridVariants) ? $selectedGridKey : '3x4';
        $selectedGrid = $a4GridVariants[$selectedGridKey];
        $a4Pages = $assets->chunk($selectedGrid['per_page']);

        $selectedAsset = $assets->first();
        $selectedBarcode = $selectedAsset ? (string) ($selectedAsset->barcode ?: $selectedAsset->serial_number) : '';
    @endphp

    <div class="barcode-page-shell" id="barcodePageShell">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
            <div>
                <h4 class="mb-1">Barcode Barang</h4>
                <p class="text-muted mb-0">Pilih ukuran kertas dulu, lalu varian grid A4 muncul di panel kiri untuk mengatur kepadatan kartu barcode.</p>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white fw-semibold d-flex justify-content-between align-items-center">
                        <span>Setting Barcode</span>
                        <span class="badge text-bg-light text-primary">
                            {{ $printFormat === 'a4' ? $selectedGrid['label'] : 'Label 107' }}
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
                            </div>
                        </div>

                        @if($printFormat === 'a4')
                            <div>
                                <label class="barcode-settings-label">Varian grid A4</label>
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
                                                    {{ $gridVariant['label'] }}
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
                            {{ $printFormat === 'a4' ? 'A4 Grid menampilkan ' . $selectedGrid['per_page'] . ' kartu per halaman dan mencetak semua aset yang tersedia.' : 'Label 107 tetap memakai satu kartu barcode.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span>Container Preview</span>
                            <div class="small text-muted fw-normal">Format {{ $printFormat === 'a4' ? $selectedGrid['label'] : 'Label 107' }}</div>
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
                                                        <h2 class="barcode-sheet-title">A4 Grid</h2>
                                                        <div class="barcode-sheet-subtitle">{{ $selectedGrid['label'] }} • {{ $pageAssets->count() }} kartu pada halaman ini</div>
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
                                                                <div class="barcode-grid-code">{{ $assetBarcode }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div
                                        id="barcodePreviewSheet"
                                        class="barcode-sheet barcode-sheet--label107"
                                        data-barcode-page
                                        data-page-index="0"
                                    >
                                        <div class="barcode-sheet-topbar"></div>

                                        <div class="barcode-sheet-header">
                                            <div class="barcode-sheet-kicker">Inventory Barang</div>
                                            <h2 class="barcode-sheet-title">{{ $selectedAsset->brand }} {{ $selectedAsset->model }}</h2>
                                            <div class="barcode-sheet-subtitle">
                                                Preview Label 107
                                            </div>
                                        </div>

                                        <div class="barcode-sheet-meta">
                                            <div>
                                                <span>Serial Number</span>
                                                <strong>{{ $selectedAsset->serial_number }}</strong>
                                            </div>
                                        </div>

                                        <div class="barcode-sheet-barcode-zone">
                                            <svg
                                                id="barcodePreviewSvg"
                                                class="barcode-preview-svg"
                                                data-barcode-svg
                                                data-barcode-value="{{ $selectedBarcode }}"
                                                data-barcode-width="1.45"
                                                data-barcode-height="62"
                                                role="img"
                                                aria-label="Barcode {{ $selectedBarcode }}"
                                            ></svg>
                                            <div class="barcode-preview-code">{{ $selectedBarcode }}</div>
                                        </div>

                                        <div class="barcode-sheet-note">
                                            Pilih printer Epson L4150 pada dialog print untuk hasil fisik.
                                        </div>
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

        .barcode-sheet--label107 {
            width: 107mm;
            min-height: 50mm;
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

        .barcode-sheet--label107 .barcode-sheet-title {
            font-size: 1rem;
        }

        .barcode-sheet-subtitle {
            margin-top: 0.35rem;
            color: #64748b;
            font-size: 0.82rem;
        }

        .barcode-sheet-meta {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .barcode-sheet-meta div {
            border: 1px solid #e0e8f4;
            border-radius: 0.85rem;
            padding: 0.75rem 0.8rem;
            background: #fbfdff;
        }

        .barcode-sheet-meta span {
            display: block;
            margin-bottom: 0.15rem;
            color: #64748b;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .barcode-sheet-meta strong {
            display: block;
            color: #0f172a;
            font-size: 0.92rem;
            word-break: break-word;
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

        .barcode-grid-code {
            color: #1d2a42;
            font-size: 0.67rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-align: center;
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

        .barcode-sheet--label107 .barcode-preview-svg {
            min-height: 58px;
        }

        .barcode-preview-code {
            color: #1d2a42;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-align: center;
        }

        .barcode-sheet-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .barcode-sheet-note {
            margin-top: 0.85rem;
            color: #64748b;
            font-size: 0.82rem;
            text-align: center;
        }

        @media (max-width: 1199.98px) {
            .barcode-preview-stage {
                min-height: 520px;
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
                : 'barcode-' + safeFileName(selectedBarcode) + '-label107';

            var renderBarcodes = function () {
                if (typeof JsBarcode !== 'function') {
                    return;
                }

                barcodeSvgs.forEach(function (svg) {
                    var barcodeValue = svg.dataset.barcodeValue || '';

                    if (!barcodeValue) {
                        return;
                    }

                    var width = parseFloat(svg.dataset.barcodeWidth || (selectedFormat === 'a4' ? String(selectedGrid.barcode_width || 1.05) : '1.45'));
                    var height = parseInt(svg.dataset.barcodeHeight || (selectedFormat === 'a4' ? String(selectedGrid.barcode_height || 36) : '62'), 10);

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
                    scale: selectedFormat === 'a4' ? parseFloat(selectedGrid.capture_scale || 2.2) : 2.6,
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

                var pdfFormat = selectedFormat === 'label107' ? [107, 50] : 'a4';
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

                var pageSize = selectedFormat === 'label107' ? '107mm 50mm' : 'A4';
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
        });
    </script>
@endpush