# Inventory Barang (Laravel 13)

Proyek ini adalah hasil konversi dari aplikasi inventaris berbasis single-file PHP ke arsitektur Laravel terbaru.

## Stack

- Backend: Laravel 13 (PHP 8.3)
- Face Recognition: face-api.js di browser + matching descriptor di Laravel
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
- Face Recognition
	- Registrasi wajah pengguna dari kamera browser
	- Peminjaman berbasis pengenalan wajah (tanpa input manual NISN)
- Barcode & Label Cetak
	- Preview barcode aset (A4 Grid, Label 107, Label 103)
	- Download PNG/JPEG, PDF, dan print langsung
	- Kalibrasi posisi cetak (offset X/Y dan skala)
	- Toggle border konten label ON/OFF
	- Toggle border grid label ON/OFF

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
| `/admin/face-register` | Registrasi wajah pengguna |
| `/admin/loans?format=label107` | Cetak label barcode T&J No.107 |
| `/admin/loans?format=label103` | Cetak label barcode T&J No.103 |
| `/admin/loans?format=a4&grid=ringkas` | Preview barcode A4 preset Ringkas |

## Integrasi Face Recognition

### 1) Konfigurasi Laravel

Tambahkan env berikut (sudah ada di `.env.example`):

```env
FACE_RECOGNITION_TOLERANCE=0.45
```

Migrasi field wajah pada tabel user:

```bash
php artisan migrate
```

### 2) Alur Pemakaian

- Buka menu admin `Register Wajah`, pilih user, aktifkan kamera, capture, simpan.
- Browser menghitung descriptor wajah dengan face-api.js, lalu Laravel menyimpan descriptor dan thumbnail.
- Di dashboard public mode peminjaman, kamera aktif otomatis untuk mengenali user tanpa service Python terpisah.
- Setelah wajah dikenali, scan barcode barang lalu konfirmasi peminjaman.

## Format Label Cetak

Menu barcode tersedia di route `/admin/loans` dengan opsi format `a4`, `label107`, dan `label103`.

### Label T&J No. 107 (Konfigurasi Aktif)

| Properti | Nilai |
|---|---|
| Merek | Tom & Jerry (T&J) |
| Nomor seri | No. 107 |
| Ukuran lembar | 21 x 16.5 cm |
| Orientasi | Portrait |
| Ukuran label | 64 x 32 mm |
| Susunan grid | 3 kolom x 4 baris |
| Label per lembar | 12 label |
| Jarak antar label | 5 mm |
| Area grid | 202 mm x 143 mm |
| Padding lembar (T/R/B/L) | 11 mm / 4 mm / 11 mm / 4 mm |

### Label T&J No. 103

| Properti | Nilai |
|---|---|
| Merek | Tom & Jerry (T&J) |
| Nomor seri | No. 103 |
| Ukuran lembar | A4 (210 x 297 mm) |
| Orientasi | Portrait |
| Susunan grid | 3 kolom x 10 baris |
| Label per lembar | 30 label |

### Fitur Cetak Barcode

- Download gambar format PNG/JPEG
- Download PDF
- Print langsung dari browser
- Kalibrasi posisi cetak (horizontal, vertikal, skala)
- Dropdown Border Konten Label (ON/OFF)
- Dropdown Border Grid Label (ON/OFF)

### Isi Setiap Label

- Kategori aset
- Nama aset (Brand + Model)
- Barcode batang (CODE128)
- Tanggal cetak
- Nama departemen/unit

## Seed Default

Seeder membuat data awal berikut:

- 1 admin
- 1 guru
- 1 siswa
- sample assets + sample loans

Password admin default seed:

- `admin12345`
