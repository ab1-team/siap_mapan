# Catatan Deployment

File ini di-push untuk trigger deployment hook di hosting.

## Status per 28 September 2026

- File `public/assets/css/custom.css` di GitHub sudah **bersih** (tidak ada styling hijau sidebar).
- Commit terbaru: `d8ebb03`.
- Jika hosting online masih menampilkan sidebar berwarna hijau, kemungkinan:
  1. Cache browser (`Ctrl+Shift+R`)
  2. Cache Laravel di hosting (`php artisan view:clear && php artisan cache:clear`)
  3. CDN/proxy cache di depan hosting

## Audit class Keuangan (28 September 2026)

Pengecekan sensitivitas huruf `App\Utils\Keuangan` di dashboard dan view terkait:

- `DashboardController.php`: semua `use App\Utils\Keuangan;` dan `new Keuangan` konsisten (`U` besar, `K` besar).
- View `partialsDashboard/sps.blade.php`, `tunggakan2.blade.php`, `tunggakan1.blade.php`: hanya `use App\Utils\Tanggal;` — Keuangan tidak dipakai, controller mengirim `$keuangan` via `with()` untuk dipakai di view lain bila perlu.
- View `welcome.blade.php`: tidak pakai class Keuangan (semua variabel yang dilempar controller adalah primitive/array).
- `composer dump-autoload` & `php artisan optimize:clear` sudah dijalankan di lokal untuk memastikan autoloader fresh.

## Fix nama file Keuangan.php (commit dc11590)

Akar masalah error **"Class App\\Utils\\Keuangan not found"** di Linux production:
- Di repo GitHub, file `app/Utils/keuangan.php` dan `app/Utils/tanggal.php` tersimpan dengan **huruf kecil** sejak Januari 2025 (commit `465256c`).
- Semua `use` statement di codebase pakai **PascalCase** (`App\Utils\Keuangan`, `App\Utils\Tanggal`).
- Di Windows filesystem case-insensitive, jadi kelihatan jalan; di Linux (production) case-sensitive → PSR-4 cari `Keuangan.php` tapi file cuma ada `keuangan.php` → Class not found.
- Fix: commit `dc11590` rename `keuangan.php` → `Keuangan.php` dan `tanggal.php` → `Tanggal.php`. Setelah ini PSR-4 autoloading jalan di Linux.

Pastikan juga di server hosting file-nya sudah benar:
```bash
ls -la /path/to/app/app/Utils/
# Harus menampilkan Keuangan.php dan Tanggal.php (huruf besar)
```

## Cara pull manual ke hosting

```bash
cd /path/to/app
git pull origin master
composer dump-autoload --optimize-autoloader
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

Atau upload manual `public/assets/css/custom.css` via FTP dari commit `d8ebb03`.