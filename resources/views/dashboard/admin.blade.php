@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Dashboard Admin</h4>
            <p class="text-muted mb-0">Ringkasan operasional peminjaman aset.</p>
        </div>
    </div>

    <div class="row g-3 admin-dashboard-split">
        <div class="col-12 admin-dashboard-left">
            <div class="row row-cols-1 g-3 admin-kpi-stack">
        <div class="col">
            <div class="card admin-kpi-card kpi-total h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="kpi-label">Total Aset</div>
                        <span class="kpi-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                    </div>
                    <div class="kpi-value">{{ number_format($totalAssets) }}</div>
                    <div class="kpi-hint mt-2">Semua aset terdaftar dalam sistem inventaris</div>

                    <div class="kpi-slide-zone">
                        @if($totalCategorySlides->isEmpty())
                            <div class="kpi-slide-empty">Belum ada kategori non-laptop.</div>
                        @elseif($totalCategorySlides->count() > 1)
                            <div id="totalAssetCategoryCarousel" class="carousel slide kpi-mini-carousel" data-bs-ride="carousel" data-bs-interval="2800">
                                <div class="carousel-inner">
                                    @foreach($totalCategorySlides as $slide)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <div class="kpi-slide-item">
                                                <span class="kpi-slide-label">{{ $slide['label'] }}</span>
                                                <span class="kpi-slide-count">{{ number_format($slide['count']) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="kpi-slide-item">
                                <span class="kpi-slide-label">{{ $totalCategorySlides->first()['label'] }}</span>
                                <span class="kpi-slide-count">{{ number_format($totalCategorySlides->first()['count']) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card admin-kpi-card kpi-laptop-combined h-100">
                <div class="card-body">
                    <div
                        id="laptopCombinedCarousel"
                        class="carousel slide kpi-combined-carousel h-100"
                        data-bs-ride="carousel"
                        data-bs-interval="2800"
                        data-bs-pause="false"
                    >
                        <div class="carousel-inner h-100">
                            <div class="carousel-item active h-100">
                                <div class="kpi-combined-slide d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="kpi-label">Laptop (Total)</div>
                                        <span class="kpi-icon"><i class="fa-solid fa-laptop"></i></span>
                                    </div>
                                    <div class="kpi-value">
                                        {{ number_format($totalLaptopAssets) }}
                                        <span class="kpi-value-unit">unit</span>
                                    </div>
                                    <div class="kpi-hint mt-2">Total barang kategori laptop (termasuk dipinjam/rusak)</div>

                                    <div class="kpi-slide-zone">
                                        @if($totalLaptopBrandSlides->isEmpty())
                                            <div class="kpi-slide-empty">Belum ada data merk laptop.</div>
                                        @elseif($totalLaptopBrandSlides->count() > 1)
                                            <div id="laptopTotalBrandCarousel" class="carousel slide kpi-mini-carousel" data-bs-ride="carousel" data-bs-interval="2200">
                                                <div class="carousel-inner">
                                                    @foreach($totalLaptopBrandSlides as $slide)
                                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                                            <div class="kpi-slide-item">
                                                                <span class="kpi-slide-label">{{ $slide['label'] }}</span>
                                                                <span class="kpi-slide-count">{{ number_format($slide['count']) }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="kpi-slide-item">
                                                <span class="kpi-slide-label">{{ $totalLaptopBrandSlides->first()['label'] }}</span>
                                                <span class="kpi-slide-count">{{ number_format($totalLaptopBrandSlides->first()['count']) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-item h-100">
                                <div class="kpi-combined-slide d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="kpi-label">Laptop Tersedia</div>
                                        <span class="kpi-icon"><i class="fa-solid fa-circle-check"></i></span>
                                    </div>
                                    <div class="kpi-value">
                                        {{ number_format($availableLaptopAssetsCount) }}
                                        <span class="kpi-value-unit">unit</span>
                                    </div>
                                    <div class="kpi-hint mt-2">Laptop siap dipinjam berdasarkan stok available</div>

                                    <div class="kpi-slide-zone">
                                        @if($availableLaptopBrandSlides->isEmpty())
                                            <div class="kpi-slide-empty">Belum ada laptop tersedia.</div>
                                        @elseif($availableLaptopBrandSlides->count() > 1)
                                            <div id="availableLaptopBrandCarousel" class="carousel slide kpi-mini-carousel" data-bs-ride="carousel" data-bs-interval="2200">
                                                <div class="carousel-inner">
                                                    @foreach($availableLaptopBrandSlides as $slide)
                                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                                            <div class="kpi-slide-item">
                                                                <span class="kpi-slide-label">{{ $slide['label'] }}</span>
                                                                <span class="kpi-slide-count">{{ number_format($slide['count']) }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="kpi-slide-item">
                                                <span class="kpi-slide-label">{{ $availableLaptopBrandSlides->first()['label'] }}</span>
                                                <span class="kpi-slide-count">{{ number_format($availableLaptopBrandSlides->first()['count']) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card admin-kpi-card kpi-borrowed h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="kpi-label">Dipinjam</div>
                        <span class="kpi-icon"><i class="fa-solid fa-right-left"></i></span>
                    </div>
                    <div class="kpi-value">{{ number_format($borrowedAssets) }}</div>
                    <div class="kpi-hint mt-2">Aset dipinjam aktif berdasarkan status barang</div>

                    <div class="kpi-slide-zone">
                        @if($borrowedCategorySlides->isEmpty())
                            <div class="kpi-slide-empty">Belum ada aset yang dipinjam.</div>
                        @elseif($borrowedCategorySlides->count() > 1)
                            <div id="borrowedCategoryCarousel" class="carousel slide kpi-mini-carousel" data-bs-ride="carousel" data-bs-interval="2600">
                                <div class="carousel-inner">
                                    @foreach($borrowedCategorySlides as $slide)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <div class="kpi-slide-item">
                                                <span class="kpi-slide-label">{{ $slide['label'] }}</span>
                                                <span class="kpi-slide-count">{{ number_format($slide['count']) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="kpi-slide-item">
                                <span class="kpi-slide-label">{{ $borrowedCategorySlides->first()['label'] }}</span>
                                <span class="kpi-slide-count">{{ number_format($borrowedCategorySlides->first()['count']) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card admin-kpi-card kpi-damaged h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="kpi-label">Barang Rusak</div>
                        <span class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    </div>
                    <div class="kpi-value">{{ number_format($damagedAssets) }}</div>
                    <div class="kpi-hint mt-2">Aset dengan kondisi minor/major damage atau maintenance</div>

                    <div class="kpi-slide-zone">
                        @if($damagedCategorySlides->isEmpty())
                            <div class="kpi-slide-empty">Belum ada data barang rusak.</div>
                        @elseif($damagedCategorySlides->count() > 1)
                            <div id="damagedCategoryCarousel" class="carousel slide kpi-mini-carousel" data-bs-ride="carousel" data-bs-interval="2600">
                                <div class="carousel-inner">
                                    @foreach($damagedCategorySlides as $slide)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <div class="kpi-slide-item">
                                                <span class="kpi-slide-label">{{ $slide['label'] }}</span>
                                                <span class="kpi-slide-count">{{ number_format($slide['count']) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="kpi-slide-item">
                                <span class="kpi-slide-label">{{ $damagedCategorySlides->first()['label'] }}</span>
                                <span class="kpi-slide-count">{{ number_format($damagedCategorySlides->first()['count']) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        </div>
        </div>

        <div class="col-12 admin-dashboard-right">
            <div class="d-flex flex-column gap-3">
                <div class="row g-2 admin-preview-switch" role="tablist" aria-label="Preview tabel dashboard admin">
                    <div class="col-12 col-sm-6">
                        <button
                            type="button"
                            class="btn admin-preview-btn active w-100"
                            id="previewStockTab"
                            data-bs-toggle="tab"
                            data-bs-target="#previewStockPane"
                            role="tab"
                            aria-controls="previewStockPane"
                            aria-selected="true"
                        >
                            Stok Barang Tersedia
                        </button>
                    </div>
                    <div class="col-12 col-sm-6">
                        <button
                            type="button"
                            class="btn admin-preview-btn w-100"
                            id="previewBorrowedTab"
                            data-bs-toggle="tab"
                            data-bs-target="#previewBorrowedPane"
                            role="tab"
                            aria-controls="previewBorrowedPane"
                            aria-selected="false"
                        >
                            Barang Sedang Dipinjam
                        </button>
                    </div>
                </div>

                <div class="tab-content">
                    <div
                        class="tab-pane fade show active"
                        id="previewStockPane"
                        role="tabpanel"
                        aria-labelledby="previewStockTab"
                        tabindex="0"
                    >
                        <div class="card h-100 admin-table-card">
                        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                            <span>Stok Barang Tersedia (Laptop)</span>
                            <span class="badge text-bg-primary">{{ number_format($availableLaptopAssetsCount) }}</span>
                        </div>
                        <div class="table-responsive stock-laptop-table-wrap">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Barang</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($availableAssets as $asset)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $asset->brand }}</div>
                                            <small class="text-muted">{{ $asset->model }} ({{ $asset->serial_number }})</small>
                                        </td>
                                        <td><span class="badge text-bg-success">Ready</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada stok tersedia.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>

                    <div
                        class="tab-pane fade"
                        id="previewBorrowedPane"
                        role="tabpanel"
                        aria-labelledby="previewBorrowedTab"
                        tabindex="0"
                    >
                        <div class="card h-100 admin-table-card">
                        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                            <span>Barang Sedang Dipinjam</span>
                            <span class="badge text-bg-warning">{{ number_format($borrowedAssets) }}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Peminjam</th>
                                    <th>Serial Number</th>
                                    <th>Kode Barcode</th>
                                    <th>Kelas</th>
                                    <th>No. HP</th>
                                    <th>Tanggal Pinjam</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($activeLoans as $loan)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $loan->user?->name ?? '-' }}</div>
                                            <small class="text-muted">{{ $loan->asset?->brand }} {{ $loan->asset?->model }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $loan->asset?->serial_number ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $loan->asset?->barcode ?? '-' }}</div>
                                        </td>
                                        <td>{{ $loan->user?->kelas ?? '-' }}</td>
                                        <td>{{ $loan->user?->phone ?? '-' }}</td>
                                        <td>
                                            @if($loan->loan_date)
                                                <div>{{ $loan->loan_date->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $loan->loan_date->format('H:i:s') }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pinjaman aktif.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .admin-kpi-card {
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
            min-height: 176px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.15);
        }

        .admin-kpi-card .card-body {
            position: relative;
            padding: 1.1rem 1.1rem 1.2rem;
        }

        .kpi-slide-zone {
            margin-top: auto;
            padding-top: 0.8rem;
        }

        .kpi-label {
            font-size: 0.79rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.94;
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.38);
            backdrop-filter: blur(1px);
        }

        .kpi-value {
            font-size: clamp(2rem, 1.3vw + 1.35rem, 2.6rem);
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .kpi-value-unit {
            display: inline-block;
            margin-left: 0.2rem;
            font-size: 0.96rem;
            font-weight: 700;
            opacity: 0.88;
        }

        .kpi-hint {
            font-size: 0.86rem;
            font-weight: 500;
            line-height: 1.35;
            opacity: 0.9;
        }

        .kpi-mini-carousel {
            border-radius: 0.8rem;
            overflow: hidden;
        }

        .kpi-mini-carousel .carousel-inner {
            border-radius: 0.8rem;
        }

        .kpi-combined-carousel,
        .kpi-combined-carousel .carousel-inner,
        .kpi-combined-carousel .carousel-item {
            height: 100%;
        }

        .kpi-combined-slide {
            min-height: 100%;
        }

        .kpi-combined-slide .kpi-hint {
            min-height: calc(1.35em * 4);
        }

        .kpi-slide-item,
        .kpi-slide-empty {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.7rem;
            border-radius: 0.8rem;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.33);
            padding: 0.48rem 0.72rem;
            min-height: 45px;
        }

        .kpi-slide-empty {
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 600;
            opacity: 0.9;
        }

        .kpi-slide-label {
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1.2;
            opacity: 0.95;
            max-width: 72%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kpi-slide-count {
            font-size: 0.88rem;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .kpi-total {
            color: #ffffff;
            background: linear-gradient(135deg, #8fb8ff 0%, #3f7bf0 48%, #173f99 100%);
        }

        .kpi-laptop {
            color: #ffffff;
            background: linear-gradient(135deg, #8fd2ff 0%, #3f98f0 48%, #1058a8 100%);
        }

        .kpi-ready {
            color: #ffffff;
            background: linear-gradient(135deg, #95e4be 0%, #2fbf7f 48%, #157347 100%);
        }

        .kpi-laptop-combined {
            color: #ffffff;
            background: linear-gradient(135deg, #a3edbf 0%, #35be72 47%, #0f7a47 100%);
        }

        .kpi-borrowed {
            color: #1c1b1b;
            background: linear-gradient(135deg, #ffe7b5 0%, #ffc45b 47%, #ff8f00 100%);
        }

        .kpi-borrowed .kpi-icon {
            background: rgba(255, 255, 255, 0.35);
            border-color: rgba(255, 255, 255, 0.55);
        }

        .kpi-borrowed .kpi-slide-item,
        .kpi-borrowed .kpi-slide-empty {
            background: rgba(255, 255, 255, 0.28);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .kpi-borrowed .kpi-slide-label,
        .kpi-borrowed .kpi-slide-count,
        .kpi-borrowed .kpi-slide-empty {
            color: #191919;
        }

        .kpi-damaged {
            color: #ffffff;
            background: linear-gradient(135deg, #ffb2b2 0%, #f05f5f 47%, #b42323 100%);
        }

        .admin-dashboard-split {
            align-items: flex-start;
        }

        .admin-kpi-stack .col {
            width: 100%;
        }

        @media (min-width: 992px) {
            .admin-dashboard-left {
                flex: 0 0 30%;
                max-width: 30%;
            }

            .admin-dashboard-right {
                flex: 0 0 70%;
                max-width: 70%;
            }
        }

        .admin-preview-btn {
            min-height: 58px;
            border-radius: 0.85rem;
            border: 2px solid #d7dee9;
            background: #ffffff;
            color: #1f2937;
            font-size: 0.95rem;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(40, 58, 90, 0.08);
            transition: all 0.2s ease;
        }

        .admin-preview-btn:hover,
        .admin-preview-btn:focus-visible {
            border-color: #9eb1ca;
            box-shadow: 0 10px 20px rgba(40, 58, 90, 0.12);
            outline: 0;
        }

        .admin-preview-btn.active {
            color: #ffffff;
            background: linear-gradient(135deg, #2f66e0 0%, #173f99 100%);
            border-color: #173f99;
        }

        .admin-table-card {
            width: 100%;
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .stock-laptop-table-wrap {
            width: 100%;
            max-width: 100%;
        }

        @media (max-width: 991.98px) {
            .admin-kpi-card {
                min-height: 162px;
            }

            .kpi-combined-slide .kpi-hint {
                min-height: calc(1.35em * 2);
            }

            .kpi-value {
                font-size: clamp(1.8rem, 2.2vw + 1rem, 2.3rem);
            }
        }
    </style>
@endpush
