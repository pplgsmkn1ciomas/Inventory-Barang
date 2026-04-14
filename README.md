# Inventory Barang (Laravel 13)

Proyek ini adalah hasil konversi dari aplikasi inventaris berbasis single-file PHP ke arsitektur Laravel terbaru.

## Stack

- Backend: Laravel 13 (PHP 8.3)
- Database default: SQLite
- Frontend: Blade + Bootstrap 5

## Modul Utama

- Dashboard Public
	- Stok barang tersedia
	- Daftar pinjaman aktif siswa
- Dashboard Admin
	- Ringkasan aset dan pinjaman
	- Tabel pinjaman aktif (kelas, no. HP, tanggal pinjam)
- Data Barang
	- Tambah/hapus barang
	- Filter (search, kategori, status)
	- Ringkasan total, per kategori, laptop per merk
- Data Pengguna
	- Tambah/hapus pengguna
	- Filter (search, role, kelas)
- Transaksi Peminjaman
	- Proses pinjam
	- Proses pengembalian

## Jalankan Proyek

```bash
composer install
php artisan migrate:fresh --seed
php artisan serve
```

Atau di Windows gunakan:

```bat
start-server.bat
```

Mode testing (port terpisah):

```bat
start-test-server.bat
```

Atau langsung dari skrip utama:

```bat
start-server.bat test
```

Override port manual:

```bat
start-server.bat 8080
start-server.bat test 8081
```

## Route Penting

| Route | Keterangan |
|---|---|
| `/` | Public dashboard |
| `/admin/dashboard` | Admin dashboard |
| `/admin/assets` | Data barang |
| `/admin/users` | Data pengguna |
| `/admin/loans` | Transaksi peminjaman |
| `/admin/loans?format=label107` | Cetak label barcode T&J No.107 |

## Format Label Cetak — T&J No. 107

Halaman cetak barcode aset diakses via `/admin/loans?format=label107`.

### Spesifikasi Kertas Label T&J No. 107

| Properti | Nilai |
|---|---|
| Merek | Tom & Jerry (T&J) |
| Nomor seri kertas | No. 107 |
| Warna kertas | Kuning |
| Ukuran kertas induk | A4 (210 mm × 297 mm) |
| Ukuran satu label | 50 mm (lebar) × 18 mm (tinggi) |
| Susunan grid | 3 kolom × 10 baris |
| Label per halaman | 30 label |
| Margin kiri & kanan | 30 mm |
| Margin atas & bawah | 58.5 mm |
| Jarak antar label | 0 mm (tanpa gap) |
| Isi per pak | 10 lembar = 300 label |

### Isi Setiap Label

- Kategori aset
- Nama (Brand + Model)
- Barcode batang (dari field `barcode`)
- Status aset (warna-coded)

## Seed Default

Seeder membuat data awal berikut:

- 1 admin
- 1 guru
- 1 siswa
- sample assets + sample loans

Password admin default seed:

- `admin12345`
