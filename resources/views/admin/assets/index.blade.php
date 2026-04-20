@extends('layouts.app')

@section('content')
    @php
        $formatOptionLabel = static fn (string $value): string => ucwords(str_replace(['_', '-'], ' ', $value));
    @endphp
    <div class="asset-page-shell" id="assetPageShell">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
        <div>
            <h4 class="mb-1">Data Barang</h4>
            <p class="text-muted mb-0">Manajemen aset inventaris sekolah dengan filter, ringkasan kategori, dan kontrol data terpusat.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <a href="{{ route('admin.assets.export', ['template' => 1]) }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-file-circle-plus me-1"></i>Template Excel
            </a>
            <a href="{{ route('admin.assets.export') }}" class="btn btn-outline-success">
                <i class="fa-solid fa-file-export me-1"></i>Export Excel
            </a>
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importAssetExcelModal">
                <i class="fa-solid fa-file-import me-1"></i>Import Excel
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAssetModal">
                <i class="fa-solid fa-plus me-1"></i>Tambah Barang
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="card asset-stat-card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="asset-stat-label">Total Barang</div>
                        <span class="asset-stat-icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                    </div>
                    <div class="asset-stat-value">{{ number_format($totalAssets) }}</div>
                    <div class="asset-stat-hint mt-2">Jumlah seluruh aset yang sudah terdaftar.</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6">
            <div class="card asset-list-card h-100">
                <div class="card-header fw-semibold asset-summary-header asset-summary-header-category">Jumlah Berdasarkan Kategori</div>
                <div class="card-body asset-list-scroll py-2">
                    @forelse($categoryCounts as $category => $count)
                        <div class="asset-list-item d-flex justify-content-between align-items-center">
                            <span>{{ $category }}</span>
                            <span class="badge rounded-pill text-bg-info">{{ number_format($count) }}</span>
                        </div>
                    @empty
                        <div class="text-muted py-2">Belum ada kategori.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-12">
            <div class="card asset-list-card h-100">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center asset-summary-header asset-summary-header-laptop">
                    <span>Laptop per Merk</span>
                    <span class="badge rounded-pill text-bg-primary">{{ number_format($totalLaptopAssets) }}</span>
                </div>
                <div class="card-body asset-list-scroll py-2">
                    @forelse($laptopBrandCounts as $brand => $count)
                        <div class="asset-list-item d-flex justify-content-between align-items-center">
                            <span>{{ $brand }}</span>
                            <span class="badge rounded-pill text-bg-primary">{{ number_format($count) }}</span>
                        </div>
                    @empty
                        <div class="text-muted py-2">Belum ada laptop terdata.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card asset-table-card">
        <div class="card-header bg-white border-0 pt-3 pb-2">
            <form method="GET" action="{{ route('admin.assets.index') }}" class="row g-2 align-items-end asset-filter-form">
                <div class="col-12 col-lg-4">
                    <label class="form-label small text-secondary mb-1">Pencarian</label>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}" placeholder="Cari merk, model, serial, kategori...">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label small text-secondary mb-1">Kategori</label>
                    <select class="form-select" name="category">
                        <option value="">Semua Kategori</option>
                        @foreach($allCategories as $category)
                            <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label small text-secondary mb-1">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        @foreach($allStatuses as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $formatOptionLabel($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-1 d-grid">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
                <div class="col-6 col-lg-1 d-grid">
                    <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-responsive asset-table-scroll">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Merk</th>
                    <th>MODEL / TYPE / SERI</th>
                    <th class="text-center">Serial Number</th>
                    <th class="text-center">Kode Barcode</th>
                    <th class="text-center">Barcode Batang</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Kondisi</th>
                    <th class="text-center">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($assets as $asset)
                    @php
                        $statusBadgeClass = match ($asset->status) {
                            'available' => 'text-bg-success',
                            'borrowed' => 'text-bg-warning',
                            'maintenance' => 'text-bg-danger',
                            'lost' => 'text-bg-dark',
                            default => 'text-bg-secondary',
                        };

                        $conditionBadgeClass = match ($asset->condition) {
                            'good' => 'text-bg-success',
                            'minor_damage' => 'text-bg-warning',
                            'major_damage' => 'text-bg-danger',
                            'under_repair' => 'text-bg-info',
                            default => 'text-bg-secondary',
                        };

                        $barcodeDownloadValue = (string) ($asset->barcode ?: $asset->serial_number);
                    @endphp
                    <tr>
                        <td>{{ $assets->firstItem() + $loop->index }}</td>
                        <td><span class="fw-medium">{{ $asset->category }}</span></td>
                        <td>{{ $asset->brand }}</td>
                        <td>{{ $asset->model }}</td>
                        <td class="text-center">{{ $asset->serial_number }}</td>
                        <td class="text-center">
                            @if($barcodeDownloadValue !== '')
                                <span class="font-monospace">{{ $barcodeDownloadValue }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if($barcodeDownloadValue !== '')
                                <div class="asset-barcode-wrap">
                                    <svg class="asset-barcode" data-barcode-value="{{ $barcodeDownloadValue }}" role="img" aria-label="Barcode {{ $barcodeDownloadValue }}"></svg>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $statusBadgeClass }}">{{ $formatOptionLabel($asset->status) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $conditionBadgeClass }}">{{ $formatOptionLabel($asset->condition) }}</span>
                        </td>
                        <td class="text-center">
                            <div class="asset-action-group d-inline-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAssetModal{{ $asset->id }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success js-download-barcode"
                                    data-asset-id="{{ $asset->id }}"
                                    data-barcode-value="{{ $barcodeDownloadValue }}"
                                    aria-label="Download barcode {{ $barcodeDownloadValue }}"
                                    @disabled($barcodeDownloadValue === '')
                                >
                                    <i class="fa-solid fa-download me-1"></i>Download
                                </button>
                                <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}" onsubmit="return confirm('Hapus barang ini?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-trash-can me-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data barang.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @foreach($assets as $asset)
            @php
                $isEditingAsset = (string) old('editing_asset_id') === (string) $asset->id;
                $editCategory = $isEditingAsset ? (string) old('category', $asset->category) : (string) $asset->category;
                $editBrand = $isEditingAsset ? (string) old('brand', $asset->brand) : (string) $asset->brand;
                $editModel = $isEditingAsset ? (string) old('model', $asset->model) : (string) $asset->model;
                $editSerialNumber = $isEditingAsset ? (string) old('serial_number', $asset->serial_number) : (string) $asset->serial_number;
                $editBarcode = $isEditingAsset ? (string) old('barcode', $asset->barcode) : (string) $asset->barcode;
                $editStatus = $isEditingAsset ? (string) old('status', $asset->status) : (string) $asset->status;
                $editCondition = $isEditingAsset ? (string) old('condition', $asset->condition) : (string) $asset->condition;
            @endphp
            <div class="modal fade" id="editAssetModal{{ $asset->id }}" tabindex="-1" aria-labelledby="editAssetModalLabel{{ $asset->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header asset-modal-header">
                            <h5 class="modal-title" id="editAssetModalLabel{{ $asset->id }}">Edit Barang</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.assets.update', $asset) }}" class="d-flex flex-column" style="min-height:0; flex:1 1 auto;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="editing_asset_id" value="{{ $asset->id }}">

                            <div class="modal-body" style="overflow-y:auto;">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Kategori</label>
                                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                            <option value="">Pilih kategori</option>
                                            @if($editCategory !== '' && !$allCategories->contains($editCategory))
                                                <option value="{{ $editCategory }}" selected>{{ $editCategory }}</option>
                                            @endif
                                            @foreach($allCategories as $category)
                                                <option value="{{ $category }}" @selected($editCategory === $category)>{{ $category }}</option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Merk</label>
                                        <select name="brand" class="form-select @error('brand') is-invalid @enderror" required>
                                            <option value="">Pilih merk</option>
                                            @if($editBrand !== '' && !$allBrands->contains($editBrand))
                                                <option value="{{ $editBrand }}" selected>{{ $editBrand }}</option>
                                            @endif
                                            @foreach($allBrands as $brand)
                                                <option value="{{ $brand }}" @selected($editBrand === $brand)>{{ $brand }}</option>
                                            @endforeach
                                        </select>
                                        @error('brand')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Model</label>
                                        <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" value="{{ $editModel }}" required>
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Serial Number</label>
                                        <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ $editSerialNumber }}" required>
                                        @error('serial_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Barcode (opsional)</label>
                                        <input
                                            type="text"
                                            name="barcode"
                                            id="editAssetBarcode{{ $asset->id }}"
                                            class="form-control @error('barcode') is-invalid @enderror"
                                            value="{{ $editBarcode }}"
                                        >
                                        @error('barcode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="asset-barcode-preview-panel mt-2 is-empty"
                                             id="editAssetBarcodePreviewPanel{{ $asset->id }}">
                                            <div class="asset-barcode-preview-title">Panel Review Barcode (Live)</div>
                                            <svg id="editAssetBarcodePreviewSvg{{ $asset->id }}"
                                                 class="asset-barcode-preview-svg"
                                                 role="img"
                                                 aria-label="Preview barcode"></svg>
                                            <div id="editAssetBarcodePreviewText{{ $asset->id }}"
                                                 class="asset-barcode-preview-text">Belum ada data barcode</div>
                                            <div class="asset-barcode-preview-hint">Barcode mengikuti input Barcode, atau Serial Number jika barcode kosong.</div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                                    @foreach($allStatuses as $status)
                                                        <option value="{{ $status }}" @selected($editStatus === $status)>{{ $formatOptionLabel($status) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label class="form-label">Kondisi</label>
                                                <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                                                    @foreach($allConditions as $condition)
                                                        <option value="{{ $condition }}" @selected($editCondition === $condition)>{{ $formatOptionLabel($condition) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('condition')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-pen-to-square me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        @if($assets->hasPages())
            <div class="card-footer bg-white">{{ $assets->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
    </div>

    <div class="modal fade" id="createAssetModal" tabindex="-1" aria-labelledby="createAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header asset-modal-header">
                    <h5 class="modal-title" id="createAssetModalLabel">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.assets.store') }}" class="d-flex flex-column" style="min-height:0; flex:1 1 auto;">
                    @csrf
                    <div class="modal-body" style="overflow-y:auto;">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Kategori</label>
                                <select
                                    name="category"
                                    class="form-select @error('category') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Pilih kategori</option>
                                    @if(old('category') && !$allCategories->contains(old('category')))
                                        <option value="{{ old('category') }}" selected>{{ old('category') }}</option>
                                    @endif
                                    @foreach($allCategories as $category)
                                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Merk</label>
                                        <select
                                            name="brand"
                                            class="form-select @error('brand') is-invalid @enderror"
                                            required
                                        >
                                            <option value="">Pilih merk</option>
                                            @if(old('brand') && !$allBrands->contains(old('brand')))
                                                <option value="{{ old('brand') }}" selected>{{ old('brand') }}</option>
                                            @endif
                                            @foreach($allBrands as $brand)
                                                <option value="{{ $brand }}" @selected(old('brand') === $brand)>{{ $brand }}</option>
                                            @endforeach
                                        </select>
                                        @error('brand')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Model</label>
                                        <input
                                            type="text"
                                            name="model"
                                            class="form-control @error('model') is-invalid @enderror"
                                            value="{{ old('model') }}"
                                            required
                                        >
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Serial Number</label>
                                <input
                                    type="text"
                                    id="createAssetSerialNumber"
                                    name="serial_number"
                                    class="form-control @error('serial_number') is-invalid @enderror"
                                    value="{{ old('serial_number') }}"
                                    required
                                >
                                @error('serial_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Barcode (opsional)</label>
                                <input
                                    type="text"
                                    id="createAssetBarcode"
                                    name="barcode"
                                    class="form-control @error('barcode') is-invalid @enderror"
                                    value="{{ old('barcode') }}"
                                >
                                @error('barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="asset-barcode-preview-panel mt-2 is-empty" id="createAssetBarcodePreviewPanel">
                                    <div class="asset-barcode-preview-title">Panel Review Barcode (Live)</div>
                                    <svg id="createAssetBarcodePreviewSvg" class="asset-barcode-preview-svg" role="img" aria-label="Preview barcode"></svg>
                                    <div id="createAssetBarcodePreviewText" class="asset-barcode-preview-text">Belum ada data barcode</div>
                                    <div class="asset-barcode-preview-hint">Barcode mengikuti input Barcode, atau Serial Number jika barcode kosong.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                            @foreach($allStatuses as $status)
                                                <option value="{{ $status }}" @selected(old('status', $allStatuses->first()) === $status)>{{ $formatOptionLabel($status) }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Kondisi</label>
                                        <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                                            @foreach($allConditions as $condition)
                                                <option value="{{ $condition }}" @selected(old('condition', $allConditions->first()) === $condition)>{{ $formatOptionLabel($condition) }}</option>
                                            @endforeach
                                        </select>
                                        @error('condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importAssetExcelModal" tabindex="-1" aria-labelledby="importAssetExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header asset-modal-header">
                    <h5 class="modal-title" id="importAssetExcelModalLabel">Import Data Barang dari Excel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.assets.import') }}" enctype="multipart/form-data" class="d-flex flex-column" style="min-height:0; flex:1 1 auto;">
                    @csrf
                    <div class="modal-body" style="overflow-y:auto;">
                        <div class="mb-3">
                            <label for="importAssetExcelFile" class="form-label">File Excel <span class="text-danger">*</span></label>
                            <input
                                type="file"
                                id="importAssetExcelFile"
                                name="excel_file"
                                class="form-control @error('excel_file') is-invalid @enderror"
                                accept=".xlsx,.xls,.csv"
                                required
                            >
                            @error('excel_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format yang didukung: XLSX, XLS, atau CSV. Maksimal 10 MB.</div>
                        </div>

                        <div class="alert alert-light border mb-0">
                            <div class="fw-semibold mb-1">Header Excel harus sama dengan header tabel Data Barang:</div>
                            <div class="small font-monospace">No | Kategori | Merk | MODEL / TYPE / SERI | Serial Number | Kode Barcode | Barcode Batang | Status | Kondisi</div>
                            <div class="small mt-2 mb-0">Formula impor: baris kosong atau baris yang tidak lengkap akan otomatis dilewati, sehingga proses import tetap lanjut ke baris berikutnya.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="{{ route('admin.assets.export', ['template' => 1]) }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-file-arrow-down me-2"></i>Download Template
                        </a>
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-file-import me-2"></i>Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .asset-page-shell {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            min-height: 0;
        }

        .asset-page-shell > .mb-3,
        .asset-page-shell > .row.mb-3 {
            margin-bottom: 0 !important;
        }

        .asset-stat-card,
        .asset-list-card,
        .asset-table-card {
            border-radius: 0.9rem;
            box-shadow: 0 8px 22px rgba(31, 50, 81, 0.08);
        }

        .asset-list-card,
        .asset-table-card {
            border: 1px solid #dbe3ef;
        }

        .asset-stat-card {
            border: 0;
            color: #ffffff;
            min-height: 176px;
            background: linear-gradient(315deg, #8fb8ff 0%, #3f7bf0 48%, #173f99 100%);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.15);
        }

        .asset-stat-card .card-body {
            padding: 1.1rem 1.1rem 1.2rem;
        }

        .asset-stat-label {
            font-size: 0.79rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.94;
        }

        .asset-stat-value {
            font-size: clamp(2rem, 1.3vw + 1.35rem, 2.6rem);
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .asset-stat-hint {
            font-size: 0.86rem;
            font-weight: 500;
            line-height: 1.35;
            opacity: 0.9;
        }

        .asset-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.8rem;
            background: rgba(255, 255, 255, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.38);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            backdrop-filter: blur(1px);
        }

        .asset-list-scroll {
            max-height: none;
            overflow: visible;
        }

        .asset-list-item {
            padding: 0.42rem 0;
            border-bottom: 1px solid #edf2f8;
            font-size: 0.94rem;
        }

        .asset-list-item:last-child {
            border-bottom: 0;
        }

        .asset-filter-form .form-control,
        .asset-filter-form .form-select {
            border-color: #d2dbe8;
        }

        .asset-table-card .table thead th {
            font-size: 0.78rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            background: linear-gradient(180deg, #edf4ff 0%, #dfebff 100%);
            color: #184a98;
            border-bottom: 1px solid #c5d8f5;
            font-weight: 800;
        }

        .asset-table-card .table {
            margin-left: 15px;
            margin-right: 15px;
            width: calc(100% - 30px);
        }

        .asset-table-card {
            display: block;
        }

        .asset-table-scroll {
            overflow-x: auto;
            overflow-y: visible;
        }

        .asset-table-card .table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
        }

        .asset-barcode-wrap {
            width: 180px;
            max-width: 100%;
            margin-inline: auto;
        }

        .asset-barcode {
            width: 170px;
            max-width: 100%;
            height: 40px;
            display: block;
        }

        .asset-barcode-text {
            margin-top: 0.2rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: #4b5f7f;
            letter-spacing: 0.04em;
            line-height: 1;
        }

        .asset-summary-header {
            border-bottom: 1px solid #cfddf3;
        }

        .asset-summary-header-category {
            background: linear-gradient(180deg, #f2f7ff 0%, #e7f0ff 100%);
            color: #1f4f97;
        }

        .asset-summary-header-laptop {
            background: linear-gradient(180deg, #eff4ff 0%, #e3edff 100%);
            color: #18488f;
        }

        .asset-summary-header-laptop .badge {
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45);
        }

        .asset-barcode-preview-panel {
            border: 1px dashed #b9c9e3;
            border-radius: 0.75rem;
            background: #f7faff;
            margin-inline: auto;
            padding: 0.62rem 0;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .asset-barcode-preview-title {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #245090;
            width: 100%;
            text-align: center;
        }

        .asset-barcode-preview-svg {
            width: 100%;
            max-width: 260px;
            height: 56px;
            margin-top: 0.34rem;
            margin-inline: auto;
            display: block;
        }

        .asset-barcode-preview-text {
            margin-top: 0.26rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #334f7a;
            letter-spacing: 0.05em;
            word-break: break-word;
            line-height: 1.2;
            width: 100%;
            text-align: center;
        }

        .asset-barcode-preview-hint {
            margin-top: 0.2rem;
            font-size: 0.68rem;
            color: #6d7f9d;
            line-height: 1.25;
            width: 100%;
            text-align: center;
        }

        .asset-barcode-preview-panel.is-empty .asset-barcode-preview-svg {
            display: none;
        }

        .asset-action-group {
            justify-content: center;
            flex-wrap: wrap;
        }

        .asset-modal-header {
            background: #0b5ed7;
            color: #ffffff;
        }
    </style>
@endpush

@push('scripts')
    @php
        $editingAssetId = old('editing_asset_id');
        $hasAssetFormErrors = $errors->hasAny([
            'category',
            'brand',
            'model',
            'serial_number',
            'barcode',
            'status',
            'condition',
        ]);
        $shouldOpenCreateAssetModal = $hasAssetFormErrors && !$editingAssetId;
        $hasImportExcelErrors = $errors->has('excel_file');
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var hasJsBarcode = typeof JsBarcode === 'function';

            var renderBarcodeToSvg = function (barcodeNode, value, options) {
                if (!barcodeNode) {
                    return false;
                }

                barcodeNode.innerHTML = '';

                var barcodeValue = (value || '').trim();
                if (!barcodeValue || !hasJsBarcode) {
                    return false;
                }

                try {
                    JsBarcode(barcodeNode, barcodeValue, Object.assign({
                        format: 'CODE128',
                        displayValue: false,
                        height: 40,
                        width: 1.3,
                        margin: 0,
                        lineColor: '#1d2a42',
                        background: 'transparent'
                    }, options || {}));

                    return true;
                } catch (error) {
                    barcodeNode.innerHTML = '';
                    return false;
                }
            };

            var renderBarcodeToCanvas = function (canvasNode, value, options) {
                if (!canvasNode) {
                    return false;
                }

                var barcodeValue = (value || '').trim();
                if (!barcodeValue || !hasJsBarcode) {
                    return false;
                }

                try {
                    JsBarcode(canvasNode, barcodeValue, Object.assign({
                        format: 'CODE128',
                        displayValue: true,
                        height: 72,
                        width: 2,
                        margin: 10,
                        lineColor: '#111827',
                        background: '#ffffff',
                        fontSize: 16,
                        textMargin: 6
                    }, options || {}));

                    return true;
                } catch (error) {
                    return false;
                }
            };

            var toSafeFilename = function (value) {
                var normalized = (value || '')
                    .toString()
                    .trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                if (!normalized) {
                    return 'barcode-item';
                }

                return normalized.slice(0, 80);
            };

            var triggerBlobDownload = function (blob, fileName) {
                var url = URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            };

            var downloadBarcodeImage = function (barcodeValue, assetId) {
                var cleanValue = (barcodeValue || '').trim();

                if (!cleanValue || !hasJsBarcode) {
                    alert('Barcode tidak tersedia untuk diunduh.');
                    return;
                }

                var barcodeCanvas = document.createElement('canvas');
                var rendered = renderBarcodeToCanvas(barcodeCanvas, cleanValue);

                if (!rendered) {
                    alert('Gagal menghasilkan gambar barcode.');
                    return;
                }

                var fileName = 'barcode-' + (assetId ? assetId + '-' : '') + toSafeFilename(cleanValue) + '.png';

                if (typeof barcodeCanvas.toBlob === 'function') {
                    barcodeCanvas.toBlob(function (blob) {
                        if (!blob) {
                            alert('Gagal membuat file barcode.');
                            return;
                        }

                        triggerBlobDownload(blob, fileName);
                    }, 'image/png');

                    return;
                }

                var fallbackLink = document.createElement('a');
                fallbackLink.href = barcodeCanvas.toDataURL('image/png');
                fallbackLink.download = fileName;
                document.body.appendChild(fallbackLink);
                fallbackLink.click();
                fallbackLink.remove();
            };

            if (hasJsBarcode) {
                document.querySelectorAll('.asset-barcode[data-barcode-value]').forEach(function (barcodeNode) {
                    var value = (barcodeNode.dataset.barcodeValue || '').trim();

                    if (!value) {
                        return;
                    }

                    var rendered = renderBarcodeToSvg(barcodeNode, value, {
                        height: 40,
                        width: 1.3,
                    });

                    if (!rendered) {
                        barcodeNode.classList.add('d-none');
                    }
                });
            }

            document.querySelectorAll('.js-download-barcode').forEach(function (downloadButton) {
                downloadButton.addEventListener('click', function () {
                    var barcodeValue = (downloadButton.dataset.barcodeValue || '').trim();
                    var assetId = (downloadButton.dataset.assetId || '').trim();
                    downloadBarcodeImage(barcodeValue, assetId);
                });
            });

            var createAssetSerialInput = document.getElementById('createAssetSerialNumber');
            var createAssetBarcodeInput = document.getElementById('createAssetBarcode');
            var createAssetBarcodePreviewPanel = document.getElementById('createAssetBarcodePreviewPanel');
            var createAssetBarcodePreviewSvg = document.getElementById('createAssetBarcodePreviewSvg');
            var createAssetBarcodePreviewText = document.getElementById('createAssetBarcodePreviewText');
            var createAssetModalElement = document.getElementById('createAssetModal');

            var updateCreateAssetBarcodePreview = function () {
                if (!createAssetBarcodePreviewPanel || !createAssetBarcodePreviewSvg || !createAssetBarcodePreviewText) {
                    return;
                }

                var barcodeInputValue = createAssetBarcodeInput ? createAssetBarcodeInput.value.trim() : '';
                var serialInputValue = createAssetSerialInput ? createAssetSerialInput.value.trim() : '';
                var previewValue = barcodeInputValue || serialInputValue;

                var rendered = renderBarcodeToSvg(createAssetBarcodePreviewSvg, previewValue, {
                    height: 56,
                    width: 1.35,
                    lineColor: '#163d73'
                });

                if (rendered) {
                    createAssetBarcodePreviewPanel.classList.remove('is-empty');
                    createAssetBarcodePreviewText.textContent = previewValue;
                } else {
                    createAssetBarcodePreviewPanel.classList.add('is-empty');
                    createAssetBarcodePreviewText.textContent = previewValue ? 'Format barcode tidak valid.' : 'Belum ada data barcode';
                }
            };

            if (createAssetSerialInput) {
                createAssetSerialInput.addEventListener('input', updateCreateAssetBarcodePreview);
            }

            if (createAssetBarcodeInput) {
                createAssetBarcodeInput.addEventListener('input', updateCreateAssetBarcodePreview);
            }

            if (createAssetModalElement) {
                createAssetModalElement.addEventListener('shown.bs.modal', updateCreateAssetBarcodePreview);
            }

            // ── Edit modal barcode preview (one per asset) ─────────
            document.querySelectorAll('[id^="editAssetModal"]').forEach(function (modalEl) {
                var assetId = modalEl.id.replace('editAssetModal', '');
                var editSerialInput  = document.getElementById('editAssetBarcode' + assetId)
                                      ? null
                                      : null; // serial not needed — barcode input has its own id
                var editBarcodeInput = document.getElementById('editAssetBarcode'  + assetId);
                var editPreviewPanel = document.getElementById('editAssetBarcodePreviewPanel' + assetId);
                var editPreviewSvg   = document.getElementById('editAssetBarcodePreviewSvg'   + assetId);
                var editPreviewText  = document.getElementById('editAssetBarcodePreviewText'  + assetId);

                // Find the serial_number input inside this modal
                var editSerialInput  = modalEl.querySelector('input[name="serial_number"]');

                if (!editPreviewPanel || !editPreviewSvg || !editPreviewText) {
                    return;
                }

                var updateEditPreview = function () {
                    var barcodeVal = editBarcodeInput ? editBarcodeInput.value.trim() : '';
                    var serialVal  = editSerialInput  ? editSerialInput.value.trim()  : '';
                    var previewVal = barcodeVal || serialVal;

                    var rendered = renderBarcodeToSvg(editPreviewSvg, previewVal, {
                        height: 56,
                        width: 1.35,
                        lineColor: '#163d73'
                    });

                    if (rendered) {
                        editPreviewPanel.classList.remove('is-empty');
                        editPreviewText.textContent = previewVal;
                    } else {
                        editPreviewPanel.classList.add('is-empty');
                        editPreviewText.textContent = previewVal ? 'Format barcode tidak valid.' : 'Belum ada data barcode';
                    }
                };

                if (editBarcodeInput) {
                    editBarcodeInput.addEventListener('input', updateEditPreview);
                }
                if (editSerialInput) {
                    editSerialInput.addEventListener('input', updateEditPreview);
                }

                // Render saat modal terbuka
                modalEl.addEventListener('shown.bs.modal', updateEditPreview);
            });

            updateCreateAssetBarcodePreview();

            var shouldOpenCreateAssetModal = @json($shouldOpenCreateAssetModal);
            var editingAssetId = @json($editingAssetId);
            var shouldOpenImportAssetModal = @json($hasImportExcelErrors);

            if (editingAssetId) {
                var editAssetModalElement = document.getElementById('editAssetModal' + editingAssetId);

                if (editAssetModalElement) {
                    var editAssetModal = new bootstrap.Modal(editAssetModalElement);
                    editAssetModal.show();
                    return;
                }
            }

            if (shouldOpenImportAssetModal) {
                var importAssetExcelModalElement = document.getElementById('importAssetExcelModal');

                if (importAssetExcelModalElement) {
                    var importAssetExcelModal = new bootstrap.Modal(importAssetExcelModalElement);
                    importAssetExcelModal.show();
                    return;
                }
            }

            if (!shouldOpenCreateAssetModal) {
                return;
            }

            var createAssetModalElement = document.getElementById('createAssetModal');
            if (!createAssetModalElement) {
                return;
            }

            var createAssetModal = new bootstrap.Modal(createAssetModalElement);
            createAssetModal.show();
        });
    </script>
@endpush
