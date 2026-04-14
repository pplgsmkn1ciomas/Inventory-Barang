@extends('layouts.app')

@section('content')
    @php
        $printDate = now()->locale('id')->translatedFormat('d F Y, H:i');
        $chunks    = $assets->chunk(30);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Cetak Label T&amp;J No.107</h4>
            <p class="text-muted mb-0">Format kertas label 50 mm × 18 mm — 3×10 = 30 label per halaman (A4).</p>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── Panel Kiri: Info & Aksi ─────────────────────────── --}}
        <div class="col-xl-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white fw-semibold d-flex justify-content-between align-items-center">
                    <span>Label T&amp;J 107</span>
                    <span class="badge text-bg-light text-primary">50×18 mm</span>
                </div>
                <div class="card-body d-flex flex-column gap-3">

                    <div>
                        <div class="l107-section-label">Spesifikasi Kertas</div>
                        <table class="table table-sm table-borderless small mb-0">
                            <tbody>
                                <tr><td class="text-muted ps-0">Kertas induk</td><td class="fw-semibold">A4 (210×297 mm)</td></tr>
                                <tr><td class="text-muted ps-0">Ukuran label</td><td class="fw-semibold">50 mm × 18 mm</td></tr>
                                <tr><td class="text-muted ps-0">Susunan grid</td><td class="fw-semibold">3 kolom × 10 baris</td></tr>
                                <tr><td class="text-muted ps-0">Label / halaman</td><td class="fw-semibold">30 label</td></tr>
                                <tr><td class="text-muted ps-0">Margin H / V</td><td class="fw-semibold">30 mm / 58.5 mm</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <div class="l107-section-label">Statistik</div>
                        <div class="d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Total Aset</span>
                                <span class="badge text-bg-primary">{{ $assets->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Halaman tercetak</span>
                                <span class="badge text-bg-secondary">{{ $chunks->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Waktu cetak</span>
                                <span class="text-dark fw-semibold" style="font-size:.72rem;">{{ $printDate }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-auto">
                        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i class="fa-solid fa-print me-1"></i> Cetak / Simpan PDF
                        </button>
                    </div>

                    <div class="small text-muted" style="font-size:.75rem;">
                        Klik <strong>Cetak / Simpan PDF</strong> untuk membuka dialog cetak browser. Pilih
                        <em>"Save as PDF"</em> agar hasil layout presisi sesuai kertas T&amp;J 107.
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Panel Kanan: Preview Container ──────────────────── --}}
        <div class="col-xl-9">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span>Container Preview</span>
                        <div class="small text-muted fw-normal">Label T&amp;J No.107 — A4, 3×10, 30 label/halaman</div>
                    </div>
                    <span class="badge text-bg-primary">
                        {{ $assets->count() }} aset &middot; {{ $chunks->count() }} halaman
                    </span>
                </div>
                <div class="card-body">
                    <div class="l107-preview-stage">

                        @if($assets->isNotEmpty())
                            <div class="l107-sheet-stack">
                                @foreach ($chunks as $pageIndex => $pageAssets)
                                    <div class="l107-sheet" data-l107-page="{{ $pageIndex }}">

                                        {{-- Sheet header (screen only) --}}
                                        <div class="l107-sheet-topbar"></div>
                                        <div class="l107-sheet-header">
                                            <div class="l107-sheet-kicker">Inventory Barang</div>
                                            <div class="l107-sheet-title">Label T&amp;J No.107</div>
                                            <div class="l107-sheet-subtitle">
                                                A4 · 3×10 · {{ $pageAssets->count() }} label pada halaman ini
                                            </div>
                                            <div class="l107-sheet-page-chip">
                                                Hal {{ $pageIndex + 1 }}/{{ $chunks->count() }}
                                            </div>
                                        </div>

                                        {{-- Label grid --}}
                                        <div class="l107-grid">

                                            @foreach ($pageAssets as $asset)
                                                <div class="l107-label">
                                                    <div class="l107-category">{{ $asset->category }}</div>
                                                    <div class="l107-name">{{ $asset->brand }} {{ $asset->model }}</div>
                                                    <div class="l107-barcode">
                                                        <svg class="l107-barcode-svg"
                                                             data-barcode-value="{{ $asset->barcode ?? $asset->serial_number }}">
                                                        </svg>
                                                    </div>
                                                    <div class="l107-date">Cetak: {{ $printDate }}</div>
                                                    <div class="l107-dept">Pengembangan Perangkat Lunak &amp; Gim</div>
                                                </div>
                                            @endforeach

                                            @for ($i = $pageAssets->count(); $i < 30; $i++)
                                                <div class="l107-label l107-label--empty"></div>
                                            @endfor

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="l107-empty-state">
                                <div class="l107-empty-icon"><i class="fa-solid fa-tags"></i></div>
                                <h5 class="mb-2">Belum ada aset</h5>
                                <p class="text-muted mb-0">Tambahkan data aset agar label bisa dipreview dan dicetak.</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* ── Section label ──────────────────────────────────────── */
    .l107-section-label {
        display: block;
        margin-bottom: .4rem;
        color: #64748b;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    /* ── Preview stage ──────────────────────────────────────── */
    .l107-preview-stage {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        min-height: 650px;
        padding: 1rem;
        overflow: auto;
        border: 1px dashed #c9d6ea;
        border-radius: 1.25rem;
        background: linear-gradient(180deg, rgba(13,110,253,.08), rgba(255,255,255,.75));
    }

    .l107-sheet-stack {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        align-items: center;
        width: 100%;
    }

    /* ── Empty state ────────────────────────────────────────── */
    .l107-empty-state {
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
        width: 100%;
    }
    .l107-empty-icon {
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

    /* ── A4 Sheet card ──────────────────────────────────────── */
    .l107-sheet {
        position: relative;
        width: 210mm;
        background: #fff;
        border: 1px solid #d4ddec;
        border-radius: 1rem;
        box-shadow: 0 24px 60px rgba(15,23,42,.16);
        overflow: hidden;
        color: #1f2937;
        /* screen: give breathing room around the grid */
        padding: 6mm 8mm 8mm;
    }

    .l107-sheet::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 8px;
        background: linear-gradient(90deg, #0d6efd, #2d7ff9, #6bb4ff);
    }

    /* Sheet header (screen only) */
    .l107-sheet-topbar {
        height: 2px;
        background: rgba(13,110,253,.12);
        margin-bottom: .6rem;
    }
    .l107-sheet-header {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
        margin-bottom: .75rem;
    }
    .l107-sheet-kicker {
        padding: .2rem .6rem;
        border-radius: 999px;
        background: #eaf2ff;
        color: #0d6efd;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
    .l107-sheet-title {
        font-size: .85rem;
        font-weight: 800;
        color: #0f172a;
    }
    .l107-sheet-subtitle {
        font-size: .7rem;
        color: #64748b;
    }
    .l107-sheet-page-chip {
        margin-left: auto;
        padding: .22rem .62rem;
        border-radius: 999px;
        background: #eaf2ff;
        color: #0d6efd;
        font-size: .67rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    /* ── Label grid ─────────────────────────────────────────── */
    .l107-grid {
        display: grid;
        grid-template-columns: repeat(3, 50mm);
        grid-template-rows: repeat(10, 18mm);
        width: 150mm;
        height: 180mm;
        gap: 0;
        /* centre the grid within the sheet card */
        margin: 0 auto;
    }

    /* ── Single label ───────────────────────────────────────── */
    .l107-label {
        width: 50mm;
        height: 18mm;
        border: .3px solid #bbb;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 1mm 1.5mm;
        overflow: hidden;
        position: relative;
        box-sizing: border-box;
    }

    .l107-label--empty::after {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            -45deg,
            transparent, transparent 4px,
            rgba(0,0,0,.03) 4px, rgba(0,0,0,.03) 5px
        );
    }

    .l107-category {
        font-size: 5pt;
        color: #666;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.1;
    }
    .l107-name {
        font-size: 7pt;
        font-weight: 700;
        color: #111;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }
    .l107-barcode {
        display: block;
        width: 100%;
        height: 7mm;
        margin: 1mm 0;
        overflow: hidden;
    }
    .l107-barcode-svg {
        width: 100% !important;
        height: 7mm !important;
        display: block;
    }
    .l107-date {
        font-size: 4.5pt;
        color: #555;
        letter-spacing: .1px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }
    .l107-dept {
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

    /* ── Print rules ────────────────────────────────────────── */
    @media print {
        @page { size: A4; margin: 0; }

        /* Hide entire admin chrome */
        .js-fixed-navbar,
        .admin-navbar,
        .has-fixed-nav { padding-top: 0 !important; }

        nav, header, footer,
        .col-xl-3,                   /* settings panel */
        .l107-sheet-topbar,
        .l107-sheet-header,
        .l107-sheet-page-chip { display: none !important; }

        /* Reset layout so only the preview stage content prints */
        body, html { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        main { padding: 0 !important; }
        .row { display: block !important; }
        .col-xl-9 { width: 100% !important; max-width: 100% !important; padding: 0 !important; }
        .card { box-shadow: none !important; border: none !important; }
        .card-header { display: none !important; }
        .card-body { padding: 0 !important; }

        /* Preview stage becomes transparent */
        .l107-preview-stage {
            display: block !important;
            border: none !important;
            background: none !important;
            padding: 0 !important;
            min-height: 0 !important;
            overflow: visible !important;
        }
        .l107-sheet-stack { gap: 0 !important; }

        /* Each sheet = 1 A4 page */
        .l107-sheet {
            width: 210mm !important;
            height: 297mm !important;
            padding: 58.5mm 30mm !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            overflow: hidden !important;
            page-break-after: always;
            break-after: page;
        }
        .l107-sheet::before { display: none !important; }
        .l107-sheet:last-child {
            page-break-after: avoid;
            break-after: avoid;
        }

        /* Grid centres in the exact A4 padded area */
        .l107-grid {
            margin: 0 !important;
            width: 150mm !important;
            height: 180mm !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/barcodes/JsBarcode.code128.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.l107-barcode-svg').forEach(function (svg) {
            var value = svg.getAttribute('data-barcode-value');
            if (!value) return;
            try {
                JsBarcode(svg, value, {
                    format: 'CODE128',
                    displayValue: false,
                    margin: 0,
                    width: 1,
                    height: 26,
                    lineColor: '#000',
                });
            } catch (e) {
                svg.style.display = 'none';
            }
        });
    });
</script>
@endpush
